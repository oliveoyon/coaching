@extends('layouts.admin')

@section('title', 'Edit Subject')
@section('page-title', 'Edit Subject')
@section('page-subtitle', 'Update academic subject master data.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
                @method('PUT')
                @include('admin.subjects._form', ['submitLabel' => 'Update Subject'])
            </form>
        </div>
    </div>
@endsection
