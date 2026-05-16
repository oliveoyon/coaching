@extends('layouts.admin')

@section('title', 'Edit Batch')
@section('page-title', 'Edit Batch')
@section('page-subtitle', 'Update batch configuration and assigned teachers.')

@section('content')
    <div class="batch-form-page">
        <div class="card page-card batch-form-wrap">
            <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.batches.update', $batch) }}">
                @method('PUT')
                @include('admin.batches._form', ['submitLabel' => 'Update Batch'])
            </form>
            </div>
        </div>
    </div>
@endsection
