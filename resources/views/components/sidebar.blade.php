<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img src="{{ asset('logo.png') }}" alt="Kopkartex" class="brand-image opacity-75 shadow">
            <span class="brand-text fw-light">KOPKARTEX</span>
        </a>
    </div>

    <!-- Sidebar Wrapper -->
    <div class="sidebar-wrapper">
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
                                if ($inarray) {
                                    $lanjut++;
                                }
                            }
                        }

                        if (array_intersect(auth()->user()->getRoleNames()->toArray(), $p1)) {
                            $lanjut++;
                        }
                    @endphp

                    @if ($lanjut > 0)
                        <li class="nav-item">
                            <a href="{{ $item->children ? '#' : (Route::has($item->link) ? route($item->link) : '') }}" class="nav-link {{ request()->routeIs($item->link) ? 'active' : '' }}">
                                <i class="nav-icon {{ $item->icon }}"></i>
                                <p>
                                    {{ $item->name }}
                                    @if ($item->children)
                                        <i class="nav-arrow bi bi-chevron-right"></i>
                                    @endif
                                </p>
                            </a>

                            @if ($item->children)
                                <ul class="nav nav-treeview ps-4">
                                    @foreach ($item->children as $chl)
                                        @php
                                            $p3 = explode(';', $chl->role);
                                            $inarray1 = array_intersect(auth()->user()->getRoleNames()->toArray(), $p3);
                                        @endphp

                                        @if ($inarray1)
                                            <li class="nav-item">
                                                @if ($chl->children)
                                                    <a href="#" class="nav-link">
                                                        <i class="nav-icon {{ $chl->icon }}"></i>
                                                        <p>{{ $chl->name }}</p>
                                                        <i class="nav-arrow bi bi-chevron-right"></i>
                                                    </a>
                                                    <ul class="nav nav-treeview ps-5">
                                                        @foreach ($chl->children as $chl2)
                                                            @php
                                                                $p4 = explode(';', $chl2->role);
                                                                $inarray2 = array_intersect(auth()->user()->getRoleNames()->toArray(), $p4);
                                                            @endphp

                                                            @if ($inarray2 && Route::has($chl2->link))
                                                                <li class="nav-item">
                                                                    <a href="{{ route($chl2->link) }}" class="nav-link {{ request()->routeIs($chl2->link) ? 'active' : '' }}">
                                                                        <i class="nav-icon {{ $chl2->icon }}"></i>
                                                                        <p>{{ $chl2->name }}</p>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    @if (Route::has($chl->link))
                                                        <a href="{{ route($chl->link) }}" class="nav-link {{ request()->routeIs($chl->link) ? 'active' : '' }}">
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
