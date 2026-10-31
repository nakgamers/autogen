<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if(auth()->user()?->isTeamLeader())
                    <x-nav-link :href="route('letters.create')" :active="request()->routeIs('letters.create')">
                        {{ __('Buat Surat') }}
                    </x-nav-link>
                    @endif
                    @if(auth()->user()?->isTeamLeader() || auth()->user()?->isDepartmentHead())
                    <x-nav-link :href="route('letters.index')" :active="request()->routeIs('letters.index')">
                        {{ __('Riwayat Surat') }}
                    </x-nav-link>
                    @endif
                    @if(auth()->user()?->isSuperAdmin())
                    <x-nav-link :href="route('bank-accounts.upload')" :active="request()->routeIs('bank-accounts.upload')">
                        {{ __('Upload Bank Account') }}
                    </x-nav-link>
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('Kelola Pengguna') }}
                    </x-nav-link>
                    <x-nav-link :href="route('rm-managers.index')" :active="request()->routeIs('rm-managers.*')">
                        {{ __('Kelola RM') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown (Desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>
                                    {{ Auth::user()->name }} 
                                    <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-semibold uppercase ml-1">
                                        {{ Auth::user()->roleLabel() }}
                                    </span>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 underline font-medium">Masuk / Login</a>
                @endauth
            </div>

            <!-- Hamburger Button (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(auth()->user()?->isTeamLeader())
            <x-responsive-nav-link :href="route('letters.create')" :active="request()->routeIs('letters.create')">
                {{ __('Buat Surat') }}
            </x-responsive-nav-link>
            @endif
            @if(auth()->user()?->isTeamLeader() || auth()->user()?->isDepartmentHead())
            <x-responsive-nav-link :href="route('letters.index')" :active="request()->routeIs('letters.index')">
                {{ __('Riwayat Surat') }}
            </x-responsive-nav-link>
            @endif
            @if(auth()->user()?->isSuperAdmin())
            <x-responsive-nav-link :href="route('bank-accounts.upload')" :active="request()->routeIs('bank-accounts.upload')">
                {{ __('Upload Bank Account') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('Kelola Pengguna') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('rm-managers.index')" :active="request()->routeIs('rm-managers.*')">
                {{ __('Kelola RM') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="px-4 py-2">
                    <a href="{{ route('login') }}" class="block text-base font-medium text-indigo-600">Silakan Login</a>
                </div>
            @endauth
        </div>
    </div>
</nav>