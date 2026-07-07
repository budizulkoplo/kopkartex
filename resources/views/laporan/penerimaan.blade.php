<x-app-layout>
    <x-slot name="pagetitle">Laporan Penerimaan Barang</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penerimaan Barang</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <select id="unit_id" class="form-select form-select-sm d-inline-block w-auto me-2"
                            onchange="reloadTable()">
                        <option value="all">Semua Unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) $defaultUnitId === (string) $unit->id)>{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                    <select id="metode_bayar" class="form-select form-select-sm d-inline-block w-auto me-2"
                            onchange="reloadTable()">
                        <option value="all">Semua Pembayaran</option>
                        <option value="cash">Cash</option>
                        <option value="tempo">Tempo</option>
                    </select>
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
                                <th>Pembayaran</th>
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
                                <td colspan="6" class="text-end">TOTAL PAGE</td>
                                <td id="page-total-jumlah" class="text-end">0</td>
                                <td></td>
                                <td id="page-total-subtotal" class="text-end">Rp 0</td>
                            </tr>
                            <tr class="table-success fw-bold">
                                <td colspan="6" class="text-end">TOTAL SEMUA DATA</td>
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

    <div id="printArea" style="display: none;"></div>

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

            function formatMetodeBayar(value) {
                if (value === 'tempo') return 'Tempo';
                if (value === 'cash') return 'Cash';
                return '-';
            }

            function selectedReportTitle() {
                const unitText = $('#unit_id option:selected').text().trim();
                const metode = $('#metode_bayar').val();
                const metodeText = metode === 'all' ? '' : formatMetodeBayar(metode);
                const titleParts = ['Laporan Penerimaan Barang'];

                if (unitText && unitText !== 'Semua Unit') {
                    titleParts.push(unitText);
                }

                if (metodeText && metodeText !== '-') {
                    titleParts.push(metodeText);
                }

                return titleParts.join(' ');
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
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

            function printReport() {
                const data = table.rows({ search: 'applied' }).data().toArray();

                if (data.length === 0) {
                    alert('Tidak ada data untuk dicetak!');
                    return;
                }

                let invoiceGroups = {};
                let invoiceOrder = [];
                let grandJumlah = 0;
                let grandSubtotal = 0;
                let rowNumber = 1;
                const reportTitle = selectedReportTitle();

                data.forEach(function(row) {
                    const invoice = row.nomor_invoice || '-';
                    const groupKey = `${row.unit_id || '-'}|${invoice}`;

                    if (!invoiceGroups[groupKey]) {
                        invoiceGroups[groupKey] = {
                            invoice: invoice,
                            unit: row.nama_unit || '-',
                            jumlah: 0,
                            subtotal: 0,
                            rows: []
                        };
                        invoiceOrder.push(groupKey);
                    }

                    const jumlah = cleanNumber(row.jumlah);
                    const subtotal = cleanNumber(row.subtotal);

                    invoiceGroups[groupKey].jumlah += jumlah;
                    invoiceGroups[groupKey].subtotal += subtotal;
                    invoiceGroups[groupKey].rows.push(row);
                    grandJumlah += jumlah;
                    grandSubtotal += subtotal;
                });

                let printHTML = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>${escapeHtml(reportTitle)}</title>
                        <style>
                            * { box-sizing: border-box; }
                            body {
                                margin: 0;
                                padding: 8mm;
                                background: #fff;
                                color: #000;
                                font-family: Arial, sans-serif;
                                font-size: 9px;
                                line-height: 1.12;
                            }
                            @page { size: A4 portrait; margin: 7mm; }
                            @media print { body { padding: 0; } }
                            .header {
                                margin-bottom: 5px;
                                padding-bottom: 4px;
                                border-bottom: 1px solid #000;
                                text-align: center;
                            }
                            .header h1 {
                                margin: 0 0 2px;
                                font-size: 14px;
                                line-height: 1.1;
                            }
                            .header p {
                                margin: 1px 0;
                                font-size: 9px;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                table-layout: fixed;
                            }
                            th, td {
                                border: 1px solid #000;
                                padding: 1px 2px;
                                vertical-align: top;
                                overflow-wrap: anywhere;
                            }
                            th {
                                background: #f1f1f1 !important;
                                font-size: 8.8px;
                                font-weight: 700;
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                            }
                            td { font-size: 8.5px; }
                            .text-center { text-align: center; }
                            .text-right { text-align: right; }
                            .bold { font-weight: 700; }
                            .invoice-total td {
                                background: #ededed !important;
                                border-top: 1.5px solid #000;
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                            }
                            .grand-total td {
                                background: #dfefff !important;
                                border-top: 2px double #000;
                                font-weight: 700;
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                            }
                            tbody.invoice-group {
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }
                            .footer {
                                margin-top: 4px;
                                text-align: right;
                                font-size: 8px;
                            }
                            th:nth-child(1), td:nth-child(1) { width: 4%; }
                            th:nth-child(2), td:nth-child(2) { width: 9%; }
                            th:nth-child(3), td:nth-child(3) { width: 11%; }
                            th:nth-child(4), td:nth-child(4) { width: 13%; }
                            th:nth-child(5), td:nth-child(5) { width: 8%; }
                            th:nth-child(6), td:nth-child(6) { width: 9%; }
                            th:nth-child(7), td:nth-child(7) { width: 20%; }
                            th:nth-child(8), td:nth-child(8) { width: 8%; }
                            th:nth-child(9), td:nth-child(9) { width: 9%; }
                            th:nth-child(10), td:nth-child(10) { width: 9%; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>${escapeHtml(reportTitle)}</h1>
                            <p>Periode: ${escapeHtml($('#bulan').val())}</p>
                            <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Tanggal</th>
                                    <th>No Invoice</th>
                                    <th>Supplier</th>
                                    <th>Pembayaran</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th class="text-right">Jumlah</th>
                                    <th class="text-right">Harga Beli</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>`;

                invoiceOrder.forEach(function(groupKey) {
                    const group = invoiceGroups[groupKey];
                    printHTML += '<tbody class="invoice-group">';

                    group.rows.forEach(function(row) {
                        printHTML += `
                            <tr>
                                <td class="text-center">${rowNumber++}</td>
                                <td>${escapeHtml(row.tgl_penerimaan)}</td>
                                <td>${escapeHtml(row.nomor_invoice)}</td>
                                <td>${escapeHtml(row.nama_supplier)}</td>
                                <td>${escapeHtml(formatMetodeBayar(row.metode_bayar))}</td>
                                <td>${escapeHtml(row.kode_barang)}</td>
                                <td>${escapeHtml(row.nama_barang)}</td>
                                <td class="text-right">${formatQty(row.jumlah)}</td>
                                <td class="text-right">${formatNumber(row.harga_beli)}</td>
                                <td class="text-right">${formatNumber(row.subtotal)}</td>
                            </tr>`;
                    });

                    printHTML += `
                            <tr class="invoice-total">
                                <td colspan="7" class="text-right bold">Total Invoice: ${escapeHtml(group.invoice)}</td>
                                <td class="text-right bold">${formatQty(group.jumlah)}</td>
                                <td></td>
                                <td class="text-right bold">${formatNumber(group.subtotal)}</td>
                            </tr>
                        </tbody>`;
                });

                printHTML += `
                            <tfoot>
                                <tr class="grand-total">
                                    <td colspan="7" class="text-right">TOTAL SEMUA DATA (${data.length.toLocaleString('id-ID')} baris / ${invoiceOrder.length.toLocaleString('id-ID')} invoice)</td>
                                    <td class="text-right">${formatQty(grandJumlah)}</td>
                                    <td></td>
                                    <td class="text-right">${formatNumber(grandSubtotal)}</td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="footer">Dicetak pada ${new Date().toLocaleTimeString('id-ID')}</div>
                    </body>
                    </html>`;

                const printWindow = window.open('', '_blank', 'width=1200,height=800');
                printWindow.document.open();
                printWindow.document.write(printHTML);
                printWindow.document.close();

                printWindow.onload = function() {
                    setTimeout(function() {
                        printWindow.focus();
                        printWindow.print();
                        printWindow.close();
                    }, 250);
                };
            }

            table = $('#tbpenerimaan').DataTable({
                ordering: false,
                processing: true,
                pageLength: 50,
                ajax: {
                    url: "{{ route('laporan.penerimaan.data') }}",
                    type: "GET",
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                        d.unit_id = $('#unit_id').val();
                        d.metode_bayar = $('#metode_bayar').val();
                    },
                    dataSrc: "data"
                },
                columns: [
                    { data: "tgl_penerimaan" },
                    { data: "nomor_invoice" },
                    { data: "nama_supplier" },
                    {
                        data: "metode_bayar",
                        render: function(data) {
                            return formatMetodeBayar(data);
                        }
                    },
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
                    dataSrc: ["tgl_penerimaan", "nomor_invoice", "nama_supplier", "metode_bayar"]
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).nodes();
                    var data = api.rows({ page: 'current' }).data();
                    var invoiceGroups = {};
                    var invoiceStarts = [];
                    var pageInvoiceCount = 0;
                    var allInvoiceGroups = {};

                    $(rows).filter('.invoice-total-row').remove();
                    $(rows).find('td').removeAttr('rowspan').removeClass('align-middle');

                    data.each(function(row, i) {
                        var invoice = (row.unit_id || '-') + '|' + (row.nomor_invoice || '-');
                        var jumlah = cleanNumber(row.jumlah);
                        var subtotal = cleanNumber(row.subtotal);

                        if (!invoiceGroups[invoice]) {
                            invoiceGroups[invoice] = {
                                count: 0,
                                jumlah: 0,
                                subtotal: 0,
                                firstRowIndex: i
                            };
                            invoiceStarts.push(i);
                            pageInvoiceCount++;
                        }

                        invoiceGroups[invoice].count++;
                        invoiceGroups[invoice].jumlah += jumlah;
                        invoiceGroups[invoice].subtotal += subtotal;
                    });

                    api.rows({ search: 'applied' }).data().each(function(row) {
                        allInvoiceGroups[(row.unit_id || '-') + '|' + (row.nomor_invoice || '-')] = true;
                    });

                    for (var invoice in invoiceGroups) {
                        var group = invoiceGroups[invoice];

                        if (group.count > 1) {
                            $(rows).eq(group.firstRowIndex).find('td:eq(0)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(1)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(2)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(3)').attr('rowspan', group.count).addClass('align-middle');

                            for (var rowIndex = group.firstRowIndex + 1; rowIndex < group.firstRowIndex + group.count; rowIndex++) {
                                if ($(rows).eq(rowIndex).find('td').length > 6) {
                                    $(rows).eq(rowIndex).find('td:eq(0)').remove();
                                    $(rows).eq(rowIndex).find('td:eq(0)').remove();
                                    $(rows).eq(rowIndex).find('td:eq(0)').remove();
                                    $(rows).eq(rowIndex).find('td:eq(0)').remove();
                                }
                            }
                        }
                    }

                    for (var i = invoiceStarts.length - 1; i >= 0; i--) {
                        var startIndex = invoiceStarts[i];
                        var row = data[startIndex];
                        var invoiceKey = (row.unit_id || '-') + '|' + (row.nomor_invoice || '-');
                        var invoiceGroup = invoiceGroups[invoiceKey];

                        $(rows).eq(startIndex + invoiceGroup.count - 1).after(
                            `<tr class="fw-bold bg-light invoice-total-row">
                                <td colspan="6" class="text-end">Total Invoice: ${escapeHtml(row.nomor_invoice || '-')}</td>
                                <td class="text-end">${formatQty(invoiceGroup.jumlah)}</td>
                                <td></td>
                                <td class="text-end">${formatRupiah(invoiceGroup.subtotal)}</td>
                            </tr>`
                        );
                    }

                    renderTotals(api);
                    $('#page-total-jumlah').text($('#page-total-jumlah').text() + ' / ' + pageInvoiceCount.toLocaleString('id-ID') + ' invoice');
                    $('#all-total-jumlah').text($('#all-total-jumlah').text() + ' / ' + Object.keys(allInvoiceGroups).length.toLocaleString('id-ID') + ' invoice');
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
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-warning btn-sm',
                        action: function () {
                            printReport();
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
