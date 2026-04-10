@extends('layouts.app')

@section('title', 'Add Fee Structure')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Foundation</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Add Fee Structure</h1>
        <p class="text-secondary mb-0">Create a pricing rule that can later be resolved by tenant billing policy.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('fee-structures.store') }}">
            @csrf
            @include('fee-structures.partials.form', ['submitLabel' => 'Create Fee Structure'])
        </form>
    </div>
@endsection
