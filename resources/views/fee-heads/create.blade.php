@extends('layouts.app')

@section('title', 'Add Fee Head')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Foundation</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Add Fee Head</h1>
        <p class="text-secondary mb-0">Create a reusable fee category for later structures and overrides.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('fee-heads.store') }}">
            @csrf
            @include('fee-heads.partials.form', ['submitLabel' => 'Create Fee Head'])
        </form>
    </div>
@endsection
