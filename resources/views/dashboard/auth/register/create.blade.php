<x-dashboard::layout bodyClass="bg-gray-200">

    <!-- Minimal home icon -->
    <x-dashboard::navbars.navs.guest signin='login' signup='register'></x-dashboard::navbars.navs.guest>
    <main class="main-content mt-0">
        <section>
            <div class="page-header align-items-start min-vh-100" style="background-image: url('{{ asset('assets/img/bg-smart-home-2.jpg') }}'); background-size: cover; background-position: center;">
                <span class="mask bg-gradient-dark opacity-6"></span>
                <div class="container d-flex align-items-center min-vh-100">
                    <div class="row justify-content-center align-items-center w-100">
                        <div class="col-lg-4 col-md-8 col-12 mx-auto">
                            <div class="card z-index-0 fadeIn3 fadeInBottom">
                                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                    <div class="bg-gradient-success shadow-success border-radius-lg py-3 pe-1">
                                        <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Sign up</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('register') }}" class="text-start">
                                        @csrf
                                        <div class="input-group input-group-outline mt-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                        </div>
                                        @error('name')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                        <div class="input-group input-group-outline mt-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                                        </div>
                                        @error('email')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                        <div class="input-group input-group-outline mt-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control" name="password">
                                        </div>
                                        @error('password')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                        <div class="form-check form-check-info text-start ps-0 mt-3">
                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                I agree the <a href="javascript:;" class="text-success font-weight-bolder">Terms and Conditions</a>
                                            </label>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg bg-gradient-success w-100 mt-4 mb-2">Sign up</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <p class="mb-2 text-sm mx-auto">
                                        Already have an account?
                                        <a href="{{ route('login') }}" class="text-success font-weight-bold">Sign in</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <x-dashboard::footers.guest></x-dashboard::footers.guest>
            </div>
        </section>
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
