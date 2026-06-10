@forelse($transactions as $trx)
    <div class="border-bottom py-2">
        <div class="d-flex justify-content-between gap-2">
            <strong class="small">{{ $trx->nomor_transaksi }}</strong>
            <span class="badge bg-primary">{{ number_format((float) $trx->sejumlah, 0, ',', '.') }}</span>
        </div>
        <div class="small text-muted">{{ $trx->dibayar_kepada }} - {{ optional($trx->created_at)->format('d-m-Y H:i') }}</div>
        @foreach($trx->logs->take(2) as $log)
            <div class="small mt-1">
                <i class="bi bi-clock-history"></i>
                {{ $log->keterangan }}
                <span class="text-muted">oleh {{ $log->user->name ?? '-' }}</span>
            </div>
        @endforeach
    </div>
@empty
    <div class="text-muted small">Belum ada log transaksi.</div>
@endforelse
