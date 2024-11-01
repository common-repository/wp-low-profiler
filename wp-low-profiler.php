<?php
/*
Plugin Name: WP low Profiler
Plugin URI: http://anappleaday.konceptus.net/posts/wp-low-profiler/
Description: Enables a user to create "low profile" posts and pages that are selectively hidden in different views throughout the blog, such as on the front page, category pages, search results, etc... The hidden item remains otherwise accessible directly using the permalink, and also visible to search engines as part of the sitemap. This plugin enables new SEO possibilities for authors since it enables them to create new posts and pages without being forced to display them on their front and in feeds.
Version: 2.0.3
Author: Robert Mahfoud
Author URI: http://anappleaday.konceptus.net
Text Domain: wp_low_profiler
*/

/*  Copyright 2009  Robert Mahfoud  (email : robert.mahfoud@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function wplp_log($msg) {
	if( defined('WPLP_DEBUG') && WPLP_DEBUG )
	   error_log("WPLP-> $msg");
}

function wplp_init() {
	global $table_prefix;
    if( !defined('WPLP_TABLE_NAME') )
        define('WPLP_TABLE_NAME', "${table_prefix}postmeta");
    if( !defined('WP_POSTS_TABLE_NAME') )
        define('WP_POSTS_TABLE_NAME', "${table_prefix}posts");
    if( !defined('WPLP_DEBUG') ) {
        define('WPLP_DEBUG', defined('WP_DEBUG') && WP_DEBUG ? 1 : 0);
	}
}
wplp_init();


function wplp_is_front_page() {
	return is_front_page();
}

function wplp_is_feed() {
	return is_feed();
}

function wplp_is_category() {
	return !wplp_is_front_page() && !wplp_is_feed() && is_category();
}

function wplp_is_tag() {
	return !wplp_is_front_page() && !wplp_is_feed() && is_tag();
}

function wplp_is_author() {
	return !wplp_is_front_page() && !wplp_is_feed() && is_author();
}

function wplp_is_archive() {
    return !wplp_is_front_page() && !wplp_is_feed() && is_date();
}

function wplp_is_search() {
    return is_search();
}

function is_applicable($item_type) {
	return !is_admin() && (($item_type == 'post' && !is_single()) || $item_type == 'page') ;
}

// Create Text Domain For Translations
add_action('init', 'wplp_textdomain');
function wplp_textdomain() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain('wp-low-profiler', ABSPATH."/$plugin_dir", $plugin_dir);
}

// Migrate to the new database schema and clean up old schema...
// Should run only once in the lifetime of the plugin... 
function wplp_migrate_db() {
    wplp_log("called: wplp_migrate_db");
	/* When I first released this plugin, I was young and crazy and didn't know about the postmeta table. 
     * With time I became wiser and wiser and decided to migrate the implementation to rely on postmeta.
     * I hope it was not a bad idea...
     */
	global $wpdb;
    global $table_prefix;
	$dbname = $wpdb->get_var("SELECT database()");
    if( !$dbname )
        return;
    $legacy_table_name = "${table_prefix}lowprofiler_posts";
    $legacy_table_exists = $wpdb->get_var("SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = '$dbname' AND table_name = '$legacy_table_name';");
    if( $legacy_table_exists ) {
        wplp_log("Migrating legacy table...");
    	// move everything to the postmeta table
        $existing = $wpdb->get_results("SELECT wplp_post_id, wplp_flag, wplp_value from $legacy_table_name", ARRAY_N);
		// scan them one by one and insert the corresponding fields in the postmeta table
        $count = 0;
        foreach($existing as $existing_array) {
        	$wplp_post_id = $existing_array[0];
            $wplp_flag = $existing_array[1];
            $wplp_value = $existing_array[2];
            if( $wplp_flag == 'home' )
                $wplp_flag = 'front';
            if( $wplp_value == 'home' )
                $wplp_value = 'front';
            if( $wplp_flag != 'page' ) {
            	$wpdb->query("INSERT INTO ".WPLP_TABLE_NAME."(post_id, meta_key, meta_value) VALUES($wplp_post_id, '_wplp_post_$wplp_flag', '1')");
            } else {
                $wpdb->query("INSERT INTO ".WPLP_TABLE_NAME."(post_id, meta_key, meta_value) VALUES($wplp_post_id, '_wplp_page_flags', $wplp_value)");
            }
            ++$count;
        }
        wplp_log("$count entries migrated from legacy table.");
        // delete the old table
        $wpdb->query("TRUNCATE TABLE $legacy_table_name");
        $wpdb->query("DROP TABLE $legacy_table_name");
        wplp_log("Legacy table deleted.");
    }
}

// Activating the plugin
function wplp_activate() {
    wplp_log("called: wplp_activate");
	global $wpdb;
    wplp_init();
	if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} else {
		die('We have a problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
	}

	wplp_migrate_db();
}
//register_activation_hook( __FILE__, 'wplp_activate' );
add_action('activate_wp-low-profiler/wp-low-profiler.php', 'wplp_activate' );

function wplp_update_low_profile($id, $lp_flag, $lp_value) {
    wplp_log("called: wplp_update_low_profile");
	global $wpdb;
	$item_type = get_post_type($id);
	if( ($item_type == 'post' && !$lp_value) || ($item_type == 'page' && ( ($lp_flag == '_wplp_page_flags' && $lp_value == 'none') || ($lp_flag == '_wplp_page_search' && !$lp_value) ) ) ) {
		wplp_unset_low_profile($item_type, $id, $lp_flag);
	} else {
		wplp_set_low_profile($item_type, $id, $lp_flag, $lp_value);
	}
}

function wplp_unset_low_profile($item_type, $id, $lp_flag) {
    wplp_log("called: wplp_unset_low_profile");
	global $wpdb;
	// Delete the flag from the database table
	$wpdb->query("DELETE FROM ".WPLP_TABLE_NAME." WHERE post_id = $id AND meta_key = '$lp_flag'");
}

function wplp_set_low_profile($item_type, $id, $lp_flag, $lp_value) {
    wplp_log("called: wplp_set_low_profile");
	global $wpdb;	
	// Ensure No Duplicates!
	$check = $wpdb->get_var("SELECT count(*) FROM ".WPLP_TABLE_NAME." WHERE post_id = $id AND meta_key='$lp_flag'");
	error_log("Check: $check");
	if(!$check) {
		$wpdb->query("INSERT INTO ".WPLP_TABLE_NAME."(post_id, meta_key, meta_value) VALUES($id, '$lp_flag', '$lp_value')");
	} elseif( $item_type == 'page' && $lp_flag == "_wplp_page_flags" ) {
		$wpdb->query("UPDATE ".WPLP_TABLE_NAME." set meta_value = '$lp_value' WHERE post_id = $id and meta_key = '$lp_flag'");
	}
}

function wplp_exclude_low_profile_items($item_type, $posts) {
    wplp_log("called: wplp_exclude_low_profile_items");
	if( $item_type != 'page' )
		return $posts;   // regular posts & search results are filtered in wplp_query_posts_join
	else {
        if( is_applicable('page') ) {
			global $wpdb;
			// now loop over the pages, and exclude the ones with low profile in this context
			$result = array();
			foreach($posts as $post) {
				$check = strval($wpdb->get_var("SELECT meta_value FROM ".WPLP_TABLE_NAME." WHERE post_id = $post->ID and meta_key = '_wplp_page_flags'"));
				if( ($check == 'front' && wplp_is_front_page()) || $check == 'all') {
					// exclude page
				} else
					$result[] = $post;
			}
	        return $result;
        } else
            return $posts;
    }
}

// hook function to filter out low profile pages
function wplp_exclude_low_profile_pages($posts) {
    wplp_log("called: wplp_exclude_low_profile_pages");
	return wplp_exclude_low_profile_items('page', $posts);
}

// register the filter "get_pages" to hide low profile pages
add_filter('get_pages', 'wplp_exclude_low_profile_pages');

add_action('admin_menu', 'wplp_add_post_edit_meta_box');
function wplp_add_post_edit_meta_box() {
    wplp_log("called: wplp_add_post_edit_meta_box");
	add_meta_box('lowprofilerdivpost', __('Post low Profile Attributes', 'wp-low-profiler'), 'wplp_metabox_post_edit', 'post', 'side');
	add_meta_box('lowprofilerdivpage', __('Page low Profile Attributes', 'wp-low-profiler'), 'wplp_metabox_page_edit', 'page', 'side');
}

function wplp_metabox_post_edit() {
    wplp_log("called: wplp_metabox_post_edit");
	global $wpdb;
	
	$id = isset($_GET['post']) ? intval($_GET['post']) : 0;

	$wplp_post_front = 0;
	$wplp_post_category = 0;
	$wplp_post_tag = 0;
	$wplp_post_author = 0;
	$wplp_post_archive = 0;
	$wplp_post_search = 0;
	$wplp_post_feed = 0;
	
	if($id > 0) {
		$flags = $wpdb->get_results("SELECT meta_key from ".WPLP_TABLE_NAME." where post_id = $id and meta_key like '_wplp_%'", ARRAY_N);
		if( $flags ) {
			foreach($flags as $flag_array) {
				$flag = $flag_array[0];
				// remove the leading _
				$flag = substr($flag, 1, strlen($flag)-1);
				${$flag} = 1;
			} 
		}
	}
?>
	<label for="wplp_post_front" class="selectit"><input type="checkbox" id="wplp_post_front" name="wplp_post_front" value="1"<?php checked($wplp_post_front, 1); ?>/>&nbsp;<?php _e('Hide on the front page.', 'wp-low-profiler'); ?></label>
	<br />
	<label for="wplp_post_category" class="selectit"><input type="checkbox" id="wplp_post_category" name="wplp_post_category" value="1"<?php checked($wplp_post_category, 1); ?>/>&nbsp;<?php _e('Hide on category pages.', 'wp-low-profiler'); ?></label>
	<br />
	<label for="wplp_post_tag" class="selectit"><input type="checkbox" id="wplp_post_tag" name="wplp_post_tag" value="1"<?php checked($wplp_post_tag, 1); ?>/>&nbsp;<?php _e('Hide on tag page(s).', 'wp-low-profiler'); ?></label>
	<br />
	<label for="wplp_post_author" class="selectit"><input type="checkbox" id="wplp_post_author" name="wplp_post_author" value="1"<?php checked($wplp_post_author, 1); ?>/>&nbsp;<?php _e('Hide on author pages.', 'wp-low-profiler'); ?></label>
	<br />
	<label for="wplp_post_archive" class="selectit"><input type="checkbox" id="wplp_post_archive" name="wplp_post_archive" value="1"<?php checked($wplp_post_archive, 1); ?>/>&nbsp;<?php _e('Hide in date archives (month, day, year, etc...)', 'wp-low-profiler'); ?></label>
	<br />
	<label for="wplp_post_search" class="selectit"><input type="checkbox" id="wplp_post_search" name="wplp_post_search" value="1"<?php checked($wplp_post_search, 1); ?>/>&nbsp;<?php _e('Hide in search results.', 'wp-low-profiler'); ?></label>
	<br />
	<label for="wplp_post_feed" class="selectit"><input type="checkbox" id="wplp_post_feed" name="wplp_post_feed" value="1"<?php checked($wplp_post_feed, 1); ?>/>&nbsp;<?php _e('Hide in feed(s).', 'wp-low-profiler'); ?></label>
    <br />
    <div style="float:right;font-size: xx-small;"><a href="http://anappleaday.konceptus.net/posts/wp-low-profiler/#comments"><?php _e("Leave feedback and report bugs...", 'wp-low-profiler'); ?></a></div>
    <br />
    <div style="float:right;font-size: xx-small;"><a href="http://wordpress.org/extend/plugins/wp-low-profiler/"><?php _e("Give 'WP low Profiler' a good rating...", 'wp-low-profiler'); ?></a></div>
    <br />
<?php
}

function wplp_metabox_page_edit() {
    wplp_log("called: wplp_metabox_page_edit");
	global $wpdb;
	
	$id = isset($_GET['post']) ? intval($_GET['post']) : 0;

	$wplp_page = 'none';
	$wplp_page_search_show = 1;
	
	if($id > 0) {
		$flags = $wpdb->get_results("SELECT meta_value from ".WPLP_TABLE_NAME." where post_id = $id and meta_key = '_wplp_page_flags'", ARRAY_N);
		if( $flags )
			$wplp_page = $flags[0][0];
        $search = $wpdb->get_results("SELECT meta_value from ".WPLP_TABLE_NAME." where post_id = $id and meta_key = '_wplp_page_search'", ARRAY_N);
        if( $search )
            $wplp_page_search_show = ! $search[0][0];
	}
?>
	<label class="selectit"><input type="radio" id="wplp_page_none" name="wplp_page" value="none"<?php checked($wplp_page, 'none'); ?>/>&nbsp;<?php _e('Show normally everywhere.', 'wp-low-profiler'); ?></label>
	<br />
	<br />
	<label class="selectit"><input type="radio" id="wplp_page_front" name="wplp_page" value="front"<?php checked($wplp_page, 'front'); ?>/>&nbsp;<?php _e('Hide when listing pages on the front page.', 'wp-low-profiler'); ?></label>
	<br />
    <br />
    <label class="selectit"><input type="radio" id="wplp_page_all" name="wplp_page" value="all"<?php checked($wplp_page, 'all'); ?>/>&nbsp;<?php _e('Hide everywhere pages are listed.', 'wp-low-profiler'); ?><sup>*</sup></label>
    <div style="height:18px;margin-left:20px">
        <div id="wplp_page_search_show_div"><label class="selectit"><input type="checkbox" id="wplp_page_search_show" name="wplp_page_search_show" value="1"<?php checked($wplp_page_search_show, 1); ?>/>&nbsp;<?php _e('Keep in search results.', 'wp-low-profiler'); ?></label></div>
    </div>
    <br />
    <div style="float:right;clear:both;font-size:x-small;">* Will still show up in sitemap.xml if you generate one automatically. See <a href="http://anappleaday.konceptus.net/posts/wp-low-profiler/">details</a>.</div>
    <br />
    <br />
    <br />
    <div style="float:right;font-size: xx-small;"><a href="http://anappleaday.konceptus.net/posts/wp-low-profiler/#comments"><?php _e("Leave feedback and report bugs...", 'wp-low-profiler'); ?></a></div>
    <br />
	<div style="float:right;clear:both;font-size:xx-small;"><a href="http://wordpress.org/extend/plugins/wp-low-profiler/"><?php _e("Give 'WP low Profiler' a good rating...", 'wp-low-profiler'); ?></a></div>
	<br />
    <script type="text/javascript">
    <!--
		// toggle the wplp_page_search_show checkbox
        var wplp_page_search_show_callback = function () {
            if(jQuery("#wplp_page_all").is(":checked"))
                jQuery("#wplp_page_search_show_div").show();
            else
                jQuery("#wplp_page_search_show_div").hide();
        };
        jQuery("#wplp_page_all").change(wplp_page_search_show_callback);
        jQuery("#wplp_page_front").change(wplp_page_search_show_callback);
        jQuery("#wplp_page_none").change(wplp_page_search_show_callback);
        jQuery(document).ready( wplp_page_search_show_callback );
    //-->
    </script>
<?php
}

function wplp_save_post($id) {
    wplp_log("called: wplp_save_post");
	$item_type = get_post_type($id);
	if( $item_type == 'post' ) {
		$wplp_post_front = isset($_POST['wplp_post_front']) ? $_POST['wplp_post_front'] : 0;
		$wplp_post_category = isset($_POST['wplp_post_category']) ? $_POST['wplp_post_category'] : 0;
		$wplp_post_tag = isset($_POST['wplp_post_tag']) ? $_POST['wplp_post_tag'] : 0;
		$wplp_post_author = isset($_POST['wplp_post_author']) ? $_POST['wplp_post_author'] : 0;
		$wplp_post_archive = isset($_POST['wplp_post_archive']) ? $_POST['wplp_post_archive'] : 0;
		$wplp_post_search = isset($_POST['wplp_post_search']) ? $_POST['wplp_post_search'] : 0;
		$wplp_post_feed = isset($_POST['wplp_post_feed']) ? $_POST['wplp_post_feed'] : 0;
		
		wplp_update_low_profile($id, '_wplp_post_front', $wplp_post_front);
		wplp_update_low_profile($id, '_wplp_post_category', $wplp_post_category);
		wplp_update_low_profile($id, '_wplp_post_tag', $wplp_post_tag);
		wplp_update_low_profile($id, '_wplp_post_author', $wplp_post_author);
		wplp_update_low_profile($id, '_wplp_post_archive', $wplp_post_archive);
		wplp_update_low_profile($id, '_wplp_post_search', $wplp_post_search);
		wplp_update_low_profile($id, '_wplp_post_feed', $wplp_post_feed);
	} elseif( $item_type == 'page' ) {
		$wplp_page = isset($_POST['wplp_page']) ? $_POST['wplp_page'] : 'none';
		wplp_update_low_profile($id, "_wplp_page_flags", $wplp_page);
		if( $wplp_page == 'all' ) {
            $wplp_page_search_show = isset($_POST['wplp_page_search_show']) ? $_POST['wplp_page_search_show'] : 0;
            wplp_update_low_profile($id, "_wplp_page_search", ! $wplp_page_search_show);
		} else
            wplp_update_low_profile($id, "_wplp_page_search", 0);
	}	
}
add_action('save_post', 'wplp_save_post');

function wplp_delete_post($post_id) {
    wplp_log("called: wplp_delete_post");
	global $wpdb;
	// Delete all post flags from the database table
	$wpdb->query("DELETE FROM ".WPLP_TABLE_NAME." WHERE post_id = $post_id and meta_key like '_wplp_%'");
}
add_action('delete_post', 'wplp_delete_post');

function wplp_query_posts_where($where) {
    wplp_log("called: wplp_query_posts_where");
	// filter posts on one of the three kinds of contexts: front, category, feed
	if( is_applicable('post') && is_applicable('page') ) {
		$where .= ' AND wplptbl.post_id IS NULL ';
	}
	//echo "\n<!-- WPLP: ".$where." -->\n";
	return $where;
}

function wplp_query_posts_join($join) {
    wplp_log("called: wplp_query_posts_join");
	if( is_applicable('post') && is_applicable('page')) {
		if( !$join )
			$join = '';
		$join .= ' LEFT JOIN '.WPLP_TABLE_NAME.' wplptbl ON '.WP_POSTS_TABLE_NAME.'.ID = wplptbl.post_id and wplptbl.meta_key like \'_wplp_%\'';
        // filter posts 
		$join .= ' AND (('.WP_POSTS_TABLE_NAME.'.post_type = \'post\' ';
		if( wplp_is_front_page() )
			$join .= ' AND wplptbl.meta_key = \'_wplp_post_front\' ';
		elseif( wplp_is_category())
			$join .= ' AND wplptbl.meta_key = \'_wplp_post_category\' ';
		elseif( wplp_is_tag() )
			$join .= ' AND wplptbl.meta_key = \'_wplp_post_tag\' ';
		elseif( wplp_is_author() )
			$join .= ' AND wplptbl.meta_key = \'_wplp_post_author\' ';
		elseif( wplp_is_archive() )
			$join .= ' AND wplptbl.meta_key = \'_wplp_post_archive\' ';
        elseif( wplp_is_feed())
            $join .= ' AND wplptbl.meta_key = \'_wplp_post_feed\' ';
		elseif( wplp_is_search())
			$join .= ' AND wplptbl.meta_key = \'_wplp_post_search\' ';
		else
            $join .= ' AND wplptbl.meta_key not like  \'_wplp_%\' ';
		$join .= ')';	
		// pages
        $join .= ' OR ('.WP_POSTS_TABLE_NAME.'.post_type = \'page\' AND wplptbl.meta_key <> \'_wplp_page_flags\'';
        if( wplp_is_search())
            $join .= ' AND wplptbl.meta_key = \'_wplp_page_search\' ';
        else
            $join .= ' AND wplptbl.meta_key not like \'_wplp_%\' ';
        $join .= '))';   
	}
    //echo "\n<!-- WPLP: ".$join." -->\n";
    return $join;
}
add_filter('posts_where_paged', 'wplp_query_posts_where');
add_filter('posts_join_paged', 'wplp_query_posts_join');


function wplp_deprecate_wp_low_profiler( $file, $plugin_data ) {
    wplp_log("called: wplp_deprecate_wp_low_profiler");
    global $wp_version;
    if( $plugin_data['Name'] == 'WP low Profiler' ) {
        if( ! $wp_version || $wp_version < '2.8' )
    	   echo '<tr><td colspan="5" class="plugin-update">';
    	else   // account for v2.8+ new look
            echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">';
        if ( ! current_user_can('update_plugins') )
            printf( __('%1$s has been deprecated and replaced by <a href="http://wordpress.org/extend/plugins/wp-hide-post/">WP Hide Post</a>.'), $plugin_data['Name']);
        else {
            $install_link = '<a href="' . admin_url('plugin-install.php?tab=plugin-information&amp;plugin=wp-hide-post&amp;TB_iframe=true&amp;width=600&amp;height=800') . '" class="thickbox onclick" title="WP Hide Post">' . __('Upgrade now!') . '</a>';
            printf( __('%1$s has been deprecated and replaced by <a href="http://wordpress.org/extend/plugins/wp-hide-post/">WP Hide Post</a>.') . " $install_link.", $plugin_data['Name']);
        }
        if( ! $wp_version || $wp_version < '2.8' )
            echo '</td></tr>';
        else   // account for v2.8+ new look
            echo '</div></td></tr>';
    }
}
add_action('after_plugin_row', 'wplp_deprecate_wp_low_profiler', 10, 2);

?>