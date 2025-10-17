<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar activePage="maintenance" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg" style="background-color: #000000; color: #ffffff;">
        <x-dashboard::navbars.navs.auth titlePage="Update Maintenance" />
        <div class="container-fluid py-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="color: #000000;">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="color: #000000;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @php $currentStep = request()->query('step', 0); @endphp

            <div class="row">
                <div class="col-12">
                    <div class="card" style="border: none; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.5); border-radius: 12px; overflow: hidden; background-color: #1a1a1a;">
                        <div class="card-header p-3" style="background-color: #2c2c2c;">
                            <h2 style="color: #FF6347; font-weight: 700;">Update Maintenance for {{ $maintenance->product->name }}</h2>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ (($currentStep + 1) / 3 * 100) }}%;" aria-valuenow="{{ (($currentStep + 1) / 3 * 100) }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                @foreach (['Maintenance Details', 'Media', 'Steps'] as $index => $label)
                                    <div class="text-center px-4 flex-fill {{ $currentStep == $index ? 'fw-bold text-warning' : 'text-muted' }}" style="font-size: 0.95rem;">
                                        {{ $label }} (Step: {{ $index + 1 }})
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-body p-4">
<form action="{{ route('maintenance.update', $maintenance->product_id) }}" method="POST" enctype="multipart/form-data" id="maintenance-form">
    @csrf
    @method('PATCH') <!-- Changed from PUT to PATCH to match the route -->
    <input type="hidden" name="current_step" value="{{ $currentStep }}" id="current-step">

    <!-- Step 1: Maintenance Details -->
    <div class="step {{ $currentStep == 0 ? '' : 'd-none' }}" id="step-0">
        <h4 style="color: #FF6347; font-weight: 600;">Maintenance Details</h4>
        <div class="mb-3">
            <label for="product_id" class="form-label" style="color: #cccccc;">Product</label>
            <input type="text" class="form-control bg-dark text-white" value="{{ $maintenance->product->name }}" disabled>
            <input type="hidden" name="product_id" value="{{ $maintenance->product_id }}">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label" style="color: #cccccc;">Description</label>
            <textarea class="form-control bg-dark text-white" id="description" name="description" rows="3">{{ old('description', $maintenance->description ?? '') }}</textarea>
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="material_id" class="form-label" style="color: #cccccc;">Material Product</label>
            <select class="form-control bg-dark text-white" id="material_id" name="material_id">
                <option value="">Select Material</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ old('material_id', $maintenance->material_id) == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                @endforeach
            </select>
            @error('material_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="optional_id" class="form-label" style="color: #cccccc;">Optional Product</label>
            <select class="form-control bg-dark text-white" id="optional_id" name="optional_id">
                <option value="">Select Optional</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ old('optional_id', $maintenance->optional_id) == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                @endforeach
            </select>
            @error('optional_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Step 2: Media -->
    <div class="step {{ $currentStep == 1 ? '' : 'd-none' }}" id="step-1">
        <h4 style="color: #FF6347; font-weight: 600;">Media</h4>
        <div class="mb-3">
            <label for="photo" class="form-label" style="color: #cccccc;">Photo (JPEG/PNG/JPG, max 2MB)</label>
            <input type="file" class="form-control bg-dark text-white" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg">
            @if ($maintenance->photo)
                <div class="mt-2">
                    <img src="{{ Storage::url($maintenance->photo) }}" alt="Current Photo" style="max-height: 200px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);" onerror="this.src='/placeholder-image.jpg';">
                </div>
            @endif
            @error('photo')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="video" class="form-label" style="color: #cccccc;">Video (MP4/WebM, max 10MB)</label>
            <input type="file" class="form-control bg-dark text-white" id="video" name="video" accept="video/mp4,video/webm">
            @if ($maintenance->video)
                <div class="mt-2">
                    <video src="{{ Storage::url($maintenance->video) }}" controls style="max-height: 200px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);" onerror="this.poster='/placeholder-video.jpg';"></video>
                </div>
            @endif
            @error('video')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Step 3: Steps -->
    <div class="step {{ $currentStep == 2 ? '' : 'd-none' }}" id="step-2">
        <h4 style="color: #FF6347; font-weight: 600;">Steps</h4>
        <div id="steps-container">
            @php
                $steps = old('steps', $maintenance->steps ?? [[]]);
            @endphp
            @foreach ($steps as $index => $step)
                <div class="step-item mb-3 p-3" style="background-color: #2c2c2c; border-radius: 8px;" data-index="{{ $index }}">
                    <div class="mb-2">
                        <label for="steps_{{ $index }}_title" class="form-label" style="color: #cccccc;">Step Title</label>
                        <input type="text" class="form-control bg-dark text-white" id="steps_{{ $index }}_title" name="steps[{{ $index }}][title]" value="{{ old('steps.' . $index . '.title', $step['title'] ?? '') }}" required>
                        @error('steps.' . $index . '.title')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label for="steps_{{ $index }}_description" class="form-label" style="color: #cccccc;">Description</label>
                        <textarea class="form-control bg-dark text-white" id="steps_{{ $index }}_description" name="steps[{{ $index }}][description]" rows="3" required>{{ old('steps.' . $index . '.description', $step['description'] ?? '') }}</textarea>
                        @error('steps.' . $index . '.description')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    @if ($index > 0)
                        <button type="button" class="btn btn-outline-danger btn-sm remove-step" style="color: #FF6347; border-color: #FF6347;">Remove Step</button>
                    @endif
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-warning mt-3" style="color: #FF6347; border-color: #FF6347;" id="add-step">Add Step</button>
    </div>

    <!-- Navigation Buttons -->
    <div class="d-flex gap-2 mt-4" id="navigation-buttons">
        @if ($currentStep > 0)
            <button type="button" class="btn btn-secondary" style="background-color: #4a4a4a; border-color: #4a4a4a;" id="prev-step">Previous</button>
        @endif
        @if ($currentStep < 2)
            <button type="button" class="btn btn-warning" style="background-color: #FF6347; border-color: #FF6347; color: #ffffff;" id="next-step">Next</button>
               @endif
                
            <button type="submit" class="btn btn-success" style="background-color: #28a745; border-color: #28a745;" id="update-maintenance">Update Maintenance</button>

        <a href="{{ route('maintenance.show', $maintenance->product_id) }}" class="btn btn-outline-light" style="color: #cccccc; border-color: #cccccc;">Cancel</a>
    </div>
</form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Include Select2 for multi-select (if needed in future) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2 (currently not used, but included for future compatibility)
            $('.select2').select2({
                placeholder: 'Select products',
                allowClear: true,
                theme: 'bootstrap-5',
                dropdownCssClass: 'bg-dark text-white'
            });

            // Step navigation
            const steps = $('.step');
            const currentStepInput = $('#current-step');
            let currentStep = parseInt(currentStepInput.val()) || 0;

            function updateStepVisibility() {
                steps.each(function (index) {
                    $(this).toggleClass('d-none', index !== currentStep);
                });
                currentStepInput.val(currentStep);
                $('.progress-bar').css('width', `${((currentStep + 1) / 3 * 100)}%`);
                $('#navigation-buttons').find('#next-step, #update-maintenance').hide();
                if (currentStep < 2) {
                    $('#next-step').show();
                } else {
                    $('#update-maintenance').show();
                }
            }

            $('#next-step').on('click', function () {
                let valid = true;
                if (currentStep === 0) {
                    const description = $('#description').val().trim();
                    if (!description) {
                        alert('Please fill in the description.');
                        valid = false;
                    }
                } else if (currentStep === 1) {
                    // No validation for media step if optional
                } else if (currentStep === 2) {
                    $('.step-item').each(function () {
                        const title = $(this).find('input[name*="[title]"]').val().trim();
                        const description = $(this).find('textarea[name*="[description]"]').val().trim();
                        if (!title || !description) {
                            alert('Please fill in all step titles and descriptions.');
                            valid = false;
                            return false;
                        }
                    });
                }
                if (valid) {
                    currentStep = Math.min(currentStep + 1, steps.length - 1);
                    updateStepVisibility();
                }
            });

            $('#prev-step').on('click', function () {
                currentStep = Math.max(currentStep - 1, 0);
                updateStepVisibility();
            });

            // Dynamic steps
            let stepIndex = {{ count(old('steps', $maintenance->steps ?? [[]])) }};
            $('#add-step').on('click', function () {
                const container = $('#steps-container');
                const newStep = $(`
                    <div class="step-item mb-3 p-3" style="background-color: #2c2c2c; border-radius: 8px;" data-index="${stepIndex}">
                        <div class="mb-2">
                            <label for="steps_${stepIndex}_title" class="form-label" style="color: #cccccc;">Step Title</label>
                            <input type="text" class="form-control bg-dark text-white" id="steps_${stepIndex}_title" name="steps[${stepIndex}][title]" required>
                        </div>
                        <div class="mb-2">
                            <label for="steps_${stepIndex}_description" class="form-label" style="color: #cccccc;">Description</label>
                            <textarea class="form-control bg-dark text-white" id="steps_${stepIndex}_description" name="steps[${stepIndex}][description]" rows="3" required></textarea>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-step" style="color: #FF6347; border-color: #FF6347;">Remove Step</button>
                    </div>
                `);
                container.append(newStep);
                stepIndex++;
            });

            $('#steps-container').on('click', '.remove-step', function () {
                if ($('.step-item').length <= 1) {
                    alert('At least one step is required.');
                    return;
                }
                $(this).closest('.step-item').remove();
            });

            // Initial visibility
            updateStepVisibility();
        });
    </script>
</x-dashboard::layout>