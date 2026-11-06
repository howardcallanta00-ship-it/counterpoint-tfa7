<x-layout title="About">
    <x-page-header title="A small app. A complete learning journey." eyebrow="ABOUT COUNTERPOINT" description="A Laravel POS back-office project focused on the people in your records." />
    <div class="about-grid">
<x-card class="padded">
<p class="eyebrow">THE PROJECT</p>
<h2>Built to be understood.</h2>
<p>Counterpoint brings customer records and user profiles into one simple account manager. You can create accounts, edit details, validate input and upload profile photos.</p>
<p>The scope is account management only. There are no sales, inventory, payments, authentication or roles. User profiles do not grant access to anything.</p>
<div class="callout">
<x-icon name="shield" />
<p>All demo names, emails and phone numbers are fictional. There is no access control: please keep real personal information out of this app.</p>
</div>
</x-card>
    <x-card class="padded">
<p class="eyebrow">THE TOOLBOX</p>
<h2>Simple by design.</h2>
<dl class="stack-list">
<div>
<dt>Application</dt>
<dd>Laravel 12 · PHP 8.2+</dd>
</div>
<div>
<dt>Interface</dt>
<dd>Blade · Hand-written CSS</dd>
</div>
<div>
<dt>Records</dt>
<dd>Eloquent · SQL migrations</dd>
</div>
<div>
<dt>Profile photos</dt>
<dd>Intervention Image v3 · GD</dd>
</div>
<div>
<dt>Deployment target</dt>
<dd>Render · PostgreSQL · S3-compatible storage</dd>
</div>
</dl>
</x-card>
</div>
    <x-card class="padded timeline-card">
<p class="eyebrow">FROM THE FIRST ARRAY TO THE FINAL FORM</p>
<h2>The lab journey</h2>
<ol class="timeline">@foreach(['TFA1' => 'CodeIgniter and static arrays', 'TFA2' => 'MySQL and persistent records', 'TFA3' => 'Account forms and validation', 'TFA4' => 'Authentication concepts', 'TFA5' => 'Laravel and static arrays', 'TFA6' => 'Eloquent and database models', 'Current' => 'Laravel account forms, validation and avatars'] as $step => $description)<li>
<span class="timeline-step">{{ $step }}</span>
<span>{{ $description }}</span>
</li>@endforeach</ol>
<p class="hint">The final app implements the current activity directly. Earlier labs provide context; their authentication and static-array versions are not included.</p>
</x-card>
</x-layout>
