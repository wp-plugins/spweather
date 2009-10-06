<?php
$return .= '<table>';
$return .= '<tr>';
if($settings->spWeatherImages == 'on'){
	$return .= '<td><img src="'.$result->icon.'" alt="'.$result->condition.'" /></td>';
}
$return .= '<td valign="top">';
$return .= $result->condition.'<br/>';
$return .= $result->temp.' '.__('Degrees', 'spWeather');
//$return .= $result->humidity.'<br/>';
//$return .= $result->wind.'<br/>';
$return .= '</td>';


if($settings->spWeatherForecast != 0 || !empty($settings->spWeatherForecast)){
	$x = 1;
	foreach ($result->forecast as $entry) {
		if($settings->spWeatherForecast >= $x){
			if($settings->spWeatherImages == 'on'){
				$return .= '<td><img src="'.$entry->icon.'" alt="'.$entry->condition.'"/></td>';
			}
			$return .= '<td valign="top">';
			$return .= '<strong>'.$entry->dow.'</strong><br/>';
			//$return .= $entry->condition.'<br/>';
			if($settings->spWeatherShowMinMax == 'on'){
				$return .= $entry->low.' '.__('Degrees', 'spWeather').' '.__('Min.', 'spWeather').'<br/>';
				$return .= $entry->high.' '.__('Degrees', 'spWeather').' '.__('Max.', 'spWeather');
			}else{
				$return .= $entry->high.' '.__('Degrees', 'spWeather');
			}
			$return .= '</td>';
		}
		$x++;
	}
}
$return .= '</tr>';
$return .= '</table>';
echo $return;