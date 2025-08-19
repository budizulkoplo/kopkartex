<x-app-layout>
    <x-slot name="pagetitle">Penjualan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6"><h3 class="mb-0">Form Penjualan</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Penjualan</li>
                        <li class="breadcrumb-item active" aria-current="page">Form</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmterima" autocomplete="off">
                <div class="card card-success card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Penjualan - {{ $unit->nama_unit }}</h5>
                    </div>
                    <div class="card-body p-3">
                        {{-- Header: Tanggal, Customer, Kasir, Barcode --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Tanggal</span>
                                    <input type="text" class="form-control datepicker" name="tanggal" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Petugas</span>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" name="kasir" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Barang</span>
                                    <input type="text" class="form-control" id="barcode-search">
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

                        {{-- Tabel Barang --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <table id="tbterima" class="table table-sm table-striped table-bordered" style="width:100%; font-size:small;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>@Harga</th>
                                            <th>Stok</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div class="mb-2 text-end">
                                    <button type="button" class="btn btn-primary btn-sm" id="tambahBarang">
                                        <i class="bi bi-plus"></i> Tambah Barang
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Subtotal</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="subtotal" id="subtotal" disabled>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Diskon</span>
                                    <input type="number" class="form-control" value="0" id="diskon" onfocus="this.select()" onkeyup="kalkulasi()" name="diskon">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Grand Total</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="grandtotal" id="grandtotal" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Metode</span>
                                    <select class="form-select form-select-sm" id="metodebayar" name="metodebayar" required>
                                        <option value="tunai" selected>Tunai</option>
                                        <option value="cicilan">Cicilan</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-sm mb-2 align-items-center">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" value="" id="flexCheckDefault" checked>
                                        <label for="flexCheckDefault" class="mb-0">Anggota</label>
                                    </div>
                                    <input type="text" class="form-control" id="customer" name="customer" required autocomplete="off">
                                    <input type="hidden" id="idcustomer" name="idcustomer">
                                </div>
                                <div class="input-group input-group-sm mb-2 fieldcicilan" style="display: none">
                                    <span class="input-group-text label-fixed-width">Jml.Cicilan</span>
                                    <select class="form-select form-select-sm" id="jmlcicilan" name="jmlcicilan"></select>
                                </div>
                                <div class="input-group input-group-sm mb-2 clmetode">
                                    <span class="input-group-text label-fixed-width">Dibayar</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="dibayar" id="dibayar" required>
                                </div>
                                <div class="input-group input-group-sm mb-2 clmetode">
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

                        {{-- Tombol --}}
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
    <script>
        let globtot = 0, users = [], barang = [], selectedFromList = false;

        // Loader
        function loader(onoff){
            if(onoff)
                $('.app-wrapper').waitMe({effect:'bounce', text:'', bg:'rgba(255,255,255,0.7)', color:'#000', waitTime:-1, textPos:'vertical'});
            else
                $('.app-wrapper').waitMe('hide');
        }

        // Format Rupiah
        function formatRupiahWithDecimal(angka){
            return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(angka);
        }

        // Numbering
        function numbering(){
            $('#tbterima tbody tr').each(function(i){ $(this).find('td:first').text(i+1); });
        }

        // Kalkulasi
        function kalkulasi(obj){
            let subtotal = 0;
            $('#tbterima tbody tr').each(function(){
                let row = $(this);
                let qty = parseInt(row.find('.barangqty').val()) || 0;
                let harga = parseInt(row.find('.hargajual').val()) || 0;
                let stok = parseInt(row.find('.stok').val()) || 0;

                if(obj && qty>stok){
                    row.find('.barangqty').val(0);
                    Swal.fire({icon:'warning',title:'Melebihi stok!',timer:1500,showConfirmButton:false});
                    qty=0;
                }

                let total = qty * harga;
                row.find('.totalitm').html(total);
                subtotal += total;
            });

            let diskon = parseFloat($('#diskon').val()) || 0;
            let grandtotal = subtotal * (1 - diskon/100);
            $('#subtotal').val(subtotal);
            $('#grandtotal').val(grandtotal);
            $('.topgrandtotal').text(formatRupiahWithDecimal(grandtotal));
            let dibayar = parseFloat($('#dibayar').val()) || 0;
            $('#kembali').val(dibayar - grandtotal);
            window.globtot = grandtotal;
        }

        // Clear form
        function clearform(){
            $('#frmterima')[0].reset();
            $('#tbterima tbody').empty();
            $('.topgrandtotal').text('Rp.0');
            $('#metodebayar').val('tunai').trigger('change');
        }

        // Typeahead Customer
        function activateTypeahead(){
            $('#customer').typeahead({
                source: function(query, process){
                    return $.ajax({
                        url:'{{ route('jual.getanggota') }}',
                        type:'GET', data:{query:query}, dataType:'json',
                        success:function(data){
                            users=data;
                            return process(data.map(u=>u.name));
                        }
                    });
                },
                afterSelect:function(name){
                    let selected = users.find(u=>u.name===name);
                    if(selected) $('#idcustomer').val(selected.id);
                }
            });
        }

        // Tambah row manual
        function addRow(datarow = null) {
            // default datarow jika tidak ada
            datarow = datarow || {id:0, code:'', text:'', harga_jual:0, stok:0};

            let newRow = $(`
                <tr data-id="${datarow.id}">
                    <td></td>
                    <td>
                        <input type="text" class="form-control form-control-sm kodebarang" value="${datarow.code}" disabled>
                    </td>
                    <td>
                        <select class="form-select form-select-sm namabarang" style="width:100%" name="idbarang[]">
                            ${datarow.id ? `<option value="${datarow.id}" selected>${datarow.text}</option>` : ''}
                        </select>
                    </td>
                    <td><input type="number" class="form-control form-control-sm hargajual" value="${datarow.harga_jual}" readonly></td>
                    <td>
                        <input type="number" class="form-control form-control-sm stok" value="${datarow.stok}" readonly>
                    </td>
                    <td><input type="number" class="form-control form-control-sm barangqty" min="1" value="1" onkeyup="kalkulasi(this)"></td>
                    <td class="totalitm"></td>
                    <td><span class="badge btn bg-danger dellist"><i class="bi bi-trash3-fill"></i></span></td>
                </tr>
            `);

            $('#tbterima tbody').append(newRow);
            numbering();
            kalkulasi();

            // inisialisasi select2 untuk row baru
            newRow.find('.namabarang').select2({
                placeholder: "Pilih barang",
                ajax: {
                    url: '{{ route("jual.getbarang") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) { return { q: params.term }; },
                    processResults: function(data) {
                        return {
                            results: data.map(b => ({id: b.code, text: b.text, harga_jual: b.harga_jual, stok: b.stok}))
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function(e){
                let data = e.params.data;
                let row = $(this).closest('tr');
                row.find('.kodebarang').val(data.id); // bisa juga data.code kalau ada
                row.find('.hargajual').val(data.harga_jual);
                row.find('.stok').val(data.stok);
                row.find('.barangqty').val(1);
                kalkulasi();
            });

            // tombol hapus
            newRow.find('.dellist').on('click', function(){
                $(this).closest('tr').remove();
                numbering();
                kalkulasi();
            });
        }

        $(document).ready(function(){
            $('.datepicker').datepicker({format:'dd-mm-yyyy', autoclose:true, todayHighlight:true}).datepicker('setDate', new Date());
            activateTypeahead();

            // Tambah row button
            $('#tambahBarang').on('click', function(){
                addRow({id:0, code:'', text:'', harga_jual:0, stok:0});
            });

            // Hapus row
            $(document).on('click','.hapus-baris', function(){ $(this).closest('tr').remove(); kalkulasi(); numbering(); });

            // Barcode search
            let currentRequest=null;
            $('#barcode-search').typeahead({
                source:function(query, process){
                    if(currentRequest) currentRequest.abort();
                    currentRequest = $.ajax({
                        url:'{{ route('jual.getbarang') }}', data:{q:query}, dataType:'json',
                        success:function(data){ barang=data; return process(data.map(b=>b.text)); }
                    });
                    return currentRequest;
                },
                afterSelect:function(text){
                    let selected = barang.find(b=>b.text===text);
                    if(selected) addRow(selected);
                    $('#barcode-search').val('');
                }
            });

            // Enter pada barcode
            $('#barcode-search').on('keydown', function(e){
                if(e.key==='Enter'){
                    $.ajax({
                        url:'{{ route('jual.getbarangbycode') }}',
                        data:{kode:$(this).val()},
                        dataType:'json',
                        success:function(resp){ addRow(resp); $('#barcode-search').val(''); },
                        error:function(){ Swal.fire({icon:'error', title:'Barang tidak ditemukan!', showConfirmButton:false, timer:1500}); $('#barcode-search').val(''); }
                    });
                }
            });

            // Metode bayar
            $('#metodebayar').on('change', function(){
                if($(this).val()=='cicilan'){
                    let maxcicil = (globtot<=1000000)?3:(globtot<=2000000)?5:(globtot<=3000000)?10:(globtot<=4000000)?15:(globtot<=5000000)?20:25;
                    let str='';
                    for(let i=1;i<=maxcicil;i++){ str+=`<option value="${i}">${i}x</option>`; }
                    $('#jmlcicilan').html(str);
                    $('.fieldcicilan').show();
                    $('.clmetode').hide().find('input,select').prop('required',false).val('');
                } else {
                    $('.fieldcicilan').hide(); $('#jmlcicilan').html('');
                    $('.clmetode').show().find('input,select').prop('required',true);
                }
            });

            // Submit form
            $('#frmterima').on('submit', function(e){
                e.preventDefault();
                if(!this.checkValidity()){ e.stopPropagation(); return; }
                let formData = new FormData(this);
                $(this).find(':input:disabled').each(function(){ formData.append(this.name, $(this).val()); });

                $.ajax({
                    type:'POST',
                    url:'{{ route('jual.store') }}',
                    data:formData, processData:false, contentType:false,
                    beforeSend:()=>loader(true),
                    success:function(resp){ Swal.fire({icon:'success',title:'Tersimpan',timer:1500,showConfirmButton:false}); clearform(); loader(false); window.open(`{{ url('/penjualan/nota') }}/${resp.invoice}`,'_blank'); },
                    error:function(){ alert('Something went wrong'); loader(false); }
                });
            });
        });
    </script>
    </x-slot>
</x-app-layout>
