<x-app-layout>
    <x-slot name="pagetitle">Stock Adjustment</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Stock Adjustment</h3>
                    <small class="text-muted">Penyesuaian stok dan konversi satuan dengan audit trail lengkap</small>
                </div>
                <div class="col-sm-6 text-end">
                    <span class="badge bg-primary">Kode Draft: {{ $kode_adjustment }}</span>
                    <span class="badge bg-secondary">{{ $unit->name ?? 'Unit tidak ditemukan' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-warning card-outline mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Adjustment</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <ul class="mb-0 ps-3">
                            <li>Gunakan `Set Stok` untuk menetapkan stok akhir.</li>
                            <li>Gunakan `Tambah` atau `Kurangi` untuk koreksi kuantitas.</li>
                            <li>Gunakan `Konversi Satuan` untuk kasus seperti `1 liter -> 1000 mililiter` agar transaksi tidak memakai pecahan.</li>
                        </ul>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Adjustment</label>
                            <input type="datetime-local" class="form-control" id="tanggalAdjustment" value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Catatan Header</label>
                            <textarea class="form-control" id="headerNote" rows="2" placeholder="Contoh: konversi stok minyak goreng dari liter ke mililiter"></textarea>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="adjustmentTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="22%">Barang</th>
                                    <th width="10%">Stok Lama</th>
                                    <th width="10%">Satuan Lama</th>
                                    <th width="12%">Tipe</th>
                                    <th width="10%">Nilai</th>
                                    <th width="10%">Faktor</th>
                                    <th width="10%">Satuan Baru</th>
                                    <th width="10%">Stok Baru</th>
                                    <th>Catatan Detail</th>
                                    <th width="6%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-outline-primary" id="btnAddRow">
                            <i class="bi bi-plus-circle"></i> Tambah Item
                        </button>
                        <button type="button" class="btn btn-success" id="btnSaveAdjustment">
                            <i class="bi bi-save"></i> Simpan Adjustment
                        </button>
                    </div>
                </div>
            </div>

            <div class="card card-success card-outline">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0">Riwayat Adjustment</h5>
                        <form method="GET" action="{{ route('stockadjustment.index') }}" class="row g-2 align-items-center">
                            <div class="col-auto">
                                <input type="date" class="form-control form-control-sm" name="tanggal_awal" value="{{ $tanggal_awal }}">
                            </div>
                            <div class="col-auto">
                                <input type="date" class="form-control form-control-sm" name="tanggal_akhir" value="{{ $tanggal_akhir }}">
                            </div>
                            <div class="col-auto">
                                <input type="text" class="form-control form-control-sm" name="keyword" placeholder="Kode / catatan" value="{{ $keyword }}">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-success" type="submit">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="14%">Tanggal</th>
                                    <th width="14%">Kode</th>
                                    <th width="14%">User</th>
                                    <th width="14%">Unit</th>
                                    <th width="10%">Total Item</th>
                                    <th>Catatan</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adjustments as $index => $adjustment)
                                    <tr>
                                        <td>{{ ($adjustments->currentPage() - 1) * $adjustments->perPage() + $index + 1 }}</td>
                                        <td>{{ $adjustment->tanggal_adjustment?->format('d-m-Y H:i') }}</td>
                                        <td><span class="badge bg-warning text-dark">{{ $adjustment->kode_adjustment }}</span></td>
                                        <td>{{ $adjustment->user->name ?? '-' }}</td>
                                        <td>{{ $adjustment->unit->name ?? '-' }}</td>
                                        <td>{{ $adjustment->details->count() }} item</td>
                                        <td>{{ $adjustment->note ?? '-' }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary btn-detail-adjustment" data-id="{{ $adjustment->id }}">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Belum ada riwayat stock adjustment.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($adjustments->hasPages())
                        <div class="mt-3">
                            {{ $adjustments->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailAdjustmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Detail Stock Adjustment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td width="35%"><strong>Kode</strong></td><td>: <span id="detailKode"></span></td></tr>
                                <tr><td><strong>Tanggal</strong></td><td>: <span id="detailTanggal"></span></td></tr>
                                <tr><td><strong>User</strong></td><td>: <span id="detailUser"></span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td width="35%"><strong>Unit</strong></td><td>: <span id="detailUnit"></span></td></tr>
                                <tr><td><strong>Catatan</strong></td><td>: <span id="detailNote"></span></td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Barang</th>
                                    <th>Tipe</th>
                                    <th>Stok Lama</th>
                                    <th>Nilai</th>
                                    <th>Stok Baru</th>
                                    <th>Satuan Lama</th>
                                    <th>Satuan Baru</th>
                                    <th>Faktor</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody id="detailAdjustmentBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            const satuanOptions = @json($satuans->map(fn($satuan) => ['id' => $satuan->id, 'name' => $satuan->name])->values());
            let rowIndex = 0;

            function buildSatuanSelectOptions(selectedId = '') {
                let options = '<option value="">Pilih</option>';
                satuanOptions.forEach(option => {
                    const selected = String(option.id) === String(selectedId) ? 'selected' : '';
                    options += `<option value="${option.id}" ${selected}>${option.name}</option>`;
                });
                return options;
            }

            function addRow() {
                rowIndex += 1;
                const html = `
                    <tr data-row="${rowIndex}">
                        <td class="text-center row-number"></td>
                        <td>
                            <select class="form-select form-select-sm barang-select"></select>
                            <small class="text-muted kode-barang"></small>
                        </td>
                        <td><input type="number" class="form-control form-control-sm old-stock" readonly></td>
                        <td><input type="text" class="form-control form-control-sm old-satuan-name" readonly></td>
                        <td>
                            <select class="form-select form-select-sm adjustment-type">
                                <option value="set">Set Stok</option>
                                <option value="add">Tambah</option>
                                <option value="subtract">Kurangi</option>
                                <option value="convert">Konversi Satuan</option>
                            </select>
                        </td>
                        <td><input type="number" class="form-control form-control-sm adjustment-value" min="0" value="0"></td>
                        <td><input type="number" class="form-control form-control-sm conversion-factor" min="1" value="1" disabled></td>
                        <td>
                            <select class="form-select form-select-sm new-satuan" disabled>
                                ${buildSatuanSelectOptions()}
                            </select>
                        </td>
                        <td><input type="number" class="form-control form-control-sm new-stock" readonly></td>
                        <td><input type="text" class="form-control form-control-sm detail-note" placeholder="Opsional"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;

                $('#adjustmentTable tbody').append(html);
                initBarangSelect($(`tr[data-row="${rowIndex}"] .barang-select`));
                refreshRowNumbers();
            }

            function refreshRowNumbers() {
                $('#adjustmentTable tbody tr').each(function(index) {
                    $(this).find('.row-number').text(index + 1);
                });
            }

            function initBarangSelect(element) {
                element.select2({
                    width: '100%',
                    placeholder: 'Cari barang',
                    ajax: {
                        url: '{{ route('stockadjustment.getbarang') }}',
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term || '' }),
                        processResults: data => ({ results: data })
                    }
                });
            }

            function recalculateRow(row) {
                const oldStock = parseInt(row.find('.old-stock').val() || 0, 10);
                const type = row.find('.adjustment-type').val();
                const adjustmentValue = parseInt(row.find('.adjustment-value').val() || 0, 10);
                const factor = parseInt(row.find('.conversion-factor').val() || 1, 10);
                let newStock = oldStock;

                if (type === 'set') {
                    newStock = adjustmentValue;
                } else if (type === 'add') {
                    newStock = oldStock + adjustmentValue;
                } else if (type === 'subtract') {
                    newStock = Math.max(0, oldStock - adjustmentValue);
                } else if (type === 'convert') {
                    newStock = oldStock * Math.max(1, factor);
                }

                row.find('.new-stock').val(newStock);
            }

            function toggleConvertInputs(row) {
                const isConvert = row.find('.adjustment-type').val() === 'convert';
                row.find('.conversion-factor, .new-satuan').prop('disabled', !isConvert);
                row.find('.adjustment-value').prop('disabled', isConvert);

                if (isConvert) {
                    row.find('.adjustment-value').val(0);
                } else {
                    row.find('.conversion-factor').val(1);
                    row.find('.new-satuan').val('');
                }

                recalculateRow(row);
            }

            function collectItems() {
                const items = [];

                $('#adjustmentTable tbody tr').each(function() {
                    const row = $(this);
                    const barangId = row.find('.barang-select').val();
                    if (!barangId) {
                        return;
                    }

                    items.push({
                        barang_id: barangId,
                        adjustment_type: row.find('.adjustment-type').val(),
                        adjustment_value: row.find('.adjustment-value').prop('disabled') ? 0 : parseInt(row.find('.adjustment-value').val() || 0, 10),
                        conversion_factor: row.find('.conversion-factor').prop('disabled') ? null : parseInt(row.find('.conversion-factor').val() || 1, 10),
                        new_satuan_id: row.find('.new-satuan').prop('disabled') ? null : row.find('.new-satuan').val(),
                        note: row.find('.detail-note').val()
                    });
                });

                return items;
            }

            $(document).ready(function() {
                addRow();

                $('#btnAddRow').on('click', addRow);

                $(document).on('click', '.btn-remove-row', function() {
                    $(this).closest('tr').remove();
                    if ($('#adjustmentTable tbody tr').length === 0) {
                        addRow();
                    } else {
                        refreshRowNumbers();
                    }
                });

                $(document).on('change', '.barang-select', function(e) {
                    const data = $(this).select2('data')[0];
                    const row = $(this).closest('tr');
                    row.find('.kode-barang').text(data?.kode_barang || '');
                    row.find('.old-stock').val(data?.stok || 0);
                    row.find('.old-satuan-name').val(data?.satuan || '');
                    row.find('.new-satuan').val('');
                    recalculateRow(row);
                });

                $(document).on('change', '.adjustment-type', function() {
                    toggleConvertInputs($(this).closest('tr'));
                });

                $(document).on('input change', '.adjustment-value, .conversion-factor, .new-satuan', function() {
                    recalculateRow($(this).closest('tr'));
                });

                $('#btnSaveAdjustment').on('click', function() {
                    const items = collectItems();

                    if (items.length === 0) {
                        Swal.fire('Info', 'Tambahkan minimal satu item adjustment.', 'info');
                        return;
                    }

                    $.ajax({
                        url: '{{ route('stockadjustment.store') }}',
                        method: 'POST',
                        data: {
                            tanggal_adjustment: $('#tanggalAdjustment').val(),
                            note: $('#headerNote').val(),
                            items: items,
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            $('#btnSaveAdjustment').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                        },
                        success: function(response) {
                            Swal.fire('Berhasil', response.message, 'success').then(() => location.reload());
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || 'Gagal menyimpan stock adjustment.';
                            Swal.fire('Error', message, 'error');
                        },
                        complete: function() {
                            $('#btnSaveAdjustment').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan Adjustment');
                        }
                    });
                });

                $('.btn-detail-adjustment').on('click', function() {
                    const id = $(this).data('id');

                    $.get(`{{ url('/stock-adjustment/detail') }}/${id}`, function(response) {
                        $('#detailKode').text(response.header.kode_adjustment);
                        $('#detailTanggal').text(response.header.tanggal_adjustment);
                        $('#detailUser').text(response.header.user);
                        $('#detailUnit').text(response.header.unit);
                        $('#detailNote').text(response.header.note);

                        let rows = '';
                        response.details.forEach((detail, index) => {
                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${detail.barang}</td>
                                    <td>${detail.adjustment_type}</td>
                                    <td>${detail.old_stock}</td>
                                    <td>${detail.adjustment_value ?? '-'}</td>
                                    <td>${detail.new_stock}</td>
                                    <td>${detail.old_satuan}</td>
                                    <td>${detail.new_satuan}</td>
                                    <td>${detail.conversion_factor ?? '-'}</td>
                                    <td>${detail.note}</td>
                                </tr>
                            `;
                        });

                        $('#detailAdjustmentBody').html(rows || '<tr><td colspan="10" class="text-center text-muted">Tidak ada detail.</td></tr>');
                        const detailModal = new bootstrap.Modal(document.getElementById('detailAdjustmentModal'));
                        detailModal.show();
                    }).fail(function() {
                        Swal.fire('Error', 'Gagal memuat detail adjustment.', 'error');
                    });
                });
            });
        </script>
    </x-slot>
</x-app-layout>
