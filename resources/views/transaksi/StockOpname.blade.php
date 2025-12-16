<x-app-layout>
    <x-slot name="pagetitle">Stock Opname</x-slot>

    <div class="app-content-header mb-3"></div>

    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmterima">
                <div class="card card-success card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Stock Opname</h5>
                        @if($existingData)
                            <span class="badge bg-warning float-end">Data Tersimpan</span>
                        @endif
                    </div>
                    <div class="card-body p-3">

                        {{-- Informasi barang terpilih --}}
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="alert alert-info py-2 mb-1">
                                    <strong>Kode:</strong> {{ $selectedBarang->code ?? '-' }}<br>
                                    <strong>Nama Barang:</strong> {{ $selectedBarang->text ?? '-' }}<br>
                                    <strong>Stok Sistem:</strong> <span id="stok-sistem">{{ $selectedBarang->stok_sistem ?? 0 }}</span>
                                </div>
                                <input type="hidden" name="barang_id" value="{{ $selectedBarang->id ?? '' }}">
                                <input type="hidden" name="bulan" value="{{ $bulan }}">
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-secondary py-2 mb-1">
                                    <strong>Petugas:</strong> {{ auth()->user()->name }}<br>
                                    <strong>Tanggal:</strong> <span id="opname-date"></span>
                                    <strong>Bulan:</strong> {{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }}
                                    <input type="hidden" name="tgl_opname" id="tgl_opname_input">
                                </div>
                            </div>
                        </div>

                        {{-- Alert jika sudah ada data --}}
                        @if($existingData)
                            <div class="alert alert-warning alert-dismissible fade show mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Perhatian!</strong> Barang ini sudah memiliki data stock opname untuk bulan ini. 
                                Input baru akan menggantikan data yang sudah ada.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Table untuk input qty + expdate --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="tbterima" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="30%">Quantity</th>
                                                <th width="30%">Expired Date</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($existingData && $existingData->details->count() > 0)
                                                @foreach($existingData->details as $index => $detail)
                                                    <tr class="align-middle" id="row-{{ $index + 1 }}">
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>
                                                            <input type="hidden" name="code[]" value="{{ $selectedBarang->code }}" required>
                                                            <input type="hidden" name="id[]" value="{{ $selectedBarang->id }}" required>
                                                            <input type="number" class="form-control form-control-sm qty" min="0" name="qty[]" value="{{ $detail->qty }}" required>
                                                        </td>
                                                        <td>
                                                            <input type="date" class="form-control form-control-sm" name="exp[]" value="{{ $detail->expired_date ? \Carbon\Carbon::parse($detail->expired_date)->format('Y-m-d') : '' }}">
                                                        </td>
                                                        <td class="text-center">
                                                            @if($index > 0)
                                                                <span class="badge bg-danger dellist" onclick="removeRow({{ $index + 1 }})">
                                                                    <i class="bi bi-trash3-fill"></i>
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                {{-- Default row --}}
                                                <tr class="align-middle" id="row-1">
                                                    <td class="text-center">1</td>
                                                    <td>
                                                        <input type="hidden" name="code[]" value="{{ $selectedBarang->code }}" required>
                                                        <input type="hidden" name="id[]" value="{{ $selectedBarang->id }}" required>
                                                        <input type="number" class="form-control form-control-sm qty" min="0" name="qty[]" value="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="date" class="form-control form-control-sm" name="exp[]">
                                                    </td>
                                                    <td class="text-center"></td>
                                                </tr>
                                            @endif
                                        </tbody>
                                        <tfoot class="table-success">
                                            <tr>
                                                <td colspan="1" class="text-end fw-bold">Total:</td>
                                                <td id="total-qty" class="fw-bold">0</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                                        <i class="bi bi-plus-circle"></i> Tambah Baris
                                    </button>
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle"></i> Total: <span id="total-fisik">0</span> (Stok Sistem: <span id="stok-sistem-display">{{ $selectedBarang->stok_sistem ?? 0 }}</span>)
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Keterangan</span>
                                    <textarea class="form-control" name="keterangan" rows="2" placeholder="Catatan khusus...">{{ $existingData->keterangan ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-start">
                            <div class="col-md-4 d-flex gap-2">
                                <a href="{{ route('stockopname.index', ['bulan' => $bulan]) }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke List
                                </a>
                                <button type="button" class="btn btn-warning" onclick="clearform();">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-floppy-fill"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            let rowCounter = {{ $existingData && $existingData->details->count() > 0 ? $existingData->details->count() : 1 }};

            function numbering(){
                $('#tbterima tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            function calculateTotal() {
                let total = 0;
                $('.qty').each(function() {
                    total += parseInt($(this).val()) || 0;
                });
                $('#total-qty').text(total);
                $('#total-fisik').text(total);
                return total;
            }

            function addRow(){
                rowCounter++;
                let row = `
                    <tr class="align-middle" id="row-${rowCounter}">
                        <td></td>
                        <td>
                            <input type="hidden" class="form-control form-control-sm" name="code[]" value="{{ $selectedBarang->code }}" required>
                            <input type="hidden" class="form-control form-control-sm" name="id[]" value="{{ $selectedBarang->id }}" required>
                            <input type="number" class="form-control form-control-sm qty" min="0" name="qty[]" value="0" required>
                        </td>
                        <td>
                            <input type="date" class="form-control form-control-sm" name="exp[]">
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger dellist" onclick="removeRow(${rowCounter})">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>
                `;
                $('#tbterima tbody').append(row);
                numbering();
                calculateTotal();
                
                // Auto focus ke input qty baru
                setTimeout(() => {
                    $('#row-' + rowCounter + ' .qty').focus();
                }, 100);
            }

            function removeRow(rowId) {
                if ($('#tbterima tbody tr').length > 1) {
                    $(`#row-${rowId}`).remove();
                    numbering();
                    calculateTotal();
                }
            }

            function clearform(){
                Swal.fire({
                    title: 'Reset Form?',
                    text: "Semua data yang sudah diinput akan dihapus",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Reset!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#tbterima tbody').empty();
                        rowCounter = 0;
                        addRow(); // Add default row
                    }
                });
            }

            $(document).ready(function () {
                const today = new Date();
                const formatted = today.toLocaleDateString('en-CA'); // Format YYYY-MM-DD
                $('#opname-date').text(today.toLocaleDateString('id-ID'));
                $('#tgl_opname_input').val(formatted);

                // Hitung total awal
                calculateTotal();

                // Auto calculate on qty change
                $(document).on('input', '.qty', function() {
                    calculateTotal();
                });

                // Submit form
                $('#frmterima').on('submit', function(e) {
                    e.preventDefault();
                    
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        this.classList.add('was-validated');
                        return;
                    }

                    const totalFisik = calculateTotal();
                    const stokSistem = parseInt('{{ $selectedBarang->stok_sistem ?? 0 }}');
                    
                    // Validasi minimal ada 1 qty > 0
                    let hasQty = false;
                    $('.qty').each(function() {
                        if (parseInt($(this).val()) > 0) {
                            hasQty = true;
                            return false;
                        }
                    });
                    
                    if (!hasQty) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Minimal ada 1 quantity yang harus diisi (lebih dari 0)!'
                        });
                        return;
                    }

                    Swal.fire({
                        title: "Simpan Stock Opname?",
                        html: `<strong>Stok Sistem:</strong> ${stokSistem}<br>
                               <strong>Stok Fisik:</strong> ${totalFisik}<br>
                               <strong>Selisih:</strong> ${totalFisik - stokSistem}`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, Simpan!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = $(this).serialize();
                            
                            $.ajax({
                                type: 'POST',
                                url: '{{ route('stockopname.store') }}',
                                data: formData,
                                dataType: 'json',
                                beforeSend: function() {
                                    $('button[type="submit"]').prop('disabled', true)
                                        .html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
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
                                            window.location.href = response.redirect;
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
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: errorMsg
                                    });
                                },
                                complete: function() {
                                    $('button[type="submit"]').prop('disabled', false)
                                        .html('<i class="bi bi-floppy-fill"></i> Simpan');
                                }
                            });
                        }
                    });
                });

                // Keyboard shortcuts
                $(document).keydown(function(e) {
                    // F2 untuk tambah row
                    if (e.key === 'F2') {
                        e.preventDefault();
                        addRow();
                    }
                    // Ctrl+S untuk simpan
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        $('#frmterima').submit();
                    }
                    // Esc untuk focus ke input pertama
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        $('#row-1 .qty').focus().select();
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>