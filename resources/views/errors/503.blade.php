@extends('layouts.sneat-error')

@section('title')
    Error Server
@endsection

@section('content')
    @include('errors.partials.5xx-content')
@endsection
