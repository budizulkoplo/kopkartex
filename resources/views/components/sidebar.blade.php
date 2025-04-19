<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark"> <!--begin::Sidebar Brand-->
    <div class="sidebar-brand"> <!--begin::Brand Link--> <a href="{{ route('dashboard') }}" class="brand-link"> <!--begin::Brand Image--> <img src="{{ asset('arisa.png') }}" alt="AdminLTE Logo" class="brand-image opacity-75 shadow"> <!--end::Brand Image--> <!--begin::Brand Text--> <span class="brand-text fw-light"></span> <!--end::Brand Text--> </a> <!--end::Brand Link--> </div> <!--end::Sidebar Brand--> <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2"> <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                @foreach (request()->menu as $item)
                    @php
                        $p1=explode(';', $item->role);
                        $lanjut=0;
                        if (!empty($item->children)){
                            foreach ($item->children as $key => $chl) {
                                $p2=explode(';', $chl->role);
                                $inarray=array_intersect(auth()->user()->getRoleNames()->toArray(),$p2);
                                if($inarray){
                                    $lanjut++;
                                }
                            }
                        }
                        if(array_intersect(auth()->user()->getRoleNames()->toArray(),$p1)){
                            $lanjut++;
                        }
                    @endphp
                    @if ($lanjut >0)
                    <li class="nav-item"> 
                        <a href="{{ $item->children?'#':route($item->link) }}" class="nav-link {{ request()->routeIs($item->link)?'active':'' }}"> 
                            <i class="nav-icon {{ $item->icon }}"></i>
                            <p>{{ $item->name }} 
                                @if ($item->children)
                                <i class="nav-arrow bi bi-chevron-right"></i>
                                @endif
                            </p>
                        </a> 
                        @if ($item->children)
                        <ul class="nav nav-treeview">
                            @foreach ($item->children as $chl)
                                @php
                                    $p3=explode(';', $chl->role);
                                    $inarray1=array_intersect(auth()->user()->getRoleNames()->toArray(),$p3);
                                @endphp
                                @if($inarray1)
                                <li class="nav-item"> 
                                    @if ($chl->children)
                                        <a href="#" class="nav-link"> 
                                        <i class="nav-icon {{ $chl->icon }}"></i>
                                        <p>{{ $chl->name }}</p> <i class="nav-arrow bi bi-chevron-right"></i>
                                        </a>
                                        <ul class="nav nav-treeview">
                                            @foreach ($chl->children as $chl2)
                                                @php
                                                    $p4=explode(';', $chl2->role);
                                                    $inarray2=array_intersect(auth()->user()->getRoleNames()->toArray(),$p4);
                                                @endphp
                                                @if($inarray2)
                                                <li class="nav-item">
                                                    <a href="{{ route($chl2->link) }}" class="nav-link {{ request()->routeIs($chl2->link)?'active':'' }}"> 
                                                    <i class="nav-icon {{ $chl2->icon }}"></i>
                                                    <p>{{ $chl2->name }}</p>
                                                    </a>
                                                </li>
                                                @endif
                                            @endforeach
                                        </ul> 
                                    @else
                                        <a href="{{ route($chl->link) }}" class="nav-link {{ request()->routeIs($chl->link)?'active':'' }}"> 
                                        <i class="nav-icon {{ $chl->icon }}"></i>
                                        <p>{{ $chl->name }}</p>
                                        </a>
                                    @endif
                                </li>
                                @endif
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endif
                @endforeach
            </ul> <!--end::Sidebar Menu-->
        </nav>
    </div> <!--end::Sidebar Wrapper-->
</aside> <!--end::Sidebar--> <!--begin::App Main-->
