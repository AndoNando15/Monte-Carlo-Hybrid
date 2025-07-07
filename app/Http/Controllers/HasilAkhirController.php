<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HasilAkhirController extends Controller
{
    public function index(Request $request)
    {
        $datasets = Dataset::orderBy('tanggal')->get();

        // Filter hanya data bulan Desember
        $desemberData = $datasets->filter(function ($data) {
            return Carbon::parse($data->tanggal)->month === 12;
        })->values(); // penting agar indexing mulai dari 0

        // Ambil forecast TES dari session jika tersedia
        $tesForecastsDatang = session('forecast_desember_only', []);
        $tesForecastsBerangkat = session('forecast_berangkat_only', []);
        $monteCarloForecastDatang = session('montecarlo_forecast_desember', []);
        $monteCarloForecastBerangkat = session('montecarlo_forecast_berangkat', []);

        // Gabungkan semua ke satu array yang bisa diproses di view
        $finalData = $desemberData->map(function ($data, $index) use ($tesForecastsDatang, $tesForecastsBerangkat, $monteCarloForecastDatang, $monteCarloForecastBerangkat) {
            return [
                'id' => $index + 1,
                'tanggal' => $data->tanggal,
                'datang' => $data->datang,
                'berangkat' => $data->berangkat,
                'prediksi_montecarlo_datang' => $monteCarloForecastDatang[$index] ?? null,
                'prediksi_tes_datang' => $tesForecastsDatang[$index] ?? null,
                'prediksi_montecarlo_berangkat' => $monteCarloForecastBerangkat[$index] ?? null,
                'prediksi_tes_berangkat' => $tesForecastsBerangkat[$index] ?? null
            ];
        });


        return view('pages.hasil-akhir.index', [
            'finalData' => $finalData
        ]);
    }
}