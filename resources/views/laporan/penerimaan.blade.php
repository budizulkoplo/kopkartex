<x-app-layout>
    <x-slot name="pagetitle">Laporan Penerimaan Barang</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penerimaan Barang</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <input type="month" id="bulan" class="form-control form-control-sm d-inline-block w-auto"
                           value="{{ $bulan }}" onchange="reloadTable()" />
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbpenerimaan" class="table table-sm table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>No Invoice</th>
                                <th>Supplier</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
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

        {{-- DataTables + Buttons --}}
        <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.3/b-3.0.1/datatables.min.css"/>
        <script src="https://cdn.datatables.net/v/bs5/dt-2.0.3/b-3.0.1/datatables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            function reloadTable() {
                table.ajax.reload();
            }

            var table = $('#tbpenerimaan').DataTable({
                ordering: false,
                processing: true,
                pageLength: 50,
                ajax: {
                    url: "{{ route('laporan.penerimaan.data') }}",
                    type: "GET",
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                    },
                    dataSrc: "data"
                },
                columns: [
                    { data: "tgl_penerimaan" },
                    { data: "nomor_invoice" },
                    { data: "nama_supplier" },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    { data: "jumlah" },
                ],
                rowGroup: {
                    dataSrc: ["tgl_penerimaan", "nomor_invoice", "nama_supplier"]
                },
                drawCallback: function(settings) {
                    // untuk buat rowspan
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).nodes();
                    var last = {};

                    api.column(0, { page: 'current' }).data().each(function(date, i) {
                        var invoice = api.cell(i,1).data();
                        var supplier = api.cell(i,2).data();
                        var key = date+invoice+supplier;

                        if (last[key]) {
                            $(rows).eq(i).find("td:eq(0)").remove();
                            $(rows).eq(i).find("td:eq(0)").remove();
                            $(rows).eq(i).find("td:eq(0)").remove();
                            last[key].count++;
                        } else {
                            last[key] = { row: $(rows).eq(i), count:1 };
                        }
                    });

                    // apply rowspan
                    $.each(last, function(k,v){
                        if(v.count > 1){
                            v.row.find("td:eq(0)").attr("rowspan", v.count);
                            v.row.find("td:eq(1)").attr("rowspan", v.count);
                            v.row.find("td:eq(2)").attr("rowspan", v.count);
                        }
                    });
                },
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
