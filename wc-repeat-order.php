<?php

   /*
   Plugin Name: Repeat Woocommerce Order 
   Description: Adds a Repeat link to Woocommerce on the order actions to repeat the existing order
   Version: 1.0
   Author: Hassan Iqbal & Jamie Gill
   License: GPLv2 or later
   */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    define( 'WCO_PLUGIN_DIR', dirname( __FILE__ ) );
    
    require_once WCO_PLUGIN_DIR . '/classes/class-clone-order.php';
    require_once WCO_PLUGIN_DIR . '/classes/class-clone-bulk.php';
    
 
    function wdo_get_woo_version_number() {
    
    	$get_plugin_path = realpath(__DIR__ . '/..');
    	$woo_path = $get_plugin_path . '/woocommerce/woocommerce.php';
		$plugin_data = get_file_data( $woo_path, array('Version' => 'Version'), false);
		
		return $plugin_data['Version'];
		
	}

	// If Woo < v3 deactivate this and display error
	
	$ver_checks = wdo_get_woo_version_number();
	
	if ($ver_checks < '3') {
		
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die('Repeat Woocommerce Order supports Woocommerce 3 and above. For a compatible version for older Woocommerce versions please contact the Plugin Author');	
		
	}
	

    if ($ver_checks > '3.3.0') {
    
    	add_filter( 'woocommerce_admin_order_actions', 'clone_order_cta', 100, 2 );
	
		function clone_order_cta( $actions, $order ) {
				
			$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			
			$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $order_id );
			//$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $order_id );
				
			$copy_link = wp_nonce_url( add_query_arg( array( 'duplicate' => 'init' ), $url ), 'duplicate_order_nonce', 'duplicate-order-nonce' );
			
			$actions['duplicate'] = array(
				'url'       => $copy_link,
				'name'      => __( 'Repeat Order', 'woocommerce' ),
				'action'    => "view duplicate", // keep "view" class for a clean button CSS
			);
			    
			return $actions;
						
		}
		
		add_filter( 'woocommerce_admin_order_preview_actions', 'clone_order_preview_cta', 100, 2 );
		
		function clone_order_preview_cta( $actions, $order ) {
				
			$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			
			$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $order_id );
				
			$copy_link = wp_nonce_url( add_query_arg( array( 'duplicate' => 'init' ), $url ), 'duplicate_order_nonce', 'duplicate-order-nonce' );
				
			$actions['duplicate'] = array(
				'url'       => $copy_link,
				'name'      => __( 'Duplicate', 'woocommerce' ),
				'action'    => "view duplicate-preview", // keep "view" class for a clean button CSS
			);
			    
			return $actions;
						
		}
		
		add_action( 'admin_head', 'add_custom_order_status_actions_button_css' );
			
		function add_custom_order_status_actions_button_css() {
			echo '<style>.view.duplicate::after { font-family: WooCommerce !important; content: "\e007" !important; }</style>';
		}
    
    } else {
	    
	    // Legacy support - Hooks Duplicate CTA to shop_order post type
    	
	    function clone_order_cta($actions, $post){
			
			
			if ($post->post_type=='shop_order') {
		        
		        $url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $post->ID );
		        
		        $copy_link = wp_nonce_url( add_query_arg( array( 'duplicate' => 'init' ), $url ), 'duplicate_order_nonce', 'duplicate-order-nonce' );
		        print_r($copy_link);
		        $actions = array_merge( $actions, 
		        	array(
		            	'duplicate' => sprintf( 'adfasdf asdfasdf<a href="%1$s">%2$s</a>%3$s',
		                	esc_url( $copy_link ), 
		                	'Repeat',
							'adfasdf'
						) 
					) 
				);
		    }
		    
		    return $actions;
					
		}
		
		add_filter( 'post_row_actions', 'clone_order_cta', 10, 2 );
	    
    }
    
}