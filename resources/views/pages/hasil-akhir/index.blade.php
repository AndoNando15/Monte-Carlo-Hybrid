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
                    <table class="table table-bordered">
                        <thead>
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

                </div>
            </div>
        </div>

    </div>
@endsection
