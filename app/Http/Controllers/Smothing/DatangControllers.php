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
        $datasets = Dataset::orderBy('tanggal')->get();

        $firstMonth = Carbon::parse($datasets->first()->tanggal)->month;
        $secondMonth = $firstMonth + 1;

        $datasets_initial_trend = $datasets->filter(function ($data) use ($firstMonth, $secondMonth) {
            $month = Carbon::parse($data->tanggal)->month;
            return $month == $firstMonth || $month == $secondMonth;
        });

        $datasets_by_month = $datasets->groupBy(function ($date) {
            return Carbon::parse($date->tanggal)->format('Y-m');
        });

        $datasets_filtered = $datasets_by_month->map(function ($monthData) {
            return $monthData->take(20);
        })->flatten();

        // Ambil data bulan pertama dan kedua
        $datasets_first_month = $datasets_initial_trend->filter(function ($data) use ($firstMonth) {
            return Carbon::parse($data->tanggal)->month == $firstMonth;
        })->values();

        $datasets_second_month = $datasets_initial_trend->filter(function ($data) use ($secondMonth) {
            return Carbon::parse($data->tanggal)->month == $secondMonth;
        })->values();

        // Perhitungan Initial Trend berdasarkan pairing indeks
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
                '(M2-M1)/20' => number_format($m2_m1_per_20, 4),
            ];
        }

        $averageInitialTrend = collect($initialTrendData)->avg(function ($item) {
            return (float) $item['(M2-M1)/20'];
        });

        // Rata-rata datang bulan pertama
        $average = $datasets_first_month->avg('datang');

        $lastDateFirstMonth = $datasets_first_month->last()->tanggal;

        foreach ($datasets_filtered as $data) {
            if (Carbon::parse($data->tanggal)->month == $firstMonth) {
                $data->level_at = $average;
            } else {
                $previousMonthData = $datasets_filtered->filter(function ($item) use ($data) {
                    return Carbon::parse($item->tanggal)->month == Carbon::parse($data->tanggal)->month - 1;
                })->last();

                $alpha = 0.1;
                $levelAtPrev = $previousMonthData ? $previousMonthData->level_at : 0;
                $trend = $previousMonthData ? $previousMonthData->trend_t : 0;
                $seasonal = $previousMonthData ? $previousMonthData->seasonal_st : 0;

                $data->level_at = $seasonal != 0
                    ? $alpha * ($data->datang / $seasonal) + (1 - $alpha) * ($levelAtPrev + $trend)
                    : 0;
            }
        }

        foreach ($datasets_filtered as $data) {
            $data->trend_t = Carbon::parse($data->tanggal)->eq(Carbon::parse($lastDateFirstMonth))
                ? $averageInitialTrend
                : 0;
        }

        foreach ($datasets_filtered as $data) {
            $data->seasonal_st = ($data->level_at && $data->datang > 0)
                ? $data->level_at / $data->datang
                : 0;
        }

        foreach ($datasets as $dataset) {
            $carbonDate = Carbon::parse($dataset->tanggal)->locale('id');
            $dataset->tanggal = $carbonDate->isoFormat('D MMMM YYYY');
            $dataset->hari = $carbonDate->isoFormat('dddd');
        }

        return view('pages.smothing.datang.index', compact(
            'datasets_filtered',
            'datasets_initial_trend',
            'initialTrendData',
            'averageInitialTrend'
        ));
    }
}