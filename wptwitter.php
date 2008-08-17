<?php
/*
Plugin Name: WPTwitter
Plugin URI: http://arti.kz/
Description: Wordpress Twitter plugin
Author: arti
Version: 0.1
Author URI: http://arti.kz/
*/

if ($_GET['updateWPTwitter']) {
	$options = get_option('widget_wpTwitter');
	$options['cache'] = file_get_contents('http://twitter.com/statuses/user_timeline/'.$options['username'].'.json');
	update_option('widget_wpTwitter', $options);
	die();
}

$wpTwitter_options = array(
	'fields' => array(
		'title' => array('label' => 'Title:', 'type' => 'text', 'default' => 'Twitter'),
		'username' => array('label' => 'Username:', 'type' => 'text', 'default' => 'arti_kz'),
		'number' => array('label' => 'Number:', 'type' => 'text', 'default' => '5'),
		'format' => array('label' => 'Format:', 'type' => 'text', 'default' => '"%text%" <a href="http://twitter.com/%user-name%/statuses/%id%" style="font-size:70%">%created_at%</a>'),
		'followme' => array('label' => 'Follow me text:', 'type' => 'text', 'default' => 'follow me on twitter'),
	),
	'cache' => '',
);

function relative_time($time) {
	$delta = time() - $time;
	$r = '';
	if ($delta < 60) {
		$r = 'меньше минуты назад';
	} else if ($delta < 120) {
		$r = 'около минуты назад';
	} else if ($delta < (45*60)) {
		$r = floor($delta / 60) . ' минут назад';
	} else if ($delta < (2*90*60)) {
		$r = 'около часа назад';
	} else if ($delta < (24*60*60)) {
		$r = 'около ' . floor($delta / 3600) . ' часов назад';
	} else if ($delta < (48*60*60)) {
		$r = '1 день назад';
	} else {
		$r = floor($delta / 86400) . ' дней назад';
	}
	return $r;
}

function widget_wpTwitter($args) {
	extract($args);
	$options = get_option('widget_wpTwitter');
	echo $before_widget;
	echo $before_title.$options['title'].$after_title;
	echo '<ul>';
	if ($options['cache']) {
		$data = json_decode($options['cache']);
		$c = $options['number'];
		foreach ($data as $v) {
			$v->created_at = relative_time(strtotime($v->created_at));
			$a = array();
			$b = array();
			foreach ($v as $i => $j) if ($i != 'user') {
				$a[] = '%'.$i.'%';
				$b[] = $j;
			}
			foreach ($v->user as $i => $j) {
				$a[] = '%user-'.$i.'%';
				$b[] = $j;
			}
			echo '<li>'.str_replace($a, $b, $options['format']).'</li>';
			if (!--$c) break;
		}
	}
	echo '<li style="text-align:right"><a href="http://twitter.com/'.$options['username'].'" style="font-size:70%">'.$options['followme'].'</a></li>';
	echo '</ul>';
	echo $after_widget;
}

function widget_wpTwitter_control() {
	global $wpTwitter_options;
	$options = get_option('widget_wpTwitter');
	
	if (isset($_POST['wpTwitter_submit'])) {
		foreach ($wpTwitter_options['fields'] as $k => $v) {
			$id = 'wpTwitter_'.$k;
			$options[$k] = stripslashes($_POST[$id]);
		}
		update_option('widget_wpTwitter', $options);
	}
	
	foreach ($wpTwitter_options['fields'] as $k => $v) {
		if (!isset($options[$k])) {
			$options[$k] = $v['default'];
		}
		$options[$k] = htmlspecialchars($options[$k], ENT_QUOTES);
		$id = 'wpTwitter_'.$k;
		echo '<p style="text-align:right"><label for="'.$k.'">'.__($v['label']).'</label><input type="'.$v['type'].'" name="'.$id.'" id="'.$id.'" value="'.$options[$k].'" /></p>';
	}
	echo '<input type="hidden" name="wpTwitter_submit" id="wpTwitter_submit" value="1" />';
}

function wpTwitter_init() {
	wp_register_sidebar_widget('wp-twitter', __('Wordpress Twitter'), 'widget_wpTwitter');
	wp_register_widget_control('wp-twitter', __('Wordpress Twitter'), 'widget_wpTwitter_control');
}

add_action("plugins_loaded", "wpTwitter_init");
