@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="javascript:;" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle text-uppercase fw-bold flex-grow-1 text-center">
        {{ $toko->nama_unit }}
    </div>

    <div class="right d-flex justify-content-end align-items-center">
        <a href="{{ route('mobile.belanja.cart') }}" 
           class="headerButton cart-header-button">
            <ion-icon name="cart-outline" style="font-size:1.5rem; color:#fff;"></ion-icon>
            @if($cartCount > 0)
                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle"
                      style="font-size:0.7rem; min-width:18px;">
                    {{ $cartCount }}
                </span>
            @endif
        </a>
    </div>
</div>
@endsection

@section('content')
<style>
    .cart-header-button {
        position: relative;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .18);
        color: #fff;
    }

    .shop-page {
        margin-top: 40px;
        padding: 14px 14px 98px;
    }

    .shop-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px;
        border: 1px solid var(--mobile-line, #e4edf3);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(23, 33, 47, .06);
    }

    .shop-summary strong {
        display: block;
        color: var(--mobile-ink, #17212f);
        font-size: 1rem;
        line-height: 1.2;
    }

    .shop-summary span {
        color: var(--mobile-muted, #6f7d8f);
        font-size: .78rem;
    }

    .summary-pill {
        flex: 0 0 auto;
        padding: 7px 10px;
        border-radius: 999px;
        background: #eaf4ff;
        color: #0b4f9a;
        font-size: .76rem;
        font-weight: 800;
    }

    .product-search {
        position: sticky;
        top: 56px;
        z-index: 6;
        padding: 10px 0 12px;
        background: linear-gradient(180deg, var(--mobile-bg, #f5f8fb) 76%, rgba(245, 248, 251, 0));
    }

    .search-box {
        display: grid;
        grid-template-columns: 1fr 40px;
        gap: 8px;
        padding: 8px;
        border: 1px solid var(--mobile-line, #e4edf3);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 10px 22px rgba(23, 33, 47, .07);
    }

    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap ion-icon {
        position: absolute;
        top: 50%;
        left: 12px;
        color: #8a98a8;
        font-size: 20px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .search-box .form-control {
        min-height: 42px;
        padding-left: 40px;
        border: 0;
        background: #f6f9fb;
    }

    .search-clear {
        min-width: 40px;
        min-height: 42px;
        border-radius: 10px;
        border: 0;
        color: #6f7d8f;
        background: #eef4fb;
    }

    .search-clear.is-hidden {
        visibility: hidden;
    }

    .search-reset {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin: 8px 2px 0;
        color: #c53d47;
        font-size: .78rem;
        font-weight: 800;
        text-decoration: none;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .product-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--mobile-line, #e4edf3);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(23, 33, 47, .06);
        transition: transform .16s ease, opacity .16s ease;
    }

    .product-card.is-hidden {
        display: none;
    }

    .product-media {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1 / .86;
        background: #f7fafc;
    }

    .product-media img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 12px;
    }

    .product-fallback-icon {
        width: 64px;
        height: 64px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        color: #0f6fcf;
        background: #eaf4ff;
    }

    .product-fallback-icon ion-icon {
        font-size: 36px;
    }

    .stock-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 5px 8px;
        border-radius: 999px;
        color: #0b4f9a;
        background: rgba(234, 244, 255, .94);
        font-size: .7rem;
        font-weight: 850;
    }

    .product-body {
        display: flex;
        min-height: 154px;
        flex-direction: column;
        padding: 11px;
    }

    .product-code {
        margin-bottom: 4px;
        color: var(--mobile-muted, #6f7d8f);
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .product-name {
        min-height: 40px;
        margin: 0;
        color: var(--mobile-ink, #17212f);
        font-size: .88rem;
        font-weight: 850;
        line-height: 1.32;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-price {
        margin-top: 8px;
        color: #0b4f9a;
        font-size: .98rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .product-action {
        margin-top: auto;
        padding-top: 10px;
    }

    .product-action .btn {
        min-height: 38px;
        border-radius: 10px;
        font-weight: 850;
    }

    .empty-state {
        margin-top: 22px;
        padding: 26px 18px;
        border: 1px solid var(--mobile-line, #e4edf3);
        border-radius: 14px;
        background: #fff;
        color: var(--mobile-muted, #6f7d8f);
        text-align: center;
        box-shadow: 0 8px 22px rgba(23, 33, 47, .06);
    }

    .empty-state.is-hidden {
        display: none;
    }

    @media (min-width: 680px) {
        .shop-page {
            max-width: 760px;
            margin-left: auto;
            margin-right: auto;
        }

        .product-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 360px) {
        .shop-page {
            padding-left: 10px;
            padding-right: 10px;
        }

        .product-grid {
            gap: 9px;
        }

        .product-body {
            min-height: 150px;
            padding: 10px;
        }

        .product-name {
            font-size: .82rem;
        }
    }
</style>

<div class="shop-page">
    <div class="shop-summary">
        <div>
            <strong>Daftar Produk</strong>
            <span><span id="productVisibleCount">{{ $produkList->count() }}</span> produk ditemukan</span>
        </div>
        <div class="summary-pill">{{ $readyCount }} ready</div>
    </div>

    <div class="product-search" id="productSearchForm">
        <div class="search-box">
            <div class="search-input-wrap">
                <ion-icon name="search-outline"></ion-icon>
                <input type="search" name="q" value="{{ $search ?? request('q') }}"
                    class="form-control"
                    placeholder="Ketik nama produk"
                    autocomplete="off"
                    inputmode="search">
            </div>

            <button type="button" class="search-clear {{ request('q') ? '' : 'is-hidden' }}" id="clearProductSearch" aria-label="Bersihkan pencarian">
                <ion-icon name="close-outline"></ion-icon>
            </button>
        </div>

    </div>

    @if($produkList->isEmpty())
        <div class="empty-state">
            <ion-icon name="alert-circle-outline" class="mb-1" style="font-size:1.6rem;"></ion-icon><br>
            Produk tidak ditemukan
        </div>
    @else
        <div class="empty-state is-hidden" id="liveEmptyState">
            <ion-icon name="search-outline" class="mb-1" style="font-size:1.6rem;"></ion-icon><br>
            Produk tidak ditemukan
        </div>

        <div class="product-grid">
            @foreach($produkList as $p)
                <article class="product-card" data-product-name="{{ \Illuminate\Support\Str::lower($p->nama_barang) }}">
                    <div class="product-media">
                        @if(isset($p->img) && $p->img)
                            <img src="{{ asset('storage/produk/' . $p->img) }}" alt="{{ $p->nama_barang }}" loading="lazy">
                        @else
                            <div class="product-fallback-icon" aria-label="Produk tanpa foto">
                                <ion-icon name="bag-add-outline"></ion-icon>
                            </div>
                        @endif

                        <span class="stock-badge">
                            Stok {{ $p->stok }}
                        </span>
                    </div>

                    <div class="product-body">
                        <div class="product-code">{{ $p->kode_barang }}</div>
                        <h6 class="product-name">{{ $p->nama_barang }}</h6>
                        <div class="product-price">Rp {{ number_format($p->harga_jual,0,',','.') }}</div>

                        <form action="{{ route('mobile.belanja.cart.add') }}" method="POST" class="product-action">
                            @csrf
                            <input type="hidden" name="id" value="{{ $p->id }}">
                            <input type="hidden" name="nama" value="{{ $p->nama_barang }}">
                            <input type="hidden" name="harga" value="{{ $p->harga_jual }}">

                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <ion-icon name="cart-outline"></ion-icon> Tambah
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('productSearchForm');
    const input = form ? form.querySelector('input[name="q"]') : null;
    const clearButton = document.getElementById('clearProductSearch');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    const visibleCount = document.getElementById('productVisibleCount');
    const emptyState = document.getElementById('liveEmptyState');
    let urlTimer = null;

    function normalize(value) {
        return (value || '').toLowerCase().replace(/\s+/g, ' ').trim();
    }

    function syncUrl(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('q', query);
        } else {
            url.searchParams.delete('q');
        }
        window.history.replaceState({}, '', url.toString());
    }

    function filterProducts() {
        const query = normalize(input.value);
        let total = 0;

        cards.forEach(function (card) {
            const name = normalize(card.dataset.productName);
            const visible = !query || name.includes(query);
            card.classList.toggle('is-hidden', !visible);
            if (visible) total++;
        });

        if (visibleCount) visibleCount.textContent = total;
        if (emptyState) emptyState.classList.toggle('is-hidden', total > 0);
        if (clearButton) clearButton.classList.toggle('is-hidden', !query);

        clearTimeout(urlTimer);
        urlTimer = setTimeout(function () {
            syncUrl(query);
        }, 120);
    }

    if (form && form.tagName === 'FORM') {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            filterProducts();
        });
    }

    if (input) {
        input.addEventListener('input', filterProducts);
        filterProducts();
    }

    if (clearButton && input) {
        clearButton.addEventListener('click', function () {
            input.value = '';
            input.focus();
            filterProducts();
        });
    }
});
</script>
@endsection
