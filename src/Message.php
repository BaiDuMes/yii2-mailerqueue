<?php
namespace doctorgp\mailerqueue;

use yii;

class Message extends \yii\swiftmailer\Message {
    public function queue() {
        $redis = Yii::$app->redis;
        if (empty($redis)) {
            throw new yii\base\InvalidConfigException('redis not found in config.');
        }
        $mailer = Yii::$app->mailer;
        if (empty($mailer) || !$redis->select($mailer->db)) {
            throw new yii\base\InvalidConfigException('db not defined.');
        }
        $message = [];
        $message['from'] = array_keys($this->from);
        $message['to'] = array_keys($this->getTo());
        $message['cc'] = empty($this->getCc()) ? [] : array_keys($this->getCc());
        $message['bcc'] = empty($this->getBcc()) ? [] : array_keys($this->getBcc());
        $message['reply_to'] = empty($this->getReplyTo()) ? [] : array_keys($this->getReplyTo());
        $message['charset'] = array_keys(empty($this->getCharset()) ? [] : $this->getCharset());
        $message['subject'] = is_array($this->getSubject()) ? array_keys($this->getSubject()) :$this->getSubject();
        $parts = $this->getSwiftMessage()->getChildren();
        if (!is_array($parts) || !sizeof($parts)) {
            $parts = [$this->getSwiftMessage()];
        }
        foreach ($parts as $part) {
            if (!$part instanceof \Swift_Mime_Attachment) {
                switch ($part->getContentType()) {
                    case 'text/html':
                        $message['html_body'] = $part->getBody();
                        break;
                    case 'text/plain':
                        $message['text_body'] = $part->getBody();
                        break;
                }
                if (!$message['charset']) {
                    $message['charset'] = $part->getCharset();
                }
            }
        }
        return $redis->rpush($mailer->key, json_encode($message));
    }
}