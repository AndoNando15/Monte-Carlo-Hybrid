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
        $tesForecasts = session('forecast_desember_only', []);

        // Gabungkan semua ke satu array yang bisa diproses di view
        $finalData = $desemberData->map(function ($data, $index) use ($tesForecasts) {
            return [
                'id' => $index + 1,
                'tanggal' => $data->tanggal,
                'datang' => $data->datang,
                'berangkat' => $data->berangkat,
                'prediksi_montecarlo_datang' => $data->datang * 1.1, // contoh dummy
                'prediksi_montecarlo_berangkat' => $data->berangkat * 1.1, // contoh dummy
                'prediksi_tes_datang' => $tesForecasts[$index] ?? null,
                'prediksi_tes_berangkat' => null // tambahkan jika ada nanti
            ];
        });

        return view('pages.hasil-akhir.index', [
            'finalData' => $finalData
        ]);
    }
}