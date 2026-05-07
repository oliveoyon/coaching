@extends('layouts.admin')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')
@section('page-subtitle', 'Update this expense.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">
                @csrf
                @method('PUT')

                @include('admin.expenses._form')
            </form>
        </div>
    </div>
@endsection
