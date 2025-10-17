<x-dashboard::layout bodyClass="bg-gray-200">
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">
                <!-- Navbar -->
                <x-dashboard::navbars.navs.guest signin='login' signup='register'></x-dashboard::navbars.navs.guest>
                <!-- End Navbar -->
            </div>
        </div>
    </div>
    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100"
            style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80');">
            <span class="mask bg-gradient-dark opacity-6"></span>
            <div class="container my-auto">
                <div class="row">
                    <div class="col-lg-4 col-md-8 col-12 mx-auto">
                        <div class="card z-index-0 fadeIn3 fadeInBottom">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Sign in</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                @if (Session::has('status'))
                                    <div class="alert alert-success alert-dismissible text-white" role="alert">
                                        <span class="text-sm">{{ Session::get('status') }}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10"
                                            data-bs-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @elseif (Session::has('email'))
                                    <div class="alert alert-danger alert-dismissible text-white" role="alert">
                                        <span class="text-sm">{{ Session::get('email') }}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10"
                                            data-bs-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                @if ($errors->has('google'))
                                    <div class="alert alert-danger alert-dismissible text-white" role="alert">
                                        <span class="text-sm">{{ $errors->first('google') }}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                @if (Session::has('demo'))
                                    <div class="row">
                                        <div class="alert alert-danger alert-dismissible text-white" role="alert">
                                            <span class="text-sm">{{ Session::get('demo') }}</span>
                                            <button type="button" class="btn-close text-lg py-3 opacity-10"
                                                data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                <form role="form" method="POST" action="{{ route('login') }}" class="text-start">
                                    @csrf
                                    <div class="input-group input-group-outline my-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>
                                    @error('email')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror
                                    <div class="input-group input-group-outline my-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password">
                                    </div>
                                    @error('password')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror
                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Sign in</button>
                                    </div>
                                    <div class="d-flex align-items-center my-3">
                                        <hr class="flex-grow-1">
                                        <span class="mx-2 text-secondary text-xs">or</span>
                                        <hr class="flex-grow-1">
                                    </div>
                                    <div class="text-center">
                                        <a href="{{ route('oauth.google.redirect') }}" class="btn btn-outline-dark w-100 mb-3" style="display:flex;align-items:center;justify-content:center;gap:8px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 533.5 544.3" width="18" height="18" aria-hidden="true">
                                                <path fill="#4285F4" d="M533.5 278.4c0-17.4-1.6-34-4.7-50.1H272.1v95h146.9c-6.3 34-25.1 62.7-53.6 81.9v68.1h86.8c50.7-46.7 81.3-115.6 81.3-194.9z"/>
                                                <path fill="#34A853" d="M272.1 544.3c72.7 0 133.8-24.1 178.5-65.9l-86.8-68.1c-24.1 16.2-55 25.7-91.7 25.7-70.5 0-130.3-47.6-151.7-111.6H31.6v70.1c44.4 88.2 135.8 149.8 240.5 149.8z"/>
                                                <path fill="#FBBC04" d="M120.4 324.4c-10.1-30-10.1-62.6 0-92.6V161.7H31.6c-41.5 82.8-41.5 179.9 0 262.7l88.8-70z"/>
                                                <path fill="#EA4335" d="M272.1 107.7c39.6-.6 77.7 14 106.5 41.2l79.4-79.4C414.8 24.5 350.3-.1 272.1 0 167.4 0 76 61.6 31.6 149.8l88.8 70c21.4-64 81.2-112.1 151.7-112.1z"/>
                                            </svg>
                                            <span>Sign in with Google</span>
                                        </a>
                                    </div>
                                    <p class="mt-4 text-sm text-center">
                                        Don't have an account?
                                        <a href="{{ route('register') }}" class="text-primary text-gradient font-weight-bold">Sign up</a>
                                    </p>
                                    <p class="mt-2 text-sm text-center">
                                        Forgot your password?
                                        <a href="{{ route('verify') }}" class="text-primary text-gradient font-weight-bold">Reset it</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-dashboard::footers.guest></x-dashboard::footers.guest>
        </div>
    </main>
    @push('js')
    <script src="{{ asset('assets') }}/js/jquery.min.js"></script>
    <script>
        $(function() {
            var text_val = $(".input-group input").val();
            if (text_val === "") {
                $(".input-group").removeClass('is-filled');
            } else {
                $(".input-group").addClass('is-filled');
            }
        });
    </script>
    @endpush
</x-dashboard::layout>
