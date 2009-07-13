<?php
/*
 Plugin Name: spWeather
 Plugin URI: http://wordpress.org/extend/plugins/spweather/
 Description: Shows the actual weather in your Region
 Author: Sebastian Klaus
 Version: 1.3
 Author URI: http://www.scriptpara.de
 */

// add admin menu
add_action('admin_menu', 'spWeatherMenu');

// load appropriate language text
add_action('init', 'spWeatherInit');

// show menu
function spWeatherMenu() {
	add_options_page(__('Weather settings','spWeather'), 'spWeather', 9, 'spWeather', 'spWeatherSettings');
}

// init
function spWeatherInit() {
	// load language
	load_plugin_textdomain('spWeather','/wp-content/plugins/spWeather/languages/');

	// Add entry to config file
	add_option('spWeather','', 'spWeather settings');
}

// display settings options
function spWeatherSettings(){
	if($_POST['spWeatherSave']){
		if($_POST['spWeatherForecast'] > 4 && !empty($_POST['spWeatherForecast'])){
			spWeatherShowMessage(__('I said: Max. 4 days for the forecast ;-)','spWeather'), 'error');
		}else{
			spWeatherShowMessage(__('Settings saved','spWeather'));
			spWeatherSaveSettings();
		}
	}

	$settings = spWeatherGetSettings();

	$result = '<div class="icon32" id="icon-options-general"><br/></div><div class="wrap"><h2>'.__('Settings','spWeather').'</h2></div>';
	$result .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';
	$result .= '<table class="form-table"><tbody>';
	$result .= '<tr valign="top">';
	$result .= '<th scope="row"><label for="spWeatherLanguage">'.__('Language', 'spWeather').'</label></th>';
	$result .= '<td>';
	$result .= '<select name="spWeatherLanguage" id="spWeatherLanguage">';
	$spWeatherLangDe = ($settings->spWeatherLanguage == 'german') ? 'selected="selected"' : '';
	$spWeatherLangEn = ($settings->spWeatherLanguage == 'english') ? 'selected="selected"' : '';
	$result .= '<option value="de" '.$spWeatherLangDe.'>'.__('German', 'spWeather').'</option>';
	$result .= '<option value="com" '.$spWeatherLangEn.'>'.__('English', 'spWeather').'</option>';
	$result .= '</select> ';
	$result .= '<span class="description">'.__('Select your display language', 'spWeather').'</span></td>';
	$result .= '</tr>';
	$result .= '<tr valign="top">';
	$result .= '<th scope="row"><label for="spWeatherRegion">'.__('Country, region or city', 'spWeather').'</label></th>';
	$spWeatherCityFound = ($settings->spWeatherCityFound == 'yes') ? '<img src="'.get_option('siteurl').'/wp-content/plugins/spWeather/icons/accept.png" alt="">' : '<img src="'.get_option('siteurl').'/wp-content/plugins/spWeather/icons/cancel.png" alt="">';
	$result .= '<td><input type="text" class="regular-text" value="'.$settings->spWeatherRegion.'" id="spWeatherRegion" name="spWeatherRegion"/>'.$spWeatherCityFound.' <span class="description">'.__('Try different spellings, if you get no results', 'spWeather').'</span></td>';
	$result .= '</tr>';
	$result .= '<tr valign="top">';
	$result .= '<th scope="row"><label for="spWeatherForecast">'.__('Forecast', 'spWeather').'</label></th>';
	$result .= '<td><input type="text" class="small-text" value="'.$settings->spWeatherForecast.'" id="spWeatherForecast" name="spWeatherForecast"/> <span class="description">'.__('days (max 4)', 'spWeather').'</span></td>';
	$result .= '</tr>';
	$result .= '<tr valign="top">';
	$result .= '<th scope="row"><label for="spWeatherImages">'.__('Show images', 'spWeather').'</label></th>';
	$spWeatherImagesChecked = ($settings->spWeatherImages == 'on') ? 'checked="checked"' : '';
	$result .= '<td><input type="checkbox" id="spWeatherImages" name="spWeatherImages" '.$spWeatherImagesChecked.'/> <span class="description">'.__('Should weather images been shown?', 'spWeather').'</span></td>';
	$result .= '</tr>';
	$result .= '<tr valign="top">';
	$result .= '<th scope="row"><label for="spWeatherShowMinMax">'.__('Show Min/Max', 'spWeather').'</label></th>';
	$spWeatherMinMaxChecked = ($settings->spWeatherShowMinMax == 'on') ? 'checked="checked"' : '';
	$result .= '<td><input type="checkbox" id="spWeatherShowMinMax" name="spWeatherShowMinMax" '.$spWeatherMinMaxChecked.'/> <span class="description">'.__('Show Min and Max or only Max Degrees in the forecast', 'spWeather').'</span></td>';
	$result .= '</tr>';
	$result .= '</tbody></table><br/><br/>';
	$result .= '<input type="hidden" name="spWeatherSave" value="1" />';
	$result .= '<input class="button-primary" type="submit" value="'.__('Save Changes').'" />';
	$result .= '</form>';

	echo $result;
}

// show message after submit
function spWeatherShowMessage($aMessage, $aClass = 'updated'){
	$result = '<div class="'.$aClass.' fade"><p>'.$aMessage.'</p></div>';
	echo $result;
}

// save the settings
function spWeatherSaveSettings(){
	$class = new stdClass();
	foreach ($_POST as $key => $entry) {
		$class->$key = $entry;
	}
	$weather = new spWeather();
	$weather->city = $_POST['spWeatherRegion'];
	$result = $weather->getIt();
	if(empty($result->city)){
		$class->spWeatherCityFound = 'no';
	}else{
		$class->spWeatherCityFound = 'yes';
	}
	update_option('spWeather', serialize($class));
}

// get the saved settings from database
function spWeatherGetSettings(){
	return unserialize(get_option('spWeather'));
}

// show the weather
function spWeatherShow(){
	$settings = spWeatherGetSettings();

	if($settings->spWeatherCityFound == 'yes'){
		$weather = new spWeather();
		$weather->city = $settings->spWeatherRegion;
		$result = $weather->getIt();

		$return = '<strong>'.__('Actual in', 'spWeather').' '.$settings->spWeatherRegion.'</strong><br/>';
		$return .= '<table>';
		$return .= '<tr>';
		if($settings->spWeatherImages == 'on'){
			$return .= '<td><img src="'.$result->icon.'" /></td>';
		}
		$return .= '<td valign="top">';
		$return .= $result->condition.'<br/>';
		$return .= $result->temp.' '.__('Degrees', 'spWeather');
		//$return .= $result->humidity.'<br/>';
		//$return .= $result->wind.'<br/>';
		$return .= '</td>';
		$return .= '</tr>';

		if($settings->spWeatherForecast != 0 || !empty($settings->spWeatherForecast)){
			$x = 1;
			foreach ($result->forecast as $entry) {
				if($settings->spWeatherForecast >= $x){
					$return .= '<tr>';
					if($settings->spWeatherImages == 'on'){
						$return .= '<td><img src="'.$entry->icon.'" /></td>';
					}
					$return .= '<td valign="top">';
					$return .= '<strong>'.$entry->dow.'</strong><br/>';
					$return .= $entry->condition.'<br/>';
					if($settings->spWeatherShowMinMax == 'on'){
						$return .= $entry->low.' '.__('Degrees', 'spWeather').' '.__('Min.', 'spWeather').'<br/>';
						$return .= $entry->high.' '.__('Degrees', 'spWeather').' '.__('Max.', 'spWeather');
					}else{
						$return .= $entry->high.' '.__('Degrees', 'spWeather');
					}
					$return .= '</td>';
					$return .= '</tr>';
				}
				$x++;
			}
		}
		$return .= '<table>';
		echo $return;
	}else{
		echo __('Please configure spWeather settings', 'spWeather');
	}
}


class spWeather {
	public $city = '';

	/**
	 * Returns a weather object
	 *
	 * @return object
	 */
	public function getIt() {
		$settings = spWeatherGetSettings();
		$lang = ($settings->spWeatherLanguage != '') ? $settings->spWeatherLanguage : 'de';
		$ort = urlencode($this->city);
		$url = 'http://www.google.'.$lang.'/ig/api?weather=' . $ort;
		$xml = simplexml_load_string(utf8_encode(file_get_contents($url)));
		if (! $xml) {
			return false;
		} else {
			return spWeather_objectbuilder::Provide($xml->weather);
		}
	}
}

class spWeather_objectbuilder {

	/**
	 * Gets the icon from the Googleserver
	 *
	 * @param string $AIcon
	 * @return string
	 */
	private function getIcon($AIcon) {
		$icon = substr($AIcon, 19);
		$path = dirname(__FILE__).'/images/';
		#echo $icon.'<br/>';
		#echo 'http://www.google.de'.$AIcon.'<br/>';
		if (! file_exists($path . $icon)) {
			$new_icon = file_get_contents('http://www.google.de' . $AIcon);
			file_put_contents($path . $icon, $new_icon);
		}
		return get_option('siteurl').'/wp-content/plugins/spWeather/images/' . $icon;
	}

	/**
	 * Fills the forecast-object
	 *
	 * @param xml $AXML
	 * @return object
	 */
	private function weatherForecast($AXML) {
		$forecast = new stdClass();
		$forecast->dow = $AXML->day_of_week['data'];
		$forecast->low = $AXML->low['data'];
		$forecast->high = $AXML->high['data'];
		$forecast->icon = self::GetIcon($AXML->icon['data']);
		$forecast->condition = $AXML->condition['data'];
		return $forecast;
	}

	/**
	 * Provides the weather object
	 *
	 * @param xml $AWeatherXML
	 * @return object
	 */
	public static function provide($AWeatherXML) {
		$weather = new stdClass();
		$weather->city = $AWeatherXML->forecast_information->postal_code['data'];
		$weather->condition = $AWeatherXML->current_conditions->condition['data'];
		$weather->temp = $AWeatherXML->current_conditions->temp_c['data'];
		$weather->wind = $AWeatherXML->current_conditions->wind_condition['data'];
		$weather->humidity = $AWeatherXML->current_conditions->humidity['data'];
		$weather->icon = self::getIcon($AWeatherXML->current_conditions->icon['data']);
		$forecast_array = array();
		foreach($AWeatherXML->forecast_conditions as $item) {
			$forecast_array[] = self::weatherForecast($item);
		}
		$weather->forecast = $forecast_array;
		return $weather;
	}
}