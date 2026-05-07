<?php

namespace App\Providers;

use App\Models\Admin\MailGateway;
use Illuminate\Support\Facades\Config;
use App\Models\MailConfiguration;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            $mail = MailGateway::where('code', '101SMTP')->first();
            if ($mail) {
                $encryption = @$mail->credential->encryption === 'PWMTA' ? null : @$mail->credential->encryption;

                Config::set('mail.default', 'smtp');
                Config::set('mail.mailers.smtp.transport', 'smtp');
                Config::set('mail.mailers.smtp.host',       @$mail->credential->host);
                Config::set('mail.mailers.smtp.port',       @$mail->credential->port);
                Config::set('mail.mailers.smtp.encryption', $encryption);
                Config::set('mail.mailers.smtp.username',   @$mail->credential->username);
                Config::set('mail.mailers.smtp.password',   @$mail->credential->password);
                Config::set('mail.from.address',            @$mail->credential->from->address);
                Config::set('mail.from.name',               @$mail->credential->from->name);
            }
        } catch (\Exception $ex) {

        }
    }
}
