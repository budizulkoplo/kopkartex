<x-app-layout>
    <x-slot name="pagetitle">Laporan Modal Awal</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Modal Awal</h3>
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
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calculator"></i> 
                            Data Modal Awal Periode <span id="periode-display">{{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }}</span>
                        </h5>
                        <div>
                            <button onclick="exportExcel()" class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-excel"></i> Export Excel
                            </button>
                            <button onclick="printReport()" class="btn btn-warning btn-sm">
                                <i class="bi bi-printer"></i> Print
                            </button>
                            <button onclick="table.ajax.reload()" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-repeat"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-info-circle"></i> 
                                    Total Barang: <strong id="total-barang">0</strong> item
                                </div>
                                <div>
                                    <strong>Total Stok Awal:</strong> <span id="total-stok-awal">0</span>
                                </div>
                                <div>
                                    <strong>Total Modal Awal:</strong> <span id="total-modal-awal">Rp 0</span>
                                </div>
                                <div>
                                    <strong>Total Stok Realtime:</strong> <span id="total-stok-realtime">0</span>
                                </div>
                                <div>
                                    <strong>Total Modal Realtime:</strong> <span id="total-modal-realtime">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tbmodalawal" class="table table-sm table-striped table-bordered" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Periode</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th class="text-end">Harga Modal</th>
                                    <th>Unit</th>
                                    <th class="text-end">Stok Awal</th>
                                    <th class="text-end">Nilai Awal</th>
                                    <th class="text-end">Stok Realtime</th>
                                    <th class="text-end">Nilai Realtime</th>
                                    <th class="text-end">Selisih Qty</th>
                                    <th class="text-end">Selisih Nominal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="table-primary fw-bold">
                                <tr>
                                    <td colspan="7" class="text-end">TOTAL:</td>
                                    <td id="footer-stok-awal" class="text-end">0</td>
                                    <td id="footer-modal-awal" class="text-end">Rp 0</td>
                                    <td id="footer-stok-realtime" class="text-end">0</td>
                                    <td id="footer-modal-realtime" class="text-end">Rp 0</td>
                                    <td id="footer-selisih-stok" class="text-end">0</td>
                                    <td id="footer-selisih-modal" class="text-end">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Print Content --}}
    <div id="printArea" style="display: none;"></div>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            let table;

            function reloadTable() {
                table.ajax.reload();
                loadTotals();
            }

            function loadTotals() {
                $.ajax({
                    url: "{{ route('laporan.modalawal.data') }}",
                    type: "GET",
                    data: { 
                        bulan: $('#bulan').val(),
                        unit: $('#unit').val()
                    },
                    success: function(response) {
                        if (response.totals) {
                            $('#total-barang').text(response.data.length);
                            $('#total-stok-awal').text(formatNumber(response.totals.total_stok_awal));
                            $('#total-modal-awal').text('Rp ' + formatNumber(response.totals.total_modal_awal, 2));
                            $('#total-stok-realtime').text(formatNumber(response.totals.total_stok_realtime));
                            $('#total-modal-realtime').text('Rp ' + formatNumber(response.totals.total_modal_realtime, 2));
                            $('#footer-stok-awal').text(formatNumber(response.totals.total_stok_awal));
                            $('#footer-modal-awal').text('Rp ' + formatNumber(response.totals.total_modal_awal, 2));
                            $('#footer-stok-realtime').text(formatNumber(response.totals.total_stok_realtime));
                            $('#footer-modal-realtime').text('Rp ' + formatNumber(response.totals.total_modal_realtime, 2));
                            $('#footer-selisih-stok').text(formatNumber(response.totals.total_selisih_stok, 3));
                            $('#footer-selisih-modal').text('Rp ' + formatNumber(response.totals.total_selisih_nominal, 2));
                        }
                        
                        // Update periode display
                        const bulan = $('#bulan').val();
                        const [tahun, bulanNum] = bulan.split('-');
                        const bulanNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $('#periode-display').text(bulanNames[parseInt(bulanNum) - 1] + ' ' + tahun);
                    }
                });
            }

            function formatNumber(value, decimals = 0) {
                if (value === null || value === undefined) return '0';
                return parseFloat(value).toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }

            function printReport() {
                $.ajax({
                    url: "{{ route('laporan.modalawal.data') }}",
                    type: "GET",
                    data: { 
                        bulan: $('#bulan').val(),
                        unit: $('#unit').val()
                    },
                    success: function(response) {
                        if (response.data && response.data.length > 0) {
                            generatePrintContent(response.data, response.totals);
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tidak Ada Data',
                                text: 'Tidak ada data untuk dicetak!'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal mengambil data untuk dicetak!'
                        });
                    }
                });
            }

            function generatePrintContent(data, totals) {
                const unitText = $('#unit option:selected').text();
                const bulanText = $('#periode-display').text();
                
                let printHTML = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Laporan Modal Awal</title>
                        <style>
                            * { margin: 0; padding: 0; box-sizing: border-box; }
                            body { font-family: 'Arial', sans-serif; font-size: 11px; line-height: 1.4; color: #000; padding: 15px; background: white; }
                            @page { size: A4 landscape; margin: 10mm; }
                            @media print { body { padding: 0; margin: 0; } }
                            
                            .header { text-align: center; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #000; }
                            .header h1 { font-size: 18px; margin-bottom: 5px; }
                            .header-info { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 10px; }
                            
                            table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
                            th { background-color: #f2f2f2 !important; color: #000 !important; border: 1px solid #000 !important; padding: 6px 3px; font-weight: bold; text-align: center; -webkit-print-color-adjust: exact; }
                            td { border: 1px solid #000 !important; padding: 4px 3px; }
                            .text-right { text-align: right; }
                            .text-center { text-align: center; }
                            .text-left { text-align: left; }
                            .bold { font-weight: bold; }
                            
                            .grand-total-row { background-color: #d1e7ff !important; -webkit-print-color-adjust: exact; }
                            .grand-total-row td { border-top: 3px double #000 !important; font-weight: bold; }
                            
                            /* Column widths */
                            th:nth-child(1), td:nth-child(1) { width: 3%; }
                            th:nth-child(2), td:nth-child(2) { width: 8%; }
                            th:nth-child(3), td:nth-child(3) { width: 10%; }
                            th:nth-child(4), td:nth-child(4) { width: 20%; }
                            th:nth-child(5), td:nth-child(5) { width: 5%; }
                            th:nth-child(6), td:nth-child(6) { width: 10%; }
                            th:nth-child(7), td:nth-child(7) { width: 10%; }
                            th:nth-child(8), td:nth-child(8) { width: 8%; }
                            th:nth-child(9), td:nth-child(9) { width: 12%; }
                            
                            .footer { margin-top: 15px; font-size: 9px; text-align: center; color: #666; }
                            .page-break { page-break-before: always; }
                            tbody tr:nth-child(even) { background-color: #f9f9f9; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>LAPORAN MODAL AWAL</h1>
                            <div class="header-info">
                                <div class="left">
                                    <p><strong>Periode:</strong> ${bulanText}</p>
                                    <p><strong>Unit:</strong> ${unitText}</p>
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
                                    <th class="text-center">Periode</th>
                                    <th class="text-center">Kode Barang</th>
                                    <th class="text-center">Nama Barang</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-center">Harga Modal</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Stok Awal</th>
                                    <th class="text-center">Nilai Awal</th>
                                    <th class="text-center">Stok Realtime</th>
                                    <th class="text-center">Nilai Realtime</th>
                                    <th class="text-center">Selisih Qty</th>
                                    <th class="text-center">Selisih Nominal</th>
                                </tr>
                            </thead>
                            <tbody>`;

                data.forEach((row, index) => {
                    printHTML += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${row.periode || '-'}</td>
                            <td class="text-left">${row.kode_barang || '-'}</td>
                            <td class="text-left">${row.nama_barang || '-'}</td>
                            <td class="text-center">${row.satuan || '-'}</td>
                            <td class="text-right">${formatNumber(row.harga_modal, 2)}</td>
                            <td class="text-left">${row.unit || '-'}</td>
                            <td class="text-right">${formatNumber(row.stok_awal, 3)}</td>
                            <td class="text-right">${formatNumber(row.nilai_modal_awal, 2)}</td>
                            <td class="text-right">${formatNumber(row.stok_realtime, 3)}</td>
                            <td class="text-right">${formatNumber(row.nilai_realtime, 2)}</td>
                            <td class="text-right">${formatNumber(row.selisih_stok, 3)}</td>
                            <td class="text-right">${formatNumber(row.selisih_nominal, 2)}</td>
                        </tr>`;
                    
                    if ((index + 1) % 40 === 0 && index < data.length - 1) {
                        printHTML += `<tr class="page-break"><td colspan="9"></td></tr>`;
                    }
                });

                printHTML += `
                            </tbody>
                            <tfoot>
                                <tr class="grand-total-row">
                                    <td colspan="7" class="text-right bold">TOTAL</td>
                                    <td class="text-right bold">${formatNumber(totals.total_stok_awal, 3)}</td>
                                    <td class="text-right bold">${formatNumber(totals.total_modal_awal, 2)}</td>
                                    <td class="text-right bold">${formatNumber(totals.total_stok_realtime, 3)}</td>
                                    <td class="text-right bold">${formatNumber(totals.total_modal_realtime, 2)}</td>
                                    <td class="text-right bold">${formatNumber(totals.total_selisih_stok, 3)}</td>
                                    <td class="text-right bold">${formatNumber(totals.total_selisih_nominal, 2)}</td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="footer">
                            <p>Total Modal Awal: Rp ${formatNumber(totals.total_modal_awal, 2)} | Total Modal Realtime: Rp ${formatNumber(totals.total_modal_realtime, 2)}</p>
                            <p>Dicetak dari Sistem pada ${new Date().toLocaleTimeString('id-ID')}</p>
                        </div>
                    </body>
                    </html>`;

                let printWindow = window.open('', '_blank', 'width=1200,height=800');
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

            function exportExcel() {
                const bulan = $('#bulan').val();
                const unit = $('#unit').val();
                window.location.href = "{{ route('laporan.modalawal.export') }}?bulan=" + bulan + "&unit=" + unit;
            }

            table = $('#tbmodalawal').DataTable({
                ordering: true,
                order: [[2, 'asc']], // Order by kode barang
                pageLength: 50,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "All"]],
                responsive: true,
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('laporan.modalawal.data') }}",
                    type: "GET",
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                        d.unit = $('#unit').val();
                    },
                    dataSrc: function(response) {
                        // Update totals
                        if (response.totals) {
                            $('#total-barang').text(response.data.length);
                            $('#total-stok-awal').text(formatNumber(response.totals.total_stok_awal));
                            $('#total-modal-awal').text('Rp ' + formatNumber(response.totals.total_modal_awal, 2));
                            $('#total-stok-realtime').text(formatNumber(response.totals.total_stok_realtime));
                            $('#total-modal-realtime').text('Rp ' + formatNumber(response.totals.total_modal_realtime, 2));
                            $('#footer-stok-awal').text(formatNumber(response.totals.total_stok_awal));
                            $('#footer-modal-awal').text('Rp ' + formatNumber(response.totals.total_modal_awal, 2));
                            $('#footer-stok-realtime').text(formatNumber(response.totals.total_stok_realtime));
                            $('#footer-modal-realtime').text('Rp ' + formatNumber(response.totals.total_modal_realtime, 2));
                            $('#footer-selisih-stok').text(formatNumber(response.totals.total_selisih_stok, 3));
                            $('#footer-selisih-modal').text('Rp ' + formatNumber(response.totals.total_selisih_nominal, 2));
                        }
                        return response.data;
                    }
                },
                columns: [
                    { 
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        className: "text-center"
                    },
                    { 
                        data: "periode",
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
                    { 
                        data: "satuan",
                        className: "text-center",
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    { 
                        data: "harga_modal",
                        className: "text-end",
                        render: function(data) {
                            return formatNumber(data, 2);
                        }
                    },
                    { 
                        data: "unit",
                        className: "text-left",
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    { 
                        data: "stok_awal",
                        className: "text-end",
                        render: function(data) {
                            return formatNumber(data, 3);
                        }
                    },
                    { 
                        data: "nilai_modal_awal",
                        className: "text-end",
                        render: function(data) {
                            return formatNumber(data, 2);
                        }
                    },
                    {
                        data: "stok_realtime",
                        className: "text-end",
                        render: function(data) {
                            return formatNumber(data, 3);
                        }
                    },
                    {
                        data: "nilai_realtime",
                        className: "text-end",
                        render: function(data) {
                            return formatNumber(data, 2);
                        }
                    },
                    {
                        data: "selisih_stok",
                        className: "text-end",
                        render: function(data) {
                            return formatNumber(data, 3);
                        }
                    },
                    {
                        data: "selisih_nominal",
                        className: "text-end",
                        render: function(data) {
                            return formatNumber(data, 2);
                        }
                    }
                ],
                dom:
                    "<'row mb-2'<'col-md-6 d-flex align-items-center'B><'col-md-6 d-flex justify-content-end'f>>" +
                    "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'i>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-md-6'i><'col-md-6 d-flex justify-content-end'p>>",
                
            });

            // Load totals on page load
            $(document).ready(function() {
                loadTotals();
                
                // Update periode display on bulan change
                $('#bulan').on('change', function() {
                    const bulan = $(this).val();
                    const [tahun, bulanNum] = bulan.split('-');
                    const bulanNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    $('#periode-display').text(bulanNames[parseInt(bulanNum) - 1] + ' ' + tahun);
                });
            });
        </script>
    </x-slot>
</x-app-layout>
