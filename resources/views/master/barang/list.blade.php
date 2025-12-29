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
                                        <th>Kelompok</th>
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
                    ajax: {
                        url: "{{ route('barang.getdata') }}",
                        data: function(d) {
                            d.kategori = $('#fkategori').val();
                            d.kelompok_unit = $('#fkelompok').val();
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
                        { targets: 9, className: "text-center" },  // Foto
                        { targets: 10, className: "text-center" }  // Aksi
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
                    const row = $(this).closest('tr');
                    
                    // Ambil data dari row yang diklik
                    const kodeBarang = table.cell(row, 1).data();
                    const namaBarang = table.cell(row, 2).data();
                    const type = table.cell(row, 3).data();
                    const kategori = table.cell(row, 4).data();
                    const satuan = table.cell(row, 5).data();
                    
                    // Ambil harga beli dan harga jual (hilangkan format currency)
                    const hargaBeliCell = table.cell(row, 6).data();
                    const hargaJualCell = table.cell(row, 7).data();
                    
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
                    
                    // Parse harga
                    const hargaBeli = parseIndonesianCurrency(hargaBeliCell);
                    const hargaJual = parseIndonesianCurrency(hargaJualCell);
                    
                    console.log('hargaBeliCell:', hargaBeliCell, 'parsed:', hargaBeli);
                    console.log('hargaJualCell:', hargaJualCell, 'parsed:', hargaJual);
                    
                    // Ambil kelompok unit dari badge
                    const kelompokUnitBadge = table.cell(row, 8).data();
                    let kelompokUnit = 'toko'; // default
                    if (kelompokUnitBadge.includes('Toko')) kelompokUnit = 'toko';
                    else if (kelompokUnitBadge.includes('Bengkel')) kelompokUnit = 'bengkel';
                    else if (kelompokUnitBadge.includes('Air')) kelompokUnit = 'air';
                    
                    // Ambil gambar (jika ada)
                    const imgCell = table.cell(row, 9).data();
                    let imgSrc = '';
                    if (imgCell.includes('src="')) {
                        const match = imgCell.match(/src="([^"]+)"/);
                        if (match) imgSrc = match[1];
                    }
                    
                    // Isi form
                    $('#idbarang').val(id);
                    $('#kode_barang').val(kodeBarang).prop('readonly', false);
                    $('input[name="nama_barang"]').val(namaBarang);
                    $('input[name="type"]').val(type || '');
                    $('select[name="kelompok_unit"]').val(kelompokUnit);
                    $('select[name="kategori"]').val(kategori);
                    $('select[name="satuan"]').val(satuan);
                    $('input[name="harga_beli"]').val(hargaBeli);
                    $('input[name="harga_jual"]').val(hargaJual);
                    
                    // Tampilkan gambar preview jika ada
                    if (imgSrc) {
                        $('#previewImg').show().attr('src', imgSrc);
                    } else {
                        $('#previewImg').hide().attr('src', '');
                    }
                    
                    $('#hapusGambar').prop('checked', false);
                    $('#modalTitle').text('Edit Barang');
                    $('#modalBarang').modal('show');
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
                });

                // Filter change
                $('#fkategori, #fkelompok').on('change', function() {
                    table.ajax.reload();
                });
            });
        </script>
    </x-slot>
</x-app-layout>