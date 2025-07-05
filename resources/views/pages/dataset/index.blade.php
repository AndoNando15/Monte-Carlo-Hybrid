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
                <h4 class="m-0 font-weight-bold text-primary">Dataset</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <!-- Left side: Create and Import buttons -->
                    <div>
                        <button class="btn btn-primary mb-3" type="button" data-toggle="modal"
                            data-target="#createModal">Create Dataset</button>
                        <!-- Import Excel Button with Modal trigger -->
                        <button class="btn btn-success mb-3" type="button" data-toggle="modal"
                            data-target="#importModal">Import Excel</button>
                    </div>
                    <!-- Right side: Delete All Data Button -->
                    <div>
                        <button class="btn btn-danger mb-3" type="button" data-toggle="modal"
                            data-target="#deleteAllModal">Delete All Data</button>
                    </div>

                </div>
                <div class="d-flex justify-content-between mb-3">
                    <!-- Month Filter Dropdown -->
                    <form action="{{ route('dataset.index') }}" method="GET" class="d-flex align-items-end ">
                        <div class=" mr-3">
                            <label for="month" class="form-label">Filter by Month</label>
                            <select name="month" class="form-control">
                                <option value="">-- Tampilkan Semua --</option>
                                @foreach ($months as $month)
                                    <option value="{{ $month->month }}"
                                        {{ request('month') == $month->month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($month->month . '-01')->locale('id')->isoFormat('MMMM YYYY') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="">
                            <button type="submit" class="btn btn-info">Filter</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTable">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Tanggal</th>
                                <th>Hari</th>
                                <th>Datang</th>
                                <th>Berangkat</th>
                                <th class="text-center">Aksi</th>
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
                                @foreach ($groupedDatasets as $month => $monthlyDatasets)
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <strong>Bulan:
                                                {{ \Carbon\Carbon::parse($month . '-01')->locale('id')->isoFormat('MMMM YYYY') }}</strong>
                                        </td>
                                    </tr>

                                    @foreach ($monthlyDatasets as $key => $dataset)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td>{{ $dataset->tanggal }}</td> <!-- 20 Mei 2025 -->
                                            <td>{{ $dataset->hari }}</td> <!-- Senin -->
                                            <td>{{ $dataset->datang }}</td>
                                            <td>{{ $dataset->berangkat }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-warning btn-sm" data-toggle="modal"
                                                    data-target="#editModal-{{ $dataset->id }}">Edit</button>
                                                <button class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#deleteModal-{{ $dataset->id }}">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal for Import Excel -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('dataset.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="file">Upload Excel File</label>
                            <input type="file" class="form-control" name="file" required>
                        </div>
                        <button type="submit" class="btn btn-success">Import</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete All Data Modal -->
    <div class="modal fade" id="deleteAllModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete All Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete all datasets?</p>
                </div>
                <div class="modal-footer">
                    <!-- Form to delete all data -->
                    <form action="{{ route('dataset.deleteAll') }}" method="POST">
                        @csrf
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete All</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Dataset</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('dataset.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                        </div>
                        <div class="form-group">
                            <label for="datang">Datang</label>
                            <input type="number" class="form-control" id="datang" name="datang" required>
                        </div>
                        <div class="form-group">
                            <label for="berangkat">Berangkat</label>
                            <input type="number" class="form-control" id="berangkat" name="berangkat" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    @foreach ($groupedDatasets as $monthlyDatasets)
        @foreach ($monthlyDatasets as $dataset)
            <div class="modal fade" id="editModal-{{ $dataset->id }}" tabindex="-1" role="dialog"
                aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Dataset</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('dataset.update', $dataset->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="editTanggal">Tanggal</label>
                                    <input type="date" class="form-control" id="editTanggal" name="tanggal"
                                        value="{{ $dataset->tanggal }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="editDatang">Datang</label>
                                    <input type="number" class="form-control" id="editDatang" name="datang"
                                        value="{{ $dataset->datang }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="editBerangkat">Berangkat</label>
                                    <input type="number" class="form-control" id="editBerangkat" name="berangkat"
                                        value="{{ $dataset->berangkat }}" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    <!-- Delete Modal -->
    @foreach ($groupedDatasets as $monthlyDatasets)
        @foreach ($monthlyDatasets as $dataset)
            <div class="modal fade" id="deleteModal-{{ $dataset->id }}" tabindex="-1" role="dialog"
                aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Delete Dataset</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this dataset?</p>
                        </div>
                        <div class="modal-footer">
                            <form action="{{ route('dataset.destroy', $dataset->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach


@endsection

@push('script')
    <script>
        // Show success toast if there is a success session message
        var toastElement = document.getElementById('successToast');
        var toast = new bootstrap.Toast(toastElement, {
            delay: 3000 // Change the duration here (in milliseconds), 10000ms = 10 seconds
        });
        toast.show();
    </script>
@endpush
