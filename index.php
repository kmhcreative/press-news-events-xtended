<?php
/*
Plugin Name: PNEX (Press-News-Events Xtended)
Plugin URI:  https://github.com/kmhcreative/pnex
Description: Create custom post types for press releases, references to external news stories, and events organized and filtered by custom categories.  Based on "Press, News, Events" plugin by Loud Dog (http://www.louddog.com)
Version: 	 1.4
Author: 	 K.M. Hansen
Author URI:  http://www.kmhcreative.com
License: 	 GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
GitHub Plugin URI: https://github.com/kmhcreative/pnex
GitHub Branch: master
*/

define('PRESS_NEWS_EVENTS_VERSION', '1.4');

new Press_News_Events;
class Press_News_Events {
	function __construct() {
		add_action('init', array($this, 'locale'));
		add_action('admin_init', array($this, '_flush_rules'));
		register_activation_hook(__FILE__, array($this, 'activate'));
		
		$this->include_files(array(
			'settings.php',
			'custom-post-type.php',
			'custom-post-types',
		));

		add_theme_support('post-thumbnails');
		add_action('admin_enqueue_scripts', array($this, 'scripts_styles'));
		add_action('admin_notices', array(__CLASS__, 'admin_notices'));
	}
	
	function include_files($files) {
		$dir = dirname(__FILE__);
		foreach ($files as $file) {
			$file = "$dir/$file";
			if (is_dir($file)) {
				foreach (glob("$file/*.php") as $file) {
					include $file;
				}
			} else include $file;
		}
	}
	
	function locale() {
		load_plugin_textdomain('press-news-events', false, dirname(plugin_basename(__FILE__)).'/languages/');
	}
	
	function activate() {
		// Generate a PR News & Events Page //
        $new_page_title = 'PR News & Events';
        $new_page_content = '[pr_filter][getpr type="" totalposts="10"   date="false"  thumbnail="false" content="excerpt" meta="true"]';
        $new_page_template = ''; //ex. template-custom.php. Leave blank if you don't want a custom page template.
        //don't change the code bellow, unless you know what you're doing
        $page_check = get_page_by_title($new_page_title);
        $new_page = array(
                'post_type' => 'page',
                'post_title' => $new_page_title,
                'post_content' => $new_page_content,
                'post_status' => 'publish',
                'post_author' => 1,
        );
        if(!isset($page_check->ID)){
                $new_page_id = wp_insert_post($new_page);
                if(!empty($new_page_template)){
                        update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
                }
        }
        // do other activate stuff...
        add_action('init', array($this, '_activate'));

	}
	
	function _activate() {
		flush_rewrite_rules();
	}
	
	function flush_rules() {
		set_transient('pne_flush_rules', true);
	}
	
	function _flush_rules() {
		if (delete_transient('pne_flush_rules')) flush_rewrite_rules();
	}

	function scripts_styles() {
		// we're not using the jQuery Datepicker Plugin so to avoid interfering with other plugins
		// only enqueue if you are on one of the PNEX post pages...
		global $pagenow, $typenow;
		if (empty($typenow) && !empty($_GET['post'])) {
  			$post = get_post($_GET['post']);
  			$typenow = $post->post_type;
		}
		if ( is_admin() && $pagenow=="post.php" && ($typenow == 'press-release' || $typenow == 'news' || $typenow == 'event')) {
			wp_enqueue_style(
				'pne_admin', // handle 
				plugins_url('css/admin.css', __FILE__), // path
				array(), // dependencies
				PRESS_NEWS_EVENTS_VERSION // version
			);

			wp_register_script(
				'pne_datepicker', // handle 
				plugins_url('js/datepicker.js', __FILE__), // path
				array('jquery'), // dependencies
				PRESS_NEWS_EVENTS_VERSION, // version
				true // in footer
			);


			wp_enqueue_script(
				'pne_admin', // handle 
				$path = plugins_url('js/admin.js', __FILE__), // path
				array('jquery', 'pne_datepicker'), // dependencies
				PRESS_NEWS_EVENTS_VERSION, // version
				true // in footer
			);
		}

	}
	
	// Static Functions ----------------------------------------------------------
	
	static function admin_notices() {
		$notices = get_option('pne_admin_notices', array());
		
		if (count($notices)) {
			foreach ($notices as $notice) { ?>
				<div class="updated">
					<p><?php echo $notice; ?></p>
				</div>
			<?php }
			delete_option('pne_admin_notices');
		}
	}

	static function add_admin_notice($notice) {
		$notices = get_option('pne_admin_notices', array());
		$notices[] = $notice;
		update_option('pne_admin_notices', $notices);
	}

	static function date_i18n($date) {
		return date_i18n(get_option('date_format', $date));
	}
	
	static function pretty_date_range($starts = false, $ends = false, $all_day = true) {
		if (!$starts) $starts = current_time('timestamp');
		
		$pne = 'press-news-events';

		$same_day = !$ends || date_i18n(__('F j, Y', $pne), $starts) == date_i18n(__('F j, Y', $pne), $ends);
		$same_time = $same_day && date_i18n(__('H i', $pne), $starts) == date_i18n(__('H i', $pne), $ends);
		$same_month = date_i18n(__('F Y', $pne), $starts) == date_i18n(__('F Y', $pne), $ends);
		$same_year = date_i18n(__('Y', $pne), $starts) ==  date_i18n(__('Y', $pne), $ends);
		
		if ($same_time) {
			return $all_day
				? date_i18n(__('F j, Y', $pne), $starts)
				: date_i18n(__('F j g:ia, Y', $pne), $starts);
		} else if ($same_day) {
			return $all_day
				? date_i18n(__('F j, Y', $pne), $starts)
				: sprintf('%s, %s - %s', date_i18n(__('F j, Y', $pne), $starts), date_i18n(__('g:ia', $pne), $starts), date_i18n(__('g:ia', $pne), $ends));
		} else if ($same_month) {
			return $all_day
				? sprintf('%s - %s, %s', date_i18n(__('F j', $pne), $starts), date_i18n(__('j', $pne), $ends), date_i18n(__('Y', $pne), $starts))
				: sprintf('%s - %s, %s', date_i18n(__('F j g:ia', $pne), $starts), date_i18n(__('j g:ia', $pne), $ends), date_i18n(__('Y', $pne), $starts));
		} else if ($same_year) {
			return $all_day
				? sprintf('%s - %s, %s', date_i18n(__('F j', $pne), $starts), date_i18n(__('F j', $pne), $ends), date_i18n(__('Y', $pne), $starts))
				: sprintf('%s - %s, %s', date_i18n(__('F j g:ia', $pne), $starts), date_i18n(__('F j g:ia', $pne), $ends), date_i18n(__('Y', $pne), $starts));
		} else {
			return $all_day
				? sprintf('%s - %s', date_i18n(__('F j, Y', $pne), $starts), date_i18n(__('F j, Y', $pne), $ends))
				: sprintf('%s - %s', date_i18n(__('F j g:ia, Y', $pne), $starts), date_i18n(__('F j g:ia, Y', $pne), $ends));
		}
	}
	
	static function debug($var, $die = false) {
		echo "<pre style='padding:5px;background-color:#EEE;white-space:pre-wrap;'>".htmlentities(print_r($var,1))."</pre>";
		if ($die) die;
	}
}
if ( is_admin() ) {
	// We are on the back end
	@require('plugin-update-checker/plugin-update-checker.php');
		$PNEXUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/kmhcreative/press-news-events-xtended',
			__FILE__,'pnex'
		);
		$PNEXUpdateChecker->getVcsApi()->enableReleaseAssets();
}