<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['blynk_devices_qry'];
  } else {
   $session->data['blynk_devices_qry']=$qry;
  }
  if (!$qry) $qry="1";
  $sortby_blynk_devices="ID DESC";
  $out['SORTBY']=$sortby_blynk_devices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM blynk_devices WHERE $qry ORDER BY ".$sortby_blynk_devices);
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
	$json_decoded=json_decode($res[$i]['JSON_DATA']);
	$res[$i]['JSON_INFO']='Name: '.$json_decoded->name.'; Board: '.$json_decoded->type;
	$id=$res[$i]['ID'];
	$stat=SQLSelectOne("SELECT * FROM blynk_data WHERE TITLE='HWOnline' AND DEVICE_ID='$id'");
	$res[$i]['HWINFO']=$stat['PIN'];
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['UPDATED']);
    $res[$i]['UPDATED']=fromDBDate($tmp[0])." ".$tmp[1];
   }
   $out['RESULT']=$res;
  }
