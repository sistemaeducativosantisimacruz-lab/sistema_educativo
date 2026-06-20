<x-mail::message>
# Hola, {{ $user->name }}

Te escribimos para confirmarte que **la contraseña de tu cuenta en el Sistema Educativo ha sido modificada con éxito**.

Si fuiste tú quien realizó este cambio, puedes ignorar este mensaje.

<x-mail::panel>
**Detalles de la cuenta:**
- Nombre: {{ $user->name }}
- Correo / Usuario: {{ $user->email }}
- Fecha del cambio: {{ now()->translatedFormat('l, d \d\e F \d\e Y \a \l\a\s H:i') }}
</x-mail::panel>

Si **NO** realizaste este cambio, por favor contacta inmediatamente con la administración del colegio para proteger tu cuenta.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
