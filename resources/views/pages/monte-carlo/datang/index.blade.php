@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Monte Carlo | Datang</h4>
            </div>
            <div class="card-body">



                <!-- Grouped Data Table (If no month is selected) -->
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped" id="dataTable">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>Datang</th>
                                <th>Frekuensi</th>
                                <th>Probabilitas</th>
                                <th>Komulatif</th>
                                <th>Range</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($groupedDatasets->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Data belum tersedia.
                                    </td>
                                </tr>
                            @else
                                @foreach ($groupedDatasets as $index => $data)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $data['datang'] }}</td>
                                        <td>{{ $data['frekuensi'] }}</td>
                                        <td>{{ $data['probabilitas'] }}</td>
                                        <td>{{ $data['komulatif'] }}</td>
                                        <td>{{ $data['range'] }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <!-- Dropdown for selecting month (Centered at the top) -->
                <div class="text-center mt-4">
                    <form method="GET" action="{{ route('monte-carlo.datang.index') }}">
                        <label for="month" class="font-weight-bold">Pilih Bulan:</label>
                        <select id="month" name="month" class="form-control d-inline-block w-auto">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach ($monthlyResults as $month => $results)
                                <option value="{{ $month }}" {{ $selectedMonth === $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($month)->format('F Y') }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary mt-2">Tampilkan</button>
                    </form>
                </div>
                <!-- Only Display Results for the Selected Month if a Month is Selected -->
                @if ($selectedMonth && !empty($selectedMonthResults))
                    <!-- Section for Results of the Selected Month -->
                    <section class="mt-5">
                        <h5 class="mt-4">Hasil Simulasi untuk Bulan:
                            {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</h5>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Prediksi</th>
                                        <th>Data Asli</th>
                                        <th>Selisih</th>
                                        <th>Error</th>
                                        <th>Akurasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($selectedMonthResults['comparison']))
                                        @foreach ($selectedMonthResults['comparison'] as $index => $comparisonGroup)
                                            @foreach ($comparisonGroup as $comparison)
                                                <tr class="text-center">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $comparison['predicted'] ?? 'Data tidak ada' }}</td>
                                                    <td>{{ $comparison['actual'] ?? 'Data tidak ada' }}</td>
                                                    <td>{{ $comparison['difference'] ?? 'Data tidak ada' }}</td>
                                                    <td>{{ sprintf('%.2f', $comparison['error']) }}%</td>
                                                    <td>{{ sprintf('%.2f', $comparison['accuracy']) }}%</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                Data tidak tersedia untuk bulan ini.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- Display MAPE and Accuracy for the Selected Month -->
                        <div class="mt-4">
                            <h5>MAPE: {{ sprintf('%.2f', $selectedMonthResults['mape']) }}%</h5>
                            <h5>Akurasi: {{ sprintf('%.2f', $selectedMonthResults['accuracy']) }}%</h5>
                        </div>
                    </section>
                @elseif ($selectedMonth)
                    <p class="text-center text-muted">Tidak ada data untuk bulan ini.</p>
                @endif

            </div>
        </div>
    </div>

    <script>
        // Mengirim data dari PHP ke JavaScript
        const monthlyResults = @json($monthlyResults); // Mengirim data $monthlyResults ke JavaScript
        const selectedMonth = @json($selectedMonth); // Mengirim data bulan yang dipilih ke JavaScript
        const groupedDatasets = @json($groupedDatasets); // Mengirim data grouped datasets ke JavaScript

        // Cek apakah ada data untuk bulan yang dipilih
        if (selectedMonth && monthlyResults[selectedMonth]) {
            const selectedResults = monthlyResults[selectedMonth];

            console.log(`Hasil Simulasi untuk Bulan: ${selectedMonth}`);
            console.log('Simulasi:', selectedResults.simulasi);
            console.log('MAPE:', selectedResults.mape);
            console.log('Akurasi:', selectedResults.accuracy);
            console.log('Perbandingan:', selectedResults.comparison);
        } else {
            console.log('Data bulan yang dipilih tidak tersedia.');
        }

        // Cek apakah ada grouped data untuk datang
        console.log('Grouped Datasets:', groupedDatasets);
    </script>

@endsection
