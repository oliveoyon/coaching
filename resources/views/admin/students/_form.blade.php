@csrf

<div class="row g-4">
    <div class="col-12">
        <div class="border rounded-4 p-4 student-form-section">
            <div class="fw-semibold mb-3 student-form-section-title">Student Info</div>
            <div class="row g-3">
                @isset($student)
                    <div class="col-md-4">
                        <label class="form-label">Student Code</label>
                        <input type="text" class="form-control" value="{{ $student->student_code }}" disabled>
                    </div>
                @endisset

                <div class="col-md-6">
                    <label for="name" class="form-label">Student Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $student->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                        <option value="">Select</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((string) old('class_id', $student->class_id ?? '') === (string) $class->id)>
                                {{ $class->name }}@if ($class->status !== 'active') (Inactive) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" @selected(old('status', $student->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $student->status ?? 'active') === 'inactive')>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="school" class="form-label">School</label>
                    <input type="text" name="school" id="school" value="{{ old('school', $student->school ?? '') }}" class="form-control @error('school') is-invalid @enderror">
                    @error('school')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4 student-form-section">
            <div class="fw-semibold mb-3 student-form-section-title">Contact</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">Student WhatsApp / Mobile</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="Optional">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="guardian_phone" class="form-label">Guardian WhatsApp / Mobile</label>
                    <input type="text" name="guardian_phone" id="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone ?? '') }}" class="form-control @error('guardian_phone') is-invalid @enderror" required>
                    @error('guardian_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $student->address ?? '') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4 student-form-section">
            <div class="fw-semibold mb-3 student-form-section-title">Face Registration</div>
            <div class="row g-3 align-items-start">
                @isset($student)
                    <div class="col-md-4">
                        <label class="form-label d-block">Current Image</label>
                        @if ($student->photoUrl())
                            <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="img-thumbnail rounded-4" style="max-width: 160px;">
                        @else
                            <div class="text-muted">No photo</div>
                        @endif
                    </div>
                @endisset

                <div class="{{ isset($student) ? 'col-md-8' : 'col-12' }}">
                    <div class="row g-3 align-items-start">
                        <div class="col-lg-6">
                            <div class="border rounded-4 p-3 bg-light face-capture-panel">
                                <div class="ratio ratio-4x3 rounded-4 overflow-hidden bg-dark position-relative">
                                    <video id="faceVideo" class="d-none w-100 h-100 object-fit-cover" autoplay playsinline muted></video>
                                    <img id="facePreview" alt="Face preview" class="d-none w-100 h-100 object-fit-cover">
                                    <div id="facePlaceholder" class="position-absolute top-0 start-0 end-0 bottom-0 d-flex align-items-center justify-content-center text-center text-white p-3">
                                        <div>
                                            <div class="fw-semibold mb-2">Use camera</div>
                                            <div class="small text-white-50">Capture a clear face for attendance.</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="face_capture" id="faceCapture" value="{{ old('face_capture') }}">
                                @error('face_capture')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary" id="startCameraButton">Open Camera</button>
                                <button type="button" class="btn btn-outline-primary d-none" id="captureButton">Capture</button>
                                <button type="button" class="btn btn-outline-danger d-none" id="stopCameraButton">Stop Camera</button>
                                <button type="button" class="btn btn-outline-secondary d-none" id="retakeButton">Retake</button>
                            </div>

                            <div class="mt-3">
                                <label for="photo" class="form-label">Upload Image</label>
                                <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept=".jpg,.jpeg,.png">
                                @error('photo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="small text-muted mt-2">Camera or upload image will be used for face attendance.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

@push('scripts')
    <script>
        (() => {
            const startCameraButton = document.getElementById('startCameraButton');
            const captureButton = document.getElementById('captureButton');
            const stopCameraButton = document.getElementById('stopCameraButton');
            const retakeButton = document.getElementById('retakeButton');
            const faceVideo = document.getElementById('faceVideo');
            const facePreview = document.getElementById('facePreview');
            const facePlaceholder = document.getElementById('facePlaceholder');
            const faceCapture = document.getElementById('faceCapture');
            let mediaStream = null;

            const stopCamera = () => {
                if (mediaStream) {
                    mediaStream.getTracks().forEach((track) => track.stop());
                    mediaStream = null;
                }

                faceVideo?.classList.add('d-none');
                stopCameraButton?.classList.add('d-none');
            };

            const showPreview = (dataUrl) => {
                if (! faceCapture || ! facePreview || ! facePlaceholder || ! captureButton || ! retakeButton || ! startCameraButton || ! stopCameraButton) {
                    return;
                }

                faceCapture.value = dataUrl;
                facePreview.src = dataUrl;
                facePreview.classList.remove('d-none');
                facePlaceholder.classList.add('d-none');
                captureButton.classList.add('d-none');
                stopCameraButton.classList.add('d-none');
                retakeButton.classList.remove('d-none');
                startCameraButton.classList.add('d-none');
            };

            if (faceCapture?.value) {
                showPreview(faceCapture.value);
            }

            startCameraButton?.addEventListener('click', async () => {
                try {
                    mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                    faceVideo.srcObject = mediaStream;
                    faceVideo.classList.remove('d-none');
                    facePreview?.classList.add('d-none');
                    facePlaceholder?.classList.add('d-none');
                    captureButton?.classList.remove('d-none');
                    stopCameraButton?.classList.remove('d-none');
                    retakeButton?.classList.add('d-none');
                } catch (error) {
                    alert('Camera access failed. Please allow camera access or upload an image.');
                }
            });

            captureButton?.addEventListener('click', () => {
                if (! faceVideo || ! faceVideo.videoWidth || ! faceVideo.videoHeight) {
                    return;
                }

                const canvas = document.createElement('canvas');
                canvas.width = faceVideo.videoWidth;
                canvas.height = faceVideo.videoHeight;
                const context = canvas.getContext('2d');

                if (! context) {
                    return;
                }

                context.drawImage(faceVideo, 0, 0, canvas.width, canvas.height);
                showPreview(canvas.toDataURL('image/png'));
                stopCamera();
            });

            stopCameraButton?.addEventListener('click', () => {
                stopCamera();
                captureButton?.classList.add('d-none');
                startCameraButton?.classList.remove('d-none');
                facePlaceholder?.classList.remove('d-none');
            });

            retakeButton?.addEventListener('click', () => {
                if (! faceCapture || ! facePreview || ! facePlaceholder || ! retakeButton || ! startCameraButton || ! stopCameraButton) {
                    return;
                }

                faceCapture.value = '';
                facePreview.src = '';
                facePreview.classList.add('d-none');
                facePlaceholder.classList.remove('d-none');
                retakeButton.classList.add('d-none');
                stopCameraButton.classList.add('d-none');
                startCameraButton.classList.remove('d-none');
            });

            window.addEventListener('beforeunload', stopCamera);
        })();
    </script>
@endpush
