<x-app-layout>
    <x-slot name="pagetitle">Riwayat Transaksi Bengkel</x-slot>

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-semibold">Riwayat Transaksi Bengkel</h3>
                    <small class="text-muted">Filter berdasarkan periode transaksi</small>
                </div>

                <div class="col-sm-6 text-end">
                    <form method="GET" action="{{ route('bengkel.riwayat') }}"
                          class="row g-2 align-items-center justify-content-end">

                        <div class="col-auto">
                            <input type="date" name="tanggal_awal"
                                   class="form-control form-control-sm"
                                   value="{{ $tanggal_awal }}">
                        </div>

                        <div class="col-auto">
                            <input type="date" name="tanggal_akhir"
                                   class="form-control form-control-sm"
                                   value="{{ $tanggal_akhir }}">
                        </div>

                        <div class="col-auto">
                            <button type="submit"
                                    class="btn btn-sm btn-success">
                                <i class="bi bi-funnel-fill"></i> Filter
                            </button>
                        </div>

                        <div class="col-auto">
                            <a href="{{ route('bengkel.riwayat') }}"
                               class="btn btn-sm btn-secondary">
                                Reset
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <table id="tbRiwayat"
                        class="table table-bordered table-striped table-sm w-100"
                        style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>No Nota</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th class="text-end">Total</th>
                                <th>Metode Bayar</th>
                                <th>Status</th>
                                <th width="220">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('bengkel.cetak',$row->id) }}"
                                    target="_blank"
                                    class="fw-semibold text-decoration-none">
                                        {{ $row->nomor_invoice }}
                                    </a>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y H:i') }}
                                </td>
                                <td>{{ $row->customer ?? '-' }}</td>
                                <td class="text-end">
                                    {{ number_format($row->grandtotal,0,',','.') }}
                                </td>
                                <td>
                                    @if($row->metode_bayar == 'tunai')
                                        Tunai
                                    @elseif($row->metode_bayar == 'cicilan')
                                        Cicilan ({{ $row->tenor }}x)
                                    @else
                                        {{ ucfirst($row->metode_bayar) }}
                                    @endif
                                </td>
                                <td>
                                    @if($row->status == 'lunas')
                                        <span class="badge bg-success">Lunas</span>
                                    @elseif($row->status == 'hutang')
                                        <span class="badge bg-warning text-dark">Hutang</span>
                                    @elseif($row->status == 'canceled')
                                        <span class="badge bg-danger">Dibatalkan</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $row->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- Cetak -->
                                        <a href="{{ route('bengkel.cetak',$row->id) }}"
                                        target="_blank"
                                        class="btn btn-primary"
                                        title="Cetak Nota">
                                            <i class="bi bi-printer-fill"></i>
                                        </a>

                                        @if($row->status != 'canceled')
                                            <!-- Revisi -->
                                            <a href="{{ route('bengkel.revise', $row->id) }}"
                                            class="btn btn-warning"
                                            title="Revisi Transaksi">
                                            <i class="bi bi-pencil-fill"></i>
                                            </a>

                                            <!-- Cancel -->
                                            <button type="button"
                                                    class="btn btn-danger btn-cancel"
                                                    data-id="{{ $row->id }}"
                                                    data-nota="{{ $row->nomor_invoice }}">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            {{-- KOSONGKAN --}}
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form Cancel -->
    <form id="formCancel" method="POST">
        @csrf
    </form>

    <!-- CSS -->
    <x-slot name="csscustom">
        <style>
            .table td, .table th { vertical-align: middle; }
            .btn-group .btn { margin-right: 2px; }
            .dataTables_wrapper .dt-buttons { margin-bottom: 8px; }
        </style>
    </x-slot>

    <!-- JS -->
    <x-slot name="jscustom">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script> window.JSZip = JSZip; </script>

    <script>
        $(document).ready(function () {

            // DataTable
            $('#tbRiwayat').DataTable({
                responsive: true,
                pageLength: 50,
                ordering: false,
                dom:
                    "<'row mb-2'<'col-md-6'B><'col-md-6 text-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-md-6'i><'col-md-6 text-end'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm'
                    }
                ]
            });

            // Cancel transaksi
            $(document).on('click','.btn-cancel',function(){

                const id = $(this).data('id');
                const nota = $(this).data('nota');

                Swal.fire({
                    title: 'Batalkan Transaksi?',
                    html: `
                        Nota <b>${nota}</b><br>
                        Stok barang akan dikembalikan.<br>
                        Aksi ini tidak bisa dibatalkan.
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Batalkan'
                }).then((result)=>{
                    if(result.isConfirmed){

                        $('#formCancel')
                            .attr('action','/bengkel/'+id+'/cancel')
                            .submit();
                    }
                });

            });

        });
    </script>
    </x-slot>

</x-app-layout>