@props(['title' => 'Overview'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Counterpoint is a simple POS back-office for managing customer and user accounts.">
    <title>{{ $title }} · Counterpoint</title>
    <link rel="icon" href="{{ asset('images/mark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer>
</script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="{{ route('home') }}">
<img src="{{ asset('images/mark.svg') }}" width="32" height="32" alt="">
<span>Counterpoint<span class="brand-caption">POS ACCOUNT MANAGER</span>
</span>
</a>
        <button class="menu-toggle btn secondary" type="button" aria-expanded="false" aria-controls="main-nav">Menu <x-icon name="menu" />
</button>
        <nav id="main-nav" aria-label="Main navigation">
            @foreach (['home' => 'Overview', 'customers.index' => 'Customers', 'users.index' => 'Users', 'about' => 'About'] as $name => $label)
                @php($active = request()->routeIs($name === 'home' || $name === 'about' ? $name : explode('.', $name)[0].'.*'))
                <a href="{{ route($name) }}" @class(['nav-link', 'active' => $active]) @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <x-badge>School demo</x-badge>
    </div>
</header>
<main id="main" class="container main" tabindex="-1">
    @if(session('success'))<x-alert>{{ session('success') }}</x-alert>@endif
    {{ $slot }}
</main>
<footer class="site-footer container">
<span>Counterpoint <span aria-hidden="true">/</span> A little order, every day.</span>
<span>Account management only · Fictional data only</span>
</footer>
</body>
</html>
