<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Администратор
 * Date: 29.06.12
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */
    class Sendemail {

        static function email($from, $to, $header, $body, $layout = 'common', $view = 'main') {

            Yii::app()->getModule('email');
            $email = new Email;

            $email->from = $from;
            $email->to = $to;
            $email->layout = $layout;
            $email->view = $view;
            $email->type = 'text/html';
            if($email->send(array('header' => $header, 'content' => $body))) {
                return true;
            }else {
                return false;
            }
        }

        static function sendConfirmEmail($email, $url) {

            return Sendemail::email('test@mail.ru', $email, 'сслыка для подтверждения email адресса', "сслыка для подтверждения email: <a href=\"$url\">Подтвердить</a>");
        }
    }