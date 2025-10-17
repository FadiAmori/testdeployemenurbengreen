@props(['titlePage'])

<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 mt-4 border-radius-xl shadow-sm position-sticky top-1 z-index-sticky" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-75" href="{{ route('user-profile') }}">Admin</a></li>
                <li class="breadcrumb-item text-sm active" aria-current="page">{{ $titlePage }}</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">{{ $titlePage }}</h6>
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">@csrf</form>
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item d-flex align-items-center me-2">
                <button type="button" class="btn btn-sm bg-gradient-primary mb-0 px-3" data-bs-toggle="modal" data-bs-target="#passwordSettingsModal">
                    <i class="material-icons me-1">settings</i>
                    <span>Settings</span>
                </button>
            </li>
            <li class="nav-item d-flex align-items-center">
                <a href="#" class="btn btn-sm bg-gradient-success mb-0 px-3" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fa fa-sign-out me-sm-1"></i>
                    <span>Sign Out</span>
                </a>
            </li>
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                <a href="javascript:;" class="nav-link p-0" id="iconNavbarSidenav">
                    <div class="sidenav-toggler-inner">
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Password Settings Modal -->
<div class="modal fade" id="passwordSettingsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('profile.password') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">New password</label>
            <input type="password" name="password" class="form-control" required>
            @error('password')<small class="text-danger">{{ $message }}</small>@enderror
          </div>
          <div class="mb-0">
            <label class="form-label">Confirm new password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
