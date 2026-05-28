<?php

require_once(__DIR__ . "/RouterClient.php");
require_once(__DIR__ . "/RouterAdapterInterface.php");
require_once(__DIR__ . "/Ros6Adapter.php");
require_once(__DIR__ . "/Ros7Adapter.php");

class RouterService
{
    private $client;
    private $adapter;
    private $sessionId;

    public function __construct($api, $rosMajorVersion = null, $sessionId = null)
    {
        $this->client = new RouterClient($api);
        $this->sessionId = $sessionId;

        if ($rosMajorVersion === null) {
            $rosMajorVersion = $this->getRosMajorVersion();
        }

        if ((string) $rosMajorVersion === "7") {
            $this->adapter = new Ros7Adapter($this->client);
        } else {
            $this->adapter = new Ros6Adapter($this->client);
        }
    }

    public function getRosMajorVersion()
    {
        $cacheKey = $this->sessionId ? ("ros_major:" . $this->sessionId) : "ros_major";
        if (isset($_SESSION) && isset($_SESSION[$cacheKey]) && $_SESSION[$cacheKey] !== "") {
            return (string) $_SESSION[$cacheKey];
        }

        $res = $this->client->comm("/system/resource/print", array(), array("version"));
        $versionStr = "";
        if (is_array($res) && isset($res[0]) && isset($res[0]["version"])) {
            $versionStr = (string) $res[0]["version"];
        }
        $major = "";
        if ($versionStr !== "") {
            $major = substr($versionStr, 0, 1);
        }

        if (isset($_SESSION)) {
            $_SESSION[$cacheKey] = $major;
        }
        return $major;
    }

    /**
     * Identity is a single request; uses proplist to keep payload minimal.
     */
    public function getIdentity()
    {
        return $this->adapter->getIdentity();
    }

    public function getSystemClock()
    {
        return $this->adapter->getSystemClock();
    }

    public function getSystemResource()
    {
        return $this->adapter->getSystemResource();
    }

    public function getRouterboard()
    {
        return $this->adapter->getRouterboard();
    }

    public function getInterfaces()
    {
        return $this->adapter->getInterfaces();
    }

    public function countHotspotUsers()
    {
        return $this->adapter->countHotspotUsers();
    }

    public function countHotspotActive()
    {
        return $this->adapter->countHotspotActive();
    }

    public function monitorInterfaceTrafficOnce($interfaceName)
    {
        return $this->adapter->monitorInterfaceTrafficOnce($interfaceName);
    }

    public function getDhcpLeases()
    {
        return $this->adapter->getDhcpLeases();
    }

    public function countDhcpLeases()
    {
        return $this->adapter->countDhcpLeases();
    }

    public function ensureHotspotLoggingToDisk()
    {
        return $this->adapter->ensureHotspotLoggingToDisk();
    }

    public function getHotspotLogs($limit = 20)
    {
        return $this->adapter->getHotspotLogs($limit);
    }

    public function getHotspotLogsAll()
    {
        return $this->adapter->getHotspotLogsAll();
    }

    public function getHotspotCookies()
    {
        return $this->adapter->getHotspotCookies();
    }

    public function countHotspotCookies()
    {
        return $this->adapter->countHotspotCookies();
    }

    public function getHotspotIpBindings()
    {
        return $this->adapter->getHotspotIpBindings();
    }

    public function countHotspotIpBindings()
    {
        return $this->adapter->countHotspotIpBindings();
    }

    public function getHotspotActive($server = null)
    {
        return $this->adapter->getHotspotActive($server);
    }

    public function countHotspotActiveFiltered($server = null)
    {
        return $this->adapter->countHotspotActiveFiltered($server);
    }

    public function getHotspotUserByName($name)
    {
        return $this->adapter->getHotspotUserByName($name);
    }

    public function getHotspotUserProfileByName($profileName)
    {
        return $this->adapter->getHotspotUserProfileByName($profileName);
    }

    public function removeHotspotHost($id)
    {
        return $this->adapter->removeHotspotHost($id);
    }

    public function removeHotspotCookie($id)
    {
        return $this->adapter->removeHotspotCookie($id);
    }

    public function removeHotspotActive($id)
    {
        return $this->adapter->removeHotspotActive($id);
    }

    public function setHotspotUserDisabled($id, $disabled)
    {
        return $this->adapter->setHotspotUserDisabled($id, $disabled);
    }

    public function resetHotspotUserCounters($id)
    {
        return $this->adapter->resetHotspotUserCounters($id);
    }

    public function clearHotspotUserLimitsAndComment($id)
    {
        return $this->adapter->clearHotspotUserLimitsAndComment($id);
    }

    public function removeSchedulerByName($name)
    {
        return $this->adapter->removeSchedulerByName($name);
    }

    public function setHotspotIpBindingDisabled($id, $disabled)
    {
        return $this->adapter->setHotspotIpBindingDisabled($id, $disabled);
    }

    public function removeHotspotIpBinding($id)
    {
        return $this->adapter->removeHotspotIpBinding($id);
    }

    public function removeScriptById($id)
    {
        return $this->adapter->removeScriptById($id);
    }

    public function removePppActive($id)
    {
        return $this->adapter->removePppActive($id);
    }

    public function setSchedulerDisabled($id, $disabled)
    {
        return $this->adapter->setSchedulerDisabled($id, $disabled);
    }

    public function removeSchedulerById($id)
    {
        return $this->adapter->removeSchedulerById($id);
    }

    public function getHotspotUsersExpired()
    {
        return $this->adapter->getHotspotUsersExpired();
    }

    public function getHotspotUsersByComment($comment)
    {
        return $this->adapter->getHotspotUsersByComment($comment);
    }

    public function removeHotspotUserById($id)
    {
        return $this->adapter->removeHotspotUserById($id);
    }

    public function reboot()
    {
        return $this->adapter->reboot();
    }

    public function shutdown()
    {
        return $this->adapter->shutdown();
    }

    public function removeHotspotUserProfileById($id)
    {
        return $this->adapter->removeHotspotUserProfileById($id);
    }
}

?>
