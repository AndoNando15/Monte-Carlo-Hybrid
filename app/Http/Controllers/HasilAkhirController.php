<?php
namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\AkurasiMape;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HasilAkhirController extends Controller
{
    public function index(Request $request)
    {
        // Fetch datasets ordered by 'tanggal'
        $datasets = Dataset::orderBy('tanggal')->get();

        // Filter only December data
        $desemberData = $datasets->filter(function ($data) {
            return Carbon::parse($data->tanggal)->month === 12;
        })->values(); // Make sure the indexing starts from 0

        // Fetch AkurasiMape data from the database
        $akurasiMape = AkurasiMape::find(1); // Assuming the relevant data is in row with ID = 1

        // Get forecasts from the session
        $tesForecastsDatang = session('forecast_desember_only', []);
        $tesForecastsBerangkat = session('forecast_berangkat_only', []);
        // Fetch forecasts from the session
        $monteCarloForecastDatang = session('montecarlo_forecast_datang', []); // Correct session key for 'datang'
        $monteCarloForecastBerangkat = session('montecarlo_forecast_berangkat', []);


        // Debugging: Log session values for monitoring
        Log::info('Forecast for Datang: ', session('montecarlo_forecast_desember', []));
        Log::info('Forecast for Berangkat: ', session('montecarlo_forecast_berangkat', []));

        // Combine all data into a final array for the view
        $finalData = $desemberData->map(function ($data, $index) use ($tesForecastsDatang, $tesForecastsBerangkat, $monteCarloForecastDatang, $monteCarloForecastBerangkat) {
            return [
                'id' => $index + 1,
                'tanggal' => $data->tanggal,
                'datang' => $data->datang,
                'berangkat' => $data->berangkat,
                'prediksi_montecarlo_datang' => $monteCarloForecastDatang[$index] ?? 'No Data',
                'prediksi_tes_datang' => $tesForecastsDatang[$index] ?? 'No Data',
                'prediksi_montecarlo_berangkat' => $monteCarloForecastBerangkat[$index] ?? 'No Data',
                'prediksi_tes_berangkat' => $tesForecastsBerangkat[$index] ?? 'No Data'
            ];
        });

        // Pass both final data and AkurasiMape to the view
        return view('pages.hasil-akhir.index', [
            'finalData' => $finalData,
            'akurasiMape' => $akurasiMape,
        ]);
    }
}