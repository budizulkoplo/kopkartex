<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penerimaan Barang</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <h4 style="text-align:center;">Laporan Penerimaan Barang</h4>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Invoice</th>
                <th>Supplier</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                @foreach($row->details as $detail)
                <tr>
                    <td>{{ $row->tgl_penerimaan }}</td>
                    <td>{{ $row->nomor_invoice }}</td>
                    <td>{{ $row->nama_supplier }}</td>
                    <td>{{ $detail->barang->kode_barang }}</td>
                    <td>{{ $detail->barang->nama_barang }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>{{ $row->note }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
