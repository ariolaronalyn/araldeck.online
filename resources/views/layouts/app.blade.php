<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AralDeck') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/araldeck_solo_logo.png') }}">

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        {{-- Impersonation Alert --}}
        @if(session()->has('impersonator_id'))
            <div class="alert alert-warning rounded-0 mb-0 py-2 border-0 shadow-sm">
                <div class="container d-flex justify-content-between align-items-center">
                    <span>
                        <strong>Viewing as:</strong> {{ Auth::user()->name }} 
                        <span class="badge bg-dark ms-1">{{ strtoupper(Auth::user()->role) }}</span>
                    </span>
                    <form action="{{ route('admin.stop') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-dark">Return to Admin</button>
                    </form>
                </div>
            </div>
        @endif

        <nav class="navbar navbar-expand-md navbar-light bg-white sticky-top border-bottom">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('images/araldeck_full_logo.png') }}" alt="AralDeck Logo" style="height: 38px;">
                </a>
                
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <i class="bi bi-list fs-2"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        @auth
                            @php
                                $userRole = auth()->user()->role;
                                $isSuperAdmin = ($userRole === 'super_admin');
                                $isAdmin = in_array($userRole, ['admin', 'super_admin']);
                                $isEncoder = ($userRole === 'encoder');
                                
                                // Should we show the main navigation links?
                                $showMainNav = !is_null($userRole) && 
                                            !request()->routeIs('onboarding.*') && 
                                            !request()->routeIs('subscription.show_checkout') &&
                                            ($isAdmin || $isEncoder || !request()->routeIs('home'));
                            @endphp

                            @if($showMainNav)
                                {{-- Standard Links for Staff and Active Users --}}
                                <li class="nav-item mx-lg-2">
                                    <a class="nav-link px-3 rounded-pill {{ request()->routeIs('flashcards.index') ? 'bg-primary text-white active shadow-sm' : '' }}" href="{{ route('flashcards.index') }}">My Decks</a>
                                </li>

                                <li class="nav-item mx-lg-2">
                                    <a class="nav-link px-3 rounded-pill {{ request()->routeIs('flashcards.create_manual') ? 'bg-primary text-white active shadow-sm' : '' }}" href="{{ route('flashcards.create_manual') }}">Manual Entry</a>
                                </li>

                                <li class="nav-item mx-lg-2">
                                    <a class="nav-link px-3 rounded-pill {{ request()->routeIs('csv.form') ? 'bg-primary text-white active shadow-sm' : '' }}" href="{{ route('csv.form') }}">Bulk Upload</a>
                                </li>
                                
                                {{-- Classroom Logic (Admins and Teachers) --}}
                                @php
                                    $showClassroom = false;
                                    if($isAdmin || $userRole === 'teacher') {
                                        $showClassroom = true;
                                    } elseif($userRole === 'student') {
                                        $showClassroom = \DB::table('class_students')
                                                        ->where('student_id', auth()->id())
                                                        ->exists();
                                    }
                                @endphp

                                @if($showClassroom)
                                    <li class="nav-item mx-lg-2">
                                        <a class="nav-link px-3 rounded-pill {{ request()->routeIs('classroom.*') ? 'bg-primary text-white active shadow-sm' : '' }}" href="{{ route('classroom.index') }}">
                                            <i class="bi bi-mortarboard-fill me-1 {{ request()->routeIs('classroom.*') ? 'text-white' : 'text-primary' }}"></i> Classrooms
                                        </a>
                                    </li>
                                @endif

                                {{-- 🟢 SUPER ADMIN ONLY: User Management --}}
                                @if($isSuperAdmin)
                                    <li class="nav-item mx-lg-2 border-start ps-lg-3">
                                        <a class="nav-link {{ request()->routeIs('admin.users') ? 'active fw-bold text-danger' : '' }}" href="{{ route('admin.users') }}">
                                            <i class="bi bi-people-fill me-1"></i> Users
                                        </a>
                                    </li>
                                    <li class="nav-item mx-lg-2">
                                        <a class="nav-link px-3 rounded-pill {{ request()->routeIs('pdf.form') ? 'bg-primary text-white active' : '' }}" href="{{ route('pdf.form') }}">PDF Extractor</a>
                                    </li>
                                @endif
                            @endif
                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto align-items-center">
                        @guest
                            {{-- ONLY show Login if the current route is NOT login --}}
                            @if (Route::has('login') && !request()->routeIs('login'))
                                <li class="nav-item me-2">
                                    <a class="nav-link fw-bold text-dark px-3" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                            @if (Route::has('register') && !request()->routeIs('register'))
                                <li class="nav-item">
                                    <a class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" href="{{ route('register') }}">Get Started Free</a>
                                </li>
                            @endif
                        @else
                            {{-- Show these only if NOT onboarding/checkout --}}
                            @if(!is_null(auth()->user()->role) && 
                                !request()->routeIs('onboarding.*') && 
                                !request()->routeIs('subscription.show_checkout'))
                                
                                {{-- 1. Notifications (Bell) --}}
                                <li class="nav-item me-2">
                                    <a href="{{ route('invites.index') }}" class="nav-link position-relative">
                                        <i class="bi bi-bell-fill text-muted fs-5"></i>
                                        @php 
                                            $inviteCount = \App\Models\Collaboration::where('invited_user_id', Auth::id())
                                                            ->where('status', 'pending')->count();
                                        @endphp
                                        @if($inviteCount > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                                        @endif
                                    </a>
                                </li>

                                {{-- 2. Settings (Cogs) --}}
                                <li class="nav-item me-2">
                                    <a href="{{ route('settings.index') }}" class="nav-link" title="Settings">
                                        <i class="bi bi-gear-fill text-muted fs-5"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- 3. User Name & Role (Simple Text) --}}
                            <li class="nav-item me-3 d-none d-md-block">
                                <span class="small fw-bold text-dark bg-light px-3 py-2 rounded-pill">
                                    <i class="bi bi-person-circle me-1 text-primary"></i> 
                                    {{ Auth::user()->name }}
                                </span>
                            </li>

                            {{-- 4. Logout Button (Beside everything else) --}}
                            <li class="nav-item">
                                <a class="nav-link text-danger fw-bold" href="{{ route('logout') }}" 
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                title="Logout">
                                    <i class="bi bi-box-arrow-right fs-5"></i>
                                    <span class="d-md-none ms-1">Logout</span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <!-- @vite(['resources/js/app.js'])
    
    @stack('scripts') -->
    <style>
        .bi-gear-fill:hover {
            color: #0d6efd !important;
            transition: transform 0.3s ease;
            display: inline-block;
            transform: rotate(45deg);
        }
        .bi-box-arrow-right:hover {
            color: #dc3545 !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Manually initialize all dropdowns to ensure they work
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl)
            });
        });
    </script>
</body>
</html>