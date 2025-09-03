<x-app-layout>
    <x-slot name="pagetitle">Riwayat Penjualan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Riwayat Penjualan</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <form method="GET" action="{{ route('jual.riwayat') }}" class="row g-2 align-items-center justify-content-end">
                        <div class="col-auto">
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $tanggal_awal }}">
                        </div>
                        <div class="col-auto">
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $tanggal_akhir }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-success">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbpenjualan" class="table table-striped table-bordered table-sm" style="width:100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>No Invoice</th>
                                <th>Tanggal</th>
                                <th>Kasir</th>
                                <th>Customer</th>
                                <th class="text-end">Grand Total</th>
                                <th>Metode Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penjualan as $p)
                            <tr>
                                <td>
                                    <a href="{{ url('/penjualan/nota/'.$p->nomor_invoice) }}" target="_blank">
                                        {{ $p->nomor_invoice }}
                                    </a>
                                </td>
                                <td>{{ $p->tanggal }}</td>
                                <td>{{ $p->kasir }}</td>
                                <td>{{ $p->customer }}</td>
                                <td class="text-end">{{ number_format($p->grandtotal,2) }}</td>
                                <td>{{ ucfirst($p->metode_bayar) }}</td>
                                <td>
                                    <a href="{{ url('/penjualan/nota/'.$p->nomor_invoice) }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-printer-fill"></i> Cetak
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="csscustom">
        <style>
            .table td, .table th { font-size: small; }
            .card-body { padding: 0.75rem; }
            #tbpenjualan_wrapper .dt-buttons { margin-bottom: 0.5rem; }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>
        <script>
            $(document).ready(function () {
                $('#tbpenjualan').DataTable({
                    responsive: true,
                    pageLength: 50,
                    lengthMenu: [[25,50,100,-1],[25,50,100,'All']],
                    ordering: false,
                    dom:
                    "<'row mb-2'<'col-md-6 d-flex align-items-center'B><'col-md-6 d-flex justify-content-end'f>>" +
                    "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'i>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-md-6'i><'col-md-6 d-flex justify-content-end'p>>",
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                            className: 'btn btn-success btn-sm',
                            exportOptions: { columns: ':visible' }
                        }
                    ]
                });
            });
        </script>
    </x-slot>
</x-app-layout>
