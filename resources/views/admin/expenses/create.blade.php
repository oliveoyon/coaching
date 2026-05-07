@extends('layouts.admin')

@section('title', 'Add Expense')
@section('page-title', 'Add Expense')
@section('page-subtitle', 'Add a new expense.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.expenses.store') }}">
                @csrf

                @include('admin.expenses._form')
            </form>
        </div>
    </div>
@endsection
