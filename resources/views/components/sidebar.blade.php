<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand text-center py-4">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('logo.png') }}" alt="Kopkartex" class="brand-image opacity-75 shadow" style="height: 45px;">
        </a>
        <div class="brand-text fw-light mt-2" style="font-size: 1.2rem;">KOPKARTEX</div>
    </div>

    <!-- Sidebar Wrapper -->
    <div class="sidebar-wrapper">
        <!-- Search -->
        <div class="px-3 pt-2">
            <input type="text" id="menuSearch" class="form-control form-control-sm" placeholder="Cari menu...">
        </div>

        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                @foreach (request()->menu as $item)
                    @php
                        $p1 = explode(';', $item->role);
                        $lanjut = 0;
                        if (!empty($item->children)) {
                            foreach ($item->children as $chl) {
                                $p2 = explode(';', $chl->role);
                                $inarray = array_intersect(auth()->user()->getRoleNames()->toArray(), $p2);
                                if ($inarray) $lanjut++;
                            }
                        }
                        if (array_intersect(auth()->user()->getRoleNames()->toArray(), $p1)) {
                            $lanjut++;
                        }
                    @endphp

                    @if ($lanjut > 0)
                        <li class="nav-item menu-item">
                            <a href="{{ $item->children ? '#' : (Route::has($item->link) ? route($item->link) : '') }}"
                               class="nav-link menu-link {{ request()->routeIs($item->link) ? 'active' : '' }}">
                                <i class="nav-icon {{ $item->icon }}"></i>
                                <p>
                                    {{ $item->name }}
                                    @if ($item->children)
                                        <i class="nav-arrow bi bi-chevron-right"></i>
                                    @endif
                                </p>
                            </a>

                            @if ($item->children)
                                <ul class="nav nav-treeview submenu ps-4">
                                    @foreach ($item->children as $chl)
                                        @php
                                            $p3 = explode(';', $chl->role);
                                            $inarray1 = array_intersect(auth()->user()->getRoleNames()->toArray(), $p3);
                                        @endphp

                                        @if ($inarray1)
                                            <li class="nav-item menu-item">
                                                @if ($chl->children)
                                                    <a href="#" class="nav-link menu-link">
                                                        <i class="nav-icon {{ $chl->icon }}"></i>
                                                        <p>{{ $chl->name }}</p>
                                                        <i class="nav-arrow bi bi-chevron-right"></i>
                                                    </a>
                                                    <ul class="nav nav-treeview submenu ps-5">
                                                        @foreach ($chl->children as $chl2)
                                                            @php
                                                                $p4 = explode(';', $chl2->role);
                                                                $inarray2 = array_intersect(auth()->user()->getRoleNames()->toArray(), $p4);
                                                            @endphp
                                                            @if ($inarray2 && Route::has($chl2->link))
                                                                <li class="nav-item menu-item">
                                                                    <a href="{{ route($chl2->link) }}"
                                                                       class="nav-link menu-link {{ request()->routeIs($chl2->link) ? 'active' : '' }}">
                                                                        <i class="nav-icon {{ $chl2->icon }}"></i>
                                                                        <p>{{ $chl2->name }}</p>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    @if (Route::has($chl->link))
                                                        <a href="{{ route($chl->link) }}"
                                                           class="nav-link menu-link {{ request()->routeIs($chl->link) ? 'active' : '' }}">
                                                            <i class="nav-icon {{ $chl->icon }}"></i>
                                                            <p>{{ $chl->name }}</p>
                                                        </a>
                                                    @endif
                                                @endif
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>
</aside>

<script>
    document.getElementById('menuSearch').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const allItems = document.querySelectorAll('.menu-item');

        allItems.forEach(item => item.style.display = 'none'); 

        allItems.forEach(item => {
            const link = item.querySelector('.menu-link');
            if (!link) return;
            const text = link.textContent.toLowerCase();

            if (text.includes(keyword)) {
                item.style.display = 'block';

                let parent = item.parentElement;
                while (parent && parent.classList.contains('submenu')) {
                    parent.style.display = 'block';
                    parent = parent.parentElement.closest('.menu-item');
                    if (parent) parent.style.display = 'block';
                }
            }
        });
    });
</script>

<style>
    .submenu {
        display: none;
    }

    .nav-item.menu-item .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
    }
    
    .label-fixed-width {
        min-width: 100px; /* Sesuaikan dengan panjang label terpanjang */
        text-align: right;
        justify-content: flex-end;
    }
    .tt-menu {
        width: 100%;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        z-index: 1000;
        max-height: 250px;
        overflow-y: auto;
    }
    .tt-suggestion {
        padding: 0.5rem 1rem;
        cursor: pointer;
    }
    .tt-suggestion:hover {
        background-color: #f8f9fa;
    }
</style>
