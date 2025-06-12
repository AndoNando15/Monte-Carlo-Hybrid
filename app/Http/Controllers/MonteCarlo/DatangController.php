<?php
namespace App\Http\Controllers\MonteCarlo;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DatangController extends Controller
{
    public function index()
    {
        $datangData = Dataset::pluck('datang');
        $groupedDatasets = collect();
        $rangeMapping = [];
        $simulasi = [];
        $randomNumbers = []; // Menyimpan angka acak
        $apeResults = []; // Menyimpan APE per simulasi
        $averageApePerSimulation = []; // Menyimpan rata-rata APE per simulasi (per hari)
        $totalApe = 0; // Untuk menghitung MAPE
        $totalCount = 0; // Untuk menghitung jumlah data APE

        // Mengecek apakah data datang ada
        if (!$datangData->isEmpty()) {
            $frequencies = $datangData->countBy();
            $total = $frequencies->sum();
            $cumulative = 0;

            // Membuat range, probabilitas, dan komulatif
            foreach ($frequencies->sortKeys() as $value => $count) {
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

            // Simulasi Monte Carlo: 22 hari, 5 nilai acak per hari
            for ($i = 0; $i < 22; $i++) {
                $dailySimulation = [];
                $dailyRandomNumbers = []; // Menyimpan angka acak untuk setiap hari

                for ($j = 0; $j < 5; $j++) {
                    $randomValue = rand(0, 100); // Angka acak antara 0 dan 100

                    // Menyimpan angka acak
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
                $randomNumbers[] = $dailyRandomNumbers;
                $simulasi[] = $dailySimulation;

                // Menghitung APE untuk setiap simulasi per hari
                $dailyApe = [];
                foreach ($dailySimulation as $index => $sim) {
                    // Mengambil nilai aktual (datang)
                    $actualValue = $datangData[$index % count($datangData)]; // Bisa disesuaikan
                    $ape = abs(($sim - $actualValue) / $actualValue) * 100; // Rumus APE
                    $dailyApe[] = round($ape, 2); // Menyimpan APE per simulasi
                    $totalApe += $ape; // Menambahkan ke total APE
                    $totalCount++; // Menambah jumlah data APE
                }
                $apeResults[] = $dailyApe; // Menyimpan APE per hari

                // Menghitung rata-rata APE per simulasi (per hari)
                $averageApePerSimulation[] = round(array_sum($dailyApe) / count($dailyApe), 2);
            }
        }

        // Menghitung MAPE (Mean Absolute Percentage Error)
        $mape = $totalCount > 0 ? round($totalApe / $totalCount, 2) : 0;

        // Menghitung akurasi keseluruhan berdasarkan MAPE
        $accuracy = 100 - $mape;

        // Menghitung akurasi per simulasi (berdasarkan rata-rata APE per simulasi)
        $accuracyPerSimulation = array_map(function ($avgApe) {
            return 100 - $avgApe; // Akurasi per simulasi
        }, $averageApePerSimulation);

        return view('pages.monte-carlo.datang.index', compact('groupedDatasets', 'simulasi', 'randomNumbers', 'apeResults', 'averageApePerSimulation', 'accuracy', 'accuracyPerSimulation', 'mape'));
    }
}