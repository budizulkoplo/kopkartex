<x-app-layout>
    <x-slot name="pagetitle">Penerimaan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Form Penerimaan</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Penerimaan</li>
                        <li class="breadcrumb-item active" aria-current="page">Form</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmterima">
                <div class="card card-success card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Penerimaan</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Date</span>
                                    <input type="text" class="form-control datepicker" name="date" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Petugas</span>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Invoice</span>
                                    <input type="text" class="form-control" name="invoice" required>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Supplier</span>
                                    <input type="text" class="form-control" name="supplier" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Barcode</span>
                                    <input type="text" class="form-control typeahead" id="barcode-search">
                                    <input type="hidden" id="barcode-id">
                                    <span class="input-group-text bg-primary"><i class="bi bi-search text-white"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <table id="tbterima" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Qty</th>
                                            <th>Harga Beli</th>
                                            <th>Harga Jual</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row align-items-start">
                            <div class="col-md-8">
                                <div class="input-group input-group-sm mb-3"> 
                                    <span class="input-group-text label-fixed-width">Catatan</span> 
                                    <textarea class="form-control" name="note" rows="2"></textarea> 
                                </div>
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="button" class="btn btn-warning" onclick="clearform();">
                                    <i class="bi bi-arrow-clockwise"></i> Batal
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

    {{-- Custom CSS --}}
    <x-slot name="csscustom">
        <style>
            .tt-menu {
                width: 100%;
                background-color: #fff;
                border: 1px solid #ced4da;
                border-radius: 0.25rem;
                z-index: 1000;
                max-height: 250px;
                overflow-y: auto;
            }
            .tt-suggestion {
                padding: 0.5rem 1rem;
                cursor: pointer;
            }
            .tt-suggestion:hover {
                background-color: #f8f9fa;
            }
        </style>
    </x-slot>

    {{-- Custom JS --}}
    <x-slot name="jscustom">
        <script>
            function numbering(){
                $('#tbterima tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            function addRow(datarow){
    let str = '', boleh = true;
    $('#tbterima tbody tr').each(function(index, element) {
        if(datarow.id == $(this).data('id')) {
            boleh=false;return false;
        }
    });
    if(boleh){
        str +=`<tr data-id="`+datarow.id+`" class="align-middle">
            <td></td>
            <td>`+datarow.code+`</td>
            <td>`+datarow.text+`</td>
            <td>
                <input type="number" value="1" class="form-control form-control-sm w-auto" 
                    min="1" name="qty[]" required>
                <input type="hidden" name="id[]" value="`+datarow.id+`">
            </td>
            <td>
                <input type="number" value="`+(datarow.harga_beli ?? 0)+`" step="0.01" 
                    class="form-control form-control-sm w-auto" 
                    name="harga_beli[]" required>
            </td>
            <td>
                <input type="number" value="`+(datarow.harga_jual ?? 0)+`" step="0.01" 
                    class="form-control form-control-sm w-auto" 
                    name="harga_jual[]" required>
            </td>
            <td>
                <span class="badge bg-danger dellist" onclick="$(this).closest('tr').remove();numbering();">
                    <i class="bi bi-trash3-fill"></i>
                </span>
            </td>
        </tr>`;
        $('#tbterima tbody').append(str);
    }
    numbering();
    $('#barcode-search').val('');
}


            function clearform(){
                $('input[name="invoice"]').val('');
                $('input[name="supplier"]').val('');
                $('textarea[name="note"]').val('');
                $('#tbterima tbody tr').remove();
            }

            $(document).ready(function () {
                let currentRequest = null;
                $('#barcode-search').typeahead({
                    source: function (query, process) {
                        if (currentRequest !== null) {
                            currentRequest.abort();
                        }
                        currentRequest = $.ajax({
                            url: '{{ route('penerimaan.getbarang') }}',
                            type: 'GET',
                            data: { q: query },
                            dataType: 'json',
                            success: function (data) {
                                barang = data;
                                return process(data.map(barang => barang.text));
                            }
                        });
                        return currentRequest;
                    },
                    afterSelect: function (text) {
                        const selected = barang.find(barang => barang.text === text);
                        if (selected) {
                            $('#barcode-id').val(selected.code);
                            addRow(selected);
                        }
                    }
                });

                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('setDate', new Date());

                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $.ajax({
                            url: '{{ route('penerimaan.getbarangbycode') }}',
                            method: 'GET',
                            data: { kode: $(this).val() },
                            dataType: 'json',
                            success: function(response) {
                                addRow(response);
                            },
                            error: function() {
                                Swal.fire({
                                    title: "Barang tidak ditemukan!",
                                    icon: "error",
                                    draggable: true
                                });
                            }
                        });
                    }
                });

                $('#frmterima').on('submit', function(e) {
                    e.preventDefault();
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                    } else {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('penerimaan.store') }}',
                            data: $(this).serialize(),
                            success: function(response) {
                                Swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: "Data berhasil disimpan",
                                    showConfirmButton: false,
                                    timer: 2500
                                });
                                clearform();
                            },
                            error: function() {
                                Swal.fire({
                                    icon: "error",
                                    title: "Oops...",
                                    text: "Terjadi kesalahan saat menyimpan!"
                                });
                            }
                        });
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>
