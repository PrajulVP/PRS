@extends('layouts.admin')

@section('page-body')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3 class="fw-800 text-uppercase" style="letter-spacing: 1px;">Database Backups</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">System</li>
                    <li class="breadcrumb-item active">Database Backups</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-800 text-uppercase" style="font-size: 0.9rem; letter-spacing: 1px; color: var(--med-primary);">
                        <i class="fa fa-database me-2"></i>Backup History
                    </h5>
                    <div>
                        <form action="{{ route('admin.backups.create') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Generate a new full database backup now?')">
                                <i class="fa fa-plus me-1"></i>Create New Backup
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="display table table-striped table-hover" id="backups-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>File Name</th>
                                    <th>File Size</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $index => $backup)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $backup['name'] }}</strong></td>
                                        <td>{{ $backup['size'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($backup['date'])->format('d M Y, h:i A') }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('admin.backups.download', $backup['name']) }}" class="btn btn-success btn-sm" title="Download">
                                                    <i class="fa fa-download"></i> Download
                                                </a>
                                                <form action="{{ route('admin.backups.destroy', $backup['name']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this backup file?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No database backups found. Click "Create New Backup" to generate one.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#backups-table').DataTable({
            "pageLength": 10,
            "ordering": false // Already sorted by controller
        });
    });
</script>
@endsection
