<x-app-layout>
    <x-slot name="pagetitle">Laporan Stok Detail</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Stok Detail</h3>
                    <small class="text-muted">Unit login: {{ $unit->nama_unit ?? $unit->name ?? '-' }}</small>
                </div>
                <div class="col-sm-6 text-end">
                    <form method="GET" action="{{ route('laporan.stokdetail') }}" class="row g-2 justify-content-end">
                        <div class="col-auto">
                            <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $bulan }}">
                        </div>
                        <div class="col-auto">
                            <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Kode / nama barang" value="{{ $keyword }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-success">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ringkasan Stok Per Barang</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm" style="font-size: small;">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th class="text-end">Stok Awal</th>
                                    <th class="text-end">Penerimaan</th>
                                    <th class="text-end">Retur</th>
                                    <th class="text-end">Penjualan</th>
                                    <th class="text-end">Adjustment</th>
                                    <th class="text-end">Stok Hitung</th>
                                    <th class="text-end">Stok Sistem</th>
                                    <th class="text-end">Selisih</th>
                                    <th class="text-end">Nilai Hitung</th>
                                    <th class="text-end">Nilai Sistem</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $index => $row)
                                    <tr>
                                        <td>{{ ($rows->currentPage() - 1) * $rows->perPage() + $index + 1 }}</td>
                                        <td>{{ $row['kode_barang'] }}</td>
                                        <td>{{ $row['nama_barang'] }}</td>
                                        <td>{{ $row['satuan'] }}</td>
                                        <td class="text-end">{{ number_format($row['opening_stock']) }}</td>
                                        <td class="text-end text-success">{{ number_format($row['penerimaan_qty'], 3, ',', '.') }}</td>
                                        <td class="text-end text-danger">{{ number_format($row['retur_qty'], 3, ',', '.') }}</td>
                                        <td class="text-end text-danger">{{ number_format($row['penjualan_qty'], 3, ',', '.') }}</td>
                                        <td class="text-end {{ $row['adjustment_qty'] >= 0 ? 'text-primary' : 'text-danger' }}">{{ number_format($row['adjustment_qty'], 3, ',', '.') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($row['calculated_stock']) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($row['system_stock']) }}</td>
                                        <td class="text-end {{ $row['selisih'] == 0 ? 'text-success' : 'text-danger fw-bold' }}">{{ number_format($row['selisih']) }}</td>
                                        <td class="text-end">Rp {{ number_format($row['nominal_calculated'], 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($row['nominal_system'], 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary btn-detail-stok" data-id="{{ $row['barang_id'] }}">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center text-muted py-4">Tidak ada data stok untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-primary fw-bold">
                                    <td colspan="4" class="text-end">TOTAL</td>
                                    <td class="text-end">{{ number_format($totals['opening_stock']) }}</td>
                                    <td class="text-end">{{ number_format($totals['penerimaan_qty'], 3, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totals['retur_qty'], 3, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totals['penjualan_qty'], 3, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totals['adjustment_qty'], 3, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totals['calculated_stock']) }}</td>
                                    <td class="text-end">{{ number_format($totals['system_stock']) }}</td>
                                    <td class="text-end">{{ number_format($totals['selisih']) }}</td>
                                    <td class="text-end">Rp {{ number_format($totals['nominal_calculated'], 2, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($totals['nominal_system'], 2, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($rows->hasPages())
                        <div class="mt-3">
                            {{ $rows->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="stokDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">Riwayat Stok Detail</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td width="35%"><strong>Barang</strong></td><td>: <span id="histBarang"></span></td></tr>
                                <tr><td><strong>Satuan</strong></td><td>: <span id="histSatuan"></span></td></tr>
                                <tr><td><strong>Bulan</strong></td><td>: <span id="histBulan"></span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td width="35%"><strong>Stok Awal</strong></td><td>: <span id="histAwal"></span></td></tr>
                                <tr><td><strong>Stok Hitung</strong></td><td>: <span id="histHitung"></span></td></tr>
                                <tr><td><strong>Stok Sistem</strong></td><td>: <span id="histSistem"></span></td></tr>
                                <tr><td><strong>Selisih</strong></td><td>: <span id="histSelisih"></span></td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Referensi</th>
                                    <th class="text-end">Masuk</th>
                                    <th class="text-end">Keluar</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="stokHistoryBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            $(document).on('click', '.btn-detail-stok', function() {
                const barangId = $(this).data('id');
                const bulan = @json($bulan);

                $.get(`{{ url('/laporan/stok-detail/history') }}/${barangId}`, { bulan }, function(response) {
                    $('#histBarang').text(response.header.barang);
                    $('#histSatuan').text(response.header.satuan);
                    $('#histBulan').text(response.header.bulan);
                    $('#histAwal').text(response.header.stok_awal);
                    $('#histHitung').text(response.header.stok_hitung);
                    $('#histSistem').text(response.header.stok_sistem);
                    $('#histSelisih').text(response.header.selisih);

                    let rows = '';
                    response.details.forEach((detail, index) => {
                        rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${detail.tanggal}</td>
                                <td>${detail.jenis}</td>
                                <td>${detail.referensi}</td>
                                <td class="text-end">${new Intl.NumberFormat('id-ID').format(detail.qty_masuk)}</td>
                                <td class="text-end">${new Intl.NumberFormat('id-ID').format(detail.qty_keluar)}</td>
                                <td class="text-end fw-bold">${new Intl.NumberFormat('id-ID').format(detail.saldo)}</td>
                                <td>${detail.keterangan ?? '-'}</td>
                            </tr>
                        `;
                    });

                    $('#stokHistoryBody').html(rows || '<tr><td colspan="8" class="text-center text-muted">Tidak ada riwayat.</td></tr>');
                    new bootstrap.Modal(document.getElementById('stokDetailModal')).show();
                }).fail(function() {
                    Swal.fire('Error', 'Gagal memuat riwayat stok detail.', 'error');
                });
            });
        </script>
    </x-slot>
</x-app-layout>
