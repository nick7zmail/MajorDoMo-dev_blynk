<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'dev_blynk/dev_blynk.class.php');
$dev_blynk_module = new dev_blynk();
$dev_blynk_module->getConfig();

$old_second = date('s');
$old_minute = date('i');
$old_hour = date('h');

$tmp = SQLSelectOne("SELECT ID FROM blynk_devices LIMIT 1");
if (!$tmp['ID'])
   exit; // no devices added -- no need to run this cycle
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;
$latest_check=0;
$checkEvery=5; // poll every 5 seconds
while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   if ((time()-$latest_check)>$checkEvery) {
    $latest_check=time();
	echo date('Y-m-d H:i:s').' Polling devices...';
	$s = date('s');
   	$m = date('i');
	$h = date('h');
		if ($s != $old_second)
	   {
			$dev_blynk_module->processCycle('5s');
			if($s20>=20) {
				$dev_blynk_module->processCycle('20s');
				$s20=0;
			} else {
				$s20=$s20+5;
			}
			$old_second = $s;
	   }	
	   if ($m != $old_minute)
	   {
			$dev_blynk_module->processCycle('1m');
			$old_minute = $m;
			if($m10>=10) {
				$dev_blynk_module->processCycle('10m');
				$m10=0;
			} else {
				$m10++;
			}
	   }

	   if ($h != $old_hour)
	   {
			$dev_blynk_module->processCycle('1h');
			$old_hour = $h;
	   }
   }
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
   sleep(5);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
