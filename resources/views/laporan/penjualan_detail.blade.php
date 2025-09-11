<x-app-layout>
    <x-slot name="pagetitle">Laporan Penjualan Detail</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penjualan Detail</h3>
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

                        <select id="metode" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all" @if($metode_bayar=='all') selected @endif>Semua Metode</option>
                            <option value="tunai" @if($metode_bayar=='tunai') selected @endif>Tunai</option>
                            <option value="cicilan" @if($metode_bayar=='cicilan') selected @endif>Cicilan</option>
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
                    <table id="tbpenjualan" class="table table-sm table table-bordered" style="width:100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Unit</th>
                                <th>Metode</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
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

    {{-- JS Custom --}}
    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>
        <script>
            let table;

            function reloadTable() {
                table.ajax.reload();
            }

            table = $('#tbpenjualan').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 50,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, 'All']],
                ajax: {
                    url: "{{ route('laporan.penjualan_detail.data') }}",
                    type: "GET",
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date   = $('#end_date').val();
                        d.unit       = $('#unit').val();
                        d.metode     = $('#metode').val();
                    }
                },
                columns: [
                    { data: "tanggal" },
                    { data: "nomor_invoice" },
                    { data: "customer" },
                    { data: "nama_unit" },
                    { data: "metode_bayar" },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    { data: "qty", className: "text-end", render: function(data) { return parseFloat(data).toLocaleString(); } },
                    { data: "harga", className: "text-end", render: function(data) { return parseFloat(data).toLocaleString(); } },
                    { data: "total", className: "text-end", render: function(data) { return parseFloat(data).toLocaleString(); } }
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
                        let total = parseFloat(row.total);
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
                            // Terapkan rowspan pada baris pertama kelompok
                            $(rows).eq(group.firstRowIndex).find('td:eq(0)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(1)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(2)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(3)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(4)').attr('rowspan', group.count).addClass('align-middle');
                            
                            // Sembunyikan kolom pada baris berikutnya dalam kelompok yang sama
                            for (let i = group.firstRowIndex + 1; i < group.firstRowIndex + group.count; i++) {
                                if ($(rows).eq(i).find('td').length > 5) {
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
                                <td colspan="2" class="text-end">Subtotal</td>
                                <td class="text-end">${group.total.toLocaleString()}</td>
                            </tr>`
                        );
                    }

                    // Render grand total di footer
                    $(api.table().footer()).html(
                        `<tr class="fw-bold bg-primary text-white">
                            <td colspan="7" class="text-end">Grand Total</td>
                            <td colspan="2" class="text-end">${data.length} transaksi</td>
                            <td class="text-end">${grandTotal.toLocaleString()}</td>
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
                                    if (column === 7 || column === 8 || column === 9) {
                                        return data.replace(/\./g, '');
                                    }
                                    return data;
                                }
                            }
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row c', sheet).attr('s', '50'); // Set tinggi row
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>