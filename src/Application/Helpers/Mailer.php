<?php
class Mailer
{
    #region Vars
    private $to;
    private $from = DEFAULT_FROM;
    private $subject;
    private $body;
    private $type = "text/html";
    private $logs_mail = "";
    #endregion

    #region Constructores
    public function __construct($pTo, $pSubject, $pBody, $pType = "text/html")
    {
        $this->to = $pTo;
        $this->subject = $pSubject;
        $this->body = $pBody;
        $this->type = $pType;
    }
    #endregion

    #region Setters
    public function setTo($pTo)
    {
        $this->to = $pTo;
    }
    public function setSubject($pSubject)
    {
        $this->subject = $pSubject;
    }
    public function setBody($pBody)
    {
        $this->body = $pBody;
    }
    public function setType($pType)
    {
        $this->type = $pType;
    }

    public function getMessageMail()
    {
        return $this->logs_mail;
    }
    #endregion

    #region Methods
    /**
     * @return bool
     */
    public function Send()
    {
        $this->logs_mail = "";
        $statusOk = false;

        try 
        {
            //Create a new PHPMailer instance
            $mail = new PHPMailer();
            //Tell PHPMailer to use SMTP
            $mail->isSMTP();
            //Set the hostname of the mail server
            $mail->Host = SMTP_SERVER;
            //Set the SMTP port number - likely to be 25, 465 or 587
            $mail->Port = SMTP_PORT;
            // Set Helo for Gmail / Hotmail / Yahoo
            $mail->Helo = SMTP_HELO;
            // 0 (No debug) / 1 (Debug)
            $mail->SMTPDebug  = SMTP_DEBUG;
            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //Set the encryption system to use - ssl (deprecated) or tls
            //$mail->SMTPSecure = 'tls';
            //Username to use for SMTP authentication
            $mail->Username = SMTP_USER;
            //Password to use for SMTP authentication
            $mail->Password = SMTP_PASS;

            //Set who the message is to be sent from
            $mail->setFrom($this->from, $this->from);

            // Receive a backup mail
            if (SMTP_USER == $this->to)
            {
                $mail->addCC(SMTP_BACKUP_MAIL, 'Confetimail Backup');
            }

            //Set who the message is to be sent to
            $mail->addAddress($this->to);

            //$mail->WordWrap = 100;
            $mail->Priority = 1;
            $mail->isHTML(true);

            //Set the subject line
            $mail->Subject = $this->subject;
            $mail->AltBody = "Hola, si no ves correctamente este correo, es que tu cliente electrónico no soporta HTML";

            $mail->msgHTML(html_entity_decode(stripslashes($this->body)));

            $mail->Encoding = "base64";
            $mail->CharSet = "UTF-8";

            //send the message, check for errors
            if (!$mail->send()) {
                $this->logs_mail .= sprintf("
                No se ha podido enviar un mail a %s con asunto %s\n<br />
                Servidor: %s, Puerto: %s\n<br />
                Error: %s", $this->to, $this->subject, SMTP_SERVER, SMTP_PORT, $mail->ErrorInfo);
            } else {
                $this->logs_mail .= sprintf("Mail enviado a %s con asunto %s correcto\n", $this->to, $this->subject);
                $statusOk = true;
            }
        }
        catch (phpmailerException $e)
        {
            $this->logs_mail .= sprintf("No se ha podido enviar un mail a %s con asunto %s\n", $this->to, $this->subject);
            $this->logs_mail .= $e->errorMessage(); //Pretty error messages from PHPMailer
            $this->logs_mail .= $e->getMessage(); //Pretty error messages from PHPMailer
            $this->logs_mail .= $e->getTraceAsString(); //Pretty error messages from PHPMailer
        }
        catch (Exception $e)
        {
            $this->logs_mail .= sprintf("No se ha podido enviar un mail a %s con asunto %s\n", $this->to, $this->subject);
            $this->logs_mail .= $e->getMessage(); //Pretty error messages from PHPMailer
            $this->logs_mail .= $e->getTraceAsString(); //Pretty error messages from PHPMailer
        }

        return $statusOk;
    }
    #endregion
}
?>