@extends('layouts.admin')

@section('title', 'Create Class')
@section('page-title', 'Create Class')
@section('page-subtitle', 'Add a new academic class.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.classes.store') }}">
                @include('admin.classes._form', ['submitLabel' => 'Create Class'])
            </form>
        </div>
    </div>
@endsection
