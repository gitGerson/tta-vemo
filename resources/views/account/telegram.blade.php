@extends('layouts.sneat')

@section('title')
    Connect Telegram
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="icon-base bx bxl-telegram icon-md me-2 text-info"></i>
                        <h5 class="mb-0">Connect Telegram</h5>
                    </div>
                    <div class="card-body">
                        @if ($user->isTelegramLinked())
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-label-success me-2"><i class="bx bx-check"></i> Tertaut</span>
                                <span class="text-body-secondary">
                                    Sejak {{ $user->telegram_linked_at?->format('d M Y H:i') }}
                                </span>
                            </div>
                            <p class="text-body-secondary">
                                Notifikasi pengingat dikirim ke Telegram Anda. Untuk berhenti, putuskan tautan di bawah ini.
                            </p>
                            <form action="{{ route('account.telegram.unlink') }}" method="POST"
                                onsubmit="return confirm('Putuskan tautan Telegram?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bx bx-unlink me-1"></i> Putuskan Tautan
                                </button>
                            </form>
                        @elseif ($deepLink)
                            <p class="text-body-secondary">
                                Klik tombol di bawah untuk membuka bot Telegram, lalu tekan <strong>Start</strong>.
                                Akun Anda akan otomatis tertaut dan mulai menerima notifikasi pengingat.
                            </p>
                            <a href="{{ $deepLink }}" target="_blank" rel="noopener" class="btn btn-info">
                                <i class="icon-base bx bxl-telegram me-1"></i> Hubungkan Telegram
                            </a>
                        @else
                            <div class="alert alert-warning mb-0" role="alert">
                                Integrasi Telegram belum dikonfigurasi (TELEGRAM_BOT_USERNAME belum diatur).
                                Hubungi administrator.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
