<x-app-layout>
    <x-slot name="pagetitle">Laporan Penjualan Bengkel Detail</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penjualan Bengkel Detail</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-inline-flex gap-2">
                        <input type="date" id="start_date" class="form-control form-control-sm"
                               value="{{ $start_date }}" onchange="reloadTable()" />
                        <input type="date" id="end_date" class="form-control form-control-sm"
                               value="{{ $end_date }}" onchange="reloadTable()" />

                        <select id="metode" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all" @if($metode_bayar=='all') selected @endif>Semua Metode</option>
                            <option value="tunai" @if($metode_bayar=='tunai') selected @endif>Tunai</option>
                            <option value="cicilan" @if($metode_bayar=='cicilan') selected @endif>Cicilan</option>
                        </select>

                        <select id="jenis_item" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all" @if($jenis_item=='all') selected @endif>Semua Item</option>
                            <option value="barang" @if($jenis_item=='barang') selected @endif>Barang</option>
                            <option value="jasa" @if($jenis_item=='jasa') selected @endif>Jasa</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-warning card-outline">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title mb-0">Detail Transaksi Bengkel</h5>
                    </div>
                    <table id="tbpenjualan" class="table table-sm table table-bordered" style="width:100%; font-size: small;">
                        <thead class="table-warning">
                            <tr>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Unit</th>
                                <th>Metode</th>
                                <th>Tipe</th>
                                <th>Kode</th>
                                <th>Nama Item</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Print Content --}}
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

            // Fungsi untuk membersihkan format angka dan konversi ke number
            function cleanNumber(value) {
                if (!value) return 0;
                // Hapus semua titik (pemisah ribuan) dan konversi ke number
                return parseFloat(value.toString().replace(/\./g, '')) || 0;
            }

            function printReport() {
                // Ambil data dari DataTable
                let data = table.rows().data().toArray();
                
                if (data.length === 0) {
                    alert('Tidak ada data untuk dicetak!');
                    return;
                }

                // Buat konten print yang bersih
                let printHTML = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Laporan Penjualan Bengkel Detail</title>
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            
                            body {
                                font-family: 'Arial', sans-serif;
                                font-size: 12px;
                                line-height: 1.4;
                                color: #000;
                                padding: 20px;
                                background: white;
                            }
                            
                            @page {
                                size: A4 landscape;
                                margin: 15mm;
                            }
                            
                            @media print {
                                body {
                                    padding: 0;
                                    margin: 0;
                                }
                                
                                .print-only {
                                    display: block !important;
                                }
                                
                                .no-print {
                                    display: none !important;
                                }
                            }
                            
                            .header {
                                text-align: center;
                                margin-bottom: 20px;
                                padding-bottom: 10px;
                                border-bottom: 2px solid #ffc107;
                            }
                            
                            .header h1 {
                                font-size: 18px;
                                margin-bottom: 5px;
                                color: #000;
                            }
                            
                            .header p {
                                margin: 2px 0;
                                font-size: 11px;
                            }
                            
                            .table-container {
                                width: 100%;
                                overflow: hidden;
                            }
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                                table-layout: fixed;
                            }
                            
                            th {
                                background-color: #ffc107 !important;
                                color: #000 !important;
                                border: 1px solid #000 !important;
                                padding: 6px 4px;
                                font-weight: bold;
                                font-size: 11px;
                                text-align: left;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            td {
                                border: 1px solid #000 !important;
                                padding: 4px 3px;
                                font-size: 10px;
                                text-align: left;
                            }
                            
                            .text-right {
                                text-align: right;
                            }
                            
                            .text-center {
                                text-align: center;
                            }
                            
                            .bold {
                                font-weight: bold;
                            }
                            
                            .invoice-total {
                                background-color: #fff3cd !important;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            .grand-total {
                                background-color: #ffc107 !important;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            .subtotal-row td {
                                border-top: 2px solid #000 !important;
                            }
                            
                            .grand-total-row td {
                                border-top: 3px double #000 !important;
                                font-weight: bold;
                            }
                            
                            .footer {
                                margin-top: 20px;
                                font-size: 10px;
                                text-align: center;
                                color: #666;
                            }
                            
                            .page-break {
                                page-break-before: always;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>LAPORAN PENJUALAN BENGKEL DETAIL</h1>
                            <p>Periode: ${$('#start_date').val().split('-').reverse().join('/')} s/d ${$('#end_date').val().split('-').reverse().join('/')}</p>
                            <p>Metode: ${$('#metode').find('option:selected').text()} | Jenis Item: ${$('#jenis_item').find('option:selected').text()}</p>
                            <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                        
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Tanggal</th>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Unit</th>
                                        <th>Metode</th>
                                        <th>Tipe</th>
                                        <th>Kode</th>
                                        <th>Nama Item</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                // Kelompokkan data berdasarkan invoice untuk perhitungan subtotal
                let invoiceGroups = {};
                let grandTotal = 0;
                let currentInvoice = '';
                let rowNumber = 1;
                let invoiceCount = 0;
                
                // Proses data
                data.forEach((row, index) => {
                    // Kelompokkan berdasarkan invoice
                    if (currentInvoice !== row.nomor_invoice) {
                        currentInvoice = row.nomor_invoice;
                        invoiceGroups[currentInvoice] = {
                            total: 0,
                            rows: []
                        };
                        invoiceCount++;
                    }
                    
                    // Bersihkan format angka sebelum dijumlah
                    let total = cleanNumber(row.total);
                    invoiceGroups[currentInvoice].total += total;
                    invoiceGroups[currentInvoice].rows.push(row);
                    grandTotal += total;
                });

                // Render data dengan subtotal per invoice
                let invoiceIndex = 0;
                for (let invoice in invoiceGroups) {
                    let group = invoiceGroups[invoice];
                    let groupRows = group.rows;
                    
                    // Render setiap baris dalam invoice
                    groupRows.forEach(row => {
                        printHTML += `
                            <tr>
                                <td class="text-center">${rowNumber++}</td>
                                <td>${row.tanggal}</td>
                                <td>${row.nomor_invoice}</td>
                                <td>${row.customer || 'Umum'}</td>
                                <td>${row.nama_unit}</td>
                                <td>${row.metode_bayar}</td>
                                <td>${row.tipe_item}</td>
                                <td>${row.kode_barang || '-'}</td>
                                <td>${row.nama_barang || '-'}</td>
                                <td class="text-right">${row.qty}</td>
                                <td class="text-right">${row.harga}</td>
                                <td class="text-right">${row.total}</td>
                            </tr>`;
                    });
                    
                    // Tambahkan subtotal untuk invoice ini
                    printHTML += `
                        <tr class="subtotal-row invoice-total">
                            <td colspan="9" class="text-right bold">Subtotal Invoice ${invoice}</td>
                            <td colspan="2" class="text-right bold">Subtotal</td>
                            <td class="text-right bold">${group.total}</td>
                        </tr>`;
                    
                    invoiceIndex++;
                    
                    // Tambahkan page break setelah setiap 40 baris (kecuali invoice terakhir)
                    if (invoiceIndex < Object.keys(invoiceGroups).length && rowNumber % 40 === 0) {
                        printHTML += `
                            <tr class="page-break">
                                <td colspan="12"></td>
                            </tr>`;
                    }
                }

                // Tambahkan grand total
                printHTML += `
                            </tbody>
                            <tfoot>
                                <tr class="grand-total-row grand-total">
                                    <td colspan="9" class="text-right bold">GRAND TOTAL</td>
                                    <td class="text-right bold">${data.length} item</td>
                                    <td class="text-right bold">${invoiceCount} invoice</td>
                                    <td class="text-right bold">${grandTotal}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="footer">
                        <p>Dicetak dari Sistem pada ${new Date().toLocaleTimeString('id-ID')}</p>
                    </div>
                </body>
                </html>`;

                // Buka window baru untuk print
                let printWindow = window.open('', '_blank', 'width=1200,height=800');
                printWindow.document.open();
                printWindow.document.write(printHTML);
                printWindow.document.close();
                
                // Tunggu konten selesai load lalu print
                printWindow.onload = function() {
                    setTimeout(function() {
                        printWindow.focus();
                        printWindow.print();
                        printWindow.close();
                    }, 250);
                };
            }

            table = $('#tbpenjualan').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 50,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, 'All']],
                ajax: {
                    url: "{{ route('penjualan-bengkel-detail.data') }}",
                    type: "GET",
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date   = $('#end_date').val();
                        d.metode     = $('#metode').val();
                        d.jenis_item = $('#jenis_item').val();
                    }
                },
                columns: [
                    { data: "tanggal" },
                    { data: "nomor_invoice" },
                    { data: "customer" },
                    { data: "nama_unit" },
                    { data: "metode_bayar" },
                    { data: "tipe_item" },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    { data: "qty", className: "text-end" },
                    { data: "harga", className: "text-end" },
                    { data: "total", className: "text-end" }
                ],
                drawCallback: function (settings) {
                    let api = this.api();
                    let rows = api.rows({ page: 'current' }).nodes();
                    let data = api.rows({ page: 'current' }).data();
                    
                    // Hapus baris total invoice sebelumnya
                    $(rows).filter('.invoice-total-row').remove();
                    
                    // Reset semua rowspan sebelumnya
                    $(rows).find('td').removeAttr('rowspan');
                    
                    let invoiceGroups = {};
                    let grandTotal = 0;

                    // Kelompokkan data berdasarkan invoice
                    data.each(function (row, i) {
                        let invoice = row.nomor_invoice;
                        // Bersihkan format angka sebelum dijumlah
                        let total = cleanNumber(row.total);
                        grandTotal += total;

                        if (!invoiceGroups[invoice]) {
                            invoiceGroups[invoice] = {
                                count: 0,
                                total: 0,
                                firstRowIndex: i,
                                data: row
                            };
                        }
                        
                        invoiceGroups[invoice].count++;
                        invoiceGroups[invoice].total += total;
                    });

                    // Terapkan rowspan untuk setiap kelompok invoice
                    for (let invoice in invoiceGroups) {
                        let group = invoiceGroups[invoice];
                        
                        if (group.count > 1) {
                            // Terapkan rowspan pada baris pertama kelompok (4 kolom: Tanggal, Invoice, Customer, Unit)
                            $(rows).eq(group.firstRowIndex).find('td:eq(0)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(1)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(2)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(3)').attr('rowspan', group.count).addClass('align-middle');
                            
                            // Sembunyikan kolom pada baris berikutnya dalam kelompok yang sama
                            for (let i = group.firstRowIndex + 1; i < group.firstRowIndex + group.count; i++) {
                                if ($(rows).eq(i).find('td').length > 4) {
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                }
                            }
                        }
                    }

                    // Tambahkan baris total untuk setiap invoice di akhir kelompok
                    let lastInvoice = null;
                    let invoiceRows = [];
                    
                    data.each(function (row, i) {
                        let invoice = row.nomor_invoice;
                        
                        if (lastInvoice !== invoice) {
                            invoiceRows.push(i);
                            lastInvoice = invoice;
                        }
                    });
                    
                    // Tambahkan baris total invoice (dari belakang ke depan agar tidak mengacaukan indeks)
                    for (let i = invoiceRows.length - 1; i >= 0; i--) {
                        let rowIndex = invoiceRows[i];
                        let invoice = data[rowIndex].nomor_invoice;
                        let group = invoiceGroups[invoice];
                        
                        $(rows).eq(rowIndex + group.count - 1).after(
                            `<tr class="fw-bold bg-warning invoice-total-row">
                                <td colspan="8" class="text-end">Total Invoice: ${invoice}</td>
                                <td colspan="2" class="text-end">Subtotal</td>
                                <td class="text-end">${group.total}</td>
                            </tr>`
                        );
                    }

                    // Render grand total di footer
                    $(api.table().footer()).html(
                        `<tr class="fw-bold bg-warning text-dark">
                            <td colspan="8" class="text-end">Grand Total</td>
                            <td colspan="2" class="text-end">${data.length} item</td>
                            <td class="text-end">${grandTotal}</td>
                        </tr>`
                    );
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
                            columns: ':visible',
                            format: {
                                body: function (data, row, column, node) {
                                    return data;
                                }
                            }
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row c', sheet).attr('s', '50');
                        }
                    },
                    {
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-warning btn-sm',
                        action: function (e, dt, node, config) {
                            printReport();
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>