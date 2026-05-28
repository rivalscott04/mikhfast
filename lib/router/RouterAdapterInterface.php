<?php

interface RouterAdapterInterface
{
    public function getIdentity();
    public function getSystemClock();
    public function getSystemResource();
    public function getRouterboard();
    public function getInterfaces();
    public function countHotspotUsers();
    public function countHotspotActive();

    public function monitorInterfaceTrafficOnce($interfaceName);
    public function getDhcpLeases();
    public function countDhcpLeases();

    public function ensureHotspotLoggingToDisk();
    public function getHotspotLogs($limit = 20);

    public function getHotspotLogsAll();

    public function getHotspotCookies();
    public function countHotspotCookies();
    public function getHotspotIpBindings();
    public function countHotspotIpBindings();

    public function getHotspotActive($server = null);
    public function countHotspotActiveFiltered($server = null);

    public function getHotspotUserByName($name);
    public function getHotspotUserProfileByName($profileName);

    public function removeHotspotHost($id);
    public function removeHotspotCookie($id);
    public function removeHotspotActive($id);
    public function setHotspotUserDisabled($id, $disabled);
    public function resetHotspotUserCounters($id);
    public function clearHotspotUserLimitsAndComment($id);
    public function removeSchedulerByName($name);
    public function setHotspotIpBindingDisabled($id, $disabled);
    public function removeHotspotIpBinding($id);

    public function removeScriptById($id);
    public function removePppActive($id);
    public function setSchedulerDisabled($id, $disabled);
    public function removeSchedulerById($id);

    public function getHotspotUsersExpired();
    public function getHotspotUsersByComment($comment);
    public function removeHotspotUserById($id);

    public function reboot();
    public function shutdown();

    public function removeHotspotUserProfileById($id);
}

?>
