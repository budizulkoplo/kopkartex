<x-app-layout>
    <x-slot name="pagetitle">Barang Bengkel</x-slot>
    
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Master Data Barang Bengkel</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-warning card-outline mb-4">
                        <div class="card-header pt-1 pb-1">
                            <div class="card-title">
                                <div class="row row-cols-auto">
                                    <div class="col">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-warning text-white">Kategori</span>
                                            <select class="form-select form-select-sm" id="fkategori">
                                                <option value="all">SEMUA</option>
                                                @foreach ($kategori as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalBarang" id="btnadd">
                                    <i class="bi bi-file-earmark-plus"></i> Tambah
                                </button>
                                <button class="btn btn-sm btn-success ms-1" data-bs-toggle="modal" data-bs-target="#modalQuickAdd">
                                    <i class="bi bi-lightning"></i> Quick Add
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tbbarang" class="table table-sm table-striped" style="width:100%; font-size: small;">
                                <thead class="table-warning">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Kode</th>
                                        <th width="20%">Nama</th>
                                        <th width="10%">Kategori</th>
                                        <th width="10%">Satuan</th>
                                        <th width="10%">Stok</th>
                                        <th width="10%">Harga Beli</th>
                                        <th width="10%">Harga Jual</th>
                                        <th width="5%">Foto</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    <div class="modal fade" id="modalBarang" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="frmbarang" class="needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalTitle">Form Barang Bengkel</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idbarang" id="idbarang">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="kode_barang" id="kode_barang" required>
                                    <button type="button" class="btn btn-outline-warning" id="btnGenerateCode">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Klik tombol untuk generate kode otomatis</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="nama_barang" required maxlength="255">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="idkategori" id="idkategori" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategori as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="idsatuan" id="idsatuan" required>
                                    <option value="">Pilih Satuan</option>
                                    @foreach ($satuan as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Harga Beli</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_beli" id="hargaBeli" min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Harga Jual</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_jual" id="hargaJual" min="0" step="1000">
                                </div>
                                <small class="text-muted" id="hargaInfo"></small>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Foto Produk</label>
                                <input type="file" class="form-control form-control-sm" name="img" accept="image/*" id="imgInput">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="hapus_gambar" value="1" id="hapusGambar">
                                    <label class="form-check-label" for="hapusGambar">Hapus gambar saat update</label>
                                </div>
                                <div class="mt-2 text-center">
                                    <img id="previewImg" src="" style="max-height:150px; display:none;" class="img-thumbnail border-warning">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Stok Unit 5 (Bengkel)</label>
                                <input type="text" class="form-control form-control-sm" name="stok_display" id="stok" readonly>
                                <small class="text-muted">Stok saat ini (readonly)</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning" id="savebarang">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Quick Add --}}
    <div class="modal fade" id="modalQuickAdd" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-lightning"></i> Quick Add Barang
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="frmQuickAdd">
                        <div class="mb-3">
                            <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="quickKode" required>
                            <div class="form-text" id="quickKodeInfo"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="quickNama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="quickKategori" required>
                                <option value="">Pilih Kategori</option>
                                @foreach ($kategori as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="quickSatuan" required>
                                <option value="">Pilih Satuan</option>
                                @foreach ($satuan as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Harga Beli</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="quickHargaBeli" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Jual</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="quickHargaJual" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-text text-muted mt-2" id="quickHargaInfo"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="saveQuickAdd()">
                        <i class="bi bi-plus"></i> Tambah
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="csscustom">
        <style>
            .table-warning th {
                background-color: #ffc107;
                color: #212529;
            }
            .stok-positive {
                color: #198754;
                font-weight: bold;
            }
            .stok-zero {
                color: #dc3545;
                font-weight: bold;
            }
        </style>
    </x-slot>

    <x-slot name="jscustom">
    <script>
        $(document).ready(function() {
            var table = $('#tbbarang').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('barangbengkel.getdata') }}",
                    data: function(d) {
                        d.kategori = $('#fkategori').val();
                    },
                    type: "GET"
                },
                columns: [
                    { data: "DT_RowIndex", name: "DT_RowIndex", searchable: false },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    { data: "kategori_nama" },
                    { data: "satuan_nama" },
                    { 
                        data: "stok",
                        render: function(data) {
                            const stokNum = parseInt(data.replace(/\./g, '')) || 0;
                            const stokClass = stokNum > 0 ? 'stok-positive' : 'stok-zero';
                            return `<span class="${stokClass}">${data}</span>`;
                        }
                    },
                    { data: "harga_beli" },
                    { data: "harga_jual" },
                    { 
                        data: "img", 
                        orderable: false, 
                        searchable: false,
                        render: function(data) {
                            if (data) {
                                return '<img src="/storage/produk/bengkel/' + data + '" class="img-thumbnail" style="max-height:50px;">';
                            }
                            return '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        data: "aksi",
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Filter change
            $('#fkategori').on('change', function() {
                table.ajax.reload();
            });

            // Generate kode otomatis
            $('#btnGenerateCode').on('click', function() {
                $.get("{{ route('barangbengkel.getcode') }}", function(code) {
                    $('#kode_barang').val(code);
                    cekKodeAvailability(code);
                });
            });

            // Cek kode unik saat input
            $('#kode_barang').on('blur', function() {
                const kode = $(this).val();
                if (kode) {
                    cekKodeAvailability(kode);
                }
            });

            function cekKodeAvailability(kode) {
                $.get("{{ route('barangbengkel.cekcode') }}", { code: kode }, function(count) {
                    if (count > 0 && !$('#idbarang').val()) {
                        $('#kode_barang').addClass('is-invalid');
                        if (!$('#kode_barang').next('.invalid-feedback').length) {
                            $('#kode_barang').after('<div class="invalid-feedback">Kode barang sudah digunakan!</div>');
                        }
                    } else {
                        $('#kode_barang').removeClass('is-invalid');
                        $('#kode_barang').next('.invalid-feedback').remove();
                    }
                });
            }

            // Validasi harga
            function validateHarga() {
                const hargaBeli = parseFloat($('#hargaBeli').val()) || 0;
                const hargaJual = parseFloat($('#hargaJual').val()) || 0;
                
                if (hargaJual > 0 && hargaBeli > 0 && hargaJual < hargaBeli) {
                    $('#hargaInfo').html('<span class="text-danger">Harga jual tidak boleh kurang dari harga beli</span>').show();
                    return false;
                } else {
                    $('#hargaInfo').html('').hide();
                    return true;
                }
            }

            $('#hargaBeli, #hargaJual').on('input', validateHarga);

            // Preview gambar
            $('#imgInput').on('change', function(evt) {
                const [file] = this.files;
                if (file) {
                    $('#previewImg').show().attr('src', URL.createObjectURL(file));
                    $('#hapusGambar').prop('checked', false);
                }
            });

            // Submit form
            $('#frmbarang').on('submit', function(e) {
                e.preventDefault();
                
                if (!validateHarga()) {
                    Swal.fire('Perhatian', 'Harga jual tidak boleh kurang dari harga beli!', 'warning');
                    return;
                }

                const formData = new FormData(this);
                
                $.ajax({
                    url: "{{ route('barangbengkel.store') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            $('#modalBarang').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error!', message, 'error');
                    }
                });
            });

            // Edit button
            $(document).on('click', '.editbtn', function() {
                const encryptedId = $(this).data('id');
                
                $.ajax({
                    url: "{{ route('barangbengkel.getdetail') }}",
                    method: "GET",
                    data: { id: encryptedId },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            $('#idbarang').val(encryptedId);
                            $('#kode_barang').val(data.kode_barang).prop('readonly', true);
                            $('input[name="nama_barang"]').val(data.nama_barang);
                            $('#idkategori').val(data.idkategori);
                            $('#idsatuan').val(data.idsatuan);
                            $('#hargaBeli').val(data.harga_beli);
                            $('#hargaJual').val(data.harga_jual);
                            $('#stok').val(data.stok);
                            
                            if (data.img) {
                                $('#previewImg').show().attr('src', '/storage/produk/bengkel/' + data.img);
                            } else {
                                $('#previewImg').hide().attr('src', '');
                            }
                            
                            $('#hapusGambar').prop('checked', false);
                            $('#modalTitle').text('Edit Barang Bengkel');
                            $('#modalBarang').modal('show');
                            
                            validateHarga();
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Gagal memuat data', 'error');
                    }
                });
            });

            // Delete button
            $(document).on('click', '.deletebtn', function() {
                const encryptedId = $(this).data('id');
                const row = $(this).closest('tr');
                const namaBarang = table.cell(row, 2).data();
                
                Swal.fire({
                    title: "Yakin hapus?",
                    html: `<strong>${namaBarang}</strong> akan dihapus permanen`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('barangbengkel.hapus') }}",
                            method: "DELETE",
                            data: { 
                                id: encryptedId,
                                _token: "{{ csrf_token() }}" 
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.ajax.reload();
                                    Swal.fire("Terhapus!", response.message, "success");
                                } else {
                                    Swal.fire("Error!", response.message, "error");
                                }
                            },
                            error: function() {
                                Swal.fire("Error!", "Gagal menghapus data", "error");
                            }
                        });
                    }
                });
            });

            // Reset form saat tambah baru
            $('#btnadd').on('click', function() {
                resetForm();
                $('#kode_barang').prop('readonly', false);
                $('#modalTitle').text('Tambah Barang Bengkel Baru');
                $('#btnGenerateCode').trigger('click');
            });

            function resetForm() {
                $('#frmbarang')[0].reset();
                $('#idbarang').val('');
                $('#previewImg').hide().attr('src', '');
                $('#hapusGambar').prop('checked', false);
                $('#hargaInfo').html('').hide();
                $('#kode_barang').removeClass('is-invalid');
                $('#kode_barang').next('.invalid-feedback').remove();
            }

            // Quick Add validation
            $('#quickHargaBeli, #quickHargaJual').on('input', function() {
                const hargaBeli = parseFloat($('#quickHargaBeli').val()) || 0;
                const hargaJual = parseFloat($('#quickHargaJual').val()) || 0;
                
                if (hargaJual > 0 && hargaBeli > 0 && hargaJual < hargaBeli) {
                    $('#quickHargaInfo').html('<span class="text-danger">Harga jual tidak boleh kurang dari harga beli</span>');
                } else {
                    $('#quickHargaInfo').html('');
                }
            });

            $('#quickKode').on('blur', function() {
                const kode = $(this).val().trim();
                if (kode) {
                    $.ajax({
                        url: "{{ route('barangbengkel.cekcode') }}",
                        data: { code: kode },
                        success: function(count) {
                            if (count > 0) {
                                $('#quickKodeInfo').html('<span class="text-danger">Kode sudah digunakan</span>');
                            } else {
                                $('#quickKodeInfo').html('<span class="text-success">Kode tersedia</span>');
                            }
                        }
                    });
                }
            });
        });

        // Quick Add function
        function saveQuickAdd() {
            const kode = $('#quickKode').val().trim();
            const nama = $('#quickNama').val().trim();
            const idkategori = $('#quickKategori').val();
            const idsatuan = $('#quickSatuan').val();
            const hargaBeli = parseFloat($('#quickHargaBeli').val()) || 0;
            const hargaJual = parseFloat($('#quickHargaJual').val()) || 0;

            if (!kode || !nama || !idkategori || !idsatuan) {
                Swal.fire('Perhatian', 'Semua field yang bertanda * harus diisi!', 'warning');
                return;
            }

            if (hargaJual > 0 && hargaBeli > 0 && hargaJual < hargaBeli) {
                Swal.fire('Perhatian', 'Harga jual tidak boleh kurang dari harga beli!', 'warning');
                return;
            }

            $.ajax({
                url: "{{ route('barangbengkel.quickadd') }}",
                method: "POST",
                data: {
                    kode_barang: kode,
                    nama_barang: nama,
                    idkategori: idkategori,
                    idsatuan: idsatuan,
                    harga_beli: hargaBeli,
                    harga_jual: hargaJual,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    $('#modalQuickAdd .btn-success').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalQuickAdd').modal('hide');
                            $('#tbbarang').DataTable().ajax.reload();
                            
                            // Reset form quick add
                            $('#quickKode').val('');
                            $('#quickNama').val('');
                            $('#quickKategori').val('');
                            $('#quickSatuan').val('');
                            $('#quickHargaBeli').val(0);
                            $('#quickHargaJual').val(0);
                            $('#quickKodeInfo').html('');
                            $('#quickHargaInfo').html('');
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', errorMsg, 'error');
                },
                complete: function() {
                    $('#modalQuickAdd .btn-success').prop('disabled', false).html('<i class="bi bi-plus"></i> Tambah');
                }
            });
        }
    </script>
    </x-slot>
</x-app-layout>