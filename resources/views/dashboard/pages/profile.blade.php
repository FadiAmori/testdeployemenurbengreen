<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar activePage="user-profile"></x-dashboard::navbars.sidebar>
    <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
        <!-- Navbar -->
        <x-dashboard::navbars.navs.auth titlePage='User Profile'></x-dashboard::navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid px-2 px-md-4">
            <div class="page-header min-height-300 border-radius-xl mt-4"
                style="background-image: url('https://images.unsplash.com/photo-1531512073830-ba890ca4eba2?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');">
                <span class="mask bg-gradient-primary opacity-6"></span>
            </div>
            <div class="card card-body admin-card mx-3 mx-md-4 mt-n6">
                <div class="row gx-4 mb-2">
                    <div class="col-auto">
                        <div class="avatar avatar-xl position-relative">
                            <img src="{{ auth()->user()->profile_photo }}" alt="profile_image"
                                class="w-100 border-radius-lg shadow-sm">
                        </div>
                    </div>
                    <div class="col-auto my-auto">
                        <div class="h-100">
                            <h5 class="mb-1">
                                {{ auth()->user()->prenom }} {{ auth()->user()->name }}
                            </h5>
                            <p class="mb-0 font-weight-normal text-sm">
                                CEO / Co-Founder
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
                        <div class="nav-wrapper position-relative end-0">
                            <ul class="nav nav-pills nav-fill p-1" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#app-tab"
                                        role="tab" aria-selected="true">
                                        <i class="material-icons text-lg position-relative">home</i>
                                        <span class="ms-1">App</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#messages-tab"
                                        role="tab" aria-selected="false">
                                        <i class="material-icons text-lg position-relative">email</i>
                                        <span class="ms-1">Messages</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#settings-tab"
                                        role="tab" aria-selected="false">
                                        <i class="material-icons text-lg position-relative">settings</i>
                                        <span class="ms-1">Settings</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card card-plain h-100">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-md-8 d-flex align-items-center">
                                <h6 class="mb-3">Profile Information</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        @if (session('status'))
                            <div class="row">
                                <div class="alert alert-success alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('status') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10"
                                        data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="app-tab" role="tabpanel">
                                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Email address</label>
                                            <input type="email" name="email" class="form-control border border-2 p-2"
                                                value="{{ old('email', auth()->user()->email) }}">
                                            @error('email')
                                                <p class="text-danger inputerror">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">First Name (Prénom)</label>
                                            <input type="text" name="prenom" class="form-control border border-2 p-2"
                                                value="{{ old('prenom', auth()->user()->prenom) }}">
                                            @error('prenom')
                                                <p class="text-danger inputerror">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" name="name" class="form-control border border-2 p-2"
                                                value="{{ old('name', auth()->user()->name) }}">
                                            @error('name')
                                                <p class="text-danger inputerror">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control border border-2 p-2"
                                                value="{{ old('phone', auth()->user()->phone) }}">
                                            @error('phone')
                                                <p class="text-danger inputerror">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" name="location" class="form-control border border-2 p-2"
                                                value="{{ old('location', auth()->user()->location) }}">
                                            @error('location')
                                                <p class="text-danger inputerror">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">Profile Photo</label>
                                            <input type="file" name="profile_photo" class="form-control border border-2 p-2"
                                                accept="image/*">
                                            @error('profile_photo')
                                                <p class="text-danger inputerror">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-12">
                                            <label for="floatingTextarea2">About</label>
                                            <textarea class="form-control border border-2 p-2"
                                                placeholder="Say something about yourself" id="floatingTextarea2" name="about"
                                                rows="4" cols="50">{{ old('about', auth()->user()->about) }}</textarea>
                                            @error('about')
                                                <p class="text-danger inputerror">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <button type="submit" class="btn bg-gradient-dark">Submit</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="messages-tab" role="tabpanel">
                                <p>Messages section (to be implemented).</p>
                            </div>
                            <div class="tab-pane fade" id="settings-tab" role="tabpanel">
                                <p>Settings section (to be implemented).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-dashboard::footers.auth></x-dashboard::footers.auth>
    </div>
    <x-dashboard::plugins></x-dashboard::plugins>
</x-dashboard::layout>