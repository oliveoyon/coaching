@extends('layouts.app')

@section('title', 'Edit Fee Head')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Foundation</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Manage Fee Head</h1>
        <p class="text-secondary mb-0">Update the fee category that structures will use later.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('fee-heads.update', $feeHead) }}">
            @csrf
            @method('PUT')
            @include('fee-heads.partials.form', ['submitLabel' => 'Update Fee Head'])
        </form>
    </div>
@endsection
