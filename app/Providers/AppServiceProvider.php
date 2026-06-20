<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $logoUrl = 'https://sistema-educativo-santisima-cruz.vercel.app/img/logo.png';
            $logo = new \Illuminate\Support\HtmlString('<div style="text-align: center;"><img src="'.$logoUrl.'" width="100" style="margin: 0 auto; display: inline-block; padding-bottom: 20px;" alt="Insignia Colegio"></div>');
            
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Restablecer Contraseña - Seguridad')
                ->line($logo)
                ->greeting('¡Hola!')
                ->line('Estás recibiendo este correo porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.')
                ->action('Restablecer Contraseña', url(route('password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false)))
                ->line('Este enlace de restablecimiento de contraseña caducará en 60 minutos.')
                ->line('Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna otra acción.')
                ->salutation('Saludos, ' . config('app.name'));
        });

        \Illuminate\Auth\Notifications\VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $logoUrl = 'https://sistema-educativo-santisima-cruz.vercel.app/img/logo.png';
            $logo = new \Illuminate\Support\HtmlString('<div style="text-align: center;"><img src="'.$logoUrl.'" width="100" style="margin: 0 auto; display: inline-block; padding-bottom: 20px;" alt="Insignia Colegio"></div>');
            
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Verificar Correo Electrónico - Seguridad')
                ->line($logo)
                ->greeting('¡Hola, ' . $notifiable->name . '!')
                ->line('Por favor, haz clic en el botón de abajo para verificar tu dirección de correo electrónico y asegurar tu cuenta.')
                ->action('Verificar Correo Electrónico', $url)
                ->line('Si no creaste una cuenta o no solicitaste este cambio, puedes ignorar este mensaje sin problemas.')
                ->salutation('Atentamente, ' . config('app.name'));
        });
    }
}
