@extends('layouts.admin')

@section('title', 'Create Fee Type')
@section('page-title', 'Create Fee Type')
@section('page-subtitle', 'Create reusable fee heads before assigning them to batches.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.fee-types.store') }}">
                @include('admin.fee-types._form', ['submitLabel' => 'Create Fee Type'])
            </form>
        </div>
    </div>
@endsection
