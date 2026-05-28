<?php

class Ros7Adapter implements RouterAdapterInterface
{
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function getIdentity()
    {
        // Same endpoint for now; kept separate for future divergence.
        $res = $this->client->comm("/system/identity/print", array(), array("name"));
        if (is_array($res) && isset($res[0]) && isset($res[0]["name"])) {
            return array("name" => $res[0]["name"]);
        }
        return array("name" => "");
    }

    public function getSystemClock()
    {
        $res = $this->client->comm("/system/clock/print", array(), array("date", "time", "time-zone-name"));
        return (is_array($res) && isset($res[0])) ? $res[0] : array();
    }

    public function getSystemResource()
    {
        $res = $this->client->comm(
            "/system/resource/print",
            array(),
            array(
                "uptime",
                "board-name",
                "version",
                "cpu-load",
                "cpu-frequency",
                "cpu-count",
                "free-memory",
                "total-memory",
                "free-hdd-space",
                "total-hdd-space",
            )
        );
        return (is_array($res) && isset($res[0])) ? $res[0] : array();
    }

    public function getRouterboard()
    {
        $res = $this->client->comm("/system/routerboard/print", array(), array("model"));
        return (is_array($res) && isset($res[0])) ? $res[0] : array();
    }

    public function getInterfaces()
    {
        return $this->client->comm("/interface/print", array(), array("name"));
    }

    public function countHotspotUsers()
    {
        return $this->client->comm("/ip/hotspot/user/print", array("count-only" => ""));
    }

    public function countHotspotActive()
    {
        return $this->client->comm("/ip/hotspot/active/print", array("count-only" => ""));
    }

    public function monitorInterfaceTrafficOnce($interfaceName)
    {
        return $this->client->comm("/interface/monitor-traffic", array(
            "interface" => (string) $interfaceName,
            "once" => "",
        ), array("tx-bits-per-second", "rx-bits-per-second"));
    }

    public function getDhcpLeases()
    {
        return $this->client->comm("/ip/dhcp-server/lease/print", array(), array(
            ".id",
            "address",
            "mac-address",
            "server",
            "active-address",
            "active-mac-address",
            "host-name",
            "status",
            "dynamic",
        ));
    }

    public function countDhcpLeases()
    {
        return $this->client->comm("/ip/dhcp-server/lease/print", array("count-only" => ""));
    }

    public function ensureHotspotLoggingToDisk()
    {
        $getlogging = $this->client->comm("/system/logging/print", array("?prefix" => "->"), array("prefix"));
        if (is_array($getlogging) && isset($getlogging[0]) && isset($getlogging[0]["prefix"]) && $getlogging[0]["prefix"] === "->") {
            return true;
        }
        $this->client->comm("/system/logging/add", array(
            "action" => "disk",
            "prefix" => "->",
            "topics" => "hotspot,info,debug",
        ));
        return true;
    }

    public function getHotspotLogs($limit = 20)
    {
        $rows = $this->client->comm("/log/print", array(
            "?topics" => "hotspot,info,debug",
        ), array("time", "message"));
        if (!is_array($rows)) {
            return array();
        }
        $rows = array_reverse($rows);
        return array_slice($rows, 0, (int) $limit);
    }

    public function getHotspotLogsAll()
    {
        $rows = $this->client->comm("/log/print", array(
            "?topics" => "hotspot,info,debug",
        ), array("time", "message"));
        return is_array($rows) ? array_reverse($rows) : array();
    }

    public function getHotspotCookies()
    {
        return $this->client->comm("/ip/hotspot/cookie/print", array(), array(
            ".id",
            "user",
            "mac-address",
            "domain",
            "expires-in",
        ));
    }

    public function countHotspotCookies()
    {
        return $this->client->comm("/ip/hotspot/cookie/print", array("count-only" => ""));
    }

    public function getHotspotIpBindings()
    {
        return $this->client->comm("/ip/hotspot/ip-binding/print", array(), array(
            ".id",
            "mac-address",
            "address",
            "to-address",
            "server",
            "comment",
            "disabled",
            "bypassed",
        ));
    }

    public function countHotspotIpBindings()
    {
        return $this->client->comm("/ip/hotspot/ip-binding/print", array("count-only" => ""));
    }

    public function getHotspotActive($server = null)
    {
        $params = array();
        if ($server !== null && $server !== "") {
            $params["?server"] = (string) $server;
        }
        return $this->client->comm("/ip/hotspot/active/print", $params, array(
            ".id",
            "server",
            "user",
            "address",
            "mac-address",
            "uptime",
            "session-time-left",
            "bytes-in",
            "bytes-out",
            "login-by",
            "comment",
        ));
    }

    public function countHotspotActiveFiltered($server = null)
    {
        $params = array("count-only" => "");
        if ($server !== null && $server !== "") {
            $params["?server"] = (string) $server;
        }
        return $this->client->comm("/ip/hotspot/active/print", $params);
    }

    public function getHotspotUserByName($name)
    {
        $res = $this->client->comm("/ip/hotspot/user/print", array(
            "?name" => (string) $name,
        ), array(
            "name",
            "profile",
            "comment",
            "uptime",
            "bytes-in",
            "bytes-out",
            "limit-uptime",
            "limit-bytes-total",
        ));
        return (is_array($res) && isset($res[0])) ? $res[0] : array();
    }

    public function getHotspotUserProfileByName($profileName)
    {
        $res = $this->client->comm("/ip/hotspot/user/profile/print", array(
            "?name" => (string) $profileName,
        ), array("on-login"));
        return (is_array($res) && isset($res[0])) ? $res[0] : array();
    }

    public function removeHotspotHost($id)
    {
        return $this->client->comm("/ip/hotspot/host/remove", array(".id" => (string) $id));
    }

    public function removeHotspotCookie($id)
    {
        return $this->client->comm("/ip/hotspot/cookie/remove", array(".id" => (string) $id));
    }

    public function removeHotspotActive($id)
    {
        return $this->client->comm("/ip/hotspot/active/remove", array(".id" => (string) $id));
    }

    public function setHotspotUserDisabled($id, $disabled)
    {
        return $this->client->comm("/ip/hotspot/user/set", array(
            ".id" => (string) $id,
            "disabled" => ($disabled ? "yes" : "no"),
        ));
    }

    public function resetHotspotUserCounters($id)
    {
        return $this->client->comm("/ip/hotspot/user/reset-counters", array(".id" => (string) $id));
    }

    public function clearHotspotUserLimitsAndComment($id)
    {
        return $this->client->comm("/ip/hotspot/user/set", array(
            ".id" => (string) $id,
            "limit-uptime" => "0",
            "comment" => "",
        ));
    }

    public function removeSchedulerByName($name)
    {
        return $this->client->comm("/system/scheduler/remove", array(
            "numbers" => (string) $name,
        ));
    }

    public function setHotspotIpBindingDisabled($id, $disabled)
    {
        return $this->client->comm("/ip/hotspot/ip-binding/set", array(
            ".id" => (string) $id,
            "disabled" => ($disabled ? "yes" : "no"),
        ));
    }

    public function removeHotspotIpBinding($id)
    {
        return $this->client->comm("/ip/hotspot/ip-binding/remove", array(".id" => (string) $id));
    }

    public function removeScriptById($id)
    {
        return $this->client->comm("/system/script/remove", array(".id" => (string) $id));
    }

    public function removePppActive($id)
    {
        return $this->client->comm("/ppp/active/remove", array(".id" => (string) $id));
    }

    public function setSchedulerDisabled($id, $disabled)
    {
        return $this->client->comm("/system/scheduler/set", array(
            ".id" => (string) $id,
            "disabled" => ($disabled ? "yes" : "no"),
        ));
    }

    public function removeSchedulerById($id)
    {
        return $this->client->comm("/system/scheduler/remove", array(".id" => (string) $id));
    }

    public function getHotspotUsersExpired()
    {
        return $this->client->comm("/ip/hotspot/user/print", array(
            "?limit-uptime" => "1s",
        ), array(".id", "profile"));
    }

    public function getHotspotUsersByComment($comment)
    {
        return $this->client->comm("/ip/hotspot/user/print", array(
            "?comment" => (string) $comment,
            "?uptime" => "00:00:00",
        ), array(".id", "profile"));
    }

    public function removeHotspotUserById($id)
    {
        return $this->client->comm("/ip/hotspot/user/remove", array(".id" => (string) $id));
    }

    public function reboot()
    {
        $this->client->write("/system/reboot");
        return $this->client->read();
    }

    public function shutdown()
    {
        $this->client->write("/system/shutdown");
        return $this->client->read();
    }

    public function removeHotspotUserProfileById($id)
    {
        return $this->client->comm("/ip/hotspot/user/profile/remove", array(".id" => (string) $id));
    }
}

?>
