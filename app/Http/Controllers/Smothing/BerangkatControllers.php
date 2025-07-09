<?php

namespace App\Http\Controllers\Smothing;

use App\Models\AkurasiMape;
use App\Models\Dataset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BerangkatControllers extends Controller
{
    public function index()
    {
        $datasets = Dataset::orderBy('tanggal')->get();
        $maxberangkatValue = $datasets->max('berangkat');

        $datasets_filtered = $datasets
            ->groupBy(function ($data) {
                return Carbon::parse($data->tanggal)->format('Y-m');
            })
            ->flatMap(function ($group) {
                return $group->sortBy('tanggal');  // Remove 20 data limit
            })
            ->sortBy('tanggal')
            ->values();

        $first_20_data = $datasets_filtered->take(20);
        $average = $first_20_data->avg('berangkat');

        $firstMonth = Carbon::parse($datasets->first()->tanggal)->month;
        $secondMonth = $firstMonth + 1;

        $datasets_initial_trend = $datasets->filter(function ($data) use ($firstMonth, $secondMonth) {
            $month = Carbon::parse($data->tanggal)->month;
            return $month == $firstMonth || $month == $secondMonth;
        });

        $datasets_first_month = $datasets_initial_trend->filter(function ($data) use ($firstMonth) {
            return Carbon::parse($data->tanggal)->month == $firstMonth;
        })->values();

        $datasets_second_month = $datasets_initial_trend->filter(function ($data) use ($secondMonth) {
            return Carbon::parse($data->tanggal)->month == $secondMonth;
        })->values();

        $initialTrendData = [];
        $pairCount = min($datasets_first_month->count(), $datasets_second_month->count());

        for ($i = 0; $i < $pairCount; $i++) {
            $m1 = $datasets_first_month[$i]->berangkat;
            $m2 = $datasets_second_month[$i]->berangkat;

            $initialTrendData[] = [
                'Month1' => $m1,
                'Month2' => $m2,
                'M2-M1' => $m2 - $m1,
                '(M2-M1)/20' => ($m2 - $m1) / 20,
            ];
        }

        $averageInitialTrend = collect($initialTrendData)->avg(function ($item) {
            return $item['(M2-M1)/20'];
        });

        // === LOOP 1: JANUARI–NOVEMBER
        foreach ($datasets_filtered as $index => $data) {
            $carbonDate = Carbon::parse($data->tanggal);
            $month = $carbonDate->month;
            $dateString = $carbonDate->format('Y-m-d');
            $previousData = $datasets_filtered[$index - 1] ?? null;

            if ($month == 12)
                continue;

            if ($dateString === '2023-01-28') {
                $data->trend_t = $averageInitialTrend;
            }

            if ($index < 20) {
                // For the first 20 records, use the average for level_at
                $data->level_at = $average;
            } else {
                $alpha = 0.14527154135641;  // Smoothing factor for level calculation
                $levelPrev = $previousData->level_at ?? 0;  // Previous record's level_at
                $trendPrev = $previousData->trend_t ?? 0;  // Previous record's trend_t


                // Cari seasonal dari index ke-(t-p)
                $correspondingIndex = $index - 20;
                // Use the seasonal index from the previous record (index-1) for the calculation
                $seasonalFromFixed = $datasets_filtered[$correspondingIndex]->seasonal_st ?? 0;  // Get seasonal from previous record

                // Ensure the seasonal index is valid (not 0) before performing the calculation
                if ($seasonalFromFixed != 0) {
                    // Calculate level_at using the seasonal index from the previous record
                    $data->level_at = $alpha * ($data->berangkat / $seasonalFromFixed) + (1 - $alpha) * ($levelPrev + $trendPrev);
                } else {
                    // If seasonal index is 0, set level_at to 0
                    $data->level_at = 0;
                }
            }

            if ($index > 19 && $month >= 2 && $month <= 11) {
                $beta = 0.05;
                $levelNow = $data->level_at ?? 0;
                $levelPrev = $previousData->level_at ?? 0;
                $trendPrev = $previousData->trend_t ?? 0;

                $data->trend_t = $beta * ($levelNow - $levelPrev) + (1 - $beta) * $trendPrev;
            }

            if ($index < 20) {
                $data->seasonal_st = ($data->berangkat > 0 && $data->level_at != 0)
                    ? $data->berangkat / $data->level_at
                    : 0;
            } else {
                $gamma = 0.1;

                // Cari seasonal dari index ke-(t-p)
                $correspondingIndex = $index - 20;
                $correspondingSeasonal = $datasets_filtered[$correspondingIndex]->seasonal_st ?? 0;

                $levelAtNow = $data->level_at ?? 0;
                $berangkatNow = $data->berangkat ?? 0;

                $data->seasonal_st = ($levelAtNow != 0)
                    ? $gamma * ($berangkatNow / $levelAtNow) + (1 - $gamma) * $correspondingSeasonal
                    : 0;
            }

            if ($index >= 20) {
                $seasonal_base = $datasets_filtered->values();  // Take all data, not just the first 20
                $previousData = $datasets_filtered[$index - 1] ?? null;

                $levelPrev = $previousData->level_at ?? 0;
                $trendPrev = $previousData->trend_t ?? 0;
                // Cari seasonal dari index ke-(t-p)
                $correspondingIndex = $index - 20;
                // Use the seasonal index from the previous record (index-1) for the calculation
                $seasonalFromFixed = $datasets_filtered[$correspondingIndex]->seasonal_st ?? 0;  // Get seasonal from previous record

                if ($levelPrev == 0 || $seasonalFromFixed == 0) {
                    $data->forecast = 0;
                } else {
                    $data->forecast = ($levelPrev + $trendPrev) * $seasonalFromFixed;

                }
            }

            $actual = $data->berangkat ?? 0;
            $forecast = $data->forecast ?? null;

            if (!is_null($forecast)) {
                $data->error = $actual - $forecast;
                $data->absolute_error = abs($data->error);
                $data->squared_error = pow($data->error, 2);

                if ($actual != 0 && ($data->level_at ?? 0) != 0 && ($data->seasonal_st ?? 0) != 0) {
                    $data->absolute_percentage_error = abs($data->error - $actual) / $actual;
                } else {
                    $data->absolute_percentage_error = 0;
                }
            }

            $data->tanggal_iso = $carbonDate->format('Y-m-d');
        }

        // === Ambil nilai level dan trend terakhir November
        $novemberLastData = $datasets_filtered->filter(fn($d) => Carbon::parse($d->tanggal)->month === 11)->last();
        $levelNovemberLast = $novemberLastData->level_at ?? 0;
        $trendNovemberLast = (float) $novemberLastData->trend_t ?? 0;
        $seasonal_november = $datasets_filtered
            ->filter(fn($d) => Carbon::parse($d->tanggal)->month === 11)
            ->values()
            ->take(20);

        // === LOOP 2: DESEMBER (forecast + error, hide columns)
        $desemberUrutan = 0;
        foreach ($datasets_filtered as $index => $data) {
            $carbonDate = Carbon::parse($data->tanggal);
            if ($carbonDate->month !== 12)
                continue;

            // Kalkulasi forecast untuk Desember
            $desemberUrutan++;
            $seasonalIndex = ($desemberUrutan - 1) % $seasonal_november->count();
            $seasonal = $seasonal_november[$seasonalIndex]->seasonal_st ?? 1;

            $data->trend_t = $trendNovemberLast * $desemberUrutan;
            $forecast = ($levelNovemberLast + $desemberUrutan * $trendNovemberLast) * $seasonal;

            // Ensure forecast is within bounds (between 0 and 13)
            if ($forecast < 0) {
                $forecast = 0; // Set forecast to 0 if it's less than 0
            } elseif ($forecast > 13) {
                $forecast = 13; // Cap forecast at 13 if it's greater than 13
            }
            $data->forecast = $forecast;
            $data->level_at = null; // Hidden in view
            $data->seasonal_st = null; // Hidden in view

            // Calculate error only 'error'
            $actual = $data->berangkat ?? 0;
            if (!is_null($data->forecast) && $actual != 0) {
                $data->error = $data->forecast - $actual;
            }

            // Remove other columns in view
            $data->absolute_error = null;
            $data->squared_error = null;
            $data->absolute_percentage_error = null;

            $data->tanggal_iso = $carbonDate->format('Y-m-d');
        }

        // === Format output for Blade
        foreach ($datasets_filtered as $data) {
            $carbon = Carbon::parse($data->tanggal)->locale('id');
            $data->tanggal = $carbon->isoFormat('D MMMM YYYY');
            $data->hari = $carbon->isoFormat('dddd');
        }

        $desemberDataForLog = collect($datasets_filtered)
            ->filter(fn($d) => Carbon::parse($d->tanggal_iso)->month === 12)
            ->values()
            ->map(function ($d, $i) {
                return [
                    'urutan' => $i + 1,
                    'tanggal' => $d->tanggal_iso,
                    'berangkat' => $d->berangkat,
                    'trend_t' => $d->trend_t,
                    'forecast' => $d->forecast,
                    'error' => $d->error,
                ];
            });

        $averageLevelAt = $datasets_filtered->take(20)->avg('level_at');
        // Create pure forecast array for December (only numbers)
        $onlyForecastBerangkat = collect($datasets_filtered)
            ->filter(fn($d) => Carbon::parse($d->tanggal_iso)->month === 12)
            ->values()
            ->pluck('forecast')
            ->map(fn($v) => round($v)) // optional rounding
            ->toArray();

        // Save to session
        session(['forecast_berangkat_only' => $onlyForecastBerangkat]); // Forecast untuk berangkat
        // Calculate Akurasi and MAPE for December
        list($sumAkurasiBerangkat, $sumAPEBerangkat, $total) = $this->calculateAkurasiAndAPEForBerangkat($datasets_filtered);

        // Calculate the final values
        $tesAkurasiBerangkat = $sumAkurasiBerangkat / $total * 100;
        $tesMapeBerangkat = $sumAPEBerangkat / $total * 100;

        // Check if there's already a record with id = 1, and update or create accordingly
        $akurasiMape = AkurasiMape::find(1);

        if ($akurasiMape) {
            // If it exists, update it
            $akurasiMape->update([
                'tes_akurasi_berangkat' => $tesAkurasiBerangkat,
                'tes_mape_berangkat' => $tesMapeBerangkat,
            ]);
        } else {
            // Otherwise, create a new one
            AkurasiMape::create([
                'tes_akurasi_berangkat' => $tesAkurasiBerangkat,
                'tes_mape_berangkat' => $tesMapeBerangkat,
            ]);
        }
        $filteredApe = $datasets_filtered->filter(function ($d) {
            $month = Carbon::parse($d->tanggal_iso)->month ?? null;
            return $month !== 12 && isset($d->absolute_percentage_error) && $d->absolute_percentage_error !== null;
        });
        $averageApe = $filteredApe->avg('absolute_percentage_error');

        // Return the view with the necessary data
        return view('pages.smothing.berangkat.index', compact(
            'datasets_filtered',
            'initialTrendData',
            'averageInitialTrend',
            'averageLevelAt',
            'desemberDataForLog',
            'tesAkurasiBerangkat',
            'tesMapeBerangkat',
            'averageApe' // tambahkan ini

        ));
    }

    private function calculateAkurasiAndAPEForBerangkat($datasets_filtered)
    {
        $sumAkurasi = 0;
        $sumAPE = 0;
        $total = 0;

        foreach ($datasets_filtered as $row) {
            $actual = $row->berangkat;  // Use berangkat
            $forecast = $row->forecast;

            $akurasi = $forecast && $actual ? min($forecast, $actual) / max($forecast, $actual) : 0;
            $ape = 1 - $akurasi;

            $sumAkurasi += $akurasi;
            $sumAPE += $ape;
            $total++;
        }

        return [$sumAkurasi, $sumAPE, $total];
    }
}