@extends('layouts.base')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Monte Carlo | Datang</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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

                    <hr>

                    <h4 class="text-center">Angka Acak yang Digunakan dalam Simulasi</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>Angka Acak 1</th>
                                <th>Angka Acak 2</th>
                                <th>Angka Acak 3</th>
                                <th>Angka Acak 4</th>
                                <th>Angka Acak 5</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($randomNumbers as $index => $randoms)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td>
                                    @foreach ($randoms as $random)
                                        <td>{{ $random }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <hr>

                    <h4 class="text-center">Simulasi Monte Carlo (22 Hari)</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>Simulasi 1</th>
                                <th>Simulasi 2</th>
                                <th>Simulasi 3</th>
                                <th>Simulasi 4</th>
                                <th>Simulasi 5</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($simulasi as $index => $simulation)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td>
                                    @foreach ($simulation as $sim)
                                        <td>{{ $sim }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <hr>

                    <h4 class="text-center">Absolute Percentage Error (APE) per Simulasi</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>APE 1</th>
                                <th>APE 2</th>
                                <th>APE 3</th>
                                <th>APE 4</th>
                                <th>APE 5</th>
                                <th>Rata-rata APE</th>
                                <th>Akurasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($apeResults as $index => $ape)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td>
                                    @foreach ($ape as $a)
                                        <td>{{ $a }}%</td>
                                    @endforeach
                                    <td>{{ $averageApePerSimulation[$index] }}%</td>
                                    <td>{{ $accuracyPerSimulation[$index] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <hr>

                    <h4 class="text-center">MAPE (Mean Absolute Percentage Error)</h4>
                    <p class="text-center">{{ $mape }}%</p>

                    <h4 class="text-center">Akurasi Keseluruhan</h4>
                    <p class="text-center">{{ $accuracy }}%</p>

                </div>
            </div>
        </div>
    </div>
@endsection
