<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Clone Order Functionality.
 *
 * @class    CloneOrder
 * @version  1.0.1
 * @category Class
 * @author   Jamie Gill
 */

class CloneBulk extends CloneOrder {
	
	public $original_order_id;
	public $cron_count = 0;

	function __construct() {
		
    	add_action('admin_footer-edit.php', array($this, 'custom_bulk_select'));
    	add_action('load-edit.php', array($this, 'custom_bulk_action'));
    	add_filter( 'manage_edit-shop_order_columns',  array($this, 'custom_shop_order_column'), 20 );
		add_action( 'manage_shop_order_posts_custom_column' , array($this,'custom_orders_list_column_content'), 20, 2 );
		add_filter( 'cron_schedules', array($this,'myprefix_custom_cron_schedule')   );
		add_action( 'repeat_order_cron_hook', array($this,'custom_bulk_action_cron') );
    }



/* function svd_deactivate() {
    wp_clear_scheduled_hook( 'svd_cron' );
}
 
add_action('init', function() {
    add_action( 'svd_cron', 'svd_run_cron' );
    register_deactivation_hook( __FILE__, 'svd_deactivate' );
 
    if (! wp_next_scheduled ( 'svd_cron' )) {
        wp_schedule_event( time(), 'daily', 'svd_cron' );
    }
}); */
 
function svd_run_cron() {
    // do your stuff.
}
function myprefix_custom_cron_schedule( $schedules ) {
    $schedules['every_fifteen_minutes'] = array(
        'interval' => 900, // Every 15 Minutes
        'display'  => __( 'Every 15 Minutes' ),
    );
    return $schedules;
}

//Schedule an action if it's not already scheduled

//create your function, that runs on cron
/* function myprefix_cron_function() {
    //your function...
} */

///Hook into that action that'll fire every six hours

//create your function, that runs on cron
/* function myprefix_cron_function() {
	$orderid =7770;
	$this->clone_order($orderid);
    //your function...
} */
	
    // ADDING 2 NEW COLUMNS WITH THEIR TITLES (keeping "Total" and "Actions" columns at the end)
function custom_shop_order_column($columns)
{
    $reordered_columns = array();

    // Inserting columns to a specific location
    foreach( $columns as $key => $column){
        $reordered_columns[$key] = $column;
        if( $key ==  'order_status' ){
            // Inserting after "Status" column
            $reordered_columns['repeat_num'] = __( 'Repeat Order Number','theme_domain');
        }
    }
    return $reordered_columns;
}

// Adding custom fields meta data for each new column (example)
function custom_orders_list_column_content( $column, $post_id )
{
    switch ( $column )
    {
        case 'repeat_num' :
            // Get custom post meta data
            $my_var_one = get_post_meta( $post_id, '_the_meta_key1', true );
            if(!empty($my_var_one))
                echo $my_var_one;

            // Testing (to be removed) - Empty value case
            else
                print_r('<input name="repeat_order_'. $post_id.'" value="0" type="text" id="repeat-'. $post_id.'">');

            break;


    }
}
    public function custom_bulk_select(){
 
		global $post_type;
		 
		if($post_type == 'shop_order') {
		?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('duplicate').text('<?php _e('Duplicate')?>').appendTo("select[name='action']");

					
					jQuery('.repeat_num').contents().unwrap().wrap('<th class="repeat_num column-repeat_num">')

					//t.prepend($('<thead></thead>').append(firstTr))
				});
			</script>
			
			<style>
			th.repeat_num.column-repeat_num input {
				padding: 0 5px;
			}
			</style>
		<?php
		}
	}
	
	function custom_bulk_action_cron() {
	
		// Thanks to J Lo for the tutorial on bulk actions 
		// https://blog.starbyte.co.uk/woocommerce-new-bulk-action/
		//wp_clear_scheduled_hook( 'myprefix_cron_hook' );
		
		$cron_count = get_option( 'cron_count' );
		$orderid = get_option( 'orderid' );
		$repeat_order = get_option( 'repeat_order' );		
		wp_mail( 'hassaniqbal.dev@gmail.com', 'The subject '.$cron_count.'  $orderid'. $orderid. 'repeat_order'. $repeat_order , 'The message' );
		if($repeat_order == $cron_count){
			wp_clear_scheduled_hook( 'repeat_order_cron_hook' );
		}
		$cron_count ++;
		update_option( 'cron_count',$cron_count);	
		$this->clone_order($orderid);
}
	
	public function custom_bulk_action() {
	
		// Thanks to J Lo for the tutorial on bulk actions 
		// https://blog.starbyte.co.uk/woocommerce-new-bulk-action/
		//session_start();
		//wp_clear_scheduled_hook( 'repeat_order_cron_hook' );
		global $typenow;
		$post_type = $typenow;

		if($post_type == 'shop_order') {
		
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
		

			$action = $wp_list_table->current_action();
			
			$allowed_actions = array("duplicate");
			
			if(!in_array($action, $allowed_actions)) return;

			if(isset($_REQUEST['post'])) {

				$orderids = array_map('intval', $_REQUEST['post']);
				

			}

			switch($action) {
				case "duplicate":
	
			foreach( $orderids as $orderid ) {
				$repeat_order = intval( $_REQUEST['repeat_order_'.$orderid] );	
				$cron_count = 1;				
				 update_option( 'cron_count', $cron_count );
				 update_option( 'repeat_order', $repeat_order );
				 update_option( 'orderid', $orderid );
				 
				 /* for($x=1; $x<=$repeat_order; $x++ ){
				 	$this->clone_order($orderid);
				 } */
				//update_post_meta( $order_id, 'repeat_order', $repeat_order);
			
				wp_mail( 'hassaniqbal.dev@gmail.com', 'The subject '.$cron_count.'  $orderid'. $orderid. 'repeat_order'. $repeat_order , 'The message' );

				wp_schedule_event( time(), 'every_fifteen_minutes', 'repeat_order_cron_hook' );
				}
				
				break;
				
			
			}
			
			$sendback = admin_url( "edit.php?post_type=$post_type&duplicate=success" );
			wp_redirect($sendback);
			
			exit();
		}
		
	}
	   
}

new CloneBulk;

