<x-app-layout>
    <x-slot name="pagetitle">Transaksi Bengkel</x-slot>
    <div class="app-content">
        <div class="container">
            <form id="formTransaksi" autocomplete="off">
                @csrf
                <div class="card card-success card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Transaksi Bengkel</h5>
                    </div>
                    <div class="card-body p-3">
                        <!-- ===== HEADER ===== -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">No Invoice</span>
                                    <input type="text" class="form-control" value="{{ $invoice }}" readonly name="invoice">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Tanggal</span>
                                    <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Customer</span>
                                    <input type="text" class="form-control" name="customer">
                                </div>
                            </div>
                        </div>

                        <!-- ===== TABEL JASA ===== -->
                        <h6 class="fw-bold mt-3">Jasa Bengkel</h6>
                        <table class="table table-sm table-striped table-bordered" id="tabelJasa" style="font-size: small;">
                            <thead>
                                <tr>
                                    <th>Nama Jasa</th>
                                    <th>Harga</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="button" id="tambahJasa" class="btn btn-sm btn-primary mb-3">
                            <i class="bi bi-plus"></i> Tambah Jasa
                        </button>

                        <!-- ===== TABEL BARANG ===== -->
                        <h6 class="fw-bold">Barang</h6>
                        <table class="table table-sm table-striped table-bordered" id="tabelBarang" style="font-size: small;">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Qty</th>
                                    <th>Harga Jual</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="button" id="tambahBarang" class="btn btn-sm btn-success mb-3">
                            <i class="bi bi-plus"></i> Tambah Barang
                        </button>

                        <!-- ===== TOTAL ===== -->
                        <div class="row mt-3">
                            <div class="col-md-4 offset-md-8">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Subtotal</span>
                                    <input type="number" class="form-control" name="subtotal" readonly>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Diskon</span>
                                    <input type="number" class="form-control" name="diskon" value="0">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Grand Total</span>
                                    <input type="number" class="form-control" name="grandtotal" readonly>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Dibayar</span>
                                    <input type="number" class="form-control" name="dibayar">
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Kembali</span>
                                    <input type="number" class="form-control" name="kembali" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- ===== BUTTON SIMPAN ===== -->
                        <div class="mt-3">
                            <button type="submit" class="btn btn-lg btn-primary">
                                <i class="bi bi-save"></i> Simpan Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function(){

        // Tambah baris jasa
        $('#tambahJasa').click(function(){
            let row = `<tr>
                <td><select name="jasa_id[]" class="form-control select2-jasa" required></select></td>
                <td><input type="number" name="jasa_harga[]" class="form-control harga" readonly></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm hapus-barang">Hapus</button></td>
            </tr>`;
            $('#tabelJasa tbody').append(row);
            initSelect2Jasa();
        });

        // Tambah baris barang
        $('#tambahBarang').click(function(){
            let row = `<tr>
                <td><select name="idbarang[]" class="form-control select2-barang" required></select></td>
                <td><input type="number" name="qty[]" class="form-control qty" value="1"></td>
                <td><input type="number" name="harga_jual[]" class="form-control harga" readonly></td>
                <td><input type="number" name="total[]" class="form-control total" readonly></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm hapus-barang">Hapus</button></td>
            </tr>`;
            $('#tabelBarang tbody').append(row);
            initSelect2Barang();
        });

        // Select2 barang
        function initSelect2Barang(){
            $('.select2-barang').select2({
                ajax: {
                    url: "{{ route('bengkel.getbarang') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { q: params.term }; },
                    processResults: function (data) { return { results: data }; }
                }
            }).on('select2:select', function(e){
                let data = e.params.data;
                $(this).closest('tr').find('.harga').val(data.harga_jual);
                hitungTotal();
            });
        }

        // Select2 jasa
        function initSelect2Jasa(){
            $('.select2-jasa').select2({
                ajax: {
                    url: "{{ route('bengkel.getjasa') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { q: params.term }; },
                    processResults: function (data) { return { results: data }; }
                }
            }).on('select2:select', function(e){
                let data = e.params.data;
                $(this).closest('tr').find('.harga').val(data.harga);
                hitungTotal();
            });
        }

        // Hitung total
        $(document).on('input', '.qty', function(){
            let tr = $(this).closest('tr');
            let harga = parseFloat(tr.find('.harga').val()) || 0;
            let qty = parseFloat($(this).val()) || 0;
            tr.find('.total').val(harga * qty);
            hitungTotal();
        });

        function hitungTotal(){
            let subtotal = 0;
            $('#tabelJasa .harga').each(function(){ subtotal += parseFloat($(this).val()) || 0; });
            $('#tabelBarang .total').each(function(){ subtotal += parseFloat($(this).val()) || 0; });
            $('input[name="subtotal"]').val(subtotal);
            let diskon = parseFloat($('input[name="diskon"]').val()) || 0;
            let grandtotal = subtotal - diskon;
            $('input[name="grandtotal"]').val(grandtotal);
            let dibayar = parseFloat($('input[name="dibayar"]').val()) || 0;
            $('input[name="kembali"]').val(dibayar - grandtotal);
        }

        $(document).on('input', 'input[name="diskon"], input[name="dibayar"]', function(){
            hitungTotal();
        });

        // Hapus baris
        $(document).on('click', '.hapus-barang', function(){
            $(this).closest('tr').remove();
            hitungTotal();
        });

        // Submit form
        $('#formTransaksi').submit(function(e){
            e.preventDefault();
            $.ajax({
                url: "{{ route('bengkel.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res){
                    alert(res.message);
                    window.location.reload();
                },
                error: function(err){
                    alert("Gagal menyimpan transaksi");
                }
            });
        });

    });
    </script>
    @endpush
</x-app-layout>
