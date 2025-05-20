@extends('layouts.base')

@section('content')
    <!-- Toast message for success -->
    <!-- Toast message for success -->
    @if (session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999; right: 0;">
            <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="container-fluid">

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Monte Carlo | Datang</h4>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTable">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Datang</th> // jumlah datang dari 0 sampai max
                                <th>Frekuensi</th>
                                <th>Probabilitas</th>
                                <th>Komulatif</th>
                                <th>Range</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($groupedDatasets->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="alert alert-warning">
                                            Belum ada data.
                                        </div>
                                    </td>
                                </tr>
                            @else
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
