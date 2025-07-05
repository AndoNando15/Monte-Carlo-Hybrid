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

        // Ambil 20 data pertama sebagai basis LEVEL At awal
        $datasets_filtered = $datasets->take(40)->values(); // Ambil lebih banyak kalau perlu

        // Ambil 20 data pertama untuk perhitungan rata-rata awal
        $first_20_data = $datasets_filtered->take(20);
        $average = $first_20_data->avg('datang');

        // Cari initial trend dari dua bulan pertama (optional, jika tetap ingin ditampilkan)
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
                $levelAtPrev = $previousData ? $previousData->level_at : 0;
                $trend = $previousData ? $previousData->trend_t : 0;
                $seasonal = $previousData ? $previousData->seasonal_st : 0;

                $data->level_at = $seasonal != 0
                    ? $alpha * ($data->datang / $seasonal) + (1 - $alpha) * ($levelAtPrev + $trend)
                    : 0;
            }
        }

        // Tentukan TREND Tt hanya untuk data ke-20 (index 19)
        foreach ($datasets_filtered as $index => $data) {
            $data->trend_t = ($index === 19) ? $averageInitialTrend : 0;
        }

        // Hitung SEASONAL St
        foreach ($datasets_filtered as $data) {
            $data->seasonal_st = ($data->level_at && $data->datang > 0)
                ? $data->level_at / $data->datang
                : 0;
        }

        // Format tanggal
        foreach ($datasets_filtered as $dataset) {
            $carbonDate = Carbon::parse($dataset->tanggal)->locale('id');
            $dataset->tanggal = $carbonDate->isoFormat('D MMMM YYYY');
            $dataset->hari = $carbonDate->isoFormat('dddd');
        }

        // Hitung rata-rata LEVEL At dari 20 data pertama
        $averageLevelAt = $datasets_filtered->take(20)->avg('level_at');

        return view('pages.smothing.datang.index', compact(
            'datasets_filtered',
            'initialTrendData',
            'averageInitialTrend',
            'averageLevelAt'
        ));
    }
}