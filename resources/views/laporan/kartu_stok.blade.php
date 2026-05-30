<x-app-layout>
    <x-slot name="pagetitle">Laporan Kartu Stok</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Kartu Stok</h3>
                    <small class="text-muted">Daftar barang dengan riwayat mutasi stok</small>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline mb-3">
                <div class="card-body">
                    <form id="filterForm" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control form-control-sm" value="{{ $tanggal_awal }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control form-control-sm" value="{{ $tanggal_akhir }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select form-select-sm">
                                <option value="">Semua Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((string) $unit_id === (string) $unit->id)>{{ $unit->nama_unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jenis</label>
                            <select name="jenis_transaksi" id="jenis_transaksi" class="form-select form-select-sm">
                                <option value="">Semua Jenis</option>
                                @foreach($jenisOptions as $jenis)
                                    <option value="{{ $jenis }}" @selected($jenis_transaksi === $jenis)>{{ ucwords(str_replace('_', ' ', $jenis)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cari</label>
                            <input type="text" name="keyword" id="keyword" class="form-control form-control-sm" value="{{ $keyword }}" placeholder="Kode, nama barang, referensi">
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Barang</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="barangKartuStokTable" class="table table-bordered table-striped table-sm align-middle w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Unit</th>
                                    <th class="text-end">Stok Sekarang</th>
                                    <th class="text-end">Total Masuk</th>
                                    <th class="text-end">Total Keluar</th>
                                    <th class="text-end">Jumlah Mutasi</th>
                                    <th>Transaksi Terakhir</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="kartuStokModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title mb-0">Riwayat Kartu Stok</h5>
                        <small id="modalSubtitle"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="riwayatKartuStokTable" class="table table-bordered table-striped table-sm align-middle w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Referensi</th>
                                    <th class="text-end">Saldo Awal</th>
                                    <th class="text-end">Masuk</th>
                                    <th class="text-end">Keluar</th>
                                    <th class="text-end">Saldo Akhir</th>
                                    <th>User</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            $(function () {
                let selectedBarangId = null;
                let selectedUnitId = null;
                let historyTable = null;

                function filterData() {
                    return {
                        tanggal_awal: $('#tanggal_awal').val(),
                        tanggal_akhir: $('#tanggal_akhir').val(),
                        unit_id: $('#unit_id').val(),
                        jenis_transaksi: $('#jenis_transaksi').val(),
                        keyword: $('#keyword').val()
                    };
                }

                const itemTable = $('#barangKartuStokTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    order: [[2, 'asc']],
                    ajax: {
                        url: '{{ route('laporan.kartu_stok.data') }}',
                        data: function (d) {
                            Object.assign(d, filterData());
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'kode_barang', name: 'b.kode_barang' },
                        { data: 'nama_barang', name: 'b.nama_barang' },
                        { data: 'nama_unit', name: 'u.nama_unit' },
                        { data: 'stok_sekarang', name: 'stok_sekarang', className: 'text-end' },
                        { data: 'total_masuk', name: 'total_masuk', className: 'text-end text-success' },
                        { data: 'total_keluar', name: 'total_keluar', className: 'text-end text-danger' },
                        { data: 'jumlah_mutasi', name: 'jumlah_mutasi', className: 'text-end' },
                        { data: 'transaksi_terakhir', name: 'transaksi_terakhir' },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
                    ],
                    language: {
                        emptyTable: 'Belum ada data kartu stok untuk filter ini.'
                    }
                });

                function ensureHistoryTable() {
                    if (historyTable) {
                        historyTable.ajax.url(`{{ url('/laporan/kartu-stok/history') }}/${selectedBarangId}`).load();
                        return;
                    }

                    historyTable = $('#riwayatKartuStokTable').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        order: [[1, 'asc']],
                        ajax: {
                            url: `{{ url('/laporan/kartu-stok/history') }}/${selectedBarangId}`,
                            data: function (d) {
                                const filters = filterData();
                                d.tanggal_awal = filters.tanggal_awal;
                                d.tanggal_akhir = filters.tanggal_akhir;
                                d.unit_id = selectedUnitId || filters.unit_id;
                                d.jenis_transaksi = filters.jenis_transaksi;
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                            { data: 'tanggal', name: 'ks.tanggal' },
                            { data: 'jenis_transaksi', name: 'ks.jenis_transaksi' },
                            { data: 'nomor_referensi', name: 'ks.nomor_referensi', defaultContent: '-' },
                            { data: 'saldo_awal', name: 'ks.saldo_awal', className: 'text-end' },
                            { data: 'qty_masuk', name: 'ks.qty_masuk', className: 'text-end text-success' },
                            { data: 'qty_keluar', name: 'ks.qty_keluar', className: 'text-end text-danger' },
                            { data: 'saldo_akhir', name: 'ks.saldo_akhir', className: 'text-end fw-bold' },
                            { data: 'user_name', name: 'usr.name' },
                            { data: 'keterangan', name: 'ks.keterangan', defaultContent: '-' },
                        ],
                        language: {
                            emptyTable: 'Belum ada riwayat untuk barang ini.'
                        }
                    });
                }

                $('#filterForm').on('submit', function (event) {
                    event.preventDefault();
                    itemTable.ajax.reload();
                });

                $(document).on('click', '.btn-kartu-stok', function () {
                    selectedBarangId = $(this).data('barang-id');
                    selectedUnitId = $(this).data('unit-id');

                    $('#modalSubtitle').text(`${$(this).data('barang')} | ${$(this).data('unit')}`);
                    $('#kartuStokModal').modal('show');

                    setTimeout(function () {
                        ensureHistoryTable();
                    }, 150);
                });
            });
        </script>
    </x-slot>
</x-app-layout>
