<x-app-layout>
    <x-slot name="pagetitle">{{ $title }}</x-slot>
    @php($routeScope = $jenis === 'pembayaran_hutang' ? 'hutang' : 'umum')

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">{{ $title }}</h3>
                <span class="badge bg-dark">{{ $nomor }}</span>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <form id="cashbankForm">
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="card card-primary card-outline">
                            <div class="card-header py-2">
                                <strong>Form Transaksi</strong>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor Transaksi</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" value="{{ $nomor }}" readonly id="nomorPreview">
                                            <button type="button" class="btn btn-outline-secondary" id="btnRefreshNumber"><i class="bi bi-arrow-clockwise"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Unit Usaha</label>
                                        <select class="form-control form-control-sm" name="unit_id" required>
                                            <option value="">Pilih Unit</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" @selected((int) auth()->user()->unit_kerja === (int) $unit->id)>{{ $unit->nama_unit }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Dokumen</label>
                                        <div class="input-group input-group-sm">
                                            <select class="form-control" name="document_code_id" id="documentCode" required>
                                                <option value="">Pilih Kode Dokumen</option>
                                                @foreach($documents as $document)
                                                    <option value="{{ $document->id }}">{{ $document->kode }} - {{ $document->nama }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#documentModal"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Akun</label>
                                        <div class="input-group input-group-sm">
                                            <select class="form-control" name="coa_id" id="mainCoa" required>
                                                <option value="">Pilih COA</option>
                                                @foreach($coas as $coa)
                                                    <option value="{{ $coa->id }}">{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#coaModal"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Transaksi</label>
                                        <input type="date" class="form-control form-control-sm" name="tgl_transaksi" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Akun Bank</label>
                                        <select class="form-control form-control-sm" name="bank_id">
                                            <option value="">Kas / tanpa bank</option>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->nama_bank }} {{ $bank->nomor_rekening ? '- '.$bank->nomor_rekening : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Dibayar Kepada</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control typeahead" id="supplierSearch" name="dibayar_kepada" autocomplete="off" required>
                                            <input type="hidden" name="supplier_id" id="supplierId">
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#supplierModal"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Dibayar Dengan</label>
                                        <select class="form-control form-control-sm" name="dibayar_dengan" required>
                                            <option value="cash">Cash</option>
                                            <option value="kredit">Kredit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Sejumlah</label>
                                        <input type="number" class="form-control form-control-sm text-end" name="sejumlah" id="sejumlah" min="0" step="0.01" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Guna Membayar</label>
                                        <textarea class="form-control form-control-sm" name="guna_membayar" rows="2"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <strong>Nama Akun Transaksi</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddDetail"><i class="bi bi-plus-lg"></i> Baris</button>
                                </div>

                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered align-middle" id="detailTable" style="font-size: small;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 23%">Kode Akun</th>
                                                <th style="width: 24%">Invoice</th>
                                                <th class="text-end">Nilai</th>
                                                <th class="text-end">Sudah Bayar</th>
                                                <th class="text-end">Jumlah</th>
                                                <th class="text-end">Sisa</th>
                                                <th style="width: 40px"></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-end">Total Pembayaran</th>
                                                <th class="text-end" id="detailTotal">0</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-warning" id="btnClear"><i class="bi bi-x-circle"></i> Batal</button>
                                    <button type="submit" class="btn btn-sm btn-success" id="btnSave"><i class="bi bi-floppy-fill"></i> Simpan & Cetak</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card card-secondary card-outline">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                <strong>Log Transaksi</strong>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnReloadLog"><i class="bi bi-arrow-clockwise"></i></button>
                            </div>
                            <div class="card-body" id="logPanel" style="max-height: 640px; overflow:auto;">
                                @include('cashbank.transaksi.partials.logs', ['transactions' => $recentLogs])
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="documentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="documentForm">
                    <div class="modal-header"><h5 class="modal-title">Tambah Kode Dokumen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input class="form-control form-control-sm mb-2" name="kode" placeholder="Kode" required>
                        <input class="form-control form-control-sm mb-2" name="nama" placeholder="Nama dokumen" required>
                        <input class="form-control form-control-sm" name="prefix" placeholder="Prefix nomor">
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="coaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="coaForm">
                    <div class="modal-header"><h5 class="modal-title">Tambah COA</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input class="form-control form-control-sm mb-2" name="kode_akun" placeholder="Kode akun" required>
                        <input class="form-control form-control-sm mb-2" name="nama_akun" placeholder="Nama akun" required>
                        <select class="form-control form-control-sm" name="tipe" required>
                            <option value="kas">Kas</option>
                            <option value="bank">Bank</option>
                            <option value="hutang">Hutang</option>
                            <option value="biaya">Biaya</option>
                            <option value="pendapatan">Pendapatan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="supplierModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="supplierForm">
                    <div class="modal-header"><h5 class="modal-title">Tambah Vendor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input class="form-control form-control-sm mb-2" name="nama_supplier" placeholder="Nama vendor" required>
                        <input class="form-control form-control-sm mb-2" name="telp" placeholder="Telepon">
                        <textarea class="form-control form-control-sm" name="alamat" rows="2" placeholder="Alamat"></textarea>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            const jenis = @json($jenis);
            const coaOptions = @json($coas->map(fn($coa) => ['id' => $coa->id, 'label' => $coa->kode_akun . ' - ' . $coa->nama_akun])->values());
            let detailIndex = 0;

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID').format(Number(value || 0));
            }

            function optionHtml(selectedId = '') {
                return '<option value="">Pilih COA</option>' + coaOptions.map(coa => `<option value="${coa.id}" ${String(coa.id) === String(selectedId) ? 'selected' : ''}>${coa.label}</option>`).join('');
            }

            function recalc() {
                let total = 0;
                $('#detailTable tbody tr').each(function () {
                    const nilai = Number($(this).find('.nilai-invoice').val() || 0);
                    const sudah = Number($(this).find('.sudah-dibayar').val() || 0);
                    const bayarInput = $(this).find('.jumlah-bayar');
                    let bayar = Number(bayarInput.val() || 0);
                    const maxBayar = Math.max(nilai - sudah, 0);
                    if (bayar > maxBayar && maxBayar > 0) {
                        bayar = maxBayar;
                        bayarInput.val(maxBayar);
                    }
                    const sisa = Math.max(nilai - sudah - bayar, 0);
                    $(this).find('.sisa').val(sisa);
                    $(this).find('.sisa-label').text(formatNumber(sisa));
                    total += bayar;
                });
                $('#detailTotal').text(formatNumber(total));
                if (total > 0) $('#sejumlah').val(total.toFixed(2));
            }

            function addDetailRow(data = {}) {
                const idx = detailIndex++;
                const row = $(`
                    <tr>
                        <td>
                            <select class="form-control form-control-sm" name="detail[${idx}][coa_id]">${optionHtml($('#mainCoa').val())}</select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm invoice-search" autocomplete="off" placeholder="Cari invoice">
                            <input type="hidden" name="detail[${idx}][penerimaan_id]" class="penerimaan-id">
                            <input type="hidden" name="detail[${idx}][nomor_invoice]" class="nomor-invoice">
                        </td>
                        <td>
                            <input type="hidden" name="detail[${idx}][nilai_invoice]" class="nilai-invoice">
                            <div class="text-end nilai-label">0</div>
                        </td>
                        <td>
                            <input type="hidden" name="detail[${idx}][sudah_dibayar]" class="sudah-dibayar">
                            <div class="text-end sudah-label">0</div>
                        </td>
                        <td><input type="number" class="form-control form-control-sm text-end jumlah-bayar" name="detail[${idx}][jumlah_bayar]" min="0" step="0.01"></td>
                        <td>
                            <input type="hidden" name="detail[${idx}][sisa]" class="sisa">
                            <div class="text-end sisa-label">0</div>
                        </td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger btnRemove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                `);
                $('#detailTable tbody').append(row);
                bindInvoiceSearch(row.find('.invoice-search'));
                if (data.nomor_invoice) fillInvoice(row, data);
            }

            function fillInvoice(row, data) {
                row.find('.invoice-search').typeahead('val', data.nomor_invoice || data.text);
                row.find('.penerimaan-id').val(data.id);
                row.find('.nomor-invoice').val(data.nomor_invoice || data.text);
                row.find('.nilai-invoice').val(data.nilai_invoice);
                row.find('.sudah-dibayar').val(data.sudah_dibayar);
                row.find('.jumlah-bayar').val(data.sisa);
                row.find('.nilai-label').text(formatNumber(data.nilai_invoice));
                row.find('.sudah-label').text(formatNumber(data.sudah_dibayar));
                recalc();
            }

            function bindInvoiceSearch(input) {
                const invoiceSource = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.whitespace,
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: {
                        url: '{{ route("cashbank.transactions.$routeScope.invoices") }}?supplier_id=' + encodeURIComponent($('#supplierId').val()) + '&q=%QUERY',
                        wildcard: '%QUERY',
                        transform: response => response
                    }
                });
                input.typeahead({ hint: true, highlight: true, minLength: 1 }, {
                    name: 'invoices',
                    source: invoiceSource,
                    display: 'text',
                    templates: {
                        suggestion: data => `<div><strong>${data.nomor_invoice}</strong><br><small>${data.nama_supplier} | Sisa: ${formatNumber(data.sisa)}</small></div>`
                    }
                }).on('typeahead:select', function (ev, suggestion) {
                    fillInvoice($(this).closest('tr'), suggestion);
                });
            }

            const supplierSource = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.whitespace,
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: { url: '{{ route("cashbank.transactions.$routeScope.suppliers") }}?q=%QUERY', wildcard: '%QUERY' }
            });

            $('#supplierSearch').typeahead({ hint: true, highlight: true, minLength: 1 }, {
                name: 'suppliers',
                source: supplierSource,
                display: 'text',
                templates: { suggestion: data => `<div><strong>${data.kode_supplier}</strong> - ${data.text}</div>` }
            }).on('typeahead:select', function (ev, suggestion) {
                $('#supplierId').val(suggestion.id);
                $(this).val(suggestion.text);
            }).on('input', function () {
                if (!$(this).val()) $('#supplierId').val('');
            });

            $('#btnAddDetail').on('click', () => addDetailRow());
            $('#detailTable').on('input', '.jumlah-bayar', recalc);
            $('#detailTable').on('click', '.btnRemove', function () { $(this).closest('tr').remove(); recalc(); });

            $('#btnRefreshNumber').on('click', function () {
                $.get("{{ route("cashbank.transactions.$routeScope.number") }}", { jenis }).done(number => $('#nomorPreview').val(number));
            });

            $('#btnReloadLog').on('click', function () {
                $('#logPanel').load("{{ route("cashbank.transactions.$routeScope.logs") }}?jenis=" + encodeURIComponent(jenis));
            });

            $('#documentForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route("cashbank.transactions.$routeScope.quick-document") }}", $(this).serialize())
                    .done(({ data }) => {
                        $('#documentCode').append(`<option value="${data.id}" selected>${data.kode} - ${data.nama}</option>`);
                        $('#documentModal').modal('hide');
                        this.reset();
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            });

            $('#coaForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route("cashbank.transactions.$routeScope.quick-coa") }}", $(this).serialize())
                    .done(({ data }) => {
                        const label = `${data.kode_akun} - ${data.nama_akun}`;
                        coaOptions.push({ id: data.id, label });
                        $('#mainCoa').append(`<option value="${data.id}" selected>${label}</option>`);
                        $('#detailTable tbody select').each(function () { $(this).append(`<option value="${data.id}">${label}</option>`); });
                        $('#coaModal').modal('hide');
                        this.reset();
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            });

            $('#supplierForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route("cashbank.transactions.$routeScope.quick-supplier") }}", $(this).serialize())
                    .done(({ data }) => {
                        $('#supplierId').val(data.id);
                        $('#supplierSearch').typeahead('val', data.nama_supplier);
                        $('#supplierModal').modal('hide');
                        this.reset();
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            });

            $('#btnClear').on('click', function () {
                $('#cashbankForm')[0].reset();
                $('#detailTable tbody').empty();
                $('#detailTotal').text('0');
                $('#supplierId').val('');
                $('#supplierSearch').typeahead('val', '');
                $('input[name=tgl_transaksi]').val('{{ date('Y-m-d') }}');
            });

            $('#cashbankForm').on('submit', function (e) {
                e.preventDefault();
                if (jenis === 'pembayaran_hutang' && $('#detailTable tbody tr').length === 0) {
                    Swal.fire('Perhatian', 'Tambahkan invoice hutang yang akan dibayar.', 'warning');
                    return;
                }
                $.ajax({
                    url: "{{ route("cashbank.transactions.$routeScope.store") }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: () => $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...'),
                    success: response => {
                        Swal.fire({ icon: 'success', title: response.message, timer: 1500, showConfirmButton: false })
                            .then(() => {
                                window.open(response.nota_url, '_blank');
                                $('#btnReloadLog').trigger('click');
                                $('#btnClear').trigger('click');
                                $('#btnRefreshNumber').trigger('click');
                            });
                    },
                    error: xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'),
                    complete: () => $('#btnSave').prop('disabled', false).html('<i class="bi bi-floppy-fill"></i> Simpan & Cetak')
                });
            });

            if (jenis === 'pembayaran_hutang') addDetailRow();
        </script>
    </x-slot>
</x-app-layout>
