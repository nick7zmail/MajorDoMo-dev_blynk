<?php
/**
* Blynk 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 23:05:39 [May 30, 2017])
*/
//
//
class dev_blynk extends module {
/**
* dev_blynk
*
* Module class constructor
*
* @access private
*/
function dev_blynk() {
  $this->name="dev_blynk";
  $this->title="Blynk";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 $out['API_URL']=$this->config['API_URL'];
 if (!$out['API_URL']) {
  $out['API_URL']='http://';
 }
 $out['API_KEY']=$this->config['API_KEY'];
 $out['API_USERNAME']=$this->config['API_USERNAME'];
 $out['API_PASSWORD']=$this->config['API_PASSWORD'];
 if ($this->view_mode=='update_settings') {
   global $api_url;
   $this->config['API_URL']=$api_url;
   global $api_key;
   $this->config['API_KEY']=$api_key;
   global $api_username;
   $this->config['API_USERNAME']=$api_username;
   global $api_password;
   $this->config['API_PASSWORD']=$api_password;
   $this->saveConfig();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='blynk_devices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_blynk_devices') {
   $this->search_blynk_devices($out);
  }
  if ($this->view_mode=='edit_blynk_devices') {
   $this->edit_blynk_devices($out, $this->id);
  }
  if ($this->view_mode=='delete_blynk_devices') {
   $this->delete_blynk_devices($this->id);
   $this->redirect("?data_source=blynk_devices");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='blynk_data') {
  if ($this->view_mode=='' || $this->view_mode=='search_blynk_data') {
   $this->search_blynk_data($out);
  }
  if ($this->view_mode=='edit_blynk_data') {
   $this->edit_blynk_data($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* blynk_devices search
*
* @access public
*/
 function search_blynk_devices(&$out) {
  require(DIR_MODULES.$this->name.'/blynk_devices_search.inc.php');
 }
/**
* blynk_devices edit/add
*
* @access public
*/
 function edit_blynk_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/blynk_devices_edit.inc.php');
 }
/**
* blynk_devices delete record
*
* @access public
*/
 function delete_blynk_devices($id) {
  $rec=SQLSelectOne("SELECT * FROM blynk_devices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM blynk_devices WHERE ID='".$rec['ID']."'");
 }
/**
* blynk_data search
*
* @access public
*/
 function search_blynk_data(&$out) {
  require(DIR_MODULES.$this->name.'/blynk_data_search.inc.php');
 }
/**
* blynk_data edit/add
*
* @access public
*/
 function edit_blynk_data(&$out, $id) {
  require(DIR_MODULES.$this->name.'/blynk_data_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
  $this->getConfig();
   $table='blynk_data';
   $properties=SQLSelect("SELECT * FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
		$id=$properties[$i]['DEVICE_ID'];
		$rec=SQLSelectOne("SELECT * FROM blynk_devices WHERE ID='$id'");
		if (stripos($properties[$i]['PIN'], ';')) {
			$pins=explode(';', $properties[$i]['PIN']);
			$pin_types=explode(';', $properties[$i]['PIN_TYPE']);
			$values=explode(';', $value);
			for($j=0; $j<count($pins);$j++) {
				$this->write_pin($rec['TOKEN'], trim(substr($pin_types[$j], 0, 1).$pins[$j]), trim($values[$j]));
			}
		} else {
			$this->write_pin($rec['TOKEN'], trim(substr($properties[$i]['PIN_TYPE'], 0, 1).$properties[$i]['PIN']), ($value));
		}
    }
   }
 }
 function processCycle($chtime = '') {
 $this->getConfig();
 $this->get_all_data($chtime);
 }
 
 function write_pin($auth_token, $pin, $value) {
	$host=$this->config['API_URL'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$host/$auth_token/update/$pin?value=$value");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	$response = curl_exec($ch);
	curl_close($ch);
 }
 
 function get_all_data($chtime = '') {
	if(isset($chtime) && $chtime!='all' && $chtime!='') {
		$db_rec=SQLSelect("SELECT * FROM blynk_devices WHERE CHTIME='$chtime'");
	} elseif (isset($chtime) && $chtime!='all') {
		$db_rec=SQLSelect("SELECT * FROM blynk_devices");
	} else {
		$db_rec=SQLSelect("SELECT * FROM blynk_devices WHERE CHTIME<>'none'");
	}
	foreach($db_rec as $rec) {
		$this->get_data($rec['TOKEN']);
		$this->get_states($rec['TOKEN']);
	}
 }
 function get_states($auth_token) {
	$table='blynk_data';
	$rec=SQLSelectOne("SELECT * FROM blynk_devices WHERE TOKEN='$auth_token'");
	$host=$this->config['API_URL'];
	$id=$rec['ID'];
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$host/$auth_token/isHardwareConnected");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	$response = curl_exec($ch);
	curl_close($ch);
	
	$name='HWOnline';
	$properties=SQLSelectOne("SELECT * FROM $table WHERE TITLE='$name' AND DEVICE_ID='$id'");
	$total=count($properties);
		if ($response=='true') {
			$properties['VALUE']='Online';
			$properties['PIN']='<span class="label label-success">ONLINE</span>';
		} else {
			$properties['VALUE']='Offline';
			$properties['PIN']='<span class="label label-danger">OFFLINE</span>';
		}
	if ($total) {
		SQLUpdate($table, $properties);
	} else {
		$properties['TITLE']=$name;
		$properties['DEVICE_ID']=$rec['ID'];
		SQLInsert($table, $properties);
	}
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$host/$auth_token/isAppConnected");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	$response = curl_exec($ch);
	curl_close($ch);
	
	$name='AppOnline';
	$properties=SQLSelectOne("SELECT * FROM $table WHERE TITLE='$name' AND DEVICE_ID='$id'");
	$total=count($properties);
		if ($response=='true') {
			$properties['VALUE']='Online';
			$properties['PIN']='<span class="label label-success">ONLINE</span>';
		} else {
			$properties['VALUE']='Offline';
			$properties['PIN']='<span class="label label-danger">OFFLINE</span>';
		}
	if ($total) {
		SQLUpdate($table, $properties);
	} else {
		$properties['TITLE']=$name;
		$properties['DEVICE_ID']=$rec['ID'];
		SQLInsert($table, $properties);
	}
	
 }	 
 function get_data($auth_token) {
	    $table='blynk_data';
		$rec=SQLSelectOne("SELECT * FROM blynk_devices WHERE TOKEN='$auth_token'");
		$host=$this->config['API_URL'];
		$id=$rec['ID'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "$host/$auth_token/project");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		$response = curl_exec($ch);
		curl_close($ch);
		$json_data_dec=json_decode($response);
		$rec['JSON_DATA']='{"id":"'.$json_data_dec->id.'","name":"'.$json_data_dec->name.'","type":"'.$json_data_dec->hardwareInfo->boardType.'"}';
		foreach ($json_data_dec->widgets as $widget) {
			$name=$widget->type.'_'.$widget->id;
			$properties=SQLSelectOne("SELECT * FROM $table WHERE TITLE='$name' AND DEVICE_ID='$id'");
			$total=count($properties);
				$properties['VALUE']=$widget->value;
				$properties['PIN']=$widget->pin;
				$properties['PIN_TYPE']=$widget->pinType;
				if ($widget->pins) {
					foreach($widget->pins as $pin) {
						$properties['VALUE'].=$pin->value.';';
						$properties['PIN'].=$pin->pin.';';
						$properties['PIN_TYPE'].=$pin->pinType.';';
					}
					$properties['VALUE']=substr_replace($properties['VALUE'], '', -1);
					$properties['PIN']=substr_replace($properties['PIN'], '', -1);
					$properties['PIN_TYPE']=substr_replace($properties['PIN_TYPE'], '', -1);
				}
			if ($total) {
				SQLUpdate($table, $properties);
				if(isset($properties['LINKED_OBJECT']) && $properties['LINKED_OBJECT']!='' && isset($properties['LINKED_PROPERTY']) && $properties['LINKED_PROPERTY']!='') sg($properties['LINKED_OBJECT'].'.'.$properties['LINKED_PROPERTY'], $properties['VALUE']);
			} else {
				$properties['DEVICE_ID']=$rec['ID'];
				$properties['TITLE']=$name;
				SQLInsert($table, $properties);
			}
		}
		$rec['UPDATED']=date('Y-m-d H:i:s');
		SQLUpdate('blynk_devices', $rec);
		return $response;	
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS blynk_devices');
  SQLExec('DROP TABLE IF EXISTS blynk_data');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data = '') {
/*
blynk_devices - 
blynk_data - 
*/
  $data = <<<EOD
 blynk_devices: ID int(10) unsigned NOT NULL auto_increment
 blynk_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 blynk_devices: TOKEN varchar(255) NOT NULL DEFAULT ''
 blynk_devices: JSON_DATA varchar(255) NOT NULL DEFAULT ''
 blynk_devices: CHTIME varchar(10) NOT NULL DEFAULT ''
 blynk_devices: UPDATED datetime
 blynk_data: ID int(10) unsigned NOT NULL auto_increment
 blynk_data: TITLE varchar(100) NOT NULL DEFAULT ''
 blynk_data: VALUE varchar(255) NOT NULL DEFAULT ''
 blynk_data: PIN varchar(50) NOT NULL DEFAULT ''
 blynk_data: PIN_TYPE varchar(100) NOT NULL DEFAULT ''
 blynk_data: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 blynk_data: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 blynk_data: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDMwLCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
