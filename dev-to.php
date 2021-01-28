<?php
/**
 * Dev 2 Posts Plugin is a new integration from the Dev.To website.
 *
 * @package Dev 2 Posts Plugin
 * @author Pixium Digital Pte Ltd
 * @license GPL-2.0+
 * @copyright 2020 Pixium Digital Pte Ltd. All rights reserved.
 *
 *            @wordpress-plugin
 *            Plugin Name: Dev 2 Posts Plugin
 *            Description: Integrates Dev.To posts into Wordpress.
 *            Version: 1.0
 *            Author: Pixium Digital Pte Ltd
 *            Author URI: https://pixiumdigital.com/
 *            Text Domain: dev-2-posts
 *            Contributors: Pixium Digital Pte Ltd
 *            License: GPL-2.0+
 *            License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Define plugin root url and require class files
 *
 * @since 1.0
 */
define( 'PLUGIN_ROOT_URL', plugin_dir_url( __FILE__ ) );
require_once plugin_dir_path(  __FILE__  ) . '/includes/class-dev-2-posts-admin.php';
require_once plugin_dir_path(  __FILE__  ) . '/includes/class-dev-2-posts-shortcode.php';
 
/**
 * Plugin Activation hook
 *
 * @since 1.0
 */
function dev_2_posts_activate() {
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'dev_2_posts_activate' );


/**
 * Plugin Deactivation hook.
 * @since 1.0
 */
function dev_2_posts_deactivate() {
    flush_rewrite_rules();
}


/**
 * Append saved textfield value to each post
 *
 * @since 1.0
 */
// add_filter ( 'the_content', 'dev_to_content' );
// function dev_to_content($content) {
// 	return $content . stripslashes_deep ( esc_attr ( get_option ( 'dev-to-api-key' ) ) );
// }