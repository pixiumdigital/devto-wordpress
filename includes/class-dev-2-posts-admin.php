<?php
/**
 * Dev 2 Posts Admin Class
 *
 * @since 1.0
 * @package Dev 2 Posts Plugin
 * @author Pixium Digital Pte Ltd
 */
 
class Dev2Posts_Admin
{
    function __construct() {

        add_action( "admin_enqueue_scripts", array( $this, "load_assets" ));
        add_action ( "admin_menu", array( $this, "devto_add_menu" ));
        add_action ( "admin_init", array( $this, "dev_to_settings" ));
    }

    
    function load_assets()
    {
        wp_enqueue_script( 'dev-to-settings-js', PLUGIN_ROOT_URL."assets/js/dev-to-settings.js");
	    wp_enqueue_style( 'dev-to-settings-style', PLUGIN_ROOT_URL."assets/css/dev-to-settings.css");
    }

    /**
     * Adding Submenu under Settings Tab
     *
     * @since 1.0
     */
    function devto_add_menu() {
        add_submenu_page ( "options-general.php", "Dev 2 Posts Plugin", "Dev 2 Posts Settings", "manage_options", "dev-to", array( $this, "dev_to_page" ));
    }

    /**
     * Get articles with API call to Dev.TO
     *
     * @since 1.0
     */
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

 
    /**
     * Setting Page Options
     * - add setting page
     * - save setting page
     *
     * @since 1.0
     */
    function dev_to_page() { ?>
        <div class="dev-to-wrap">
            <div class="dev-to-header">
                <h1>
                    Dev 2 Posts Plugin for <a href="https://dev.to" target="_blank">DEV.TO</a>
                </h1>
                <h3>
                    Developed By: <a href="https://pixiumdigital.com" target="_blank">Pixium Digital Pte Ltd</a>
                </h3>
            </div>
        
            <form method="post" action="options.php">
                <?php
                    settings_fields ( "dev_to_config" );
                    do_settings_sections ( "dev-to" );
                    submit_button ();
                ?>
            </form>

            <!-- Get the list of articles here -->
            <?php
                if(get_option ( 'dev-to-api-key' )){
                    $result = $this->get_articles();;
                    // Handle curl error
                    if ($result) {
                        echo "<div class='detect-notice'>
                        <h4 class='detect-msg'>Number of articles detected: ".count($result)."</h4>
                        </div>";
                    } else {
                        echo "ERROR";
                    }
                }

            ?>

            <div class="shortcode-instruction">
                <h4>Using the shortcodes:</h4>
                <p>You can use the shortcode <a>[dev_to]</a> to display all the articles or the shortcode <a>[dev_to_last]</a> which will display the last 3 articles by default.</p>
                <p>You may set a limit for the <a>[dev_to_last]</a> shortcode as follows, <a>[dev_to_last limit="5"]</a>. The number of articles displayed will follow the limit value set.</p>
            </div>
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

        add_settings_field ( "dev-to-api-key", "Enter your dev.to API KEY:", array( $this, "dev_to_key" ), "dev-to", "dev_to_config" );
        register_setting ( "dev_to_config", "dev-to-api-key" );

        add_settings_field ( "dev-to-type", "Select the type of articles you want to retrieve:", array( $this, "dev_to_type" ), "dev-to", "dev_to_config" );
        register_setting ( "dev_to_config", "dev-to-type" );

        add_settings_field ( "dev-to-username", "Enter the organization name to get the articles from:", array( $this, "dev_to_username" ), "dev-to", "dev_to_config", $args = array("class" => "dev-to-username") );
        register_setting ( "dev_to_config", "dev-to-username" );
    }

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
     * Add a field for the type (organization vs user)
     *
     * @since 1.0
     */
    function dev_to_type() {
        ?>
        <div class="postbox" style="width: 65%; padding: 30px;">
            <select name="dev-to-type" id="devto-type">
                <?php 
                $dev_to_type = get_option ( 'dev-to-type' ); 
                if($dev_to_type == "me"):?>
                    <option value="organization">Organization</option>
                    <option value="me" selected="selected">Me</option>	
                <?php else: ?>
                    <option value="organization" selected="selected">Organization</option>
                    <!-- <option value="user">User</option> -->
                    <option value="me">Me</option>
                <?php endif;?>
            </select>
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
} 
new Dev2Posts_Admin();