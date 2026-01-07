<x-app-layout>
    <x-slot name="pagetitle">Laporan Mutasi Stok</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Mutasi Stok</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-inline-flex gap-2">
                        <input type="date" id="start_date" class="form-control form-control-sm"
                               value="{{ $start_date }}" onchange="reloadTable()" />
                        <input type="date" id="end_date" class="form-control form-control-sm"
                               value="{{ $end_date }}" onchange="reloadTable()" />

                        <select id="unit" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all">Semua Unit</option>
                            @foreach($units as $id => $nama)
                                <option value="{{ $id }}" @if($unit_id == $id) selected @endif>{{ $nama }}</option>
                            @endforeach
                        </select>

                        <select id="status" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all" @if($status == 'all') selected @endif>Semua Status</option>
                            <option value="diajukan" @if($status == 'diajukan') selected @endif>Diajukan</option>
                            <option value="dikirim" @if($status == 'dikirim') selected @endif>Dikirim</option>
                            <option value="diterima" @if($status == 'diterima') selected @endif>Diterima</option>
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
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title mb-0">Detail Mutasi Stok</h5>
                    </div>
                    <table id="tbmutasi" class="table table-sm table-bordered" style="width:100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>Dari Unit</th>
                                <th>Ke Unit</th>
                                <th>Status</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-end">Qty</th>
                                <th>Note</th>
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
                        <title>Laporan Mutasi Stok</title>
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
                            }
                            
                            .header {
                                text-align: center;
                                margin-bottom: 20px;
                                padding-bottom: 10px;
                                border-bottom: 2px solid #000;
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
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                                table-layout: fixed;
                            }
                            
                            th {
                                background-color: #f2f2f2 !important;
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
                                background-color: #e9ecef !important;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            .grand-total {
                                background-color: #d1e7ff !important;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            .subtotal-row td {
                                border-top: 2px solid #000 !important;
                            }
                            
                            .grand-total-row td {
                                border-top: 3px double #000 !important;
                                font-weight: bold;
                            }
                            
                            /* Column widths */
                            th:nth-child(1), td:nth-child(1) { width: 8%; }   /* No */
                            th:nth-child(2), td:nth-child(2) { width: 8%; }   /* Tanggal */
                            th:nth-child(3), td:nth-child(3) { width: 12%; }  /* Invoice */
                            th:nth-child(4), td:nth-child(4) { width: 10%; }  /* Dari Unit */
                            th:nth-child(5), td:nth-child(5) { width: 10%; }  /* Ke Unit */
                            th:nth-child(6), td:nth-child(6) { width: 8%; }   /* Status */
                            th:nth-child(7), td:nth-child(7) { width: 10%; }  /* Kode Barang */
                            th:nth-child(8), td:nth-child(8) { width: 15%; }  /* Nama Barang */
                            th:nth-child(9), td:nth-child(9) { width: 6%; }   /* Qty */
                            th:nth-child(10), td:nth-child(10) { width: 13%; } /* Note */
                            
                            .footer {
                                margin-top: 20px;
                                font-size: 10px;
                                text-align: center;
                                color: #666;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>LAPORAN MUTASI STOK</h1>
                            <p>Periode: ${$('#start_date').val()} s/d ${$('#end_date').val()}</p>
                            <p>Unit: ${$('#unit').find('option:selected').text()} | Status: ${$('#status').find('option:selected').text()}</p>
                            <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Tanggal</th>
                                    <th>Invoice</th>
                                    <th>Dari Unit</th>
                                    <th>Ke Unit</th>
                                    <th>Status</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th class="text-right">Qty</th>
                                    <th>Note</th>
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
                            tanggal: row.tanggal,
                            dari_unit: row.dari_unit,
                            ke_unit: row.ke_unit,
                            status: row.status,
                            total: 0,
                            rows: []
                        };
                        invoiceCount++;
                    }
                    
                    invoiceGroups[currentInvoice].total += parseFloat(row.qty);
                    invoiceGroups[currentInvoice].rows.push(row);
                    grandTotal += parseFloat(row.qty);
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
                                <td>${row.dari_unit}</td>
                                <td>${row.ke_unit}</td>
                                <td>${row.status}</td>
                                <td>${row.kode_barang}</td>
                                <td>${row.nama_barang}</td>
                                <td class="text-right">${parseFloat(row.qty).toLocaleString('id-ID')}</td>
                                <td>${row.note || ''}</td>
                            </tr>`;
                    });
                    
                    // Tambahkan subtotal untuk invoice ini
                    printHTML += `
                        <tr class="subtotal-row invoice-total">
                            <td colspan="8" class="text-right bold">Subtotal Invoice ${invoice}</td>
                            <td class="text-right bold">${group.total.toLocaleString('id-ID')}</td>
                            <td></td>
                        </tr>`;
                    
                    invoiceIndex++;
                }

                // Tambahkan grand total
                printHTML += `
                            </tbody>
                            <tfoot>
                                <tr class="grand-total-row grand-total">
                                    <td colspan="8" class="text-right bold">GRAND TOTAL</td>
                                    <td class="text-right bold">${grandTotal.toLocaleString('id-ID')}</td>
                                    <td class="text-right bold">${invoiceCount} invoice</td>
                                </tr>
                            </tfoot>
                        </table>
                        
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

            table = $('#tbmutasi').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 50,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, 'All']],
                ajax: {
                    url: "{{ route('laporan.mutasi_stok.data') }}",
                    type: "GET",
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date   = $('#end_date').val();
                        d.unit       = $('#unit').val();
                        d.status     = $('#status').val();
                    },
                    dataSrc: function (res) {
                        // Ubah format grouped data menjadi flat untuk DataTable
                        const flatData = [];
                        (res.data || []).forEach(group => {
                            const header = group.header || {};
                            (group.details || []).forEach((detail, index) => {
                                flatData.push({
                                    tanggal: header.tanggal,
                                    nomor_invoice: header.nomor_invoice,
                                    dari_unit: header.dari_unit,
                                    ke_unit: header.ke_unit,
                                    status: header.status,
                                    kode_barang: detail.kode_barang,
                                    nama_barang: detail.nama_barang,
                                    qty: detail.qty,
                                    note: header.note,
                                    rowspan: group.details.length,
                                    isFirstRow: index === 0,
                                    groupId: header.nomor_invoice,
                                    groupTotal: group.total_qty
                                });
                            });
                        });
                        return flatData;
                    }
                },
                columns: [
                    { 
                        data: "tanggal", 
                        className: "text-start",
                        render: function(data, type, row) {
                            if (type === 'display' && row.isFirstRow) {
                                return `<span class="align-middle">${data}</span>`;
                            }
                            return data;
                        }
                    },
                    { 
                        data: "nomor_invoice", 
                        className: "text-start",
                        render: function(data, type, row) {
                            if (type === 'display' && row.isFirstRow) {
                                return `<span class="align-middle fw-bold">${data}</span>`;
                            }
                            return data;
                        }
                    },
                    { 
                        data: "dari_unit", 
                        className: "text-start",
                        render: function(data, type, row) {
                            if (type === 'display' && row.isFirstRow) {
                                return `<span class="align-middle">${data}</span>`;
                            }
                            return data;
                        }
                    },
                    { 
                        data: "ke_unit", 
                        className: "text-start",
                        render: function(data, type, row) {
                            if (type === 'display' && row.isFirstRow) {
                                return `<span class="align-middle">${data}</span>`;
                            }
                            return data;
                        }
                    },
                    { 
                        data: "status", 
                        className: "text-start",
                        render: function(data, type, row) {
                            let badgeClass = 'badge ';
                            switch(data) {
                                case 'diajukan': badgeClass += 'bg-warning'; break;
                                case 'dikirim': badgeClass += 'bg-info'; break;
                                case 'diterima': badgeClass += 'bg-success'; break;
                                default: badgeClass += 'bg-secondary';
                            }
                            
                            if (type === 'display' && row.isFirstRow) {
                                return `<span class="align-middle"><span class="${badgeClass}">${data}</span></span>`;
                            }
                            return data;
                        }
                    },
                    { data: "kode_barang", className: "text-start" },
                    { data: "nama_barang", className: "text-start" },
                    { 
                        data: "qty", 
                        className: "text-end",
                        render: function(data) { 
                            return parseFloat(data).toLocaleString('id-ID'); 
                        } 
                    },
                    { 
                        data: "note", 
                        className: "text-start",
                        render: function(data, type, row) {
                            if (type === 'display' && row.isFirstRow) {
                                return `<span class="align-middle">${data || ''}</span>`;
                            }
                            return data || '';
                        }
                    }
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
                        let qty = parseFloat(row.qty);
                        grandTotal += qty;

                        if (!invoiceGroups[invoice]) {
                            invoiceGroups[invoice] = {
                                count: 0,
                                total: 0,
                                firstRowIndex: i,
                                data: row
                            };
                        }
                        
                        invoiceGroups[invoice].count++;
                        invoiceGroups[invoice].total += qty;
                    });

                    // Terapkan rowspan untuk setiap kelompok invoice
                    for (let invoice in invoiceGroups) {
                        let group = invoiceGroups[invoice];
                        
                        if (group.count > 1) {
                            // Terapkan rowspan pada baris pertama kelompok
                            $(rows).eq(group.firstRowIndex).find('td:eq(0)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(1)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(2)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(3)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(4)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(8)').attr('rowspan', group.count).addClass('align-middle');
                            
                            // Sembunyikan kolom pada baris berikutnya dalam kelompok yang sama
                            for (let i = group.firstRowIndex + 1; i < group.firstRowIndex + group.count; i++) {
                                if ($(rows).eq(i).find('td').length > 6) {
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                    $(rows).eq(i).find('td:eq(0)').remove();
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
                            `<tr class="fw-bold bg-light invoice-total-row">
                                <td colspan="7" class="text-end">Total Invoice: ${invoice}</td>
                                <td class="text-end">${group.total.toLocaleString('id-ID')}</td>
                                <td></td>
                            </tr>`
                        );
                    }

                    // Render grand total di footer
                    $(api.table().footer()).html(
                        `<tr class="fw-bold bg-primary text-white">
                            <td colspan="7" class="text-end">Grand Total</td>
                            <td class="text-end">${grandTotal.toLocaleString('id-ID')}</td>
                            <td class="text-end">${Object.keys(invoiceGroups).length} invoice</td>
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
                                    // Untuk kolom numerik, hapus format pemisah ribuan saat export
                                    if (column === 7) {
                                        return data.replace(/\./g, '');
                                    }
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