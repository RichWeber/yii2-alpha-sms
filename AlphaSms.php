<?php
namespace richweber\yii\alpha\sms;

class AlphaSms
{
    public $login = 'login';
    public $password = 'password';

    public function test()
    {
        echo '<br>';
        echo $this->login;
        echo '<br>';
        echo $this->password;
        echo '<br>';
    }
}