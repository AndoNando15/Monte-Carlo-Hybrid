@extends('layouts.base')

@section('content')
    <div class="container-fluid">

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Hasil Akhir</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">
                        <h3 class="text-center text-primary py-2" style="font-size: 1.5rem; font-weight: bold;">
                            | HASIL AKHIR |
                        </h3>
                    </div>
                    <table class="table table-bordered table-striped"
                        style="font-size: 0.85rem; width: 100%; margin: 0 auto;">
                        <thead class="text-center bg-primary text-white">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Aktual Datang</th>
                                <th>Prediksi Monte Carlo Datang</th>
                                <th>Prediksi TES Datang</th>
                                <th>No</th>
                                <th>Aktual Berangkat</th>
                                <th>Prediksi Monte Carlo Berangkat</th>
                                <th>Prediksi TES Berangkat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($finalData as $row)
                                <tr class="text-center">
                                    <td>{{ $row['id'] }}</td>
                                    <td>{{ $row['datang'] }}</td>
                                    <td>
                                        {{ $row['prediksi_montecarlo_datang'] !== null ? number_format($row['prediksi_montecarlo_datang'], 0) : '-' }}
                                    </td>
                                    <td>
                                        {{ $row['prediksi_tes_datang'] !== null ? number_format($row['prediksi_tes_datang'], 0) : '-' }}
                                    </td>

                                    {{-- Kolom berangkat --}}
                                    <td>{{ $row['id'] }}</td>
                                    <td>{{ $row['berangkat'] }}</td>
                                    <td>
                                        {{ $row['prediksi_montecarlo_berangkat'] !== null ? number_format($row['prediksi_montecarlo_berangkat'], 0) : '-' }}
                                    </td>
                                    <td>
                                        {{ $row['prediksi_tes_berangkat'] !== null ? number_format($row['prediksi_tes_berangkat'], 0) : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="text-center" style="background-color: #ecf7ff;">
                                <h4 class="text-primary font-weight-bold py-2">| Rekap Akurasi dan MAPE |</h4>
                            </div>
                            <table class="table table-sm table-bordered table-striped text-center"
                                style="font-size: 0.9rem;">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Kategori</th>
                                        <th>MAPE (%)</th>
                                        <th>Akurasi (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rekapAkurasi as $rekap)
                                        <tr>
                                            <td>{{ $rekap['kategori'] }}</td>
                                            <td>{{ number_format($rekap['mape'], 2) }}%</td>
                                            <td>{{ number_format($rekap['akurasi'], 2) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div> --}}

                </div>
            </div>
        </div>

    </div>
@endsection
