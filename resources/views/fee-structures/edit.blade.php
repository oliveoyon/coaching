@extends('layouts.app')

@section('title', 'Edit Fee Structure')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Foundation</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Manage Fee Structure</h1>
        <p class="text-secondary mb-0">Update how a fee head applies across tenant, batch, or program scope.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('fee-structures.update', $feeStructure) }}">
            @csrf
            @method('PUT')
            @include('fee-structures.partials.form', ['submitLabel' => 'Update Fee Structure'])
        </form>
    </div>
@endsection
