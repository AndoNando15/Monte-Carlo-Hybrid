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
                                        {{ $row['prediksi_montecarlo_datang'] !== 'No Data' ? number_format($row['prediksi_montecarlo_datang'], 0) : '-' }}
                                    </td>
                                    <td>
                                        {{ $row['prediksi_tes_datang'] !== 'No Data' ? number_format($row['prediksi_tes_datang'], 0) : '-' }}
                                    </td>

                                    {{-- Kolom berangkat --}}
                                    <td>{{ $row['id'] }}</td>
                                    <td>{{ $row['berangkat'] }}</td>
                                    <td>
                                        {{ $row['prediksi_montecarlo_berangkat'] !== 'No Data' ? number_format($row['prediksi_montecarlo_berangkat'], 0) : '-' }}
                                    </td>
                                    <td>
                                        {{ $row['prediksi_tes_berangkat'] !== 'No Data' ? number_format($row['prediksi_tes_berangkat'], 0) : '-' }}
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
                                    <!-- Displaying Akurasi and MAPE for different categories -->
                                    <tr>
                                        <td>Monte Carlo - Datang</td>
                                        <td>{{ number_format($akurasiMape->monte_mape_datang ?? 0, 2) }}%</td>
                                        <td>{{ number_format($akurasiMape->monte_akurasi_datang ?? 0, 2) }}%</td>
                                    </tr>
                                    <tr>
                                        <td>Monte Carlo - Berangkat</td>
                                        <td>{{ number_format($akurasiMape->monte_mape_berangkat ?? 0, 2) }}%</td>
                                        <td>{{ number_format($akurasiMape->monte_akurasi_berangkat ?? 0, 2) }}%</td>
                                    </tr>
                                    <tr>
                                        <td>TES - Datang</td>
                                        <td>{{ number_format($akurasiMape->tes_mape_datang ?? 0, 2) }}%</td>
                                        <td>{{ number_format($akurasiMape->tes_akurasi_datang ?? 0, 2) }}%</td>
                                    </tr>
                                    <tr>
                                        <td>TES - Berangkat</td>
                                        <td>{{ number_format($akurasiMape->tes_mape_berangkat ?? 0, 2) }}%</td>
                                        <td>{{ number_format($akurasiMape->tes_akurasi_berangkat ?? 0, 2) }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> --}}

                    {{-- <!-- Final Data Table -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="text-center" style="background-color: #ecf7ff;">
                                <h4 class="text-primary font-weight-bold py-2">| Data Final |</h4>
                            </div>
                            <table class="table table-sm table-bordered table-striped text-center"
                                style="font-size: 0.9rem;">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Datang</th>
                                        <th>Berangkat</th>
                                        <th>Prediksi Monte Carlo Datang</th>
                                        <th>Prediksi TES Datang</th>
                                        <th>Prediksi Monte Carlo Berangkat</th>
                                        <th>Prediksi TES Berangkat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($finalData as $data)
                                        <tr>
                                            <td>{{ $data['id'] }}</td>
                                            <td>{{ \Carbon\Carbon::parse($data['tanggal'])->format('d F Y') }}</td>
                                            <td>{{ number_format($data['datang'], 2) }}</td>
                                            <td>{{ number_format($data['berangkat'], 2) }}</td>
                                            <td>{{ number_format($data['prediksi_montecarlo_datang'] ?? 0, 2) }}</td>
                                            <td>{{ number_format($data['prediksi_tes_datang'] ?? 0, 2) }}</td>
                                            <td>{{ number_format($data['prediksi_montecarlo_berangkat'] ?? 0, 2) }}</td>
                                            <td>{{ number_format($data['prediksi_tes_berangkat'] ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div> --}}




                </div>


            </div>

        </div>
        <div class="card shadow mb-4">

            <div class="card-body">
                <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">
                    <h3 class="text-center text-primary py-2" style="font-size: 1.5rem; font-weight: bold;">
                        | Grafik Hasil Visualisasi |
                    </h3>
                </div>
                <!-- Grafik Kedatangan -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h4 class="m-0 font-weight-bold text-primary">Hasil Visualisasi Kedatangan</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="grafikKedatangan" style="height: 300px;"></canvas>
                    </div>
                </div>

                <!-- Grafik Keberangkatan -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h4 class="m-0 font-weight-bold text-primary">Hasil Visualisasi Keberangkatan</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="grafikKeberangkatan" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const grafikKedatangan = document.getElementById('grafikKedatangan').getContext('2d');
        const grafikKeberangkatan = document.getElementById('grafikKeberangkatan').getContext('2d');

        const dataKedatangan = {
            labels: @json($finalData->pluck('id')), // Menyusun label berdasarkan ID
            datasets: [{
                label: 'Aktual Datang',
                data: @json($finalData->pluck('datang')),
                borderColor: 'blue',
                backgroundColor: 'transparent',
                fill: false,
                pointStyle: 'circle', // Lingkaran untuk Aktual
                pointRadius: 5, // Ukuran titik
            }, {
                label: 'Monte Carlo Datang',
                data: @json($finalData->pluck('prediksi_montecarlo_datang')),
                borderColor: 'orange',
                backgroundColor: 'transparent',
                fill: false,
                pointStyle: 'rect', // Kotak untuk Monte Carlo
                pointRadius: 5, // Ukuran titik
            }, {
                label: 'Prediksi TES Datang',
                data: @json($finalData->pluck('prediksi_tes_datang')),
                borderColor: 'gray',
                backgroundColor: 'transparent',
                fill: false,
                pointStyle: 'triangle', // Segitiga untuk TES
                pointRadius: 5, // Ukuran titik
            }]
        };

        const dataKeberangkatan = {
            labels: @json($finalData->pluck('id')), // Menyusun label berdasarkan ID
            datasets: [{
                label: 'Aktual Berangkat',
                data: @json($finalData->pluck('berangkat')),
                borderColor: 'blue',
                backgroundColor: 'transparent',
                fill: false,
                pointStyle: 'circle', // Lingkaran untuk Aktual
                pointRadius: 5, // Ukuran titik
            }, {
                label: 'Monte Carlo Berangkat',
                data: @json($finalData->pluck('prediksi_montecarlo_berangkat')),
                borderColor: 'orange',
                backgroundColor: 'transparent',
                fill: false,
                pointStyle: 'rect', // Kotak untuk Monte Carlo
                pointRadius: 5, // Ukuran titik
            }, {
                label: 'Prediksi TES Berangkat',
                data: @json($finalData->pluck('prediksi_tes_berangkat')),
                borderColor: 'gray',
                backgroundColor: 'transparent',
                fill: false,
                pointStyle: 'triangle', // Segitiga untuk TES
                pointRadius: 5, // Ukuran titik
            }]
        };

        new Chart(grafikKedatangan, {
            type: 'line',
            data: dataKedatangan,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                height: 300,
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Kedatangan'
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true, // Menampilkan simbol yang sesuai di legenda
                        }
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
            }
        });

        new Chart(grafikKeberangkatan, {
            type: 'line',
            data: dataKeberangkatan,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                height: 300,
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Keberangkatan'
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true, // Menampilkan simbol yang sesuai di legenda
                        }
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
            }
        });
    </script>
@endsection
