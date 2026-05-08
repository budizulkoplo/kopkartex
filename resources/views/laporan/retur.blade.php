<x-app-layout>
    <x-slot name="pagetitle">Laporan Retur</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Retur</h3>
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
                    <table id="tbretur" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Unit</th>
                                <th>No Retur</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-end">Qty</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td colspan="6" class="text-end">TOTAL PAGE</td>
                                <td id="page-total-qty" class="text-end">0</td>
                            </tr>
                            <tr class="table-success fw-bold">
                                <td colspan="6" class="text-end">TOTAL SEMUA DATA</td>
                                <td id="all-total-qty" class="text-end">0</td>
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
            let table;

            function reloadTable() {
                table.ajax.reload();
            }

            function cleanNumber(value) {
                if (value === null || value === undefined || value === '') return 0;
                if (typeof value === 'number') return value;

                let text = String(value).replace(/<[^>]*>/g, '').replace(/[^\d,.-]/g, '').trim();
                if (/^-?\d{1,3}(\.\d{3})+(,\d+)?$/.test(text)) {
                    text = text.replace(/\./g, '').replace(',', '.');
                } else if (/^-?\d{1,3}(,\d{3})+(\.\d+)?$/.test(text)) {
                    text = text.replace(/,/g, '');
                } else {
                    text = text.replace(',', '.');
                }

                return parseFloat(text) || 0;
            }

            function formatNumber(value, decimals = 3) {
                return cleanNumber(value).toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }

            function calculateQty(api, selector) {
                let qty = 0;
                api.rows(selector).data().each(function(row) {
                    qty += cleanNumber(row.qty);
                });
                return qty;
            }

            function renderTotals(api) {
                $('#page-total-qty').text(formatNumber(calculateQty(api, { page: 'current' })));
                $('#all-total-qty').text(formatNumber(calculateQty(api, { search: 'applied' })));
            }

            // init DataTable
            table = $('#tbretur').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 50,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, 'All']],
                ajax: {
                    url: "{{ route('laporan.retur.data') }}",
                    type: "GET",
                    data: function (d) {
                        d.tanggal = $('#tanggal').val();
                    },
                    // FLATTEN dari {header, details[]} -> array baris datar
                    dataSrc: function (res) {
                        const out = [];
                        (res.data || []).forEach(g => {
                            const h = g.header || {};
                            const noRetur = h.no_retur ?? (String(h.tgl_retur || '').replace(/-/g, '') + '-' + (h.unit || ''));
                            (g.details || []).forEach(d => {
                                out.push({
                                    tgl_retur:  h.tgl_retur || '',
                                    supplier:   h.supplier || '',
                                    unit:       h.unit || '',
                                    no_retur:   noRetur,
                                    kode_barang: d.kode_barang || '',
                                    nama_barang: d.nama_barang || '',
                                    qty:        d.qty ?? 0
                                });
                            });
                        });
                        return out;
                    }
                },
                columns: [
                    { data: "tgl_retur" },
                    { data: "supplier" },
                    { data: "unit" },
                    { data: "no_retur" },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    {
                        data: "qty",
                        className: "text-end",
                        render: function(data) {
                            return formatNumber(data);
                        }
                    }
                ],
                drawCallback: function() {
                    renderTotals(this.api());
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
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-primary btn-sm',
                        exportOptions: { columns: ':visible' },
                        title: function () {
                            return 'Laporan Retur - ' + $('#tanggal').val();
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
