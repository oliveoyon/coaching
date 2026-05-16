@extends('layouts.admin')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')
@section('page-subtitle', 'Update this expense.')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9 col-xxl-8">
            <div class="card page-card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 p-lg-5" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 42%, #f8fafc 100%);">
                        <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">
                            @csrf
                            @method('PUT')

                            @include('admin.expenses._form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
