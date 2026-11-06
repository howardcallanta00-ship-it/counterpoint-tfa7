@php($isUser = $kind === 'users')
@php($singular = $isUser ? 'user' : 'customer')
<x-layout :title="ucfirst($kind)">
    <x-page-header :title="ucfirst($kind)" :description="$isUser ? 'The people behind your counter, all in one place.' : 'Keep your customer details clear, current and easy to find.'">
<x-button :href="route($kind.'.create')">
<x-icon name="plus" /> Add {{ $singular }}</x-button>
</x-page-header>
    <x-card class="records-card">
        <div class="card-heading">
<div class="inline">
<x-icon :name="$isUser ? 'user' : 'people'" />
<h2>All {{ $kind }}</h2>
<x-badge>{{ $records->count() }}</x-badge>
</div>
<span class="muted small">Sorted by name</span>
</div>
        @if($records->isEmpty())<x-empty-state :kind="$kind" />@else
        <x-data-table>
<caption class="sr-only">{{ ucfirst($kind) }} and account details</caption>
<thead>
<tr>
<th scope="col">{{ $isUser ? 'User' : 'Customer' }}</th>
<th scope="col">{{ $isUser ? 'Username' : 'Email address' }}</th>@unless($isUser)<th scope="col">Phone</th>@endunless<th scope="col">Created</th>
<th scope="col">
<span class="sr-only">Actions</span>
</th>
</tr>
</thead>
<tbody>
        @foreach($records as $record)<tr>
<td>
<div class="person">@if($isUser)<x-avatar :user="$record" />@else<span class="initial-avatar" aria-hidden="true">{{ mb_substr($record->full_name, 0, 1) }}</span>@endif<strong>{{ $record->full_name }}</strong>
</div>
</td>
<td>@if($isUser)<span class="username">{{ $record->username }}</span>@else{{ $record->email }}@endif</td>@unless($isUser)<td>{{ $record->phone ?: '—' }}</td>@endunless<td class="date">{{ $record->created_at?->format('M d, Y') ?: '—' }}</td>
<td>
<a class="btn row-action" href="{{ route($kind.'.edit', $record) }}" aria-label="Edit {{ $record->full_name }}">
<x-icon name="edit" /> Edit</a>
</td>
</tr>@endforeach
        </tbody>
</x-data-table>
        <div class="table-footer">{{ $records->count() }} {{ $records->count() === 1 ? $singular : $kind }} <span>Fictional data only</span>
</div>
        @endif
    </x-card>
    <p class="under-card">
<x-icon name="shield" /> Account management only. These records do not grant sign-in access.</p>
</x-layout>
