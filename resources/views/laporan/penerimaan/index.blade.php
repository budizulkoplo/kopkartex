@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Laporan Penerimaan Barang</h4>
    <form method="GET" class="row mb-3">
        <div class="col-md-3">
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('laporan.penerimaan.excel', request()->all()) }}" class="btn btn-success">Excel</a>
            <a href="{{ route('laporan.penerimaan.pdf', request()->all()) }}" class="btn btn-danger">PDF</a>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Invoice</th>
                <th>Supplier</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                @foreach($row->details as $detail)
                <tr>
                    <td>{{ $row->tgl_penerimaan }}</td>
                    <td>{{ $row->nomor_invoice }}</td>
                    <td>{{ $row->nama_supplier }}</td>
                    <td>{{ $detail->barang->kode_barang }}</td>
                    <td>{{ $detail->barang->nama_barang }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>{{ $row->note }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endsection
