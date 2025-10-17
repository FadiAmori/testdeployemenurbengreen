@php
    $activePage = 'maintenance';
    $inputClass = 'form-control bg-transparent text-white border border-secondary';
    $cardClass = 'card bg-gradient-dark text-white shadow';
@endphp

@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid py-4">
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Notifications pour {{ $product->name }}</h2>
                <p class="text-muted">Gérez les notifications associées à ce produit</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.maintenance.categories') }}" class="btn btn-secondary">Retour aux catégories</a>
            </div>
        </div>

        <!-- Product Info Card -->
        <div class="{{ $cardClass }} mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informations du produit</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <img src="{{ $product->primary_image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" 
                             alt="{{ $product->name }}" 
                             class="img-fluid rounded" 
                             style="max-height: 150px; object-fit: cover;">
                    </div>
                    <div class="col-md-9">
                        <h6>{{ $product->name }}</h6>
                        <p class="mb-1"><strong>Catégorie:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Prix:</strong> {{ $product->price }}€</p>
                        <p class="mb-0"><strong>Stock:</strong> {{ $product->stock_quantity ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Notifications -->
        <div class="{{ $cardClass }} mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Notifications actuelles ({{ $notifications->count() }})</h5>
                <button type="button" class="btn btn-sm btn-success" onclick="generateAINotifications()">
                    <i class="material-icons me-1" style="font-size: 18px; vertical-align: middle;">psychology</i>
                    🤖 Générer avec IA
                </button>
            </div>
            <div class="card-body">
                @forelse($notifications as $notification)
                    @php
                        // decode pivot days/time if available
                        $pivotDays = [];
                        if (! empty($notification->pivot->days)) {
                            // Check if days is already an array or needs JSON decoding
                            if (is_array($notification->pivot->days)) {
                                $pivotDays = $notification->pivot->days;
                            } elseif (is_string($notification->pivot->days)) {
                                try { 
                                    $pivotDays = json_decode($notification->pivot->days, true) ?: []; 
                                } catch (\Exception $e) { 
                                    $pivotDays = []; 
                                }
                            } elseif (is_numeric($notification->pivot->days)) {
                                // If it's a number, convert to array with that many days
                                $pivotDays = [(int)$notification->pivot->days];
                            }
                        }
                        $pivotTime = $notification->pivot->time ?? null;
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background-color: rgba(255,255,255,0.1);">
                        <div>
                            <strong>{{ $notification->name }}</strong>
                            @if($notification->description)
                                <br><small class="text-muted">{{ $notification->description }}</small>
                            @endif
                            @if(! empty($pivotDays) || ! empty($pivotTime))
                                @php
                                    $scheduleParts = [];
                                    if (is_array($pivotDays) && in_array('everyday', $pivotDays)) {
                                        $scheduleParts[] = 'Tous les jours';
                                    } elseif (! empty($pivotDays) && is_array($pivotDays)) {
                                        $scheduleParts[] = implode(', ', array_map('ucfirst', $pivotDays));
                                    } elseif (! empty($pivotDays)) {
                                        $scheduleParts[] = 'Tous les ' . implode(', ', (array)$pivotDays) . ' jours';
                                    }
                                    if (! empty($pivotTime)) {
                                        try {
                                            $scheduleParts[] = \Carbon\Carbon::parse($pivotTime)->format('H:i');
                                        } catch (\Exception $e) {
                                            // ignore parse errors
                                        }
                                    }
                                    $scheduleText = implode(' — ', $scheduleParts);
                                @endphp
                                <div class="mt-1"><small class="text-muted">{{ $scheduleText }}</small></div>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateNotifModal{{ $notification->id }}">
                                <i class="material-icons">edit</i>
                            </button>
                            <form method="POST" action="{{ route('admin.shop.products.notifications.detach', [$product, $notification]) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Retirer cette notification du produit?')">
                                    <i class="material-icons">remove</i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Update Modal -->
                    <div class="modal fade" id="updateNotifModal{{ $notification->id }}" tabindex="-1" aria-labelledby="updateNotifModalLabel{{ $notification->id }}" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content bg-dark text-white">
                          <div class="modal-header">
                            <h5 class="modal-title" id="updateNotifModalLabel{{ $notification->id }}">Mettre à jour: {{ $notification->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <form method="POST" action="{{ route('admin.shop.products.notifications.update', [$product, $notification->id]) }}">
                            @csrf @method('PUT')
                            <div class="modal-body">
                                <label class="form-label text-muted">Jours de notification</label>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days[]" id="modal_day_everyday_{{ $notification->id }}" value="everyday" {{ (is_array($pivotDays) && in_array('everyday', $pivotDays)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="modal_day_everyday_{{ $notification->id }}">Tous les jours</label>
                                    </div>
                                    @foreach(['monday' => 'Lundi', 'tuesday' => 'Mardi', 'wednesday' => 'Mercredi', 'thursday' => 'Jeudi', 'friday' => 'Vendredi', 'saturday' => 'Samedi', 'sunday' => 'Dimanche'] as $key => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" id="modal_day_{{ $key }}_{{ $notification->id }}" value="{{ $key }}" {{ (is_array($pivotDays) && in_array($key, $pivotDays)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="modal_day_{{ $key }}_{{ $notification->id }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mb-2">
                                    <label class="form-label text-muted">Heure (HH:MM)</label>
                                    <input type="time" name="time" class="{{ $inputClass }}" value="{{ !empty($pivotTime) ? \Carbon\Carbon::parse($pivotTime)->format('H:i') : '' }}">
                                </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                              <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune notification associée à ce produit.</p>
                @endforelse
            </div>
        </div>

        <!-- Add Notification -->
        <div class="{{ $cardClass }}">
            <div class="card-header">
                <h5 class="mb-0">Ajouter une notification</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.shop.products.notifications.attach', $product) }}">
                    @csrf
                    <div class="row gy-2">
                        <div class="col-md-12">
                            <select name="notification_id" class="{{ $inputClass }}" required>
                                <option value="">Sélectionner une notification...</option>
                                @foreach($allNotifications as $notification)
                                    @if(!$notifications->contains($notification))
                                        <option value="{{ $notification->id }}">
                                            {{ $notification->name }}
                                            @if($notification->description) - {{ Str::limit($notification->description, 50) }}@endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Day-of-week checkboxes -->
                        <div class="col-md-12">
                            <label class="form-label text-muted">Jours de notification</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" id="day_everyday" value="everyday">
                                    <label class="form-check-label" for="day_everyday">Tous les jours</label>
                                </div>
                                @foreach(['monday' => 'Lundi', 'tuesday' => 'Mardi', 'wednesday' => 'Mercredi', 'thursday' => 'Jeudi', 'friday' => 'Vendredi', 'saturday' => 'Samedi', 'sunday' => 'Dimanche'] as $key => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days[]" id="day_{{ $key }}" value="{{ $key }}">
                                        <label class="form-check-label" for="day_{{ $key }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Time input -->
                        <div class="col-md-6">
                            <label class="form-label text-muted">Heure (HH:MM)</label>
                            <input type="time" name="time" class="{{ $inputClass }}" value="{{ old('time') }}">
                        </div>

                        <div class="col-md-6 text-end align-self-end">
                            <button type="submit" class="btn btn-success">
                                <i class="material-icons">add</i> Ajouter
                            </button>
                        </div>
                    </div>
                </form>
                
                @if($allNotifications->count() == $notifications->count())
                    <p class="text-muted mt-2 mb-0">Toutes les notifications disponibles sont déjà associées à ce produit.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- AI Notification Script -->
    <script>
        function generateAINotifications() {
            const productId = {{ $product->id }};
            const productName = '{{ $product->name }}';
            
            // Show loading state
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Génération...';
            
            // Call API
            fetch(`/api/products/${productId}/ai-notifications`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Une erreur est survenue');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    const message = `✅ Succès!\n\n` +
                        `• ${data.count} suggestion(s) trouvée(s) par l'IA\n` +
                        `• ${data.created} nouvelle(s) notification(s) créée(s)\n` +
                        `• ${data.attached} notification(s) attachée(s) au produit\n\n` +
                        `La page va se recharger...`;
                    
                    alert(message);
                    
                    // Reload page to show new notifications
                    window.location.reload();
                } else {
                    alert('❌ ' + (data.message || data.error || 'Aucune suggestion trouvée'));
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('AI Notification Error:', error);
                alert('❌ Erreur: ' + error.message + '\n\nAssurez-vous que le service IA est démarré sur le port 5002.');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            });
        }
    </script>
@endsection
                    