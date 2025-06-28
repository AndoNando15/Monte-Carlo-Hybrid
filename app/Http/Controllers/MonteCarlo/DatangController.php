<?php

namespace App\Http\Controllers\MonteCarlo;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use Illuminate\Http\Request;
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
        $comparisonResults = []; // For storing comparison results

        // Mengecek apakah data datang ada
        if (!$datangData->isEmpty()) {
            // Sort data datang by 'datang' to ensure it starts from 0
            $datangData = $datangData->sortBy('datang');

            $frequencies = $datangData->groupBy('datang')->map(function ($group) {
                return $group->count();
            });  // Group by 'datang' value and count the occurrences

            $total = $frequencies->sum();
            $cumulative = 0;

            // Membuat range, probabilitas, dan komulatif
            $previousMax = 0;
            foreach ($frequencies as $value => $count) {
                $probability = $count / $total;
                $cumulative += $probability;

                // Set the range based on the previous max and the current cumulative value
                $min = $previousMax;
                $max = (round($cumulative, 4) == 1.0000) ? 100 : ceil(($cumulative * 100) - 1);

                // Update previousMax to the current max
                $previousMax = $max + 1;

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
                $comparisonPerMonth = []; // Initialize comparison array

                // Simulasi untuk setiap hari dalam bulan tersebut
                foreach ($dailyData as $dayData) {
                    $dailySimulation = [];
                    $dailyRandomNumbers = [];

                    // Generate 1 random value for the simulation (instead of 5 random values)
                    $randomValue = rand(0, 100); // Angka acak antara 0 dan 100
                    $dailyRandomNumbers[] = $randomValue;

                    // Tentukan "datang" berdasarkan range
                    foreach ($rangeMapping as $range) {
                        if ($randomValue >= $range['min'] && $randomValue <= $range['max']) {
                            $dailySimulation[] = $range['datang'];
                            break;
                        }
                    }

                    // Menyimpan angka acak dan simulasi harian
                    $randomNumbersPerMonth[] = $dailyRandomNumbers;
                    $simulasiPerMonth[] = $dailySimulation;

                    // Menghitung APE dan perbandingan untuk setiap simulasi per hari
                    $dailyComparison = [];  // Initialize comparison array

                    // Only 1 simulation per day
                    foreach ($dailySimulation as $index => $sim) {
                        $actualValue = $datangData[$index % count($datangData)]->datang;  // Mengakses nilai datang yang benar

                        $error = abs($sim - $actualValue);  // Menghitung error
                        $accuracy = 100 - $error;  // Akurasi dihitung dengan 100 - error

                        $dailyComparison[] = [
                            'predicted' => $sim,        // Nilai prediksi
                            'actual' => $actualValue,   // Nilai aktual
                            'difference' => $error,    // Selisih
                            'error' => $error,         // Error absolut (tanpa pembulatan berlebihan)
                            'accuracy' => $accuracy,   // Akurasi
                        ];

                        // Menghitung APE
                        $ape = abs(($sim - $actualValue) / $actualValue) * 100;  // Menghitung APE
                        $apePerMonth[] = $ape;  // Menyimpan APE per simulasi tanpa pembulatan
                    }

                    // Menyimpan perbandingan per hari
                    $comparisonPerMonth[] = $dailyComparison;
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
                    'comparison' => $comparisonPerMonth, // Store comparison data
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
    // Fungsi untuk menghitung MAPE
    private function calculateMape($apeResults)
    {
        if (is_array($apeResults)) {
            $totalApe = array_sum($apeResults); // Hanya menjumlahkan array
            $count = count($apeResults);
        } else {
            $totalApe = $apeResults;
            $count = 1;  // Cukup 1 jika hanya satu nilai
        }

        // Mengembalikan MAPE tanpa pembulatan yang tidak perlu
        return ($count > 0) ? $totalApe / $count : 0;
    }

}