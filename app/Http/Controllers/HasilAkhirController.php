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

        // Inisialisasi fungsi bantu
        function calculateMape($actual, $forecast)
        {
            $errors = [];
            foreach ($actual as $i => $act) {
                $pred = $forecast[$i] ?? null;
                if ($act != 0 && $pred !== null) {
                    $ape = abs($act - $pred) / $act;
                    $errors[] = $ape;
                }
            }
            return count($errors) > 0 ? array_sum($errors) / count($errors) * 100 : 0;
        }

        // Hitung MAPE dan Akurasi
        $actualDatang = $desemberData->pluck('datang')->toArray();
        $actualBerangkat = $desemberData->pluck('berangkat')->toArray();

        $rekapAkurasi = [
            [
                'kategori' => 'Monte Carlo - Datang',
                'mape' => calculateMape($actualDatang, $monteCarloForecastDatang),
            ],
            [
                'kategori' => 'Monte Carlo - Berangkat',
                'mape' => calculateMape($actualBerangkat, $monteCarloForecastBerangkat),
            ],
            [
                'kategori' => 'TES - Datang',
                'mape' => calculateMape($actualDatang, $tesForecastsDatang),
            ],
            [
                'kategori' => 'TES - Berangkat',
                'mape' => calculateMape($actualBerangkat, $tesForecastsBerangkat),
            ],
        ];

        // Tambahkan akurasi
        foreach ($rekapAkurasi as &$row) {
            $row['akurasi'] = 100 - $row['mape'];
        }

        return view('pages.hasil-akhir.index', [
            'finalData' => $finalData,
            'rekapAkurasi' => $rekapAkurasi,
        ]);
    }
    function calculateAkurasiVersiBlade($actuals, $forecasts)
    {
        $akurasiList = [];
        foreach ($actuals as $i => $act) {
            $f = $forecasts[$i] ?? null;
            if ($act != null && $f != null && ($act > 0 || $f > 0)) {
                $akurasi = min($act, $f) / max($act, $f);
                $akurasiList[] = $akurasi;
            }
        }
        return count($akurasiList) > 0 ? array_sum($akurasiList) / count($akurasiList) * 100 : 0;
    }

}