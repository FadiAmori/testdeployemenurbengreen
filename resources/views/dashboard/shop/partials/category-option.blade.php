@php
    $targetField = $fieldName ?? 'parent_id';
    $shouldUseOld = $useOld ?? true;
    $selectedValue = $shouldUseOld ? old($targetField, $selected ?? null) : ($selected ?? null);
    $excludeIds = collect($exclude ?? [])->filter()->map(fn ($id) => (int) $id)->all();
    $isExcluded = in_array((int) ($category->id ?? 0), $excludeIds, true);
    $indent = str_repeat('— ', max($level ?? 0, 0));
@endphp

@if (! $isExcluded)
    <option value="{{ $category->id }}" {{ (int) $selectedValue === (int) ($category->id ?? 0) ? 'selected' : '' }}>
        {{ $indent }}{{ $category->name }}
    </option>
@endif
