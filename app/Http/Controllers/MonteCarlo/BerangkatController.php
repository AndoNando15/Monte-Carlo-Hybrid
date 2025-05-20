<?php

namespace App\Http\Controllers\MonteCarlo;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use Illuminate\Http\Request;

class BerangkatController extends Controller
{
    public function index()
    {

        $datangData = Dataset::pluck('berangkat');
        $groupedDatasets = collect();

        if (!$datangData->isEmpty()) {
            $frequencies = $datangData->countBy();
            $total = $frequencies->sum();
            $cumulative = 0;

            foreach ($frequencies->sortKeys() as $value => $count) {
                $probability = $count / $total;
                $cumulative += $probability;
                $min = ($cumulative - $probability) * 100;
                $max = ($cumulative * 100);

                $groupedDatasets->push([
                    'berangkat' => $value,
                    'frekuensi' => $count,
                    'probabilitas' => round($probability, 4),
                    'komulatif' => round($cumulative, 4),
                    'range' => floor($min) . ' - ' . ceil($max),
                ]);
            }
        }

        return view('pages.monte-carlo.berangkat.index', compact('groupedDatasets'));
    }
}