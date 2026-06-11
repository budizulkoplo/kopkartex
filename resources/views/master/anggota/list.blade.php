<x-app-layout>
    <x-slot name="pagetitle">Anggota</x-slot>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Anggota Management</h3>
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
                                    <div class="col"></div>
                                </div>
                            </div>
                            <div class="card-tools"> 
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalForm" id="btnadd"><i class="bi bi-file-earmark-plus"></i> Pendaftaran Anggota</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tabelAnggota" class="table table-sm table-striped" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        @foreach ($baseColumns as $column)
                                            <th>{{ $column['label'] }}</th>
                                        @endforeach
                                        @foreach ($legacyColumns as $column)
                                            <th>{{ $column['label'] }}</th>
                                        @endforeach
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('users.updatepassword') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabelReset">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="userid" id="tuserid" required>
                    <div class="row">
                        <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">New Password</span>
                        <input type="password" name="new_password" id="tpassword" class="form-control" placeholder="" aria-label="Password" aria-describedby="basic-addon1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saverole">Save changes</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    
    <div class="modal fade" id="exampleModalForm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <form id="frmusers" class="needs-validation" novalidate enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="fidusers" id="fidusers">
                    <div class="row">
                        <div class="col col-lg-6 mb-1">
                            <label for="fnomor_anggota" class="form-label">No.Anggota</label>
                            <input type="text" class="form-control form-control-sm" id="fnomor_anggota" name="nomor_anggota" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="fusername" class="form-label">Username</label>
                            <input type="text" class="form-control form-control-sm" id="fusername" name="username" readonly required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Nama</label>
                            <input type="text" class="form-control form-control-sm" id="fname" name="name" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">NIK</label>
                            <input type="number" class="form-control form-control-sm" id="fnik" name="nik" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-sm" id="femail" name="email" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Jabatan</label>
                            <input type="text" class="form-control form-control-sm" id="fjabatan" name="jabatan" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Gaji</label>
                            <input type="number" class="form-control form-control-sm" id="fgaji" name="gaji" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="flimit_hutang" class="form-label">Limit Hutang</label>
                            <input type="number" class="form-control form-control-sm" id="flimit_hutang" name="limit_hutang" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="flimit_ppob" class="form-label">Limit PPOB</label>
                            <input type="number" class="form-control form-control-sm" id="flimit_ppob" name="limit_ppob" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">No HP</label>
                            <input type="text" class="form-control form-control-sm" id="fnohp" name="nohp" required>
                        </div>
                        <!-- auto generate Simpanan Awal -->
                        <div class="col col-lg-6 mb-1">
                            <label for="fsimpanan_awal" class="form-label">Simpanan Awal</label>
                            <input type="number" class="form-control form-control-sm" id="fsimpanan_awal" name="simpanan_awal" value="0" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="flexCheckChecked" name="status" checked>
                            <label class="form-check-label" for="flexCheckChecked">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saverole">Save changes</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    
    <x-slot name="csscustom">
        <style>
            #tabelAnggota th,
            #tabelAnggota td {
                white-space: nowrap;
                vertical-align: middle;
            }

            #tabelAnggota .editable-cell {
                min-width: 92px;
                height: 28px;
                padding: 2px 6px;
                font-size: 12px;
            }

            #tabelAnggota .editable-cell.is-saving {
                border-color: #ffc107;
                background-color: #fff8e1;
            }

            #tabelAnggota .editable-cell.is-saved {
                border-color: #198754;
                background-color: #ecf8f1;
            }

            #tabelAnggota .editable-cell.is-error {
                border-color: #dc3545;
                background-color: #fff1f1;
            }
        </style>
    </x-slot>
    <x-slot name="jscustom">
        <script>
            function loader(obj,onoff){
                if(onoff){
                    obj.waitMe({
                    effect : 'bouncePulse',
                    text : 'Please wait',
                    bg : 'rgba(255,255,255,0.7)',
                    color : '#000',
                    maxSize : '',
                    waitTime : -1,
                    textPos : 'vertical',
                    fontSize : '',
                    source : '',
                    onClose : function() {}
                    });
                }else{
                    obj.waitMe('hide');
                }
            }
            $(document).ready(function() {
                const baseColumns = @json($baseColumns);
                const legacyColumns = @json($legacyColumns);
                const editableColumns = {...baseColumns, ...legacyColumns};
                const editableColumnEntries = [
                    ...Object.entries(baseColumns).map(([field, meta]) => ({field, meta, searchable: true})),
                    ...Object.entries(legacyColumns).map(([field, meta]) => ({field, meta, searchable: false})),
                ];

                function escapeHtml(value) {
                    return $('<div>').text(value ?? '').html();
                }

                function renderEditableCell(field, meta) {
                    return function(data, type, row) {
                        if (type !== 'display') {
                            return data;
                        }

                        const inputType = meta.type === 'numeric'
                            ? 'number'
                            : (meta.type === 'date' ? 'date' : 'text');
                        const value = data ?? '';
                        const maxAttr = meta.max ? ` maxlength="${meta.max}"` : '';

                        return `<input type="${inputType}" class="form-control form-control-sm editable-cell" data-user="${row.id}" data-field="${field}" data-original="${escapeHtml(value)}" value="${escapeHtml(value)}"${maxAttr}>`;
                    }
                }

                const dataTableColumns = [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    ...editableColumnEntries.map(({field, meta, searchable}) => ({
                        data: field,
                        name: field,
                        searchable: searchable,
                        render: renderEditableCell(field, meta)
                    })),
                    {
                        data: 'idusers',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <span class="badge rounded-pill bg-info formcell" data-bs-toggle="modal" data-bs-target="#exampleModalForm" data-id="${data}">
                                    <i class="bi bi-pencil-square"></i>
                                </span>
                                <span class="badge rounded-pill bg-warning" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="$('#tuserid').val('${row.id}')">
                                    <i class="bi bi-key"></i>
                                </span>
                                <span class="badge rounded-pill bg-danger">
                                    <i class="fa-solid fa-trash-can"></i>
                                </span>
                            `;
                        }
                    }
                ];

                $('#fnomor_anggota').on('input', function() {
                    var nomor = $(this).val();
                    $('#fusername').val(nomor);
                });
                $('#btnadd').on('click',function(){
                    clearfrm();
                    $.ajax({
                        url: "{{ route('anggota.getcode') }}",method: "GET",
                        beforeSend: function(xhr) {loader($('#frmusers'),true)},
                        success: function(response) {
                            // $('input[name="nomor_anggota"]').val(response);
                            loader($('#frmusers'),false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Gagal:', status, error);
                            console.log('Response Text:', xhr.responseText);
                            loader($('#frmusers'),false);
                        },
                    });
                });
                var table = $('#tabelAnggota').DataTable({
                    ordering: false,
                    responsive: false,
                    scrollX: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('anggota.getdata') }}",
                        type: "GET"
                    },
                    columns: dataTableColumns,
                    columnDefs: [
                        { targets: [dataTableColumns.length - 1], className: 'text-center' }
                    ]
                });

                $('#tabelAnggota tbody').on('focus', '.editable-cell', function() {
                    $(this).data('last-value', $(this).val());
                });

                $('#tabelAnggota tbody').on('blur change', '.editable-cell', function() {
                    const input = $(this);
                    const value = input.val();

                    if (value === input.data('original') || value === input.data('last-value')) {
                        return;
                    }

                    input.removeClass('is-saved is-error').addClass('is-saving');
                    input.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('anggota.inline-update') }}",
                        method: "POST",
                        data: {
                            id: input.data('user'),
                            field: input.data('field'),
                            value: value,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            input.data('original', response.value ?? '');
                            input.data('last-value', response.value ?? '');
                            input.val(response.value ?? '');
                            input.removeClass('is-saving is-error').addClass('is-saved');
                            setTimeout(function() {
                                input.removeClass('is-saved');
                            }, 900);
                        },
                        error: function(xhr) {
                            input.removeClass('is-saving').addClass('is-error');
                            const message = xhr.responseJSON?.message ?? 'Data gagal disimpan.';
                            alert(message);
                        },
                        complete: function() {
                            input.prop('disabled', false);
                        }
                    });
                });

                function clearfrm() {
                    $('#fidusers').val('');
                    $('#fnomor_anggota').val('');
                    $('#fusername').val('').prop('disabled', false);
                    $('#fname').val('');
                    $('#fnik').val('');
                    $('#fjabatan').val('');
                    $('#fgaji').val('');
                    $('#flimit_hutang').val('');
                    $('#flimit_ppob').val('');
                    $('#femail').val('');
                    $('#fnohp').val('');
                    $('#flexCheckChecked').prop('checked', true);
                }

                $('#btnadd').on('click', function() {
                    clearfrm();
                    $('#exampleModalLabel').text('Tambah Anggota');
                    // You might want to add code here to generate nomor_anggota
                });

                $('#tabelAnggota tbody').on('click', '.formcell', function() {
                    var id = $(this).data('id');
                    $('#exampleModalLabel').text('Edit Anggota');
                    
                    var row = table.row($(this).closest('tr')).data();
                    $('#fidusers').val(row.idusers);
                    $('input[name="nomor_anggota"]').val(row.nomor_anggota);
                    $('input[name="name"]').val(row.name);
                    $('input[name="username"]').val(row.username);
                    $('input[name="username"]').prop('disabled', true);
                    $('input[name="nik"]').val(row.nik);
                    $('input[name="jabatan"]').val(row.jabatan);
                    $('select[name="unit_kerja"]').val(row.unit_kerja);
                    $('input[name="tanggal_masuk"]').val(row.tanggal_masuk);
                    $('input[name="limit_hutang"]').val(row.limit_hutang);
                    $('input[name="limit_ppob"]').val(row.limit_ppob);
                    $('input[name="email"]').val(row.email);
                    $('input[name="nohp"]').val(row.nohp);
                    console.log(row.status)
                    if(row.status=='aktif'){
                        $('#flexCheckChecked').prop('checked', true);
                    }else{
                        $('#flexCheckChecked').prop('checked', false);
                    }   
                });

                $('#frmusers').on('submit', function(e) {
                    e.preventDefault();
                    const form = this;
                    const disabled = form.querySelectorAll(':disabled');
                    
                    // Enable temporarily
                    disabled.forEach(el => el.disabled = false);
                    const formData = new FormData(this);
                    disabled.forEach(el => el.disabled = true);
                    
                    $.ajax({
                        url: "{{ route('anggota.store') }}",
                        method: "POST",
                        data: formData,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            table.ajax.reload(null, false);
                            $('#exampleModalForm').modal('hide');
                            clearfrm();
                        },
                        error: function(xhr) {
                            // Handle errors
                        }
                    });
                });
            });
        </script>
    </x-slot>
</x-app-layout>
