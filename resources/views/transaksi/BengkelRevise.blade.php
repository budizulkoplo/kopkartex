<x-app-layout>
    <x-slot name="pagetitle">Revisi Transaksi Bengkel</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Revisi Transaksi Bengkel</h3>
                    <small class="text-muted">Nota: {{ $transaksi->nomor_invoice }}</small>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="{{ route('bengkel.riwayat') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container">
            <form id="formTransaksi" autocomplete="off" class="needs-validation" novalidate>
                @csrf
                <div class="card card-warning card-outline mb-4">
                    <div class="card-header p-2 bg-warning bg-opacity-10">
                        <div class="alert alert-warning ps-2 p-0 mb-0" role="alert" id="infoRevisi">
                            <i class="bi bi-exclamation-triangle-fill"></i> 
                            Mode Revisi - Stok akan dikembalikan dan dikurangi ulang
                        </div>
                    </div>
                    <div class="card-body p-3">

                        {{-- READ-ONLY HEADER --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Tanggal</span>
                                    <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y') }}" readonly>
                                    <input type="hidden" name="tanggal" value="{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('Y-m-d') }}">
                                </div>
                                <div class="input-group input-group-sm mb-2 align-items-center">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" id="flexCheckDefault" {{ $transaksi->anggota_id ? 'checked disabled' : 'disabled' }}>
                                        <label for="flexCheckDefault" class="mb-0">Anggota</label>
                                    </div>
                                    <input type="text" class="form-control bg-light" value="{{ $transaksi->customer ?? 'Non Anggota' }}" readonly>
                                    <input type="hidden" id="idcustomer" name="idcustomer" value="{{ $transaksi->anggota_id }}">
                                    <input type="hidden" name="customer" value="{{ $transaksi->customer }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Petugas</span>
                                    <input type="text" class="form-control bg-light" value="{{ $transaksi->user->name ?? auth()->user()->name }}" readonly>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Barang</span>
                                    <input type="text" class="form-control typeahead" id="barcode-search" placeholder="Scan barcode atau ketik nama">
                                    <span class="input-group-text bg-warning"><i class="fa-solid fa-barcode text-white"></i></span>
                                </div>
                            </div>

                            <div class="col-md-4 text-end">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Invoice</label>
                                    <div class="fs-6 fw-bold txtinv">{{ $transaksi->nomor_invoice }}</div>
                                </div>
                                <div class="fs-3 fw-bold text-warning topgrandtotal">Rp. {{ number_format($transaksi->grandtotal, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        {{-- INPUT QTY --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center bg-light p-2 rounded border">
                                    <div class="me-3 fw-bold text-warning" style="min-width: 100px;">
                                        <i class="bi bi-plus-circle"></i> QTY:
                                    </div>
                                    <div style="width: 150px;">
                                        <input type="number" class="form-control form-control-sm" id="input-qty" value="1" min="1" onfocus="this.select()">
                                    </div>
                                    <div class="ms-3 text-muted small">
                                        <i class="bi bi-arrow-return-left"></i> isi qty, lalu Enter untuk input produk
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL JASA --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-0">Jasa Bengkel</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <table class="table table-sm table-striped table-bordered mb-2" id="tabelJasa" style="font-size: small;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama Jasa</th>
                                                    <th>Harga</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($transaksi->details->where('jenis', 'jasa') as $detail)
                                                <tr data-detail-id="{{ $detail->id }}" data-jenis="jasa">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <select class="form-select form-select-sm namajasa" style="width:100%" required>
                                                            <option value="{{ $detail->jasa_id }}" 
                                                                    selected 
                                                                    data-harga="{{ $detail->harga }}">
                                                                {{ $detail->jasa->nama_jasa ?? 'Jasa' }}
                                                            </option>
                                                        </select>
                                                        <input type="hidden" name="jasa_id[]" class="idjasa" value="{{ $detail->jasa_id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="jasa_harga[]" class="form-control form-control-sm harga-jasa" value="{{ $detail->harga }}" readonly>
                                                    </td>
                                                    <td>
                                                        <span class="badge btn bg-danger dellist" onclick="removeJasaRow($(this).closest('tr'))">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="text-end">
                                            <button type="button" id="tambahJasa" class="btn btn-primary btn-sm">
                                                <i class="bi bi-plus"></i> Tambah Jasa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TABEL BARANG --}}
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-0">Sparepart</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <table class="table table-sm table-striped table-bordered mb-2" id="tabelBarang" style="font-size: small;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Kode</th>
                                                    <th>Nama Barang</th>
                                                    <th>Stok</th>
                                                    <th>Qty</th>
                                                    <th>Harga</th>
                                                    <th>Total</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($transaksi->details->where('jenis', 'barang') as $detail)
                                                <tr data-detail-id="{{ $detail->id }}" data-id="{{ $detail->barang_id }}" data-jenis="barang">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="kodebarang">{{ $detail->barang->kode_barang ?? '' }}</td>
                                                    <td>
                                                        <select class="form-select form-select-sm namabarang" style="width:100%" required>
                                                            <option value="{{ $detail->barang_id }}" 
                                                                    selected 
                                                                    data-cicilan="{{ $detail->barang->kategori->cicilan ?? 1 }}">
                                                                {{ $detail->barang->nama_barang ?? 'Barang' }}
                                                            </option>
                                                        </select>
                                                        <input type="hidden" name="idbarang[]" class="idbarang" value="{{ $detail->barang_id }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="stoktext">{{ $detail->barang->stok?->stok ?? 0 }}</span>
                                                        <input type="hidden" class="stok" value="{{ $detail->barang->stok?->stok ?? 0 }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="qty[]" class="form-control form-control-sm barangqty" 
                                                               value="{{ $detail->qty }}" min="1" max="{{ $detail->barang->stok?->stok ?? 999 }}" 
                                                               onfocus="this.select()" onkeyup="kalkulasi()" required>
                                                        <input type="hidden" name="harga_jual[]" class="hargajual" value="{{ $detail->harga }}">
                                                    </td>
                                                    <td class="hargajualtext">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                                                    <td class="totalitm">{{ number_format($detail->total, 0, ',', '.') }}</td>
                                                    <td>
                                                        <span class="badge btn bg-danger dellist" onclick="removeBarangRow($(this).closest('tr'))">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="text-end">
                                            <button type="button" id="tambahBarang" class="btn btn-success btn-sm">
                                                <i class="bi bi-plus"></i> Tambah Barang
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TOTAL & PAYMENT (EDITABLE) --}}
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Subtotal</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control bg-light" value="{{ $transaksi->subtotal }}" name="subtotal" id="subtotal" readonly>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Diskon</span>
                                    <input type="number" class="form-control" value="{{ $transaksi->diskon }}" name="diskon" id="diskon" min="0" max="100" step="0.01" onkeyup="kalkulasi()" onchange="kalkulasi()">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Grand Total</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control bg-light" value="{{ $transaksi->grandtotal }}" name="grandtotal" id="grandtotal" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Metode</span>
                                    <select class="form-select form-select-sm" id="metodebayar" name="metodebayar" onchange="ubahMetodeBayar()" readonly>
                                        <option value="tunai" {{ $transaksi->metode_bayar == 'tunai' ? 'selected' : '' }}>Tunai</option>
                                        <option value="cicilan" {{ $transaksi->metode_bayar == 'cicilan' ? 'selected' : '' }}>Cicilan</option>
                                    </select>
                                </div>
                                
                                <div id="cicilanSection" style="{{ $transaksi->metode_bayar == 'cicilan' ? '' : 'display: none;' }}">
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text label-fixed-width">Jml.Cicilan</span>
                                        <input type="number" class="form-control" id="jmlcicilan" name="jmlcicilan" 
                                            value="{{ $transaksi->tenor ?? 2 }}" min="1" max="12" step="1" 
                                            onkeyup="kalkulasi()" onchange="kalkulasi()" required>
                                        <span class="input-group-text">bulan</span>
                                    </div>
                                    <div class="alert alert-info py-1 small" id="infoCicilan">
                                        <i class="bi bi-info-circle"></i> <span id="textInfoCicilan">Periksa kelayakan limit</span>
                                    </div>
                                </div>

                                <div id="tunaiSection" style="{{ $transaksi->metode_bayar == 'tunai' ? '' : 'display: none;' }}">
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text label-fixed-width">Dibayar</span>
                                        <span class="input-group-text">Rp.</span>
                                        <input type="number" class="form-control" value="{{ $transaksi->dibayar ?? 0 }}" name="dibayar" id="dibayar" min="0" onkeyup="hitungKembali()" onchange="hitungKembali()">
                                    </div>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text label-fixed-width">Kembali</span>
                                        <span class="input-group-text">Rp.</span>
                                        <input type="number" class="form-control bg-light" value="{{ $transaksi->kembali ?? 0 }}" name="kembali" id="kembali" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-3"> 
                                    <span class="input-group-text label-fixed-width">Catatan</span> 
                                    <textarea class="form-control" name="note" rows="3">{{ $transaksi->note ?? '' }}</textarea> 
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-auto d-flex gap-2">
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('bengkel.riwayat') }}'">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-pencil-square"></i> Update Revisi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="csscustom">
        <style>
        /* Chrome, Safari, Edge, Opera */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
        
        /* Typeahead dropdown menu */
        .tt-menu {
            width: 100%;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            z-index: 1050;
            max-height: 300px;
            overflow-y: auto;
        }

        .tt-suggestion {
            padding: 0.5rem;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .tt-suggestion:hover {
            background-color: #f8f9fa;
        }
        
        .tt-suggestion:last-child {
            border-bottom: none;
        }
        
        .tt-cursor {
            background-color: #e7f3ff;
        }
        
        .select2-results__option {
            padding: 8px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .select2-results__option:last-child {
            border-bottom: none;
        }
        
        .select2-results__option--highlighted {
            background-color: #e7f3ff !important;
            color: #333 !important;
        }
        
        .select2-container--default .select2-selection--single {
            height: 31px;
            border: 1px solid #ced4da;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px;
            padding-left: 8px;
            font-size: 0.875rem;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 29px;
        }
        
        .label-fixed-width {
            min-width: 100px;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 13px;
        }
        
        .table td {
            font-size: 13px;
            vertical-align: middle;
        }
        
        .card-header {
            background-color: #f8f9fa !important;
            padding: 0.75rem 1rem;
        }
        
        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="{{ asset('plugins/loader/waitMe.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
        <script>
            var globtot = {{ $transaksi->grandtotal }};
            var existingJasa = {};
            var existingBarang = {};
            var typeaheadInstance = null;
            var enterPressed = false;
            var transaksiId = {{ $transaksi->id }};
            
            function loader(onoff) {
                if(onoff)
                    $('.app-wrapper').waitMe({effect : 'bounce', text : '', bg : 'rgba(255,255,255,0.7)', color : '#000', waitTime : -1, textPos : 'vertical'});
                else
                    $('.app-wrapper').waitMe('hide');
            }

            function formatRupiahWithDecimal(angka) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(angka);
            }

            function numbering(tableId) {
                $(tableId + ' tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            function kalkulasi() {
                let subtotal = 0;
                
                // Hitung total jasa
                $('#tabelJasa tbody tr').each(function() {
                    let harga = parseFloat($(this).find('.harga-jasa').val()) || 0;
                    subtotal += harga;
                });
                
                // Hitung total barang
                $('#tabelBarang tbody tr').each(function() {
                    let qty = parseFloat($(this).find('.barangqty').val()) || 0;
                    let harga = parseFloat($(this).find('.hargajual').val()) || 0;
                    let total = qty * harga;
                    $(this).find('.totalitm').text(formatRupiahWithDecimal(total));
                    subtotal += total;
                });
                
                let diskon = parseFloat($('#diskon').val()) || 0;
                globtot = subtotal * (1 - (diskon / 100));
                
                $('#subtotal').val(subtotal);
                $('#grandtotal').val(globtot);
                $('.topgrandtotal').text(formatRupiahWithDecimal(globtot));
            }

            function clearBarcodeSearch() {
                $('#barcode-search').val('');
                if (typeaheadInstance) {
                    $('#barcode-search').typeahead('val', '');
                }
            }

            function focusToQtyInput() {
                setTimeout(() => {
                    $('#input-qty').focus().select();
                }, 100);
            }

            function focusToBarcode() {
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 100);
            }

            // FUNGSI UNTUK JASA
            function addJasaRow(datarow = null) {
                let newRow = $(`
                    <tr>
                        <td></td>
                        <td>
                            <select class="form-select form-select-sm namajasa" style="width:100%" required>
                                ${datarow ? `<option value="${datarow.id}" selected data-harga="${datarow.harga}">${datarow.text}</option>` : ''}
                            </select>
                            <input type="hidden" name="jasa_id[]" class="idjasa" value="${datarow ? datarow.id : ''}">
                        </td>
                        <td>
                            <input type="number" name="jasa_harga[]" class="form-control form-control-sm harga-jasa" value="${datarow ? datarow.harga : 0}" readonly>
                        </td>
                        <td>
                            <span class="badge btn bg-danger dellist" onclick="removeJasaRow($(this).closest('tr'))">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>
                `);
                
                $('#tabelJasa tbody').prepend(newRow);
                numbering('#tabelJasa');
                initSelect2Jasa(newRow);
                
                if (datarow && datarow.id) {
                    existingJasa[datarow.id] = newRow;
                }
                
                kalkulasi();
            }

            function removeJasaRow(row) {
                let idjasa = row.find('.idjasa').val();
                if (idjasa && existingJasa[idjasa]) {
                    delete existingJasa[idjasa];
                }
                row.remove();
                numbering('#tabelJasa');
                kalkulasi();
            }

            function initSelect2Jasa(context) {
                context.find('.namajasa').select2({
                    placeholder: "Pilih jasa",
                    width: '100%',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('bengkel.getjasa') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) { 
                            return { q: params.term }; 
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(j => ({
                                    id: j.id, 
                                    text: j.text, 
                                    harga: j.harga
                                }))
                            };
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        
                        let harga = formatRupiahWithDecimal(data.harga);
                        
                        return $(
                            `<div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${data.text}</strong>
                                </div>
                                <div class="text-primary fw-bold">${harga}</div>
                            </div>`
                        );
                    },
                    templateSelection: function(data) {
                        return data.text || data.id;
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    let row = $(this).closest('tr');
                    
                    row.find('.harga-jasa').val(data.harga);
                    row.find('.idjasa').val(data.id);
                    
                    if (data.id && !existingJasa[data.id]) {
                        existingJasa[data.id] = row;
                    }
                    
                    kalkulasi();
                }).on('select2:clear', function() {
                    let row = $(this).closest('tr');
                    let idjasa = row.find('.idjasa').val();
                    
                    if (idjasa && existingJasa[idjasa]) {
                        delete existingJasa[idjasa];
                    }
                    
                    row.find('.harga-jasa').val(0);
                    row.find('.idjasa').val('');
                    kalkulasi();
                });
            }

            // FUNGSI UNTUK BARANG
            function addBarangRow(datarow = null, qty = 1) {
                if (datarow && datarow.stok <= 0) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Stok habis!',
                        text: `Produk "${datarow.text}" tidak tersedia`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    clearBarcodeSearch();
                    focusToBarcode();
                    return;
                }
                
                // Cek apakah produk sudah ada
                if (datarow && datarow.id && existingBarang[datarow.id]) {
                    let existingRow = existingBarang[datarow.id];
                    let currentQty = parseInt(existingRow.find('.barangqty').val()) || 0;
                    let newQty = currentQty + qty;
                    let maxStok = parseInt(existingRow.find('.barangqty').attr('max')) || datarow.stok;
                    
                    if (newQty > maxStok) {
                        newQty = maxStok;
                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Melebihi stok!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                    
                    existingRow.find('.barangqty').val(newQty);
                    kalkulasi();
                    clearBarcodeSearch();
                    focusToBarcode();
                    return;
                }
                
                let newRow = $(`
                    <tr data-id="${datarow ? datarow.id : 0}">
                        <td></td>
                        <td class="kodebarang">${datarow ? datarow.code : ''}</td>
                        <td>
                            <select class="form-select form-select-sm namabarang" style="width:100%" required>
                                ${datarow ? `<option value="${datarow.id}" selected data-cicilan="${datarow.kategori_cicilan || 1}">${datarow.text}</option>` : ''}
                            </select>
                            <input type="hidden" name="idbarang[]" class="idbarang" value="${datarow ? datarow.id : ''}">
                        </td>
                        <td class="text-center">
                            <span class="stoktext">${datarow ? datarow.stok : 0}</span>
                            <input type="hidden" class="stok" value="${datarow ? datarow.stok : 0}">
                        </td>
                        <td>
                            <input type="number" name="qty[]" class="form-control form-control-sm barangqty" 
                                   value="${qty}" min="1" max="${datarow ? datarow.stok : 999}" 
                                   onfocus="this.select()" onkeyup="kalkulasi()" required>
                            <input type="hidden" name="harga_jual[]" class="hargajual" value="${datarow ? datarow.harga_jual : 0}">
                        </td>
                        <td class="hargajualtext">${datarow ? formatRupiahWithDecimal(datarow.harga_jual) : ''}</td>
                        <td class="totalitm"></td>
                        <td>
                            <span class="badge btn bg-danger dellist" onclick="removeBarangRow($(this).closest('tr'))">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>
                `);
                
                $('#tabelBarang tbody').prepend(newRow);
                numbering('#tabelBarang');
                initSelect2Barang(newRow);
                
                if (datarow && datarow.id) {
                    existingBarang[datarow.id] = newRow;
                }
                
                kalkulasi();
                clearBarcodeSearch();
                focusToBarcode();
            }

            function removeBarangRow(row) {
                let idbarang = row.find('.idbarang').val();
                if (idbarang && existingBarang[idbarang]) {
                    delete existingBarang[idbarang];
                }
                row.remove();
                numbering('#tabelBarang');
                kalkulasi();
            }

            function initSelect2Barang(context) {
                context.find('.namabarang').select2({
                    placeholder: "Pilih barang",
                    width: '100%',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('bengkel.getbarang') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) { 
                            return { q: params.term }; 
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(b => ({
                                    id: b.id, 
                                    code: b.code,
                                    text: b.text, 
                                    harga_jual: b.harga_jual, 
                                    stok: b.stok,
                                    kategori_cicilan: b.kategori_cicilan || 1
                                }))
                            };
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        
                        let stokInfo = data.stok > 0 ? 
                            `<span class="text-success">Stok: ${data.stok}</span>` : 
                            `<span class="text-danger">Stok: ${data.stok}</span>`;
                        
                        let cicilanInfo = data.kategori_cicilan == 0 ? 
                            `<span class="badge bg-warning">Cicilan 1x</span>` : 
                            `<span class="badge bg-info">Cicilan fleksibel</span>`;
                        
                        let harga = formatRupiahWithDecimal(data.harga_jual);
                        
                        return $(
                            `<div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${data.code}</strong> - ${data.text}
                                </div>
                                <div class="text-end">
                                    <div class="text-primary fw-bold">${harga}</div>
                                    <div class="small">${stokInfo}</div>
                                    <div class="small">${cicilanInfo}</div>
                                </div>
                            </div>`
                        );
                    },
                    templateSelection: function(data) {
                        return data.text || data.code;
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    let row = $(this).closest('tr');
                    
                    if (data.stok <= 0) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Stok habis!',
                            text: `Produk "${data.text}" tidak tersedia`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        $(this).val(null).trigger('change');
                        return;
                    }
                    
                    // Cek apakah produk sudah ada di row lain
                    if (data.id && existingBarang[data.id] && existingBarang[data.id] !== row) {
                        let existingRow = existingBarang[data.id];
                        let currentQty = parseInt(existingRow.find('.barangqty').val()) || 0;
                        let newQty = currentQty + 1;
                        let maxStok = parseInt(existingRow.find('.barangqty').attr('max')) || data.stok;
                        
                        if (newQty > maxStok) {
                            newQty = maxStok;
                        }
                        
                        existingRow.find('.barangqty').val(newQty);
                        row.remove();
                        numbering('#tabelBarang');
                        kalkulasi();
                        clearBarcodeSearch();
                        focusToBarcode();
                        return;
                    }
                    
                    row.attr("data-id", data.id);
                    row.find('.kodebarang').text(data.code);
                    row.find('.hargajual').val(data.harga_jual);
                    row.find('.hargajualtext').text(formatRupiahWithDecimal(data.harga_jual));
                    row.find('.stoktext').text(data.stok);
                    row.find('.stok').val(data.stok);
                    row.find('.barangqty').val(1).attr("max", data.stok);
                    row.find('.idbarang').val(data.id);
                    
                    $(this).find('option:selected').attr('data-cicilan', data.kategori_cicilan);
                    
                    if (data.id && !existingBarang[data.id]) {
                        existingBarang[data.id] = row;
                    }
                    
                    kalkulasi();
                    
                    clearBarcodeSearch();
                    focusToBarcode();
                    
                }).on('select2:clear', function() {
                    let row = $(this).closest('tr');
                    let idbarang = row.find('.idbarang').val();
                    
                    if (idbarang && existingBarang[idbarang]) {
                        delete existingBarang[idbarang];
                    }
                    
                    row.attr("data-id", 0);
                    row.find('.kodebarang').text('');
                    row.find('.hargajual').val(0);
                    row.find('.hargajualtext').text('');
                    row.find('.stoktext').text('0');
                    row.find('.stok').val(0);
                    row.find('.barangqty').val(0);
                    row.find('.idbarang').val('');
                    kalkulasi();
                });
            }

            // TYPEAHEAD UNTUK BARANG
            function initTypeahead() {
                typeaheadInstance = $('#barcode-search').typeahead({
                    minLength: 1,
                    highlight: true,
                    source: function(query, process) {
                        $.ajax({
                            url: '{{ route('bengkel.getbarang') }}',
                            type: 'GET',
                            data: { q: query },
                            dataType: 'json',
                            success: function(data) {
                                let suggestions = data.map(function(item) {
                                    return {
                                        id: item.id,
                                        code: item.code,
                                        text: item.text,
                                        harga_jual: item.harga_jual,
                                        stok: item.stok,
                                        kategori_cicilan: item.kategori_cicilan,
                                        display: `${item.code} - ${item.text} (Stok: ${item.stok})`
                                    };
                                });
                                process(suggestions);
                            }
                        });
                    },
                    displayText: function(item) {
                        return item.display || item.text;
                    },
                    afterSelect: function(item) {
                        setTimeout(() => {
                            clearBarcodeSearch();
                        }, 10);
                    },
                    updater: function(item) {
                        if (enterPressed) {
                            enterPressed = false;
                            return '';
                        }
                        
                        if (item.stok <= 0) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'warning',
                                title: 'Stok habis!',
                                text: `Produk "${item.text}" tidak tersedia`,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            return '';
                        }
                        
                        $('#barcode-search').data('selected-item', item);
                        focusToQtyInput();
                        return '';
                    }
                });
            }

            $(document).ready(function() {
                // Load existing jasa dan barang ke tracking object
                $('#tabelJasa tbody tr').each(function() {
                    let id = $(this).find('.idjasa').val();
                    if (id) {
                        existingJasa[id] = $(this);
                    }
                });
                
                $('#tabelBarang tbody tr').each(function() {
                    let id = $(this).find('.idbarang').val();
                    if (id) {
                        existingBarang[id] = $(this);
                    }
                });

                // Initialize Select2 untuk jasa dan barang yang sudah ada
                $('#tabelJasa tbody tr').each(function() {
                    initSelect2Jasa($(this));
                });
                
                $('#tabelBarang tbody tr').each(function() {
                    initSelect2Barang($(this));
                });

                // Nonaktifkan Enter di seluruh form kecuali input tertentu
                $(window).keydown(function(event) {
                    if (event.key === "Enter") {
                        if ($(event.target).is('#barcode-search') || $(event.target).is('#input-qty')) {
                            return true;
                        }
                        event.preventDefault();
                        return false;
                    }
                });

                // Initialize typeahead
                initTypeahead();

                // Handle Enter di input qty
                $('#input-qty').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        
                        let selectedItem = $('#barcode-search').data('selected-item');
                        let qty = parseInt($(this).val()) || 1;
                        
                        if (selectedItem) {
                            addBarangRow(selectedItem, qty);
                            $('#barcode-search').removeData('selected-item');
                            $(this).val(1);
                        } else {
                            focusToBarcode();
                        }
                    }
                });

                // Handle Enter untuk barcode
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        let barcode = $(this).val().trim();
                        
                        enterPressed = true;
                        
                        if(barcode) {
                            $.ajax({
                                url: '{{ route('bengkel.getbarangbycode') }}',
                                method: 'GET',
                                data: { kode: barcode },
                                dataType: 'json',
                                beforeSend: function() { loader(true); },
                                success: function(response) {
                                    if (response.stok <= 0) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "warning",
                                            title: "Stok habis!",
                                            text: `Produk "${response.text}" tidak tersedia`,
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                        clearBarcodeSearch();
                                        focusToBarcode();
                                        loader(false);
                                        return;
                                    }
                                    
                                    $('#barcode-search').data('selected-item', response);
                                    focusToQtyInput();
                                    loader(false);
                                },
                                error: function() {
                                    Swal.fire({
                                        position: "top-end",
                                        icon: "error",
                                        title: "Barang tidak ditemukan!",
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    clearBarcodeSearch();
                                    focusToBarcode();
                                    loader(false);
                                }
                            });
                        }
                        
                        setTimeout(() => {
                            enterPressed = false;
                        }, 100);
                    }
                });

                // Tambah Jasa
                $('#tambahJasa').on('click', function() {
                    addJasaRow();
                });

                // Tambah Barang manual
                $('#tambahBarang').on('click', function() {
                    addBarangRow();
                });

                // Submit form revisi
                $('#formTransaksi').on('submit', function(e) {
                    e.preventDefault();
                    
                    if($('#tabelJasa tbody tr').length === 0 && $('#tabelBarang tbody tr').length === 0) {
                        Swal.fire({
                            icon: "warning",
                            title: "Tidak ada item",
                            text: "Minimal harus ada 1 jasa atau 1 barang"
                        });
                        return;
                    }
                    
                    // Validasi semua jasa sudah dipilih
                    let semuaJasaTerpilih = true;
                    $('#tabelJasa tbody tr').each(function() {
                        if (!$(this).find('.namajasa').val()) {
                            semuaJasaTerpilih = false;
                            return false;
                        }
                    });
                    
                    if (!semuaJasaTerpilih) {
                        Swal.fire({
                            icon: "warning",
                            title: "Jasa belum lengkap",
                            text: "Pastikan semua jasa sudah dipilih"
                        });
                        return;
                    }
                    
                    // Validasi semua barang sudah dipilih
                    let semuaBarangTerpilih = true;
                    $('#tabelBarang tbody tr').each(function() {
                        if (!$(this).find('.namabarang').val()) {
                            semuaBarangTerpilih = false;
                            return false;
                        }
                    });
                    
                    if (!semuaBarangTerpilih) {
                        Swal.fire({
                            icon: "warning",
                            title: "Barang belum lengkap",
                            text: "Pastikan semua barang sudah dipilih"
                        });
                        return;
                    }
                    
                    Swal.fire({
                        title: "Simpan Revisi?",
                        text: "Stok akan dikembalikan dan dikurangi ulang",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#ffc107",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Ya, Simpan Revisi!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processRevise();
                        }
                    });
                });

                function processRevise() {
                    // Kumpulkan data items
                    let items = [];
                    
                    // Data jasa
                    $('#tabelJasa tbody tr').each(function() {
                        items.push({
                            jenis: 'jasa',
                            id: $(this).find('.idjasa').val(),
                            harga: parseFloat($(this).find('.harga-jasa').val()) || 0
                        });
                    });
                    
                    // Data barang
                    $('#tabelBarang tbody tr').each(function() {
                        items.push({
                            jenis: 'barang',
                            id: $(this).find('.idbarang').val(),
                            qty: parseInt($(this).find('.barangqty').val()) || 0,
                            harga: parseFloat($(this).find('.hargajual').val()) || 0
                        });
                    });
                    
                    // Tambahkan items ke form data
                    let formData = new FormData($('#formTransaksi')[0]);
                    formData.append('items', JSON.stringify(items));
                    
                    $.ajax({
                        url: "{{ route('bengkel.revise.update', ':id') }}".replace(':id', transaksiId),
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() { loader(true); },
                        success: function(res) {
                            loader(false);
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Transaksi berhasil direvisi',
                                icon: 'success',
                                confirmButtonText: 'Lihat Nota'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    let notaUrl = '{{ route("bengkel.nota", ":invoice") }}'.replace(':invoice', res.invoice);
                                    window.open(notaUrl, '_blank');
                                    window.location.href = '{{ route("bengkel.riwayat") }}';
                                } else {
                                    window.location.href = '{{ route("bengkel.riwayat") }}';
                                }
                            });
                        },
                        error: function(xhr) {
                            loader(false);
                            let errorMessage = 'Terjadi kesalahan saat merevisi transaksi';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: "Error!",
                                text: errorMessage,
                                icon: "error"
                            });
                        }
                    });
                }

                // Initial calculation
                kalkulasi();
            });
        </script>
    </x-slot>
</x-app-layout>