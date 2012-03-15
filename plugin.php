<?php

/*
Plugin Name: Simple ACF Time Field
Description: A really simple time field for the Advance Custom Fields API
Version: 0.0.1
Author: Seamus P. H. Leahy
Author URI: http://seamusleahy.com
License: MIT
*/
define('SIMPLE_ACF_TIME_FIELD_FILE', __FILE__);


function register_simple_acf_time_field($fields) {
  $fields[] = array('class' => 'Simple_ACF_Time_Field', 'url' => dirname(__FILE__).'/simple_acf_time_field.class.php');
  
  return $fields;
}


add_filter('acf_register_field', 'register_simple_acf_time_field');