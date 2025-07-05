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

        // Filter data untuk bulan pertama dan kedua, hanya ambil 20 data pertama
        $datasets_filtered = $datasets->filter(function ($data) use ($firstMonth) {
            return Carbon::parse($data->tanggal)->month == $firstMonth || Carbon::parse($data->tanggal)->month == $firstMonth + 1;
        })->take(20); // Ambil hanya 20 data pertama

        // Hitung rata-rata untuk LEVEL At pada bulan pertama
        $datasets_first_month = $datasets_filtered->filter(function ($data) use ($firstMonth) {
            return Carbon::parse($data->tanggal)->month == $firstMonth;
        });

        $totalKedatangan = $datasets_first_month->sum('datang');
        $count = $datasets_first_month->count();
        $average = $totalKedatangan / $count;

        // Menentukan tanggal terakhir bulan pertama
        $lastDateFirstMonth = $datasets_first_month->last()->tanggal;

        // Menyimpan LEVEL At hanya pada tanggal terakhir bulan pertama
        foreach ($datasets_filtered as $data) {
            $data->level_at = (Carbon::parse($data->tanggal)->eq(Carbon::parse($lastDateFirstMonth))) ? $average : null;
        }

        // Perhitungan Initial Trend untuk 20 data pertama
        $initialTrendData = [];
        for ($i = 0; $i < count($datasets_filtered) - 1; $i++) {
            $month1 = $datasets_filtered[$i]->datang;
            $month2 = $datasets_filtered[$i + 1]->datang;

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

        // Kirim data ke view
        return view('pages.smothing.datang.index', compact('datasets_filtered', 'initialTrendData', 'averageInitialTrend'));
    }
}