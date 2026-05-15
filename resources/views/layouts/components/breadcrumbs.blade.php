@if(!empty($breadcrumbs))
    @once
        <style>
            .app-breadcrumb-container + .container-p-y {
                padding-top: 0.35rem !important;
            }

            .app-breadcrumb-shell {
                background: rgba(255, 255, 255, 0.82);
                border: 1px solid rgba(67, 89, 113, 0.14);
                border-radius: 0.5rem;
                box-shadow: 0 0.125rem 0.5rem rgba(67, 89, 113, 0.08);
                padding: 0.85rem 1rem;
            }

            .app-breadcrumb-list {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem;
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .app-breadcrumb-item {
                align-items: center;
                color: #697a8d;
                display: inline-flex;
                font-size: 0.875rem;
                font-weight: 500;
            }

            .app-breadcrumb-link {
                align-items: center;
                color: #566a7f;
                display: inline-flex;
                gap: 0.35rem;
                text-decoration: none;
                transition: color 0.15s ease, background-color 0.15s ease;
            }

            .app-breadcrumb-link:hover {
                color: #2f9e44;
            }

            .app-breadcrumb-current {
                align-items: center;
                color: #2f9e44;
                display: inline-flex;
                gap: 0.35rem;
                font-weight: 600;
            }

            .app-breadcrumb-separator {
                color: #a1acb8;
                display: inline-flex;
                font-size: 0.95rem;
                font-weight: 600;
                line-height: 1;
                margin: 0 0.15rem;
            }

            .app-breadcrumb-home-icon {
                font-size: 1.25rem;
                line-height: 1;
            }

            .app-breadcrumb-home-separator {
                color: #a1acb8;
                display: inline-flex;
                font-size: 1rem;
                line-height: 1;
            }
        </style>
    @endonce

    <div class="container-xxl pt-4 app-breadcrumb-container">
        <div class="app-breadcrumb-shell d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <nav aria-label="breadcrumb">
                <ol class="app-breadcrumb-list">
                    @foreach($breadcrumbs as $breadcrumb)
                        @if(!$loop->first)
                            <li class="app-breadcrumb-separator" aria-hidden="true">
                                <i class="bx bx-chevron-right"></i>
                            </li>
                        @endif

                        @if($breadcrumb['active'])
                            <li class="app-breadcrumb-item" aria-current="page">
                                <span class="app-breadcrumb-current">
                                    @if($loop->first)
                                        <i class="bx bx-home-alt app-breadcrumb-home-icon"></i>
                                        <i class="bx bx-chevron-right app-breadcrumb-home-separator"></i>
                                    @endif
                                    {{ $breadcrumb['label'] }}
                                </span>
                            </li>
                        @elseif(!empty($breadcrumb['url']))
                            <li class="app-breadcrumb-item">
                                <a class="app-breadcrumb-link" href="{{ $breadcrumb['url'] }}">
                                    @if($loop->first)
                                        <i class="bx bx-home-alt app-breadcrumb-home-icon"></i>
                                        <i class="bx bx-chevron-right app-breadcrumb-home-separator"></i>
                                    @endif
                                    {{ $breadcrumb['label'] }}
                                </a>
                            </li>
                        @else
                            <li class="app-breadcrumb-item">
                                <span class="app-breadcrumb-link">
                                    @if($loop->first)
                                        <i class="bx bx-home-alt app-breadcrumb-home-icon"></i>
                                        <i class="bx bx-chevron-right app-breadcrumb-home-separator"></i>
                                    @endif
                                    {{ $breadcrumb['label'] }}
                                </span>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        </div>
    </div>
@endif
