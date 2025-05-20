<?php

namespace App\Http\Controllers\MonteCarlo;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use Illuminate\Http\Request;

class DatangController extends Controller
{
    public function index()
    {

        $datangData = Dataset::pluck('datang');
        $groupedDatasets = collect();

        if (!$datangData->isEmpty()) {
            $frequencies = $datangData->countBy();
            $total = $frequencies->sum();
            $cumulative = 0;

            foreach ($frequencies->sortKeys() as $value => $count) {
                $probability = $count / $total;
                $cumulative += $probability;
                $min = ($cumulative - $probability) * 100;

                // Cek jika cumulative sudah 1 (akhir), maka max = 100
                if (round($cumulative, 4) == 1.0000) {
                    $max = 100;
                } else {
                    $max = ($cumulative * 100) - 1;
                }

                $groupedDatasets->push([
                    'datang' => $value,
                    'frekuensi' => $count,
                    'probabilitas' => round($probability, 4),
                    'komulatif' => round($cumulative, 4),
                    'range' => floor($min) . ' - ' . ceil($max),
                ]);
            }

        }

        return view('pages.monte-carlo.datang.index', compact('groupedDatasets'));
    }
}