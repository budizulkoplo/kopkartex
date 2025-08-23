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
                    <div class="card card-info card-outline mb-4 cardizin"> <!--begin::Header-->
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
                            <div class="row">
                                <h3>Pinjaman</h3>
                                <table id="tbpinjaman" class="table table-sm table-bordered" style="width: 100%; font-size: small;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Nomor Anggota</th>
                                            <th>Nama</th>
                                            <th>Nominal Pengajuan</th>
                                            <th>jaminan</th>
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

            var tbpinjaman = $('#tbpinjaman').DataTable({
                ordering: false,"responsive": true,"processing": true, dom: 'frtip',
                "ajax": {
                    "url": "{{ route('app.gethutang') }}",
                    "data":{jenis : 'pinjaman',startdate : function() { return window.ds},enddate : function() { return window.de}},
                    "type": "GET"
                },
                "columns": 
                [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { "data": "tgl_pengajuan","orderable": false },
                    { "data": "nomor_anggota","orderable": false},
                    { "data": "UserName","orderable": false},
                    { "data": "nominal_pengajuan","orderable": false},
                    { "data": "jaminan","orderable": false},
                    { "data": "tenor","orderable": false},
                    { "data": "bunga_pinjaman","orderable": false},
                    { "data": "approval1","orderable": false,
                        render: function (data, type, row, meta) {
                            let tswitch;
                            @if(auth()->user()->hasAnyRole(['SuperAdmin']))
                                tswitch = `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval1','pinjaman')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                            @elseif(auth()->user()->hasAnyRole(['admin']))
                                 if(row.VarCicilan == 0){
                                    if(row.approval3 == 1){
                                        tswitch='<i class="fa-solid fa-circle-check" style="color: #1fbd8d;"></i><span></span>';
                                    }else{
                                        tswitch = `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval1','pinjaman')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                    }
                                }else{
                                    if(row.approval2 == 1){
                                        tswitch='<i class="fa-solid fa-circle-check" style="color: #1fbd8d;"></i><span></span>';
                                    }else{
                                        tswitch = `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval1','pinjaman')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                    }
                                }
                            @else
                                if(data === 0)
                                tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                else
                                tswitch='<i class="fa-solid fa-circle-check" style="color: #1fbd8d;"></i><span></span>';
                            @endif
                            return tswitch;
                        }
                    },
                    { "data": "approval2","orderable": false,
                        render: function (data, type, row, meta) {
                            let tswitch='';
                            if(row.VarCicilan === 1){
                                @if(auth()->user()->hasRole('SuperAdmin'))
                                    tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval2','pinjaman')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                @elseif(auth()->user()->hasRole('hrd'))
                                    if(row.approval1 === 1 && row.approval3 === 0){
                                        tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval2','pinjaman')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                    }else if(row.approval1 === 0){
                                        tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                    }else if(row.approval3 === 1){
                                        tswitch='<i class="fa-solid fa-circle-check" style="color: #1fbd8d;"></i>';
                                    }
                                @else
                                    if(row.approval2 === 0)
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                    else
                                    tswitch='<i class="fa-solid fa-circle-check" style="color: #1fbd8d;"></i>';
                                @endif
                            }else{
                                tswitch='-';
                            }
                            return tswitch;
                        }
                    },
                    { "data": "approval3","orderable": false,
                        render: function (data, type, row, meta) {
                            let tswitch='';
                            @if(auth()->user()->hasAnyRole(['SuperAdmin']))
                                tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval3','pinjaman')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                            @elseif(auth()->user()->hasAnyRole(['pengurus']))
                            if(row.VarCicilan == 0){
                                if(row.approval1 == 0){
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                }else{
                                    tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval3','pinjaman')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                }
                            }else{
                                if(row.approval2 == 0){
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                }else{
                                    tswitch= `<div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input chk2" onClick="approval(this,'`+row.id+`','approval3','pinjaman')" `+(data==1?'checked':'')+` type="checkbox" data-code="`+row.id+`" role="switch"/>
                                        </div>`;
                                }
                            }
                            @else
                                if(row.approval3 == 0 ){
                                    tswitch='<i class="fa-regular fa-hourglass-half"></i>';
                                }else{
                                    tswitch='<i class="fa-solid fa-circle-check" style="color: #1fbd8d;"></i>';
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
            //function chkneed
            function approval(obj,tcode,act,jenis){
                let ckecked = obj.checked
                let urlstore = jenis == 'penjualan' ? '{{ route('app.set') }}' : '{{ route('app.set') }}';
                Swal.fire({
                    title: ckecked ? "Dokumen Disetujui?" : "Persetujuan di Batalkan?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: ckecked ? "Ya, saya setuju!" : "Batalkan sekarang"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: urlstore,
                            method:"PUT",
                            data: { 
                                fld: act,
                                code: tcode, 
                                chk: ckecked?1:0,
                                jenis:jenis
                            },
                            beforeSend: function(xhr) {loader($('.cardizin'),true);},
                            success: function(response) {
                                if(response){
                                    if(jenis == 'penjualan'){
                                        table.ajax.reload(null, false);
                                    }else{
                                        tbpinjaman.ajax.reload(null, false);
                                    }
                                    Swal.fire({position: "top-end",icon: "success",title: "Success",showConfirmButton: false,timer: 2500});
                                }else{
                                    if(obj.checked)
                                    $(obj).prop('checked', false);
                                    else
                                    $(obj).prop('checked', true);
                                    Swal.fire({position: "top-end",icon: "error",title: response.message,showConfirmButton: false,timer: 1500});
                                }
                                loader($('.cardizin'),false);
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                if(obj.checked)
                                    $(obj).prop('checked', false);
                                else
                                    $(obj).prop('checked', true);
                                Swal.fire({position: "top-end",icon: "error",title: jqXHR.responseJSON.message,showConfirmButton: false,timer: 1500});
                                loader($('.cardizin'),false);
                            }
                        });
                    }else{
                        if(obj.checked)
                        $(obj).prop('checked', false);
                        else
                        $(obj).prop('checked', true);
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
                    table.ajax.reload(null, false);
                    tbpinjaman.ajax.reload(null, false);
                });
            });
        </script>
    </x-slot>
</x-app-layout>