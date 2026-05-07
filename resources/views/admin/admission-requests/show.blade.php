@extends('layouts.admin')

@section('title', 'Admission Request')
@section('page-title', 'Admission Request')
@section('page-subtitle', 'Review and approve')

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card page-card h-100">
                <div class="card-body p-4 text-center">
                    @if ($admissionRequest->photo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($admissionRequest->photo_path) }}" alt="{{ $admissionRequest->name }}" class="rounded-circle border shadow-sm mb-3" style="width: 132px; height: 132px; object-fit: cover;">
                    @else
                        <div class="rounded-circle border bg-light d-inline-flex align-items-center justify-content-center text-muted mb-3" style="width: 132px; height: 132px;">
                            No Photo
                        </div>
                    @endif

                    <h2 class="h4 mb-1">{{ $admissionRequest->name }}</h2>
                    <div class="text-muted mb-3">{{ $admissionRequest->batch?->name ?: '-' }}</div>

                    <span class="badge rounded-pill {{ $admissionRequest->status === 'pending' ? 'text-bg-warning' : ($admissionRequest->status === 'approved' ? 'text-bg-success' : 'text-bg-secondary') }}">
                        {{ ucfirst($admissionRequest->status) }}
                    </span>

                    <div class="text-start mt-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="text-muted small">Student Mobile</div>
                                <div class="fw-semibold">{{ $admissionRequest->phone ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Guardian Mobile</div>
                                <div class="fw-semibold">{{ $admissionRequest->guardian_phone ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">School</div>
                                <div class="fw-semibold">{{ $admissionRequest->school ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Address</div>
                                <div class="fw-semibold">{{ $admissionRequest->address ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Submitted</div>
                                <div class="fw-semibold">{{ $admissionRequest->created_at?->format('d M Y h:i A') ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Link</div>
                                <div class="fw-semibold">{{ $admissionRequest->batchAdmissionLink?->title ?: 'General Admission Link' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card page-card mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-muted small mb-2">Batch</div>
                                <div class="fw-semibold">{{ $admissionRequest->batch?->name ?: '-' }}</div>
                                <div class="small text-muted">
                                    {{ $admissionRequest->batch?->academicClass?->name ?: '-' }}
                                    @if ($admissionRequest->batch?->subject)
                                        | {{ $admissionRequest->batch->subject->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-muted small mb-2">Teachers</div>
                                <div class="fw-semibold">
                                    {{ $admissionRequest->batch?->teachers?->pluck('user.name')->filter()->implode(', ') ?: '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card page-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Possible Matches</h2>
                        <span class="small text-muted">{{ $possibleMatches->count() }}</span>
                    </div>

                    @if ($possibleMatches->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Phone</th>
                                        <th>Guardian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($possibleMatches as $match)
                                        <tr>
                                            <td>{{ $match->student_code }}</td>
                                            <td>{{ $match->name }}</td>
                                            <td>{{ $match->academicClass?->name }}</td>
                                            <td>{{ $match->phone ?: '-' }}</td>
                                            <td>{{ $match->guardian_phone }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">No matching student found.</div>
                    @endif
                </div>
            </div>

            @if ($admissionRequest->status === 'pending')
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card page-card h-100">
                            <div class="card-body p-4">
                                <h2 class="h5 mb-3">Approve</h2>

                                <form method="POST" action="{{ route('admin.admission-requests.approve', $admissionRequest) }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="existing_student_id" class="form-label">Use Existing Student</label>
                                        <select name="existing_student_id" id="existing_student_id" class="form-select @error('existing_student_id') is-invalid @enderror">
                                            <option value="">Create new student</option>
                                            @foreach ($possibleMatches as $match)
                                                <option value="{{ $match->id }}" @selected((string) old('existing_student_id') === (string) $match->id)>
                                                    {{ $match->student_code }} - {{ $match->name }} ({{ $match->academicClass?->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('existing_student_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="approve_review_note" class="form-label">Note</label>
                                        <textarea name="review_note" id="approve_review_note" rows="3" class="form-control @error('review_note') is-invalid @enderror">{{ old('review_note') }}</textarea>
                                        @error('review_note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-success w-100">Approve and Enroll</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card page-card h-100">
                            <div class="card-body p-4">
                                <h2 class="h5 mb-3">Reject</h2>

                                <form method="POST" action="{{ route('admin.admission-requests.reject', $admissionRequest) }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="mb-4">
                                        <label for="reject_review_note" class="form-label">Note</label>
                                        <textarea name="review_note" id="reject_review_note" rows="4" class="form-control @error('review_note') is-invalid @enderror">{{ old('review_note') }}</textarea>
                                        @error('review_note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-outline-danger w-100">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card page-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Review Result</h2>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small">Reviewed By</div>
                                <div class="fw-semibold">{{ $admissionRequest->reviewer?->name ?: '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Reviewed At</div>
                                <div class="fw-semibold">{{ $admissionRequest->reviewed_at?->format('d M Y h:i A') ?: '-' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Linked Student</div>
                                <div class="fw-semibold">
                                    @if ($admissionRequest->student)
                                        {{ $admissionRequest->student->student_code }} - {{ $admissionRequest->student->name }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Note</div>
                                <div class="fw-semibold">{{ $admissionRequest->review_note ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
