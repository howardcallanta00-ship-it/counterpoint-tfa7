@props(['kind'])
<div class="empty-state">
<span class="feature-icon">
<x-icon :name="$kind === 'users' ? 'user' : 'people'" />
</span>
<h2>No {{ $kind }} yet</h2>
<p class="muted">Start with one account. You can edit the details any time.</p>
<x-button :href="route($kind.'.create')">
<x-icon name="plus" /> Add {{ $kind === 'users' ? 'user' : 'customer' }}</x-button>
</div>
