<?php

namespace DataBundle\Utils;
use Doctrine\Bundle\DoctrineBundle\Registry;

class SystemInfo {
    private $doc;
    public function __construct(Registry $doc)
    {
        $this->doc = $doc;
    }
    public function cpuStatus()
    {
        $loadPercentage = 0;
        if(function_exists("sys_getloadavg"))
        {
            $load = sys_getloadavg();
            $loadPercentage = round($load[0]*100);
        }
        return $loadPercentage;
    }
    public function actualRoomPersons()
    {
        return $this->doc->getRepository('DataBundle:Room')->loadRoomActualSize();
    }
    public function actualRoomNonVipPersons()
    {
        return $this->doc->getRepository('DataBundle:Room')->loadRoomActualNonVipSize();
    }
    public function actualRoomVipPersons()
    {
        return $this->doc->getRepository('DataBundle:Room')->loadRoomActualVipSize();
    }
    public function sessionSells()
    {
        return $this->doc->getRepository('DataBundle:Sells')->loadSessionSolds();
    }
    public function sessionMediumPersonSells()
    {
        $actualRoomPersons = $this->actualRoomPersons();
        if($actualRoomPersons === 0)
        {
            $actualRoomPersons = 1;
        }
        return (int) $this->sessionSells()/$actualRoomPersons;
    }
    public function maxRoomPersons()
    {
        return $this->doc->getRepository('DataBundle:Room')->loadRoomMaxSize();
    }
    public function salesStatus()
    {
        return "TO_DO";
    }
    public function ramStatus()
    {
        $memory_usage = 0;
        $free = shell_exec('free');
	$free = (string)trim($free);
	$free_arr = explode("\n", $free);
        if(is_array($free_arr) && count($free_arr) > 0 && array_key_exists(1,$free_arr) && is_array($free_arr[1]))
        {
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);
            $memory_usage = $mem[2] / $mem[1] * 100;
        }
 
	return $memory_usage;
    }
    public function hddStatus()
    {
        $disktotal = disk_total_space('/');
	$diskfree  = disk_free_space ('/');
	$diskuse   = round (100 - (($diskfree / $disktotal) * 100));
	
	return $diskuse;
    }
    public function uptimeStatus()
    {
        $uptime = 0;
        if(file_exists("/proc/uptime"))
        {
            $uptime = floor(preg_replace ('/\.[0-9]+/', '', file_get_contents('/proc/uptime')) / 86400);
        }
	return $uptime;
    }
}
