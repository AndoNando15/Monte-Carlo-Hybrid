@extends('layouts.base')
<!-- Import Carbon class at the top of your file -->
@php
    use Carbon\Carbon;
@endphp
@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Triple Exponential Smoothing (Holt-Winters) | Datang</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                    <!-- Tabel Initial Trend -->
                    <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">
                        <h3 class="text-center text-primary py-2" style="font-size: 1.5rem; font-weight: bold;">
                            | Tabel Initial Trend |
                        </h3>
                    </div>
                    <table class="table table-bordered table-striped mt-4" style="font-size: 0.85rem;">
                        <thead class="text-center bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Month 1</th>
                                <th>Month 2</th>
                                <th>M2-M1</th>
                                <th>(M2-M1)/20</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($initialTrendData as $index => $data)
                                <tr class=" text-center">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $data['Month1'] }}</td>
                                    <td>{{ $data['Month2'] }}</td>
                                    <td>{{ $data['M2-M1'] }}</td>
                                    <td>{{ number_format($data['(M2-M1)/20'], 4) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-center">
                        <strong>Rata-rata: {{ number_format($averageInitialTrend, 4) }}</strong>
                    </div>





                </div>
            </div>

        </div>
        <div class="card shadow mb-4">

            <div class="card-body">
                <!-- Tabel Forecast -->
                <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">
                    {{-- ACUAN Prediksi Text --}}
                    <h5 class="text-center text-primary py-2" style="font-size: 1.5rem; font-weight: bold;">
                        | Proses Perhitungan Triple Eksponential Smoothing |
                    </h5>
                </div>
                <table class="table table-bordered table-striped" id="dataTable" style="font-size: 0.85rem;">
                    <thead class="text-center bg-primary text-white">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Datang</th>
                            <th>LEVEL At (Pemulusan)</th>
                            <th>Trend Tt</th>
                            <th>Seasonal St</th>
                            <th>Forecast</th>
                            <th>Error</th>
                            <th>Absolute Error</th>
                            <th>Squared Error</th>
                            <th>Absolute % Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datasets_filtered as $index => $data)
                            @php
                                $isDesember = \Carbon\Carbon::parse($data->tanggal_iso)->month === 12;
                            @endphp
                            <tr class="text-center">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $data->tanggal }}</td>
                                <td>{{ $data->datang }}</td>

                                <!-- LEVEL At -->
                                <td>
                                    @if (!$isDesember)
                                        {{ $data->level_at !== null ? number_format($data->level_at, 4) : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- TREND Tt -->
                                <td>{{ $data->trend_t !== null ? number_format($data->trend_t, 4) : '-' }}</td>

                                <!-- SEASONAL St -->
                                <td>
                                    @if (!$isDesember)
                                        {{ $data->seasonal_st !== null ? number_format($data->seasonal_st, 4) : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- FORECAST -->
                                <td>
                                    {{ $data->forecast !== null
                                        ? ($isDesember
                                            ? number_format($data->forecast, 0)
                                            : number_format($data->forecast, 4))
                                        : '-' }}
                                </td>

                                <!-- ERROR -->
                                <td>
                                    {{ $data->error !== null
                                        ? ($isDesember
                                            ? number_format($data->error, 0)
                                            : number_format($data->error, 4))
                                        : '-' }}
                                </td>

                                <!-- ABSOLUTE ERROR -->
                                <td>
                                    @if (!$isDesember)
                                        {{ $data->absolute_error !== null ? number_format($data->absolute_error, 4) : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- SQUARED ERROR -->
                                <td>
                                    @if (!$isDesember)
                                        {{ $data->squared_error !== null ? number_format($data->squared_error, 4) : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- ABSOLUTE % ERROR -->
                                <td>
                                    @if (!$isDesember)
                                        {{ $data->absolute_percentage_error !== null ? number_format($data->absolute_percentage_error, 4) . '%' : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
        <div class="card shadow mb-4">


            <div class="card-body">
                <!-- Tabel Akurasi dan APE Desember -->
                <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">
                    {{-- ACUAN Prediksi Text --}}
                    @php
                        // Initialize variables for accumulating sum of APE and sum of accuracy
                        $sumAkurasi = 0;
                        $sumAPE = 0;
                        $total = 0;
                    @endphp

                    <!-- Process non-December data -->
                    @foreach ($datasets_filtered as $data)
                        @php
                            $sumAkurasi = 0;
                            $sumAPE = 0;
                            $total = 0;
                        @endphp

                        @foreach ($datasets_filtered as $data)
                            @php
                                $month = \Carbon\Carbon::parse($data->tanggal_iso)->month;

                                // Hanya proses bulan Januari–November
                                if ($month >= 1 && $month <= 11) {
                                    $actual = $data->datang ?? 0;
                                    $forecast = $data->forecast ?? null;
                                    $data->error = $actual - $forecast;
                                    $data->absolute_error = abs($data->error);
                                    $data->squared_error = pow($data->error, 2);

                                    // Pastikan forecast dan actual valid
                                    if ($actual != 0 && ($data->level_at ?? 0) != 0 && ($data->seasonal_st ?? 0) != 0) {
                                        $akurasi = min($forecast, $actual) / max($forecast, $actual);
                                        $ape = abs($data->error - $actual) / $actual;
                                        $sumAkurasi += $akurasi;
                                        $sumAPE += $ape;
                                        $total++;
                                    }
                                }

                            @endphp
                        @endforeach
                    @endforeach

                    <div class="text-center py-2" style="background-color: #ecf7ff; width: 100%;">
                        <h1 class="text-center mb-2 text-primary" style="font-size: 2rem; font-weight: bold;">
                            | Perbandingan ERROR MAPE |
                        </h1>
                        <h5 class="text-center" style="font-size: 1.25rem; color: #555;">
                            {{-- Rata-Rata Akurasi Prediksi:
                            <span class="font-weight-bold" style="color: #008cff;">
                                {{ $total > 0 ? number_format(($sumAkurasi / $total) * 100, 2) . '%' : '0%' }}
                            </span> --}}
                            MAPE:
                            <span class="font-weight-bold" style="color: #f44336;">
                                {{ isset($averageApe) ? number_format(($averageApe * 100) / 100, 2) . '%' : '-' }}
                            </span>
                        </h5>
                    </div>

                </div>
                <table class="table table-bordered table-striped" style="font-size: 0.85rem; width: 100%; margin: 0 auto;">
                    <thead class="text-center bg-primary text-white">
                        <tr>
                            <th>K</th>
                            <th>Aktual</th>
                            <th>Forecast</th>
                            <th>Error</th>
                            <th>Akurasi (%)</th>
                            <th>APE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sumAkurasi = 0;
                            $sumAPE = 0;
                            $total = 0;
                        @endphp
                        @foreach ($desemberDataForLog as $row)
                            @php
                                $aktual = $row['datang'];
                                $forecast = $row['forecast'];
                                $error = $row['error'];

                                $akurasi = $forecast && $aktual ? min($forecast, $aktual) / max($forecast, $aktual) : 0;
                                $ape = 1 - $akurasi;

                                $sumAkurasi += $akurasi;
                                $sumAPE += $ape;
                                $total++;
                            @endphp
                            <tr class="text-center">
                                <td>{{ $row['urutan'] }}</td>
                                <td>{{ number_format($aktual, 0) }}</td>
                                <td>{{ number_format($forecast, 0) }}</td>
                                <td>{{ number_format($error, 0) }}</td>
                                <td>{{ number_format($akurasi * 100, 2) }}%</td>
                                <td>{{ number_format($ape * 100, 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light text-center font-weight-bold">
                        <tr>
                            <td colspan="4">Rata-rata</td>
                            <td>
                                {{ $total > 0 ? number_format(($sumAkurasi / $total) * 100, 2) . '%' : '0%' }}
                            </td>
                            <td>
                                {{ $total > 0 ? number_format(($sumAPE / $total) * 100, 2) . '%' : '0.00' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <script>
            const desemberData = @json($desemberDataForLog);
            console.log("📊 Data TREND bulan Desember (format aman untuk JS):");
            console.table(desemberData);

            const parsedDates = desemberData.map(item => new Date(item.tanggal));
            console.log("🕒 Parsed ISO Dates (Desember):", parsedDates);
        </script>
    @endsection
