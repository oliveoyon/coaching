@extends('layouts.admin')

@section('title', 'Add Expense')
@section('page-title', 'Add Expense')
@section('page-subtitle', 'Record common operating costs or teacher-specific support expenses.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.expenses.store') }}">
                @csrf

                @include('admin.expenses._form')

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
@endsection
