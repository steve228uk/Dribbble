<?php
/**
 * @plugin Dribbble
 * @description Display latest Dribbble shots. Use {{dribbble}}
 * @author Cocoon Design
 * @authorURI http://www.wearecocoon.co.uk/
 * @copyright 2012 (C) Cocoon Design  
 * @version 1.0
 * @since 0.7.4
 */

class Dribbble {

	public static function install(){

		$dbh = new CandyDB();
 		$sth = $dbh->prepare("INSERT INTO ".DB_PREFIX."options (option_key, option_value) VALUES (?, ?)");
 		$sth->execute(array('dribbble', 'steve228uk'));

 		$sth = $dbh->prepare("INSERT INTO ".DB_PREFIX."options (option_key, option_value) VALUES (?, ?)");
 		$sth->execute(array('dribbblecount', '5'));

	}

	public static function candyHead(){
		$html = '<link rel="stylesheet" type="text/css" href="'.URL_PATH.'plugins/Dribbble/css/dribbble.css" />';
		return $html;
	}

	private static function getShots(){

		$dbh = new CandyDB();
		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('dribbble'));

		$user = $sth->fetchColumn();

		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('dribbblecount'));
		$limit = $sth->fetchColumn();

		$ch = curl_init("http://api.dribbble.com/players/$user/shots?per_page=$limit");

		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json')
		);
		 
		curl_setopt_array($ch, $options);

		$return = curl_exec($ch);
		curl_close($ch);

		return $return;
			
	}

	private static function parseShots(){

		$shots = json_decode(self::getShots());
		
		$html = '<ul class="dribbble-shots">';

		$i=0;

		$count = count($shots->shots)-1;

		foreach ($shots->shots as $shot) {
			
			if ($i==0) {
				$html .= '<li class="first">';
			} elseif ($i==$count) {
				$html .= '<li class="last">';
			} else {
				$html .= '<li>';
			}
			
			$html .= '<a href="'.$shot->url.'" title="View on Dribbble">';
			$html .= '<img src="'.$shot->image_teaser_url.'" alt="'.$shot->title.'" />'; 
			$html .= '<span class="likes"><span>&hearts;</span> '.$shot->likes_count.'</span>';
			$html .= '</a>';
			$html .= '</li>';

			$i++;
		}

		$html .= '</ul>';

		return $html;

	}

	public static function addShorttag(){
		$results = self::parseShots();
		return array('{{dribbble}}' => $results);
	}

	public static function adminSettings(){
 		
 		$dbh = new CandyDB();
		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('dribbble'));

		$user = $sth->fetchColumn();

		$sth = $dbh->prepare("SELECT option_value FROM ".DB_PREFIX."options WHERE `option_key`=?");
		$sth->execute(array('dribbblecount'));
		$limit = $sth->fetchColumn();
 		
 		$html = "<h3>Dribbble Settings</h3>";
 		
 		$html .= "<ul>";
 		$html .= "<li>";
 		$html .= "<label>Dribbble Username</label>";
 		$html .= "<input type='text' name='dribbble' value='$user'/>";
 		$html .= "</li>";
 		
 		$html .= "<label>Shot Limit</label>";
 		$html .= "<input type='text' name='dribbblecount' value='$limit'/>";
 		$html .= "</li>";
 		
 		$html .= "</ul>";
 		
 		return $html;
 	}
 	
 	public static function saveSettings(){
 		$account = $_POST['dribbble'];
 		$limit = $_POST['dribbblecount'];
 		 		
 		$dbh = new CandyDB();
 		$dbh->exec('UPDATE '. DB_PREFIX .'options SET option_value="'. $account .'" WHERE option_key="dribbble"');
 		
 		$dbh->exec('UPDATE '. DB_PREFIX .'options SET option_value="'. $limit .'" WHERE option_key="dribbblecount"');
 		
 	}

}