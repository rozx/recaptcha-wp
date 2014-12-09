<?php
/*
Plugin Name: reCAPTCHA
Description: A wordpress Plugin enable google reCAPTCHA in your wordpress Site.
Plugin URI: http://www.heavyskymobile.com
Version: 0.2.3
Author: rozx
Author URI: http://www.heavyskymobile.com
License: GPLv2 
*/

add_action('admin_init', 'wp_recaptcha_admin_init');

function wp_recaptcha_admin_init(){
 	add_settings_section('wp_recaptcha_setting_section', 'reCAPTCHA', 'wp_recaptcha_callback', 'discussion');
	add_settings_field('wp_recaptcha',  'reCAPTCHA key settings', 'wp_recaptcha_input', 'discussion', 'wp_recaptcha_setting_section' );
	register_setting( 'discussion', 'wp_recaptcha_comment' );
	register_setting( 'discussion', 'wp_recaptcha_register' );
	register_setting( 'discussion', 'p_site_key' );
	register_setting( 'discussion', 'p_secret_key' );
}

function wp_recaptcha_callback() {
 	echo '<p>Option for the reCAPTCHA plugin</p>';
}

function wp_recaptcha_input() {
 	//echo '<input name="wp_recaptcha_register" type="checkbox" value="1" ' . checked( 1, get_option( 'wp_recaptcha_register' ), false ) . ' /> Use reCAPTCHA when new user registering. </em><br><br>';
 	echo '<input name="wp_recaptcha_comment" type="checkbox" value="1" ' . checked( 1, get_option( 'wp_recaptcha_comment' ), false ) . ' /> Use reCAPTCHA only for guest. </em><br><br>';
 	echo '<em>Site key:  </em><input name="p_site_key" type="text" value=" '. get_option( 'p_site_key' ) . '" size = "50" /> <br><br>';
 	echo '<em>Secret key:  </em><input name="p_secret_key" type="text" value=" ' .get_option( 'p_secret_key' ) .'" size = "50"/><br><br>';
 	
 	echo '<em>get your reCAPTCHA keys here:  <a href="https://www.google.com/recaptcha/intro/index.html">reCAPTCHA</a></em>';
 	
}

add_action('init','wp_recaptcha_init');

function wp_recaptcha_init(){
	
	 add_action('wp_head', 'wp_recaptcha_head');
	
	
	// use reCAPTCHA when posting comments
    if(get_option( 'wp_recaptcha_comment' )){
	
    	if (!is_user_logged_in()) {
	// config the comment form
        	
             add_filter('comment_form_field_comment','wp_recaptcha_config');
             
    	    // process the comment
    	    
    	    add_filter('preprocess_comment','wp_recaptcha_process');
    	}
        
    } else {
	      add_filter('comment_form_field_comment','wp_recaptcha_config');
             
    	    // process the comment
    	    
    	    add_filter('preprocess_comment','wp_recaptcha_process');
}
    
    /*
    
    if( get_option( 'wp_recaptcha_register' )){
        add_action( 'register_form', 'wp_recaptcha_register_form' );
       
    }
    */
	
}	

function wp_recaptcha_register_form(){
    echo '<div class="g-recaptcha" data-sitekey="'.get_option( 'p_site_key' ).'"></div>';
}

function wp_recaptcha_head(){
    echo '<script src="https://www.google.com/recaptcha/api.js"></script>';
}


function wp_recaptcha_process($commentdata) {
	
	if ($commentdata['comment_type'] != '') return $commentdata;
		
	global $post;

    $result = wp_recaptcha_getresult();
    
    if($result->{'success'} == 1){
        
        //$commentdata['comment_approved'] = '1';
        return  $commentdata;
        
    } else {
        
        $commentdata['comment_approved'] = 'spam';
		wp_insert_comment($commentdata);
        
        wp_die('Error! Action failed!');
    }
    
	
	
}

function wp_recaptcha_config($field){
	
	$field = str_replace('</textarea>','</textarea><div class="g-recaptcha" data-sitekey="'. get_option( 'p_site_key' ) . '"></div>',$field);
	
	return $field;
}


function wp_recaptcha_getresult(){
    
    // get result from google
    
    $url = 'https://www.google.com/recaptcha/api/siteverify?response='.$_POST['g-recaptcha-response'].'&secret='.get_option( 'p_secret_key' ).'&remoteip='. $commentdata['comment_author_IP'];
	$xml = file_get_contents($url);
    $result = json_decode($xml);
    
    return $result;
}