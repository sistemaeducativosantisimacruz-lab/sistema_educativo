<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex">
                    <!-- Inicio -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Inicio') }}
                    </x-nav-link>

                    <!-- ESTUDIANTE -->
                    @if(auth()->user()->role?->nombre === 'estudiante')
                        <x-nav-link :href="route('estudiante.cursos.index')" :active="request()->routeIs('estudiante.cursos.*')">
                            {{ __('Mis Cursos') }}
                        </x-nav-link>
                    @endif

                    <!-- DOCENTE -->
                    @if(auth()->user()->role?->nombre === 'docente')
                        <x-nav-link :href="route('docente.admision.index')" :active="request()->routeIs('docente.admision.*')">
                            <span class="inline-flex items-center gap-1.5">
                                {{ __('Admisión') }}
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
                                    2027
                                </span>
                            </span>
                        </x-nav-link>
                        <x-nav-link :href="route('docente.secciones')" :active="request()->routeIs('docente.secciones')">
                            {{ __('Secciones a cargo') }}
                        </x-nav-link>
                        @if(auth()->user()->docente && auth()->user()->docente->tutoriaSecciones()->count() > 0)
                            <x-nav-link :href="route('docente.importar')" :active="request()->routeIs('docente.importar')">
                                {{ __('Importar Notas') }}
                            </x-nav-link>
                        @endif
                    @endif

                    <!-- ADMINISTRADOR -->
                    @if(auth()->user()->role?->nombre === 'admin')
                        <!-- 1. Admisión 2027 (Directo y destacado) -->
                        <x-nav-link :href="route('admin.admision.index')" :active="request()->routeIs('admin.admision.*')">
                            <span class="inline-flex items-center gap-1.5">
                                {{ __('Admisión') }}
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
                                    2027
                                </span>
                            </span>
                        </x-nav-link>

                        <!-- 2. Gestión Escolar (Dropdown) -->
                        <div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" type="button" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 h-full transition duration-150 ease-in-out {{ request()->routeIs(['admin.estudiantes.*', 'admin.docentes.*', 'admin.grado-secciones.*']) ? 'border-indigo-500 text-gray-900 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <span>{{ __('Gestión Escolar') }}</span>
                                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400 transition-transform duration-150" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute left-0 top-full mt-1 w-52 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                                <a href="{{ route('admin.estudiantes.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ request()->routeIs('admin.estudiantes.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    {{ __('Estudiantes') }}
                                </a>
                                <a href="{{ route('admin.docentes.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ request()->routeIs('admin.docentes.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                                    {{ __('Docentes') }}
                                </a>
                                <a href="{{ route('admin.grado-secciones.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ request()->routeIs('admin.grado-secciones.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ __('Secciones y Aulas') }}
                                </a>
                            </div>
                        </div>

                        <!-- 3. Evaluación & Notas (Dropdown) -->
                        <div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" type="button" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 h-full transition duration-150 ease-in-out {{ request()->routeIs(['admin.analytics.*', 'admin.rendimiento.*', 'admin.importar', 'admin.importaciones.*']) ? 'border-indigo-500 text-gray-900 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <span>{{ __('Evaluación & Notas') }}</span>
                                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400 transition-transform duration-150" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute left-0 top-full mt-1 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                                <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    {{ __('Analítica Académica') }}
                                </a>
                                <a href="{{ route('admin.rendimiento.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ request()->routeIs('admin.rendimiento.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ __('Rendimiento Escolar') }}
                                </a>
                                <a href="{{ route('admin.importar') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ request()->routeIs('admin.importar') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    {{ __('Importar Notas SIAGIE') }}
                                </a>
                            </div>
                        </div>

                        <!-- 4. Mensualidades (Directo) -->
                        <x-nav-link :href="route('admin.mensualidades.index')" :active="request()->routeIs('admin.mensualidades.*')">
                            {{ __('Mensualidades') }}
                        </x-nav-link>

                        <!-- 5. Configuración (Dropdown) -->
                        <div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" type="button" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 h-full transition duration-150 ease-in-out {{ request()->routeIs(['admin.bimestres.*', 'admin.anos-lectivos.*']) ? 'border-indigo-500 text-gray-900 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <span>{{ __('Configuración') }}</span>
                                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400 transition-transform duration-150" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute right-0 top-full mt-1 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50">
                                <a href="{{ route('admin.bimestres.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ request()->routeIs('admin.bimestres.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ __('Bimestres') }}
                                </a>
                                <a href="{{ route('admin.anos-lectivos.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm {{ request()->routeIs('admin.anos-lectivos.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ __('Años Lectivos') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Mi Perfil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Botón Hamburguesa Móvil -->
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

    <!-- Menú Responsive Móvil -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Inicio') }}
            </x-responsive-nav-link>

            @if(auth()->user()->role?->nombre === 'estudiante')
                <x-responsive-nav-link :href="route('estudiante.cursos.index')" :active="request()->routeIs('estudiante.cursos.*')">
                    {{ __('Mis Cursos') }}
                </x-responsive-nav-link>
            @endif

            @if(auth()->user()->role?->nombre === 'docente')
                <x-responsive-nav-link :href="route('docente.admision.index')" :active="request()->routeIs('docente.admision.*')">
                    {{ __('Admisión 2027') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('docente.secciones')" :active="request()->routeIs('docente.secciones')">
                    {{ __('Secciones a cargo') }}
                </x-responsive-nav-link>
                @if(auth()->user()->docente && auth()->user()->docente->tutoriaSecciones()->count() > 0)
                    <x-responsive-nav-link :href="route('docente.importar')" :active="request()->routeIs('docente.importar')">
                        {{ __('Importar Notas') }}
                    </x-responsive-nav-link>
                @endif
            @endif

            @if(auth()->user()->role?->nombre === 'admin')
                <!-- Admisión 2027 -->
                <x-responsive-nav-link :href="route('admin.admision.index')" :active="request()->routeIs('admin.admision.*')">
                    <span class="flex items-center justify-between">
                        <span>{{ __('Admisión') }}</span>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-700">2027</span>
                    </span>
                </x-responsive-nav-link>

                <!-- Gestión Escolar -->
                <div class="pt-2 pb-1 px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    {{ __('Gestión Escolar') }}
                </div>
                <x-responsive-nav-link :href="route('admin.estudiantes.index')" :active="request()->routeIs('admin.estudiantes.*')">
                    {{ __('Estudiantes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.docentes.index')" :active="request()->routeIs('admin.docentes.*')">
                    {{ __('Docentes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.grado-secciones.index')" :active="request()->routeIs('admin.grado-secciones.*')">
                    {{ __('Secciones y Aulas') }}
                </x-responsive-nav-link>

                <!-- Evaluación & Notas -->
                <div class="pt-2 pb-1 px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    {{ __('Evaluación & Notas') }}
                </div>
                <x-responsive-nav-link :href="route('admin.analytics.index')" :active="request()->routeIs('admin.analytics.*')">
                    {{ __('Analítica Académica') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.rendimiento.index')" :active="request()->routeIs('admin.rendimiento.*')">
                    {{ __('Rendimiento Escolar') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.importar')" :active="request()->routeIs('admin.importar')">
                    {{ __('Importar Notas SIAGIE') }}
                </x-responsive-nav-link>

                <!-- Mensualidades -->
                <div class="pt-2 pb-1 px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    {{ __('Finanzas') }}
                </div>
                <x-responsive-nav-link :href="route('admin.mensualidades.index')" :active="request()->routeIs('admin.mensualidades.*')">
                    {{ __('Mensualidades') }}
                </x-responsive-nav-link>

                <!-- Configuración -->
                <div class="pt-2 pb-1 px-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    {{ __('Configuración') }}
                </div>
                <x-responsive-nav-link :href="route('admin.bimestres.index')" :active="request()->routeIs('admin.bimestres.*')">
                    {{ __('Bimestres') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.anos-lectivos.index')" :active="request()->routeIs('admin.anos-lectivos.*')">
                    {{ __('Años Lectivos') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Mi Perfil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
