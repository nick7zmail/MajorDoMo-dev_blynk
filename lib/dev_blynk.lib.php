<?php

//Преобразовывает html код цвета в его rgb эквивалент

function hex2rgb($hex) {
	$hex = str_replace('#', '', $hex);
	list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");
	return array($r, $g, $b);	
}

//Преобразовывает rgb код цвета в его html эквивалент (на входе массив rgb)

function rgb2hex($rgb)
{
	return '#' . sprintf('%02x', $rgb[0]) . sprintf('%02x', $rgb[1]) . sprintf('%02x', $rgb[2]);
}

function hex2adc($hex) {
	$hex = str_replace('#', '', $hex);
	list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");	
	return array(($r+1)*4-1, ($g+1)*4-1, ($b+1)*4-1);	
}

//Преобразовывает rgb код цвета в его html эквивалент (на входе массив rgb)

function adc2hex($rgb)
{
	foreach	($rgb as $k => $v) {
		$rgb[$k]=round(($v+1)/4)-1;
		if($v==0) $rgb[$k]=0;
	}
	print_r($rgb);
	return '#' . sprintf('%02x', $rgb[0]) . sprintf('%02x', $rgb[1]) . sprintf('%02x', $rgb[2]);
}
?>
