@forelse($pesananTerbaru as $index => $p)
<tr>
    <td>{{ $index+1 }}</td>
    <td>{{ $p->nomor_invoice ?? '-' }}</td>
    <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }}</td>
    <td>{{ $p->customer ?? '-' }}</td>
    <td>{{ number_format($p->grandtotal,0,',','.') }}</td>
    <td>
        @php
            $badgeClass = match($p->status ?? null) {
                'lunas' => 'success',
                'pending' => 'warning',
                'batal', 'canceled' => 'danger',
                'hutang' => 'secondary',
                default => 'info'
            };
        @endphp
        <span class="badge bg-{{ $badgeClass }}">
            {{ ucfirst($p->status ?? '-') }}
        </span>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center">Belum ada transaksi</td>
</tr>
@endforelse
