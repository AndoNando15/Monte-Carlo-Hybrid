<?php

namespace App\Http\Controllers\Smothing;

use App\Models\Dataset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class DatangControllers extends Controller
{
    public function index()
    {
        // Ambil semua data urut berdasarkan tanggal
        $datasets = Dataset::orderBy('tanggal')->get();

        // Ambil maksimal 20 data per bulan, lalu urutkan berdasarkan tanggal
        $datasets_filtered = $datasets
            ->groupBy(function ($data) {
                return Carbon::parse($data->tanggal)->format('Y-m');
            })
            ->flatMap(function ($group) {
                return $group->sortBy('tanggal')->take(20);
            })
            ->sortBy('tanggal')
            ->values();

        // Ambil 20 data pertama untuk perhitungan rata-rata awal LEVEL At
        $first_20_data = $datasets_filtered->take(20);
        $average = $first_20_data->avg('datang');

        // Cari initial trend dari dua bulan pertama
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
            $month1 = $datasets_first_month[$i]->datang;
            $month2 = $datasets_second_month[$i]->datang;

            $m2_m1 = $month2 - $month1;
            $m2_m1_per_20 = $m2_m1 / 20;

            $initialTrendData[] = [
                'Month1' => $month1,
                'Month2' => $month2,
                'M2-M1' => $m2_m1,
                '(M2-M1)/20' => $m2_m1_per_20,
            ];
        }

        $averageInitialTrend = collect($initialTrendData)->avg(function ($item) {
            return $item['(M2-M1)/20'];
        });

        // Hitung LEVEL At
        foreach ($datasets_filtered as $index => $data) {
            if ($index < 20) {
                $data->level_at = $average;
            } else {
                $previousData = $datasets_filtered[$index - 1] ?? null;

                $alpha = 0.1;
                $levelAtPrev = $previousData->level_at ?? 0;
                $trendPrev = $previousData->trend_t ?? 0;
                $seasonalPrev = $previousData->seasonal_st ?? 1; // cegah divide by zero

                $data->level_at = $alpha * ($data->datang / $seasonalPrev) + (1 - $alpha) * ($levelAtPrev + $trendPrev);
            }
        }

        // Hitung TREND Tt
        foreach ($datasets_filtered as $index => $data) {
            if ($index === 19) {
                // Baris ke-20 → isi TREND dari rata-rata initial trend
                $data->trend_t = $averageInitialTrend;
            } elseif ($index > 19) {
                $previousData = $datasets_filtered[$index - 1] ?? null;

                $beta = 0.05;
                $levelNow = $data->level_at ?? 0;
                $levelPrev = $previousData->level_at ?? 0;
                $trendPrev = $previousData->trend_t ?? 0;

                $data->trend_t = $beta * ($levelNow - $levelPrev) + (1 - $beta) * $trendPrev;
            } else {
                $data->trend_t = 0; // untuk 0-18
            }
        }

        // Hitung SEASONAL St
        foreach ($datasets_filtered as $index => $data) {
            if ($index < 20) {
                // 20 data pertama: rumus awal
                $data->seasonal_st = ($data->datang > 0)
                    ? $data->datang / $data->level_at
                    : 0;
            } else {
                // Data setelah 20 → gunakan rumus smoothing
                $gamma = 0.1;
                $prevSeasonal = $datasets_filtered[$index - 1]->seasonal_st ?? 1;
                $levelAtNow = $data->level_at ?? 1;
                $datangNow = $data->datang ?? 1;

                $data->seasonal_st = ($levelAtNow != 0)
                    ? $gamma * ($datangNow / $levelAtNow) + (1 - $gamma) * $prevSeasonal
                    : $prevSeasonal;
            }
        }
        $secondMonth = Carbon::parse($datasets_filtered[20]->tanggal)->month; // pastikan data ke-21 mulai dari bulan ke-2

        $seasonal_base = $datasets_filtered->take(20)->values(); // ambil St dari bulan pertama

        foreach ($datasets_filtered as $index => $data) {
            if ($index >= 20) {
                $level = $data->level_at ?? 0;
                $trend = $data->trend_t ?? 0;

                // Ambil seasonal berdasarkan pola musiman awal (index sejajar dengan baris pertama)
                $seasonalIndex = ($index - 20) % 20;
                $seasonal = $seasonal_base[$seasonalIndex]->seasonal_st ?? 1;

                $data->forecast = ($level + $trend) * $seasonal;
            } else {
                $data->forecast = null;
            }
        }

        foreach ($datasets_filtered as $data) {
            $actual = $data->datang ?? 0;
            $forecast = $data->forecast ?? null;

            if (!is_null($forecast) && $actual != 0) {
                $data->error = $actual - $forecast;
                $data->absolute_error = abs($data->error);
                $data->squared_error = pow($data->error, 2);
                $data->absolute_percentage_error = abs($data->error) / $actual;
            } else {
                $data->error = null;
                $data->absolute_error = null;
                $data->squared_error = null;
                $data->absolute_percentage_error = null;
            }
        }


        // Format tanggal & hari
        foreach ($datasets_filtered as $dataset) {
            $carbonDate = Carbon::parse($dataset->tanggal)->locale('id');
            $dataset->tanggal = $carbonDate->isoFormat('D MMMM YYYY');
            $dataset->hari = $carbonDate->isoFormat('dddd');
        }

        // Rata-rata LEVEL At untuk 20 data pertama
        $averageLevelAt = $datasets_filtered->take(20)->avg('level_at');

        return view('pages.smothing.datang.index', compact(
            'datasets_filtered',
            'initialTrendData',
            'averageInitialTrend',
            'averageLevelAt'
        ));
    }
}