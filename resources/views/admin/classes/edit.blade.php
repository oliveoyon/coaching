@extends('layouts.admin')

@section('title', 'Edit Class')
@section('page-title', 'Edit Class')
@section('page-subtitle', 'Update academic class master data.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.classes.update', $class) }}">
                @method('PUT')
                @include('admin.classes._form', ['submitLabel' => 'Update Class'])
            </form>
        </div>
    </div>
@endsection
