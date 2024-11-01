<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if(!class_exists('Wxp_Password_MySQL')){
	class Wxp_Password_MySQL{

		function __construct(){
			$this->create_mysql();
			$this->create_setup();
		}

		function create_setup(){
			$login_page_id = get_option('_wxp_password_login_page');
			if($login_page_id== '' || !wxp_pass()->is_page_exists($login_page_id)){
				$page = array(
					'post_title'    => 'Login',
					'post_content'  => '[wxp_passwords title="Please login with your password to access site."]',
					'post_status'   => 'publish',
					'post_type'     => 'page',
					'post_name'     => 'take-over',
					'post_author'   => get_current_user_id(),
				);
				$post_id = wp_insert_post($page);
				update_option('_wxp_password_login_page',$post_id);
			}
			$exclude_home = get_option('_wxp_exclude_home_page');
			if($exclude_home==''){
				update_option('_wxp_exclude_home_page','yes');
			}
		}

		function create_mysql(){
			global $wpdb;
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$sql1 = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wxp_passwords(
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `user_id` bigint(20) NOT NULL,
			  `password_text` varchar(100) DEFAULT '' NOT NULL,
			  `password` varchar(100) DEFAULT '' NOT NULL,
			  `password_time` varchar(20) DEFAULT '' NOT NULL,
			  `current_time` varchar(20) DEFAULT '' NOT NULL,
			  `rules` text NOT NULL,
			   PRIMARY KEY (`id`)
			   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
			dbDelta($sql1);
		}
	}
}
new Wxp_Password_MySQL;
?>