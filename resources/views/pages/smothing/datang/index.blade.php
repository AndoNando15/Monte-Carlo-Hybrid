@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Triple Exponential Smoothing (Holt-Winters) | Datang</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive mt-4">

                    <!-- Tabel Initial Trend -->
                    <h5>Initial Trend</h5>
                    <table class="table table-bordered table-striped" style="font-size: 0.95rem;">
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
                                <tr>
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

                    <!-- Tabel Forecast -->
                    <h5 class="mt-4">LEVEL At (Pemulusan)</h5>
                    <table class="table table-bordered table-striped" id="dataTable" style="font-size: 0.95rem;">
                        <thead class="text-center bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Datang</th>
                                <th>LEVEL At (Pemulusan)</th>
                                <th>TREND Tt</th>
                                <th>SEASONAL St(Musiman)</th>
                                <th>FORECAST</th>
                                <th>ERROR</th>
                                <th>ABSOLUTE ERROR</th>
                                <th>SQUARED ERROR</th>
                                <th>ABSOLUTE % ERROR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datasets_filtered as $index => $data)
                                @php
                                    $isDesember = \Carbon\Carbon::parse($data->tanggal_iso)->month === 12;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $data->tanggal }}</td>
                                    <td>{{ $data->datang }}</td>

                                    <!-- LEVEL At -->
                                    <td>
                                        @if (!$isDesember)
                                            {{ $data->level_at !== null ? number_format($data->level_at, 2) : '-' }}
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
                                            {{ $data->absolute_percentage_error !== null
                                                ? number_format($data->absolute_percentage_error * 100, 2) . '%'
                                                : '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Tabel Akurasi dan APE Desember -->
                    <h5 class="mt-5">Akurasi dan APE Desember</h5>
                    <table class="table table-bordered table-striped" style="font-size: 0.95rem; width: 60%;">
                        <thead class="text-center bg-success text-white">
                            <tr>
                                <th>K</th>
                                <th>Aktual</th>
                                <th>Forecast</th>
                                <th>ERROR</th>
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

                                    $akurasi =
                                        $forecast && $aktual ? min($forecast, $aktual) / max($forecast, $aktual) : 0;
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
                                    <td>{{ number_format($ape, 2) }}</td>
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
                                    {{ $total > 0 ? number_format($sumAPE / $total, 2) : '0.00' }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
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
