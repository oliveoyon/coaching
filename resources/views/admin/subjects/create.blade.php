@extends('layouts.admin')

@section('title', 'Create Subject')
@section('page-title', 'Create Subject')
@section('page-subtitle', 'Add a new academic subject.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.subjects.store') }}">
                @include('admin.subjects._form', ['submitLabel' => 'Create Subject'])
            </form>
        </div>
    </div>
@endsection
