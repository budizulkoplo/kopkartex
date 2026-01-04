<x-app-layout>
    <x-slot name="pagetitle">Mutasi Stok</x-slot>
    
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Mutasi Stok</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline mb-4">
                        <div class="card-header pt-1 pb-1">
                            <div class="card-title">
                                <div class="row">
                                    <div class="col-md-auto pe-1">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" id="inputGroup-sizing-sm">Periode</span>
                                            <input type="text" id="txtperiod" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-tools"> 
                                <a class="btn btn-sm btn-primary" href="{{ route('mutasi.form') }}" role="button">
                                    <i class="bi bi-file-earmark-plus"></i> Buat Mutasi
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <table id="tbdatatable" class="table table-sm table-bordered" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>No. Mutasi</th>
                                        <th>From Unit</th>
                                        <th>To Unit</th>
                                        <th>Tanggal</th>
                                        <th>Petugas</th>
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
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalDetailLabel">
                        <i class="bi bi-info-circle"></i> Detail Mutasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card card-sm">
                                <div class="card-body p-2">
                                    <small class="text-muted">No. Mutasi</small>
                                    <h6 class="mb-0" id="detailNomorMutasi">-</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-sm">
                                <div class="card-body p-2">
                                    <small class="text-muted">Tanggal</small>
                                    <h6 class="mb-0" id="detailTanggal">-</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card card-sm">
                                <div class="card-body p-2">
                                    <small class="text-muted">Dari Unit</small>
                                    <h6 class="mb-0" id="detailDariUnit">-</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-sm">
                                <div class="card-body p-2">
                                    <small class="text-muted">Ke Unit</small>
                                    <h6 class="mb-0" id="detailKeUnit">-</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card card-sm">
                                <div class="card-body p-2">
                                    <small class="text-muted">Catatan</small>
                                    <p class="mb-0" id="detailCatatan">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mb-2">Detail Barang</h6>
                    <table id="tbdtl" class="table table-sm table-bordered" style="width: 100%; font-size: small;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty Mutasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="cetakNotaMutasi()" id="btnCetakModal">
                        <i class="bi bi-printer"></i> Cetak Nota
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="csscustom">
        <style>
            .status-badge {
                font-size: 0.75rem;
                padding: 0.2rem 0.5rem;
                border-radius: 0.25rem;
            }
            .status-diajukan {
                background-color: #ffc107;
                color: #000;
            }
            .status-dikirim {
                background-color: #17a2b8;
                color: #fff;
            }
            .status-diterima {
                background-color: #28a745;
                color: #fff;
            }
            .status-dibatalkan {
                background-color: #dc3545;
                color: #fff;
            }
            .aksi-buttons {
                display: flex;
                gap: 5px;
                justify-content: center;
                flex-wrap: wrap;
            }
            .aksi-buttons .btn {
                padding: 0.15rem 0.4rem;
                font-size: 0.75rem;
                min-width: 30px;
            }
            .card-sm {
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
            }
            .table th {
                background-color: #f8f9fa;
            }
            .badge-status {
                font-size: 0.7rem;
            }
            .btn-action-sm {
                padding: 0.1rem 0.3rem;
                font-size: 0.7rem;
                margin: 1px;
            }
        </style>
    </x-slot>
    
    <x-slot name="jscustom">
        <script>
            const currentDate = moment().format('YYYY-MM-DD');
            var ds = currentDate, de = currentDate;
            var selectedMutasiId = null;
            var selectedMutasiNumber = null;
            var selectedMutasiData = null;

            var table = $('#tbdatatable').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('mutasi.getdata') }}",
                    data: {
                        startdate: function() { return window.ds },
                        enddate: function() { return window.de }
                    },
                    type: "GET"
                },
                columns: [
                    { 
                        data: 'DT_RowIndex', 
                        name: 'DT_RowIndex', 
                        orderable: false, 
                        searchable: false,
                        width: '3%'
                    },
                    { 
                        data: "id",
                        orderable: false,
                        width: '10%',
                        render: function(data, type, row) {
                            return 'MUT-' + String(data).padStart(6, '0');
                        }
                    },
                    { 
                        data: "NamaUnit1",
                        orderable: false,
                        width: '15%'
                    },
                    { 
                        data: "NamaUnit2",
                        orderable: false,
                        width: '15%'
                    },
                    { 
                        data: "tanggal",
                        orderable: false,
                        width: '12%',
                        render: function(data) {
                            return moment(data).format('DD/MM/YYYY');
                        }
                    },
                    { 
                        data: "petugas",
                        orderable: false,
                        width: '15%'
                    },
                    { 
                        data: "status",
                        orderable: false,
                        width: '10%',
                        render: function(data) {
                            let badgeClass = 'status-diajukan';
                            let statusText = 'Diajukan';
                            
                            switch(data) {
                                case 'dikirim':
                                    badgeClass = 'status-dikirim';
                                    statusText = 'Dikirim';
                                    break;
                                case 'diterima':
                                    badgeClass = 'status-diterima';
                                    statusText = 'Diterima';
                                    break;
                                case 'dibatalkan':
                                    badgeClass = 'status-dibatalkan';
                                    statusText = 'Dibatalkan';
                                    break;
                            }
                            
                            return `<span class="badge status-badge ${badgeClass}">${statusText}</span>`;
                        }
                    },
                    { 
                        data: null,
                        orderable: false,
                        width: '20%',
                        render: function(data, type, row) {
                            const mutasiNumber = 'MUT-' + String(row.id).padStart(6, '0');
                            let str = `
                                <div class="aksi-buttons">
                                    <button type="button" class="btn btn-info btn-sm" onclick="showDetail('${row.id}')" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="cetakNota('${row.id}', '${mutasiNumber}')" title="Cetak Nota">
                                        <i class="bi bi-printer"></i>
                                    </button>
                            `;
                            
                            // Tombol update status hanya untuk mutasi yang belum diterima/dibatalkan
                            if (row.status === 'diajukan' || row.status === 'dikirim') {
                                str += `
                                    <button type="button" class="btn btn-success btn-sm" onclick="updateStatus('${row.id}', 'diterima')" title="Set Diterima">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                `;
                            }
                           
                            str += `</div>`;
                            return str;
                        }
                    }
                ],
                language: {
                    processing: "Memproses...",
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    zeroRecords: "Data tidak ditemukan",
                    emptyTable: "Tidak ada data tersedia",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            function showDetail(idmutasi) {
                selectedMutasiId = idmutasi;
                selectedMutasiNumber = 'MUT-' + String(idmutasi).padStart(6, '0');
                
                // Reset modal content
                $('#detailNomorMutasi').text(selectedMutasiNumber);
                $('#detailDariUnit').text('-');
                $('#detailKeUnit').text('-');
                $('#detailTanggal').text('-');
                $('#detailCatatan').text('-');
                
                // Load detail mutasi
                $.ajax({
                    type: 'GET',
                    url: '{{ url("mutasi/detail") }}/' + idmutasi,
                    data: { id: idmutasi },
                    beforeSend: function() {
                        $('#tbdtl tbody').html('<tr><td colspan="6" class="text-center"><i class="bi bi-hourglass-split"></i> Memuat data...</td></tr>');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update header info
                            selectedMutasiData = response.data;
                            $('#detailNomorMutasi').text(selectedMutasiNumber);
                            $('#detailDariUnit').text(response.data.nama_unit_asal);
                            $('#detailKeUnit').text(response.data.nama_unit_tujuan);
                            $('#detailTanggal').text(response.data.tanggal_formatted);
                            $('#detailCatatan').text(response.data.note || '-');
                            
                            // Update modal title
                            $('#modalDetailLabel').html(`<i class="bi bi-info-circle"></i> Detail Mutasi ${selectedMutasiNumber}`);
                            
                            // Load detail barang
                            loadDetailBarang(idmutasi);
                        } else {
                            $('#tbdtl tbody').html('<tr><td colspan="6" class="text-center text-danger">' + response.message + '</td></tr>');
                        }
                    },
                    error: function() {
                        $('#tbdtl tbody').html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>');
                    }
                });
                
                $('#modalDetail').modal('show');
            }

            function loadDetailBarang(idmutasi) {
                $.ajax({
                    type: 'GET',
                    url: '{{ route('mutasi.dtl') }}',
                    data: { id: idmutasi },
                    success: function(response) {
                        let str = '';
                        let cn = 1;
                        
                        if (response.length === 0) {
                            str = '<tr><td colspan="6" class="text-center">Tidak ada data barang</td></tr>';
                        } else {
                            $.each(response, function(index, value) {
                                str += `<tr class="align-middle">
                                    <td class="text-center">${cn}</td>
                                    <td>${value.kode_barang || 'N/A'}</td>
                                    <td>${value.nama_barang || 'N/A'}</td>
                                    <td class="text-center">${value.qty || 0}</td>
                                    <td class="text-center">`;
                                
                                if (value.canceled == 0) {
                                    str += `<span class="badge bg-success badge-status">Aktif</span>`;
                                } else {
                                    str += `<span class="badge bg-danger badge-status">Dikembalikan</span>`;
                                }
                                
                                str += `</td>
                                    <td class="text-center">`;
                                
                                // Tombol kembalikan hanya untuk barang yang belum dikembalikan
                                if (value.canceled == 0 && selectedMutasiData && selectedMutasiData.status !== 'dibatalkan') {
                                    str += `
                                        <button type="button" class="btn btn-warning btn-action-sm" 
                                                onclick="kembalikanBarang('${value.id}', '${value.barang_id}', '${value.nama_barang}')" 
                                                title="Kembalikan Barang">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    `;
                                }
                                
                                str += `</td></tr>`;
                                cn++;
                            });
                        }
                        
                        $('#tbdtl tbody').html(str);
                    },
                    error: function() {
                        $('#tbdtl tbody').html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data barang</td></tr>');
                    }
                });
            }

            function cetakNota(idmutasi, mutasiNumber) {
                const notaUrl = '{{ route("mutasi.nota", ":id") }}'.replace(':id', idmutasi);
                
                // Buka di tab baru dengan auto print
                const printWindow = window.open(notaUrl + '?autoprint=true', '_blank');
                
                // Beri feedback
                Swal.fire({
                    icon: 'success',
                    title: 'Membuka Nota',
                    text: `Nota ${mutasiNumber} sedang dibuka...`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            function cetakNotaMutasi() {
                if (selectedMutasiId) {
                    cetakNota(selectedMutasiId, selectedMutasiNumber);
                }
            }

            function updateStatus(idmutasi, status) {
                let statusText = status === 'diterima' ? 'Diterima' : 'Dikirim';
                
                Swal.fire({
                    title: `Update Status ke ${statusText}?`,
                    text: "Anda yakin ingin mengupdate status mutasi?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Update!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('mutasi.updateStatus') }}',
                            data: {
                                id: idmutasi,
                                status: status,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: `Status mutasi berhasil diupdate ke ${statusText}`,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload();
                                    if (selectedMutasiId === idmutasi) {
                                        $('#modalDetail').modal('hide');
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Gagal mengupdate status'
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Terjadi kesalahan saat mengupdate status'
                                });
                            }
                        });
                    }
                });
            }

            function batalkanMutasi(idmutasi) {
                Swal.fire({
                    title: "Batalkan Mutasi?",
                    html: `<div class="text-start">
                            <p>Anda yakin ingin membatalkan mutasi ini?</p>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> <strong>Perhatian:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Semua barang akan dikembalikan ke unit asal</li>
                                    <li>Mutasi tidak dapat dikembalikan setelah dibatalkan</li>
                                    <li>Status mutasi akan berubah menjadi "Dibatalkan"</li>
                                </ul>
                            </div>
                        </div>`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#dc3545",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Batalkan!",
                    cancelButtonText: "Batal",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            type: 'POST',

                            url: '{{ url("mutasi/batalkan") }}/' + idmutasi,
                            data: {
                                id: idmutasi,
                                _token: '{{ csrf_token() }}'
                            }
                        }).then(response => {
                            if (!response.success) {
                                throw new Error(response.message || 'Gagal membatalkan mutasi');
                            }
                            return response;
                        }).catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Mutasi berhasil dibatalkan',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload();
                        if (selectedMutasiId === idmutasi) {
                            $('#modalDetail').modal('hide');
                        }
                    }
                });
            }

            function kembalikanBarang(detailId, barangId, namaBarang) {
                Swal.fire({
                    title: "Kembalikan Barang?",
                    html: `<div class="text-start">
                            <p>Anda yakin ingin mengembalikan barang:</p>
                            <div class="alert alert-info">
                                <strong>${namaBarang}</strong>
                            </div>
                            <p>Barang akan dikembalikan ke unit asal.</p>
                        </div>`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Kembalikan!",
                    cancelButtonText: "Batal",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            type: 'POST',
                            url: '{{ route('mutasi.kembalikan') }}',
                            data: {
                                idmutasi: selectedMutasiId,
                                idbarang: barangId,
                                detail_id: detailId,
                                _token: '{{ csrf_token() }}'
                            }
                        }).then(response => {
                            if (!response.success) {
                                throw new Error(response.message || 'Gagal mengembalikan barang');
                            }
                            return response;
                        }).catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Barang berhasil dikembalikan',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        // Reload detail barang
                        loadDetailBarang(selectedMutasiId);
                    }
                });
            }

            $(document).ready(function() {
                $('#txtperiod').daterangepicker({
                    opens: 'left',
                    locale: {
                        format: 'DD/MM/YYYY',
                        applyLabel: 'Terapkan',
                        cancelLabel: 'Batal',
                        customRangeLabel: 'Custom Range'
                    },
                    startDate: moment().subtract(30, 'days'),
                    endDate: moment()
                }, function (start, end, label) {
                    window.ds = start.format('YYYY-MM-DD');
                    window.de = end.format('YYYY-MM-DD');
                    table.ajax.reload();
                });

                $('#txtperiod').on('apply.daterangepicker', function() {
                    table.ajax.reload();
                });

                // Shortcut untuk refresh table dengan F5
                $(document).keydown(function(e) {
                    if (e.key === 'F5') {
                        e.preventDefault();
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'info',
                            title: 'Refresh',
                            text: 'Tabel direfresh',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
                
                // Event saat modal detail ditutup
                $('#modalDetail').on('hidden.bs.modal', function () {
                    selectedMutasiId = null;
                    selectedMutasiNumber = null;
                    selectedMutasiData = null;
                });
            });
        </script>
    </x-slot>
</x-app-layout>