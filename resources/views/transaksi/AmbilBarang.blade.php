<x-app-layout>
    <x-slot name="pagetitle">Ambil Barang</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Ambil Barang</h3>
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
                                            <span class="input-group-text" id="inputGroup-sizing-sm">Tgl.Pembelian</span>
                                            <input type="text" id="txtperiod" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-auto pe-1">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text" id="inputGroup-sizing-sm">Status</span>
                                            <select name="" class="form-control form-control-sm" id="txtstatus">
                                                <option value="all">All</option>
                                                <option value="pesan">dipesan</option>
                                                <option value="proses">proses</option>
                                                <option value="ready">siap</option>
                                                <option value="finish">selesai</option>
                                            </select>
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
                                        <th>Tanggal Pembelian</th>
                                        <th>Customer</th>
                                        <th>Bayar</th>
                                        <th>Status</th>
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
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="frmbarang" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idmutasi" id="idmutasi">
                    <table id="tbdtl" class="table table-sm table-bordered" style="width: 100%; font-size: small;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot></tfoot>
                    </table>
                </div>
            </form>
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
                    "url": "{{ route('ambil.getPenjualan') }}",
                    "data":{startdate : function() { return window.ds},enddate : function() { return window.de},status:function(){return $('#txtstatus').val()}},
                    "type": "GET"
                },
                "columns": 
                [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { "data": "nomor_invoice","orderable": false },
                    { "data": "tanggal","orderable": false},
                    { "data": "customer","orderable": false},
                    { "data": "grandtotal","orderable": false},
                    { "data": "status_ambil","orderable": false},
                    { "data": null,"orderable": false,
                        render: function (data, type, row, meta) {
                            let str= `<span class="badge rounded-pill btn bg-warning editcel" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="dtl('${row.id}')"><i class="fa-solid fa-circle-info"></i></span>`;
                            return str;
                        }
                    }
                ],
            });
            function ambil(idjual,pstatus,obj){
                if(pstatus == 'proses' || pstatus == 'ready'){
                    loader($('#exampleModal'),true);
                    axios.put('{{ route('ambil.AmbilBarang') }}', {
                        id: idjual,
                        status: pstatus
                    })
                    .then(response => {
                        $(obj).prop("disabled", true);
                        loader($('#exampleModal'),false);
                        table.ajax.reload(null,false);
                    })
                    .catch(error => {
                        Swal.fire({title: "Error!",text: error,icon: "error"});
                        loader($('#exampleModal'),true);
                    });
                }else{
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
                                data: {id: idjual,status: pstatus},
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
            }
            function delitem(iddtl,idpenjualan){
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        axios.delete('{{ route('ambil.delitem') }}', {data: { id: iddtl,penjualan: idpenjualan }})
                        .then(response => {
                            Swal.fire({title: "Deleted!",text: "Your file has been deleted.",icon: "success"});
                            dtl(idpenjualan);
                            table.ajax.reload(null,false);
                        })
                        .catch(error => {
                            Swal.fire({icon: 'error',title: 'Gagal',text: error});
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
                                <td><button type="button" class="btn btn-sm btn-danger" onclick="delitem(${value.id},${value.penjualan_id})" ${response.hdr.status_ambil == 'finish' ? 'style="display:none"':'' }><i class="fa-solid fa-trash"></i></button></td>
                                </tr>`;
                            cn++;
                            grand +=value.qty*value.harga;
                        });
                        $('#exampleModalLabel').text(response.hdr.nomor_invoice);
                        $('#tbdtl tbody').html(str);
                        $('#tbdtl tfoot').html(`
                        <tr><th colspan="5" class="text-end">SubTotal</th><th colspan="2">`+response.hdr.subtotal+`</th></tr>
                        <tr><th colspan="5" class="text-end">Diskon</th><th colspan="2">`+response.hdr.diskon+`%</th></tr>
                        <tr><th colspan="5" class="text-end">GrandTotal</th><th colspan="2">`+response.hdr.grandtotal+`</th></tr>
                        <tr><th colspan="7" class="text-end">
                            <button type="button" class="btn btn-warning" onclick="ambil(${response.hdr.id},'proses',this)" ${response.hdr.status_ambil == 'pesan' ? '':'disabled' }><i class="fas fa-box"></i> Diproses</button>
                            <button type="button" class="btn btn-warning" onclick="ambil(${response.hdr.id},'ready',this)" ${response.hdr.status_ambil == 'ready' || response.hdr.status_ambil == 'finish' ? 'disabled':''}><i class="fas fa-tools"></i> Siap diambil</button>
                            <button type="button" class="btn btn-success" onclick="ambil(${response.hdr.id},'finish',this)" ${response.hdr.status_ambil == 'finish' ? 'disabled':'' }><i class="fas fa-truck"></i> Ambil&Bayar</button>
                        </th></tr>
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
                $('#txtperiod, #txtstatus').on('change',function(){
                    table.ajax.reload(null,false);
                });
            });
        </script>
    </x-slot>
</x-app-layout>