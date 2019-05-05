<?php

namespace bwt\App\Libs\Saver\Base;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use bwt\App\Libs\Saver\Base\Interfaces\NotifierInterface;

class Notifier implements NotifierInterface
{

    private $mailer;

    /**
     * Notifier constructor.
     * Set settings to PHPMailer library
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $settings['host'];
        $mailer->SMTPAuth = true;

        $mailer->Username = $settings['user'];
        $mailer->Password = $settings['password'];
        $mailer->SMTPSecure = $settings['encryption'];
        $mailer->Port = $settings['port'];

        $from = $settings['from'] ?? 'from@example.com';
        $mailer->setFrom($from);
        $to = $settings['recipient'];
        if(is_array($to)) {
            foreach($to as $val) {
                $mailer->addAddress($val);
            }
        } else {
            $mailer->addAddress($to);
        }

        $this->mailer = $mailer;
    }

    /**
     * Trying to send message
     * @param string $subject
     * @param string $message
     */
    public function send(string $subject, string $message)
    {
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $message;
        try {
            $this->mailer->send();
            echo "\n",date("H:i:s")," [m] Message has been sent", "\n";
        } catch(\Exception $e) {
            echo "[m] Message couldn\'t been sent. Mailer error: ", $this->mailer->ErrorInfo, "\n";
        }
    }
}
