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
        $datangData = Dataset::select('datang', 'tanggal')->get();
        $groupedDatasets = collect();
        $rangeMapping = [];
        $monthlyResults = [];

        if (!$datangData->isEmpty()) {
            $datangData = $datangData->sortBy('datang');

            $frequencies = $datangData->groupBy('datang')->map(fn($group) => $group->count());

            $total = $frequencies->sum();
            $cumulative = 0;
            $previousMax = 0;

            foreach ($frequencies as $value => $count) {
                $probability = $count / $total;
                $cumulative += $probability;

                $min = $previousMax;
                $max = (round($cumulative, 4) == 1.0000) ? 100 : ceil(($cumulative * 100) - 1);
                $previousMax = $max + 1;

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

            $groupedByMonth = $datangData->groupBy(fn($dataset) => Carbon::parse($dataset->tanggal)->format('M-Y'));

            foreach ($groupedByMonth as $month => $dailyData) {
                $simulasiPerMonth = [];
                $randomNumbersPerMonth = [];
                $apePerMonth = [];
                $comparisonPerMonth = [];

                foreach ($dailyData as $dayData) {
                    $dailyRandomNumbers = [];
                    $dailySimulation = [];
                    $dailyAkurasi = [];
                    $dailyAPE = [];

                    for ($i = 0; $i < 5; $i++) {
                        $randomValue = rand(0, 100);
                        $dailyRandomNumbers[] = $randomValue;

                        foreach ($rangeMapping as $range) {
                            if ($randomValue >= $range['min'] && $randomValue <= $range['max']) {
                                $dailySimulation[] = $range['datang'];
                                break;
                            }
                        }
                    }

                    $actualValue = $dayData->datang;

                    foreach ($dailySimulation as $sim) {
                        $error = abs($sim - $actualValue);
                        $accuracy = 100 - $error;

                        $ape = ($actualValue != 0)
                            ? abs(($sim - $actualValue) / $actualValue)
                            : 0;

                        $dailyAkurasi[] = $accuracy;
                        $dailyAPE[] = $ape;
                        $apePerMonth[] = $ape;
                    }

                    $comparisonPerMonth[] = [
                        'random_numbers' => $dailyRandomNumbers,
                        'simulations' => $dailySimulation,
                        'accuracies' => $dailyAkurasi,
                        'apes' => $dailyAPE,
                        'actual' => $actualValue,
                    ];

                    $randomNumbersPerMonth[] = $dailyRandomNumbers;
                    $simulasiPerMonth[] = $dailySimulation;
                }

                // Hitung rata-rata akurasi setiap kolom simulasi
                $numRows = count($comparisonPerMonth);
                $sumAccuracies = array_fill(0, 5, 0);
                $bestPredictions = [];

                foreach ($comparisonPerMonth as $row) {
                    foreach ($row['accuracies'] as $i => $acc) {
                        $sumAccuracies[$i] += $acc;
                    }
                }

                $avgAccuracies = array_map(fn($sum) => $numRows > 0 ? $sum / $numRows : 0, $sumAccuracies);
                $bestSimulationIndex = array_keys($avgAccuracies, max($avgAccuracies))[0];

                // Simpan nilai prediksi terbaik per baris
                foreach ($comparisonPerMonth as $row) {
                    $bestPredictions[] = $row['simulations'][$bestSimulationIndex];
                }

                $mapePerMonth = $this->calculateMape($apePerMonth);
                $accuracyPerMonth = 100 - $mapePerMonth;

                $monthlyResults[$month] = [
                    'simulasi' => $simulasiPerMonth,
                    'random_numbers' => $randomNumbersPerMonth,
                    'comparison' => $comparisonPerMonth,
                    'ape' => $apePerMonth,
                    'mape' => $mapePerMonth,
                    'accuracy' => $accuracyPerMonth,
                    'best_simulation_index' => $bestSimulationIndex,
                    'best_predictions' => $bestPredictions,
                    'best_simulation_avg_accuracy' => $avgAccuracies[$bestSimulationIndex],
                ];
            }
        }

        $selectedMonth = $request->input('month', null);
        $selectedMonthResults = [];

        if ($selectedMonth && isset($monthlyResults[$selectedMonth])) {
            $selectedMonthResults = $monthlyResults[$selectedMonth];
        }

        return view('pages.monte-carlo.datang.index', compact(
            'groupedDatasets',
            'monthlyResults',
            'selectedMonthResults',
            'selectedMonth'
        ));
    }

    private function calculateMape($apeResults)
    {
        if (is_array($apeResults)) {
            $totalApe = array_sum($apeResults);
            $count = count($apeResults);
        } else {
            $totalApe = $apeResults;
            $count = 1;
        }

        return ($count > 0) ? $totalApe / $count : 0;
    }
}