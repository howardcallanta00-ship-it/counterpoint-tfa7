<x-layout title="Overview">
    <div class="workspace-label">
<span class="status-dot" aria-hidden="true">
</span> YOUR BACK-OFFICE, SIMPLIFIED</div>
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-copy">
<p class="eyebrow">MEET COUNTERPOINT</p>
<h1 id="hero-title">Good business starts<br class="desktop-break"> with good records.</h1>
<p class="hero-description">A clear, simple home for your customer and user accounts. Keep the people behind your POS organized, one detail at a time.</p>
<div class="actions">
<x-button :href="route('customers.index')">Customers <x-icon />
</x-button>
<x-button variant="secondary" :href="route('users.index')">Users <x-icon />
</x-button>
</div>
<p class="scope-note">
<x-icon name="shield" /> A school project focused on account management.</p>
</div>
        <div class="hero-preview" aria-label="Illustrative account record">
            <div class="preview-top">
<span class="eyebrow">THE PEOPLE BEHIND THE COUNTER</span>
<span class="preview-dots" aria-hidden="true">•••</span>
</div>
            <div class="sample-person">
<span class="sample-avatar">MS</span>
<div>
<strong>Maria Santos</strong>
<span>Fictional customer</span>
</div>
<span class="small-check">
<x-icon name="check" />
</span>
</div>
            <div class="sample-detail">
<span>Email address</span>
<strong>maria@example.test</strong>
</div>
<div class="sample-detail">
<span>Account details</span>
<strong>All in one place</strong>
</div>
            <div class="sample-bottom">
<span class="status-dot" aria-hidden="true">
</span> Clear records. Less busywork.</div>
        </div>
    </section>
    <div class="section-heading">
<h2>A place for every detail.</h2>
<span class="muted">Small tools. Everyday clarity.</span>
</div>
    <div class="feature-grid">
        <x-card class="feature">
<span class="feature-icon">
<x-icon name="people" />
</span>
<p class="eyebrow">01 / CUSTOMERS</p>
<h3>Know your customers.</h3>
<p>Keep names, email addresses and phone numbers together. Add a record or update it in a few clicks.</p>
<a class="text-link" href="{{ route('customers.index') }}">Manage customers <x-icon />
</a>
</x-card>
        <x-card class="feature">
<span class="feature-icon">
<x-icon name="user" />
</span>
<p class="eyebrow">02 / USERS</p>
<h3>Put a name to the team.</h3>
<p>Create user profiles with unique usernames and clear, readable details. No passwords or sign-in required.</p>
<a class="text-link" href="{{ route('users.index') }}">Manage users <x-icon />
</a>
</x-card>
        <x-card class="feature">
<span class="feature-icon">
<x-icon name="image" />
</span>
<p class="eyebrow">03 / PROFILE PHOTOS</p>
<h3>A familiar face helps.</h3>
<p>Add a profile photo. We crop it into a consistent square and keep the current one until its replacement is saved.</p>
<a class="text-link" href="{{ route('users.create') }}">Create a profile <x-icon />
</a>
</x-card>
    </div>
    <div class="demo-note">
<x-badge>Made for learning</x-badge>
<p>This demo has no access control. Please use fictional information only.</p>
<a class="text-link" href="{{ route('about') }}">About the project <x-icon />
</a>
</div>
</x-layout>
