<?php
require_once 'lib/PHPMailer/PHPMailerAutoload.php';
require_once 'models/emailNotification.php';


class EmailManager{

    private $mail;
    private $body;
    private $subject;

    public function setBody($body){
        $this->body = $body;
    }

    /**
     * Method to set the subject
     */
    public function setSubject($subject){
        $this->subject = $subject;
    }

    /**
     * Method  to add the reply to
     */
    public function addReplyTo($replies){
        foreach($replies as $reply){
            if (isset($reply['email']) && isset($reply['name']))
                $this->mail->addReplyTo($reply['email'],$reply['name']);
        }
    }


    private function initComponents(){
        // TCP port to connect to
        $this->mail = new PHPMailer;
        $this->mail->isSMTP();
        $this->mail->SMTPDebug 	= \Config\Config::$smtp_debug;                   // Enable verbose debug output
        // Set mailer to use SMTP
        $this->mail->Host 		= \Config\Config::$smtp_host;  						// Specify main and backup SMTP servers
        $this->mail->SMTPAuth 	= true;                               	// Enable SMTP authentication
        $this->mail->Username 	= \Config\Config::$smtp_username;   				// SMTP username
        $this->mail->Password 	= \Config\Config::$smtp_passwd;					// SMTP password
        $this->mail->SMTPSecure = \Config\Config::$smtp_secure;					// Enable TLS encryption, `ssl` also accepted
        $this->mail->Port 		= \Config\Config::$smtp_port;
    }

    /**
     * Method construct
     */
    public function __construct($subject = null,$body = null,$includeHeader = true, $includeFooter = true){
        $this->initComponents();
        $this->subject = $subject;

        $this->body = "";
        if ($includeHeader) $this->addHeader();
        $this->body.= "<div style=\"font-family:arial;\">$body</div>";
        if ($includeFooter) $this->addFooter();
    }

    /**
     * Method to replace patterns in emails
     */
    public function replacePatterns($patterns = [],$replacePatterns = [],$defReplace = true){
        $defPatterns = $defReplacePatterns = [];
        if ($defReplace){
            $defPatterns = array("{PROJECT}");
            $defReplacePatterns = array(\Config\Config::$projectName);
        }
        $patterns 			= array_merge($defPatterns,$patterns);
        $replacePatterns 	= array_merge($defReplacePatterns,$replacePatterns);

        $this->subject 	= str_replace($patterns,$replacePatterns,$this->subject);
        $this->body 	= str_replace($patterns,$replacePatterns,$this->body);
    }


    /**
     * Method to send email
     */
    public function send($receivers,$ccs = array(),$bccs = array()){
        $this->mail->From = \Config\Config::$smtp_username;
        $this->mail->FromName = \Config\Config::$smtp_fullname;

        $receivers = is_array($receivers) ? $receivers : array();
        $ccs = is_array($ccs) ? $ccs : array();
        $bccs = is_array($bccs) ? $bccs : array();

        foreach($receivers as $r)
            $this->mail->addAddress($r['email'], $r['name']);

        foreach($ccs as $cc)
            $this->mail->addCC($cc['email'], $cc['name']);

        foreach($bccs as $bcc)
            $this->mail->addBCC($bcc['email'], $bcc['name']);

        $this->mail->Subject = $this->subject;

        $this->body = "<html>
                         <head>
                            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\" />
                        </head>
                        <body>
                            {$this->body}
                        </body>
                        </html>";
        $this->mail->Body = $this->body;

        $this->mail->isHTML(true);



        if(!$this->mail->send()) {
            $logger = new \Core\CustomLogger();
            $logger->error($this->mail->ErrorInfo);
            return false;
        } else {
            //log the email
            $emailNotification = new EmailNotification();
            $emailNotification->subject = $this->mail->Subject;
            $emailNotification->body = $this->mail->Body;
            $emailNotification->emailFrom = $this->mail->From;

            $strEmail = "";
            foreach($this->mail->getToAddresses() as $email){
                $strEmail.= $strEmail ? "," : "";
                $strEmail.= $email[1] . "(" . $email[0] . ")";
            }
            $emailNotification->receivers = $strEmail;

            $strEmail = "";
            foreach($this->mail->getCcAddresses() as $email){
                $strEmail.= $strEmail ? "," : "";
                $strEmail.= $email[1] . "(" . $email[0] . ")";
            }
            $emailNotification->ccs = $strEmail;

            $strEmail = "";
            foreach($this->mail->getBccAddresses() as $email){
                $strEmail.= $strEmail ? "," : "";
                $strEmail.= $email[1] . "(" . $email[0] . ")";
            }
            $emailNotification->bccs = $strEmail;

            $strEmail = "";
            foreach($this->mail->getReplyToAddresses() as $email){
                $strEmail.= $strEmail ? "," : "";
                $strEmail.= $email[1] . "(" . $email[0] . ")";
            }
            $emailNotification->repliesTo = $strEmail;

            $emailNotification->add();
            return true;
        }
    }

    /**
     * Method to send a custom email
     */
    public function emailWithCustomTemplate($header,$footer){
        $this->body = $header .
            $this->body .
            $footer;
    }

    /**
     * Method to add header HTML to send email
     */
    private function addHeader(){
        $this->body .= \Config\Config::$email_templ_header;
    }

    /**
     * Method to add footer HTML to send email
     */
    private function addFooter(){
        $this->body .= \Config\Config::$email_templ_footer;
    }
}