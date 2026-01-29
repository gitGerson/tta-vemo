 @php
     $canCreateMaintenance = auth()->user()?->hasPermission('maintenances.create');
     $canApproveMaintenance = auth()->user()?->hasPermission('maintenances.approve');
     $serviceCount = 0;
     $approvalCount = 0;
     $serviceNotifications = collect();
     $approvalNotifications = collect();

     if ($canCreateMaintenance) {
         $serviceQuery = \App\Models\NotificationService::where('read', 0);
         $serviceCount = (clone $serviceQuery)->count();
         $serviceNotifications = $serviceQuery->orderByDesc('created_at')->limit(5)->get();
     }

     if ($canApproveMaintenance) {
         $approvalQuery = \App\Models\Maintenances::where('status', 'PENDING');
         $approvalCount = (clone $approvalQuery)->count();
         $approvalNotifications = $approvalQuery->orderByDesc('date_in')->limit(5)->get();
     }
 @endphp

 <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
     id="layout-navbar">
     <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
         <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
             <i class="icon-base bx bx-menu icon-md"></i>
         </a>
     </div>

     <!-- Desktop Sidebar Toggle -->
     <div class="sidebar-toggle-desktop navbar-nav align-items-xl-center me-4 me-xl-0 d-none d-xl-block">
         <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)" id="sidebar-toggle-btn"
             title="Toggle Sidebar">
             <i class="icon-base bx bx-menu icon-md"></i>
         </a>
     </div>

     <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
         <!-- Search -->
         <div class="navbar-nav align-items-center me-auto">
             <div class="nav-item d-flex align-items-center position-relative">
                 <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
                 <input type="text" id="menu-search"
                     class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none"
                     placeholder="Search menu... (Ctrl+K)" aria-label="Search menu..." autocomplete="off" />

                 <!-- Search Results Dropdown -->
                 <div id="search-results" class="dropdown-menu search-dropdown d-none"
                     style="width: 420px; max-height: 400px; overflow-y: auto;">
                     <div class="dropdown-header d-flex justify-content-between align-items-center">
                         <small class="text-muted fw-semibold">Search Results</small>
                         <small class="text-muted">
                             <i class="bx bx-info-circle me-1"></i>
                             <span class="d-none d-sm-inline">Use ↑↓ to navigate, Enter to select</span>
                         </small>
                     </div>
                     <div id="search-results-content">
                         <!-- Results will be populated here -->
                     </div>
                     <div id="no-results" class="dropdown-item-text text-center text-muted py-3 d-none">
                         <i class="bx bx-search-alt-2 me-2"></i>No results found
                         <div class="mt-1">
                             <small>Try searching for: users, roles, dashboard, logs</small>
                         </div>
                     </div>
                     <div class="dropdown-divider my-2"></div>
                     <div class="dropdown-item-text">
                         <small class="text-muted">
                             <i class="bx bx-key me-1"></i>
                             Press <kbd class="bg-light border px-1 rounded">Ctrl+K</kbd> to focus search
                         </small>
                     </div>
                 </div>
             </div>
         </div>

         <!-- /Search -->

         <ul class="navbar-nav flex-row align-items-center ms-md-auto">
             @if ($canCreateMaintenance)
                 <li class="nav-item dropdown me-2">
                     <a class="nav-link dropdown-toggle hide-arrow position-relative btn btn-icon rounded-circle bg-light"
                         href="javascript:void(0);"
                         data-bs-toggle="dropdown" aria-expanded="false" aria-label="Service notifications">
                         <i class="icon-base bx bx-wrench icon-md"></i>
                         @if ($serviceCount > 0)
                             <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                 {{ $serviceCount }}
                             </span>
                         @endif
                     </a>
                     <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 320px;">
                         <li class="dropdown-header d-flex align-items-center justify-content-between py-2">
                             <span class="fw-semibold">Butuh Perbaikan</span>
                             <span class="badge bg-info-subtle text-info">{{ $serviceCount }}</span>
                         </li>
                         <li>
                             <div class="dropdown-divider my-1"></div>
                         </li>
                         @forelse ($serviceNotifications as $item)
                             <li>
                                 <a class="dropdown-item d-flex align-items-start gap-2"
                                     href="{{ route('maintenances.create', ['vehicle' => $item->no_pol]) }}">
                                     <i class="bx bx-car text-primary mt-1"></i>
                                     <div>
                                         <div class="fw-semibold">{{ $item->no_pol }}</div>
                                         <small class="text-muted">KM {{ $item->from_km }} → {{ $item->to_km }}</small>
                                     </div>
                                 </a>
                             </li>
                         @empty
                             <li class="dropdown-item-text text-muted py-3 text-center">
                                 Tidak ada kendaraan yang butuh perbaikan.
                             </li>
                         @endforelse
                         <li>
                             <div class="dropdown-divider my-1"></div>
                         </li>
                         <li>
                             <a class="dropdown-item text-center" href="{{ route('home') }}">Lihat semua</a>
                         </li>
                     </ul>
                 </li>
             @endif

             @if ($canApproveMaintenance)
                 <li class="nav-item dropdown me-2">
                     <a class="nav-link dropdown-toggle hide-arrow position-relative btn btn-icon rounded-circle bg-light"
                         href="javascript:void(0);"
                         data-bs-toggle="dropdown" aria-expanded="false" aria-label="Approval notifications">
                         <i class="icon-base bx bx-check-shield icon-md"></i>
                         @if ($approvalCount > 0)
                             <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning notification-badge">
                                 {{ $approvalCount }}
                             </span>
                         @endif
                     </a>
                     <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 320px;">
                         <li class="dropdown-header d-flex align-items-center justify-content-between py-2">
                             <span class="fw-semibold">Butuh Persetujuan</span>
                             <span class="badge bg-warning-subtle text-warning">{{ $approvalCount }}</span>
                         </li>
                         <li>
                             <div class="dropdown-divider my-1"></div>
                         </li>
                         @forelse ($approvalNotifications as $item)
                             <li>
                                 <a class="dropdown-item d-flex align-items-start gap-2"
                                     href="{{ url('/maintenances/' . $item->code_mt) }}">
                                     <i class="bx bx-clipboard text-warning mt-1"></i>
                                     <div>
                                         <div class="fw-semibold">{{ $item->no_pol }}</div>
                                         <small class="text-muted">Kode: {{ $item->code_mt }}</small>
                                     </div>
                                 </a>
                             </li>
                         @empty
                             <li class="dropdown-item-text text-muted py-3 text-center">
                                 Tidak ada perbaikan menunggu approval.
                             </li>
                         @endforelse
                         <li>
                             <div class="dropdown-divider my-1"></div>
                         </li>
                         <li>
                             <a class="dropdown-item text-center" href="{{ route('home') }}">Lihat semua</a>
                         </li>
                     </ul>
                 </li>
             @endif

             <li class="nav-item me-3">
                 <button type="button" class="btn theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                     <i class="theme-toggle-icon bx bx-moon"></i>
                 </button>
             </li>

             <!-- User -->
             <li class="nav-item navbar-dropdown dropdown-user dropdown">
                 <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                     data-bs-toggle="dropdown">
                     <div class="avatar avatar-online">
                         <img src="{{ Avatar::create(Auth::user()->name) }}" alt
                             class="w-px-40 h-auto rounded-circle" />
                     </div>
                 </a>
                 <ul class="dropdown-menu dropdown-menu-end">
                     <li>
                         <a class="dropdown-item" href="#">
                             <div class="d-flex">
                                 <div class="flex-shrink-0 me-3">
                                     <div class="avatar avatar-online">
                                         <img src="{{ Avatar::create(Auth::user()->name) }}" alt
                                             class="w-px-40 h-auto rounded-circle" />
                                     </div>
                                 </div>
                                 <div class="flex-grow-1">
                                     <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                     <small class="text-body-secondary">
                                         @forelse (Auth::user()->roles as $role)
                                             <span class="badge bg-label-primary">{{ $role->display_name }}</span>
                                             <br>
                                         @empty
                                         @endforelse
                                     </small>
                                 </div>
                             </div>
                         </a>
                     </li>
                     <li>
                         <div class="dropdown-divider my-1"></div>
                     </li>
                     <li>
                         <a class="dropdown-item d-none" href="#">
                             <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span>
                         </a>
                     </li>
                     <li>
                         <a class="dropdown-item d-none" href="#">
                             <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                         </a>
                     </li>
                     <li>
                         <a class="dropdown-item d-none" href="#">
                             <span class="d-flex align-items-center align-middle">
                                 <i class="flex-shrink-0 icon-base bx bx-credit-card icon-md me-3"></i><span
                                     class="flex-grow-1 align-middle">Billing Plan</span>
                                 <span class="flex-shrink-0 badge rounded-pill bg-danger">4</span>
                             </span>
                         </a>
                     </li>
                     <li>
                         <div class="dropdown-divider my-1 d-none"></div>
                     </li>
                     <li>
                         <a class="dropdown-item" href="{{ route('logout') }}"
                             onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                             <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                         </a>
                         <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                             @csrf
                         </form>
                     </li>
                 </ul>
             </li>
             <!--/ User -->
         </ul>
     </div>
 </nav>
