<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='blynk_devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if ($this->tab=='') {

   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }

   global $token;
   $rec['TOKEN']=$token;

   global $json_data;
   $rec['JSON_DATA']=$json_data;

   global $updated_date;
   global $updated_minutes;
   global $updated_hours;
   $rec['UPDATED']=toDBDate($updated_date)." $updated_hours:$updated_minutes:00";
  }
  
  $json_data=$this->get_data($rec['TOKEN']);
   
  
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  // step: default
  if ($this->tab=='') {
	$json_decoded=json_decode($rec['JSON_DATA']);
	$out['JSON_ID']=$json_decoded->id;
	$out['JSON_NAME']=$json_decoded->name;
	$out['JSON_TYPE']=$json_decoded->type;
  if ($rec['UPDATED']!='') {
   $tmp=explode(' ', $rec['UPDATED']);
   $out['UPDATED_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $updated_hours=$tmp2[0];
   $updated_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$updated_minutes) {
    $out['UPDATED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['UPDATED_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$updated_hours) {
    $out['UPDATED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['UPDATED_HOURS'][]=array('TITLE'=>$title);
   }
  }
  }

  if ($this->mode=='set_time') {
	  if (isset($_GET['time'])) {
		  $rec['CHTIME']=$_GET['time'];
		  SQLUpdate($table_name, $rec);
	  }
  }
  
  if ($this->tab=='data') {
   //dataset2
   $new_id=0;
   $properties=SQLSelect("SELECT * FROM blynk_data WHERE DEVICE_ID='".$rec['ID']."' ORDER BY ID");
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
	if ($properties[$i]['PIN_TYPE']=='ANALOG') $properties[$i]['PIN_TYPE']='<span class="label label-info">'.$properties[$i]['PIN_TYPE'].'</span>';
	if ($properties[$i]['PIN_TYPE']=='DIGITAL') $properties[$i]['PIN_TYPE']='<span class="label label-primary">'.$properties[$i]['PIN_TYPE'].'</span>';
	if ($properties[$i]['PIN_TYPE']=='VIRTUAL') $properties[$i]['PIN_TYPE']='<span class="label label-success">'.$properties[$i]['PIN_TYPE'].'</span>';
	if (stripos($properties[$i]['PIN_TYPE'],';')) {
		$expl=explode(';', $properties[$i]['PIN_TYPE']);
		$properties[$i]['PIN_TYPE']='';
		foreach($expl as $exploded) {
			if ($exploded=='ANALOG') $properties[$i]['PIN_TYPE'].='<span class="label label-info">'.$exploded.'</span>&nbsp;';
			if ($exploded=='DIGITAL') $properties[$i]['PIN_TYPE'].='<span class="label label-primary">'.$exploded.'</span>&nbsp;';
			if ($exploded=='VIRTUAL') $properties[$i]['PIN_TYPE'].='<span class="label label-success">'.$exploded.'</span>&nbsp;';
		}
	}
	
	if ($this->mode=='updateSets') {
		global $setsid;
		$prop_rec=SQLSelectOne("SELECT * FROM blynk_data WHERE ID='".$setsid."'");
		if($this->sets=='r1') $prop_rec['R']=1;
		if($this->sets=='r0') $prop_rec['R']=0;
		if($this->sets=='i1') $prop_rec['I']=1;
		if($this->sets=='i0') $prop_rec['I']=0;
		SQLUpdate('blynk_data', $prop_rec);
	}
	global $delete_id;
	   if ($delete_id) {
		SQLExec("DELETE FROM dev_broadlink_commands WHERE ID='".(int)$delete_id."'");
	   }
    if ($properties[$i]['ID']==$new_id) continue;
    if ($this->mode=='update') {
      global ${'title'.$properties[$i]['ID']};
      $properties[$i]['TITLE']=trim(${'title'.$properties[$i]['ID']});
      global ${'value'.$properties[$i]['ID']};
      $properties[$i]['VALUE']=trim(${'value'.$properties[$i]['ID']});
      global ${'linked_object'.$properties[$i]['ID']};
      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
      global ${'linked_property'.$properties[$i]['ID']};
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
      SQLUpdate('blynk_data', $properties[$i]);
      $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
      }
      if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
       addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
      }
     }
	if (is_numeric($properties[$i]['VALUE'])) $properties[$i]['VALUE_T']='float';
	if ($properties[$i]['VALUE']=='1' || $properties[$i]['VALUE']=='0') $properties[$i]['VALUE_T']='num';
   }
   $out['PROPERTIES']=$properties;   
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
