<x-app-layout>
    <x-slot name="pagetitle">Laporan Stok</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Stok Barang</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <!-- <button class="btn btn-sm btn-success" onclick="table.ajax.reload()">
                        <i class="bi bi-arrow-repeat"></i> Refresh
                    </button> -->
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbstok" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
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
        {{-- jQuery --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

        {{-- DataTables Core + Buttons + Responsive (tanpa export) --}}
        <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.3/b-3.0.1/r-3.0.1/datatables.min.css"/>
        <script src="https://cdn.datatables.net/v/bs5/dt-2.0.3/b-3.0.1/r-3.0.1/datatables.min.js"></script>

        {{-- Tambahan khusus export --}}
        <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>

        {{-- JSZip untuk Excel --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            var table = $('#tbstok').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 25,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "All"]],
                ajax: {
                    url: "{{ route('laporan.stokbarang.data') }}",
                    type: "GET",
                    dataSrc: "data"
                },
                columns: [
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    @foreach($units as $id => $nama)
                        { data: "{{ $nama }}" },
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
