@props(['name', 'label', 'type' => 'text', 'value' => '', 'hint' => '', 'required' => false])
<div class="field">
    <label for="{{ $name }}">{{ $label }} @if($required)<span class="required" aria-hidden="true">*</span>@else<span class="optional">Optional</span>@endif</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($type !== 'file') value="{{ old($name, $value) }}" @endif @required($required) @if($hint || $errors->has($name)) aria-describedby="{{ $hint ? $name.'-hint' : '' }} {{ $errors->has($name) ? $name.'-error' : '' }}" @endif @if($errors->has($name)) aria-invalid="true" @endif {{ $attributes }}>
    @if($hint)<p class="hint" id="{{ $name }}-hint">{{ $hint }}</p>@endif
    @error($name)<p class="field-error" id="{{ $name }}-error">{{ $message }}</p>@enderror
</div>
