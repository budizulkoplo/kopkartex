<x-app-layout>
    <x-slot name="pagetitle">Laporan Penjualan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penjualan</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <input type="month" id="bulan" class="form-control form-control-sm d-inline-block w-auto"
                           value="{{ $bulan }}" onchange="reloadTable()" />
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbpenjualan" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                @foreach($units as $id => $nama)
                                    <th>{{ $nama }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- JS Custom --}}
    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            function reloadTable() {
                table.ajax.reload();
            }

            // fungsi format Rupiah
            function formatRupiah(angka) {
                if (angka == null) return '';
                return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
            }

            var table = $('#tbpenjualan').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 31,
                ajax: {
                    url: "{{ route('laporan.penjualan.data') }}",
                    type: "GET",
                    data: function(d) {
                        d.bulan = $('#bulan').val();
                    },
                    dataSrc: "data"
                },
                columns: [
                    { data: "tanggal" },
                    @foreach($units as $id => $nama)
                        { 
                            data: "{{ $nama }}", 
                            render: function(data,type,row){
                                return formatRupiah(data);
                            }
                        },
                    @endforeach
                ],
                dom:
                "<'row mb-2'<'col-md-6 d-flex align-items-center'B><'col-md-6 d-flex justify-content-end'f>>" +
                "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'i>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-md-6'i><'col-md-6 d-flex justify-content-end'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
