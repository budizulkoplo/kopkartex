<x-app-layout>
    <x-slot name="pagetitle">{{ $title }}</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">{{ $title }}</h3>
                <a href="{{ route('cashbank.transactions.hutang.index') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Transaksi Baru
                </a>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('cashbank.transactions.hutang.history') }}" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $tanggal_awal }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $tanggal_akhir }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cari</label>
                            <input type="text" name="q" class="form-control form-control-sm" value="{{ $keyword }}" placeholder="No transaksi / supplier / no nota">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Tampilkan</button>
                            <a href="{{ route('cashbank.transactions.hutang.history') }}" class="btn btn-sm btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle" style="font-size: small;">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No Transaksi</th>
                                    <th>Dibayar Kepada</th>
                                    <th>No Ref</th>
                                    <th class="text-end">Jumlah</th>
                                    <th>Detail & Log Transaksi</th>
                                    <th style="width: 160px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ optional($transaction->tgl_transaksi)->format('d-m-Y') }}</td>
                                        <td><strong>{{ $transaction->nomor_transaksi }}</strong></td>
                                        <td>{{ $transaction->dibayar_kepada }}</td>
                                        <td>{{ $transaction->no_ref_nota ?: '-' }}</td>
                                        <td class="text-end">{{ number_format((float) $transaction->sejumlah, 0, ',', '.') }}</td>
                                        <td>
                                            @foreach($transaction->details as $detail)
                                                <div>
                                                    <strong>{{ $detail->nomor_invoice ?: '-' }}</strong>
                                                    <span class="text-muted">{{ $detail->coa->kode_akun ?? '' }} {{ $detail->coa->nama_akun ?? '' }}</span>
                                                    <span class="float-end">{{ number_format((float) $detail->jumlah_bayar, 0, ',', '.') }}</span>
                                                </div>
                                                @if($detail->keterangan)
                                                    <div class="text-muted">Ket: {{ $detail->keterangan }}</div>
                                                @endif
                                            @endforeach
                                            <div class="border-top mt-1 pt-1">
                                                @forelse($transaction->logs as $log)
                                                    <div class="text-muted">
                                                        <i class="bi bi-clock-history"></i>
                                                        {{ optional($log->created_at)->format('d-m-Y H:i') }}
                                                        - {{ $log->keterangan }}
                                                        oleh {{ $log->user->name ?? '-' }}
                                                    </div>
                                                @empty
                                                    <div class="text-muted">Belum ada log transaksi.</div>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('cashbank.transactions.hutang.nota', $transaction->nomor_transaksi) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-warning btnEdit" data-id="{{ $transaction->id }}" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btnDelete" data-id="{{ $transaction->id }}" data-number="{{ $transaction->nomor_transaksi }}" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi pembayaran hutang.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="editForm">
                    <input type="hidden" name="jenis" value="pembayaran_hutang">
                    <input type="hidden" name="supplier_id" id="editSupplierId">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Pembayaran Supplier <span id="editNomor" class="text-muted"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">Unit Usaha</label>
                                <select class="form-control form-control-sm" name="unit_id" id="editUnit" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kode Dokumen</label>
                                <select class="form-control form-control-sm" name="document_code_id" id="editDocument" required>
                                    @foreach($documents as $document)
                                        <option value="{{ $document->id }}">{{ $document->kode }} - {{ $document->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control form-control-sm" name="tgl_transaksi" id="editDate" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Periode</label>
                                <input type="text" class="form-control form-control-sm" name="periode" id="editPeriode" maxlength="6" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dibayar Kepada</label>
                                <input type="text" class="form-control form-control-sm" name="dibayar_kepada" id="editPaidTo" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Guna Membayar</label>
                                <input type="text" class="form-control form-control-sm" name="guna_membayar" id="editPurpose">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No Ref / No Nota</label>
                                <input type="text" class="form-control form-control-sm" name="no_ref_nota" id="editRef">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sejumlah</label>
                                <input type="number" class="form-control form-control-sm text-end" name="sejumlah" id="editAmount" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Dibayar Dengan</label>
                                <select class="form-control form-control-sm" name="dibayar_dengan" id="editPaidBy" required>
                                    <option value="cash">CASH</option>
                                    <option value="kredit">KREDIT</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bank</label>
                                <select class="form-control form-control-sm" name="bank_id" id="editBank">
                                    <option value="">Kas / tanpa bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->nama_bank }} {{ $bank->nomor_rekening ? '- '.$bank->nomor_rekening : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="no_cash_cek_giro" id="editGiroNo">
                            <input type="hidden" name="tgl_giro_cek" id="editGiroDate">
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <strong>Detail Transaksi</strong>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnEditAddRow"><i class="bi bi-plus-lg"></i> Baris</button>
                        </div>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered align-middle" id="editDetailTable" style="font-size: small;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 22%">COA</th>
                                        <th>Invoice</th>
                                        <th class="text-end">Nilai</th>
                                        <th class="text-end">Sudah Bayar</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Sisa</th>
                                        <th>Keterangan</th>
                                        <th style="width: 42px"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total</th>
                                        <th class="text-end" id="editDetailTotal">0</th>
                                        <th colspan="3"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        <button class="btn btn-primary btn-sm" id="btnEditSave"><i class="bi bi-save"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            const coaOptions = @json($coas->map(fn($coa) => ['id' => $coa->id, 'label' => $coa->kode_akun.' - '.$coa->nama_akun])->values());
            let editId = null;
            let editIndex = 0;

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID').format(Number(value || 0));
            }

            function escapeAttr(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function coaOptionHtml(selectedId = '') {
                return '<option value="">Pilih COA</option>' + coaOptions.map(coa => `<option value="${coa.id}" ${String(coa.id) === String(selectedId) ? 'selected' : ''}>${coa.label}</option>`).join('');
            }

            function editRecalc() {
                let total = 0;
                $('#editDetailTable tbody tr').each(function () {
                    const nilai = Number($(this).find('.edit-nilai').val() || 0);
                    const sudah = Number($(this).find('.edit-sudah').val() || 0);
                    const bayarInput = $(this).find('.edit-jumlah');
                    let bayar = Number(bayarInput.val() || 0);
                    const maxBayar = Math.max(nilai - sudah, 0);
                    if (bayar > maxBayar && maxBayar > 0) {
                        bayar = maxBayar;
                        bayarInput.val(maxBayar);
                    }
                    const sisa = Math.max(nilai - sudah - bayar, 0);
                    $(this).find('.edit-sisa').val(sisa);
                    $(this).find('.edit-sisa-label').text(formatNumber(sisa));
                    total += bayar;
                });
                $('#editDetailTotal').text(formatNumber(total));
                $('#editAmount').val(total.toFixed(2));
            }

            function addEditRow(data = {}) {
                const idx = editIndex++;
                const row = $(`
                    <tr>
                        <td><select class="form-control form-control-sm" name="detail[${idx}][coa_id]">${coaOptionHtml(data.coa_id || '')}</select></td>
                        <td>
                            <input type="text" class="form-control form-control-sm" name="detail[${idx}][nomor_invoice]" value="${escapeAttr(data.nomor_invoice)}">
                            <input type="hidden" name="detail[${idx}][penerimaan_id]" value="${data.penerimaan_id || ''}">
                        </td>
                        <td><input type="number" class="form-control form-control-sm text-end edit-nilai" name="detail[${idx}][nilai_invoice]" value="${data.nilai_invoice || 0}" min="0" step="0.01"></td>
                        <td><input type="number" class="form-control form-control-sm text-end edit-sudah" name="detail[${idx}][sudah_dibayar]" value="${data.sudah_dibayar || 0}" min="0" step="0.01"></td>
                        <td><input type="number" class="form-control form-control-sm text-end edit-jumlah" name="detail[${idx}][jumlah_bayar]" value="${data.jumlah_bayar || 0}" min="0" step="0.01"></td>
                        <td>
                            <input type="hidden" class="edit-sisa" name="detail[${idx}][sisa]" value="${data.sisa || 0}">
                            <div class="text-end edit-sisa-label">${formatNumber(data.sisa || 0)}</div>
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="detail[${idx}][keterangan]" value="${escapeAttr(data.keterangan)}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger btnEditRemove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                `);
                $('#editDetailTable tbody').append(row);
                editRecalc();
            }

            $('#editForm').on('keydown', 'input, select, textarea, button', function (e) {
                if (e.key !== 'Enter' || $(this).is('textarea') || $(this).attr('type') === 'submit') return;
                e.preventDefault();

                const focusable = $('#editForm')
                    .find('input, select, textarea, button')
                    .filter(':visible:not([disabled]):not([readonly])');
                const currentIndex = focusable.index(this);
                if (currentIndex >= 0 && currentIndex < focusable.length - 1) {
                    focusable.eq(currentIndex + 1).trigger('focus');
                }
            });

            $('.btnEdit').on('click', function () {
                editId = $(this).data('id');
                const url = "{{ route('cashbank.transactions.hutang.show', ['transaction' => '__ID__']) }}".replace('__ID__', editId);

                $.get(url).done(function (data) {
                    $('#editNomor').text(data.nomor_transaksi);
                    $('#editUnit').val(data.unit_id);
                    $('#editDocument').val(data.document_code_id);
                    $('#editDate').val(data.tgl_transaksi);
                    $('#editPeriode').val(data.periode);
                    $('#editSupplierId').val(data.supplier_id);
                    $('#editPaidTo').val(data.dibayar_kepada);
                    $('#editPurpose').val(data.guna_membayar);
                    $('#editRef').val(data.no_ref_nota);
                    $('#editAmount').val(data.sejumlah);
                    $('#editPaidBy').val(data.dibayar_dengan);
                    $('#editBank').val(data.bank_id);
                    $('#editGiroNo').val(data.no_cash_cek_giro);
                    $('#editGiroDate').val(data.tgl_giro_cek);
                    $('#editDetailTable tbody').empty();
                    editIndex = 0;
                    (data.details || []).forEach(addEditRow);
                    if (!data.details || !data.details.length) addEditRow();
                    $('#editModal').modal('show');
                }).fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            });

            $('#btnEditAddRow').on('click', () => addEditRow());
            $('#editDetailTable').on('input', '.edit-nilai, .edit-sudah, .edit-jumlah', editRecalc);
            $('#editDetailTable').on('click', '.btnEditRemove', function () {
                $(this).closest('tr').remove();
                editRecalc();
            });

            $('#editForm').on('submit', function (e) {
                e.preventDefault();
                if (!editId) return;

                const url = "{{ route('cashbank.transactions.hutang.update', ['transaction' => '__ID__']) }}".replace('__ID__', editId);
                $.ajax({
                    url,
                    method: 'PUT',
                    data: $(this).serialize(),
                    beforeSend: () => $('#btnEditSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...'),
                    success: response => {
                        Swal.fire({ icon: 'success', title: response.message, timer: 1200, showConfirmButton: false })
                            .then(() => window.location.reload());
                    },
                    error: xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'),
                    complete: () => $('#btnEditSave').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan Perubahan')
                });
            });

            $('.btnDelete').on('click', function () {
                const id = $(this).data('id');
                const number = $(this).data('number');
                const url = "{{ route('cashbank.transactions.hutang.destroy', ['transaction' => '__ID__']) }}".replace('__ID__', id);

                Swal.fire({
                    title: 'Hapus transaksi?',
                    html: `Transaksi <b>${number}</b> akan dihapus dan status hutang terkait dihitung ulang.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({ url, method: 'DELETE' })
                        .done(response => Swal.fire({ icon: 'success', title: response.message, timer: 1200, showConfirmButton: false }).then(() => window.location.reload()))
                        .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
                });
            });
        </script>
    </x-slot>
</x-app-layout>
