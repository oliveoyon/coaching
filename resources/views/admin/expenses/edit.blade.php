@extends('layouts.admin')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')
@section('page-subtitle', 'Update this expense entry without touching unrelated finance records.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">
                @csrf
                @method('PUT')

                @include('admin.expenses._form')

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Expense</button>
                </div>
            </form>
        </div>
    </div>
@endsection
