<x-app-layout>
    <x-slot name="pagetitle">Laporan Harian Bengkel</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Harian Bengkel</h3>
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
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-body py-3">
                            <div class="text-muted small">Total Non Jasa</div>
                            <div class="fs-5 fw-semibold" id="summaryNonJasa">
                                Rp {{ number_format($totals['total_non_jasa'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-warning card-outline h-100">
                        <div class="card-body py-3">
                            <div class="text-muted small">Total Jasa</div>
                            <div class="fs-5 fw-semibold" id="summaryJasa">
                                Rp {{ number_format($totals['total_jasa'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-success card-outline h-100">
                        <div class="card-body py-3">
                            <div class="text-muted small">Total Penjualan</div>
                            <div class="fs-5 fw-semibold" id="summaryPenjualan">
                                Rp {{ number_format($totals['total_penjualan'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbHarianBengkel" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-end">Cash Non Jasa</th>
                                <th class="text-end">Kredit Non Jasa</th>
                                <th class="text-end">Cash Jasa</th>
                                <th class="text-end">Kredit Jasa</th>
                                <th class="text-end">Total Non Jasa</th>
                                <th class="text-end">Total Jasa</th>
                                <th class="text-end">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th class="text-end">TOTAL</th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            let table;

            function formatRupiah(value) {
                const number = Number(value || 0);
                return 'Rp ' + number.toLocaleString('id-ID');
            }

            function updateSummary(totals) {
                $('#summaryNonJasa').text(formatRupiah(totals.total_non_jasa));
                $('#summaryJasa').text(formatRupiah(totals.total_jasa));
                $('#summaryPenjualan').text(formatRupiah(totals.total_penjualan));
            }

            function reloadTable() {
                table.ajax.reload();
            }

            table = $('#tbHarianBengkel').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 31,
                ajax: {
                    url: "{{ route('laporan.harian_bengkel.data') }}",
                    type: "GET",
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                    },
                    dataSrc: function(json) {
                        updateSummary(json.totals || {});
                        return json.data || [];
                    }
                },
                columns: [
                    { data: "tanggal_display" },
                    { data: "cash_non_jasa", className: "text-end", render: formatRupiah },
                    { data: "kredit_non_jasa", className: "text-end", render: formatRupiah },
                    { data: "cash_jasa", className: "text-end", render: formatRupiah },
                    { data: "kredit_jasa", className: "text-end", render: formatRupiah },
                    { data: "total_non_jasa", className: "text-end", render: formatRupiah },
                    { data: "total_jasa", className: "text-end", render: formatRupiah },
                    { data: "total_penjualan", className: "text-end fw-semibold", render: formatRupiah }
                ],
                footerCallback: function(row, data) {
                    const api = this.api();
                    const columns = [
                        'cash_non_jasa',
                        'kredit_non_jasa',
                        'cash_jasa',
                        'kredit_jasa',
                        'total_non_jasa',
                        'total_jasa',
                        'total_penjualan'
                    ];

                    columns.forEach(function(column, index) {
                        const total = data.reduce(function(sum, item) {
                            return sum + Number(item[column] || 0);
                        }, 0);

                        $(api.column(index + 1).footer()).html(formatRupiah(total));
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
                        title: function () {
                            return 'Laporan Harian Bengkel - ' + $('#bulan').val();
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-primary btn-sm',
                        title: function () {
                            return 'Laporan Harian Bengkel - ' + $('#bulan').val();
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
