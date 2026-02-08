<x-app-layout>
    <x-slot name="pagetitle">Barang Bengkel</x-slot>
    
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Master Data Barang Bengkel</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Barang Bengkel</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-wrench-adjustable-circle text-warning"></i> Data Barang Bengkel
                    </h5>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalBarang" id="btnadd">
                            <i class="bi bi-plus-circle"></i> Tambah Barang
                        </button>
                        <button class="btn btn-sm btn-success ms-1" data-bs-toggle="modal" data-bs-target="#modalQuickAdd">
                            <i class="bi bi-lightning"></i> Quick Add
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filter --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-warning text-white">
                                    <i class="bi bi-filter"></i>
                                </span>
                                <select class="form-select" id="fkategori">
                                    <option value="all">SEMUA KATEGORI</option>
                                    @foreach ($kategori as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari kode/nama barang...">
                                <button class="btn btn-warning" type="button" onclick="table.search($('#searchInput').val()).draw()">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel --}}
                    <div class="table-responsive">
                        <table id="tbbarang" class="table table-sm table-striped table-hover" style="width:100%; font-size: small;">
                            <thead class="table-warning">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Kode Barang</th>
                                    <th width="20%">Nama Barang</th>
                                    <th width="10%">Kategori</th>
                                    <th width="10%">Satuan</th>
                                    <th width="10%">Stok</th>
                                    <th width="15%">Harga Beli</th>
                                    <th width="15%">Harga Jual</th>
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

    {{-- Modal Form Utama --}}
    <div class="modal fade" id="modalBarang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="frmbarang" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-wrench"></i> Form Barang Bengkel
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idbarang" id="idbarang">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="kode_barang" required 
                                           placeholder="Kode unik barang" id="kodeInput">
                                    <button type="button" class="btn btn-outline-warning" onclick="generateKode()">
                                        <i class="bi bi-magic"></i> Generate
                                    </button>
                                </div>
                                <div class="form-text text-muted" id="kodeInfo"></div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="nama_barang" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <select class="form-select form-select-sm" name="idkategori" required id="idkategori">
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($kategori as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-success" onclick="showModalKategori()">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <select class="form-select form-select-sm" name="idsatuan" required id="idsatuan">
                                        <option value="">Pilih Satuan</option>
                                        @foreach ($satuan as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-success" onclick="showModalSatuan()">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Stok Unit 5</label>
                                <input type="number" class="form-control form-control-sm" name="stok" id="stok" 
                                       min="0" step="1" value="0" readonly>
                                <div class="form-text text-muted">Stok di unit bengkel (ID: 5)</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Harga Beli</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_beli" min="0" step="1000" id="hargaBeli">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Harga Jual</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_jual" min="0" step="1000" id="hargaJual">
                                </div>
                                <div class="form-text text-muted" id="hargaInfo"></div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Foto Barang (Opsional)</label>
                                <input type="file" class="form-control form-control-sm" name="img" accept="image/*" id="fileInput">
                                <div class="mt-2 text-center">
                                    <img id="previewImg" src="" style="max-height: 150px; max-width: 200px;" 
                                         class="img-thumbnail d-none border-warning">
                                    <input type="hidden" name="hapus_gambar" id="hapus_gambar" value="0">
                                </div>
                                <div class="form-text">Format: JPG, PNG, JPEG (Max: 2MB)</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning" id="savebarang">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Kategori --}}
    <div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-tags"></i> Tambah Kategori
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="namaKategori" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="simpanKategori()">
                        <i class="bi bi-check"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Satuan --}}
    <div class="modal fade" id="modalSatuan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-box"></i> Tambah Satuan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="namaSatuan" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="simpanSatuan()">
                        <i class="bi bi-check"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Quick Add --}}
    <div class="modal fade" id="modalQuickAdd" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-lightning"></i> Quick Add
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="quickKode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="quickNama" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Harga Beli</label>
                            <input type="number" class="form-control form-control-sm" id="quickHargaBeli" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Jual</label>
                            <input type="number" class="form-control form-control-sm" id="quickHargaJual" value="0">
                        </div>
                    </div>
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
            .badge-edit {
                cursor: pointer;
                padding: 0.25rem 0.5rem;
            }
            .badge-edit:hover {
                opacity: 0.8;
            }
            .img-thumbnail {
                border: 2px solid #ffc107;
            }
            .stok-positive {
                color: #198754;
                font-weight: bold;
            }
            .stok-zero {
                color: #dc3545;
                font-weight: bold;
            }
            .select2-container--default .select2-selection--single {
                height: 31px;
                border: 1px solid #ced4da;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 29px;
                font-size: 0.875rem;
            }
        </style>
    </x-slot>

    <x-slot name="jscustom">
    <script>
        let table;
        
        $(document).ready(function () {
            // Inisialisasi DataTable
            table = $('#tbbarang').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('barangbengkel.getdata') }}",
                    type: "GET",
                    data: function(d) {
                        d.kategori = $('#fkategori').val();
                        if ($('#searchInput').val()) {
                            d.search = {
                                value: $('#searchInput').val(),
                                regex: false
                            };
                        }
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables error:', error, thrown);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Loading Data',
                            text: 'Gagal memuat data. Silakan refresh halaman.'
                        });
                    }
                },
                columns: [
                    { 
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    { 
                        data: 'kode_barang',
                        name: 'kode_barang',
                        width: '15%'
                    },
                    { 
                        data: 'nama_barang',
                        name: 'nama_barang',
                        width: '20%'
                    },
                    { 
                        data: 'kategori_nama',
                        name: 'kategori_nama',
                        width: '10%'
                    },
                    { 
                        data: 'satuan_nama',
                        name: 'satuan_nama',
                        width: '10%'
                    },
                    { 
                        data: 'stok',
                        name: 'stok',
                        orderable: true,
                        searchable: false,
                        width: '10%',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                const stokNum = parseInt(data.replace(/\./g, '')) || 0;
                                const stokClass = stokNum > 0 ? 'stok-positive' : 'stok-zero';
                                return `<span class="${stokClass}">${data}</span>`;
                            }
                            return data;
                        }
                    },
                    { 
                        data: 'harga_beli_format',
                        name: 'harga_beli',
                        orderable: true,
                        searchable: false,
                        width: '15%'
                    },
                    { 
                        data: 'harga_jual_format',
                        name: 'harga_jual',
                        orderable: true,
                        searchable: false,
                        width: '15%'
                    },
                    { 
                        data: 'foto',
                        name: 'img',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false,
                        width: '10%'
                    }
                ],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    emptyTable: "Tidak ada data barang bengkel",
                    loadingRecords: "Memuat data...",
                    processing: "Memproses...",
                    zeroRecords: "Tidak ada data yang cocok",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    var startIndex = api.page.info().start;
                    api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                        cell.innerHTML = startIndex + i + 1;
                    });
                }
            });

            // Filter kategori
            $('#fkategori').on('change', function() {
                table.ajax.reload();
            });

            // Search dengan debounce
            let searchTimeout;
            $('#searchInput').on('keyup', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    table.search($('#searchInput').val()).draw();
                }, 500);
            });

            // Generate kode
            window.generateKode = function() {
                $.get("{{ route('barangbengkel.getcode') }}", function(response) {
                    $('#kodeInput').val(response);
                    checkKodeAvailability(response);
                }).fail(function() {
                    Swal.fire('Error', 'Gagal generate kode', 'error');
                });
            };

            // Check kode availability
            $('#kodeInput').on('blur', function() {
                const kode = $(this).val().trim();
                if (kode) {
                    checkKodeAvailability(kode);
                }
            });

            function checkKodeAvailability(kode) {
                $.ajax({
                    url: "{{ route('barangbengkel.cekcode') }}",
                    data: { code: kode },
                    success: function(count) {
                        if (count > 0) {
                            $('#kodeInfo').html('<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Kode sudah digunakan</span>');
                        } else {
                            $('#kodeInfo').html('<span class="text-success"><i class="bi bi-check-circle"></i> Kode tersedia</span>');
                        }
                    },
                    error: function() {
                        $('#kodeInfo').html('<span class="text-warning"><i class="bi bi-question-circle"></i> Gagal validasi kode</span>');
                    }
                });
            }

            // Check harga validation
            $('#hargaBeli, #hargaJual').on('input', function() {
                validateHarga();
            });

            function validateHarga() {
                const hargaBeli = parseFloat($('#hargaBeli').val()) || 0;
                const hargaJual = parseFloat($('#hargaJual').val()) || 0;
                
                if (hargaJual > 0 && hargaBeli > 0 && hargaJual < hargaBeli) {
                    $('#hargaInfo').html('<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Harga jual kurang dari harga beli</span>');
                } else {
                    $('#hargaInfo').html('');
                }
            }

            // Preview image
            $('#fileInput').on('change', function(evt) {
                const [file] = this.files;
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewImg').removeClass('d-none').attr('src', e.target.result);
                        $('#hapus_gambar').val('0'); // Reset hapus gambar jika upload baru
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Submit form
            $('#frmbarang').on('submit', function(e) {
                e.preventDefault();
                
                if (!this.checkValidity()) {
                    e.stopPropagation();
                    this.classList.add('was-validated');
                    return;
                }

                const hargaBeli = parseFloat($('#hargaBeli').val()) || 0;
                const hargaJual = parseFloat($('#hargaJual').val()) || 0;
                
                if (hargaJual > 0 && hargaBeli > 0 && hargaJual < hargaBeli) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Harga jual tidak boleh kurang dari harga beli!'
                    });
                    return;
                }

                const formData = new FormData(this);
                
                // Ambil stok jika barang baru
                const idbarang = $('#idbarang').val();
                if (!idbarang) {
                    formData.append('stok', $('#stok').val() || 0);
                }
                
                $.ajax({
                    url: "{{ route('barangbengkel.store') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#savebarang').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
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
                                $('#modalBarang').modal('hide');
                                table.ajax.reload();
                                resetForm();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan saat menyimpan!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            errorMsg = xhr.responseText;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    },
                    complete: function() {
                        $('#savebarang').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan');
                    }
                });
            });

            // Edit button
            $(document).on('click', '.btn-edit', function() {
                const encryptedId = $(this).data('id');
                
                resetForm();
                $('#modalBarang').modal('show');
                
                // Show loading
                $('#modalBarang .modal-body').html('<div class="text-center py-5"><div class="spinner-border text-warning"></div><p class="mt-2">Memuat data...</p></div>');
                
                $.ajax({
                    url: "{{ route('barangbengkel.getsingledata') }}",
                    method: "GET",
                    data: { id: encryptedId },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            // Kembalikan konten modal ke form
                            $('#modalBarang .modal-body').html(`
                                <input type="hidden" name="idbarang" id="idbarang" value="${data.id}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" name="kode_barang" required 
                                                   placeholder="Kode unik barang" id="kodeInput" value="${data.kode_barang}" readonly>
                                            <button type="button" class="btn btn-outline-warning" onclick="generateKode()" disabled>
                                                <i class="bi bi-magic"></i> Generate
                                            </button>
                                        </div>
                                        <div class="form-text text-muted">Kode tidak dapat diubah saat edit</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="nama_barang" required value="${data.nama_barang}">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <select class="form-select form-select-sm" name="idkategori" required id="idkategori">
                                                <option value="">Pilih Kategori</option>
                                                @foreach ($kategori as $item)
                                                <option value="{{ $item->id }}">${data.idkategori == {{ $item->id }} ? 'selected' : ''}>{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-success" onclick="showModalKategori()">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <select class="form-select form-select-sm" name="idsatuan" required id="idsatuan">
                                                <option value="">Pilih Satuan</option>
                                                @foreach ($satuan as $item)
                                                <option value="{{ $item->id }}">${data.idsatuan == {{ $item->id }} ? 'selected' : ''}>{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-success" onclick="showModalSatuan()">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Stok Unit 5</label>
                                        <input type="number" class="form-control form-control-sm" name="stok_display" id="stok" 
                                               min="0" step="1" value="${data.stok}" readonly>
                                        <div class="form-text text-muted">Stok di unit bengkel (ID: 5)</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Harga Beli</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="harga_beli" min="0" step="1000" id="hargaBeli" value="${data.harga_beli}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Harga Jual</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="harga_jual" min="0" step="1000" id="hargaJual" value="${data.harga_jual}">
                                        </div>
                                        <div class="form-text text-muted" id="hargaInfo"></div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label">Foto Barang (Opsional)</label>
                                        <input type="file" class="form-control form-control-sm" name="img" accept="image/*" id="fileInput">
                                        <div class="mt-2 text-center">
                                            ${data.img ? 
                                                `<img id="previewImg" src="{{ asset('storage/produk/bengkel') }}/${data.img}" style="max-height: 150px; max-width: 200px;" class="img-thumbnail border-warning">
                                                 <div class="mt-1">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage()">
                                                        <i class="bi bi-trash"></i> Hapus Foto
                                                    </button>
                                                 </div>` 
                                                : 
                                                `<img id="previewImg" src="" style="max-height: 150px; max-width: 200px;" class="img-thumbnail d-none border-warning">`
                                            }
                                        </div>
                                        <input type="hidden" name="hapus_gambar" id="hapus_gambar" value="0">
                                    </div>
                                </div>
                            `);
                            
                            // Re-attach event handlers
                            attachFormEvents();
                            
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                            $('#modalBarang').modal('hide');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data barang'
                        });
                        $('#modalBarang').modal('hide');
                    }
                });
            });

            // Function untuk re-attach event handlers
            function attachFormEvents() {
                // Check harga validation
                $('#hargaBeli, #hargaJual').on('input', function() {
                    validateHarga();
                });
                
                // Preview image
                $('#fileInput').on('change', function(evt) {
                    const [file] = this.files;
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#previewImg').removeClass('d-none').attr('src', e.target.result);
                            $('#hapus_gambar').val('0');
                        };
                        reader.readAsDataURL(file);
                    }
                });
                
                // Harga validation initial
                validateHarga();
            }

            // Function untuk validasi harga
            function validateHarga() {
                const hargaBeli = parseFloat($('#hargaBeli').val()) || 0;
                const hargaJual = parseFloat($('#hargaJual').val()) || 0;
                
                if (hargaJual > 0 && hargaBeli > 0 && hargaJual < hargaBeli) {
                    $('#hargaInfo').html('<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Harga jual kurang dari harga beli</span>');
                } else {
                    $('#hargaInfo').html('');
                }
            }

            // Function untuk hapus foto
            function removeImage() {
                Swal.fire({
                    title: 'Hapus Foto?',
                    text: "Foto akan dihapus dari sistem",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#hapus_gambar').val('1');
                        $('#previewImg').addClass('d-none').attr('src', '');
                        Swal.fire({
                            icon: 'success',
                            title: 'Foto akan dihapus saat disimpan',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            }

            // Delete button
            $(document).on('click', '.btn-delete', function() {
                const encryptedId = $(this).data('id');
                
                Swal.fire({
                    title: 'Hapus Barang Bengkel?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
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
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Terhapus!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        table.ajax.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = 'Terjadi kesalahan saat menghapus!';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg
                                });
                            }
                        });
                    }
                });
            });

            // Reset modal saat ditutup
            $('#modalBarang').on('hidden.bs.modal', function () {
                resetForm();
            });

            // Reset form saat tambah baru
            $('#btnadd').on('click', function() {
                resetForm();
                $('#kodeInput').prop('readonly', false).val('');
                generateKode();
            });

            // Show modal kategori
            window.showModalKategori = function() {
                $('#namaKategori').val('');
                $('#modalKategori').modal('show');
            }

            // Simpan kategori
            window.simpanKategori = function() {
                const nama = $('#namaKategori').val().trim();
                if (!nama) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Nama kategori harus diisi!'
                    });
                    return;
                }

                
            }

            // Show modal satuan
            window.showModalSatuan = function() {
                $('#namaSatuan').val('');
                $('#modalSatuan').modal('show');
            }

            // Simpan satuan
            window.simpanSatuan = function() {
                const nama = $('#namaSatuan').val().trim();
                if (!nama) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Nama satuan harus diisi!'
                    });
                    return;
                }

                
            }
        });

        function resetForm() {
            if ($('#frmbarang').length > 0) {
                $('#frmbarang')[0].reset();
            }
            $('#idbarang').val('');
            $('#kodeInfo').html('');
            $('#hargaInfo').html('');
            $('#previewImg').addClass('d-none').attr('src', '');
            $('#hapus_gambar').val('0');
            if ($('#frmbarang').hasClass('was-validated')) {
                $('#frmbarang').removeClass('was-validated');
            }
        }

        // Quick Add functions
        function saveQuickAdd() {
            const kode = $('#quickKode').val().trim();
            const nama = $('#quickNama').val().trim();
            const hargaBeli = parseFloat($('#quickHargaBeli').val()) || 0;
            const hargaJual = parseFloat($('#quickHargaJual').val()) || 0;

            if (!kode || !nama) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Kode dan Nama harus diisi!'
                });
                return;
            }

            if (hargaJual > 0 && hargaBeli > 0 && hargaJual < hargaBeli) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Harga jual tidak boleh kurang dari harga beli!'
                });
                return;
            }

            $.ajax({
                url: "{{ route('barangbengkel.quickadd') }}",
                method: "POST",
                data: {
                    kode_barang: kode,
                    nama_barang: nama,
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
                            table.ajax.reload();
                            
                            // Reset form quick add
                            $('#quickKode').val('');
                            $('#quickNama').val('');
                            $('#quickHargaBeli').val(0);
                            $('#quickHargaJual').val(0);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                },
                complete: function() {
                    $('#modalQuickAdd .btn-success').prop('disabled', false).html('<i class="bi bi-plus"></i> Tambah');
                }
            });
        }
    </script>
</x-slot>
</x-app-layout>