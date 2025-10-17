@php
    /** @var \Illuminate\Support\Collection|array $categories */
    $selectedId = isset($selected) ? (int) $selected : null;
    $hasOptions = false;
@endphp

<option value="">Sélectionner</option>
@foreach($categories as $category)
    @php($subs = $category->children ?? collect())
    @if($subs->count())
        @php($hasOptions = true)
        <optgroup label="{{ $category->name }}">
            @foreach($subs as $sub)
                <option value="{{ $sub->id }}" {{ $selectedId === (int) $sub->id ? 'selected' : '' }}>
                    {{ $sub->name }}
                </option>
            @endforeach
        </optgroup>
    @endif
@endforeach
@if(! $hasOptions)
    <option value="" disabled>Aucune sous-catégorie disponible</option>
@endif
