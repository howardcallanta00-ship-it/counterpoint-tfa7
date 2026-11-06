@props(['user', 'size' => 44])
<img {{ $attributes->class(['avatar']) }} src="{{ app(\App\Services\AvatarService::class)->url($user->avatar) }}" alt="Profile photo of {{ $user->full_name ?: 'new user' }}" width="{{ $size }}" height="{{ $size }}" data-avatar data-fallback="{{ asset('images/avatar-placeholder.svg') }}">
