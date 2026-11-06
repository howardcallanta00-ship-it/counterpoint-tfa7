@props(['name' => 'arrow'])
<svg {{ $attributes->class(['icon']) }} width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('people')<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M22 21v-2a4 4 0 0 0-3-3.87"/>
<circle cx="9" cy="7" r="4"/>
<path d="M16 3.13a4 4 0 0 1 0 7.75"/>@break
@case('user')<circle cx="12" cy="8" r="4"/>
<path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@case('edit')<path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15z"/>@break
@case('check')<path d="m5 12 4 4L19 6"/>@break
@case('image')<rect x="3" y="3" width="18" height="18" rx="2"/>
<circle cx="8" cy="8" r="1"/>
<path d="m21 15-5-5L5 21"/>@break
@case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
@case('back')<path d="m12 5-7 7 7 7M5 12h14"/>@break
@case('shield')<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6zM8 12l3 3 5-6"/>@break
@default<path d="M5 12h14m-6-6 6 6-6 6"/>
@endswitch
</svg>
