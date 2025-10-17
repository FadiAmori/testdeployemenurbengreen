@php($activePage = 'notifications')
@php($inputClass = 'form-control admin-input')
@php($cardClass = 'card admin-card shadow-sm')

<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar :activePage="$activePage" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-dashboard::navbars.navs.auth titlePage="Notifications" />

        <div class="container-fluid py-4">
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <h2 class="mb-1">Notifications</h2>
            <p class="text-muted">{{ $notifications->count() }} au total</p>

            <!-- Create Form -->
            <div class="{{ $cardClass }} mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="mb-0">Ajouter une notification</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.notifications.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" class="{{ $inputClass }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="{{ $inputClass }}" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">CRÉER LA NOTIFICATION</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List with Edit and Delete -->
            <div class="card card-admin">
                <div class="card-header bg-transparent border-0 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">Liste des notifications</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-admin">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                    <tr>
                                        <td>{{ $notification->name }}</td>
                                        <td>{{ $notification->description }}</td>
                                        <td class="text-end">
                                            <!-- Edit Button triggering Modal -->
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $notification->id }}">MODIFIER</button>

                                            <!-- Delete Form -->
                                            <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Confirmer la suppression?')">SUPPRIMER</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $notification->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Modifier Notification</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="{{ route('admin.notifications.update', $notification) }}">
                                                        @csrf @method('PUT')
                                                        <div class="mb-3">
                                                            <label class="form-label">Nom</label>
                                                            <input type="text" name="name" value="{{ $notification->name }}" class="{{ $inputClass }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="{{ $inputClass }}">{{ $notification->description }}</textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Sauvegarder</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Aucune notification pour le moment.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-dashboard::layout>
