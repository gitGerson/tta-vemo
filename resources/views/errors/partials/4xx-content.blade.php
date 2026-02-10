@php
    $currentCode = isset($exception) ? $exception->getStatusCode() : 400;
    $statusText = \Symfony\Component\HttpFoundation\Response::$statusTexts[$currentCode] ?? 'Client Error';
    $detail = isset($exception) && $exception->getMessage() ? $exception->getMessage() : $statusText;
@endphp

<div class="text-center">
    <div class="mb-3">
        <img src="{{ asset('error.svg') }}" alt="" class="img-fluid w-50">
        <br>
        <span class="badge bg-label-warning fs-5">{{ $currentCode }}</span>
    </div>
    <h4 class="mb-2">{{ $statusText }}</h4>
    <p class="text-muted mb-0">{{ $detail }}</p>
    <div class="mt-4">
        <a href="{{ url('/') }}" class="btn btn-primary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke awal
        </a>
    </div>
</div>
