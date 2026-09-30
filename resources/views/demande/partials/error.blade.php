@error($field)
    <p class="mt-1 text-sm text-red-700" id="{{ str_replace(['.', '[', ']'], '-', $field) }}-error">{{ $message }}</p>
@enderror
