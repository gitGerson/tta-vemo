<div class="card {{ $class }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title }}</h5>

        @isset($actions)
            <div>{{ $actions }}</div>
        @endisset
    </div>
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
