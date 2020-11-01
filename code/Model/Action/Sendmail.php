<?php

namespace Model\Action;

use PHPMailer\PHPMailer\PHPMailer;
use Infr;

class Sendmail extends \Model\Action {

    static protected $fromEmail = 'robot@botman.one';
    static protected $fromName = 'Botman.one';

    protected $_emailsTo = [];
    protected $_conclusionId;

    protected $_conclusionText = '';

    protected $_algorithmHeader;

    static public function newFromArray($data = []) {
        /* @var \Model\Action\Sendmail $entity */
        $entity = parent::newFromArray($data);

        $entity->_conclusionId = new \PT\ConclusionId($data['settings']['conclusion_id']);

        $emails = explode(',', $data['settings']['email_to']);

        foreach ($emails as $email) {
            $entity->_emailsTo[] = trim($email);
        }

        return $entity;
    }


    protected function initAction() {

        $conclusion = $this->_executor->getResult()->getConclusion($this->_conclusionId);

        $this->_algorithmHeader = $this->_executor->getAlgorithm()->header;

        if (!$conclusion)
            throw new \Exception("Conclusion id: {$this->_conclusionId} not found");

        $this->_executor->getResult()->assignTemplate();

        $conclusionText = $conclusion->render();
        $this->_conclusionText = is_array($conclusionText)?$conclusionText['text']:$conclusionText;  // Проверка на RENDER_MODE_ARRAY

        parent::initAction();
    }

    protected function doAction() {

        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = 4;                                 // Enable verbose debug output

            $smtp = Infr\Config::getSMTP();


            $mail->set('CharSet', PHPMailer::CHARSET_UTF8);

            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $smtp['host'];
            $mail->Port = $smtp['port'];

            $mail->SMTPAutoTLS = $smtp['tls'];


            if (!is_null($smtp['user']) and !is_null($smtp['password'])) {
                $mail->SMTPAuth = true;

                $mail->Username = $smtp['user'];
                $mail->Password = $smtp['password'];
            }


            $mail->setFrom(self::$fromEmail, self::$fromName);

            foreach ($this->_emailsTo as $email)
                $mail->addAddress($email);


            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $this->_algorithmHeader;
            $mail->Body    = $this->_conclusionText;

            $mail->send();

        } catch (\Exception $e) {
            Infr\ErrorLogger::mailError($e->getMessage());
            return false;
        }

        return true;
    }


    protected function makeHash() {
        return md5($this->_conclusionText);
    }
}