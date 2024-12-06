<?php

namespace App\Mail;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $leave;
    public $role;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Leave $leave, $role)
    {
        $this->leave = $leave;
        $this->role = $role;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.leave_notification')
            ->subject('Leave Notification')
            ->with([
                'leave' => $this->leave,
                'role' => $this->role,
            ]);
    }
}
