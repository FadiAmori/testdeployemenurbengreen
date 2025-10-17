<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <!-- Add CSRF token meta tag as a fallback -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-dashboard::navbars.sidebar activePage="user-management.index"></x-dashboard::navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-dashboard::navbars.navs.auth titlePage="User Management"></x-dashboard::navbars.navs.auth>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class=" me-3 my-3 text-end">
                            <a class="btn btn-primary mb-0" href="#" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                <i class="material-icons text-sm">add</i>&nbsp;&nbsp;Add New User
                            </a>
                        </div>
                        <div class="card-body px-0 pb-2">
                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible text-white" role="alert">
                                    <span class="text-sm">{{ session('status') }}</span>
                                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            <div class="alert alert-success alert-dismissible text-white d-none" id="successAlert">
                                <span class="text-sm" id="successMessage"></span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="alert alert-danger alert-dismissible text-white d-none" id="errorAlert">
                                <span class="text-sm" id="errorMessage"></span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0" id="userTable">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">PHOTO</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NAME</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">EMAIL</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ROLE</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">STATUS</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">CREATION DATE</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr data-id="{{ $user->id }}">
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <p class="mb-0 text-sm">{{ $user->id }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div>
                                                            <img src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : asset('default-photo.jpg') }}" class="avatar avatar-sm me-3 border-radius-lg" alt="user{{ $user->id }}">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $user->prenom }} {{ $user->name }}</h6>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">{{ ucfirst($user->role ?? 'user') }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">{{ $user->is_blocked ? 'Blocked' : 'Active' }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">{{ $user->created_at->format('d/m/y') }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <button class="btn btn-success btn-link edit-user" data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#editUserModal">
                                                        <i class="material-icons">edit</i>
                                                        <div class="ripple-container"></div>
                                                    </button>
                                                    <button class="btn btn-danger btn-link delete-user" data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                                        <i class="material-icons">close</i>
                                                        <div class="ripple-container"></div>
                                                    </button>
                                                    <button class="btn btn-{{ $user->is_blocked ? 'success' : 'warning' }} btn-link {{ $user->is_blocked ? 'unblock-user' : 'block-user' }}" data-id="{{ $user->id }}">
                                                        <i class="material-icons">{{ $user->is_blocked ? 'lock_open' : 'lock' }}</i>
                                                        {{ $user->is_blocked ? 'Unblock' : 'Block' }}
                                                        <div class="ripple-container"></div>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create User Modal -->
            <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createUserModalLabel">Add New User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createUserForm" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Email address</label>
                                    <input type="email" name="email" class="form-control border border-2 p-2" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">First Name (Prénom)</label>
                                    <input type="text" name="prenom" class="form-control border border-2 p-2">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="name" class="form-control border border-2 p-2" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control border border-2 p-2">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control border border-2 p-2">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Profile Photo</label>
                                    <input type="file" name="profile_photo" class="form-control border border-2 p-2" accept="image/*">
                                    <div class="invalid-feedback"></div>
                                </div>
                                @if (Schema::hasColumn('users', 'role'))
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role" class="form-select border border-2 p-2">
                                            <option value="{{ \App\Models\User::ROLE_USER }}" selected>User</option>
                                            <option value="{{ \App\Models\User::ROLE_ADMIN }}">Admin</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control border border-2 p-2" value="user123">
                                    <small class="text-muted">Default is <code>user123</code> if left unchanged.</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control border border-2 p-2" value="user123">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">About</label>
                                    <textarea class="form-control border border-2 p-2" name="about" rows="4" cols="50"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <button type="submit" class="btn bg-gradient-dark">Create User</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editUserForm" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="id">
                                <div class="mb-3">
                                    <label class="form-label">Email address</label>
                                    <input type="email" name="email" class="form-control border border-2 p-2" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">First Name (Prénom)</label>
                                    <input type="text" name="prenom" class="form-control border border-2 p-2">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="name" class="form-control border border-2 p-2" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control border border-2 p-2">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control border border-2 p-2">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Profile Photo</label>
                                    <input type="file" name="profile_photo" class="form-control border border-2 p-2" accept="image/*">
                                    <img src="" class="avatar avatar-sm mt-2 border-radius-lg d-none" id="currentPhoto">
                                    <div class="invalid-feedback"></div>
                                </div>
                                @if (Schema::hasColumn('users', 'role'))
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role" class="form-select border border-2 p-2">
                                            <option value="{{ \App\Models\User::ROLE_USER }}">User</option>
                                            <option value="{{ \App\Models\User::ROLE_ADMIN }}">Admin</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">About</label>
                                    <textarea class="form-control border border-2 p-2" name="about" rows="4" cols="50"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <button type="submit" class="btn bg-gradient-dark">Update User</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete User Modal -->
            <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this user?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <x-dashboard::footers.auth></x-dashboard::footers.auth>
        </div>
    </main>
    <x-dashboard::plugins></x-dashboard::plugins>

    <!-- JavaScript for Modal and AJAX -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const createUserForm = document.getElementById('createUserForm');
            const editUserForm = document.getElementById('editUserForm');
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            const userTable = document.getElementById('userTable').getElementsByTagName('tbody')[0];
            const deleteUserModal = document.getElementById('deleteUserModal');
            let currentUserId = null;

            // Helper to get CSRF token
            function getCsrfToken() {
                const tokenElement = document.querySelector('meta[name="csrf-token"]');
                if (!tokenElement) {
                    console.error('CSRF token meta tag not found');
                    errorMessage.textContent = 'CSRF token not found. Please refresh the page.';
                    errorAlert.classList.remove('d-none');
                    return null;
                }
                return tokenElement.content;
            }

            function formatRole(role) {
                if (!role) {
                    return 'User';
                }
                const normalised = role.toString();
                return normalised.charAt(0).toUpperCase() + normalised.slice(1);
            }

            async function requestJson(url, options = {}) {
                const headers = options.headers || {};
                headers['Accept'] = 'application/json';
                headers['X-Requested-With'] = 'XMLHttpRequest';
                options.headers = headers;
                try {
                    const res = await fetch(url, options);
                    const text = await res.text();
                    let data = {};
                    try { data = text ? JSON.parse(text) : {}; } catch (e) { data = { message: text || res.statusText }; }

                    if (!res.ok) {
                        const err = new Error((data && data.message) || `HTTP ${res.status}`);
                        err.status = res.status;
                        err.data = data;
                        throw err;
                    }
                    return data;
                } catch (err) {
                    throw err;
                }
            }

            // Create User Form Submission
            createUserForm.addEventListener('submit', function (e) {
                e.preventDefault();
                clearErrors(createUserForm);

                const formData = new FormData(createUserForm);
                const csrfToken = getCsrfToken();
                if (!csrfToken) return;

                requestJson('{{ route('user-management.store') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData,
                }).then(data => {
                    if (data.status === 'success') {
                        const newRow = userTable.insertRow();
                        newRow.setAttribute('data-id', data.user.id);
                        newRow.innerHTML = `
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div class="d-flex flex-column justify-content-center">
                                        <p class="mb-0 text-sm">${data.user.id}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div>
                                        <img src="${data.user.profile_photo || '{{ asset("default-photo.jpg") }}'}" class="avatar avatar-sm me-3 border-radius-lg" alt="user${data.user.id}">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">${data.user.prenom ? data.user.prenom + ' ' : ''}${data.user.name}</h6>
                                </div>
                            </td>
                            <td class="align-middle text-center text-sm">
                                <p class="text-xs text-secondary mb-0">${data.user.email}</p>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">${formatRole(data.user.role)}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">${data.user.is_blocked ? 'Blocked' : 'Active'}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">${data.user.created_at}</span>
                            </td>
                            <td class="align-middle">
                                <button class="btn btn-success btn-link edit-user" data-id="${data.user.id}" data-bs-toggle="modal" data-bs-target="#editUserModal">
                                    <i class="material-icons">edit</i>
                                    <div class="ripple-container"></div>
                                </button>
                                <button class="btn btn-danger btn-link delete-user" data-id="${data.user.id}" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                    <i class="material-icons">close</i>
                                    <div class="ripple-container"></div>
                                </button>
                                <button class="btn btn-${data.user.is_blocked ? 'success' : 'warning'} btn-link ${data.user.is_blocked ? 'unblock-user' : 'block-user'}" data-id="${data.user.id}">
                                    <i class="material-icons">${data.user.is_blocked ? 'lock_open' : 'lock'}</i>
                                    ${data.user.is_blocked ? 'Unblock' : 'Block'}
                                    <div class="ripple-container"></div>
                                </button>
                            </td>
                        `;
                        successMessage.textContent = data.message;
                        successAlert.classList.remove('d-none');
                        createUserForm.reset();
                        bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
                    } else if (data.errors) {
                        handleErrors(data.errors, createUserForm);
                    } else {
                        throw new Error(data.message || 'Create user failed');
                    }
                }).catch(error => {
                    console.error('Create user error:', error);
                    if (error.status === 422 && error.data && error.data.errors) {
                        handleErrors(error.data.errors, createUserForm);
                    } else {
                        errorMessage.textContent = error.data?.message || 'An error occurred while creating the user. Please try again.';
                        errorAlert.classList.remove('d-none');
                    }
                });
            });

            // Edit User Button Click
            document.addEventListener('click', function (e) {
                if (e.target.closest('.edit-user')) {
                    const userId = e.target.closest('.edit-user').getAttribute('data-id');
                    const csrfToken = getCsrfToken();
                    if (!csrfToken) return;

                requestJson(`{{ url('user-management') }}/${userId}/edit`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }).then(data => {
                        const form = editUserForm;
                        clearErrors(form);
                        form.querySelector('input[name="id"]').value = data.user.id;
                        form.querySelector('input[name="email"]').value = data.user.email;
                        form.querySelector('input[name="prenom"]').value = data.user.prenom || '';
                        form.querySelector('input[name="name"]').value = data.user.name;
                        form.querySelector('input[name="phone"]').value = data.user.phone || '';
                        form.querySelector('input[name="location"]').value = data.user.location || '';
                        form.querySelector('textarea[name="about"]').value = data.user.about || '';
                        @if (Schema::hasColumn('users', 'role'))
                            form.querySelector('select[name="role"]').value = data.user.role || '{{ \App\Models\User::ROLE_USER }}';
                        @endif
                        const currentPhoto = form.querySelector('#currentPhoto');
                        if (data.user.profile_photo) {
                            currentPhoto.src = data.user.profile_photo;
                            currentPhoto.classList.remove('d-none');
                        } else {
                            currentPhoto.classList.add('d-none');
                        }
                    }).catch(error => {
                        console.error('Edit user fetch error:', error);
                        errorMessage.textContent = 'Failed to load user data. Please try again.';
                        errorAlert.classList.remove('d-none');
                    });
                }
            });

            // Edit User Form Submission
            editUserForm.addEventListener('submit', function (e) {
                e.preventDefault();
                clearErrors(editUserForm);

                const userId = editUserForm.querySelector('input[name="id"]').value;
                const formData = new FormData(editUserForm);
                const csrfToken = getCsrfToken();
                if (!csrfToken) return;

                // Use method spoofing for robust handling of multipart + PATCH
                formData.append('_method', 'PATCH');
                requestJson(`{{ url('user-management') }}/${userId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData,
                }).then(data => {
                    if (data.status === 'success') {
                        const row = userTable.querySelector(`tr[data-id="${data.user.id}"]`);
                        row.innerHTML = `
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div class="d-flex flex-column justify-content-center">
                                        <p class="mb-0 text-sm">${data.user.id}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div>
                                        <img src="${data.user.profile_photo || '{{ asset("default-photo.jpg") }}'}" class="avatar avatar-sm me-3 border-radius-lg" alt="user${data.user.id}">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm">${data.user.prenom ? data.user.prenom + ' ' : ''}${data.user.name}</h6>
                                </div>
                            </td>
                            <td class="align-middle text-center text-sm">
                                <p class="text-xs text-secondary mb-0">${data.user.email}</p>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">${formatRole(data.user.role)}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">${data.user.is_blocked ? 'Blocked' : 'Active'}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">${data.user.created_at}</span>
                            </td>
                            <td class="align-middle">
                                <button class="btn btn-success btn-link edit-user" data-id="${data.user.id}" data-bs-toggle="modal" data-bs-target="#editUserModal">
                                    <i class="material-icons">edit</i>
                                    <div class="ripple-container"></div>
                                </button>
                                <button class="btn btn-danger btn-link delete-user" data-id="${data.user.id}" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                    <i class="material-icons">close</i>
                                    <div class="ripple-container"></div>
                                </button>
                                <button class="btn btn-${data.user.is_blocked ? 'success' : 'warning'} btn-link ${data.user.is_blocked ? 'unblock-user' : 'block-user'}" data-id="${data.user.id}">
                                    <i class="material-icons">${data.user.is_blocked ? 'lock_open' : 'lock'}</i>
                                    ${data.user.is_blocked ? 'Unblock' : 'Block'}
                                    <div class="ripple-container"></div>
                                </button>
                            </td>
                        `;
                        successMessage.textContent = data.message;
                        successAlert.classList.remove('d-none');
                        bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                    } else if (data.errors) {
                        handleErrors(data.errors, editUserForm);
                    } else {
                        throw new Error(data.message || 'Update user failed');
                    }
                }).catch(error => {
                    console.error('Update user error:', error);
                    if (error.status === 422 && error.data && error.data.errors) {
                        handleErrors(error.data.errors, editUserForm);
                    } else {
                        errorMessage.textContent = error.data?.message || 'An error occurred while updating the user. Please try again.';
                        errorAlert.classList.remove('d-none');
                    }
                });
            });

            // Delete User Button Click
            document.addEventListener('click', function (e) {
                if (e.target.closest('.delete-user')) {
                    currentUserId = e.target.closest('.delete-user').getAttribute('data-id');
                }
            });

            // Confirm Delete Button
            document.getElementById('confirmDelete').addEventListener('click', function () {
                if (currentUserId) {
                    const csrfToken = getCsrfToken();
                    if (!csrfToken) return;

                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    requestJson(`{{ url('user-management') }}/${currentUserId}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData,
                    }).then(data => {
                        if (data.status === 'success') {
                            userTable.querySelector(`tr[data-id="${currentUserId}"]`).remove();
                            successMessage.textContent = data.message;
                            successAlert.classList.remove('d-none');
                            bootstrap.Modal.getInstance(deleteUserModal).hide();
                            currentUserId = null;
                        } else {
                            errorMessage.textContent = 'Failed to delete user.';
                            errorAlert.classList.remove('d-none');
                        }
                    }).catch(error => {
                        console.error('Delete user error:', error);
                        errorMessage.textContent = 'An error occurred while deleting the user. Please try again.';
                        errorAlert.classList.remove('d-none');
                    });
                }
            });

            // Block User Button Click
            document.addEventListener('click', function (e) {
                if (e.target.closest('.block-user')) {
                    const userId = e.target.closest('.block-user').getAttribute('data-id');
                    const csrfToken = getCsrfToken();
                    if (!csrfToken) return;

                    fetch(`{{ route('user-management.block', ':userId') }}`.replace(':userId', userId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            const row = userTable.querySelector(`tr[data-id="${userId}"]`);
                            row.querySelector('td:nth-child(6) span').textContent = 'Blocked';
                            const button = row.querySelector('.block-user');
                            button.classList.remove('btn-warning', 'block-user');
                            button.classList.add('btn-success', 'unblock-user');
                            button.querySelector('i').textContent = 'lock_open';
                            button.querySelector('i').nextSibling.nodeValue = ' Unblock';
                            successMessage.textContent = data.message;
                            successAlert.classList.remove('d-none');
                        } else {
                            errorMessage.textContent = data.message || 'Failed to block user.';
                            errorAlert.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        console.error('Block user error:', error);
                        errorMessage.textContent = 'An error occurred while blocking the user. Please try again.';
                        errorAlert.classList.remove('d-none');
                    });
                }
            });

            // Unblock User Button Click
            document.addEventListener('click', function (e) {
                if (e.target.closest('.unblock-user')) {
                    const userId = e.target.closest('.unblock-user').getAttribute('data-id');
                    const csrfToken = getCsrfToken();
                    if (!csrfToken) return;

                    fetch(`{{ route('user-management.unblock', ':userId') }}`.replace(':userId', userId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            const row = userTable.querySelector(`tr[data-id="${userId}"]`);
                            row.querySelector('td:nth-child(6) span').textContent = 'Active';
                            const button = row.querySelector('.unblock-user');
                            button.classList.remove('btn-success', 'unblock-user');
                            button.classList.add('btn-warning', 'block-user');
                            button.querySelector('i').textContent = 'lock';
                            button.querySelector('i').nextSibling.nodeValue = ' Block';
                            successMessage.textContent = data.message;
                            successAlert.classList.remove('d-none');
                        } else {
                            errorMessage.textContent = data.message || 'Failed to unblock user.';
                            errorAlert.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        console.error('Unblock user error:', error);
                        errorMessage.textContent = 'An error occurred while unblocking the user. Please try again.';
                        errorAlert.classList.remove('d-none');
                    });
                }
            });

            // Clear error messages
            function clearErrors(form) {
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.classList.remove('d-block');
                });
                form.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
            }

            // Handle validation errors
            function handleErrors(errors, form) {
                for (const [field, messages] of Object.entries(errors)) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const errorDiv = input.nextElementSibling;
                        if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                            errorDiv.textContent = messages[0];
                            errorDiv.classList.add('d-block');
                        }
                    }
                }
            }
        });
    </script>
</x-dashboard::layout>
