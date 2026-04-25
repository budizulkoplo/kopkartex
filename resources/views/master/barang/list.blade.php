<x-app-layout>
    <x-slot name="pagetitle">Barang</x-slot>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Master Data Barang</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline mb-4">
                        <div class="card-header pt-1 pb-1">
                            <div class="card-title">
                                <div class="row row-cols-auto">
                                    <div class="col">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Kategori</span>
                                            <select class="form-select form-select-sm" id="fkategori">
                                                <option value="all">SEMUA</option>
                                                @foreach ($kategori as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Kelompok</span>
                                            <select class="form-select form-select-sm" id="fkelompok">
                                                <option value="all">SEMUA</option>
                                                <option value="toko">Toko</option>
                                                <option value="bengkel">Bengkel</option>
                                                <option value="air">Air</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Status</span>
                                            <select class="form-select form-select-sm" id="fstatusproduk">
                                                <option value="aktif">Aktif</option>
                                                <option value="all">Semua</option>
                                                <option value="nonaktif">Nonaktif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalBarang" id="btnadd">
                                    <i class="bi bi-file-earmark-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tbbarang" class="table table-sm table-striped" style="width:100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Type</th>
                                        <th>Kategori</th>
                                        <th>Satuan</th>
                                        <th>Harga Beli</th>
                                        <th>Harga Jual</th>
                                        <th>Harga Umum</th>
                                        <th>Kelompok</th>
                                        <th>Status</th>
                                        <th>Foto</th>
                                        <th>Aksi</th>
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
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Form Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idbarang" id="idbarang">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="kode_barang" id="kode_barang" required>
                                    <button type="button" class="btn btn-outline-secondary" id="btnGenerateCode">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Klik tombol untuk generate kode otomatis</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="nama_barang" required maxlength="100">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Type</label>
                                <input type="text" class="form-control form-control-sm" name="type" maxlength="50">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Kelompok Unit</label>
                                <select class="form-select form-select-sm" name="kelompok_unit">
                                    <option value="toko">Toko</option>
                                    <option value="bengkel">Bengkel</option>
                                    <option value="air">Air</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Status Produk</label>
                                <select class="form-select form-select-sm" name="status_produk">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategori as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="satuan" required>
                                    <option value="">Pilih Satuan</option>
                                    @foreach ($satuan as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Harga Beli</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_beli" min="0" step="100">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Harga Jual</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_jual" min="0" step="100">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Harga Jual Umum</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_jual_umum" min="0" step="100">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Foto Produk</label>
                                <input type="file" class="form-control form-control-sm" name="img" accept="image/*" id="imgInput">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="hapus_gambar" value="1" id="hapusGambar">
                                    <label class="form-check-label" for="hapusGambar">Hapus gambar saat update</label>
                                </div>
                                <div class="mt-2 text-center">
                                    <img id="previewImg" src="" style="max-height:150px; display:none;" class="img-thumbnail">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="savebarang">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            $(document).ready(function() {
                var table = $('#tbbarang').DataTable({
                    ordering: false,
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    pageLength: 100,
                    ajax: {
                        url: "{{ route('barang.getdata') }}",
                        data: function(d) {
                            d.kategori = $('#fkategori').val();
                            d.kelompok_unit = $('#fkelompok').val();
                            d.status_produk = $('#fstatusproduk').val();
                        },
                        type: "GET"
                    },
                    columns: [
                        { data: "id", visible: false },
                        { data: "kode_barang" },
                        { data: "nama_barang" },
                        { data: "type" },
                        { data: "kategori_nama" },
                        { data: "satuan_nama" },
                        { data: "harga_beli", },
                        { data: "harga_jual", },
                        { data: "harga_jual_umum", },
                        { 
                            data: "kelompok_unit",
                            render: function(data) {
                                const labels = {
                                    'toko': '<span class="badge bg-primary">Toko</span>',
                                    'bengkel': '<span class="badge bg-warning">Bengkel</span>',
                                    'air': '<span class="badge bg-info">Air</span>'
                                };
                                return labels[data] || data;
                            }
                        },
                        {
                            data: "status_produk",
                            render: function(data, type, row) {
                                const isAktif = (data || 'aktif') === 'aktif';
                                const nextStatus = isAktif ? 'nonaktif' : 'aktif';
                                const badge = isAktif
                                    ? '<span class="badge bg-success">Aktif</span>'
                                    : '<span class="badge bg-secondary">Nonaktif</span>';

                                return `
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        ${badge}
                                        <button class="btn btn-xs btn-outline-${isAktif ? 'secondary' : 'success'} toggle-status-btn"
                                            data-id="${row.id}" data-status="${nextStatus}">
                                            ${isAktif ? 'Nonaktifkan' : 'Aktifkan'}
                                        </button>
                                    </div>`;
                            }
                        },
                        { 
                            data: "img", 
                            orderable: false, 
                            searchable: false,
                            render: function(data, type, row) {
                                if (data) {
                                    return '<img src="/storage/produk/' + data + '" class="img-thumbnail" style="max-height:60px;">';
                                }
                                return '<span class="text-muted">-</span>';
                            }
                        },
                        {
                            data: "id",
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                return `
                                    <button class="btn btn-sm btn-warning editbtn" data-id="${data}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger deletebtn" data-id="${data}" title="Hapus">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>`;
                            }
                        }
                    ],
                    columnDefs: [
                        { targets: 0, className: "text-center" },  // ID
                        { targets: 1, className: "text-left" },    // Kode
                        { targets: 2, className: "text-left" },    // Nama
                        { targets: 3, className: "text-left" },    // Type
                        { targets: 4, className: "text-center" },  // Kategori
                        { targets: 5, className: "text-center" },  // Satuan
                        { targets: 6, className: "text-right" },   // Harga Beli
                        { targets: 7, className: "text-right" },   // Harga Jual
                        { targets: 8, className: "text-center" },  // Kelompok
                        { targets: 9, className: "text-center" },  // Status
                        { targets: 10, className: "text-center" }, // Foto
                        { targets: 11, className: "text-center" }  // Aksi
                    ]
                });

                // Generate kode otomatis
                $('#btnGenerateCode').on('click', function() {
                    $.get("{{ route('barang.getcode') }}", function(code) {
                        $('#kode_barang').val(code);
                    });
                });

                // Cek kode unik saat input
                $('#kode_barang').on('blur', function() {
                    if ($(this).val()) {
                        $.get("{{ route('barang.cekcode') }}", { code: $(this).val() }, function(count) {
                            if (count > 0 && !$('#idbarang').val()) {
                                Swal.fire('Peringatan', 'Kode barang sudah digunakan!', 'warning');
                            }
                        });
                    }
                });

                // Submit form
                $('#frmbarang').on('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    $.ajax({
                        url: "{{ route('barang.store') }}",
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            table.ajax.reload();
                            $('#modalBarang').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            let message = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                message = xhr.responseText;
                            }
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                });

                // Preview gambar
                $('#imgInput').on('change', function(evt) {
                    const [file] = this.files;
                    if (file) {
                        $('#previewImg').show().attr('src', URL.createObjectURL(file));
                    }
                });

                // Edit button - PERBAIKAN INI
                $(document).on('click', '.editbtn', function() {
                    const id = $(this).data('id');

                    // Function untuk konversi format Indonesia ke angka
                    function parseIndonesianCurrency(currencyString) {
                        if (!currencyString) return 0;
                        
                        // Hapus "Rp " dan spasi
                        let cleaned = currencyString.toString()
                            .replace('Rp', '')
                            .trim();
                            
                        // Jika ada koma desimal (misal: "1.500,75")
                        if (cleaned.includes(',')) {
                            // Ganti titik pemisah ribuan dengan kosong
                            // Ganti koma desimal dengan titik
                            cleaned = cleaned.replace(/\./g, '')  // Hapus semua titik
                                        .replace(',', '.');    // Ganti koma dengan titik
                        } else {
                            // Jika tidak ada koma, langsung hapus titik
                            cleaned = cleaned.replace(/\./g, '');
                        }
                        
                        // Parse ke float
                        const result = parseFloat(cleaned);
                        return isNaN(result) ? 0 : result;
                    }
                    
                    $.get("{{ route('barang.detail') }}", { id: id }, function(response) {
                        if (!response.success) {
                            Swal.fire('Error!', 'Data tidak ditemukan', 'error');
                            return;
                        }

                        const data = response.data;

                        $('#idbarang').val(data.id);
                        $('#kode_barang').val(data.kode_barang).prop('readonly', false);
                        $('input[name="nama_barang"]').val(data.nama_barang);
                        $('input[name="type"]').val(data.type || '');
                        $('select[name="kelompok_unit"]').val(data.kelompok_unit || 'toko');
                        $('select[name="status_produk"]').val(data.status_produk || 'aktif');
                        $('select[name="kategori"]').val(data.kategori);
                        $('select[name="satuan"]').val(data.satuan);
                        $('input[name="harga_beli"]').val(parseIndonesianCurrency(data.harga_beli));
                        $('input[name="harga_jual"]').val(parseIndonesianCurrency(data.harga_jual));
                        $('input[name="harga_jual_umum"]').val(parseIndonesianCurrency(data.harga_jual_umum));

                        if (data.img) {
                            $('#previewImg').show().attr('src', '/storage/produk/' + data.img);
                        } else {
                            $('#previewImg').hide().attr('src', '');
                        }

                        $('#hapusGambar').prop('checked', false);
                        $('#modalTitle').text('Edit Barang');
                        $('#modalBarang').modal('show');
                    });
                });

                $(document).on('click', '.toggle-status-btn', function() {
                    const id = $(this).data('id');
                    const status = $(this).data('status');
                    const label = status === 'nonaktif' ? 'menonaktifkan' : 'mengaktifkan';

                    Swal.fire({
                        title: 'Ubah Status Produk?',
                        text: `Produk akan ${label}.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.post("{{ route('barang.status') }}", {
                            id: id,
                            status_produk: status,
                            _token: "{{ csrf_token() }}"
                        }).done(function(response) {
                            table.ajax.reload(null, false);
                            Swal.fire('Berhasil', response.message, 'success');
                        }).fail(function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal mengubah status produk', 'error');
                        });
                    });
                });

                // Delete button
                $(document).on('click', '.deletebtn', function() {
                    const id = $(this).data('id');
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
                                url: "{{ route('barang.hapus') }}",
                                method: "DELETE",
                                data: { 
                                    id: id,
                                    _token: "{{ csrf_token() }}" 
                                },
                                success: function(response) {
                                    table.ajax.reload();
                                    Swal.fire("Terhapus!", response.message, "success");
                                },
                                error: function(xhr) {
                                    Swal.fire("Error!", "Gagal menghapus data", "error");
                                }
                            });
                        }
                    });
                });

                // Reset form saat tambah baru
                $('#btnadd').on('click', function() {
                    $('#frmbarang')[0].reset();
                    $('#idbarang').val('');
                    $('#kode_barang').prop('readonly', false);
                    $('#previewImg').hide().attr('src', '');
                    $('#hapusGambar').prop('checked', false);
                    $('#modalTitle').text('Tambah Barang Baru');
                    $('#btnGenerateCode').trigger('click');
                    
                    // Reset select
                    $('select[name="kategori"]').val('');
                    $('select[name="satuan"]').val('');
                    $('select[name="kelompok_unit"]').val('toko');
                    $('select[name="status_produk"]').val('aktif');
                });

                // Filter change
                $('#fkategori, #fkelompok, #fstatusproduk').on('change', function() {
                    table.ajax.reload();
                });
            });
        </script>
    </x-slot>
</x-app-layout>
