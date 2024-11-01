<?php

if(!class_exists('WP_List_Table')){
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

if(!class_exists('Wxp_Passwords_Table')){
    class Wxp_Passwords_Table extends WP_List_Table {

        private $pass_data = array();
        function __construct(){
            global $status, $page;
            parent::__construct( array(
                'singular'  => __('wxp-password','wp-passwords'),     //singular name of the listed records
                'plural'    => __('wxp-passwords','wp-passwords'),   //plural name of the listed records
                'ajax'      => true        //does this table support ajax?
            ) );
            $this->init();
        }

        function init(){
            $this->prepare_items();
            $this->display();
        }

        function no_items() {
            _e('No password found.' );
        }

        function column_default($item,$column_name){

            switch($column_name){
                case 'cb':
                case 'id':
	            echo $item['id'];
	            break;
                case 'password_text':
                    echo $item['password_text'];
                    break;
                case 'current_time':
	                echo date('Y-m-d H:i:s',$item['current_time']);
	                break;
                case 'status':
	                $this->show_status($item['password_time']);
                    break;
	            case 'action':
                     echo '<div class="wxp-pass-actions">';
                     echo '<span class="wxp-icon icon-wxp-edit" title="'.__('Edit','wp-passwords').'" data-id="'.$item['id'].'"></span>';
		             echo '<span class="wxp-icon icon-wxp-delete" title="'.__('Delete','wp-passwords').'"  data-id="'.$item['id'].'"></span>';
                     echo '</div>';
	                break;
            }
        }

        function show_status($time){
            if($time==0){
                echo '<span class="wxp-valid-pass">'.__('Valid','wp-passwords').'</span>';
            }
            elseif(is_numeric($time) && $time){
                if(current_time('timestamp',0)>$time){
                    echo '<span class="wxp-expired-pass">'.__('Expired','wp-passwords').'</span>';
                }
	            elseif(current_time('timestamp',0)<$time){
		            $now = new DateTime(date('Y-m-d H:i:s',current_time('timestamp',0)));
		            $future_date = new DateTime(date('Y-m-d H:i:s',$time));
		            $interval = $future_date->diff($now);
		            if($interval->h){
			            echo '<span class="wxp-valid-pass">Expiring in '.$interval->format("%h hours, %i minutes, %s seconds").'</span>';
                    }
                    elseif($interval->i){
	                    echo '<span class="wxp-valid-pass">Expiring in '.$interval->format("%i minutes, %s seconds").'</span>';
                    }
                    elseif($interval->s){
			            echo '<span class="wxp-valid-pass">Expiring in '.$interval->format("%s seconds").'</span>';
		            }
		            else
		            {
			            echo '<span class="wxp-expired-pass">'.__('Expired','wp-passwords').'</span>';
                    }
	            }
            }
        }

        function get_sortable_columns() {
            $sortable_columns = array(
                'id'  => array('id',false),
                'password_text' => array('password_text',true),
                'current_time'   => array('current_time',true),
                'status'   => array('password_time',true),
            );
            return $sortable_columns;
        }

        function get_columns(){
            $columns = array(
                    'cb'=>'<input type="checkbox" />',
                    'id'=>__('#', 'wp-passwords'),
                    'password_text'=> __( 'Password','wp-passwords'),
                    'current_time'=>__('Date','wp-passwords'),
                    'status'=> __('Status','wp-passwords'),
                    'action'=>__('Action','wp-passwords')
            );
            return $columns;
        }

        function usort_reorder( $a, $b ) {
            // If no sort, default to title
            $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
            // If no order, default to asc
            $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
            // Determine sort order
            $result = strcmp( $a[$orderby], $b[$orderby] );
            // Send final sort direction to usort
            return ( $order === 'asc' ) ? $result : -$result;
        }

        function get_data(){
            global $wpdb;
            $wps_table = $wpdb->prefix.'wxp_passwords';
            $data = $wpdb->get_results("SELECT * FROM ".$wps_table." ORDER BY id ASC",ARRAY_A);
            return $data;
        }

        function get_bulk_actions() {
            $actions = array(
                'delete'    => 'Delete'
            );
            return $actions;
        }
        function column_cb($item) {
            return sprintf(
                '<input type="checkbox" name="wps-password[]" value="%s" />', $item['id']
            );
        }

        function prepare_items() {

            $this->process_delete_action();
            $this->pass_data = $this->get_data();
            $columns  = $this->get_columns();
            $hidden   = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array( $columns, $hidden, $sortable );
            usort( $this->pass_data, array( &$this, 'usort_reorder' ) );

            $per_page = 15;
            $current_page = $this->get_pagenum();
            $total_items = count( $this->pass_data );
            $this->set_pagination_args( array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
                'total_pages' => ceil( $total_items / $per_page ),
            ) );
            $data = array_slice($this->pass_data, ( ( $current_page - 1 ) * $per_page ), $per_page );
            $this->items = $data;
	        //echo '<pre>'; print_r($columns); echo '</pre>';
            //echo '<pre>'; print_r($this->items); echo '</pre>';
            //die;
        }

        function process_delete_action(){
            if('delete' === $this->current_action() && isset($_REQUEST['wps-password'])){
                global $wpdb;
                $wps_table = $wpdb->prefix.'wps_passwords';
                $wpdb->query("DELETE FROM $wps_table WHERE id=".$_REQUEST['wps-password']);
                $url = admin_url('admin.php?page=wp-passwords&wps-deleted=true');
                wp_redirect($url);
                exit;
            }
        }

        function display_tablenav( $which ) {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">
                <?php
                $this->extra_tablenav($which);
                $this->pagination( $which );
                ?>
                <br class="clear" />
            </div>
            <?php if($which=="bottom") {
                echo '<br class="clear" />';
            }
        }
        function extra_tablenav($which){
            //echo '<pre>'; print_r($blogusers); echo '</pre>';
            if($which=="top"){
	            echo '<br class="clear" />';
            }

            if($which=="bottom"){
                echo '<br class="clear" />';
            }
            $views = $this->get_views();
            if(empty($views)){
                return;
            }
            $this->views();
        }
    }
}
new Wxp_Passwords_Table();
?>