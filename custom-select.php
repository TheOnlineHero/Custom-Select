<?php
/*
Plugin Name: Custom Select
Plugin URI: http://wordpress.org/extend/plugins/custom-select
Description: Custom Select is a plugin that allows you to make ugly, boring, standard html select boxes stand out.

Installation:

1) Install WordPress 3.5.2 or higher

2) Download the following file:

http://downloads.wordpress.org/plugin/custom-select.zip

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.

Version: 1.0
Author: TheOnlineHero - Tom Skroza
License: GPL2
*/

require_once("custom-select-path.php");

function custom_select_activate() {
  if (!is_dir(get_template_directory()."/custom_select_css")) {
    custom_select_copy_directory(CustomSelectPath::normalize(dirname(__FILE__)."/css"), get_template_directory());  
  } else {
    add_option("custom_select_current_css_file", "style.css");
  }
}
register_activation_hook( __FILE__, 'custom_select_activate' );

add_action( 'admin_init', 'register_custom_select_settings' );
function register_custom_select_settings() {
  register_setting( 'custom-select-settings-group', 'custom_select_selector' );

  @check_custom_select_dependencies_are_active(
    "Custom Select", 
    array(
      "Tom M8te" => array("plugin"=>"tom-m8te/tom-m8te.php", "url" => "http://downloads.wordpress.org/plugin/tom-m8te.zip", "version" => "1.4.2"))
  );
}

add_action('admin_menu', 'register_custom_select_page');
function register_custom_select_page() {
  add_menu_page('Custom Select', 'Custom Select', 'update_themes', 'custom-select/custom-select.php', 'custom_select_initial_page');
}

function custom_select_initial_page() {
  
  	wp_enqueue_script('jquery');
    wp_register_style("custom-select", plugins_url("/admin_css/style.css", __FILE__));
    wp_enqueue_style("custom-select");

    if (isset($_POST["custom_selector"])) {
      update_option("custom_select_selector", $_POST["custom_selector"]);
    }
    
  	$css_content = file_get_contents(get_template_directory()."/custom_select_css/style.css");
  	if (isset($_POST["css_content"])) {
      $location = get_template_directory()."/custom_select_css/style.css";
      $css_content = $_POST["css_content"];
      tom_write_to_file($_POST["css_content"], $location);
  	}
  ?>
  <div class="wrap a-form">
  <h2>Custom Select - Selector</h2>
  <div class="postbox " style="display: block; ">
  <div class="inside">
    <form action="" method="post">
    	<p><label for="custom_selector">CSS Selector:</label><input id="custom_selector" name="custom_selector" value="<?php echo(get_option("custom_select_selector")); ?>" /> Example: select, #select, .select</p>
    	<p><input type="submit" value="Update"/></p>
    </form>
  </div>
  </div>
  
  <h2>Custom Select - Styling</h2>
  <div class="postbox " style="display: block; ">
  <div class="inside">
    <form action="" method="post">
    	<p><label for="css_content">CSS:</label><textarea id="css_content" name="css_content"><?php echo($css_content); ?></textarea></p>
    	<p><input type="submit" value="Update"/></p>
    </form>
  </div>
  </div>
  </div>

  <?php
}

add_action('wp_head', 'add_custom_select_js_and_css');
function add_custom_select_js_and_css() {
  wp_enqueue_script("jquery");
  wp_register_script("custom-select", plugins_url("/js/jquery.customSelect.min.js", __FILE__));
  wp_enqueue_script("custom-select");
  
  wp_register_script("custom-select-app", plugins_url("/js/application.js", __FILE__));
  wp_enqueue_script("custom-select-app");
  
  wp_localize_script( 'custom-select', 'CustomSelectAjax', array(
    "custom_select_selector" => get_option("custom_select_selector")
  ));
  wp_enqueue_script("custom-select");
  
  wp_register_style("custom-select", get_template_directory_uri().'/custom_select_css/style.css');
  wp_enqueue_style("custom-select");
  
}

function check_custom_select_dependencies_are_active($plugin_name, $dependencies) {
  $msg_content = "<div class='updated'><p>Sorry for the confusion but you must install and activate ";
  $plugins_array = array();
  $upgrades_array = array();
  define('PLUGINPATH', ABSPATH.'wp-content/plugins');
  foreach ($dependencies as $key => $value) {
    $plugin = get_plugin_data(PLUGINPATH."/".$value["plugin"],true,true);
    $url = $value["url"];
    if (!is_plugin_active($value["plugin"])) {
      array_push($plugins_array, $key);
    } else {
      if (isset($value["version"]) && str_replace(".", "", $plugin["Version"]) < str_replace(".", "", $value["version"])) {
        array_push($upgrades_array, $key);
      }
    }
  }
  $msg_content .= implode(", ", $plugins_array) . " before you can use $plugin_name. Please go to Plugins/Add New and search/install the following plugin(s): ";
  $download_plugins_array = array();
  foreach ($dependencies as $key => $value) {
    if (!is_plugin_active($value["plugin"])) {
      $url = $value["url"];
      array_push($download_plugins_array, $key);
    }
  }
  $msg_content .= implode(", ", $download_plugins_array)."</p></div>";
  if (count($plugins_array) > 0) {
    deactivate_plugins( __FILE__, true);
    echo($msg_content);
  } 

  if (count($upgrades_array) > 0) {
    deactivate_plugins( __FILE__,true);
    echo "<div class='updated'><p>$plugin_name requires the following plugins to be updated: ".implode(", ", $upgrades_array).".</p></div>";
  }
}


// Copy directory to another location.
function custom_select_copy_directory($src,$dst) { 
    $dir = opendir($src); 
    try{
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    custom_select_copy_directory($src . '/' . $file,$dst . '/' . $file); 
                } else { 
                    copy($src . '/' . $file,$dst . '/' . $file);
                } 
            }   
        }
        closedir($dir); 
    } catch(Exception $ex) {
        return false;
    }
    return true;
}

?>