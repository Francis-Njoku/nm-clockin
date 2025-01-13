<?php
namespace App\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;

class UserAccountNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $credentials;
    public $role;

    public function __construct(array $credentials, string $role)
    {
        $this->credentials = $credentials;
        $this->role        = $role;
    }

    public function build()
    {
        try {
            return $this->markdown('emails.user_account_notification')
                ->subject('Welcome to NM Clockin - Your Account Credentials')
                ->with([
                    'credentials' => $this->credentials,
                    'role' => $this->role,
                ]);
        } catch (\Exception $e) {
            Log::error('Failed to build user account notification email', [
                'error' => $e->getMessage(),
                'user_email' => $this->credentials['email'] ?? null
            ]);
            throw $e;
        }
    }
}
