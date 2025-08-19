<x-app-layout>
    <x-slot name="pagetitle">Persetujuan</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Persetujuan Hutang</h3>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div>
    <div class="app-content"> <!--begin::Container-->
        <div class="container"> <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline mb-4"> <!--begin::Header-->
                        <div class="card-header pt-1 pb-1">
                            <div class="card-title">
                                <div class="row">
                                <div class="col-md-auto pe-1">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" id="inputGroup-sizing-sm">Tanggal Transaksi</span>
                                        <input type="text" id="txtperiod" class="form-control">
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div> <!--end::Header--> <!--begin::Body-->
                        <div class="card-body">
                            <table id="tbdatatable" class="table table-sm table-bordered" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Invoice</th>
                                        <th>Tgl Transaksi</th>
                                        <th>Customer</th>
                                        <th>Nominal Hutang</th>
                                        <th>Tenor</th>
                                        <th>Bunga</th>
                                        <th>Admin</th>
                                        <th>HRD</th>
                                        <th>Pengurus</th>
                                        <th></th>
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
    
    <x-slot name="csscustom">
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
            const currentDate = moment().format('YYYY-MM-DD');
            var ds=currentDate,de=currentDate;
            var table = $('#tbdatatable').DataTable({
                ordering: false,"responsive": true,"processing": true,
                "ajax": {
                    "url": "{{ route('app.gethutang') }}",
                    "data":{startdate : function() { return window.ds},enddate : function() { return window.de}},
                    "type": "GET"
                },
                "columns": 
                [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { "data": "nomor_invoice","orderable": false },
                    { "data": "tanggal","orderable": false},
                    { "data": "customer","orderable": false},
                    { "data": "grandtotal","orderable": false},
                    { "data": "tenor","orderable": false},
                    { "data": "bunga_barang","orderable": false},
                    { "data": "approval1","orderable": false,
                        render: function (data, type, row, meta) {
                            let tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval1')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                            return tswitch;
                        }
                    },
                    { "data": "approval2","orderable": false,
                        render: function (data, type, row, meta) {
                            let tswitch='';
                            if(row.VarCicilan == 0){
                                @if(auth()->user()->hasRole('SuperAdmin'))
                                    tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval2')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                @elseif(auth()->user()->hasRole('hrd'))
                                    tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval2')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                @else
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                @endif
                            }else{
                                tswitch='';
                            }
                            return tswitch;
                        }
                    },
                    { "data": "approval3","orderable": false,
                        render: function (data, type, row, meta) {
                            let tswitch='';
                            @if(auth()->user()->hasAnyRole(['SuperAdmin','pengurus']))
                            if(row.VarCicilan == 0){
                                if(row.approval2 == 0){
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                }else{
                                    tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval3')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                }
                            }else{
                                if(row.approval1 == 0){
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                }else{
                                    tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval3')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                }
                            }
                            @else
                            if(row.VarCicilan == 0){
                                if(row.approval2 == 0){
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                }else{
                                    tchecked='<i class="fa-solid fa-circle-check" style="color: #1fbd8d;"></i>';
                                }
                            }else{
                                if(row.approval1 == 0){
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                }else{
                                    tchecked='<i class="fa-solid fa-circle-check" style="color: #1fbd8d;"></i>';
                                }
                            }
                            @endif
                            return tswitch;
                        }
                    },
                    { "data": null,"orderable": false,
                        render: function (data, type, row, meta) {
                            let str= `<span class="badge rounded-pill btn bg-warning editcel" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="dtl('${row.id}')"><i class="fa-solid fa-circle-info"></i></span>`;
                            return str;
                        }
                    }
                ],
            });
            function ambil(idjual){
                Swal.fire({
                    title: "Ambil barang?",
                    text: "Pastikan pembayaran sudah dilakukan!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, lanjutkan!"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'PUT',
                            url: "{{ route('ambil.AmbilBarang') }}",
                            data: {id: idjual},
                            beforeSend: function(xhr) {loader($('#exampleModal'),true)},
                            success: function(response) {
                                Swal.fire({title: "Berhasil!",icon: "success"});
                                table.ajax.reload(null, false);
                                $('#exampleModal').modal('hide');
                                loader($('#exampleModal'),false);
                            },
                            error: function(xhr) {
                                Swal.fire({title: "Error!",text: xhr.responseText,icon: "error"});
                                loader($('#exampleModal'),false);
                            }
                        });
                        
                    }
                });
            }
            function dtl(idjual){
                $.ajax({
                    type: 'GET',
                    url: "{{ route('ambil.getPenjualanDtl', ['id' => ':id']) }}".replace(':id', idjual),
                    beforeSend: function(xhr) {loader($('#exampleModal'),true)},
                    success: function(response) {
                        let str='',grand=0,cn=1;
                        $.each(response.dtl, function(index, value) {
                            str += `<tr class="align-middle">
                                <td>${cn}</td>
                                <td>${value.kode_barang}</td>
                                <td>${value.nama_barang}</td>
                                <td>${value.qty}</td>
                                <td>${value.harga}</td>
                                <td>${value.qty*value.harga}</td>
                                </tr>`;
                            cn++;
                            grand +=value.qty*value.harga;
                        });
                        $('#exampleModalLabel').text(response.hdr.nomor_invoice);
                        $('#tbdtl tbody').html(str);
                        $('#tbdtl tfoot').html(`
                        <tr><th colspan="5" class="text-end">SubTotal</th><th>`+response.hdr.subtotal+`</th></tr>
                        <tr><th colspan="5" class="text-end">Diskon</th><th>`+response.hdr.diskon+`%</th></tr>
                        <tr><th colspan="5" class="text-end">GrandTotal</th><th>`+response.hdr.grandtotal+`</th></tr>
                        <tr><th colspan="6" class="text-end"><button type="button" class="btn btn-success" onclick="ambil(`+response.hdr.id+`)">Ambil&Bayar</button></th></tr>
                        `);
                        loader($('#exampleModal'),false);
                    },
                    error: function(xhr) {
                        Swal.fire({title: "Error!",text: xhr.responseText,icon: "error"});
                        loader($('#exampleModal'),false);
                    }
                });
            }
            $( document ).ready(function() {
                $('#txtperiod').daterangepicker({
                    opens: 'left', // Specify the position of the calendar
                    locale: {format: 'DD/MM/YYYY',},
                }, function (start, end, label) {
                    window.ds = start.format('YYYY-MM-DD');
                    window.de = end.format('YYYY-MM-DD');
                });
                $('#txtperiod').on('change',function(){
                    table.ajax.reload();
                });
            });
        </script>
    </x-slot>
</x-app-layout>