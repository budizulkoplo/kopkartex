<x-app-layout>
    <x-slot name="pagetitle">Penjualan Umum</x-slot>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Form Penjualan Umum - {{ $unit->nama_unit }}</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Penjualan</li>
                        <li class="breadcrumb-item active" aria-current="page">Umum</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmterima" autocomplete="off">
                <div class="card card-success card-outline mb-4">
                    <div class="card-body p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Tanggal</span>
                                    <input type="text" class="form-control datepicker" name="tanggal" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                                <div class="input-group input-group-sm mb-2 align-items-center">
                                    <div class="input-group-text">
                                        <label class="mb-0">Customer</label>
                                    </div>
                                    <input type="text" class="form-control" id="customer" name="customer" required autocomplete="off">
                                    <input type="hidden" id="idcustomer" name="idcustomer" value="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Petugas</span>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" name="kasir" disabled>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Barang</span>
                                    <input type="text" class="form-control typeahead" id="barcode-search">
                                    <input type="hidden" id="barcode-id">
                                    <span class="input-group-text bg-primary"><i class="fa-solid fa-barcode text-white"></i></span>
                                </div>
                            </div>

                            <div class="col-md-4 text-end">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Invoice</label>
                                    <div class="fs-6 fw-bold txtinv">{{ $invoice }}</div>
                                </div>
                                <div class="fs-3 fw-bold text-success topgrandtotal">Rp. 0</div>
                            </div>
                        </div>

                        <!-- Tabel barang, subtotal, dll sama seperti Penjualan.blade.php -->
                        <!-- ... -->

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Subtotal</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="subtotal" id="subtotal" disabled>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Diskon</span>
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="diskon" id="diskon">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Grand Total</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="grandtotal" id="grandtotal" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Metode bayar hanya tunai untuk umum -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Dibayar</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="dibayar" id="dibayar" required>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Kembali</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="kembali" id="kembali" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-3"> 
                                    <span class="input-group-text label-fixed-width">Catatan</span> 
                                    <textarea class="form-control" name="note" rows="3"></textarea> 
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-auto d-flex gap-2">
                                <button type="button" class="btn btn-warning" onclick="clearform();"><i class="bi bi-arrow-clockwise"></i> Batal</button>
                                <button type="submit" class="btn btn-success"><i class="bi bi-floppy-fill"></i> Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <x-slot name="jscustom">
        <script src="{{ asset('plugins/loader/waitMe.js') }}"></script>
        <script>
            var globtot=0;
            var barang = [];
            
            function invoice(){
                $.ajax({
                    url: '{{ route('jual.umum.getinv') }}', // Route baru untuk invoice umum
                    method: 'GET',
                    dataType: 'json',
                    beforeSend: function(xhr) {loader(true);},
                    success: function(response) {
                        $('.txtinv').text(response);
                        loader(false);
                    },
                    error: function(xhr, status, error) {loader(false);}
                });
            }
            
            // Modifikasi addRow untuk penjualan umum
            function addRow(datarow = null){
                datarow = datarow || {id:0, code:'', text:'', harga_jual:0, stok:0};
                
                let newRow = $(`<tr data-id="`+datarow.id+`" class="align-middle">
                    <td></td>
                    <td class="kodebarangtext">`+datarow.code+`</td>
                    <td>
                        <select class="form-select form-select-sm namabarang" style="width:100%" name="idbarang[]">
                        ${datarow.id ? `<option value="${datarow.id}" selected>${datarow.text}</option>` : ''}
                        </select>
                    </td>
                    <td class="hargajualtext">`+datarow.harga_jual+`</td>
                    <td><span class="stoktext">`+datarow.stok+`</span><input type="hidden" name="stok[]" class="stok" value="`+datarow.stok+`"></td>
                    <td>
                        <input type="number" class="form-control form-control-sm w-auto barangqty" onfocus="this.select()" min="1" max="`+datarow.stok+`" name="qty[]" onkeyup="kalkulasi(this)" value="1" data-id="`+datarow.id+`" required>
                        <input type="hidden" name="harga_beli[]" class="hargabeli" value="`+datarow.harga_beli+`">
                        <input type="hidden" name="harga_jual[]" class="hargajual" value="`+datarow.harga_jual+`">
                    </td>
                    <td class="totalitm"></td>
                    <td><span class="badge btn bg-danger dellist" onclick="$(this).parent().parent().remove();kalkulasi();numbering();"><i class="bi bi-trash3-fill"></i></span></td>
                </tr>`);
                
                $('#tbterima tbody').append(newRow);
                numbering();
                kalkulasi();
                
                // Inisialisasi select2 untuk penjualan umum
                newRow.find('.namabarang').select2({
                    placeholder: "Pilih barang",
                    ajax: {
                        url: '{{ route('jual.umum.getbarang') }}', // Route baru untuk barang umum
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
                                    harga_beli: b.harga_beli, 
                                    harga_jual: b.harga_jual, 
                                    stok: b.stok,
                                    kategori_cicilan: b.kategori_cicilan
                                }))
                            };
                        },
                        cache: true
                    }
                }).on('select2:select', function(e){
                    let data = e.params.data;
                    let row = $(this).closest('tr');
                    row.attr("data-id", data.id);
                    row.find('.kodebarangtext').text(data.code);
                    row.find('.hargajual').val(data.harga_jual);
                    row.find('.hargajualtext').text(data.harga_jual);
                    row.find('.hargabeli').val(data.harga_beli);
                    row.find('.stoktext').text(data.stok);
                    row.find('.stok').val(data.stok);
                    row.find('.barangqty').val(1);
                    row.find('.barangqty').attr("max", data.stok);
                    row.find('.barangqty').attr("data-id", data.id);
                    kalkulasi();
                });

                newRow.find('.dellist').on('click', function(){
                    $(this).closest('tr').remove();
                    numbering();
                    kalkulasi();
                });
                
                $('#barcode-search').val('');
            }
            
            $(document).ready(function () {
                // ... (kode lainnya sama) ...
                
                // Typeahead untuk barcode search (umum)
                $('#barcode-search').typeahead({
                    source: function (query, process) {
                        $.ajax({
                            url: '{{ route('jual.umum.getbarang') }}',
                            type: 'GET',
                            data: { q: query },
                            dataType: 'json',
                            success: function (data) {
                                barang = data;
                                return process(data.map(barang => barang.text));
                            }
                        });
                    },
                    afterSelect: function (text) {
                        const selected = barang.find(barang => barang.text === text);
                        if (selected) {
                            addRow(selected);
                        }
                    }
                });
                
                // Enter untuk barcode (umum)
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        $.ajax({
                            url: '{{ route('jual.umum.getbarangbycode') }}',
                            method: 'GET',
                            data: { kode: $(this).val() },
                            dataType: 'json',
                            beforeSend: function(xhr) {loader(true);},
                            success: function(response) {
                                addRow(response);
                                loader(false);
                                $('#barcode-search').val('');
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    position: "top-end",
                                    icon: "error",
                                    title: "Barang tidak ditemukan!",
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#barcode-search').val('');
                                loader(false);
                            }
                        });
                    }
                });
                
                // Submit form untuk penjualan umum
                $('#frmterima').on('submit', function(e) {
                    e.preventDefault(); 
                    
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                    } else {
                        Swal.fire({
                            title: "Transaksi sekarang?",
                            text: "Pastikan data sudah benar",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Ya, lanjutkan!"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var form = $(this)[0];
                                var formData = new FormData(form);

                                $(form).find(':input:disabled').each(function() {
                                    formData.append(this.name, $(this).val());
                                });

                                $.ajax({
                                    type: 'POST',
                                    url: '{{ route('jual.umum.store') }}', // Route baru untuk store umum
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    beforeSend: function(xhr) {loader(true);},
                                    success: function(response) {
                                        clearform();
                                        loader(false);
                                        invoice();
                                        Swal.fire({
                                            title: 'Berhasil!',
                                            text: 'Nota penjualan umum berhasil dibuat',
                                            icon: 'success',
                                            confirmButtonText: 'Lihat Nota'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                const url = `{{ url('/penjualan/nota') }}/${response.invoice}`;
                                                window.open(url, '_blank');
                                            }
                                        });
                                    },
                                    error: function(xhr) {
                                        loader(false);
                                        Swal.fire({
                                            title: "Error!",
                                            text: xhr.responseText,
                                            icon: "error"
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>