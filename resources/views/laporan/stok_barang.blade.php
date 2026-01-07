<x-app-layout>
    <x-slot name="pagetitle">Laporan Stok</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Stok Barang</h3>
                </div>
                
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    
                    <table id="tbstok" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                @foreach($units as $id => $nama)
                                    <th>{{ $nama }}</th>
                                @endforeach
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td colspan="3" class="text-end">GRAND TOTAL:</td>
                                @foreach($units as $id => $nama)
                                    <td id="total-{{ $id }}" class="text-end">0</td>
                                @endforeach
                                <td id="grand-total" class="text-end">0</td>
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
        {{-- JSZip untuk Excel --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            let table;

            function printReport() {
                // Ambil semua data dari server (bukan hanya halaman yang ditampilkan)
                $.ajax({
                    url: "{{ route('laporan.stokbarang.data') }}",
                    type: "GET",
                    data: { all: true }, // Parameter untuk mengambil semua data
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
                // Hitung lebar kolom unit secara dinamis
                const unitCount = {{ count($units) }};
                const unitWidthPercentage = unitCount > 0 ? Math.floor(55 / unitCount) : 0;
                
                // Buat konten print yang bersih
                let printHTML = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Laporan Stok Barang</title>
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
                            
                            .header p {
                                margin: 2px 0;
                                font-size: 10px;
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
                            
                            /* Column widths */
                            th:nth-child(1), td:nth-child(1) { width: 3%; }   /* No */
                            th:nth-child(2), td:nth-child(2) { width: 8%; }   /* Kode Barang */
                            th:nth-child(3), td:nth-child(3) { width: 20%; }  /* Nama Barang */
                `;
                
                // Tambahkan lebar untuk setiap kolom unit
                printHTML += `
                            /* Unit columns */
                `;
                
                @foreach($units as $id => $nama)
                printHTML += `
                            th:nth-child({{ $loop->index + 4 }}), td:nth-child({{ $loop->index + 4 }}) { 
                                width: ${unitWidthPercentage}%; 
                            }   /* {{ $nama }} */
                `;
                @endforeach
                
                printHTML += `
                            /* Total column */
                            th:nth-child(${unitCount + 4}), td:nth-child(${unitCount + 4}) { 
                                width: 6%; 
                            }   /* Total */
                            
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
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>LAPORAN STOK BARANG</h1>
                            <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-left">Kode Barang</th>
                                    <th class="text-left">Nama Barang</th>
                `;
                
                // Header untuk unit
                @foreach($units as $id => $nama)
                printHTML += `
                                    <th class="text-center">{{ $nama }}</th>
                `;
                @endforeach
                
                printHTML += `
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>`;

                // Hitung total per unit dan grand total
                let unitTotals = {};
                @foreach($units as $id => $nama)
                    unitTotals[{{ $id }}] = 0;
                @endforeach
                let grandTotal = 0;

                // Render data
                data.forEach((row, index) => {
                    let rowTotal = 0;
                    let unitCells = '';
                    
                    @foreach($units as $id => $nama)
                        let qty{{ $id }} = parseFloat(row["{{ $nama }}"]) || 0;
                        unitTotals[{{ $id }}] += qty{{ $id }};
                        rowTotal += qty{{ $id }};
                        unitCells += `<td class="text-center">${qty{{ $id }}.toLocaleString('id-ID')}</td>`;
                    @endforeach
                    
                    grandTotal += rowTotal;
                    
                    printHTML += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-left">${row.kode_barang || ''}</td>
                            <td class="text-left">${row.nama_barang || ''}</td>
                            ${unitCells}
                            <td class="text-center fw-bold">${rowTotal.toLocaleString('id-ID')}</td>
                        </tr>`;
                    
                    // Tambahkan page break setiap 40 baris
                    if ((index + 1) % 40 === 0 && index < data.length - 1) {
                        printHTML += `
                            <tr class="page-break">
                                <td colspan="${unitCount + 4}"></td>
                            </tr>`;
                    }
                });

                // Tambahkan baris total
                printHTML += `
                            </tbody>
                            <tfoot>
                                <tr class="grand-total-row grand-total">
                                    <td colspan="3" class="text-right bold">TOTAL:</td>`;
                
                @foreach($units as $id => $nama)
                    printHTML += `<td class="text-center bold">${unitTotals[{{ $id }}].toLocaleString('id-ID')}</td>`;
                @endforeach
                
                printHTML += `
                                    <td class="text-center bold">${grandTotal.toLocaleString('id-ID')}</td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="footer">
                            <p>Jumlah Barang: ${data.length} item | Dicetak pada: ${new Date().toLocaleTimeString('id-ID')}</p>
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

            table = $('#tbstok').DataTable({
                ordering: true,
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 25,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "All"]],
                ajax: {
                    url: "{{ route('laporan.stokbarang.data') }}",
                    type: "GET",
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
                        data: "kode_barang",
                        className: "text-left" 
                    },
                    { 
                        data: "nama_barang",
                        className: "text-left" 
                    },
                    @foreach($units as $id => $nama)
                        {
                            data: "{{ $nama }}",
                            className: "text-center",
                            render: function(data) {
                                return parseFloat(data || 0).toLocaleString('id-ID');
                            }
                        },
                    @endforeach
                    {
                        data: null,
                        render: function(data) {
                            let total = 0;
                            @foreach($units as $id => $nama)
                                total += parseFloat(data["{{ $nama }}"] || 0);
                            @endforeach
                            return `<span class="fw-bold">${total.toLocaleString('id-ID')}</span>`;
                        },
                        className: "text-center fw-bold"
                    }
                ],
                drawCallback: function(settings) {
                    // Hitung total per kolom unit
                    let api = this.api();
                    let unitTotals = {};
                    
                    @foreach($units as $id => $nama)
                        unitTotals[{{ $id }}] = 0;
                    @endforeach
                    
                    // Hitung total dari data yang ditampilkan di halaman
                    api.rows({ page: 'current' }).data().each(function(row) {
                        @foreach($units as $id => $nama)
                            unitTotals[{{ $id }}] += parseFloat(row["{{ $nama }}"] || 0);
                        @endforeach
                    });
                    
                    // Update total di footer
                    @foreach($units as $id => $nama)
                        $('#total-{{ $id }}').text(unitTotals[{{ $id }}].toLocaleString('id-ID'));
                    @endforeach
                    
                    // Hitung grand total
                    let grandTotal = 0;
                    Object.values(unitTotals).forEach(total => {
                        grandTotal += total;
                    });
                    $('#grand-total').text(grandTotal.toLocaleString('id-ID'));
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