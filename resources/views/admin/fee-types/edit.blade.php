@extends('layouts.admin')

@section('title', 'Edit Fee Type')
@section('page-title', 'Edit Fee Type')
@section('page-subtitle', 'Update the reusable fee head and frequency behavior.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.fee-types.update', $feeType) }}">
                @method('PUT')
                @include('admin.fee-types._form', ['submitLabel' => 'Update Fee Type'])
            </form>
        </div>
    </div>
@endsection
