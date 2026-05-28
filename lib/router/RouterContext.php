<?php

class RouterContext
{
    public $session;
    public $ip;
    public $user;
    public $pass;

    public $rosVersion = null; // "6", "7", or null (unknown yet)
    public $capabilities = array(); // cached feature probes later

    public function __construct($session, $ip, $user, $pass)
    {
        $this->session = $session;
        $this->ip = $ip;
        $this->user = $user;
        $this->pass = $pass;
    }
}

?>
