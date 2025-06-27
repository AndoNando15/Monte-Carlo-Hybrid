<?php
namespace App\Http\Controllers\MonteCarlo;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DatangController extends Controller
{
    public function index(Request $request)
    {
        $datangData = Dataset::select('datang', 'tanggal')->get();  // Get both 'datang' and 'tanggal' for grouping
        $groupedDatasets = collect();
        $rangeMapping = [];
        $simulasi = [];
        $randomNumbers = []; // Menyimpan angka acak
        $apeResults = []; // Menyimpan APE per simulasi
        $monthlyResults = [];

        // Mengecek apakah data datang ada
        if (!$datangData->isEmpty()) {
            $frequencies = $datangData->groupBy('datang')->map(function ($group) {
                return $group->count();
            });  // Group by 'datang' value and count the occurrences

            $total = $frequencies->sum();
            $cumulative = 0;

            // Membuat range, probabilitas, dan komulatif
            foreach ($frequencies as $value => $count) {
                $probability = $count / $total;
                $cumulative += $probability;
                $min = floor(($cumulative - $probability) * 100);
                $max = (round($cumulative, 4) == 1.0000) ? 100 : ceil(($cumulative * 100) - 1);

                // Menambahkan data yang dihitung ke dalam collection
                $groupedDatasets->push([
                    'datang' => $value,
                    'frekuensi' => $count,
                    'probabilitas' => round($probability, 4),
                    'komulatif' => round($cumulative, 4),
                    'range' => $min . ' - ' . $max,
                ]);

                $rangeMapping[] = [
                    'min' => $min,
                    'max' => $max,
                    'datang' => $value,
                ];
            }

            // Group data by month using 'tanggal' (date) field from the Dataset model
            $groupedByMonth = $datangData->groupBy(function ($dataset) {
                return Carbon::parse($dataset->tanggal)->format('M-Y'); // Format date to month-year (e.g., Jan-2023)
            });

            // Simulasi Monte Carlo: Simulasi per bulan berdasarkan jumlah hari
            foreach ($groupedByMonth as $month => $dailyData) {
                $simulasiPerMonth = [];
                $randomNumbersPerMonth = [];
                $apePerMonth = [];

                // Simulasi untuk setiap hari dalam bulan tersebut
                foreach ($dailyData as $dayData) {
                    $dailySimulation = [];
                    $dailyRandomNumbers = [];

                    for ($j = 0; $j < 5; $j++) {
                        $randomValue = rand(0, 100); // Angka acak antara 0 dan 100
                        $dailyRandomNumbers[] = $randomValue;

                        // Tentukan "datang" berdasarkan range
                        foreach ($rangeMapping as $range) {
                            if ($randomValue >= $range['min'] && $randomValue <= $range['max']) {
                                $dailySimulation[] = $range['datang'];
                                break;
                            }
                        }
                    }

                    // Menyimpan angka acak dan simulasi harian
                    $randomNumbersPerMonth[] = $dailyRandomNumbers;
                    $simulasiPerMonth[] = $dailySimulation;

                    // Menghitung APE untuk setiap simulasi per hari
                    $dailyApe = [];
                    foreach ($dailySimulation as $index => $sim) {
                        $actualValue = $datangData[$index % count($datangData)]->datang; // Get 'datang' value directly
                        $ape = abs(($sim - $actualValue) / $actualValue) * 100; // Rumus APE
                        $dailyApe[] = round($ape, 2); // Menyimpan APE per simulasi
                    }

                    // Menyimpan APE per hari
                    $apePerMonth[] = $dailyApe;
                }

                // Menghitung MAPE dan akurasi untuk bulan ini
                $mapePerMonth = $this->calculateMape($apePerMonth);
                $accuracyPerMonth = 100 - $mapePerMonth;

                // Menyimpan hasil simulasi dan APE untuk bulan ini
                $monthlyResults[$month] = [
                    'simulasi' => $simulasiPerMonth,
                    'ape' => $apePerMonth,
                    'mape' => $mapePerMonth,
                    'accuracy' => $accuracyPerMonth,
                ];
            }
        }

        // Handle month selection if passed in the request
        $selectedMonth = $request->input('month', null);
        $selectedMonthResults = [];

        // Only populate selected month results if a month is selected and it exists
        if ($selectedMonth && isset($monthlyResults[$selectedMonth])) {
            $selectedMonthResults = $monthlyResults[$selectedMonth];
        }

        return view('pages.monte-carlo.datang.index', compact('groupedDatasets', 'monthlyResults', 'selectedMonthResults', 'selectedMonth'));
    }


    // Fungsi untuk menghitung MAPE
    private function calculateMape($apeResults)
    {
        $totalApe = array_sum(array_map('array_sum', $apeResults));
        $count = count($apeResults);
        return ($count > 0) ? round($totalApe / $count, 2) : 0;
    }
}