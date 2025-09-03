<x-app-layout>
    <x-slot name="pagetitle">Mutasi Stok</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Mutasi Stok</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <input type="date" id="tanggal" class="form-control form-control-sm d-inline-block w-auto"
                           value="{{ $tanggal }}" onchange="reloadTable()" />
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbmutasi" class="table table-sm table-striped table-bordered" style="width:100%; font-size:small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Dari Unit</th>
                                <th>Ke Unit</th>
                                <th>Note</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-end">Qty</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script>window.JSZip = JSZip;</script>

        <script>
            let table;
            function reloadTable() { table.ajax.reload(); }

            table = $('#tbmutasi').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 50,
                ajax: {
                    url: "{{ route('laporan.mutasi_stok.data') }}",
                    type: "GET",
                    data: function(d){ d.tanggal = $('#tanggal').val(); },
                    dataSrc: function(res){
                        const out = [];
                        (res.data || []).forEach(g => {
                            const h = g.header || {};
                            (g.details || []).forEach(d => {
                                out.push({
                                    tanggal: h.tanggal || '',
                                    dari_unit: h.dari_unit || '',
                                    ke_unit: h.ke_unit || '',
                                    note: h.note || '',
                                    kode_barang: d.kode_barang || '',
                                    nama_barang: d.nama_barang || '',
                                    qty: d.qty || 0
                                });
                            });
                        });
                        return out;
                    }
                },
                columns: [
                    { data: "tanggal" },
                    { data: "dari_unit" },
                    { data: "ke_unit" },
                    { data: "note" },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    { data: "qty", className: "text-end" }
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
                        exportOptions: { columns: ':visible' }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
