<?php
/*
Plugin Name: Wxp Passwords
Plugin URI: https://wooexperts.com
Description: Allow limited access for users with passwords.
Author: Vikram Singh
Version: 1.0
Author URI: http://vsjodha.com
*/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Wxp_Passwords{

    private $pass_pre = 'wxp-hash-';
	private $wxp_login_page;
	private $wxp_home_page;
	private $wxp_exclude_home = true;
	private $wxp_valid = false;

	protected static $_instance = null;

	public static function instance(){
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    function __construct(){
	    register_activation_hook(__FILE__, array($this,'create_wxp_mysql'));
	    add_action('init',array($this,'wxp_init_settings'));
        add_action('admin_menu',array($this,'wxp_passwords_menu_items'));
        add_action('admin_enqueue_scripts',array(&$this,'admin_header'));
	    add_action('wp_enqueue_scripts',array($this,'wxp_front_scripts'));
	    add_action('wp_ajax_wxp_load_add_password',array($this,'wxp_load_add_password'));
	    add_action('wp_ajax_wxp_load_edit_password',array($this,'wxp_load_edit_password'));
	    add_action('wp_ajax_wxp_passwords_store',array($this,'wxp_passwords_store'));
	    add_shortcode('wxp_passwords',array($this,'wxp_passwords_shortcode'));
	    add_action('wp_ajax_wxp_pass_check',array($this,'check_submit'));
	    add_action('wp_ajax_nopriv_wxp_pass_check',array($this,'check_submit'));
	    add_action('wp_ajax_wxp_load_settings',array($this,'wxp_load_settings'));
	    add_action('template_redirect',array($this,'check_wps_login'));
	    add_action('admin_notices',array($this,'wxp_passwords_page_notice'));
    }

    function wxp_init_settings(){
	    $login_page_id = get_option('_wxp_password_login_page');
	    $exclude_home = get_option('_wxp_exclude_home_page');
	    $this->wxp_login_page = $this->is_page_exists($login_page_id) ? $login_page_id : '';
	    $this->wxp_exclude_home = isset($exclude_home) && $exclude_home=='no' ? false : true;
	    $this->wxp_home_page = get_option('page_on_front')!=0 && get_option('page_on_front')!='' ? get_option('page_on_front') : '';
    }

    function wxp_passwords_page_notice(){
	    $screen = get_current_screen();
	    if(isset($screen->id) && $screen->id=='toplevel_page_wxp-passwords' && $this->wxp_login_page==''){
		    echo '<div class="notice notice-error is-dismissible">';
		    echo '<p>'.__('Please set Login page in password settings.','wxp-pro-manager').'</p>';
		    echo '</div>';
        }
    }

    function is_page_exists($page_id){
	    $page = get_post($page_id);
	    if(isset($page->ID) && isset($page->post_status) && $page->post_status=='publish'){
	        return true;
        }
        else
        {
	        return false;
        }
    }

    function create_wxp_mysql(){
	    include(dirname(__FILE__)."/includes/wxp-password-mysql.php");
    }

	function wxp_passwords_menu_items(){
		add_menu_page(__('Passwords','wxp-passwords'),__('Passwords','wxp-passwords'),'manage_options','wxp-passwords',array($this,'render_wxp_passwords'));
	}

	function admin_header() {

		wp_enqueue_style('fancybox',plugins_url('assets/css/jquery.fancybox.min.css',__FILE__));
		wp_enqueue_style('wxp_password_css',plugins_url('assets/css/wxp-passwords.css',__FILE__));
		wp_enqueue_script('fancybox',plugins_url('assets/js/jquery.fancybox.min.js',__FILE__),array('jquery'),NULL,false);
		wp_enqueue_script('blockUI',plugins_url('assets/js/jquery.blockUI.js',__FILE__),array('jquery'),NULL);

		wp_register_script('wxp_password_js',plugins_url('assets/js/wxp-passwords.js',__FILE__),array('jquery'),NULL,true);
		$translation_array = array(
			'ajax_url' => admin_url('admin-ajax.php')
		);
		wp_localize_script('wxp_password_js','wxp_pass',$translation_array);
		wp_enqueue_script('wxp_password_js');
	}

	function wxp_front_scripts(){
		wp_enqueue_style('wxp_password_front',plugins_url('assets/css/wxp-front.css',__FILE__));
    }

	function render_wxp_passwords(){
		global $title;
		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">'.$title.'</h1>';
		echo '<a href="javascript:void(0);" class="page-title-action wxp-add-password">'.__('Add Password','wxp-passwords').'</a>';
		echo '<a href="javascript:void(0);" class="page-title-action wxp-password-settings">'.__('Settings','wxp-passwords').'</a>';
		include(dirname(__FILE__)."/includes/list-table.php");
		echo '</div>';
	}

	function wxp_load_add_password(){
		include(dirname(__FILE__)."/includes/add-password.php");
    }

    function wxp_load_settings(){
	    include(dirname(__FILE__)."/includes/settings.php");
    }

	function wxp_load_edit_password(){
	    global $wpdb;
	    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		$row = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'wxp_passwords WHERE id='.$id);
		$rules = isset($row->rules) ? maybe_unserialize($row->rules) : array();
		if(isset($row->password_time) && $row->password_time>0){
			$now = new DateTime(date('Y-m-d H:i:s',current_time('timestamp',0)));
			$future_date = new DateTime(date('Y-m-d H:i:s',$row->password_time));
			$interval = $future_date->diff($now);
			$hour = $interval->h ? $interval->h : 0;
			$minute = $interval->i ? $interval->i : 0;
			$second = $interval->s ? $interval->s : 0;
        }
        else
        {
	        $hour = 0;
	        $minute = 0;
	        $second = 0;
        }
		include(dirname(__FILE__)."/includes/edit-password.php");
	}

    function get_pass_hash($pass){
	    return md5($this->pass_pre.''.$pass);
    }

    function get_pass_time($hour=0,$minute=0,$second=0){
        $curr_time = current_time('timestamp',0);
        $is_time = false;

        if(is_numeric($hour) && $hour){
	        $is_time = true;
	        $curr_time = strtotime('+'.$hour.' hour',$curr_time);
        }
	    if(is_numeric($minute) && $minute){
		    $is_time = true;
		    $curr_time = strtotime('+'.$minute.' minutes',$curr_time);
	    }
	    if(is_numeric($second) && $second){
		    $is_time = true;
		    $curr_time = strtotime('+'.$second.' seconds',$curr_time);
	    }
        return $is_time ? $curr_time : 0;
    }

	function wxp_pass_add_record($table,$data){
		$types = array();
		if(is_array($data) && !empty($data)){
			$types = array();
			foreach($data as $key=>$val){
				$types[] = is_int($val) ? '%d' : '%s';
			}
		}
		global $wpdb;
		$wpdb->insert(
			$table,
			$data,
			$types
		);
		return $wpdb->insert_id;
	}

    function wxp_pass_update_record($table,$data,$where){
	    global $wpdb;
	    $types = array();
	    if(is_array($data) && !empty($data)){
		    $types = array();
		    foreach($data as $key=>$val){
			    $types[] = is_int($val) ? '%d' : '%s';
		    }
	    }
	    $row = $wpdb->update(
		    $table,
		    $data,
		    array('id'=>$where),
		    $types,
		    array('%d')
	    );
	    return $row;
    }

	function wxp_passwords_store(){
		global $wpdb;
		$res = array('msg'=>'','class'=>'','reload'=>false);
		if(isset($_POST['data']['case'])){
			switch($_POST['data']['case']){
				case 'save-wxp-password':
					parse_str($_POST['data']['wxp-data'],$passwords);
					$rules = array('allow'=>isset($passwords['wxp-allow-types']) ? $passwords['wxp-allow-types'] : array(),'block'=>isset($passwords['wxp-block-types']) ? $passwords['wxp-block-types'] : array());
					$data = array(
						'password_text' => trim($passwords['wxp-pass-password']),
						'password'   => $this->get_pass_hash(trim($passwords['wxp-pass-password'])),
						'password_time' => $this->get_pass_time($passwords['wxp-pass-hour'],$passwords['wxp-pass-minute'],$passwords['wxp-pass-second']),
						'current_time' => current_time('timestamp',0),
                        'rules' => maybe_serialize($rules)
					);
					$wxp_table = $wpdb->prefix.'wxp_passwords';
					$pass_hash = $this->get_pass_hash($passwords['wxp-pass-password']);
					$is_pass_exist = $wpdb->get_var('SELECT COUNT(*) FROM '.$wxp_table.' WHERE password_text="'.$passwords['wxp-pass-password'].'" AND password="'.$pass_hash.'"');
                    if(!$is_pass_exist){
	                    $id = $this->wxp_pass_add_record($wpdb->prefix.'wxp_passwords',$data);
                    }
					else
					{
						$res = array(
							'msg'=>__('Password already exists!','wxp-passwords'),
							'class'=>'wxp-pass-err',
							'reload'=>false
						);
						echo json_encode($res);
						die;
                    }
					if($id){
						$res = array(
							'msg'=>__('Password saved successfully.','wxp-passwords'),
							'class'=>'wxp-pass-success',
							'reload'=>true
						);
                    }
                    else
                    {
	                    $res = array(
		                    'msg'=>__('Please try again,something went wrong!','wxp-passwords'),
		                    'class'=>'wxp-pass-err',
		                    'reload'=>false
	                    );
                    }
					break;
				case 'delete-wxp-password':
				    $id = isset($_POST['data']['wxp-pass-id']) ? $_POST['data']['wxp-pass-id'] : 0;
					$wpdb->delete($wpdb->prefix.'wxp_passwords',array('id'=>$id),array('%d'));
					$res = array('msg'=>'','class'=>'','reload'=>true);
					break;
				case 'update-wxp-password':
					parse_str($_POST['data']['wxp-data'],$passwords);
					$rules = array('allow'=>isset($passwords['wxp-allow-types']) ? $passwords['wxp-allow-types'] : array(),'block'=>isset($passwords['wxp-block-types']) ? $passwords['wxp-block-types'] : array());
					$data = array(
						'password_text' => trim($passwords['wxp-pass-password']),
						'password'   => $this->get_pass_hash(trim($passwords['wxp-pass-password'])),
						'password_time' => $this->get_pass_time($passwords['wxp-pass-hour'],$passwords['wxp-pass-minute'],$passwords['wxp-pass-second']),
						'current_time' => current_time('timestamp',0),
						'rules' => maybe_serialize($rules)
					);

					$wxp_table = $wpdb->prefix.'wxp_passwords';
					$is_pass_exist = $wpdb->get_var('SELECT COUNT(*) FROM '.$wxp_table.' WHERE password_text="'.$passwords['wxp-pass-password'].'" AND id!='.$passwords['password_id']);
                    if(!$is_pass_exist){
	                    $row = $this->wxp_pass_update_record($wpdb->prefix.'wxp_passwords',$data,$passwords['password_id']);
                    }
                    else
                    {
	                    $res = array(
		                    'msg'=>__('Password already exists!','wxp-passwords'),
		                    'class'=>'wxp-pass-err',
		                    'reload'=>false
	                    );
	                    echo json_encode($res);
	                    die;
                    }
					if($row){
						$res = array(
							'msg'=>__('Password saved successfully.','wxp-passwords'),
							'class'=>'wxp-pass-success',
							'reload'=>true
						);
					}
					else
					{
						$res = array(
							'msg'=>__('Please try again,something went wrong!','wxp-passwords'),
							'class'=>'wxp-pass-err',
							'reload'=>false
						);
					}
					break;
				case 'save-wxp-settings':
					parse_str($_POST['data']['wxp-data'],$passwords);
					$exclude_home = isset($passwords['wxp-pass-exclude-home']) && $passwords['wxp-pass-exclude-home']=='on' ? 'yes' : 'no';
					update_option('_wxp_password_login_page',$passwords['page_id']);
					update_option('_wxp_exclude_home_page',$exclude_home);
					$res = array(
						'msg'=>__('Settings saved successfully.','wxp-passwords'),
						'class'=>'wxp-pass-success',
						'reload'=>true
					);
					break;
			}
		}
		echo json_encode($res);
		die;
	}

    function wxp_passwords_shortcode($atts){
        $this->check_wps_login();
        $html='';
        ob_start();
	    include(dirname(__FILE__)."/includes/login.php");
        $html= ob_get_contents();
        ob_end_clean();
        return $html;
    }

    function check_submit(){
        if(isset($_POST['wxp_security'])){
            ob_start();
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            if(wp_verify_nonce($_POST['wxp_security'],'wxp_ajax_login')){
                $validated = $this->validate_pass($_POST['password']);
                if($validated){
                    wp_redirect(get_permalink($this->wxp_login_page));
                    exit();
                }
                else
                {
                    wp_redirect(add_query_arg('wxp-err','true',get_permalink($this->wxp_login_page)));
                    exit();
                }
            }
        }
    }

    function validate_pass($pass){
	    global $wpdb;
	    $valid = false;
	    $wxp_table = $wpdb->prefix.'wxp_passwords';
	    $pass_hash = $this->get_pass_hash($_POST['password']);
	    $is_pass_exist = $wpdb->get_row('SELECT * FROM '.$wxp_table.' WHERE password_text="'.$_POST['password'].'" AND password="'.$pass_hash.'"');
	    if(isset($is_pass_exist->password_time) && is_numeric($is_pass_exist->password_time) && $is_pass_exist->password_time>0){
	        if($is_pass_exist->password_time>current_time('timestamp',0)){
	            $time_remaining = $is_pass_exist->password_time-current_time('timestamp',0);
	            if($time_remaining){
		            setcookie('_wxp_check',$is_pass_exist->id,current_time('timestamp',0)+$time_remaining, "/");
		            $valid = true;
                }
            }
        }
        elseif(isset($is_pass_exist->id) && $is_pass_exist->password_time==0){
	        setcookie('_wxp_check',$is_pass_exist->id, time() + (60*60*24*30*12), "/");
	        $valid = true;
        }
        return $valid;
    }

    function is_valid_pass($id){
	    global $wpdb;
	    $pass = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'wxp_passwords WHERE id='.$id);
	    if(!isset($pass->id)){
		    setcookie('_wxp_check',0,time()-3600,'/');
        }
        else
        {
	        $this->wxp_valid = true;
        }
    }

	function get_pass_rules($id){
		global $wpdb;
		$pass = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'wxp_passwords WHERE id='.$id);
		$rules = isset($pass->rules) ? maybe_unserialize($pass->rules) : array('allow'=>array(),'block'=>array());
		return $rules;
	}

    function check_wps_login(){
	    ob_start();
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        if(isset($this->wxp_login_page) && $this->wxp_login_page){
	        global $wp_query;
	        $page = isset($wp_query->query['pagename']) ? get_page_by_path($wp_query->query['pagename']) : 0;
	        $page_id = isset($page->ID) ? $page->ID : 0;
	        $page_id = is_front_page() ? $this->wxp_home_page : $page_id;
	        $page_id = is_home() ? 'home' : $page_id;
	        $rules = isset($_COOKIE['_wxp_check']) ? $this->get_pass_rules($_COOKIE['_wxp_check']) : array('allow'=>array(),'block'=>array());

	        if(!isset($_COOKIE['_wxp_check']) && !is_page($this->wxp_login_page)){
		        wp_redirect(get_permalink($this->wxp_login_page));
		        exit();
	        }
            elseif(isset($_COOKIE['_wxp_check']) && in_array($page_id,$rules['block']) && !is_page($this->wxp_login_page)){
	            if(!is_front_page()){
		            wp_redirect(get_permalink($this->wxp_login_page));
		            exit();
                }
                elseif((is_front_page() && !$this->wxp_exclude_home)){
	                wp_redirect(get_permalink($this->wxp_login_page));
	                exit();
                }
	        }
	        elseif(is_home() && !$this->wxp_exclude_home){
		        wp_redirect(get_permalink($this->wxp_login_page));
		        exit();
            }
            elseif(isset($_COOKIE['_wxp_check']) && in_array($page_id,$rules['allow'])){
		        $this->is_valid_pass($_COOKIE['_wxp_check']);
	        }
	        elseif(isset($_COOKIE['_wxp_check'])){
		        $this->is_valid_pass($_COOKIE['_wxp_check']);
            }
        }
    }

}

function wxp_pass() {
	return Wxp_Passwords::instance();
}
wxp_pass();
?>