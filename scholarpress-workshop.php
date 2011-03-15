<?php
/*
Plugin Name: ScholarPress Workshop
Plugin URI: http://scholarpress.net/
Description: Organize workshops with the power of WordPress and Zotero.
Version: 1.0
Author: Center for History and New Media
Author URI: http://scholarpress.net/
*/

/*
    Copyright (C) 2009-2011, Center for History and New Media. All rights
    reserved.
    
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class ScholarPress_Workshop {
        
    /**
     * ScholarPress Workshop constructor
     *
     * @since 1.0
     * @uses add_action()
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     */
	function scholarpress_workshop() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'plugins_loaded', array( $this, 'loaded' ) );
		// When Researcher is loaded, get includes.
		add_action( 'scholarpress_workshop_loaded', array( $this, 'includes' ) );
        add_action( 'scholarpress_workshop_loaded', array($this, 'flush_rewrite_rules') );
        
		// When Researcher is initialized, add localization files.
		add_action( 'scholarpress_workshop_init', array( $this, 'textdomain' ) );
		
		// Load the post types
		add_action( 'scholarpress_workshop_init', array( $this, 'register_post_types' ) );
		
		add_action( 'scholarpress_workshop_admin_init', array( $this, 'add_meta_boxes') );
		
		add_action( 'save_post', array( $this, 'save_post') );
	}

    /**
     * Adds a plugin initialization action.
     */
	function init() {
		do_action( 'scholarpress_workshop_init' );
	}

	/**
     * Adds a plugin admin initialization action.
     */
	function admin_init() {
	    do_action( 'scholarpress_workshop_admin_init');
	}

	/**
     * Adds a plugin loaded action.
     */
	function loaded() {
		do_action( 'scholarpress_workshop_loaded' );
	}

    /**
     * Includes other necessary plugin files.
     */
	function includes() {
        // require( dirname( __FILE__ ).'/phpZotero/phpZotero.php' );
	}

    /**
     * Handles localization files. Added on scholarpress_workshop_init. 
     * Plugin core localization files are in the 'languages' directory. Users
     * can also add custom localization files in 
     * 'wp-content/scholarpress-workshop-files/languages' if desired.
     *
     * @uses load_textdomain()
     * @uses get_locale()
     */
	function textdomain() {
		$locale = get_locale();
		$mofile_custom = WP_CONTENT_DIR . "/scholarpress-workshop-files/languages/spworkshop-$locale.mo";
		$mofile_packaged = WP_PLUGIN_DIR . "/scholarpress-workshop/languages/spworkshop-$locale.mo";

    	if ( file_exists( $mofile_custom ) ) {
      		load_textdomain( 'spworkshop', $mofile_custom );
      		return;
      	} else if ( file_exists( $mofile_packaged ) ) {
      		load_textdomain( 'spworkshop', $mofile_packaged );
      		return;
      	}
	}
	
	function register_post_types() {
	     $workshopLabels = array(
			'name' => _x('Workshops', 'workshop general name'),
			'singular_name' => _x('Workshop', 'single workshop entry'),
			'add_new' => _x('Add New', 'workshop'),
			'add_new_item' => __('Add New Workshop'),
			'edit_item' => __('Edit Workshop Entry'),
			'new_item' => __('New Workshop Entry'),
			'view_item' => __('View Workshop Entry'),
			'search_items' => __('Search Workshops'),
			'not_found' =>  __('No entries found'),
			'not_found_in_trash' => __('No entries found in Trash'),
			'parent_item_colon' => ''
		  );
		
		$workshopPostDef = array(
			'label'                 => __( 'workshop', 'spworkshop' ),
			'labels'                => $workshopLabels,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'_builtin'              => false,
			'show_ui'               => true,
			'capability_type'       => 'page',
			'hierarchical'          => true,
            'supports'              => array( 'title', 'editor', 'custom-fields' ),
			'rewrite'               => array("slug" => "workshop") // Permalinks format
	    );
		
		register_post_type( 'sp_workshop', $workshopPostDef );
	}
	
	function add_meta_boxes() {
        add_meta_box("zotero-information", "Zotero Information", array($this, "zotero_meta_box"), "sp_workshop", "side", "low");
	}

    function zotero_meta_box(){
        global $post;
        $custom = get_post_custom($post->ID);
        if (array_key_exists('zotero_user_id', $custom)) {
            $zotero_user_id = $custom["zotero_user_id"][0];
        } else {
            $zotero_user_id = '';
        }
        if (array_key_exists('zotero_api_key', $custom)) {
            $zotero_api_key = $custom["zotero_api_key"][0];
        } else {
            $zotero_api_key = '';
        }
        ?>
        <label>Zotero User ID:</label>
        <input name="zotero_user_id" value="<?php echo $zotero_user_id; ?>" />
        <br />
        <label>Zotero API Key:</label>
        <input name="zotero_api_key" value="<?php echo $zotero_api_key; ?>" />
        <?php
    }
    
    function save_post(){
        global $post;
        if (array_key_exists('zotero_user_id', $_POST)) {
            update_post_meta($post->ID, "zotero_user_id", $_POST["zotero_user_id"]);
        }
        if (array_key_exists('zotero_api_key', $_POST)) { 
            update_post_meta($post->ID, "zotero_api_key", $_POST["zotero_api_key"]);
        }
    }

    function flush_rewrite_rules() {
        
    }
}

$scholarPressWorkshop = new ScholarPress_Workshop();