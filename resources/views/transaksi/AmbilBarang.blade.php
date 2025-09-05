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
                    <h5 class="modal-title" id="exampleModalLabel"></h5> <i class="fa-regular fa-copy copy-btn" data-clipboard-target="#exampleModalLabel"></i>
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
        <style>
            .swal2-container input,
            .swal2-container select {
            pointer-events: auto !important;
            }
        </style>
    </x-slot>
    @push('scripts')
        <script>
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
                        { "data": "nomor_invoice","orderable": false, 
                            render: function(data, type, row, meta){
                                return `<span id="${data}">${data} <i class="fa-regular fa-copy copy-btn" data-clipboard-target="#${data}"></i>`;
                            }
                        },
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
            document.addEventListener("DOMContentLoaded", function() {
                
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
            function ambil(idjual,grandtotal,pstatus,obj){
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
                        dtl(idjual);
                    })
                    .catch(error => {
                        Swal.fire({title: "Error!",text: error,icon: "error"});
                        loader($('#exampleModal'),true);
                    });
                }else{
                    $('#exampleModal').modal('hide');
                    Swal.fire({
                        title: "Ambil&Bayar!!",
                        icon: "warning",
                        showCancelButton: true,
                        focusConfirm: false,     // jangan paksa fokus ke tombol
                        allowOutsideClick: false,
                        allowEnterKey: false,    // biar enter tidak langsung submit
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Bayar",
                        focusConfirm: false,
                        customClass: {
                            popup: 'swal2-width-auto'
                        },
                        html: `
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Total</span>
                                <input type="number" class="form-control" readonly value="${grandtotal}">
                            </div>
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Metode Bayar</span>
                                <select id="metodeBayar" class="form-control">
                                    <option value="tunai">Tunai</option>
                                    <option value="cicilan">Cicilan</option>
                                </select>
                            </div>
                            <div class="input-group input-group-sm mb-2" id="dibayarWrapper"> 
                                <span class="input-group-text label-fixed-width">Dibayar</span>
                                <input type="number" class="form-control" id="dibayar" value="0">
                            </div>
                            <div class="input-group input-group-sm mb-2" id="kembalianWrapper"> 
                                <span class="input-group-text label-fixed-width">Kembali</span>
                                <input type="number" class="form-control" id="kembalian" value="0" readonly>
                            </div>
                            <div id="cicilanWrapper" class="input-group input-group-sm mb-2" style="display:none;"> 
                                <span class="input-group-text label-fixed-width">Jumlah Cicilan</span>
                                <select id="cicilanOption" class="form-control">
                                    <option value="1">1x</option>
                                    <option value="2">2x</option>
                                    <option value="3">3x</option>
                                </select>
                            </div>
                        `,
                        willOpen: () => {
                        // Trik penting: override input utama SweetAlert2
                        Swal.getInput = () => {
                            return null; // biar Swal ga "lock" hanya 1 input
                        }
                        },
                        didOpen: () => {
                            // force enable input supaya bisa diketik
                            document.querySelectorAll('input, select').forEach(el => {
                                el.removeAttribute('disabled');
                                el.style.pointerEvents = 'auto';
                            });

                            // langsung fokus ke input dibayar
                            document.getElementById('dibayar').focus();

                            const metode = document.getElementById("metodeBayar");
                            const wrappercicilan = document.getElementById("cicilanWrapper");
                            const cicilan = document.getElementById("cicilanOption");
                            const wrapperdibayar = document.getElementById("dibayarWrapper");
                            const dibayar = document.getElementById("dibayar");
                            const wrapperkembalian = document.getElementById("kembalianWrapper");
                            const kembalian = document.getElementById("kembalian");

                            // parsing grandtotal biar bisa dihitung
                            const total = parseFloat(`${grandtotal}`);

                            // Hitung kembalian saat user input
                            dibayar.addEventListener("input", function() {
                                const bayar = parseFloat(dibayar.value) || 0;
                                let kembali = bayar - total;
                                if (kembali < 0) kembali = 0; // kalau kurang, tetap 0
                                kembalian.value = kembali;
                            });

                            metode.addEventListener("change", function() {
                                if (this.value === "cicilan") {
                                    wrappercicilan.style.display = "flex"; // tampilkan div wrapper
                                    wrapperdibayar.style.display = "none";
                                    wrapperkembalian.style.display = "none";
                                } else {
                                    wrappercicilan.style.display = "none";
                                    wrapperdibayar.style.display = "flex";
                                    wrapperkembalian.style.display = "flex";
                                }
                                dibayar.value = 0;
                                kembalian.value = 0;
                            });
                        },
                        preConfirm: () => {
                            const metode = document.getElementById("metodeBayar").value;
                            const cicilan = document.getElementById("cicilanOption").value;
                            const dibayar = document.getElementById("dibayar").value;
                            const kembalian = document.getElementById("kembalian").value;

                            if (!metode) {
                                Swal.showValidationMessage("Silakan pilih metode pembayaran!");
                            } else if (metode === "cicilan" && !cicilan) {
                                Swal.showValidationMessage("Silakan pilih jumlah cicilan!");
                            }

                            return { metode, cicilan, kembalian, dibayar };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log("Metode dipilih:", result.value.metode);
                            const metodedata = result.value.metode;
                            const cicilandata = result.value.cicilan;
                            const dibayardata = result.value.dibayar;
                            const kembaliandata = result.value.kembalian;
                            if (result.value.metode === "cicilan") {
                                console.log("Jumlah cicilan:", result.value.cicilan);
                                // aksi untuk cicilan
                            } else {
                                // aksi untuk tunai
                            }
                            $.ajax({
                                type: 'PUT',
                                url: "{{ route('ambil.AmbilBarang') }}",
                                data: {id: idjual,status: pstatus, metode:metodedata, jmlcicilan:cicilandata ,dibayar:dibayardata,kembalian:kembaliandata},
                                beforeSend: function(xhr) {loader($('#exampleModal'),true)},
                                success: function(response) {
                                    table.ajax.reload(null, false);
                                    $('#exampleModal').modal('hide');
                                    loader($('#exampleModal'), false);

                                    // Buka nota di tab baru
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: "Barang berhasil diambil dan nota siap dicetak",
                                        icon: "success",
                                        confirmButtonText: "Cetak Nota"
                                    }).then((result) => {
                                        if(result.isConfirmed){
                                            const url = `{{ url('/penjualan/nota') }}/${response.invoice}`;
                                            window.open(url, '_blank');
                                        }
                                    });
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
                                <td>${formatRupiah(value.harga)}</td>
                                <td>${formatRupiah(value.qty*value.harga)}</td>
                                <td><button type="button" class="btn btn-sm btn-danger" onclick="delitem(${value.id},${value.penjualan_id})" ${response.hdr.status_ambil == 'finish' ? 'style="display:none"':'' }><i class="fa-solid fa-trash"></i></button></td>
                                </tr>`;
                            cn++;
                            grand +=value.qty*value.harga;
                        });
                        $('#exampleModalLabel').text(response.hdr.nomor_invoice);
                        $('#tbdtl tbody').html(str);
                        $('#tbdtl tfoot').html(`
                        <tr><th colspan="5" class="text-end">SubTotal</th><th colspan="2">`+formatRupiah(response.hdr.subtotal)+`</th></tr>
                        <tr><th colspan="5" class="text-end">Diskon</th><th colspan="2">`+response.hdr.diskon+`%</th></tr>
                        <tr><th colspan="5" class="text-end">GrandTotal</th><th colspan="2">`+formatRupiah(response.hdr.grandtotal)+`</th></tr>
                        <tr><th colspan="7" class="text-end">
                            <button type="button" class="btn btn-warning" 
                                onclick="ambil(${response.hdr.id},${response.hdr.grandtotal},'proses',this)" 
                                ${response.hdr.status_ambil == 'pesan' ? '' : 'disabled'}>
                                <i class="fas fa-box"></i> Diproses
                            </button>

                            <button type="button" class="btn btn-warning" 
                                onclick="ambil(${response.hdr.id},${response.hdr.grandtotal},'ready',this)" 
                                ${response.hdr.status_ambil == 'proses' ? '' : 'disabled'}>
                                <i class="fas fa-tools"></i> Siap diambil
                            </button>

                            <button type="button" class="btn btn-success" 
                                onclick="ambil(${response.hdr.id},${response.hdr.grandtotal},'finish',this)" 
                                ${response.hdr.status_ambil == 'ready' ? '' : 'disabled'}>
                                <i class="fas fa-truck"></i> Ambil&Bayar
                            </button>
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
        </script>
    @endpush
</x-app-layout>