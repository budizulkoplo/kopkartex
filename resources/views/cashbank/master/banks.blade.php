<x-app-layout>
    <x-slot name="pagetitle">Bank Cash Bank</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Bank</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-header py-2">
                    <strong>Data diambil dari COA dengan ATT4 KAS / BANK</strong>
                </div>
                <div class="card-body">
                    <table id="tableData" class="table table-sm table-striped" style="width:100%; font-size: small;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th>ATT4</th>
                                <th>Nama Kas/Bank</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            const table = $('#tableData').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: "{{ route('cashbank.banks.data') }}",
                columns: [
                    { data: 'id', visible: false },
                    { data: 'kode_akun' },
                    { data: 'nama_akun' },
                    { data: 'att4', render: data => data === 'BANK' ? '<span class="badge bg-primary">BANK</span>' : '<span class="badge bg-success">KAS</span>' },
                    { data: 'nama_bank' },
                    { data: 'is_active', render: data => data ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' },
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
