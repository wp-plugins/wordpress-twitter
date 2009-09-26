<?php
/*
Plugin Name: Wordpress Twitter
Plugin URI: http://indiafascinates.com/wordpress/wordpress-twitter-plugin/
Description:  A wonderful tweets widget to show your timeline, friends' timeline or tweets on any keywords
Version: 1.4
Author: India Fascinates (Suhas), Rajesh (BestIndianBloggers Dot Com)
Author URI: http://indiafascinates.com/
*/

/*
Copyright (C) 2009 India Fascinates (Suhas), Rajesh (BestIndianBloggers Dot Com)
Copyright (C) 2009 Mauro Rocco "fireantology@gmail.com"
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
// inits json decoder/encoder object if not already available
if ( !class_exists( 'Services_JSON' ) ) {
	include_once( dirname( __FILE__ ) . '/class.json.php' );
}

class bibtweets{
    var  $tweet_statuses  = 'http://twitter.com/statuses/';
	var $search_tweets  = 'http://search.twitter.com/search.json?';
    var $preurl_user="http://twitter.com/";
    var $preurl_tag="http://search.twitter.com/search?q=";
	var $bibt_options;
    var $instance_name;
    var $username;
    var $password;
    var $theme = "blue";
    var $timeline = "user";
	var $numtweets = 20;
    var $string_to_search = "q=wordpress";    
    var $width = 400;
    var $height = 265;
	var $followme = 1;
    var $bib_time_interval=60;
	
    var $version = "1.4";
    var $USER_TIMELINE = "user";
    var $FRIENDS_TIMELINE = "friends";
	var $SEARCH = "search";
	
	var $styles = array ("red", "green", "blue");
	var $timelines = array ("user", "friends", "search");
	var $defaultStyle = "blue";
	var $dflttimeline = "user";  
    var $dfltnumtweets = 20;	

    function  bibtweets(){

        global $wp_version;
		$this->wp_version = $wp_version;
		
		// Pre-2.6 compatibility
		if ( ! defined( 'WP_CONTENT_URL' ) )
			define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
		if ( ! defined( 'WP_CONTENT_DIR' ) )
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		if ( ! defined( 'WP_PLUGIN_URL' ) )
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
		if ( ! defined( 'WP_PLUGIN_DIR' ) )
			define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );		
		
		$bibt_options = get_option('bibt_options');
		$this->bibt_options = $bibt_options;			
		
        $this->username = $this->bibt_options['bib_user'];
        $this->password = $this->bibt_options['bib_pwd'];
		$this->width = $this->bibt_options['bib_width'];
        $this->height = $this->bibt_options['bib_height']; 
		$this->followme = $this->bibt_options['bib_followme'];
        $this->bib_time_interval = $this->bibt_options['bib_time_interval'];		
        $this->theme = $this->bibt_options['bib_style']; 
		$this->numtweets = $this->bibt_options['bib_numtweets']; 
        $this->timeline = $this->bibt_options['bib_timeline']; 
		$this->instance_name="bibt";
		if (stripcslashes($this->bibt_options['bib_search']) !="")
			$this->string_to_search = "q=".$this->bibt_options['bib_search'];         		
    }   
        /**
     * Print html code for css include
     */

    function printCssRef(){
        echo '<link href="'.WP_PLUGIN_URL.'/wordpress-twitter/css/'.$this->theme.'/bibtweets.css" rel="stylesheet" type="text/css" />';
    }

    function parseText($text){
        $text.=" ";
        $text=preg_replace("/http:\/\/([^ ]+) /", "<a href=\"http://$1\" rel=\"nofollow\" target=\"_blank\">http://$1</a> ", $text);
        $text=preg_replace("/@([a-zA-z0-9_]+)/", "@<a href=\"".$this->preurl_user."$1\" rel=\"nofollow\" target=\"_blank\">$1</a> ", $text);
        $text=preg_replace("/#([a-zA-z0-9_]+)/", "#<a href=\"".$this->preurl_tag."$1\" rel=\"nofollow\" target=\"_blank\">$1</a> ", $text);
        return $text;
    }


    function dateFormatting($date){
        $temp=strtotime($date);
        $date=date("d M Y H:i", $temp);
        return $date;
    }

    /**
     * Get Tweets as html
     * @param $timeline
     * @return string post html code
     */

    function  getTweets($timeline){
        $messages="";
		$is_search = false;
		$XML =  $this-> readBibXml($timeline);
		if (!$XML) return false;
        $tweetset = file_get_contents($XML);
		if (substr($tweetset, 2, 7) == "results") $is_search = true;
        		
        if($is_search) {
            $json_object=json_decode($tweetset);
            $response=$json_object->{'results'};            
        }
        else {			    
			$json_object=json_decode($tweetset);
            $response=$json_object;             
		}
		//if (empty($response)) return false;
        if($is_search) {
			foreach ($response as $status) {
				$messages.= '<div id="item_BiB">';

				$messages.= '<div id="text_BiB"><img src="'.$status->profile_image_url.'"><a href="'.$this->preurl_user.$status->from_user.'" rel="nofollow" target="_blank"><b>' . $status->from_user . '</b></a>: '.$this->parseText($status->text);
				$messages.= '<div id="date_BiB"><em>on ' . $this->dateFormatting($status->created_at);
				if($status->to_user!="")
					$messages.= ' in reply to <a href="'.$this->preurl_user.$status->to_user.'" rel="nofollow" target="_blank">'.$status->to_user.'</a>';
					$messages.="</em></div><div style=\"clear: both; display: block;\"> </div></div><div style=\"clear: both; display: block;\"> </div></div>";
        }

		} else {
			foreach ($response as $status) {
				$messages.= '<div id="item_BiB">';

				$messages.= '<div id="text_BiB"><img src="'.$status->user->profile_image_url.'"><a href="'.$this->preurl_user.$status->user->screen_name.'" rel="nofollow" target="_blank"><b>' . $status->user->screen_name . '</b></a>: '.$this->parseText($status->text);
				$messages.= '<div id="date_BiB"><em>on ' . $this->dateFormatting($status->created_at);
					if($status->in_reply_to_screen_name!="")
						$messages.= ' in reply to <a href="'.$this->preurl_user.$status->in_reply_to_screen_name.'" rel="nofollow" target="_blank">'.$status->in_reply_to_screen_name.'</a>';
						$messages.="</em></div><div style=\"clear: both; display: block;\"> </div></div><div style=\"clear: both; display: block;\"> </div></div>";
			}
		}
        return $messages;
    }
	
	function init() {
		if (function_exists('load_plugin_textdomain')) {
		
			load_plugin_textdomain('bibtweets', WP_PLUGIN_DIR . '/wordpress-twitter');
			
		}
	}
	
	function admin_menu() {
        $file = __FILE__;
        add_submenu_page('options-general.php', __('Wordpress Twitter', 'bibtweets'), __('Wordpress Twitter','bibtweets'), 10, $file, array($this, 'options_panel'));                
    }
	
	function echo_to_blog_header() {
		
		echo '<link href="'.WP_PLUGIN_DIR.'/wordpress-twitter/css/'.$this->theme.'/bibtweets.css" rel="stylesheet" type="text/css" />';
	}

    /**
     * Print html/javascript Wordpress Twitter box
     */

    function printBox(){
	    $tweets_to_print = $this->getTweets($this->bibt_options['bib_timeline']);
		if ($tweets_to_print != false) {
			echo "<div id=\"BiB_".$this->theme."\" style=\"width: ".$this->width."px;\">";
			echo "	<div id=\"top_BiB\">
				<div id=\"center_BiB\">
				<div id=\"left_BiB\"><a href=\"http://indiafascinates.com\" target=\"_blank\"><img src=\"".WP_PLUGIN_URL."/wordpress-twitter/css/".$this->theme."/images/wpt_left.png\"></a></div>";
				if ($this->bibt_options['bib_followme']) {
				echo " <div id=\"text_BiB\">
				<a href=\"".$this->preurl_user.$this->username."\"><img align=\"absmiddle\" src=\"".WP_PLUGIN_URL."/wordpress-twitter/css/".$this->theme."/images/followme.png\" border=\"0\"></a>&nbsp; <a href=\"".$this->preurl_user.$this->username."\">Follow me on twitter </a>&nbsp;&nbsp;
				</div>";
				}
				echo "<div id=\"right_BiB\"><img src=\"".WP_PLUGIN_URL."/wordpress-twitter/css/".$this->theme."/images/top_right.png\" border=\"0\"></div>
				</div>
				</div>
				<div id=\"content_BiB\">
				<div id=\"".$this->instance_name."BiBOverFlow\" class=\"BiBOverFlow\" style=\"height: ".$this->height."px;\">";
			echo $tweets_to_print;
			echo "</div></div><div id=\"bottom_BiB\"><div id=\"center_BiB\">
				<div id=\"left_BiB\"><a href=\"http://bestindianbloggers.com\" target=\"_blank\"><img src=\"".WP_PLUGIN_URL."/wordpress-twitter/css/".$this->theme."/images/bottom_left.png\" border=\"0\"></a></div>
				<div id=\"right_BiB\"><img src=\"".WP_PLUGIN_URL."/wordpress-twitter/css/".$this->theme."/images/bottom_right.png\" border=\"0\"></div>
				</div></div><div style=\"display:block; clear: both;\"> </div>";
			echo "</div>";
		}
    }

    function readBibXml($timeline){
	    
        $file_date=WP_PLUGIN_DIR . '/wordpress-twitter/cache/'.$this->instance_name."date.dat";
        $fd = @fopen($file_date, 'r');
		$bib_ti = $this->bib_time_interval;
        if(!$fd) $this->writeBibXML($timeline);
        else{
            $date = fread($fd, filesize($file_date));
            fclose($fd);
            if(time()-$date>$bib_ti)  {
				$tweet_status = $this->writeBibXML($timeline);				
				if (!$tweet_status) return false;				
			}
        }
        $cache_file=WP_PLUGIN_DIR . '/wordpress-twitter/cache/'.$this->instance_name."cache.xml";        
        return $cache_file;
    }

    function writeBibXML($timeline){
        $messages=$this->requestTwitterStatuses($timeline);
		if (!$messages) return false; 
		
        $file=fopen(WP_PLUGIN_DIR . '/wordpress-twitter/cache/'.$this->instance_name."cache.xml", "w");
        fwrite($file, $messages);
        fclose($file);
        
        $file_date=fopen(WP_PLUGIN_DIR . '/wordpress-twitter/cache/'.$this->instance_name."date.dat", "w");
        fwrite($file_date, time());
        fclose($file_date);
        return 1;
    }

    function requestTwitterStatuses($timeline) {
        $string_timeline="";
		//var $xml;
        if($timeline==$this->FRIENDS_TIMELINE){ $string_timeline = $this -> tweet_statuses . "friends_timeline/" . $this -> username . '.json?count='.$this->bibt_options['bib_numtweets'];}
        else if($timeline==$this->USER_TIMELINE){ $string_timeline = $this -> tweet_statuses . "user_timeline/" . $this -> username . '.json?count='.$this->bibt_options['bib_numtweets'];}
		else if($timeline==$this->SEARCH) { $string_timeline = $this -> search_tweets . $this->string_to_search . '&rpp='.$this->bibt_options['bib_numtweets'];  }	
		if(function_exists('curl_init')){
			$handler_curl = curl_init();
			curl_setopt($handler_curl, CURLOPT_URL, $string_timeline);
			curl_setopt($handler_curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($handler_curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($handler_curl, CURLOPT_USERPWD, $this->username.':'.$this->password);
			$xml= curl_exec($handler_curl);
			curl_close($handler_curl);
			unset($handler_curl);
		}
		else{
			$xml = file_get_contents($string_timeline);
		}
        return $xml;
    }
	
		function options_panel() {      

		$message = null;
        $message_updated = __("Wordpress Twitter Options Updated.", 'bibtweets');
        // update options
        if ($_POST['action'] && $_POST['action'] == 'bib_update') {
				$nonce = $_POST['bib-options-nonce'];
				if (!wp_verify_nonce($nonce, 'bib-options-nonce')) die ( 'Security Check - If you receive this in error, log out and back in to WordPress');
                $message = $message_updated;       
				$upd_options['bib_user'] = $_POST['bib_user'];
				$upd_options['bib_pwd'] = $_POST['bib_pwd'];
				$upd_options['bib_width']= $_POST['bib_width'];
				$upd_options['bib_height']= $_POST['bib_height'];
                $upd_options['bib_followme']= $_POST['bib_followme'];				
				$upd_options['bib_time_interval']= $_POST['bib_time_interval'];
				$upd_options['bib_style']= $_POST['bib_style'];
				$upd_options['bib_numtweets']= $_POST['bib_numtweets'];
				$upd_options['bib_timeline']= $_POST['bib_timeline'];
				$upd_options['bib_search']= $_POST['bib_search'];
				
                update_option('bibt_options', $upd_options);
				$this->bibt_options = get_option('bibt_options');
 				if (stripcslashes($this->bibt_options['bib_search']) !="")
					$this->string_to_search = "q=".$this->bibt_options['bib_search'];
        }
        ?>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div class="wrap">
<h2><?php _e('Wordpress Twitter Plugin Options', 'bibtweets'); ?></h2>
<p>
<?php _e("This is version ", 'bibtweets') ?><?php _e("$this->version ", 'bibtweets') ?>
&nbsp;
| <a target="_blank" title="<?php _e('FAQ', 'bibtweets') ?>" href="http://indiafascinates.com/wordpress/wordpress-twitter-plugin/"><?php _e('FAQ', 'bibtweets') ?></a>
| <a target="_blank" title="<?php _e('Wordpress Twitter Plugin Feedback', 'bibtweets') ?>" href="http://indiafascinates.com/wordpress/wordpress-twitter-plugin/"><?php _e('Feedback', 'bibtweets') ?></a>
</p>
<script type="text/javascript">
<!--
    function toggleVisibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
	
//-->
</script>

<h3><?php _e('Click on option titles to get help!', 'bibtweets') ?></h3>

<form name="dofollow" action="" method="post">
<table class="form-table">
<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_user_tip');">
<?php _e('Enter your Twitter User Name:', 'bibtweets')?>
</a>
</td>
<td>
<textarea cols="16" rows="1" name="bib_user"><?php echo stripcslashes($this->bibt_options['bib_user']); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="bib_user_tip">
<?php
_e('Enter your Twitter User Name.', 'bibtweets');
 ?>
</div>
</td>
</tr>
<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_pwd_tip');">
<?php _e('Enter your Twitter Password:', 'bibtweets')?>
</a>
</td>
<td>
<input type="password" name="bib_pwd" value="<?php echo stripcslashes($this->bibt_options['bib_pwd']); ?>" />
<div style="max-width:500px; text-align:left; display:none" id="bib_pwd_tip">
<?php
_e('Enter your Twitter Password.', 'bibtweets');
 ?>
</div>
</td>
</tr>
<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_width_tip');">
<?php _e('Enter the width of the Tweets widget(in pixels):', 'bibtweets')?>
</a>
</td>
<td>
<textarea cols="16" rows="1" name="bib_width"><?php echo stripcslashes($this->bibt_options['bib_width']); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="bib_width_tip">
<?php
_e('Enter the width of the Tweets widget', 'bibtweets');
 ?>
</div>
</td>
</tr>
<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_height_tip');">
<?php _e('Enter the height of the Tweets widget(in pixels):', 'bibtweets')?>
</a>
</td>
<td>
<textarea cols="16" rows="1" name="bib_height"><?php echo stripcslashes($this->bibt_options['bib_height']); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="bib_height_tip">
<?php
_e('Enter the height of the Tweets widget', 'bibtweets');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_followme_tip');">
<?php _e('Show "Fllow me on twitter" link:', 'bibtweets')?>
</a>
</td>
<td>
<input type="checkbox" name="bib_followme" <?php if (stripcslashes($this->bibt_options['bib_followme'])) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="bib_followme_tip">
<?php
_e("Choose this option to show Follow me on Twitter link in the widget", 'bibtweets');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_time_interval_tip');">
<?php _e('Enter the time interval to refresh tweets in Tweets widget (in seconds):', 'bibtweets')?>
</a>
</td>
<td>
<textarea cols="16" rows="1" name="bib_time_interval"><?php echo stripcslashes($this->bibt_options['bib_time_interval']); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="bib_time_interval_tip">
<?php
_e('Enter the time interval to refresh tweets in Tweets widget (in seconds)', 'bibtweets');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_style_tip');">
<?php _e('Choose the style for the Tweets widget:', 'bibtweets')?>
</a>
</td>
<td>
<select style="width:80px;" name="bib_style"><?php foreach ($this->styles as $style) { ?><option<?php if ( stripcslashes($this->bibt_options['bib_style']) == $style) { echo ' selected="selected"'; } elseif (stripcslashes($this->bibt_options['bib_style']) ==="" && $this->defaultStyle == $style) { echo ' selected="selected"'; } ?>><?php echo $style; ?></option><?php } ?></select>
<div style="max-width:500px; text-align:left; display:none" id="bib_style_tip">
<?php
_e('Choose the style for the Tweets widget', 'bibtweets');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_numtweets_tip');">
<?php _e('Choose the number of tweets to show:', 'bibtweets')?>
</a>
</td>
<td>
<select style="width:80px;" name="bib_numtweets" id="bib_numtweets"><?php for ( $numtweets = 1; $numtweets <= 20; ++$numtweets ) { ?><option<?php if ( stripcslashes($this->bibt_options['bib_numtweets']) == $numtweets) { echo ' selected="selected"'; } elseif (stripcslashes($this->bibt_options['bib_numtweets']) ==="" && $this->dfltnumtweets == $numtweets) { echo ' selected="selected"'; } ?>><?php echo $numtweets; ?></option><?php } ?></select>
<div style="max-width:500px; text-align:left; display:none" id="bib_numtweets_tip">
<?php
_e('Choose the number of tweets to show in the Tweets widget', 'bibtweets');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'bibtweets')?>" onclick="toggleVisibility('bib_timeline_tip');">
<?php _e('Choose the timeline for the Tweets widget:', 'bibtweets')?>
</a>
</td>
<td>
<select style="width:80px;" name="bib_timeline" id="bib_timeline"><?php foreach ($this->timelines as $timeline) { ?><option<?php if ( stripcslashes($this->bibt_options['bib_timeline']) == $timeline) { echo ' selected="selected"'; } elseif (stripcslashes($this->bibt_options['bib_timeline']) ==="" && $this->dflttimeline == $timeline) { echo ' selected="selected"'; } ?>><?php echo $timeline; ?></option><?php } ?></select>
<div style="max-width:500px; text-align:left; display:none" id="bib_timeline_tip">
<?php
_e('Choose the timeline for the Tweets widget', 'bibtweets');
 ?>
</div>
</td>
</tr>
<div style="max-width:500px; text-align:left; display:none" id="bib_search_tip">
<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<?php _e('Specify the search keyword if you choose "search" as the timeline:', 'bibtweets')?>
</td>
<td>
<textarea cols="16" rows="1" name="bib_search" id="bib_search"><?php echo stripcslashes($this->bibt_options['bib_search']); ?></textarea>
</td>
</tr>
</div>
</table>
<p class="submit">
<input type="hidden" name="action" value="bib_update" />
<input type="hidden" name="bib-options-nonce" value="<?php echo wp_create_nonce('bib-options-nonce'); ?>" />
<input type="submit" name="Submit" value="<?php _e('Update Options', 'bibtweets')?> &raquo;" />
</p>
</form>
</div>
<?php

        } // options_panel

}
$bibt_options = array(
		'bib_user' => null,
		'bib_pwd' => null,
		'bib_width' => 400,
		'bib_height' => 265,
		'bib_followme' => 1,
		'bib_time_interval' => 60,
		'bib_style' => 'blue',
		'bib_numtweets' => 20,
		'bib_timeline' => 'user',
		'bib_search' => 'wordpress'		
	);	

add_option( 'bibt_options', $bibt_options);

$tweets=new bibtweets();
add_action('wp_head', array($tweets, 'printCssRef'));
add_action('init', array($tweets, 'init'));
add_action('admin_menu', array($tweets, 'admin_menu'));

function widget_wp_tweets_init() {

  if(!function_exists('register_sidebar_widget')) { return; }
  function widget_wp_tweets($args) {
    extract($args);
    echo $before_widget . $before_title . $after_title;
    $bibtweets=new bibtweets();
    $bibtweets->printBox();
    echo $after_widget;
  }
  register_sidebar_widget('Wordpress Twitter','widget_wp_tweets');

}
add_action('plugins_loaded', 'widget_wp_tweets_init');
?>