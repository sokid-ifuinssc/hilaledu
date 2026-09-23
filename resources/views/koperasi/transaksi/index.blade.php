@extends('layouts.app')

@section('page-title', 'Transaksi')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Transaksi</h5>
        <a href="#" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Data
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Keterangan</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3" class="text-center">Belum ada data Transaksi.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

