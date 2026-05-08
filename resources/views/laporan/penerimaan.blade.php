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
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Harga Beli</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td colspan="5" class="text-end">TOTAL PAGE</td>
                                <td id="page-total-jumlah" class="text-end">0</td>
                                <td></td>
                                <td id="page-total-subtotal" class="text-end">Rp 0</td>
                            </tr>
                            <tr class="table-success fw-bold">
                                <td colspan="5" class="text-end">TOTAL SEMUA DATA</td>
                                <td id="all-total-jumlah" class="text-end">0</td>
                                <td></td>
                                <td id="all-total-subtotal" class="text-end">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- JS Custom --}}
    <x-slot name="jscustom">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            function reloadTable() {
                table.ajax.reload();
            }

            function cleanNumber(value) {
                if (value === null || value === undefined || value === '') return 0;
                if (typeof value === 'number') return value;

                let text = String(value).replace(/<[^>]*>/g, '').replace(/[^\d,.-]/g, '').trim();

                if (text.includes(',') && text.includes('.')) {
                    text = text.replace(/\./g, '').replace(',', '.');
                } else if (text.includes(',')) {
                    text = text.replace(',', '.');
                }

                return parseFloat(text) || 0;
            }

            function formatNumber(value, decimals = 0) {
                return cleanNumber(value).toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }

            function formatQty(value) {
                return cleanNumber(value).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 3
                });
            }

            function formatRupiah(value) {
                return 'Rp ' + formatNumber(value);
            }

            function calculateTotals(api, selector) {
                let jumlah = 0;
                let subtotal = 0;

                api.rows(selector).data().each(function(row) {
                    jumlah += cleanNumber(row.jumlah);
                    subtotal += cleanNumber(row.subtotal);
                });

                return { jumlah, subtotal };
            }

            function renderTotals(api) {
                const pageTotals = calculateTotals(api, { page: 'current' });
                const allTotals = calculateTotals(api, { search: 'applied' });

                $('#page-total-jumlah').text(formatQty(pageTotals.jumlah));
                $('#page-total-subtotal').text(formatRupiah(pageTotals.subtotal));
                $('#all-total-jumlah').text(formatQty(allTotals.jumlah));
                $('#all-total-subtotal').text(formatRupiah(allTotals.subtotal));
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
                    {
                        data: "jumlah",
                        className: "text-end",
                        render: function(data) {
                            return formatQty(data);
                        }
                    },
                    {
                        data: "harga_beli",
                        className: "text-end",
                        render: function(data) {
                            return formatRupiah(data);
                        }
                    },
                    {
                        data: "subtotal",
                        className: "text-end fw-semibold",
                        render: function(data) {
                            return formatRupiah(data);
                        }
                    },
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

                    renderTotals(api);
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
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-primary btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        },
                        title: function () {
                            return 'Laporan Penerimaan Barang - ' + $('#bulan').val();
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
