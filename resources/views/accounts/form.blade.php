@php($isUser = $kind === 'users')
@php($singular = $isUser ? 'user' : 'customer')
@php($editing = $record->exists)
@php($title = ($editing ? 'Edit ' : 'Add ').$singular)
<x-layout :title="$title">
    <div class="form-container">
<a class="text-link back-link" href="{{ route($kind.'.index') }}">
<x-icon name="back" /> Back to {{ $kind }}</a>
    <x-page-header :title="$title" eyebrow="ACCOUNT DETAILS" :description="$editing ? 'A few small updates keep your records in good shape.' : 'A new connection starts with the essentials.'" />
    @if($errors->any())<div class="alert danger error-summary" role="alert" tabindex="-1" data-error-summary>
<div>
<strong>Please check the following details.</strong>
<ul>@foreach($errors->getMessages() as $field => $messages)<li>
<a href="#{{ $field }}">{{ $messages[0] }}</a>
</li>@endforeach</ul>
</div>
</div>@endif
    <x-card class="form-card">
        <form action="{{ $editing ? route($kind.'.update', $record) : route($kind.'.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($editing) @method('PUT') @endif
            <div class="form-section">
<div class="form-section-heading">
<span class="feature-icon">
<x-icon :name="$isUser ? 'user' : 'people'" />
</span>
<div>
<h2>{{ $isUser ? 'Profile information' : 'Customer information' }}</h2>
<p class="hint">Fields marked <span class="required">*</span> are required.</p>
</div>
</div>
            <x-field name="full_name" label="Full name" :value="$record->full_name" :required="true" maxlength="100" autocomplete="name" placeholder="e.g. Maria Santos" />
            @if($isUser)
                <x-field name="username" label="Username" :value="$record->username" :required="true" maxlength="50" autocomplete="off" spellcheck="false" hint="Choose a unique name, up to 50 characters." placeholder="e.g. maria.santos" />
            @else
                <x-field name="email" label="Email address" type="email" :value="$record->email" :required="true" maxlength="100" autocomplete="email" placeholder="e.g. maria@example.test" />
                <x-field name="phone" label="Phone number" type="tel" :value="$record->phone" maxlength="20" autocomplete="tel" hint="Include an area or country code when needed." placeholder="e.g. DEMO-000-0001" />
            @endif</div>
            @if($isUser)<div class="form-section photo-section">
<h2>Profile photo</h2>
<p class="muted small">Make the profile a little more personal.</p>
<div class="photo-field">
<x-avatar :user="$record" :size="96" />
<div>
<x-field name="avatar" label="Upload a photo" type="file" accept="image/jpeg,image/png,.jpg,.jpeg,.png" hint="JPG or PNG, up to 2 MB and 4096 × 4096 pixels. Cropped to 256 × 256." />@if($editing)<p class="hint">Leave this empty to keep the current photo.</p>@endif</div>
</div>
</div>@endif
            <div class="form-actions">
<x-button variant="secondary" :href="route($kind.'.index')">Cancel</x-button>
<x-button>Save {{ $singular }} <x-icon name="check" />
</x-button>
</div>
        </form>
    </x-card>
<p class="under-card">This is a public school demo. Use fictional details only.</p>
</div>
</x-layout>
