@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Monte Carlo | Datang</h4>
            </div>
            <div class="card-body">

                {{-- ACUAN Prediksi Text --}}
                <h3 class="text-center mb-4" style="font-size: 1.5rem; font-weight: bold; color: #4CAF50;">
                    ACUAN Prediksi
                </h3>

                {{-- Tabel Data Terkelompok --}}
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped" id="dataTable" style="font-size: 0.95rem;">
                        <thead class="text-center bg-primary text-white">
                            <tr>
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

                {{-- Lihat Proses Button --}}
                <div class="text-center mt-4">
                    <button id="lihatProses" class="btn btn-outline-success mb-2">Lihat Proses</button>
                </div>

                {{-- Process Buttons --}}
                <div class="text-center mt-4" id="processButtons" style="display:none;">
                    <button id="toggleRandomNumbers" class="btn btn-outline-primary mb-2">Angka Acak</button>
                    <button id="toggleSimulasi" class="btn btn-outline-primary mb-2">Simulasi</button>
                    <button id="toggleAkurasi" class="btn btn-outline-primary mb-2">Akurasi</button>
                    <button id="toggleApe" class="btn btn-outline-primary mb-2">APE</button>
                    <button id="showAll" class="btn btn-outline-primary mb-2">Show All</button>
                </div>

                {{-- Angka Acak Table --}}
                <div class="table-responsive mt-4" id="angkaAcakTable" style="display:none;">
                    <table class="table table-bordered table-striped">
                        <thead class="text-center bg-primary text-white">
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

                {{-- Simulasi Table --}}
                <div class="table-responsive mt-4" id="simulasiTable" style="display:none;">
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

                {{-- Akurasi Table --}}
                <div class="table-responsive mt-4" id="akurasTable" style="display:none;">
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

                {{-- APE Table --}}
                <div class="table-responsive mt-4" id="apeTable" style="display:none;">
                    <table class="table table-bordered table-striped">
                        <thead class="text-center bg-primary text-white">
                            <tr>
                                <th rowspan="2">No</th>
                                <th colspan="5">Absolute Percentage Error (APE)</th>
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
                {{-- Always Visible Akurasi Perbandingan APE Section --}}
                <h1 class="text-center mb-4" style="font-size: 2rem; font-weight: bold; color: #4CAF50;">Akurasi
                    Perbandingan APE</h1>

                <h5 class="text-center mb-5" style="font-size: 1.25rem; color: #555;">
                    Rata-Rata Akurasi Prediksi:
                    <span class="font-weight-bold" style="color: #2196F3;">
                        {{ sprintf('%.2f', $selectedMonthResults['accuracy']) }}%</span> &
                    MAPE:
                    <span class="font-weight-bold" style="color: #f44336;">
                        {{ sprintf('%.2f', $selectedMonthResults['mape']) }}%</span>
                </h5>
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

            // Lihat Proses button to show other buttons
            $("#lihatProses").click(function() {
                $("#processButtons").toggle();
                hideAllTables();
            });

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
