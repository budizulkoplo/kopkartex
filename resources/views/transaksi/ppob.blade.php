<x-app-layout>
    <x-slot name="pagetitle">Transaksi PPOB</x-slot>

    <div class="app-content">
        <div class="container">
            <form id="frmppob" class="needs-validation" novalidate>
                <div class="card card-info card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Transaksi PPOB</h5>
                    </div>
                    <div class="card-body p-3">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Tanggal</span>
                                    <input type="text" class="form-control datepicker" name="tanggal" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Kategori</span>
                                    <select class="form-select" name="kategori" id="kategori" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="pulsa">Pulsa</option>
                                        <option value="token">Token Listrik</option>
                                        <option value="tagihan">Tagihan Listrik</option>
                                        <option value="bpjs">BPJS</option>
                                        <option value="pdam">PDAM</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">No. Tujuan / ID Pelanggan</span>
                                    <input type="text" class="form-control" name="id_pelanggan" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Nominal</span>
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="nominal" id="nominal" onkeyup="updateTotal()" onfocus="this.select()" required>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Admin Fee</span>
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="admin" id="admin" value="1500" onkeyup="updateTotal()" onfocus="this.select()" required>
                                </div>
                            </div>

                            <div class="col-md-4 text-end">
                                <label class="form-label fw-bold">Total Bayar</label>
                                <div class="fs-3 fw-bold text-success" id="totalBayarText">Rp. 0</div>
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-auto d-flex gap-2">
                                <button type="reset" class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i> Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Bayar</button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="csscustom">
        <link rel="stylesheet" href="{{ asset('plugins/BootstrapDatePicker/bootstrap-datepicker.min.css') }}">
    </x-slot>

    <x-slot name="jscustom">
        <script src="{{ asset('plugins/BootstrapDatePicker/bootstrap-datepicker.min.js') }}"></script>
        <script src="{{ asset('plugins/sweetalert2@11.js') }}"></script>
        <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true
            }).datepicker('setDate', new Date());

            $('#frmppob').on('submit', function(e) {
                e.preventDefault();
                if (!this.checkValidity()) {
                    this.classList.add('was-validated');
                    return;
                }

                $.ajax({
                    url: '{{ route("ppob.transaksi") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function () {
                        Swal.showLoading();
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Transaksi PPOB berhasil!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#frmppob')[0].reset();
                        updateTotal();
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Transaksi gagal diproses.'
                        });
                    }
                });
            });
        });

        function updateTotal() {
            let nominal = parseInt($('#nominal').val()) || 0;
            let admin = parseInt($('#admin').val()) || 0;
            let total = nominal + admin;
            $('#totalBayarText').text(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total));
        }
        </script>
    </x-slot>
</x-app-layout>
