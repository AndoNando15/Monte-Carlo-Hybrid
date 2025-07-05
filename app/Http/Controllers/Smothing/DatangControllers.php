<?php

namespace App\Http\Controllers\Smothing;

use App\Models\Dataset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DatangControllers extends Controller
{
    public function index()
    {
        $datasets = Dataset::orderBy('tanggal')->get();

        $datasets_filtered = $datasets
            ->groupBy(function ($data) {
                return Carbon::parse($data->tanggal)->format('Y-m');
            })
            ->flatMap(function ($group) {
                return $group->sortBy('tanggal')->take(20);
            })
            ->sortBy('tanggal')
            ->values();

        $first_20_data = $datasets_filtered->take(20);
        $average = $first_20_data->avg('datang');

        $firstMonth = Carbon::parse($datasets->first()->tanggal)->month;
        $secondMonth = $firstMonth + 1;

        $datasets_initial_trend = $datasets->filter(function ($data) use ($firstMonth, $secondMonth) {
            $month = Carbon::parse($data->tanggal)->month;
            return $month == $firstMonth || $month == $secondMonth;
        });

        $datasets_first_month = $datasets_initial_trend->filter(function ($data) use ($firstMonth) {
            return Carbon::parse($data->tanggal)->month == $firstMonth;
        })->values();

        $datasets_second_month = $datasets_initial_trend->filter(function ($data) use ($secondMonth) {
            return Carbon::parse($data->tanggal)->month == $secondMonth;
        })->values();

        $initialTrendData = [];
        $pairCount = min($datasets_first_month->count(), $datasets_second_month->count());

        for ($i = 0; $i < $pairCount; $i++) {
            $m1 = $datasets_first_month[$i]->datang;
            $m2 = $datasets_second_month[$i]->datang;

            $initialTrendData[] = [
                'Month1' => $m1,
                'Month2' => $m2,
                'M2-M1' => $m2 - $m1,
                '(M2-M1)/20' => ($m2 - $m1) / 20,
            ];
        }

        $averageInitialTrend = collect($initialTrendData)->avg(function ($item) {
            return $item['(M2-M1)/20'];
        });

        // === LOOP PERTAMA: JANUARI–NOVEMBER ===
        foreach ($datasets_filtered as $index => $data) {
            $carbonDate = Carbon::parse($data->tanggal);
            $month = $carbonDate->month;
            $dateString = $carbonDate->format('Y-m-d');
            $previousData = $datasets_filtered[$index - 1] ?? null;

            if ($month == 12)
                continue;

            // Tanggal 28 Jan pakai initial trend
            if ($dateString === '2023-01-28') {
                $data->trend_t = $averageInitialTrend;
            }

            if ($index < 20) {
                $data->level_at = $average;
            } else {
                $alpha = 0.1;
                $levelPrev = $previousData->level_at ?? 0;
                $trendPrev = $previousData->trend_t ?? 0;
                $seasonalPrev = $previousData->seasonal_st ?? 1;
                $data->level_at = $alpha * ($data->datang / $seasonalPrev) + (1 - $alpha) * ($levelPrev + $trendPrev);
            }

            if ($index > 19 && $month >= 2 && $month <= 11) {
                $beta = 0.05;
                $levelNow = $data->level_at ?? 0;
                $levelPrev = $previousData->level_at ?? 0;
                $trendPrev = $previousData->trend_t ?? 0;
                $data->trend_t = $beta * ($levelNow - $levelPrev) + (1 - $beta) * $trendPrev;
            }

            if ($index < 20) {
                $data->seasonal_st = ($data->datang > 0) ? $data->datang / $data->level_at : 0;
            } else {
                $gamma = 0.1;
                $prevSeasonal = $previousData->seasonal_st ?? 1;
                $levelAtNow = $data->level_at ?? 1;
                $datangNow = $data->datang ?? 1;
                $data->seasonal_st = ($levelAtNow != 0)
                    ? $gamma * ($datangNow / $levelAtNow) + (1 - $gamma) * $prevSeasonal
                    : $prevSeasonal;
            }

            if ($index >= 20) {
                $seasonal_base = $datasets_filtered->take(20)->values();
                $level = $data->level_at ?? 0;
                $trend = $data->trend_t ?? 0;
                $seasonalIndex = ($index - 20) % 20;
                $seasonal = $seasonal_base[$seasonalIndex]->seasonal_st ?? 1;
                $data->forecast = ($level + $trend) * $seasonal;
            }

            $actual = $data->datang ?? 0;
            $forecast = $data->forecast ?? null;

            if (!is_null($forecast) && $actual != 0) {
                $data->error = $actual - $forecast;
                $data->absolute_error = abs($data->error);
                $data->squared_error = pow($data->error, 2);
                $data->absolute_percentage_error = abs($data->error) / $actual;
            }

            // Format tanggal
            $data->tanggal_iso = Carbon::parse($data->tanggal)->format('Y-m-d');
        }

        // === Ambil nilai trend terakhir bulan November
        $novemberLastData = $datasets_filtered->filter(fn($d) => Carbon::parse($d->tanggal)->month === 11)->last();
        $trendNovemberLast = $novemberLastData ? (float) $novemberLastData->trend_t : 0;
        Log::info("📌 Trend Tt akhir November: $trendNovemberLast");

        // === LOOP KEDUA: DESEMBER
        $desemberUrutan = 0;
        foreach ($datasets_filtered as $index => $data) {
            $carbonDate = Carbon::parse($data->tanggal);
            if ($carbonDate->month !== 12)
                continue;

            $desemberUrutan++;
            $data->trend_t = $trendNovemberLast * $desemberUrutan;
            Log::info("📈 Desember ke-$desemberUrutan | trend_t = $data->trend_t");

            $data->level_at = null;
            $data->seasonal_st = null;
            $data->forecast = null;
            $data->error = null;
            $data->absolute_error = null;
            $data->squared_error = null;
            $data->absolute_percentage_error = null;

            $data->tanggal_iso = $carbonDate->format('Y-m-d');
        }

        // === Untuk tampilan Blade
        foreach ($datasets_filtered as $data) {
            $carbon = Carbon::parse($data->tanggal)->locale('id');
            $data->tanggal = $carbon->isoFormat('D MMMM YYYY');
            $data->hari = $carbon->isoFormat('dddd');
        }

        $desemberDataForLog = collect($datasets_filtered)
            ->filter(fn($d) => Carbon::parse($d->tanggal_iso)->month === 12)
            ->values()
            ->map(function ($d, $i) {
                return [
                    'urutan' => $i + 1,
                    'tanggal' => $d->tanggal_iso,
                    'datang' => $d->datang,
                    'trend_t' => $d->trend_t,
                ];
            });

        $averageLevelAt = $datasets_filtered->take(20)->avg('level_at');

        return view('pages.smothing.datang.index', compact(
            'datasets_filtered',
            'initialTrendData',
            'averageInitialTrend',
            'averageLevelAt',
            'desemberDataForLog'
        ));
    }
}