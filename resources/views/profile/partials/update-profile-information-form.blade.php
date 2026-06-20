<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Información del Perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Actualice la información de su perfil de cuenta y dirección de correo electrónico.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-gray-100 text-gray-500 cursor-not-allowed" :value="old('name', $user->name)" readonly />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
            <p class="text-xs text-gray-500 mt-1">El nombre no puede ser modificado por el usuario.</p>
        </div>

        @if ($user->estudiante)
            <div>
                <x-input-label for="sexo" :value="__('Sexo')" />
                <select id="sexo" name="sexo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="M" {{ old('sexo', $user->estudiante->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('sexo', $user->estudiante->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('sexo')" />
                <p class="text-xs text-gray-500 mt-1">Actualice su género si fue registrado incorrectamente.</p>
            </div>
        @endif

        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            @php
                $isDefault = $user->email === $user->dni || !str_contains($user->email, '@') || str_ends_with($user->email, '@sistema.edu') || str_ends_with($user->email, '@sistema.edu.pe');
                $displayEmail = $isDefault ? '' : $user->email;
            @endphp
            <div class="relative">
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full pr-10" :value="old('email', $displayEmail)" autocomplete="username" />
                @if (!$isDefault && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && $user->hasVerifiedEmail())
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" title="Correo verificado">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                @endif
            </div>

            @if ($isDefault)
                <p class="text-xs text-amber-600 mt-1 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Añade un correo real para habilitar notificaciones y recuperación de cuenta.
                </p>
            @endif
            
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (!$isDefault && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800 flex items-center font-medium">
                        <svg class="w-4 h-4 mr-1.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Correo no verificado
                    </p>
                    <p class="text-xs text-red-600 mt-1 mb-2">
                        Verifica tu correo para asegurar tu cuenta y poder recuperar tu contraseña.
                    </p>
                    <button form="send-verification" class="text-xs font-semibold text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        {{ __('Reenviar enlace de verificación') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-xs text-green-600 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ __('Enlace enviado exitosamente.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Guardado.') }}</p>
            @endif
        </div>
    </form>
</section>
