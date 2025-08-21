<x-app-layout>
    <x-slot name="pagetitle">Laporan Stok Opname</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Stok Opname</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <input type="month" id="bulan" class="form-control form-control-sm w-auto"
                               value="{{ $bulan }}" onchange="reloadTable()" />

                        <select id="unit" class="form-select form-select-sm w-auto" onchange="reloadTable()">
                            <option value="all">Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbstokopname" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Unit</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-end">Stok Sistem</th>
                                <th class="text-end">Stok Fisik</th>
                                <th class="text-end">Selisih</th>
                                <th>Keterangan</th>
                                <th>Detail Expired</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- JS Custom --}}
    <x-slot name="jscustom">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.3/b-3.0.1/r-3.0.1/datatables.min.css"/>
        <script src="https://cdn.datatables.net/v/bs5/dt-2.0.3/b-3.0.1/r-3.0.1/datatables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            function reloadTable() {
                table.ajax.reload();
            }

            var table = $('#tbstokopname').DataTable({
                ordering: true,
                order: [[0, 'desc']],
                pageLength: 50,
                responsive: true,
                processing: true,
                ajax: {
                    url: "{{ route('laporan.stokopname.data') }}",
                    type: "GET",
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                        d.unit = $('#unit').val();
                    },
                    dataSrc: "data"
                },
                columns: [
                    { data: "tgl_opname" },
                    { data: "unit" },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    { data: "stock_sistem", className: "text-end" },
                    { data: null, className: "text-end", render: function(data){
                        if (data.status === "pending") {
                            return "<span class='text-muted'>Belum Input</span>";
                        }
                        return data.stock_fisik;
                    }},
                    { data: null, className: "text-end", render: function(data){
                        if (data.status === "pending") {
                            return "-";
                        }
                        return (data.stock_fisik - data.stock_sistem);
                    }},
                    { data: "keterangan" },
                    { data: "detail_expired" },
                    { data: null, render: function(data){
                        if (data.status === "pending") {
                            return '<span class="badge bg-warning text-dark">Pending</span>';
                        }
                        return '<span class="badge bg-success">Sukses</span>';
                    }}
                ],
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
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
