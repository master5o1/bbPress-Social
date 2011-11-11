<?php
/**
 * Plugin Name: bbPress Social
 * Plugin URI: http://master5o1.com/
 * Description: Google +1 button
 * Version: 0.1
 * Author: Jason Schwarzenberger
 * Author URI: http://master5o1.com/
 */
/*  Copyright 2011  Jason Schwarzenberger  (email : jason@master5o1.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'bbp_template_before_replies_loop', array('bbp_social', 'buttons') );
add_action( 'bbp_init', array('bbp_social', 'script') );
add_filter( 'head_tag_attributes', array('bbp_social', 'open_graph_head') );
add_action( 'bbp_head', array('bbp_social', 'open_graph'), -999);

add_action( 'bbp_admin_init', array( 'bbp_social', 'register_admin_settings'), 20 );

class bbp_social {

	function get_fb_app_id() { return get_option('bbp_social_fb_app_id', ''); }
	
	function get_tumblr() {
		if (get_option('bbp_social_tumblr', '') == 1) return true;
		return false;
	}
	function get_twitter() {
		if (get_option('bbp_social_twitter', '') == 1) return true;
		return false;
	}
	function get_plus() {
		if (get_option('bbp_social_plus', '') == 1) return true;
		return false;
	}
	
	function register_admin_settings() {
		add_settings_section( 'bbp_social', __('bbPress Social', 'bbp-social'), array('bbp_social','settings'), 'bbpress' );
		add_settings_field( 'bbp_social_tumblr', __( 'Tumblr', 'bbp-social' ), array('bbp_social', 'settings_tumblr'), 'bbpress', 'bbp_social' );
		add_settings_field( 'bbp_social_twitter', __( 'Twitter', 'bbp-social' ), array('bbp_social', 'settings_twitter'), 'bbpress', 'bbp_social' );
		add_settings_field( 'bbp_social_plus', __( 'Google+', 'bbp-social' ), array('bbp_social', 'settings_plus'), 'bbpress', 'bbp_social' );
		add_settings_field( 'bbp_social_fb_app_id', __( 'Facebook App ID', 'bbp-social' ), array('bbp_social', 'settings_fb_id'), 'bbpress', 'bbp_social' );
		register_setting( 'bbp_social', 'bbp_social_tumblr' );
		register_setting( 'bbp_social', 'bbp_social_twitter' );
		register_setting( 'bbp_social', 'bbp_social_plus' );
		register_setting( 'bbp_social', 'bbp_social_fb_app_id' );
	}
	
	function settings() {
		echo "Social Media buttons for topic pages.";
		settings_fields( 'bbp_social' );
	}
	
	function settings_tumblr() {
		$checked = '';
		if ( bbp_social::get_tumblr() )
			$checked = ' checked="true"';
		?>
		<input id="bbp_social_tumblr" name="bbp_social_tumblr" type="checkbox" value="1"<?php echo $checked ?> />
		<label for="bbp_social_tumblr"><?php _e( 'Show Tumblr share button?', 'bbp-social' ); ?></label>
		
		<?php
	}
	
	function settings_twitter() {
		$checked = '';
		if ( bbp_social::get_twitter() )
			$checked = ' checked="true"';
		?>
		
		<input id="bbp_social_twitter" name="bbp_social_twitter" type="checkbox" value="1"<?php echo $checked ?> />
		<label for="bbp_social_twitter"><?php _e( 'Show Twitter share button?', 'bbp-social' ); ?></label>
		
		<?php
	}
	
	function settings_plus() {
		$checked = '';
		if ( bbp_social::get_plus() )
			$checked = ' checked="true"';
		?>
		
		<input id="bbp_social_plus" name="bbp_social_plus" type="checkbox" value="1"<?php echo $checked ?> />
		<label for="bbp_social_plus"><?php _e( 'Show Google +1 button?', 'bbp-social' ); ?></label>
		
		<?php
	}
	
	function settings_fb_id() {
		?>
		
		<input id="bbp_social_fb_app_id" name="bbp_social_fb_app_id" type="text" value="<?php echo bbp_social::get_fb_app_id(); ?>" />
		<label for="bbp_social_fb_app_id"><?php _e( 'Setting this will show a Like button. Empty to hide it.', 'bbp-social' ); ?></label>
		
		<?php
	}

	function open_graph_head($atts) {
		if ( !function_exists('is_bbpress') ) return $atts;
		if ( !is_bbpress() ) return $atts;
		if ( bbp_social::get_fb_app_id() == '' ) return $atts;
		$atts .= ' prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# tartarusnz: http://ogp.me/ns/fb/tartarusnz#"';
		return trim($atts);
	}

	function open_graph() {
		if ( !function_exists('bbp_get_topic_id') ) return;
		if ( bbp_get_topic_id() == 0 ) return;
		if ( bbp_social::get_fb_app_id() == '' ) return;
		
		echo "\n";
		echo '<meta property="fb:app_id"         content="' . bbp_social::get_fb_app_id() . '" />' . "\n";
		echo '<meta property="og:type"           content="tartarusnz:forum_topic" />' . "\n";
		$user = get_userdata(bbp_get_topic_author_id());
		echo '<meta property="tartarusnz:author" content="' . $user->user_nicename . '" />' . "\n";
		echo '<meta property="og:url"            content="' . bbp_get_topic_permalink() . '" />' . "\n";
		echo '<meta property="og:title"          content="' . bbp_get_topic_title() . '" />' . "\n";
		echo '<meta property="og:description"    content="' . esc_attr( strip_tags( trim(bbp_get_topic_content()) ) ) . '" />' . "\n";
		echo '<meta property="og:image"          content="' . home_url('/favicon.png') . '" />' . "\n";
	}

	function buttons($html = '') {
		$social = array();
		if ( bbp_social::get_tumblr() ) {
			$social['tumblr'] = '<span style="margin: 0 0.45em 0 0; padding: 0 0 0 0; height: 2.0em; vertical-align: middle; display: inline-block;"><a href="http://www.tumblr.com/share" title="Share on Tumblr" style="text-decoration: none; display:inline-block; text-indent:-9999px; overflow:hidden; width:81px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_1.png\') top left no-repeat transparent;">&nbsp;</a></span>';
		}
		if ( bbp_social::get_twitter() ) {
			$social['twitter'] = '<span style="margin: 0 -0.45em 0 0; padding: 0 0 0 0; height: 2.0em; vertical-align: middle; display: inline-block;"><a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></span>';
		}
		if ( bbp_social::get_plus() ) {
		$social['plusone'] = '<span style="margin: 0 -1.5em 0 0; padding: 0 0 0 0; height: 2.0em; vertical-align: middle; display: inline-block;"><g:plusone size="medium"></g:plusone></span>';
		}
		if ( bbp_social::get_fb_app_id() != '' ) {
			$social['facebook'] = '<span style="margin: 0 -1.0em 0.20em 0; padding: 0 0 0 0; height: 2.0em; vertical-align: middle; display: inline-block;"><div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#appId=' . bbp_social::get_fb_app_id() . '&amp;xfbml=1"></script><fb:like href="" send="false" layout="button_count" width="" show_faces="false" font=""></fb:like></span>';
		}
		if ( empty($social) ) return;
		$out = $html;
		foreach ($social as $s) {
			$out .= '' . $s . '';
		}
		print '<div style="text-align: left; margin: 0 0 -0.5em 0;">';
		print $out;
		print '</div><div style="clear:both;"></div>';
	}
	
	function script() {
		if ( bbp_social::get_plus() ) {
			wp_register_script( 'plusone', 'https://apis.google.com/js/plusone.js' );
			wp_enqueue_script( 'plusone' );
		}
		if ( bbp_social::get_tumblr() ) {
			wp_register_script( 'tumbler', 'http://platform.tumblr.com/v1/share.js' );
			wp_enqueue_script( 'tumbler' );
		}
	}

}
?>