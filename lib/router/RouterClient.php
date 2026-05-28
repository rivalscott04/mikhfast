<?php

class RouterClient
{
    private $api;

    public function __construct($api)
    {
        $this->api = $api;
    }

    /**
     * Comm helper with optional proplist to avoid N+1 patterns.
     *
     * Examples:
     * - comm("/ip/hotspot/user/print", array("?name" => "foo"), array(".id","name"))
     */
    public function comm($path, $params = array(), $proplist = null)
    {
        if ($proplist !== null) {
            if (is_array($proplist)) {
                $params[".proplist"] = implode(",", $proplist);
            } else {
                $params[".proplist"] = (string) $proplist;
            }
        }
        return $this->api->comm($path, $params);
    }

    public function write($command, $param2 = true)
    {
        return $this->api->write($command, $param2);
    }

    public function read($parse = true)
    {
        return $this->api->read($parse);
    }
}

?>
