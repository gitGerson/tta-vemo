@extends('layouts.auth')

@section('content')
    <!-- Parallax Background -->
    <div class="tea-parallax-bg">
        <div class="parallax-layer parallax-back">
            <div class="tea-leaves tea-leaf-1"></div>
            <div class="tea-leaves tea-leaf-2"></div>
            <div class="tea-leaves tea-leaf-3"></div>
        </div>
        <div class="parallax-layer parallax-mid">
            <div class="steam-effect steam-1"></div>
            <div class="steam-effect steam-2"></div>
            <div class="steam-effect steam-3"></div>
        </div>
    </div>

    <div class="row min-vh-100 g-0">
        <div class="col-md-8 col-12 d-flex align-items-center left-section p-3">
            <div class="w-100 p-5">

                <h1 class="tea-title mb-4">
                    {{-- APP_NAME from ENV  --}}
                    {{ config('app.name', 'Tong Tji Starter') }}
                </h1>

                <p class="tea-subtitle mb-4">
                    <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseFeature" role="button"
                        aria-expanded="false" aria-controls="collapseFeature">
                        Version Release Notes & Updates
                    </a>
                </p>
                <div class="collapse" id="collapseFeature">
                    <div class="tea-features">
                        <div class="feature-item" style="--delay: 0s">
                            <div class="feature-icon">📦</div>
                            <div class="feature-content">
                                <h5>Version 1.0.0 - Initial Release</h5>
                                <p>✅ LDAP Authentication Integration<br>
                                    ✅ User Management System<br>
                                    ✅ DataTables with Server-side Processing</p>
                            </div>
                        </div>

                        <!-- Collapsible Updates Section -->
                        <div class="feature-item collapsible-section" style="--delay: 0.1s">
                            <div class="feature-icon">🚀</div>
                            <div class="feature-content">
                                <h5>
                                    <button
                                        class="btn btn-link text-white p-0 text-start w-100 d-flex align-items-center justify-content-between collapsed"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#updatesCollapse"
                                        aria-expanded="false" aria-controls="updatesCollapse">
                                        <span>Updates</span>
                                        <i class="bx bx-chevron-down ms-2 collapse-icon"></i>
                                    </button>
                                </h5>
                                <div class="collapse" id="updatesCollapse">
                                    <p class="collapse-content">• Sneat Admin Template Integration<br>
                                        • Enhanced Login UI with Tea Theme<br>
                                        • Responsive Design Implementation<br>
                                        • Audit Trail Implementation<br>
                                        • Soft Delete Functionality</p>
                                </div>
                            </div>
                        </div>

                        <!-- Collapsible Fix Section -->
                        <div class="feature-item collapsible-section" style="--delay: 0.2s">
                            <div class="feature-icon">🔧</div>
                            <div class="feature-content">
                                <h5>
                                    <button
                                        class="btn btn-link text-white p-0 text-start w-100 d-flex align-items-center justify-content-between collapsed"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#fixCollapse"
                                        aria-expanded="false" aria-controls="fixCollapse">
                                        <span>Fix</span>
                                        <i class="bx bx-chevron-down ms-2 collapse-icon"></i>
                                    </button>
                                </h5>
                                <div class="collapse" id="fixCollapse">
                                    <p class="collapse-content">• Advanced User Roles & Permissions<br>
                                        • Export Functionality Enhancement<br>
                                        • Real-time Notifications<br>
                                        • Mobile Responsive Improvements<br>
                                        • Performance Optimizations</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-12 d-flex align-items-center right-section p-3">
            <div class="w-100 p-5">
                <!-- Logo -->
                <div class="app-brand justify-content-center mb-4 text-center">
                    <a href="#" class="app-brand-link d-inline-block">
                        <img src="{{ asset('appicon.png') }}" alt="Logo" class="img-fluid" style="width: 80px;">
                    </a>
                </div>

                <!-- /Logo -->

                <div class="text-center mb-4">
                    <h4 class="mb-2 text-dark">Welcome Back! 👋</h4>
                    <p class="mb-0 text-muted">Please sign-in with your LDAP account</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label text-dark">Username</label>
                        <input type="text" class="form-control flat-input-white @error('username') is-invalid @enderror"
                            id="username" name="username" placeholder="Enter your username" autofocus required />
                        @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="mb-3 form-password-toggle">
                        <label class="form-label text-dark" for="password">Password</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password"
                                class="form-control flat-input-white @error('password') is-invalid @enderror"
                                name="password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="password" />
                            <span class="input-group-text flat-input-addon-white cursor-pointer">
                                <i class="icon-base bx bx-hide text-muted"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }} />
                                <label class="form-check-label text-muted" for="remember"> Remember Me </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-flat-primary d-grid w-100" type="submit">
                            <span>Login</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .tea-parallax-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -1;
            background: linear-gradient(135deg, #2d5016 0%, #3a6b1c 25%, #4a7c2a 50%, #5a8d38 75%, #6a9e46 100%);
            overflow: hidden;
        }

        .parallax-layer {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .tea-leaves {
            position: absolute;
            width: 60px;
            height: 60px;
            background: rgba(139, 195, 74, 0.3);
            border-radius: 0 100% 0 100%;
            animation: float 6s ease-in-out infinite;
        }

        .tea-leaf-1 {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
            transform: rotate(45deg);
        }

        .tea-leaf-2 {
            top: 60%;
            left: 80%;
            animation-delay: 2s;
            transform: rotate(-30deg);
        }

        .tea-leaf-3 {
            top: 40%;
            left: 60%;
            animation-delay: 4s;
            transform: rotate(120deg);
        }

        .steam-effect {
            position: absolute;
            width: 4px;
            height: 100px;
            background: linear-gradient(to top, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0));
            border-radius: 2px;
            animation: steam 4s ease-in-out infinite;
        }

        .steam-1 {
            top: 30%;
            left: 20%;
            animation-delay: 0s;
        }

        .steam-2 {
            top: 50%;
            left: 25%;
            animation-delay: 1s;
        }

        .steam-3 {
            top: 35%;
            left: 30%;
            animation-delay: 2s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-20px) rotate(5deg);
            }

            66% {
                transform: translateY(-10px) rotate(-5deg);
            }
        }

        @keyframes steam {

            0%,
            100% {
                transform: translateY(0) scale(1) rotate(0deg);
                opacity: 0;
            }

            25% {
                opacity: 1;
            }

            50% {
                transform: translateY(-50px) scale(1.1) rotate(2deg);
                opacity: 0.8;
            }

            75% {
                opacity: 0.5;
            }
        }

        .tea-title {
            color: #fff;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: slideInLeft 0.8s ease-out;
        }

        .tea-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
            font-style: italic;
            animation: slideInLeft 0.8s ease-out 0.1s both;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
            animation: slideInLeft 0.8s ease-out calc(0.2s + var(--delay)) both;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-item:hover {
            transform: translateX(15px);
        }

        .feature-icon {
            font-size: 2rem;
            min-width: 50px;
            text-align: center;
        }

        .feature-content h5 {
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .feature-content p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0;
            line-height: 1.6;
        }

        .flat-input {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 0 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 0 !important;
            color: #fff !important;
            padding: 0.75rem 0 !important;
            transition: all 0.3s ease;
        }

        .flat-input:focus {
            background: rgba(255, 255, 255, 0.08) !important;
            border-bottom-color: #8bc34a !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .flat-input::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        .flat-input-addon {
            background: transparent !important;
            border: 0 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 0 !important;
            border-left: none !important;
            padding: 0.75rem 0 !important;
        }

        .btn-flat-primary {
            background: #8bc34a !important;
            border: 0 !important;
            color: white !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            padding: 1rem !important;
            border-radius: 0 !important;
            transition: all 0.3s ease !important;
            position: relative;
            overflow: hidden;
        }

        .btn-flat-primary:hover {
            background: #689f38 !important;
            transform: translateY(-2px);
        }

        .btn-flat-primary:active {
            transform: translateY(0);
        }

        .flat-input-white {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 0 !important;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
            border-radius: 0 !important;
            color: #212529 !important;
            padding: 0.75rem 0 !important;
            transition: all 0.3s ease;
        }

        .flat-input-white:focus {
            background: rgba(139, 195, 74, 0.05) !important;
            border-bottom-color: #8bc34a !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .flat-input-white::placeholder {
            color: rgba(0, 0, 0, 0.4) !important;
        }

        .flat-input-addon-white {
            background: transparent !important;
            border: 0 !important;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
            border-radius: 0 !important;
            border-left: none !important;
            padding: 0.75rem 0 !important;
        }

        .left-section {
            background: rgba(45, 80, 22, 0.4);
            backdrop-filter: blur(5px);
            position: relative;
        }

        .right-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: relative;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg,
                    rgba(139, 195, 74, 0.1) 0%,
                    rgba(104, 159, 56, 0.15) 50%,
                    rgba(76, 125, 42, 0.2) 100%);
            z-index: -1;
        }

        .right-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg,
                    rgba(248, 249, 250, 0.9) 0%,
                    rgba(255, 255, 255, 0.95) 50%,
                    rgba(241, 243, 245, 0.9) 100%);
            z-index: -1;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Collapsible Styles */
        .collapsible-section .btn-link {
            text-decoration: none !important;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .collapsible-section .btn-link:hover {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .collapsible-section .btn-link:focus {
            box-shadow: none !important;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }

        .collapsed .collapse-icon {
            transform: rotate(-90deg);
        }

        .collapse-content {
            margin-top: 0.5rem;
            padding-left: 0;
        }

        .collapsible-section .collapse.show {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }

            to {
                opacity: 1;
                max-height: 200px;
            }
        }

        @media (max-width: 768px) {
            .tea-title {
                font-size: 2rem;
            }

            .tea-info-section,
            .login-form-container {
                margin-bottom: 1rem;
            }

            /* Mobile: Default collapsed state */
            .collapsible-section .collapse {
                display: none !important;
            }

            .collapsible-section .collapse.show {
                display: block !important;
                animation: slideDown 0.3s ease-out;
            }

            .left-section {
                padding: 1rem !important;
            }

            .w-100.p-5 {
                padding: 1rem !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle responsive collapse behavior
            function handleResponsiveCollapse() {
                const isMobile = window.innerWidth <= 768;
                const updateCollapse = document.getElementById('updatesCollapse');
                const fixCollapse = document.getElementById('fixCollapse');
                const updateButton = document.querySelector('[data-bs-target="#updatesCollapse"]');
                const fixButton = document.querySelector('[data-bs-target="#fixCollapse"]');

                if (!isMobile) {
                    // On desktop: show both sections by default
                    if (updateCollapse && !updateCollapse.classList.contains('show')) {
                        updateCollapse.classList.add('show');
                        updateButton.classList.remove('collapsed');
                        updateButton.setAttribute('aria-expanded', 'true');
                    }
                    if (fixCollapse && !fixCollapse.classList.contains('show')) {
                        fixCollapse.classList.add('show');
                        fixButton.classList.remove('collapsed');
                        fixButton.setAttribute('aria-expanded', 'true');
                    }
                }
                // On mobile: keep them collapsed (default state)
            }

            // Initialize on page load
            handleResponsiveCollapse();

            // Handle window resize
            window.addEventListener('resize', function() {
                setTimeout(handleResponsiveCollapse, 100);
            });

            // Handle collapse icon rotation
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
                button.addEventListener('click', function() {
                    setTimeout(() => {
                        const target = document.querySelector(this.getAttribute(
                            'data-bs-target'));
                        if (target.classList.contains('show')) {
                            this.classList.remove('collapsed');
                            this.setAttribute('aria-expanded', 'true');
                        } else {
                            this.classList.add('collapsed');
                            this.setAttribute('aria-expanded', 'false');
                        }
                    }, 10);
                });
            });
        });
    </script>
@endsection
