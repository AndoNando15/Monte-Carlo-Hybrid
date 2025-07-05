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
                                <th>Month 1</th>
                                <th>Month 2</th>
                                <th>M2-M1</th>
                                <th>(M2-M1)/20</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($initialTrendData as $data)
                                <tr>
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

                    <!-- Tabel LEVEL At -->
                    <h5 class="mt-4">LEVEL At (Pemulusan)</h5>
                    <table class="table table-bordered table-striped" id="dataTable" style="font-size: 0.95rem;">
                        <thead class="text-center bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Datang</th>
                                <th>LEVEL At (Pemulusan)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datasets_filtered as $index => $data)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $data->tanggal }}</td>
                                    <td>{{ $data->datang }}</td>
                                    <td>
                                        @if ($data->level_at)
                                            {{ number_format($data->level_at, 2) }}
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
        </div>
    </div>
@endsection
