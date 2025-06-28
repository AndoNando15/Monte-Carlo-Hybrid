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
                                        <td title="Probabilitas = Frekuensi / Total Data">
                                            {{ sprintf('%.4f', $data['probabilitas']) }}
                                        </td> {{-- Probabilitas --}}
                                        <td title="Komulatif = Sum(Probabilitas)">
                                            {{ sprintf('%.4f', $data['komulatif']) }}
                                        </td> {{-- Komulatif --}}
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
                                                <td title="Akurasi = MIN(Prediksi, Data Asli) / MAX(Prediksi, Data Asli)">
                                                    {{ sprintf('%.2f', $acc) }}%
                                                </td>
                                            @endforeach


                                            {{-- APE --}}
                                            @foreach ($comparison['apes'] as $ape)
                                                <td title="APE = (|Prediksi - Data Asli| / Data Asli) * 100">
                                                    {{ sprintf('%.2f', $ape) }}%
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Tabel Prediksi, Data Asli, Selisih, Error, dan Akurasi --}}
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-striped text-center">
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
                                    @foreach ($selectedMonthResults['comparison'] as $index => $comparison)
                                        @php
                                            $prediksi = $selectedMonthResults['best_predictions'][$index];
                                            $dataAsli = $comparison['actual'];
                                            $selisih = abs($prediksi - $dataAsli);

                                            // Cek untuk menghindari pembagian dengan nol
                                            if ($dataAsli != 0) {
                                                $error = ($selisih / $dataAsli) * 100;
                                                $akurasi = 100 - $error;

                                                // Pastikan error tidak lebih besar dari 100%
                                                if ($error > 100) {
                                                    $error = 100;
                                                    $akurasi = 0;
                                                }
                                            } else {
                                                $error = 0;
                                                $akurasi = 0;
                                            }
                                        @endphp
                                        <tr class="text-center">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $prediksi }}</td>
                                            <td>{{ $dataAsli }}</td>
                                            <td>{{ $selisih }}</td>
                                            <td title="Error = (|Prediksi - Data Asli| / Data Asli) * 100">
                                                {{ sprintf('%.2f', $error) }}%
                                            </td>
                                            <td title="Akurasi = MIN(Prediksi, Data Asli) / MAX(Prediksi, Data Asli)">
                                                {{ sprintf('%.2f', $akurasi) }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Ringkasan hasil --}}
                        <div class="mt-4">
                            <h5>MAPE: {{ sprintf('%.2f', $selectedMonthResults['mape']) }}%</h5>
                            <h5>Accuracy: {{ sprintf('%.2f', $selectedMonthResults['accuracy']) }}%</h5>


                        </div>
                    </section>
                @endif

            </div>
        </div>
    </div>
@endsection
