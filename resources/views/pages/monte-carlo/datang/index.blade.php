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

                <!-- Display Results for the Selected Month (Only if a month is selected) -->
                @if ($selectedMonth && !empty($selectedMonthResults))
                    <h5 class="mt-4">Hasil Simulasi untuk Bulan:
                        {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</h5>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Simulasi Ke-1</th>
                                    <th>Simulasi Ke-2</th>
                                    <th>Simulasi Ke-3</th>
                                    <th>Simulasi Ke-4</th>
                                    <th>Simulasi Ke-5</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedMonthResults['simulasi'] as $index => $simulation)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        @foreach ($simulation as $sim)
                                            <td>{{ $sim }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Display MAPE and Accuracy for the Selected Month -->
                    <div class="mt-4">
                        <h5>MAPE: {{ $selectedMonthResults['mape'] }}%</h5>
                        <h5>Akurasi: {{ $selectedMonthResults['accuracy'] }}%</h5>
                    </div>
                @elseif ($selectedMonth)
                    <p class="text-center text-muted">Tidak ada data untuk bulan ini.</p>
                @endif

                <!-- Dropdown for selecting month (Centered at the bottom) -->
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

            </div>
        </div>
    </div>
@endsection
