@extends('layouts.admin')

@section('title', 'Admission Links')
@section('page-title', 'Admission Links')
@section('page-subtitle', 'Share batch links')

@section('content')
    @php
        $dayLabels = [
            'sat' => 'Sat',
            'sun' => 'Sun',
            'mon' => 'Mon',
            'tue' => 'Tue',
            'wed' => 'Wed',
            'thu' => 'Thu',
            'fri' => 'Fri',
        ];
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-primary mb-2">Total Links</div>
                    <div class="h4 mb-0 text-dark">{{ $links->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-success mb-2">Open Links</div>
                    <div class="h4 mb-0 text-dark">{{ $links->getCollection()->filter(fn ($link) => $link->isOpen())->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                <div class="card-body p-4">
                    <div class="small fw-semibold text-warning mb-2">Pending Requests</div>
                    <div class="h4 mb-0 text-dark">{{ $links->getCollection()->sum('pending_requests_count') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.admission-links.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> New Link
                    </a>
                    <a href="{{ route('admin.admission-requests.index') }}" class="btn btn-outline-secondary">View Requests</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch</th>
                            <th>Teachers</th>
                            <th>Time</th>
                            <th>Link</th>
                            <th>Expires</th>
                            <th>Requests</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($links as $link)
                            @php
                                $schedulePreview = collect($link->batch?->schedule_entries ?? [])
                                    ->take(2)
                                    ->map(function ($entry) use ($dayLabels) {
                                        $dayLabel = $dayLabels[strtolower($entry['day'] ?? '')] ?? ucfirst((string) ($entry['day'] ?? ''));

                                        try {
                                            $start = \Carbon\Carbon::createFromFormat('H:i', $entry['start_time'])->format('h:i A');
                                            $end = \Carbon\Carbon::createFromFormat('H:i', $entry['end_time'])->format('h:i A');
                                        } catch (\Throwable $exception) {
                                            $start = $entry['start_time'] ?? '-';
                                            $end = $entry['end_time'] ?? '-';
                                        }

                                        return $dayLabel.' '.$start.' - '.$end;
                                    });
                                $publicLink = route('admission.apply', $link->token);
                            @endphp
                            <tr>
                                <td style="min-width: 210px;">
                                    <div class="fw-semibold">{{ $link->batch?->name ?: '-' }}</div>
                                    <div class="small text-muted">
                                        {{ $link->batch?->academicClass?->name ?: '-' }}
                                        @if ($link->batch?->subject)
                                            | {{ $link->batch->subject->name }}
                                        @endif
                                    </div>
                                    @if ($link->title)
                                        <div class="small text-muted mt-1">{{ $link->title }}</div>
                                    @endif
                                </td>
                                <td style="min-width: 180px;">
                                    @forelse ($link->batch?->teachers ?? collect() as $teacher)
                                        <span class="badge text-bg-light border me-1 mb-1">{{ $teacher->user?->name }}</span>
                                    @empty
                                        <span class="text-muted small">No teacher</span>
                                    @endforelse
                                </td>
                                <td style="min-width: 220px;">
                                    @if ($schedulePreview->isNotEmpty())
                                        @foreach ($schedulePreview as $line)
                                            <div class="small">{{ $line }}</div>
                                        @endforeach
                                        @if (count($link->batch?->schedule_entries ?? []) > 2)
                                            <div class="small text-muted">+{{ count($link->batch?->schedule_entries ?? []) - 2 }} more</div>
                                        @endif
                                    @else
                                        <span class="text-muted small">Not set</span>
                                    @endif
                                </td>
                                <td style="min-width: 260px;">
                                    <div class="small text-break">{{ $publicLink }}</div>
                                    <div class="mt-2 d-flex flex-wrap gap-2">
                                        <a href="{{ $publicLink }}" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-copy-text="{{ $publicLink }}"
                                        >
                                            Copy
                                        </button>
                                    </div>
                                </td>
                                <td style="min-width: 120px;">
                                    <div>{{ $link->expires_at?->format('d M Y') ?: 'No expiry' }}</div>
                                    <div class="small mt-1">
                                        <span class="badge rounded-pill {{ $link->isOpen() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $link->isOpen() ? 'Open' : 'Closed' }}
                                        </span>
                                    </div>
                                </td>
                                <td style="min-width: 100px;">
                                    <div class="fw-semibold">{{ $link->admission_requests_count }}</div>
                                    <div class="small text-muted">{{ $link->pending_requests_count }} pending</div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.admission-links.show', $link) }}" class="btn btn-sm btn-outline-dark">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No links found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($links->hasPages())
                <div class="mt-4">{{ $links->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-copy-text]').forEach((button) => {
            button.addEventListener('click', async () => {
                const text = button.getAttribute('data-copy-text') || '';

                try {
                    await navigator.clipboard.writeText(text);
                    const original = button.textContent;
                    button.textContent = 'Copied';
                    setTimeout(() => {
                        button.textContent = original;
                    }, 1200);
                } catch (error) {
                    button.textContent = 'Copy failed';
                    setTimeout(() => {
                        button.textContent = 'Copy';
                    }, 1200);
                }
            });
        });
    </script>
@endpush
