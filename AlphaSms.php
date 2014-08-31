<?php
namespace richweber\alpha\sms;

class AlphaSms
{
    public $login = 'login';
    public $password = 'password';
    public $key = '';

    private $service = 'https://alphasms.com.ua/api/xml.php';

    public function test()
    {
        echo '<br>';
        echo $this->login;
        echo '<br>';
        echo $this->password;
        echo '<br>';
    }

    public function balance()
    {
        if (!$this->key) {
            # code...
        }

        $data = [
            'login' => $this->login,
            'password' => $this->password,
            'balance' => '',
        ];

        return $this->request($data);
    }

    private function request($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->service);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}