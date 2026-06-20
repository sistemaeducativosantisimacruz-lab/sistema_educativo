<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 bg-blue-50 border border-blue-200 p-4 rounded-lg">
        <p class="font-bold text-blue-800 mb-2">{{ __('Por razones de seguridad, debes cambiar tu contraseña antes de continuar.') }}</p>
        <p class="mb-2">Tu contraseña actual es tu número de DNI.</p>
        <p class="font-semibold mt-2">La nueva contraseña debe cumplir los siguientes requisitos:</p>
        <ul class="list-disc pl-5 mt-1">
            <li>Mínimo 8 caracteres</li>
            <li>Al menos una letra mayúscula y una minúscula</li>
            <li>Al menos un número</li>
            <li>Al menos un carácter especial (ej. @, $, !, %, *, ?, &)</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('password.update_forced') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Nueva Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="bg-yellow-600 hover:bg-yellow-700">
                {{ __('Cambiar Contraseña') }}
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('password.skip_forced') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-500 hover:text-gray-800 underline">
            {{ __('Omitir por ahora') }}
        </button>
    </form>
</x-guest-layout>
