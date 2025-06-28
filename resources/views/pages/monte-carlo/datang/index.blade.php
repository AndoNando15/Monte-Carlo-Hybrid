@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Monte Carlo | Datang</h4>
            </div>
            <div class="card-body">

                {{-- Tabel Data Terkelompok --}}
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

                {{-- Dropdown pilih bulan --}}
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

                {{-- Tabel Simulasi jika bulan dipilih --}}
                @if ($selectedMonth && !empty($selectedMonthResults))
                    <section class="mt-5">
                        <h5 class="mt-4">Hasil Simulasi untuk Bulan:
                            {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</h5>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-striped">
                                <thead class="text-center">
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th colspan="5">Angka Acak</th>
                                        <th colspan="5">Simulasi</th>
                                        <th colspan="5">Akurasi</th>
                                        <th colspan="5">Absolute Percentage Error</th>
                                        <th rowspan="2">Prediksi (Akurasi Tertinggi)</th>
                                        <th rowspan="2">Akurasi Prediksi</th>
                                        <th rowspan="2">Data Asli</th>
                                    </tr>
                                    <tr>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <th>Acak-{{ $i }}</th>
                                        @endfor
                                        @for ($i = 1; $i <= 5; $i++)
                                            <th>Simulasi-{{ $i }}</th>
                                        @endfor
                                        @for ($i = 1; $i <= 5; $i++)
                                            <th>Akurasi-{{ $i }}</th>
                                        @endfor
                                        @for ($i = 1; $i <= 5; $i++)
                                            <th>APE-{{ $i }}</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedMonthResults['comparison'] as $index => $comparison)
                                        <tr class="text-center">
                                            <td>{{ $index + 1 }}</td>

                                            {{-- Angka Acak --}}
                                            @foreach ($comparison['random_numbers'] as $num)
                                                <td>{{ $num }}</td>
                                            @endforeach

                                            {{-- Simulasi --}}
                                            @foreach ($comparison['simulations'] as $sim)
                                                <td>{{ $sim }}</td>
                                            @endforeach

                                            {{-- Akurasi --}}
                                            @foreach ($comparison['accuracies'] as $acc)
                                                <td>{{ sprintf('%.2f', $acc) }}%</td>
                                            @endforeach

                                            {{-- APE --}}
                                            @foreach ($comparison['apes'] as $ape)
                                                <td>{{ sprintf('%.2f', $ape) }}%</td>
                                            @endforeach

                                            {{-- Prediksi Terbaik --}}
                                            <td>{{ $selectedMonthResults['best_predictions'][$index] }}</td>

                                            {{-- Akurasi prediksi terbaik --}}
                                            <td>
                                                {{ sprintf('%.2f', $comparison['accuracies'][$selectedMonthResults['best_simulation_index']]) }}%
                                            </td>

                                            {{-- Data Asli --}}
                                            <td>{{ $comparison['actual'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Ringkasan hasil --}}
                        <div class="mt-4">
                            <h5>MAPE:
                                {{ sprintf('%.2f', $selectedMonthResults['mape']) }}%
                            </h5>
                            <h5>Akurasi Rata-rata Kolom Prediksi Terbaik:
                                {{ sprintf('%.2f', $selectedMonthResults['best_simulation_avg_accuracy']) }}%
                            </h5>
                        </div>
                    </section>
                @endif

            </div>
        </div>
    </div>

    <script>
        const monthlyResults = @json($monthlyResults);
        const selectedMonth = @json($selectedMonth);
        const groupedDatasets = @json($groupedDatasets);

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

        console.log('Grouped Datasets:', groupedDatasets);
    </script>
@endsection
