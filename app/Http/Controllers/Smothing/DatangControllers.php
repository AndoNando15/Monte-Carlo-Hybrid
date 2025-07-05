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
        // Ambil data kedatangan dari database dan urutkan berdasarkan tanggal
        $datasets = Dataset::orderBy('tanggal')->get();

        // Ambil bulan pertama berdasarkan data yang ada
        $firstMonth = Carbon::parse($datasets->first()->tanggal)->month;

        // Ambil bulan kedua
        $secondMonth = $firstMonth + 1;

        // Filter data untuk bulan pertama dan kedua, hanya ambil 20 data pertama untuk Initial Trend
        $datasets_initial_trend = $datasets->filter(function ($data) use ($firstMonth, $secondMonth) {
            $month = Carbon::parse($data->tanggal)->month;
            return $month == $firstMonth || $month == $secondMonth;
        })->take(21); // Ambil hanya 20 data pertama untuk Initial Trend

        // Mengelompokkan data berdasarkan bulan dan hanya mengambil 20 data per bulan
        $datasets_by_month = $datasets->groupBy(function ($date) {
            return Carbon::parse($date->tanggal)->format('Y-m'); // Kelompokkan berdasarkan bulan
        });

        // Batasi jumlah data per bulan menjadi 20
        $datasets_filtered = $datasets_by_month->map(function ($monthData) {
            return $monthData->take(20); // Ambil 20 data pertama setiap bulan
        })->flatten();

        // Hitung rata-rata untuk LEVEL At pada bulan pertama
        $datasets_first_month = $datasets_initial_trend->filter(function ($data) use ($firstMonth) {
            return Carbon::parse($data->tanggal)->month == $firstMonth;
        });

        $totalKedatangan = $datasets_first_month->sum('datang');
        $count = $datasets_first_month->count();
        $average = $totalKedatangan / $count;

        // Menentukan tanggal terakhir bulan pertama
        $lastDateFirstMonth = $datasets_first_month->last()->tanggal;

        // Menyimpan LEVEL At hanya pada tanggal terakhir bulan pertama
        foreach ($datasets_filtered as $data) {
            // LEVEL At diisi dengan nilai rata-rata bulan pertama pada semua baris bulan pertama
            if (Carbon::parse($data->tanggal)->month == $firstMonth) {
                $data->level_at = $average;
            } else {
                // Rumus LEVEL At untuk bulan kedua
                $previousMonthData = $datasets_filtered->filter(function ($item) use ($data) {
                    return Carbon::parse($item->tanggal)->month == Carbon::parse($data->tanggal)->month - 1;
                })->last(); // Ambil data bulan sebelumnya
                $alpha = 0.1; // Set alpha value as per your requirement
                $levelAtPrev = $previousMonthData ? $previousMonthData->level_at : 0;
                $trend = $previousMonthData ? $previousMonthData->trend_t : 0;
                $seasonal = $previousMonthData ? $previousMonthData->seasonal_st : 0;

                // Prevent division by zero in LEVEL At formula
                if ($seasonal != 0) {
                    $data->level_at = $alpha * ($data->datang / $seasonal) + (1 - $alpha) * ($levelAtPrev + $trend);
                } else {
                    $data->level_at = 0; // If seasonal is zero, set LEVEL At to 0
                }
            }
        }

        // Perhitungan Initial Trend untuk 20 data pertama
        $initialTrendData = [];
        for ($i = 0; $i < count($datasets_initial_trend) - 1; $i++) {
            $month1 = $datasets_initial_trend[$i]->datang;
            $month2 = $datasets_initial_trend[$i + 1]->datang;

            $m2_m1 = $month2 - $month1; // M2 - M1
            $m2_m1_per_20 = $m2_m1 / 20; // (M2 - M1) / 20

            $initialTrendData[] = [
                'Month1' => $month1,
                'Month2' => $month2,
                'M2-M1' => $m2_m1,
                '(M2-M1)/20' => $m2_m1_per_20
            ];
        }

        // Rata-rata dari kolom (M2-M1)/20
        $averageInitialTrend = array_sum(array_column($initialTrendData, '(M2-M1)/20')) / count($initialTrendData);

        // Menambahkan TREND Tt pada tanggal terakhir bulan pertama
        foreach ($datasets_filtered as $data) {
            if (Carbon::parse($data->tanggal)->eq(Carbon::parse($lastDateFirstMonth))) {
                $data->trend_t = $averageInitialTrend;
            } else {
                $data->trend_t = 0;  // Kolom TREND Tt diisi 0 selain pada tanggal terakhir bulan pertama
            }
        }

        // Menambahkan kolom SEASONAL St(Musiman)
        foreach ($datasets_filtered as $data) {
            // SEASONAL St(Musiman) dihitung dengan rumus LEVEL At / Datang
            if ($data->level_at && $data->datang > 0) {
                $data->seasonal_st = $data->level_at / $data->datang; // Only divide if Datang > 0
            } else {
                // Jika tidak ada LEVEL At atau Datang = 0, SEASONAL St(Musiman) diset ke 0
                $data->seasonal_st = 0;
            }
        }

        // Format the date and day in Indonesian
        foreach ($datasets as $dataset) {
            $carbonDate = Carbon::parse($dataset->tanggal)->locale('id');
            $dataset->tanggal = $carbonDate->isoFormat('D MMMM YYYY');
            $dataset->hari = $carbonDate->isoFormat('dddd');
        }

        // Kirim data ke view
        return view('pages.smothing.datang.index', compact('datasets_filtered', 'datasets_initial_trend', 'initialTrendData', 'averageInitialTrend'));
    }
}