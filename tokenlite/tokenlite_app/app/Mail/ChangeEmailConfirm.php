<?php

namespace App\Mail;

use App\Models\EmailTemplate;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangeEmailConfirm extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $user;
    private $extra;
    
    public function __construct($user,  $extra=null)
    {
        $this->user = $user;
        $this->extra = $extra;
    }

    /**
     * Build the message.
     *
     * @return $this
     * @version 1.8.0
     * @return void
     */
    public function build()
    {
        $from_name = email_setting('from_name', get_setting('site_name'));
        $from_email = email_setting('from_email', get_setting('site_email'));

        $support = get_setting('site_support_email') != '' ? get_setting('site_support_email') : $from_email;

        $et = EmailTemplate::get_template('users-confirm-password-email');

        $subject = $et->subject != '' ? replace_shortcode($et->subject) : 'Confirm Email on '.$from_name;
        $greeting = $et->greeting != '' ? $et->greeting : 'Hey, '.$this->user->name;
        $et->regards = ($et->regards == 'true' ? get_setting('site_mail_footer') : null);
        $regards = $et->regards != '' ? replace_shortcode($et->regards) : null;

        $et->message = replace_with($et->message, '[[user_name]]', "<strong>".$this->user->name."</strong>");
        $message = ($et->message != '') ? str_replace("\n", "<br>", replace_shortcode($et->message)) : $et->message;

        $extra = (isset($this->extra->password) ? 'Your Password is : **'. $this->extra->password . '**' : '---');

        return $this->from($from_email, $from_name)
        ->subject($subject)
        ->markdown('mail.base', [
            "greeting" => replace_shortcode(replace_with($greeting, '[[user_name]]', $this->user->name)),
            "__message" =>replace_with($message, '[[user_name]]', "<strong>".$this->user->name."</strong>"),
            "actionText" => 'Confirm Email Address', route('verify.email', ['id'=>$this->user->id, 'token'=>$this->user->meta->email_token]),
            "actionUrl" => route('verify.email', ['id'=>$this->user->id, 'token'=>$this->user->meta->email_token]),
            "level" => 'primary',
            "introLines" => [],
            "outroLines" => [],
            "user" => $this->user,
        ]);
    }
}
