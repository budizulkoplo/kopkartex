<x-app-layout>
    <x-slot name="pagetitle">Laporan Stok Opname</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Stok Opname</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2">
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
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title mb-0">Data Stok Opname</h5>
                        <button onclick="table.ajax.reload()" class="btn btn-success btn-sm">
                            <i class="bi bi-arrow-repeat"></i> Refresh
                        </button>
                    </div>
                    <table id="tbstokopname" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Unit</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-end">Stok Sistem</th>
                                <th class="text-end">Stok Fisik</th>
                                <th class="text-end">Selisih</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td colspan="5" class="text-end">TOTAL:</td>
                                <td id="total-sistem" class="text-end">0</td>
                                <td id="total-fisik" class="text-end">0</td>
                                <td id="total-selisih" class="text-end">0</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
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
                // Ambil semua data dari server (bukan hanya halaman yang ditampilkan)
                $.ajax({
                    url: "{{ route('laporan.stokopname.data') }}",
                    type: "GET",
                    data: { 
                        bulan: $('#bulan').val(),
                        unit: $('#unit').val(),
                        all: true // Parameter untuk mengambil semua data
                    },
                    success: function(response) {
                        if (response.data && response.data.length > 0) {
                            generatePrintContent(response.data);
                        } else {
                            alert('Tidak ada data untuk dicetak!');
                        }
                    },
                    error: function() {
                        alert('Gagal mengambil data untuk dicetak!');
                    }
                });
            }

            function generatePrintContent(data) {
                // Hitung total
                let totalSistem = 0;
                let totalFisik = 0;
                let totalSelisih = 0;
                
                // Buat konten print yang bersih
                let printHTML = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Laporan Stok Opname</title>
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            
                            body {
                                font-family: 'Arial', sans-serif;
                                font-size: 10px;
                                line-height: 1.4;
                                color: #000;
                                padding: 15px;
                                background: white;
                            }
                            
                            @page {
                                size: A4 landscape;
                                margin: 10mm;
                            }
                            
                            @media print {
                                body {
                                    padding: 0;
                                    margin: 0;
                                }
                            }
                            
                            .header {
                                text-align: center;
                                margin-bottom: 15px;
                                padding-bottom: 8px;
                                border-bottom: 2px solid #000;
                            }
                            
                            .header h1 {
                                font-size: 16px;
                                margin-bottom: 5px;
                                color: #000;
                            }
                            
                            .header-info {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 10px;
                                font-size: 9px;
                            }
                            
                            .header-info .left, .header-info .right {
                                width: 48%;
                            }
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                                table-layout: fixed;
                                font-size: 9px;
                            }
                            
                            th {
                                background-color: #f2f2f2 !important;
                                color: #000 !important;
                                border: 1px solid #000 !important;
                                padding: 5px 3px;
                                font-weight: bold;
                                font-size: 9px;
                                text-align: center;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            td {
                                border: 1px solid #000 !important;
                                padding: 4px 2px;
                                font-size: 8.5px;
                                text-align: left;
                            }
                            
                            .text-right {
                                text-align: right;
                            }
                            
                            .text-center {
                                text-align: center;
                            }
                            
                            .text-left {
                                text-align: left;
                            }
                            
                            .bold {
                                font-weight: bold;
                            }
                            
                            .grand-total {
                                background-color: #d1e7ff !important;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            .grand-total-row td {
                                border-top: 3px double #000 !important;
                                font-weight: bold;
                            }
                            
                            .positive {
                                color: #28a745;
                                font-weight: bold;
                            }
                            
                            .negative {
                                color: #dc3545;
                                font-weight: bold;
                            }
                            
                            .zero {
                                color: #6c757d;
                            }
                            
                            /* Column widths */
                            th:nth-child(1), td:nth-child(1) { width: 3%; }   /* No */
                            th:nth-child(2), td:nth-child(2) { width: 8%; }   /* Tanggal */
                            th:nth-child(3), td:nth-child(3) { width: 10%; }  /* Unit */
                            th:nth-child(4), td:nth-child(4) { width: 10%; }  /* Kode Barang */
                            th:nth-child(5), td:nth-child(5) { width: 20%; }  /* Nama Barang */
                            th:nth-child(6), td:nth-child(6) { width: 8%; }   /* Stok Sistem */
                            th:nth-child(7), td:nth-child(7) { width: 8%; }   /* Stok Fisik */
                            th:nth-child(8), td:nth-child(8) { width: 8%; }   /* Selisih */
                            th:nth-child(9), td:nth-child(9) { width: 15%; }  /* Keterangan */
                            th:nth-child(10), td:nth-child(10) { width: 10%; } /* Status */
                            
                            .footer {
                                margin-top: 15px;
                                font-size: 8px;
                                text-align: center;
                                color: #666;
                            }
                            
                            .page-break {
                                page-break-before: always;
                            }
                            
                            /* Zebra stripes untuk readability */
                            tbody tr:nth-child(even) {
                                background-color: #f9f9f9;
                            }
                            
                            .status-badge {
                                padding: 2px 6px;
                                border-radius: 3px;
                                font-size: 8px;
                                font-weight: bold;
                            }
                            
                            .status-pending {
                                background-color: #ffc107;
                                color: #000;
                            }
                            
                            .status-success {
                                background-color: #28a745;
                                color: #fff;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>LAPORAN STOK OPNAME</h1>
                            <div class="header-info">
                                <div class="left">
                                    <p><strong>Bulan:</strong> ${formatBulan($('#bulan').val())}</p>
                                    <p><strong>Unit:</strong> ${$('#unit option:selected').text()}</p>
                                </div>
                                <div class="right">
                                    <p><strong>Tanggal Cetak:</strong> ${new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                    <p><strong>Jumlah Data:</strong> ${data.length} item</p>
                                </div>
                            </div>
                        </div>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Kode Barang</th>
                                    <th class="text-center">Nama Barang</th>
                                    <th class="text-center">Stok Sistem</th>
                                    <th class="text-center">Stok Fisik</th>
                                    <th class="text-center">Selisih</th>
                                    <th class="text-center">Keterangan</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>`;

                // Render data
                data.forEach((row, index) => {
                    const stockSistem = parseFloat(row.stock_sistem) || 0;
                    const stockFisik = row.status === "pending" ? 0 : parseFloat(row.stock_fisik) || 0;
                    const selisih = row.status === "pending" ? 0 : stockFisik - stockSistem;
                    
                    totalSistem += stockSistem;
                    totalFisik += stockFisik;
                    totalSelisih += selisih;
                    
                    // Format selisih dengan warna
                    let selisihClass = "zero";
                    let selisihText = "-";
                    if (row.status !== "pending") {
                        if (selisih > 0) {
                            selisihClass = "positive";
                            selisihText = `+${selisih.toLocaleString('id-ID')}`;
                        } else if (selisih < 0) {
                            selisihClass = "negative";
                            selisihText = `${selisih.toLocaleString('id-ID')}`;
                        } else {
                            selisihClass = "zero";
                            selisihText = "0";
                        }
                    }
                    
                    // Format status
                    const statusClass = row.status === "pending" ? "status-pending" : "status-success";
                    const statusText = row.status === "pending" ? "Pending" : "Sukses";
                    
                    printHTML += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${row.tgl_opname || '-'}</td>
                            <td class="text-center">${row.unit || '-'}</td>
                            <td class="text-center">${row.kode_barang || '-'}</td>
                            <td class="text-left">${row.nama_barang || '-'}</td>
                            <td class="text-right">${stockSistem.toLocaleString('id-ID')}</td>
                            <td class="text-right">${row.status === "pending" ? "-" : stockFisik.toLocaleString('id-ID')}</td>
                            <td class="text-right ${selisihClass}">${selisihText}</td>
                            <td class="text-left">${row.keterangan || '-'}</td>
                            <td class="text-center">
                                <span class="status-badge ${statusClass}">${statusText}</span>
                            </td>
                        </tr>`;
                    
                    // Tambahkan page break setiap 40 baris
                    if ((index + 1) % 40 === 0 && index < data.length - 1) {
                        printHTML += `
                            <tr class="page-break">
                                <td colspan="10"></td>
                            </tr>`;
                    }
                });

                // Tambahkan baris total
                printHTML += `
                            </tbody>
                            <tfoot>
                                <tr class="grand-total-row grand-total">
                                    <td colspan="5" class="text-right bold">TOTAL:</td>
                                    <td class="text-right bold">${totalSistem.toLocaleString('id-ID')}</td>
                                    <td class="text-right bold">${totalFisik.toLocaleString('id-ID')}</td>
                                    <td class="text-right bold">${totalSelisih > 0 ? '+' : ''}${totalSelisih.toLocaleString('id-ID')}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="footer">
                            <p>Rekapitulasi: Stok Sistem: ${totalSistem.toLocaleString('id-ID')} | Stok Fisik: ${totalFisik.toLocaleString('id-ID')} | Selisih: ${totalSelisih > 0 ? '+' : ''}${totalSelisih.toLocaleString('id-ID')}</p>
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

            function formatBulan(bulanString) {
                if (!bulanString) return '-';
                const [tahun, bulan] = bulanString.split('-');
                const bulanNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return `${bulanNames[parseInt(bulan) - 1]} ${tahun}`;
            }

            table = $('#tbstokopname').DataTable({
                ordering: true,
                order: [[1, 'desc']],
                pageLength: 50,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "All"]],
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
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        orderable: false,
                        className: "text-center"
                    },
                    { 
                        data: "tgl_opname",
                        className: "text-center"
                    },
                    { 
                        data: "unit",
                        className: "text-center"
                    },
                    { 
                        data: "kode_barang",
                        className: "text-center"
                    },
                    { 
                        data: "nama_barang",
                        className: "text-left"
                    },
                    { 
                        data: "stock_sistem", 
                        className: "text-end",
                        render: function(data) {
                            return parseFloat(data || 0).toLocaleString('id-ID');
                        }
                    },
                    { 
                        data: null, 
                        className: "text-end", 
                        render: function(data){
                            if (data.status === "pending") {
                                return "<span class='text-muted'>-</span>";
                            }
                            return parseFloat(data.stock_fisik || 0).toLocaleString('id-ID');
                        }
                    },
                    { 
                        data: null, 
                        className: "text-end", 
                        render: function(data){
                            if (data.status === "pending") {
                                return "<span class='text-muted'>-</span>";
                            }
                            const selisih = parseFloat(data.stock_fisik || 0) - parseFloat(data.stock_sistem || 0);
                            let className = "text-muted";
                            let prefix = "";
                            
                            if (selisih > 0) {
                                className = "text-success fw-bold";
                                prefix = "+";
                            } else if (selisih < 0) {
                                className = "text-danger fw-bold";
                            }
                            
                            return `<span class="${className}">${prefix}${selisih.toLocaleString('id-ID')}</span>`;
                        }
                    },
                    { 
                        data: "keterangan",
                        className: "text-left"
                    },
                    { 
                        data: null, 
                        render: function(data){
                            if (data.status === "pending") {
                                return '<span class="badge bg-warning text-dark">Pending</span>';
                            }
                            return '<span class="badge bg-success">Sukses</span>';
                        },
                        className: "text-center"
                    }
                ],
                drawCallback: function(settings) {
                    // Hitung total dari data yang ditampilkan di halaman
                    let api = this.api();
                    let totalSistem = 0;
                    let totalFisik = 0;
                    let totalSelisih = 0;
                    
                    api.rows({ page: 'current' }).data().each(function(row) {
                        const stockSistem = parseFloat(row.stock_sistem) || 0;
                        const stockFisik = row.status === "pending" ? 0 : parseFloat(row.stock_fisik) || 0;
                        const selisih = row.status === "pending" ? 0 : stockFisik - stockSistem;
                        
                        totalSistem += stockSistem;
                        totalFisik += stockFisik;
                        totalSelisih += selisih;
                    });
                    
                    // Update total di footer
                    $('#total-sistem').text(totalSistem.toLocaleString('id-ID'));
                    $('#total-fisik').text(totalFisik.toLocaleString('id-ID'));
                    $('#total-selisih').html(`<span class="${totalSelisih > 0 ? 'text-success fw-bold' : totalSelisih < 0 ? 'text-danger fw-bold' : 'text-muted'}">
                        ${totalSelisih > 0 ? '+' : ''}${totalSelisih.toLocaleString('id-ID')}
                    </span>`);
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
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                                page: 'all'
                            }
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row c', sheet).attr('s', '45');
                        }
                    },
                    {
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-warning btn-sm',
                        action: function(e, dt, node, config) {
                            printReport();
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>