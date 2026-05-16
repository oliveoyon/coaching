@extends('layouts.admin')

@section('title', 'Create Fee Type')
@section('page-title', 'Create Fee Type')
@section('page-subtitle', 'Add a new fee type')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9 col-xxl-8">
            <div class="card page-card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 p-lg-5" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 42%, #f8fafc 100%);">
                        <form method="POST" action="{{ route('admin.fee-types.store') }}">
                            @include('admin.fee-types._form', ['submitLabel' => 'Create Fee Type'])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
