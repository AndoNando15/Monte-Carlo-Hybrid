@extends('layouts.base')

@section('content')
    <div class="container-fluid">

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Dataset</h4>
            </div>
            <div class="card-body">
                <button class="btn btn-primary mb-3" type="button" data-toggle="modal" data-target="#createModal">Create
                    Dataset</button>
                <a href="">Import Excel</a>

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
                            @if ($datasets->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="alert alert-warning">
                                            Belum ada data.
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($datasets as $key => $dataset)
                                    <tr>
                                        <td class="text-center">{{ $key + 1 }}</td> <!-- Automatically calculated -->
                                        <td>{{ $dataset->tanggal }}</td> <!-- 20 Mei 2025 -->
                                        <td>{{ $dataset->hari }}</td> <!-- Senin -->
                                        <td>{{ $dataset->datang }}</td>
                                        <td>{{ $dataset->berangkat }}</td>
                                        <td class="text-center">
                                            <!-- Edit Button -->
                                            <button class="btn btn-warning btn-sm" data-toggle="modal"
                                                data-target="#editModal-{{ $dataset->id }}">Edit</button>
                                            <!-- Delete Button -->
                                            <button class="btn btn-danger btn-sm" data-toggle="modal"
                                                data-target="#deleteModal-{{ $dataset->id }}">Delete</button>
                                        </td>

                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
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
    @foreach ($datasets as $dataset)
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

    <!-- Delete Modal -->
    @foreach ($datasets as $dataset)
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

@endsection



@push('css')
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "pageLength": 5,
                "lengthMenu": [5, 10, 25, 50, 100]
            });
        });
    </script>
@endpush
