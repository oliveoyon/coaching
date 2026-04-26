@extends('layouts.admin')

@section('title', 'Review Admission Request')
@section('page-title', 'Review Admission Request')
@section('page-subtitle', 'Verify the student information, check likely duplicates, and approve enrollment into the target batch.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card page-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h5 mb-0">Submitted Information</h2>
                        <span class="badge rounded-pill {{ $admissionRequest->status === 'pending' ? 'text-bg-warning' : ($admissionRequest->status === 'approved' ? 'text-bg-success' : 'text-bg-secondary') }}">
                            {{ ucfirst($admissionRequest->status) }}
                        </span>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            @if ($admissionRequest->photo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($admissionRequest->photo_path) }}" alt="{{ $admissionRequest->name }}" class="img-thumbnail w-100" style="max-height: 260px; object-fit: cover;">
                            @else
                                <div class="border rounded p-4 text-center text-muted">No photo uploaded</div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted small">Student Name</div>
                                    <div class="fw-semibold">{{ $admissionRequest->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">Student WhatsApp / Mobile</div>
                                    <div class="fw-semibold">{{ $admissionRequest->phone ?: '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">Guardian WhatsApp / Mobile</div>
                                    <div class="fw-semibold">{{ $admissionRequest->guardian_phone }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">School</div>
                                    <div class="fw-semibold">{{ $admissionRequest->school ?: '-' }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted small">Address</div>
                                    <div class="fw-semibold">{{ $admissionRequest->address ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Target Batch</div>
                            <div class="fw-semibold">{{ $admissionRequest->batch?->name }}</div>
                            <div class="small text-muted">
                                {{ $admissionRequest->batch?->academicClass?->name }}
                                @if ($admissionRequest->batch?->subject)
                                    | {{ $admissionRequest->batch?->subject?->name }}
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Submitted Via</div>
                            <div class="fw-semibold">{{ $admissionRequest->batchAdmissionLink?->title ?: 'General Admission Link' }}</div>
                            <div class="small text-muted">{{ $admissionRequest->created_at?->format('d M Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card page-card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Possible Existing Student Matches</h2>

                    @if ($possibleMatches->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
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
                        <div class="text-muted">No likely student matches found by phone, guardian phone, or name.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($admissionRequest->status === 'pending')
                <div class="card page-card mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Approve and Enroll</h2>

                        <form method="POST" action="{{ route('admin.admission-requests.approve', $admissionRequest) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label for="start_date" class="form-label">Enrollment Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="existing_student_id" class="form-label">Use Existing Student Instead</label>
                                <select name="existing_student_id" id="existing_student_id" class="form-select @error('existing_student_id') is-invalid @enderror">
                                    <option value="">Create new student from this request</option>
                                    @foreach ($possibleMatches as $match)
                                        <option value="{{ $match->id }}" @selected((string) old('existing_student_id') === (string) $match->id)>
                                            {{ $match->student_code }} - {{ $match->name }} ({{ $match->academicClass?->name }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Use this when the student already exists in your system and only needs enrollment.</div>
                                @error('existing_student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="approve_review_note" class="form-label">Admin Note</label>
                                <textarea name="review_note" id="approve_review_note" rows="3" class="form-control @error('review_note') is-invalid @enderror">{{ old('review_note') }}</textarea>
                                @error('review_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success w-100">Approve and Enroll</button>
                        </form>
                    </div>
                </div>

                <div class="card page-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Reject Request</h2>

                        <form method="POST" action="{{ route('admin.admission-requests.reject', $admissionRequest) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label for="reject_review_note" class="form-label">Rejection Note</label>
                                <textarea name="review_note" id="reject_review_note" rows="3" class="form-control @error('review_note') is-invalid @enderror">{{ old('review_note') }}</textarea>
                                @error('review_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-outline-danger w-100">Reject Request</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card page-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Review Outcome</h2>
                        <div class="mb-2"><span class="text-muted">Reviewed By:</span> {{ $admissionRequest->reviewer?->name ?: '-' }}</div>
                        <div class="mb-2"><span class="text-muted">Reviewed At:</span> {{ $admissionRequest->reviewed_at?->format('d M Y h:i A') ?: '-' }}</div>
                        <div class="mb-2"><span class="text-muted">Linked Student:</span>
                            @if ($admissionRequest->student)
                                {{ $admissionRequest->student->student_code }} - {{ $admissionRequest->student->name }}
                            @else
                                -
                            @endif
                        </div>
                        <div class="mt-3">
                            <div class="text-muted small">Review Note</div>
                            <div class="fw-semibold">{{ $admissionRequest->review_note ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
