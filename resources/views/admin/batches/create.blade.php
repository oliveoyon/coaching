@extends('layouts.admin')

@section('title', 'Create Batch')
@section('page-title', 'Create Batch')
@section('page-subtitle', 'Add a new batch')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.batches.store') }}">
                @include('admin.batches._form', ['submitLabel' => 'Create Batch'])
            </form>
        </div>
    </div>
@endsection
