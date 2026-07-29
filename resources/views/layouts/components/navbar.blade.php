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
             title="Buka/tutup sidebar">
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
                     placeholder="Cari menu..." aria-label="Cari menu..." autocomplete="off" autofocus />

                 <!-- Search Results Dropdown -->
                 <div id="search-results" class="dropdown-menu search-dropdown d-none"
                     style="width: 360px; max-height: 420px; overflow-y: auto;">
                     <div class="dropdown-header d-flex justify-content-between align-items-center">
                         <small class="text-muted fw-semibold">Hasil Pencarian</small>
                         <small class="text-muted">
                             <i class="bx bx-info-circle me-1"></i>
                             <span class="d-none d-sm-inline">Gunakan ↑↓ untuk navigasi, Enter untuk memilih</span>
                         </small>
                     </div>
                     <div id="search-results-content">
                         <!-- Results will be populated here -->
                     </div>
                     <div id="no-results" class="dropdown-item-text text-center text-muted py-3 d-none">
                         <i class="bx bx-search-alt-2 me-2"></i>Tidak ada hasil
                         <div class="mt-1">
                             <small>Coba cari: pengguna, role, dasbor, log</small>
                         </div>
                     </div>
                     <div class="dropdown-divider my-2"></div>
                     <div class="dropdown-item-text">
                         <small class="text-muted">
                             <i class="bx bx-key me-1"></i>
                             Tekan <kbd class="bg-light border px-1 rounded">Ctrl+K</kbd> untuk fokus ke pencarian
                         </small>
                     </div>
                 </div>
             </div>
         </div>

         <!-- /Search -->

         <ul class="navbar-nav flex-row align-items-center ms-md-auto">
             <li class="nav-item me-3">
                 <button type="button" class="btn theme-toggle" id="theme-toggle" aria-label="Ubah mode gelap">
                     <i class="theme-toggle-icon bx bx-moon"></i>
                 </button>
             </li>

             <!-- Notifications -->
             <li class="nav-item me-3">
                 <button type="button"
                     class="nav-link notification-toggle position-relative p-0 border-0"
                     data-notifications-toggle aria-controls="notifications-offcanvas" aria-expanded="false">
                     <i class="icon-base bx bx-bell icon-md"></i>
                     @if ($navbarNotifications['count'] > 0)
                         <span
                             class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                             {{ $navbarNotifications['count'] > 9 ? '9+' : $navbarNotifications['count'] }}
                             <span class="visually-hidden">notifikasi</span>
                         </span>
                     @endif
                 </button>
             </li>
             <!--/ Notifications -->

             <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <button type="button" class="nav-link dropdown-toggle hide-arrow p-0 border-0 bg-transparent"
                    id="navbar-user-dropdown" aria-expanded="false">
                    <div class="avatar avatar-online">
                        <img src="data:image/svg+xml;base64,{{ base64_encode(Avatar::create(Auth::user()->name)->toSvg()) }}" alt
                            class="w-px-40 h-auto rounded-circle" />
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="navbar-user-menu"
                    aria-labelledby="navbar-user-dropdown">
                    @php
                        $currentUser = auth()->user();
                        $canViewProfile = $currentUser?->hasPermission('management.users.view') === true;
                        $canEditProfile = $currentUser?->hasPermission('management.users.edit') === true;
                    @endphp
                     <li>
                         @if ($canViewProfile)
                             <a class="dropdown-item" href="{{ route('users.show', $currentUser) }}">
                                 <div class="d-flex">
                                     <div class="flex-shrink-0 me-3">
                                         <div class="avatar avatar-online">
                                             <img src="data:image/svg+xml;base64,{{ base64_encode(Avatar::create(Auth::user()->name)->toSvg()) }}" alt
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
                         @else
                             <div class="dropdown-item">
                                 <div class="d-flex">
                                     <div class="flex-shrink-0 me-3">
                                         <div class="avatar avatar-online">
                                             <img src="data:image/svg+xml;base64,{{ base64_encode(Avatar::create(Auth::user()->name)->toSvg()) }}" alt
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
                             </div>
                         @endif
                     </li>
                     @if ($canViewProfile || $canEditProfile)
                         <li>
                             <div class="dropdown-divider my-1"></div>
                         </li>
                     @endif
                     @if ($canViewProfile)
                         <li>
                             <a class="dropdown-item" href="{{ route('users.show', $currentUser) }}">
                                 <i class="icon-base bx bx-user icon-md me-3"></i><span>Profil Saya</span>
                             </a>
                         </li>
                     @endif
                     <li>
                         <a class="dropdown-item" href="{{ route('account.telegram') }}">
                             <i class="icon-base bx bxl-telegram icon-md me-3"></i><span>Hubungkan Telegram</span>
                         </a>
                     </li>
                     <li>
                         <a class="dropdown-item" href="{{ route('logout') }}"
                             onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                             <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Keluar</span>
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
