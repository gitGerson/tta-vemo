@extends('layouts.sneat')

@section('title')
    Daftar Tujuan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <x-datatable :table="$table" title="Daftar Tujuan"></x-datatable>
    </div>

    <div class="modal fade" id="destination-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Tujuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- AJAX-loaded detail fragment --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
    <style>
        .destination-map-preview {
            display: block;
            height: 320px;
            width: 100%;
            border: 1px solid var(--bs-border-color);
            border-radius: .375rem;
            overflow: hidden;
        }

        .destination-map-preview.leaflet-container {
            height: 320px;
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>
<script>
(function () {
    document.dispatchEvent(new Event('form:enhance'));

    const destinationMaps = new WeakMap();

    const refreshMapSize = (map, coordinates = null) => {
        [0, 100, 250, 500, 900].forEach((delay) => {
            setTimeout(() => {
                map.invalidateSize(false);

                if (coordinates) {
                    map.setView(coordinates, map.getZoom(), {
                        animate: false,
                    });
                }
            }, delay);
        });
    };

    const initDestinationMaps = (root = document) => {
        if (!window.L) {
            return;
        }

        root.querySelectorAll('.destination-map-preview').forEach((element) => {
            const latitude = Number(element.dataset.latitude);
            const longitude = Number(element.dataset.longitude);
            const coordinates = [latitude, longitude];
            const existingMap = destinationMaps.get(element);

            if (existingMap) {
                refreshMapSize(existingMap, coordinates);
                return;
            }

            if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                return;
            }

            if (element.offsetWidth === 0 || element.offsetHeight === 0) {
                return;
            }

            const map = L.map(element, {
                scrollWheelZoom: false,
            }).setView([latitude, longitude], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            L.marker([latitude, longitude])
                .addTo(map)
                .bindPopup(element.dataset.name || 'Tujuan');

            destinationMaps.set(element, map);
            refreshMapSize(map, coordinates);

            if ('ResizeObserver' in window) {
                new ResizeObserver(() => refreshMapSize(map, coordinates)).observe(element);
            }
        });
    };

    const scheduleDestinationMaps = (root = document) => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => initDestinationMaps(root));
        });
    };

    const detailModal = document.getElementById('destination-detail-modal');

    if (detailModal) {
        const body = detailModal.querySelector('.modal-body');

        if (body) {
            new MutationObserver(() => {
                if (detailModal.classList.contains('show')) {
                    scheduleDestinationMaps(body);
                }
            }).observe(body, {
                childList: true,
                subtree: true,
            });
        }

        detailModal.addEventListener('shown.bs.modal', () => scheduleDestinationMaps(detailModal));
    }
})();
</script>
@endpush
