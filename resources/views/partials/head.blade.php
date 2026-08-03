<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="google-site-verification" content="_Zf-YQmcbwjFdLl_B4lkpdElzrVHwS6xnvJi_9NL0y4" />

@php
    $siteSetting = \App\Models\SiteSetting::current();
    $siteName = $siteSetting->site_name;
    $metaTitle = $metaTitle ?? $siteName;
    $metaDescription = $metaDescription ?? 'Portal Digital Ikatan Cendekiawan Muslim Indonesia (ICMI) Kabupaten Bengkalis.';
    // Canonical WAJIB dari APP_URL, bukan host request — domain asing yang
    // menunjuk ke server ini tidak boleh mengaku sebagai versi resmi halaman.
    $canonicalUrl = rtrim(config('app.url'), '/').(request()->path() === '/' ? '' : '/'.request()->path());
@endphp

<title>{{ $metaTitle }}</title>
<link rel="icon" href="{{ $siteSetting->faviconUrl() ?? '/favicon.ico' }}" />
<meta name="description" content="{{ $metaDescription }}" />
<link rel="canonical" href="{{ $canonicalUrl }}" />

<meta property="og:type" content="website" />
<meta property="og:title" content="{{ $metaTitle }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:site_name" content="{{ $siteName }}" />
<meta name="twitter:card" content="summary" />

@stack('meta')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,400;0,600;0,700;1,600&family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
