<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        $appName = \App\Models\Setting::get('app_name', config('app.name', 'FSM'));
        $metaTitle = \App\Models\Setting::get('meta_title', $appName);
        $metaDescription = \App\Models\Setting::get('meta_description', 'Repair Management System');
        $metaKeywords = \App\Models\Setting::get('meta_keywords', 'repair, service, management');
        $logoUrl = \App\Models\Setting::get('logo_url');
        $faviconUrl = \App\Models\Setting::get('favicon_url');
        $ogTitle = \App\Models\Setting::get('og_title', $metaTitle);
        $ogDescription = \App\Models\Setting::get('og_description', $metaDescription);
        $ogImage = \App\Models\Setting::get('og_image', $logoUrl);
        $twitterTitle = \App\Models\Setting::get('twitter_title', $ogTitle);
        $twitterDescription = \App\Models\Setting::get('twitter_description', $ogDescription);
        $twitterImage = \App\Models\Setting::get('twitter_image', $ogImage);
               $primaryColor = \App\Models\Setting::get('primary_color', '#3B82F6');
               $secondaryColor = \App\Models\Setting::get('secondary_color', '#1E40AF');
               $neutralColor = \App\Models\Setting::get('neutral_color', '#F1F5F9');
    @endphp
    
    <title>@yield('title', 'Admin Panel') - {{ $appName }}</title>
    
    <!-- Favicon -->
    @if($faviconUrl)
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
    @endif
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta property="og:type" content="website">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $twitterTitle }}">
    <meta name="twitter:description" content="{{ $twitterDescription }}">
    @if($twitterImage)
        <meta name="twitter:image" content="{{ $twitterImage }}">
    @endif
    
           <!-- Dynamic Colors -->
           <style>
               :root {
                   --primary-color: {{ $primaryColor }};
                   --secondary-color: {{ $secondaryColor }};
                   --neutral-color: {{ $neutralColor }};
               }
               .bg-primary { background-color: var(--primary-color) !important; }
               .text-primary { color: var(--primary-color) !important; }
               .border-primary { border-color: var(--primary-color) !important; }
               .bg-secondary { background-color: var(--secondary-color) !important; }
               .text-secondary { color: var(--secondary-color) !important; }
               .bg-neutral { background-color: var(--neutral-color) !important; }
               .text-neutral { color: var(--neutral-color) !important; }
           </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 antialiased selection:bg-blue-500 selection:text-white" style="font-family: 'Inter', sans-serif;">
    <!-- Mesh Gradient Background -->
    <div class="fixed inset-0 -z-10 h-full w-full bg-white [background:radial-gradient(125%_125%_at_50%_10%,#fff_40%,#6366f1_100%)] opacity-20"></div>
    
    <div class="min-h-screen flex">
        <!-- Glassmorphic Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 glass border-r border-slate-200/50 z-50 shadow-xl">
            <div class="h-full flex flex-col p-6">
                <div class="flex items-center gap-2 mb-10">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-10 w-auto max-w-[180px] object-contain">
                    @else
                        <span class="font-bold text-xl tracking-tight text-slate-800">{{ $appName }}</span>
                    @endif
                </div>

                <nav class="space-y-2 flex-1">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.triage.index') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.triage.*') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Triage Queue
                        @if(request()->routeIs('admin.triage.*'))
                            <span class="ml-auto bg-pulse-orange/10 text-pulse-orange py-0.5 px-2 rounded-md text-xs font-semibold timer-urgent">
                                {{ \App\Models\Ticket::whereIn('status', ['pending_triage', 'triage'])->count() }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('admin.customers.index') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.customers.*') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Customers
                    </a>
                    <a href="{{ route('admin.components.index') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.components.*') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Components
                    </a>
                    <a href="{{ route('admin.services.index') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.services.*') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Service Catalog
                    </a>
                    <a href="{{ route('admin.technicians.index') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.technicians.*') && !request()->routeIs('admin.technicians.map') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Technicians
                    </a>
                    <a href="{{ route('admin.technicians.map') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.technicians.map') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        Live Map
                    </a>
                    <a href="{{ route('admin.reports.index') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.reports.*') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Reports
                    </a>
                    <a href="{{ route('admin.settings.index') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm shadow-blue-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                </nav>

                <div class="pt-6 border-t border-slate-200">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    
    <!-- Footer -->
    @php
        $footerText = \App\Models\Setting::get('footer_text');
        $companyName = \App\Models\Setting::get('company_name');
    @endphp
    @if($footerText || $companyName)
        <footer class="bg-white border-t border-slate-200 mt-auto py-6">
            <div class="ml-64 px-8">
                <p class="text-sm text-slate-500 text-center">
                    @if($footerText)
                        {{ $footerText }}
                    @elseif($companyName)
                        © {{ date('Y') }} {{ $companyName }}. All rights reserved.
                    @endif
                </p>
            </div>
        </footer>
    @endif
</body>
</html>
