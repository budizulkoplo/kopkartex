<x-app-layout>
    <x-slot name="pagetitle">Stock Opname</x-slot>

    <div class="app-content-header mb-3"></div>

    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmterima">
                <div class="card card-success card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Stock Opname</h5>
                    </div>
                    <div class="card-body p-3">

                        {{-- Informasi barang terpilih --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="alert alert-info py-2 mb-1">
                                    <strong>Kode:</strong> {{ $selectedBarang->code ?? '-' }}<br>
                                    <strong>Nama Barang:</strong> {{ $selectedBarang->text ?? '-' }}
                                </div>
                                <input type="hidden" name="barang_id" value="{{ $selectedBarang->id ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-secondary py-2 mb-1">
                                    <strong>Petugas:</strong> {{ auth()->user()->name }}<br>
                                    <strong>Tanggal:</strong> <span id="opname-date"></span>
                                    <input type="text" name="tgl_opname" id="tgl_opname_input">
                                </div>
                            </div>
                        </div>

                        {{-- Table untuk input qty + expdate --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <table id="tbterima" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Qty</th>
                                            <th>ExpDate</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                                    <i class="bi bi-plus-circle"></i> Tambah Baris
                                </button>
                            </div>
                        </div>

                        <div class="row align-items-start">
                            <div class="col-md-4 d-flex gap-2">
                                <a href="/stock" class="btn btn-secondary">
                                    ← Kembali ke List Barang
                                </a>
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
            function numbering(){
                $('#tbterima tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            function addRow(){
                let row = `
                    <tr class="align-middle">
                        <td></td>
                        <td>
                            <input type="hidden" class="form-control form-control-sm"  name="code[]" value="{{ $selectedBarang->code}}" required>
                            <input type="hidden" class="form-control form-control-sm"  name="id[]" value="{{ $selectedBarang->id}}" required>
                            <input type="number" class="form-control form-control-sm qty" min="1" name="qty[]" value="0" required>
                        </td>
                        <td>
                            <input type="date" class="form-control form-control-sm" name="exp[]" required>
                        </td>
                        <td>
                            <span class="badge bg-danger dellist" onclick="$(this).closest('tr').remove(); numbering();">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>
                `;
                $('#tbterima tbody').append(row);
                numbering();
            }

            function clearform(){
                $('#tbterima tbody').empty();
                numbering();
            }

            $(document).ready(function () {
                const today = new Date();
                const formatted = today.toLocaleDateString('en-GB').split('/').join('-');
                $('#opname-date').text(formatted);
                $('#tgl_opname_input').val(formatted);

                addRow(); // start with 1 row

                $('#frmterima').on('submit', function(e) {
                    e.preventDefault();
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        this.classList.add('was-validated');
                    } else {
                        Swal.fire({
                            title: "Simpan data?",
                            text: "Data akan disimpan!",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Ya, simpan!"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: 'POST',
                                    url: '{{ route('stockopname.store') }}',
                                    data: $(this).serialize(),
                                    success: function(response) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil disimpan!',
                                            showConfirmButton: false,
                                            timer: 1000
                                        });
                                        setTimeout(function() {
                                            window.location.href = '/stock';
                                        }, 1000);
                                    },
                                    error: function(xhr) {
                                        alert('Terjadi kesalahan saat menyimpan!');
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
