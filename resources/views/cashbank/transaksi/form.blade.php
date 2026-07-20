<x-app-layout>
    <x-slot name="pagetitle">{{ $title }}</x-slot>
    @php
        $routeScope = $jenis === 'pembayaran_hutang' ? 'hutang' : 'umum';
        $coaOptionsForJs = $coas->map(fn($coa) => [
            'id' => $coa->id,
            'label' => $coa->kode_akun . ' - ' . $coa->nama_akun,
        ])->values();
        $coaLookupForJs = $coas->mapWithKeys(fn($coa) => [
            $coa->id => [
                'code' => $coa->kode_akun,
                'name' => $coa->nama_akun,
            ],
        ])->all();
        $bankLookupForJs = $banks->mapWithKeys(fn($bank) => [
            $bank->id => [
                'account_code' => $bank->kode_akun ?? '',
                'account_name' => $bank->nama_akun ?? '',
            ],
        ])->all();
    @endphp
    <x-slot name="csscustom">
        <style>
            .cashbank-page .card {
                border-radius: .5rem;
                box-shadow: 0 .125rem .5rem rgba(15, 23, 42, .06);
            }
            .cashbank-page .card-header {
                background: #fff;
                border-bottom: 1px solid #e9ecef;
            }
            .cashbank-page .card-body {
                padding: 1rem;
            }
            .cashbank-title {
                font-size: 20px;
                line-height: 1.2;
            }
            .cb-line {
                display: flex;
                align-items: center;
                gap: 4px;
                margin-bottom: 4px;
                min-height: 24px;
            }
            .cb-form-grid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(320px, 380px);
                gap: 18px;
                align-items: start;
            }
            .cb-field {
                display: grid;
                grid-template-columns: 150px minmax(0, 1fr);
                gap: 10px;
                align-items: center;
                margin-bottom: 8px;
                min-height: 36px;
            }
            .cb-field > label {
                display: block;
                margin: 0;
                color: #344054;
                font-size: .875rem;
                font-weight: 700;
                letter-spacing: 0;
                line-height: 1.25;
                text-transform: uppercase;
                white-space: normal;
            }
            .cb-field > label:empty {
                display: none;
            }
            .cb-control-row {
                display: flex;
                gap: 6px;
                align-items: center;
                min-width: 0;
            }
            .cb-control-row > .form-control,
            .cb-control-row > .form-select,
            .cb-control-row > select {
                min-width: 0;
            }
            .cb-control-row .form-control,
            .cb-control-row .form-select,
            .cb-field .form-control,
            .cb-field .form-select,
            .cb-field .btn {
                min-height: 34px;
                padding: .25rem .6rem;
                border-radius: .25rem;
                font-size: .95rem;
                font-weight: 400;
            }
            .cb-side {
                padding: 12px;
                border: 1px solid #e5e7eb;
                border-radius: .5rem;
                background: #f8fafc;
            }
            .cb-side .cb-field {
                grid-template-columns: 126px minmax(0, 1fr);
                margin-bottom: 8px;
            }
            .cb-select2-sm + .select2-container--bootstrap-5 .select2-selection {
                min-height: 34px;
                font-size: .95rem;
                border-radius: .25rem;
            }
            .cb-select2-sm + .select2-container--bootstrap-5 .select2-selection__rendered {
                line-height: 32px;
            }
            .cb-select2-sm + .select2-container--bootstrap-5 .select2-selection__arrow {
                min-height: 34px;
            }
            #detailTable .select2-container {
                min-width: 180px;
            }
            .cb-line label {
                width: 124px;
                margin: 0;
                font-weight: 700;
                text-transform: uppercase;
                white-space: nowrap;
            }
            .cb-line-offset {
                padding-left: 128px;
            }
            .cb-code {
                flex: 0 0 96px;
                max-width: 120px;
            }
            .cb-fill {
                flex: 1 1 auto;
                min-width: 120px;
            }
            .cb-cyan {
                background-color: #72eeee;
                color: #033;
            }
            .cb-new {
                flex: 0 0 auto;
                min-width: 46px;
            }
            .cb-amount {
                flex: 0 0 145px;
                max-width: 145px;
            }
            .cb-ref-label {
                width: 110px !important;
                margin-left: 8px !important;
                text-transform: none !important;
            }
            .cb-ref {
                flex: 0 0 290px;
                height: 48px !important;
            }
            .cb-giro-label {
                width: 115px !important;
                text-transform: none !important;
                text-align: right;
            }
            .cb-giro {
                flex: 0 0 170px;
            }
            .cb-giro-date-label {
                width: 86px !important;
                text-transform: none !important;
                text-align: right;
            }
            .cb-giro-date {
                flex: 0 0 130px;
            }
            .cb-period-label,
            .cb-trx-label {
                width: 100px !important;
                text-align: right;
            }
            .cb-period {
                flex: 0 0 84px;
                background-color: #fff59d;
            }
            .cb-trx {
                flex: 0 0 165px;
            }
            #detailTable .detail-note {
                min-width: 160px;
            }
            #cashbankForm .form-control-sm,
            #cashbankForm .form-select-sm,
            #cashbankForm select.form-control-sm {
                min-height: 34px;
                padding: .25rem .6rem;
                font-size: .95rem;
                border-radius: .25rem;
                font-weight: 400;
            }
            #cashbankForm .btn-sm {
                padding: .25rem .6rem;
                font-size: .95rem;
                border-radius: .25rem;
            }
            #cashbankForm .table {
                font-size: .95rem;
            }
            #cashbankForm .form-control[readonly] {
                background-color: #f8f9fa;
            }
            #cashbankForm .cb-cyan {
                background-color: #ecfeff;
                border-color: #99f6e4;
                color: #164e63;
            }
            #cashbankForm #documentCode {
                max-width: 130px !important;
            }
            #cashbankForm #mainCoaCode,
            #cashbankForm #supplierCodePreview {
                max-width: 125px !important;
            }
            #cashbankForm input[name="tgl_transaksi"],
            #cashbankForm #sejumlah {
                max-width: 180px !important;
            }
            #cashbankForm select[name="dibayar_dengan"] {
                max-width: 120px !important;
            }
            #cashbankForm .cb-side .form-control,
            #cashbankForm .cb-side select {
                max-width: none !important;
            }
            #cashbankForm .cb-side .cb-period {
                max-width: 150px !important;
            }
            .cb-money-group {
                display: flex;
                max-width: 220px;
            }
            .cb-money-prefix {
                display: inline-flex;
                align-items: center;
                padding: .25rem .6rem;
                border: 1px solid #ced4da;
                border-right: 0;
                border-radius: .25rem 0 0 .25rem;
                background: #f8fafc;
                color: #475467;
                font-weight: 700;
            }
            .cb-money-group .form-control {
                border-top-left-radius: 0 !important;
                border-bottom-left-radius: 0 !important;
            }
            .cashbank-detail-head {
                margin-top: 14px;
                padding-top: 12px;
                border-top: 1px solid #e9ecef;
            }
            #detailTable {
                margin-bottom: 0;
            }
            #detailTable thead th {
                background: #f8fafc;
                color: #344054;
                font-size: .875rem;
                vertical-align: middle;
            }
            #detailTable td {
                vertical-align: middle;
            }
            .cashbank-actions {
                padding-top: 16px;
                border-top: 1px solid #e9ecef;
            }
            @media (max-width: 992px) {
                .cb-form-grid {
                    grid-template-columns: 1fr;
                }
                .cb-side {
                    padding: 12px;
                }
                .cb-line {
                    flex-wrap: wrap;
                }
                .cb-line label {
                    width: 120px;
                }
                .cb-line-offset {
                    padding-left: 0;
                }
                .cb-ref,
                .cb-giro,
                .cb-giro-date {
                    flex: 1 1 140px;
                }
            }
        </style>
    </x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0 cashbank-title">{{ $title }}</h3>
                <div class="d-flex align-items-center gap-2">
                    @if($jenis === 'pembayaran_hutang')
                        <a href="{{ route('cashbank.transactions.hutang.history') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-clock-history"></i> Riwayat Transaksi
                        </a>
                    @endif
                    <span class="badge bg-dark">{{ $nomor }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content cashbank-page">
        <div class="container-fluid">
            <form id="cashbankForm">
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header py-2">
                                <strong>Form Transaksi</strong>
                            </div>
                            <div class="card-body">
                                <div class="cb-form-grid">
                                    <div>
                                        <div class="cb-field">
                                            <label>Unit Usaha</label>
                                            <select class="form-control form-control-sm" name="unit_id" required>
                                                <option value="">Pilih Unit Usaha</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}" data-name="{{ $unit->nama_unit }}" @selected((int) auth()->user()->unit_kerja === (int) $unit->id)>{{ $unit->nama_unit }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="cb-field">
                                            <label>Kode Dokumen</label>
                                            <div class="cb-control-row">
                                                <select class="form-control form-control-sm" name="document_code_id" id="documentCode" required style="max-width: 120px;">
                                                    <option value="">Kode</option>
                                                    @foreach($documents as $document)
                                                        <option value="{{ $document->id }}"
                                                            data-name="{{ $document->nama }}"
                                                            data-prefix="{{ $document->prefix ?: $document->kode }}"
                                                            data-bank-id="{{ $document->bank_id }}"
                                                            data-transaction-type="{{ $document->transaction_type ?? 'payment' }}"
                                                            data-account-code="{{ $document->bank?->kode_akun ?? '' }}"
                                                            data-account-name="{{ $document->bank?->nama_akun ?? '' }}">
                                                            {{ $document->kode }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-sm btn-outline-secondary cb-new" data-bs-toggle="modal" data-bs-target="#documentModal">New</button>
                                            </div>
                                        </div>

                                        <div class="cb-field">
                                            <label>-</label>
                                            <input type="text" class="form-control form-control-sm" id="documentNamePreview" readonly>
                                        </div>

                                        <div class="cb-field">
                                            <label>Kode Akun</label>
                                            <div class="cb-control-row">
                                                <input type="hidden" name="coa_id" id="mainCoa">
                                                <input type="text" class="form-control form-control-sm" id="mainCoaCode" readonly style="max-width: 120px;">
                                                <input type="text" class="form-control form-control-sm cb-cyan" id="mainCoaName" readonly>
                                            </div>
                                        </div>

                                        @if($jenis === 'pembayaran_hutang')
                                            <div class="cb-field">
                                                <label>Bayar Supplier</label>
                                                <div class="cb-control-row">
                                                    <input type="text" class="form-control form-control-sm cb-cyan fw-semibold" id="supplierCodePreview" readonly style="max-width: 120px;">
                                                    <input type="text" class="form-control form-control-sm cb-cyan fw-semibold" id="supplierSearch" autocomplete="off">
                                                    <input type="hidden" name="supplier_id" id="supplierId">
                                                    <button type="button" class="btn btn-sm btn-outline-info cb-new" id="btnPickSupplier">Ambil</button>
                                                    <button type="button" class="btn btn-sm btn-info cb-new" id="btnLoadSupplierInvoices"><i class="bi bi-search"></i> Muat Invoice</button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary cb-new" data-bs-toggle="modal" data-bs-target="#supplierModal"><i class="bi bi-plus-lg"></i></button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="cb-field">
                                                <label>Bayar Anggota</label>
                                                <div class="cb-control-row">
                                                    <input type="text" class="form-control form-control-sm cb-cyan fw-semibold" id="memberCodePreview" readonly style="max-width: 120px;">
                                                    <input type="text" class="form-control form-control-sm cb-cyan fw-semibold" id="memberSearch" autocomplete="off">
                                                    <button type="button" class="btn btn-sm btn-outline-info cb-new" id="btnPickMember">Ambil</button>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="cb-field">
                                            <label>Tgl Transaksi</label>
                                            <input type="date" class="form-control form-control-sm" name="tgl_transaksi" value="{{ date('Y-m-d') }}" required style="max-width: 160px;">
                                        </div>

                                        <div class="cb-field">
                                            <label>Dibayar Kepada</label>
                                            <input type="text" class="form-control form-control-sm" id="paidToPreview" name="dibayar_kepada" required>
                                        </div>

                                        <div class="cb-field">
                                            <label>Guna Pembayaran</label>
                                            <input type="text" class="form-control form-control-sm" name="guna_membayar">
                                        </div>

                                        <div class="cb-field">
                                            <label>Sejumlah</label>
                                            <div class="cb-money-group">
                                                <span class="cb-money-prefix">Rp.</span>
                                                <input type="text" class="form-control form-control-sm text-end money-display" id="sejumlahDisplay" data-hidden="#sejumlah" inputmode="numeric" autocomplete="off" required>
                                                <input type="hidden" name="sejumlah" id="sejumlah">
                                            </div>
                                        </div>

                                        <div class="cb-field">
                                            <label>Dibayar Dengan</label>
                                            <div class="cb-control-row">
                                                <select class="form-control form-control-sm" name="dibayar_dengan" required style="max-width: 120px;">
                                                    <option value="cash">CASH</option>
                                                    <option value="kredit">KREDIT</option>
                                                </select>
                                                <select class="form-control form-control-sm" name="bank_id">
                                                    <option value="">Kas / tanpa bank</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->nama_bank }} {{ $bank->nomor_rekening ? '- '.$bank->nomor_rekening : '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="cb-side">
                                        <div class="cb-field">
                                            <label>Periode</label>
                                            <input type="text" class="form-control form-control-sm cb-period" name="periode" value="{{ date('Ym') }}" maxlength="6" required style="max-width: 100px;">
                                        </div>
                                        <div class="cb-field">
                                            <label>No.Transaksi</label>
                                            <div class="cb-control-row">
                                                <input type="text" class="form-control form-control-sm" value="{{ $nomor }}" readonly id="nomorPreview">
                                                <button type="button" class="btn btn-sm btn-outline-secondary cb-new" id="btnRefreshNumber"><i class="bi bi-arrow-clockwise"></i></button>
                                            </div>
                                        </div>
                                        @if($jenis === 'pembayaran_hutang')
                                            <div class="cb-field">
                                                <label>No Ref / No Nota</label>
                                                <textarea class="form-control form-control-sm" name="no_ref_nota" id="noRefNota" rows="3" style="height: 70px;"></textarea>
                                            </div>
                                        @else
                                            <input type="hidden" name="no_ref_nota" id="noRefNota">
                                        @endif
                                        <input type="hidden" name="no_cash_cek_giro">
                                        <input type="hidden" name="tgl_giro_cek">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center cashbank-detail-head">
                                    <strong>Detail Pembayaran</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddDetail"><i class="bi bi-plus-lg"></i> Baris</button>
                                </div>

                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered align-middle" id="detailTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 23%">Kode Akun</th>
                                                <th style="width: 24%">Invoice</th>
                                                <th class="text-end">Nilai</th>
                                                <th class="text-end">Jumlah</th>
                                                <th style="width: 18%">Keterangan</th>
                                                <th style="width: 40px"></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total Pembayaran</th>
                                                <th class="text-end" id="detailTotal">0</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end gap-2 cashbank-actions">
                                    <button type="button" class="btn btn-sm btn-warning" id="btnClear"><i class="bi bi-x-circle"></i> Batal</button>
                                    <button type="submit" class="btn btn-sm btn-success" id="btnSave"><i class="bi bi-floppy-fill"></i> Simpan</button>
                                    <button type="button" class="btn btn-sm btn-primary" id="btnPrint" disabled><i class="bi bi-printer"></i> Cetak</button>
                                </div>
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
                        <input class="form-control form-control-sm mb-2" name="prefix" placeholder="Prefix nomor">
                        <select class="form-control form-control-sm mb-2" name="transaction_type" required>
                            <option value="payment">Payment - Kredit</option>
                            <option value="receipt">Receipt - Debet</option>
                        </select>
                        <select class="form-control form-control-sm" name="bank_id">
                            <option value="">Pilih Bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">
                                    {{ $bank->kode_bank }} - {{ $bank->nama_bank }}
                                    | {{ $bank->kode_akun }} - {{ $bank->nama_akun }}
                                </option>
                            @endforeach
                        </select>
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
                        <input class="form-control form-control-sm mb-2" name="tipe" placeholder="Kelompok laporan" value="BIAYA" required>
                        <select class="form-control form-control-sm mb-2" name="att5">
                            <option value="D">Detail - bisa dipakai transaksi</option>
                            <option value="H">Header - kelompok/induk</option>
                        </select>
                        <select class="form-control form-control-sm" name="att4">
                            <option value="">Kas/Bank: -</option>
                            <option value="KAS">KAS</option>
                            <option value="BANK">BANK</option>
                            <option value="CASH">CASH</option>
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

    <div class="modal fade" id="supplierPickModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ambil Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control" id="supplierPickKeyword" placeholder="Cari kode / nama supplier">
                        <button type="button" class="btn btn-primary" id="btnSupplierPickSearch"><i class="bi bi-search"></i> Cari</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped" id="supplierPickTable" style="font-size: small;">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Supplier</th>
                                    <th>Alamat</th>
                                    <th>Telp</th>
                                    <th>Kontak</th>
                                    <th>Email</th>
                                    <th style="width: 70px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="7" class="text-muted">Ketik kata kunci lalu cari.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="memberPickModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ambil Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control" id="memberPickKeyword" placeholder="Cari no / nama anggota">
                        <button type="button" class="btn btn-primary" id="btnMemberPickSearch"><i class="bi bi-search"></i> Cari</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped" id="memberPickTable" style="font-size: small;">
                            <thead>
                                <tr>
                                    <th>No Anggota</th>
                                    <th>Nama</th>
                                    <th style="width: 70px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="3" class="text-muted">Ketik kata kunci lalu cari.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            const jenis = @json($jenis);
            const coaOptions = @json($coaOptionsForJs);
            const coaLookup = @json($coaLookupForJs);
            const bankLookup = @json($bankLookupForJs);
            let detailIndex = 0;
            let lastSavedNotaUrl = '';
            let lastSavedNomor = '';
            let formDirty = true;
            $('#noRefNota').data('auto-ref', true).on('input', function () {
                $(this).data('auto-ref', false);
            });

            function markFormDirty() {
                formDirty = true;
                $('#btnPrint').prop('disabled', true);
            }

            function rememberSavedTransaction(response) {
                lastSavedNotaUrl = response.nota_url || '';
                lastSavedNomor = response.nomor || $('#nomorPreview').val();
                formDirty = false;
                $('#btnPrint').prop('disabled', !lastSavedNotaUrl);
            }

            function resetFormForNextTransaction(selectedDocumentId = '') {
                const currentDocumentId = selectedDocumentId || $('#documentCode').val() || '';
                const currentUnitId = $('[name=unit_id]').val() || '';
                const currentDate = $('input[name=tgl_transaksi]').val() || '{{ date('Y-m-d') }}';
                const currentPeriod = $('input[name=periode]').val() || '{{ date('Ym') }}';

                $('#cashbankForm')[0].reset();
                $('#detailTable tbody').empty();
                $('#detailTotal').text('0');
                setMoney('#sejumlahDisplay', 0);
                $('#supplierId').val('');
                $('#supplierSearch').val('');
                $('#supplierCodePreview').val('');
                $('#memberCodePreview').val('');
                $('#memberSearch').val('');
                $('#paidToPreview').val('');
                $('#documentNamePreview').val('');
                setMainAccount('', '');
                $('#noRefNota').data('auto-ref', true).val('');
                $('input[name=no_cash_cek_giro]').val('');
                $('input[name=tgl_giro_cek]').val('');
                $('input[name=tgl_transaksi]').val(currentDate);
                $('input[name=periode]').val(currentPeriod);
                if (currentUnitId) {
                    $('[name=unit_id]').val(currentUnitId);
                }
                if (currentDocumentId) {
                    $('#documentCode').val(currentDocumentId);
                    $('#documentCode').trigger('change');
                } else {
                    $('#nomorPreview').val('');
                }
                syncUnitPreview();
                detailIndex = 0;
                addDetailRow();
                formDirty = true;
            }

            function validateDetailCoa() {
                const rows = $('#detailTable tbody tr');
                if (!rows.length) {
                    Swal.fire('Perhatian', 'Tambahkan minimal satu baris detail pembayaran.', 'warning');
                    return false;
                }

                let invalidSelect = null;
                rows.each(function () {
                    const row = $(this);
                    const amount = parseMoney(row.find('.jumlah-bayar-display').val());
                    const coaSelect = row.find('.detail-coa-select');

                    if (amount > 0 && !coaSelect.val()) {
                        invalidSelect = coaSelect;
                        return false;
                    }
                });

                if (invalidSelect) {
                    Swal.fire('Perhatian', 'Kode akun detail wajib diisi untuk setiap baris yang memiliki jumlah bayar.', 'warning')
                        .then(() => invalidSelect.trigger('focus'));
                    return false;
                }

                return true;
            }

            $('#cashbankForm').on('keydown', 'input, select, textarea, button', function (e) {
                if (e.key !== 'Enter' || $(this).is('textarea') || $(this).attr('type') === 'submit') return;
                e.preventDefault();

                const focusable = $('#cashbankForm')
                    .find('input, select, textarea, button')
                    .filter(':visible:not([disabled]):not([readonly])');
                const currentIndex = focusable.index(this);

                if (currentIndex >= 0 && currentIndex < focusable.length - 1) {
                    focusable.eq(currentIndex + 1).trigger('focus');
                }
            });

            $('#cashbankForm').on('input change', 'input, select, textarea', function () {
                markFormDirty();
            });

            function setMainAccount(code = '', name = '') {
                $('#mainCoa').val('');
                $('#mainCoaCode').val(code || '');
                $('#mainCoaName').val(name || '');
            }

            function syncUnitPreview() {
                return true;
            }

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID').format(Number(value || 0));
            }

            function parseMoney(value) {
                const cleaned = String(value || '').replace(/[^\d]/g, '');
                return cleaned ? Number(cleaned) : 0;
            }

            function formatMoneyInput(input) {
                const target = $(input);
                const value = parseMoney(target.val());
                target.val(value > 0 ? formatNumber(value) : '');

                const hiddenSelector = target.data('hidden');
                if (hiddenSelector) {
                    $(hiddenSelector).val(value > 0 ? value : '');
                }
            }

            function setMoney(target, value) {
                const input = $(target);
                const number = Number(value || 0);
                input.val(number > 0 ? formatNumber(number) : '');

                const hiddenSelector = input.data('hidden');
                if (hiddenSelector) {
                    $(hiddenSelector).val(number > 0 ? number : '');
                }
            }

            function escapeAttr(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function optionHtml(selectedId = '') {
                return '<option value="">Pilih COA</option>' + coaOptions.map(coa => `<option value="${coa.id}" ${String(coa.id) === String(selectedId) ? 'selected' : ''}>${coa.label}</option>`).join('');
            }

            function initCoaSelect2(target) {
                if (!$.fn.select2) return;

                target.each(function () {
                    const select = $(this);
                    if (select.data('select2')) {
                        select.select2('destroy');
                    }

                    select.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Pilih / cari COA',
                        allowClear: true
                    });
                });
            }

            function updateRefNota() {
                const refs = [];
                $('#detailTable tbody .nomor-invoice').each(function () {
                    const ref = $(this).val();
                    if (ref && !refs.includes(ref)) refs.push(ref);
                });
                if ($('#noRefNota').data('auto-ref')) {
                    $('#noRefNota').val(refs.join(','));
                }
            }

            function selectSupplier(data) {
                markFormDirty();
                $('#supplierId').val(data.id || '');
                $('#supplierCodePreview').val(data.kode_supplier || '');
                $('#supplierSearch').val(data.text || data.nama_supplier || '');
                $('#paidToPreview').val(data.text || data.nama_supplier || '');
            }

            function selectMember(data) {
                markFormDirty();
                $('#memberCodePreview').val(data.nomor_anggota || '');
                $('#memberSearch').val(data.text || data.name || '');
                $('#paidToPreview').val([data.nomor_anggota, data.text || data.name].filter(Boolean).join(' - '));
            }

            function recalc() {
                let total = 0;
                $('#detailTable tbody tr').each(function () {
                    const nilai = Number($(this).find('.nilai-invoice').val() || 0);
                    const sudah = Number($(this).find('.sudah-dibayar').val() || 0);
                    const bayarDisplay = $(this).find('.jumlah-bayar-display');
                    let bayar = parseMoney(bayarDisplay.val());
                    const maxBayar = Math.max(nilai - sudah, 0);
                    if (bayar > maxBayar && maxBayar > 0) {
                        bayar = maxBayar;
                    }
                    const sisa = Math.max(nilai - sudah - bayar, 0);
                    $(this).find('.sisa').val(sisa);
                    setMoney(bayarDisplay, bayar);
                    total += bayar;
                });
                $('#detailTotal').text(formatNumber(total));
                if (total > 0) {
                    setMoney('#sejumlahDisplay', total);
                }
                updateRefNota();
            }

            function addDetailRow(data = {}) {
                markFormDirty();
                const idx = detailIndex++;
                const selectedCoa = data.coa_id || $('#mainCoa').val();
                const row = $(`
                    <tr>
                        <td>
                            <select class="form-control form-control-sm detail-coa-select cb-select2-sm" name="detail[${idx}][coa_id]">${optionHtml(selectedCoa)}</select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm invoice-search nomor-invoice" name="detail[${idx}][nomor_invoice]" autocomplete="off" placeholder="No invoice / ref">
                            <input type="hidden" name="detail[${idx}][penerimaan_id]" class="penerimaan-id">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm text-end nilai-invoice-display money-display" data-hidden="#nilaiInvoice${idx}" inputmode="numeric" autocomplete="off">
                            <input type="hidden" name="detail[${idx}][nilai_invoice]" class="nilai-invoice" id="nilaiInvoice${idx}">
                            <input type="hidden" name="detail[${idx}][sudah_dibayar]" class="sudah-dibayar" id="sudahDibayar${idx}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm text-end jumlah-bayar-display money-display" data-hidden="#jumlahBayar${idx}" inputmode="numeric" autocomplete="off">
                            <input type="hidden" class="jumlah-bayar" id="jumlahBayar${idx}" name="detail[${idx}][jumlah_bayar]">
                            <input type="hidden" name="detail[${idx}][sisa]" class="sisa">
                        </td>
                        <td><input type="text" class="form-control form-control-sm detail-note" name="detail[${idx}][keterangan]" value="${escapeAttr(data.keterangan)}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger btnRemove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                `);
                $('#detailTable tbody').append(row);
                initCoaSelect2(row.find('.detail-coa-select'));
                bindInvoiceSearch(row.find('.invoice-search'));
                if (data.nomor_invoice) fillInvoice(row, data);
                if (data.nilai_invoice) setMoney(row.find('.nilai-invoice-display'), data.nilai_invoice);
                row.find('.sudah-dibayar').val(Number(data.sudah_dibayar || 0));
                if (data.jumlah_bayar) setMoney(row.find('.jumlah-bayar-display'), data.jumlah_bayar);
                recalc();
            }

            function fillInvoice(row, data) {
                markFormDirty();
                row.find('.invoice-search').val(data.nomor_invoice || data.text);
                row.find('.penerimaan-id').val(data.id);
                setMoney(row.find('.nilai-invoice-display'), data.nilai_invoice);
                row.find('.sudah-dibayar').val(Number(data.sudah_dibayar || 0));
                setMoney(row.find('.jumlah-bayar-display'), data.jumlah_bayar || data.sisa);
                if (data.supplier_id) $('#supplierId').val(data.supplier_id);
                if (data.nama_supplier) {
                    selectSupplier({
                        id: data.supplier_id,
                        kode_supplier: data.kode_supplier || '',
                        text: data.nama_supplier
                    });
                }
                recalc();
            }

            function bindInvoiceSearch(input) {
                if (jenis !== 'pembayaran_hutang') return;

                input.on('change blur', function () {
                    const row = $(this).closest('tr');
                    const keyword = $(this).val().trim();
                    if (!keyword || row.find('.nomor-invoice').val() === keyword) return;

                    $.get("{{ route("cashbank.transactions.$routeScope.invoices") }}", {
                        supplier_id: $('#supplierId').val(),
                        supplier_code: $('#supplierCodePreview').val(),
                        q: keyword
                    }).done(function (rows) {
                        if (!rows.length) {
                            Swal.fire('Info', 'Invoice belum ditemukan atau sudah lunas.', 'info');
                            return;
                        }
                        fillInvoice(row, rows[0]);
                    }).fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
                });
            }

            $('#supplierSearch').on('input', function () {
                if (!$(this).val()) {
                    $('#supplierId').val('');
                    $('#supplierCodePreview').val('');
                }
            }).on('keydown', function (e) {
                if (e.key === 'Enter') e.preventDefault();
            });

            function searchSupplierPicker() {
                const keyword = $('#supplierPickKeyword').val();
                const tbody = $('#supplierPickTable tbody');
                tbody.html('<tr><td colspan="7" class="text-muted">Memuat...</td></tr>');

                $.get("{{ route("cashbank.transactions.$routeScope.suppliers") }}", { q: keyword })
                    .done(function (rows) {
                        if (!rows.length) {
                            tbody.html('<tr><td colspan="7" class="text-muted">Supplier tidak ditemukan.</td></tr>');
                            return;
                        }

                        tbody.html(rows.map(row => `
                            <tr>
                                <td>${row.kode_supplier || ''}</td>
                                <td>${row.text || ''}</td>
                                <td>${row.alamat || ''}</td>
                                <td>${row.telp || ''}</td>
                                <td>${row.kontak_person || ''}</td>
                                <td>${row.email || ''}</td>
                                <td><button type="button" class="btn btn-sm btn-primary pick-supplier" data-id="${row.id}" data-code="${row.kode_supplier || ''}" data-name="${row.text || ''}">Pilih</button></td>
                            </tr>
                        `).join(''));
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            }

            $('#btnPickSupplier').on('click', function () {
                $('#supplierPickKeyword').val($('#supplierSearch').val());
                $('#supplierPickModal').modal('show');
                searchSupplierPicker();
            });

            $('#btnSupplierPickSearch').on('click', searchSupplierPicker);
            $('#supplierPickKeyword').on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchSupplierPicker();
                }
            });
            $('#supplierPickTable').on('click', '.pick-supplier', function () {
                selectSupplier({
                    id: $(this).data('id'),
                    kode_supplier: $(this).data('code'),
                    text: $(this).data('name')
                });
                $('#supplierPickModal').modal('hide');
            });

            $('#memberSearch').on('input', function () {
                if (!$(this).val()) {
                    $('#memberCodePreview').val('');
                }
                if (jenis === 'umum') {
                    $('#paidToPreview').val($(this).val());
                }
            }).on('keydown', function (e) {
                if (e.key === 'Enter') e.preventDefault();
            });

            function searchMemberPicker() {
                const keyword = $('#memberPickKeyword').val();
                const tbody = $('#memberPickTable tbody');
                tbody.html('<tr><td colspan="3" class="text-muted">Memuat...</td></tr>');

                $.get("{{ route("cashbank.transactions.$routeScope.members") }}", { q: keyword })
                    .done(function (rows) {
                        if (!rows.length) {
                            tbody.html('<tr><td colspan="3" class="text-muted">Anggota tidak ditemukan.</td></tr>');
                            return;
                        }

                        tbody.html(rows.map(row => `
                            <tr>
                                <td>${row.nomor_anggota || ''}</td>
                                <td>${row.text || ''}</td>
                                <td><button type="button" class="btn btn-sm btn-primary pick-member" data-code="${row.nomor_anggota || ''}" data-name="${row.text || ''}">Pilih</button></td>
                            </tr>
                        `).join(''));
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            }

            $('#btnPickMember').on('click', function () {
                $('#memberPickKeyword').val($('#memberSearch').val());
                $('#memberPickModal').modal('show');
                searchMemberPicker();
            });
            $('#btnMemberPickSearch').on('click', searchMemberPicker);
            $('#memberPickKeyword').on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchMemberPicker();
                }
            });
            $('#memberPickTable').on('click', '.pick-member', function () {
                selectMember({
                    nomor_anggota: $(this).data('code'),
                    text: $(this).data('name')
                });
                $('#memberPickModal').modal('hide');
            });

            $('#btnAddDetail').on('click', () => addDetailRow());
            $('#cashbankForm').on('input', '.money-display', function () {
                formatMoneyInput(this);

                if ($(this).hasClass('jumlah-bayar-display') || $(this).hasClass('nilai-invoice-display')) {
                    recalc();
                }
            });
            $('#detailTable').on('click', '.btnRemove', function () { markFormDirty(); $(this).closest('tr').remove(); recalc(); });

            $('#documentCode').on('change', function () {
                const selected = $(this).find(':selected');
                $('#documentNamePreview').val(selected.attr('data-name') || selected.data('name') || '');
                const bankId = selected.attr('data-bank-id') || selected.data('bank-id');
                if (bankId) $('[name=bank_id]').val(bankId);
                const bank = bankLookup[bankId] || {};
                setMainAccount(
                    selected.attr('data-account-code') || selected.data('account-code') || bank.account_code || '',
                    selected.attr('data-account-name') || selected.data('account-name') || bank.account_name || ''
                );
                refreshNumberPreview();
            });

            $('[name=unit_id]').on('change', syncUnitPreview);

            $('#btnLoadSupplierInvoices').on('click', function () {
                const supplierCode = $('#supplierCodePreview').val().trim();
                if (!supplierCode) {
                    Swal.fire('Perhatian', 'Pilih supplier terlebih dahulu.', 'warning');
                    return;
                }

                $.get("{{ route("cashbank.transactions.$routeScope.invoices") }}", {
                    supplier_code: supplierCode,
                    q: ''
                }).done(function (rows) {
                    if (!rows.length) {
                        Swal.fire('Info', 'Tidak ada nota hutang yang belum terbayar untuk supplier ini.', 'info');
                        return;
                    }

                    rows.forEach(row => {
                        const emptyRow = $('#detailTable tbody tr').filter(function () {
                            return !$(this).find('.nomor-invoice').val()
                                && !$(this).find('.invoice-search').val()
                                && parseMoney($(this).find('.jumlah-bayar-display').val()) <= 0;
                        }).first();
                        const targetRow = emptyRow.length ? emptyRow : null;
                        const data = {
                            ...row,
                            coa_id: $('#mainCoa').val(),
                            sisa: row.sisa
                        };

                        if (targetRow) {
                            fillInvoice(targetRow, data);
                        } else {
                            addDetailRow(data);
                        }
                        recalc();
                    });
                }).fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            });

            $('#btnRefreshNumber').on('click', function () {
                refreshNumberPreview();
            });

            function refreshNumberPreview() {
                $.get("{{ route("cashbank.transactions.$routeScope.number") }}", {
                    jenis,
                    document_code_id: $('#documentCode').val()
                }).done(number => $('#nomorPreview').val(number));
            }

            $('#btnPrint').on('click', function () {
                if (!lastSavedNotaUrl) {
                    Swal.fire('Info', 'Simpan transaksi terlebih dahulu sebelum mencetak nota.', 'info');
                    return;
                }

                window.open(lastSavedNotaUrl, '_blank');
            });

            $('#documentForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route("cashbank.transactions.$routeScope.quick-document") }}", $(this).serialize())
                    .done(({ data }) => {
                        const bank = bankLookup[data.bank_id] || {};
                        $('#documentCode').append(`<option value="${data.id}" data-name="${data.nama}" data-prefix="${data.prefix || data.kode}" data-bank-id="${data.bank_id || ''}" data-transaction-type="${data.transaction_type || 'payment'}" data-account-code="${bank.account_code || ''}" data-account-name="${bank.account_name || ''}" selected>${data.kode}</option>`);
                        $('#documentNamePreview').val(data.nama);
                        if (data.bank_id) $('[name=bank_id]').val(data.bank_id);
                        setMainAccount(bank.account_code, bank.account_name);
                        refreshNumberPreview();
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
                        if ((data.att5 || 'D') === 'D') {
                            coaOptions.push({ id: data.id, label });
                            coaLookup[data.id] = { code: data.kode_akun, name: data.nama_akun };
                            $('#detailTable tbody .detail-coa-select').each(function () {
                                const select = $(this);
                                select.append(`<option value="${data.id}">${label}</option>`);
                                if (select.data('select2')) {
                                    select.trigger('change.select2');
                                }
                            });
                        }
                        $('#coaModal').modal('hide');
                        this.reset();
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            });

            $('#supplierForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route("cashbank.transactions.$routeScope.quick-supplier") }}", $(this).serialize())
                    .done(({ data }) => {
                        selectSupplier(data);
                        $('#supplierModal').modal('hide');
                        this.reset();
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            });

            $('#btnClear').on('click', function () {
                lastSavedNotaUrl = '';
                lastSavedNomor = '';
                formDirty = true;
                $('#btnPrint').prop('disabled', true);
                $('#cashbankForm')[0].reset();
                $('#detailTable tbody').empty();
                $('#detailTotal').text('0');
                setMoney('#sejumlahDisplay', 0);
                $('#supplierId').val('');
                $('#supplierSearch').val('');
                $('#supplierCodePreview').val('');
                $('#memberCodePreview').val('');
                $('#memberSearch').val('');
                $('#paidToPreview').val('');
                $('#documentNamePreview').val('');
                setMainAccount('', '');
                $('#noRefNota').data('auto-ref', true).val('');
                $('input[name=tgl_transaksi]').val('{{ date('Y-m-d') }}');
                $('input[name=periode]').val('{{ date('Ym') }}');
                syncUnitPreview();
                addDetailRow();
            });

            $('#cashbankForm').on('submit', function (e) {
                e.preventDefault();

                if (lastSavedNotaUrl && !formDirty) {
                    Swal.fire('Info', 'Transaksi sudah tersimpan. Klik Cetak untuk mencetak nota.', 'info');
                    return;
                }

                if (!validateDetailCoa()) {
                    return;
                }

                $.ajax({
                    url: "{{ route("cashbank.transactions.$routeScope.store") }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: () => $('#btnSave').prop('disabled', true),
                    success: response => {
                        rememberSavedTransaction(response);
                        Swal.fire({ icon: 'success', title: response.message, timer: 1500, showConfirmButton: false })
                            .then(() => resetFormForNextTransaction($('#documentCode').val()));
                    },
                    error: xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'),
                    complete: () => $('#btnSave').prop('disabled', false)
                });
            });

            addDetailRow();
            if (!$('#documentCode').val() && $('#documentCode option[value!=""]').length === 1) {
                $('#documentCode option[value!=""]').first().prop('selected', true);
            }
            syncUnitPreview();
                $('#documentCode').trigger('change');
        </script>
    </x-slot>
</x-app-layout>
