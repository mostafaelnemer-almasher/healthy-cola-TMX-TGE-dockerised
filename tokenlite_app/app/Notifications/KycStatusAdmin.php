<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycStatusAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    private $template;
    private $kuser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($template, $kuser)
    {
        $this->template = $template;
        $this->kuser = $kuser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $notifiable_users = $notifiable;

        $from_name = email_setting('from_name', get_setting('site_name'));
        $from_email = email_setting('from_email', get_setting('site_email'));

        $status = isset($this->kuser->status) ? $this->kuser->status : 'pending';
        $status = ($status == 'pending') ? 'submitted' : $status;
        $et_status = str_replace(['submitted', 'pending'], ['submit', 'submit'], $status);
        $notes = (($this->kuser->status == 'missing' || $this->kuser->status == 'rejected') && $this->kuser->notes != null) ? $this->kuser->notes : 'Wrong document/information';

        $et = EmailTemplate::get_template('kyc-submit-'.$this->template);

        $greeting = $et->greeting;
        $subject = $this->convert_shortcode($et->subject, $this->kuser);
        $et->message = $this->convert_shortcode($et->message, $this->kuser);
        $message = ($et_status == 'missing' || $et_status == 'rejected') ? replace_with($et->message, '[[message]]', "<strong>" . $notes . "</strong>") : $et->message;
        $et->regards = $et->regards == 'true' ? get_setting('site_mail_footer') : null;
        $regards = $et->regards != '' ? $this->convert_shortcode($et->regards, $this->kuser) : null;

        return (new MailMessage)
                    ->from($from_email, $from_name)
                    ->subject($subject)
                    ->markdown('mail.kyc.admin_submitted', [
                        'admin' => $notifiable_users,
                        'greeting' => $greeting,
                        'subject' => $subject,
                        'message' => $message,
                        'salutation' => $regards,
                        'status' => $status,
                    ]);
    }

    /**
     * Get the short-code and replace with data.
     *
     * @param  mixed  $code
     * @param  mixed  $notifiable
     * @return void
     */
    public function convert_shortcode($code, $kuser)
    {
        $shortcode =array(
            "\n",
            '[[site_name]]',
            '[[site_email]]',
            '[[user_name]]',
            '[[user_email]]',
            '[[user_id]]',
        );
        $replace = array(
            "<br>",
            site_info('name'),
            site_info('email'),
            $kuser->firstName . ' ' . $kuser->lastName,
            $kuser->email,
            set_id($kuser->userId),
        );

        $return = str_replace($shortcode, $replace, $code);
        return $return;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
