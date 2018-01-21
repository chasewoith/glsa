<?php 
/**
 * @author 		: Saravana Kumar K
 * @author url  : http://iamsark.com
 * @copyright	: sarkware.com
 *
 * This module is responsible for loading and initializing various components of WC Fields Factory
 *
 */

if (!defined( 'ABSPATH' )) { exit; }

class Wcff_Setup {
    
    public function __construct() {
        $this->register_post_types();
        if( is_admin() ) {
            $this->register_assets();
            add_action( 'admin_menu', array( $this,'register_admin_menus' ) );
        }
    }
    
    /**
     * Does the regiteration for core custom post types  
     * 
     */
    private function register_post_types() {
        
        /* Label object for custom product post type */
        $wccpf_labels = array (
            'name' => 'WC&nbsp;Product&nbsp;Field&nbsp;Groups',
            'singular_name' => 'WC Product Custom Fields',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New WC Product Field Group',
            'edit_item' => 'Edit WC Product Field Group',
            'new_item' =>  'New WC Product Field Group',
            'view_item' => 'View Product Field Group',
            'search_items' => 'Search WC Product Field Groups',
            'not_found' =>  'No WC Product Field Groups found',
            'not_found_in_trash' => 'No WC Product Field Groups found in Trash',
        );
        
        /* Label object for custom admin post type */
        $wccaf_labels = array (
            'name' => 'WC&nbsp;Admin&nbsp;Field&nbsp;Groups',
            'singular_name' => 'WC Custom Admin Fields',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New WC Admin Field Group',
            'edit_item' => 'Edit WC Admin Field Group',
            'new_item' =>  'New WC Admin Field Group',
            'view_item' => 'View Admin Field Group',
            'search_items' => 'Search WC Admin Field Groups',
            'not_found' =>  'No WC Admin Field Groups found',
            'not_found_in_trash' => 'No WC Admin Field Groups found in Trash'
        );
        
        /* Custom post type registration for Products ( Front End ) */
        register_post_type(
            'wccpf',
            array (
                'labels' => $wccpf_labels,
                'public' => false,
                'show_ui' => true,
                '_builtin' =>  false,
                'capability_type' => 'page',
                'hierarchical' => true,
                'rewrite' => false,
                'query_var' => "wccpf",
                'supports' => array( 'title' ),
                'show_in_menu'	=> false
            )
        );
        
        /* Custom post type registration for Products ( Admin ) */
        register_post_type(
            'wccaf',
            array (
                'labels' => $wccaf_labels,
                'public' => false,
                'show_ui' => true,
                '_builtin' =>  false,
                'capability_type' => 'page',
                'hierarchical' => true,
                'rewrite' => false,
                'query_var' => "wccaf",
                'supports' => array( 'title' ),
                'show_in_menu'	=> false
            )
        );
        
    }
    
    /**
     * Responsible for inserting Admin menu and submenu
     * 
     */
    public function register_admin_menus() {
        
        /* This is the main menu entry for WC Fields Factory */
        $admin = add_menu_page(
            "WC Fields Factory",
            "Fields Factory",
            "manage_woocommerce",
            "edit.php?post_type=wccpf",
            false,
            null
        );
        
        /* Sub menu for Product Fields */
        add_submenu_page(
            "edit.php?post_type=wccpf",
            "Product Fields",
            "Product Fields",
            "manage_woocommerce",
            "edit.php?post_type=wccpf"
        );
        
        /* Sub menu for Admin Fields */
        add_submenu_page(
            "edit.php?post_type=wccpf",
            "Admin Fields",
            "Admin Fields",
            "manage_woocommerce",
            "edit.php?post_type=wccaf"
        );
        
        /* Sub menu for Option page */
        add_submenu_page(
            "edit.php?post_type=wccpf",
            "Wc Fields Factory Options",
            "Settings",
            "manage_woocommerce",
            "wcff_settings",
            "wccpf_render_option_page"
        );
        
    }
	
	public function register_assets() {	    
	    wp_register_script( 'wcff-script', WCFF()->info['dir'] . 'assets/js/wcff-admin.js', 'jquery', WCFF()->info['version'] );
	    wp_register_style( 'wcff-style', WCFF()->info['dir'] . 'assets/css/wcff-admin.css' );	    
	}
    
}

new Wcff_Setup();

?>