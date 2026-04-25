<x-app-layout>
    <x-slot name="pagetitle">Stock Opname</x-slot>

    <div class="app-content-header mb-3">
        <div class="container-fluid">
            <h3 class="mb-0">Daftar Barang - Stock Opname</h3>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Cek apakah periode sudah selesai --}}
    @php
        $periodeSelesai = false;
        if ($hasData) {
            $unitId = Auth::user()->unit_kerja;
            $startDate = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
            $endDate = \Carbon\Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
            $periodeSelesai = App\Models\StockOpnameHDR::where('id_unit', $unitId)
                ->whereBetween('tgl_opname', [$startDate, $endDate])
                ->where('status', 'selesai')
                ->exists();
        }
    @endphp

    {{-- Alert jika periode sudah selesai --}}
    @if($periodeSelesai)
        <div class="alert alert-warning m-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Perhatian!</strong> Periode {{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }} sudah selesai diopname. 
            Data tidak dapat diubah lagi. Silakan pilih periode lain jika ingin melakukan input.
        </div>
    @endif

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <span class="fw-bold">Unit: {{ auth()->user()->unit->nama_unit ?? '-' }}</span>
                </div>

                <div class="card-body">
                    {{-- Filter Bulan & Tombol Aksi --}}
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <form method="GET" action="{{ route('stockopname.index') }}" class="d-flex">
                            <div class="input-group input-group-sm">
                                <input type="month" id="bulan" name="bulan" value="{{ $bulan }}" class="form-control">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                            </div>
                        </form>

                        {{-- Sembunyikan tombol Start dan Selesai jika periode sudah selesai --}}
                        @if(!$periodeSelesai)
                            <form id="formMulaiOpname" method="POST" action="{{ route('stockopname.mulai') }}" class="d-flex">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="date" name="tgl_opname" value="{{ now()->format('Y-m-d') }}" class="form-control">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-play-circle"></i> Start Opname
                                    </button>
                                </div>
                            </form>

                            @if($hasData)
                                <a href="{{ route('mobile.stokopname.index') }}" class="btn btn-primary btn-sm d-flex align-items-center">
                                    <i class="bi bi-upc-scan me-1"></i> Scan Opname (Mobile)
                                </a>
                                <button type="button" class="btn btn-success" onclick="selesaiOpname('{{ $bulan }}')">
                                    <i class="bi bi-check-circle"></i> Selesai Opname
                                </button>
                            @endif
                        @endif

                        
                    </div>

                    {{-- Sembunyikan form scan jika periode sudah selesai --}}
                    @if(!$periodeSelesai)
                        <div class="card p-2 mb-3 border border-info" style="background-color:#e9f7ff;">
                            <form id="formScan" class="d-flex gap-2">
                                <input type="text" id="kodeScan" name="kode" 
                                    class="form-control form-control-sm"
                                    placeholder="🔍 Scan / Input Kode Barang"
                                    style="font-size:0.9rem;">
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="bi bi-upc-scan me-1"></i> Scan
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-secondary p-2 mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Scan dinonaktifkan karena periode ini sudah selesai.
                        </div>
                    @endif

                    {{-- Tabel Barang --}}
                    <table class="table table-sm table-bordered table-striped text-center" id="tbbarang" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Stok Sistem</th>
                                <th>Stok Fisik</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    {{-- Script Custom --}}
    <x-slot name="jscustom">
        <script>
            // Flag untuk periode selesai
            const periodeSelesai = {{ $periodeSelesai ? 'true' : 'false' }};
            
            $(document).ready(function () {
                $('#tbbarang').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 50,
                    ajax: {
                        url: "{{ route('stockopname.barangajax') }}",
                        data: { 
                            bulan: "{{ $bulan }}" 
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'kode_barang', name: 'stock_opname.kode_barang' },
                        { data: 'nama_barang', name: 'barang.nama_barang' },
                        { data: 'stock_sistem', name: 'stock_opname.stock_sistem' },
                        { data: 'stock_fisik', name: 'stock_opname.stock_fisik' },
                        { 
                            data: 'status', 
                            name: 'stock_opname.status',
                            render: function(data) {
                                if (periodeSelesai) {
                                    return '<span class="badge bg-secondary">Selesai</span>';
                                }
                                if (data === 'draft') {
                                    return '<span class="badge bg-success">Draft</span>';
                                } else if (data === 'pending') {
                                    return '<span class="badge bg-warning text-dark">Pending</span>';
                                }
                                return '<span class="badge bg-secondary">-</span>';
                            }
                        },
                        { 
                            data: 'aksi', 
                            name: 'aksi', 
                            orderable: false, 
                            searchable: false,
                            render: function(data, type, row) {
                                // Jika periode selesai, kembalikan string kosong atau teks nonaktif
                                if (periodeSelesai) {
                                    return '<span class="text-muted">-</span>';
                                }
                                return data;
                            }
                        }
                    ],
                    pageLength: 25,
                    responsive: true,
                    createdRow: function(row, data, dataIndex) {
                        if (data.status === 'draft') {
                            $(row).addClass('table-warning');
                        }
                    }
                });

                // Nonaktifkan form submit jika periode selesai
                @if(!$periodeSelesai)
                    // Verifikasi password sebelum mulai opname
                    $('#formMulaiOpname').on('submit', function(e) {
                        e.preventDefault();
                        let form = this;

                        Swal.fire({
                            title: 'Mulai Stock Opname?',
                            text: "Jika bulan ini sudah ada data, data lama akan dihapus!",
                            icon: 'warning',
                            input: 'password',
                            inputLabel: 'Masukkan password Anda',
                            inputPlaceholder: 'Password',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Lanjutkan!',
                            cancelButtonText: 'Batal',
                            inputAttributes: {
                                autocapitalize: 'off',
                                autocorrect: 'off'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                let password = result.value;
                                if (!password) {
                                    Swal.fire('Error', 'Password wajib diisi', 'error');
                                    return;
                                }

                                $.post("{{ route('stockopname.verifyPassword') }}", { 
                                    _token: "{{ csrf_token() }}", 
                                    password: password 
                                })
                                .done(function(res) {
                                    if(res.valid){
                                        form.submit();
                                    } else {
                                        Swal.fire('Error', 'Password salah', 'error');
                                    }
                                })
                                .fail(function(){
                                    Swal.fire('Error', 'Terjadi kesalahan', 'error');
                                });
                            }
                        });
                    });

                    // Scan barang
                    $('#formScan').on('submit', function(e) {
                        e.preventDefault();
                        let kode = $('#kodeScan').val();
                        let bulan = $('#bulan').val() || "{{ $bulan }}";
                        if (!kode) return;

                        $.post("{{ route('stockopname.scan') }}", { 
                            kode: kode, 
                            bulan: bulan,
                            _token: "{{ csrf_token() }}" 
                        })
                        .done(function(res) {
                            if (res.status === 'found') {
                                window.location.href = res.form_url ?? ("{{ url('/stock/form') }}" + "?barang_id=" + res.data.id + "&bulan=" + encodeURIComponent(bulan));
                            } else if (res.status === 'old') {
                                Swal.fire({
                                    title: 'Barang tidak ada di master!',
                                    text: "Barang ditemukan di master lama. Apakah mau ditambahkan ke master baru?",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, Tambahkan',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $.post("{{ route('stockopname.insertOld') }}", { 
                                            kode: kode, 
                                            _token: "{{ csrf_token() }}" 
                                        })
                                        .done(function(res2) {
                                            window.location.href = "{{ url('/stock/form') }}" + "?barang_id=" + res2.data.id + "&bulan=" + encodeURIComponent(bulan);
                                        });
                                    }
                                });
                            }
                        })
                        .fail(function(err) {
                            Swal.fire('Error', err.responseJSON?.message ?? 'Barang tidak ditemukan', 'error');
                        });
                    });

                    // Fokus input scan
                    document.getElementById("kodeScan")?.focus();
                @endif
            });

            function selesaiOpname(bulan) {
                Swal.fire({
                    title: 'Selesaikan Stock Opname?',
                    html: `
                        <p>Proses ini akan:</p>
                        <ul class="text-start">
                            <li>Menutup periode opname bulan ${bulan}</li>
                            <li>Mengunci hasil opname yang sudah berjalan realtime di stok sistem</li>
                            <li>Memastikan snapshot modal awal periode tetap tersimpan</li>
                            <li><strong>Data tidak dapat diubah setelah ini!</strong></li>
                        </ul>
                        <p class="text-danger"><strong>Pastikan semua data sudah benar!</strong></p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Selesaikan!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Verifikasi password dulu
                        Swal.fire({
                            title: 'Verifikasi Password',
                            input: 'password',
                            inputLabel: 'Masukkan password Anda untuk konfirmasi',
                            inputPlaceholder: 'Password',
                            showCancelButton: true,
                            confirmButtonText: 'Verifikasi',
                            cancelButtonText: 'Batal',
                            inputValidator: (value) => {
                                if (!value) {
                                    return 'Password harus diisi!';
                                }
                            }
                        }).then((passwordResult) => {
                            if (passwordResult.isConfirmed) {
                                // Verifikasi password via AJAX
                                $.ajax({
                                    url: '{{ route("stockopname.verifyPassword") }}',
                                    method: 'POST',
                                    data: {
                                        password: passwordResult.value,
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        if (response.valid) {
                                            // Lanjutkan proses selesai opname
                                            $.ajax({
                                                url: '{{ route("stockopname.selesai") }}',
                                                method: 'POST',
                                                data: {
                                                    bulan: bulan,
                                                    _token: '{{ csrf_token() }}'
                                                },
                                                beforeSend: function() {
                                                    Swal.fire({
                                                        title: 'Memproses...',
                                                        html: 'Menyelesaikan opname dan menyimpan modal awal',
                                                        allowOutsideClick: false,
                                                        didOpen: () => {
                                                            Swal.showLoading();
                                                        }
                                                    });
                                                },
                                                success: function(response) {
                                                    if (response.success) {
                                                        Swal.fire({
                                                            icon: 'success',
                                                            title: 'Berhasil!',
                                                            html: `
                                                                ${response.message}<br>
                                                                <strong>Total Barang:</strong> ${response.data.total_barang}<br>
                                                                <strong>Total Modal:</strong> Rp ${response.data.total_modal}<br>
                                                                <strong>Periode:</strong> ${response.data.periode}
                                                            `,
                                                            confirmButtonColor: '#28a745'
                                                        }).then(() => {
                                                            location.reload();
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
                                                        title: 'Error!',
                                                        text: errorMsg
                                                    });
                                                }
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Gagal!',
                                                text: 'Password salah!'
                                            });
                                        }
                                    },
                                    error: function() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal!',
                                            text: 'Verifikasi password gagal!'
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>
