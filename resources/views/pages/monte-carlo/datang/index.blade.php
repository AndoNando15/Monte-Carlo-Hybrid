@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Monte Carlo | Datang</h4>
            </div>
            <div class="card-body">
                <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">
                    {{-- ACUAN Prediksi Text --}}
                    <h3 class="text-center text-primary py-2" style="font-size: 1.5rem; font-weight: bold;">
                        | Acuan Prediksi |
                    </h3>
                </div>
                {{-- Tabel Data Terkelompok --}}
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped" id="dataTable" style="font-size: 0.95rem;">
                        <thead class="text-center bg-primary text-white">
                            <tr>
                                <th class="p-2">No</th>
                                <th class="p-2">Datang</th>
                                <th class="p-2">Frekuensi</th>
                                <th class="p-2">Probabilitas</th>
                                <th class="p-2">Komulatif</th>
                                <th class="p-2">Range</th>
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
                                        </td>
                                        <td title="Komulatif = Sum(Probabilitas)">
                                            {{ sprintf('%.4f', $data['komulatif']) }}
                                        </td>
                                        <td>{{ $data['range'] }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

        <div class="card shadow mb-4">

            <div class="card-body">
                {{-- Dropdown pilih bulan --}}
                <div class="text-center">

                    <form method="GET" class=" gap-3" action="{{ route('monte-carlo.datang.index') }}">
                        <div class=" text-center mb-3" style=" background-color: #ecf7ff; width: 100%;">

                            <h5 class="text-center text-primary py-2">| Pilih Bulan: |</h5>
                        </div>
                        <select id="month" name="month" class="form-control d-inline-block">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach ($monthlyResults as $month => $results)
                                <option value="{{ $month }}" {{ $selectedMonth === $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($month)->format('F Y') }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary mt-2 ">Tampilkan</button>
                    </form>
                </div>

            </div>
        </div>

        {{-- Tombol untuk memilih tabel yang ditampilkan --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="text-center ">
                    <div class=" text-center mb-3" style=" background-color: #ecf7ff; width: 100%;">

                        <h5 class="text-center text-primary py-2">| Proses Monte Carlo |</h5>
                    </div>
                    <button id="toggleRandomNumbers" class="btn btn-outline-primary mb-2">Angka Acak</button>
                    <button id="toggleSimulasi" class="btn btn-outline-primary mb-2">Simulasi</button>
                    <button id="toggleAkurasi" class="btn btn-outline-primary mb-2">Akurasi</button>
                    <button id="toggleApe" class="btn btn-outline-primary mb-2">APE</button>
                    <button id="showAll" class="btn btn-outline-primary mb-2">Tampilkan Semuanya</button>
                </div>
            </div>
        </div>

        {{-- Angka Acak Table --}}
        @if (isset($selectedMonthResults['comparison']))
            <div class="card shadow mb-4" id="angkaAcakTable" style="display:block;">
                <div class="card-body">
                    <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">

                        <h5 class="text-center py-2 text-primary">| Proses Angka Acak |</h5>
                    </div>
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped">
                            <thead class="text-center py-2 bg-primary text-white">
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th colspan="5">Angka Acak</th>
                                </tr>
                                <tr>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <th>Acak-{{ $i }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedMonthResults['comparison'] as $index => $comparison)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        @foreach ($comparison['random_numbers'] as $num)
                                            <td>{{ $num }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Simulasi Table --}}
        @if (isset($selectedMonthResults['comparison']))
            <div class="card shadow mb-4" id="simulasiTable" style="display:none;">
                <div class="card-body">
                    <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">

                        <h5 class="text-center text-primary py-2">| Proses Simulasi |</h5>
                    </div>
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped">
                            <thead class="text-center bg-primary text-white">
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th colspan="5">Simulasi</th>
                                </tr>
                                <tr>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <th>Simulasi-{{ $i }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedMonthResults['comparison'] as $index => $comparison)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        @foreach ($comparison['simulations'] as $sim)
                                            <td>{{ $sim }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Akurasi Table --}}
        @if (isset($selectedMonthResults['comparison']))
            <div class="card shadow mb-4" id="akurasTable" style="display:none;">
                <div class="card-body">
                    <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">

                        <h5 class="text-center text-primary py-2 ">| Proses Akurasi |</h5>
                    </div>
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped">
                            <thead class="text-center bg-primary text-white">
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th colspan="5">Akurasi</th>
                                </tr>
                                <tr>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <th>Akurasi-{{ $i }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedMonthResults['comparison'] as $index => $comparison)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        @foreach ($comparison['accuracies'] as $acc)
                                            <td>{{ sprintf('%.2f', $acc) }}%</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- APE Table --}}
        @if (isset($selectedMonthResults['comparison']))
            <div class="card shadow mb-4" id="apeTable" style="display:none;">
                <div class="card-body">
                    <div class=" text-center" style=" background-color: #ecf7ff; width: 100%;">

                        <h5 class="text-center text-primary py-2">| Proses Absolute Percentage Error (APE) |</h5>
                    </div>
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped">
                            <thead class="text-center bg-primary text-white">
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th colspan="5">APE</th>
                                </tr>
                                <tr>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <th>APE-{{ $i }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Initialize an array to hold the sum of each APE column
                                    $apeSums = [0, 0, 0, 0, 0];
                                    $totalRows = count($selectedMonthResults['comparison']);
                                @endphp

                                @foreach ($selectedMonthResults['comparison'] as $index => $comparison)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        @foreach ($comparison['apes'] as $apeIndex => $ape)
                                            <td>{{ sprintf('%.2f', $ape) }}%</td>
                                            @php
                                                // Add each APE value to the corresponding column sum
                                                $apeSums[$apeIndex] += $ape;
                                            @endphp
                                        @endforeach
                                    </tr>
                                @endforeach

                                {{-- Add a row for MAPE and Accuracy --}}
                                <tr class="text-center">
                                    <td><strong>MAPE</strong></td>
                                    @foreach ($apeSums as $sum)
                                        @php
                                            $averageApe = $sum / $totalRows;
                                            $mape = $averageApe; // MAPE is the average of APE
                                            $accuracy = 100 - $mape; // Accuracy = 100 - MAPE
                                        @endphp
                                        <td>
                                            <strong>{{ sprintf('%.2f', $mape) }}%</strong>
                                        </td>
                                    @endforeach
                                </tr>

                                <tr class="text-center">
                                    <td><strong>Accuracy</strong></td>
                                    @foreach ($apeSums as $sum)
                                        @php
                                            $averageApe = $sum / $totalRows;
                                            $accuracy = 100 - $averageApe; // Calculate accuracy
                                        @endphp
                                        <td>
                                            <strong>{{ sprintf('%.2f', $accuracy) }}%</strong>
                                        </td>
                                    @endforeach
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        <div class="card shadow mb-4">

            <div class="card-body">
                {{-- Always Visible Akurasi Perbandingan APE Section --}}
                <div class=" text-center py-2" style=" background-color: #ecf7ff; width: 100%;">
                    <h1 class="text-center mb-2 text-primary" style="font-size: 2rem; font-weight: bold;">| Akurasi
                        Perbandingan APE |</h1>

                    <h5 class="text-center" style="font-size: 1.25rem; color: #555;">
                        Rata-Rata Akurasi Prediksi:
                        <span class="font-weight-bold" style="color: #008cff;">
                            {{ sprintf('%.2f', $selectedMonthResults['accuracy'] ?? 0) }}%</span> & MAPE:
                        <span class="font-weight-bold" style="color: #f44336;">
                            {{ sprintf('%.2f', $selectedMonthResults['mape'] ?? 0) }}%</span>
                    </h5>
                </div>
                {{-- Tabel Prediksi, Data Asli, Selisih, Error, dan Akurasi --}}
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped text-center"
                        style="font-size: 0.95rem; border: 1px solid #ddd;">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="p-2" style="width: 50px;">No</th>
                                <th class="p-2">Prediksi</th>
                                <th class="p-2">Data Asli</th>
                                <th class="p-2">Selisih</th>
                                <th class="p-2">Error</th>
                                <th class="p-2">Akurasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($selectedMonthResults['comparison'] ?? [] as $index => $comparison)
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
                                <tr>
                                    <td class="p-3">{{ $index + 1 }}</td>
                                    <td class="p-3">{{ $prediksi }}</td>
                                    <td class="p-3">{{ $dataAsli }}</td>
                                    <td class="p-3">{{ $selisih }}</td>
                                    <td class="p-3" title="Error = (|Prediksi - Data Asli| / Data Asli) * 100">
                                        {{ sprintf('%.2f', $error) }}%
                                    </td>
                                    <td class="p-3"
                                        title="Akurasi = MIN(Prediksi, Data Asli) / MAX(Prediksi, Data Asli)">
                                        {{ sprintf('%.2f', $akurasi) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript to toggle table visibility and handle button colors --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to hide all tables
            function hideAllTables() {
                $('#angkaAcakTable').hide();
                $('#simulasiTable').hide();
                $('#akurasTable').hide();
                $('#apeTable').hide();
            }

            // Function to reset button colors
            function resetButtonColors() {
                $(".btn-outline-primary").removeClass("active");
                $(".btn-outline-secondary").removeClass("active");
                $(".btn-outline-info").removeClass("active");
                $(".btn-outline-warning").removeClass("active");
                $(".btn-outline-danger").removeClass("active");
            }

            // Toggle Angka Acak Table
            $("#toggleRandomNumbers").click(function() {
                hideAllTables();
                $("#angkaAcakTable").toggle();
                resetButtonColors();
                $(this).addClass("active");
            });

            // Toggle Simulasi Table
            $("#toggleSimulasi").click(function() {
                hideAllTables();
                $("#simulasiTable").toggle();
                resetButtonColors();
                $(this).addClass("active");
            });

            // Toggle Akurasi Table
            $("#toggleAkurasi").click(function() {
                hideAllTables();
                $("#akurasTable").toggle();
                resetButtonColors();
                $(this).addClass("active");
            });

            // Toggle APE Table
            $("#toggleApe").click(function() {
                hideAllTables();
                $("#apeTable").toggle();
                resetButtonColors();
                $(this).addClass("active");
            });

            // Show all tables
            $("#showAll").click(function() {
                $(".table-responsive").show();
            });
        });
    </script>
@endsection
