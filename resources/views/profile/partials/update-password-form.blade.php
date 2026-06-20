<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Actualizar Contraseña') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Asegúrese de que su cuenta utilice una contraseña larga y aleatoria para mantenerse segura.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        @php
            $user = auth()->user();
            $isDefaultEmail = $user->email === $user->dni || !str_contains($user->email, '@') || str_ends_with($user->email, '@sistema.edu') || str_ends_with($user->email, '@sistema.edu.pe');
            $hasVerifiedEmail = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && $user->hasVerifiedEmail() && !$isDefaultEmail;
        @endphp

        @if($hasVerifiedEmail)
            <div x-data="{ sending: false, sent: false }">
                <x-input-label for="otp_code" :value="__('Código de Verificación (Enviado al correo)')" />
                <div class="flex mt-1 gap-2">
                    <x-text-input id="otp_code" name="otp_code" type="text" class="block w-full" placeholder="Ej. 123456" />
                    <button type="button" 
                            @click="
                                sending = true;
                                fetch('{{ route('password.send_otp') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                }).then(res => res.json()).then(data => {
                                    sending = false;
                                    if(data.message) sent = true;
                                    alert(data.message || data.error);
                                });
                            "
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            x-bind:disabled="sending">
                        <span x-show="!sending && !sent">Enviar Código</span>
                        <span x-show="sending">Enviando...</span>
                        <span x-show="!sending && sent">Reenviar</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('otp_code')" class="mt-2" />
                <p class="text-xs text-green-600 mt-2" x-show="sent" style="display: none;">Se ha enviado un código a tu correo verificado.</p>
            </div>
        @else
            <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50">
                <span class="font-medium">Aviso de Seguridad:</span> Se recomienda configurar y verificar un correo electrónico en su perfil para cambiar contraseñas de forma más segura mediante un código de validación.
            </div>
            <div>
                <x-input-label for="update_password_current_password" :value="__('Contraseña Actual')" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>
        @endif

        <div>
            <x-input-label for="update_password_password" :value="__('Nueva Contraseña')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirmar Contraseña')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar') }}</x-primary-button>

            @if (session('status') === 'password-updated')
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
