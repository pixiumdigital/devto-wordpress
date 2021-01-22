<?php
/**
 * Dev To Plugin is a new integration from the Dev.To website.
 *
 * @package Dev To Plugin
 * @author Pixium Digital Pte Ltd
 * @license GPL-2.0+
 * @copyright 2020 Pixium Digital Pte Ltd. All rights reserved.
 *
 *            @wordpress-plugin
 *            Plugin Name: Dev To News Plugin
 *            Description: Dev To News integrator into wordpress.
 *            Version: 1.0
 *            Author: Pixium Digital Pte Ltd
 *            Author URI: https://pixiumdigital.com/
 *            Text Domain: dev-to
 *            Contributors: Pixium Digital Pte Ltd
 *            License: GPL-2.0+
 *            License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
 
/**
 * Adding Submenu under Settings Tab
 *
 * @since 1.0
 */
function devto_add_menu() {
	add_submenu_page ( "options-general.php", "DevTo Plugin", "DevTo Settings", "manage_options", "dev-to", "dev_to_page" );
}
add_action ( "admin_menu", "devto_add_menu" );
 
/**
 * Setting Page Options
 * - add setting page
 * - save setting page
 *
 * @since 1.0
 */
function dev_to_page() { ?>
		<div class="wrap">
			<h1>
				<a href="https://dev.to" target="_blank">DEV.TO</a> Plugin
			</h1>
			<h3>
				From: <a href="https://pixiumdigital.com" target="_blank">Pixium Digital Pte Ltd</a>
			</h3>
		
			<form method="post" action="options.php">
				<?php
					settings_fields ( "dev_to_config" );
					do_settings_sections ( "dev-to" );
					submit_button ();
				?>
			</form>

			<div class="postbox" style="width: 100%; padding: 30px;">
				<p>You can use the [dev_to] to include all the articles into a page or the [dev_to_last] which will display the last 3 articles only.</p>
			</div>


			<!-- Get the list of articles here -->
			<?php
				// echo "API Key: ".get_option ( 'dev-to-api-key' )."<br>";
				if(get_option ( 'dev-to-api-key' )){
					$result = get_articles();;
					// handle curl error
					if ($result) {
						echo "<br>Nb of Articles detected: ".count($result)."<br>";
					} else {
						echo "ERROR";
					}
				}

			?>

		</div>
 
<?php
}
 
/**
 * Init setting section, Init setting field and register settings page
 *
 * @since 1.0
 */
function dev_to_settings() {
	add_settings_section ( "dev_to_config", "", null, "dev-to" );
	add_settings_field ( "dev-to-api-key", "Enter your dev.to API KEY", "dev_to_key", "dev-to", "dev_to_config" );
	register_setting ( "dev_to_config", "dev-to-api-key" );

	add_settings_field ( "dev-to-username", "Enter the dev.to username to get the articles from", "dev_to_username", "dev-to", "dev_to_config" );
	register_setting ( "dev_to_config", "dev-to-username" );

	add_settings_field ( "dev-to-type", "Enter the dev.to username to get the articles from", "dev_to_type", "dev-to", "dev_to_config" );
	register_setting ( "dev_to_config", "dev-to-type" );
}
add_action ( "admin_init", "dev_to_settings" );
 



/**
 * Add simple textfield value to setting page
 *
 * @since 1.0
 */
function dev_to_key() {
	?>
<div class="postbox" style="width: 65%; padding: 30px;">
	<input type="text" name="dev-to-api-key" style="width:100%;"
		value="<?php echo stripslashes_deep ( esc_attr ( get_option ( 'dev-to-api-key' ) ) ); ?>" />
</div>
<?php
}


/**
 * Add a field for the username
 *
 * @since 1.0
 */
function dev_to_username() {
	?>
<div class="postbox" style="width: 65%; padding: 30px;">
	<input type="text" name="dev-to-username" style="width:100%;"
		value="<?php echo stripslashes_deep ( esc_attr ( get_option ( 'dev-to-username' ) ) ); ?>" />
</div>
<?php
}


/**
 * Add a field for the type (organization vs user)
 *
 * @since 1.0
 */
function dev_to_type() {
	?>
<div class="postbox" style="width: 65%; padding: 30px;">
	<select name="dev-to-type" id="devto-type">
		<option value="organization">Organization</option>
		<!-- <option value="user">User</option> -->
		<option value="me">Me</option>
	</select>
</div>
<?php
}



 



// add_action( 'wp_enqueue_style', 'dev_to_add_style' );
// /**
//  * Enqueue plugin style-file
//  */
// function dev_to_add_style() {
//     // Respects SSL, Style.css is relative to the current file
// 	wp_register_style( 'dev-to-style', plugins_url('./style.css?v=1.0', __FILE__) );
// 	// $plugin_dir = Helpers::plugin_url();
// 	// wp_enqueue_style( 'dev-to-style',
// 	// 	plugins_url( './style.css', __FILE__ ),
// 	// 	array());

//     wp_enqueue_style( 'dev-to-style' );
// }


function get_articles(){
	$crl = null;
	if(get_option('dev-to-type')=='organization'){
		$crl = curl_init("https://dev.to/api/organizations/".get_option('dev-to-username')."/articles");
	}else if(get_option('dev-to-type')=='me'){
		$crl = curl_init("https://dev.to/api/articles/me");
	}

	if($crl){
		curl_setopt($crl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'api-key: ' . get_option ( 'dev-to-api-key' )
			]
		);
		// curl_setopt($crl, CURLOPT_VERBOSE, false );
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($crl);
		$json = json_decode($result);
		curl_close($crl);
		return $json;
	}
	return null;
}



add_shortcode( 'dev_to_last' , 'render_last_dev_to_articles');
function render_last_dev_to_articles() {
	echo '<div class="row">';
		$count = 0;
		$json = get_articles();
		if($json){
			echo '<link rel="stylesheet" href="'.plugins_url( './style.css?'.date('l jS \of F Y h:i:s A'), __FILE__ ).'">';
			foreach($json as $article){
				render_single_article($article);
				$count++;
				if($count>=3){
					break;
				}
			}
		}
	echo '</div>';
}


add_shortcode( 'dev_to' , 'render_dev_to_articles');
function render_dev_to_articles() {
	// echo "API Key: ".get_option ( 'dev-to-api-key' )."<br>";

	if(get_option('dev-to-api-key') && get_option('dev-to-username')){
		$json = get_articles();
		// handle curl error
		if ($json) {
			echo '<link rel="stylesheet" href="'.plugins_url( './style.css?'.date('l jS \of F Y h:i:s A'), __FILE__ ).'">';
			// var_dump($json);
			echo '<div class="row">';
			foreach($json as $article){
				render_single_article($article);
			}
			echo '</div>';
		} else {
			echo "ERROR";
		}
		
	}
}


/**
 * Render the article CARD
 */
function render_single_article($article){
	$timestr = strtotime($article->published_at);
	echo '<div class="example-1 card">';
		echo '<div class="wrapper" style="background: url('.$article->cover_image.') center / cover no-repeat;">';
			echo '<div class="date">';
				echo '<span class="day">'.date('d', $timestr).'</span>';
				echo '<span class="month">'.date('M', $timestr).'</span>';
				echo '<span class="year">'.date('Y', $timestr).'</span>';
			echo '</div>';
			echo '<div class="data">';
				echo '<div class="content">';
					echo '<span class="author">'.($article && $article->organization) ? $article->organization->name : '-'.'</span>';
					echo '<h1 class="title"><a href="'.$article->url.'" target="_blank">'.$article->title.'</a></h1>';
					// <p class="text">Olympic gold medals contain only about 1.34 percent gold, with the rest composed of sterling silver.</p>
					// echo '<label for="show-menu" class="menu-button"><span></span></label>';
				echo '</div>';
				echo '<input type="checkbox" id="show-menu" />';
				echo '<ul class="menu-content">';
					echo '<li>';
						echo '<a href="#" class="fa fa-bookmark-o"></a>';
					echo '</li>';
					echo '<li><a href="#" class="fa fa-heart-o"><span>47</span></a></li>';
					echo '<li><a href="#" class="fa fa-comment-o"><span>8</span></a></li>';
				echo '</ul>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
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