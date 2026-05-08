<x-app-layout>
    <x-slot name="pagetitle">Laporan Penjualan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penjualan</h3>
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
                    <table id="tbpenjualan" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                @foreach($units as $id => $nama)
                                    <th>{{ $nama }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td class="text-end">TOTAL PAGE</td>
                                @foreach($units as $id => $nama)
                                    <td id="page-total-{{ $id }}" class="text-end">Rp 0</td>
                                @endforeach
                            </tr>
                            <tr class="table-success fw-bold">
                                <td class="text-end">TOTAL SEMUA DATA</td>
                                @foreach($units as $id => $nama)
                                    <td id="all-total-{{ $id }}" class="text-end">Rp 0</td>
                                @endforeach
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

            // fungsi format Rupiah
            function formatRupiah(angka) {
                if (angka == null) return '';
                return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
            }

            const unitColumns = @json($units->map(fn ($nama, $id) => ['id' => $id, 'nama' => $nama])->values());

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

            function calculateTotals(api, selector) {
                const totals = {};
                unitColumns.forEach(unit => totals[unit.id] = 0);

                api.rows(selector).data().each(function(row) {
                    unitColumns.forEach(function(unit) {
                        totals[unit.id] += cleanNumber(row[unit.nama]);
                    });
                });

                return totals;
            }

            function renderTotals(api) {
                const pageTotals = calculateTotals(api, { page: 'current' });
                const allTotals = calculateTotals(api, { search: 'applied' });

                unitColumns.forEach(function(unit) {
                    $('#page-total-' + unit.id).text(formatRupiah(pageTotals[unit.id]));
                    $('#all-total-' + unit.id).text(formatRupiah(allTotals[unit.id]));
                });
            }

            var table = $('#tbpenjualan').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 31,
                ajax: {
                    url: "{{ route('laporan.penjualan.data') }}",
                    type: "GET",
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                    },
                    dataSrc: "data"
                },
                columns: [
                    { data: "tanggal" },
                    @foreach($units as $id => $nama)
                        { 
                            data: "{{ $nama }}", 
                            render: function(data,type,row){
                                return formatRupiah(data);
                            }
                        },
                    @endforeach
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
                            return 'Laporan Penjualan - ' + $('#bulan').val();
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
