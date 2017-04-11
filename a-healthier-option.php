<?php
/*
Plugin Name: A Healthier Option
Description: Ever had an unweildy options table slow you down and cause crazy errors? Maybe you have, but don't know it? A Healther Option helps your options table get back into shape.
Version: 0.1.0
Author: Zao
Author URI: https://zao.is/
License: GPLv2 or later
Text Domain: a-healthier-option
Domain Path:
 */

/*
 Copyright by Zao and the contributors

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined( 'ABSPATH' ) || die;

function aho_is_innodb() {
	global $wpdb;

	$engine = $wpdb->get_row( $wpdb->prepare( "SHOW TABLE STATUS WHERE `Name` = %s", $wpdb->options ) )->Engine;

	return 'InnoDB' === $engine;
}

function aho_add_admin_menu() {
	add_submenu_page( 'tools.php', __( 'A Healthier Option', 'a-healthier-option' ), __( 'A Healthier Option', 'a-healthier-option' ), 'manage_options', 'a_healthier_option', 'aho_options_page' );
}

add_action( 'admin_menu', 'aho_add_admin_menu' );

function aho_settings_init() {

	register_setting( 'aho_settings', 'aho_settings' );

	add_settings_section(
		'aho_aho_settings_section',
		__( 'Customize your options table', 'aho' ),
		'aho_settings_section_callback',
		'aho_settings'
	);

	add_settings_field(
		'aho_text_field_0',
		__( 'Add an index to the autoload column', 'aho' ),
		'aho_text_field_0_render',
		'aho_settings',
		'aho_aho_settings_section'
	);
}

add_action( 'admin_init', 'aho_settings_init' );

function aho_text_field_0_render() {

	$options = get_option( 'aho_settings' );
	?>
	<input type='text' name='aho_settings[aho_text_field_0]' value='<?php echo $options['aho_text_field_0']; ?>'>
	<?php

}

function aho_settings_section_callback() {
	_e( 'Depending on the nature of your database server, the settings below should be helpful for improving the performance of your options table.', 'aho' );
}

function aho_options_page() {
	?>
    <h2><?php _e( 'Options', 'a-healthier-option' ); ?></h2>

    <form method="post">
        <input type="hidden" name="page" value="aho_list_table">

        <?php
        $list_table = new AHO_Options_List_Table();
        $list_table->prepare_items();
        $list_table->search_box( 'search', 'search_id' );
        $list_table->display();
        ?>
    </form>

	<form action='options.php' method='post'>

		<h2>A Healthier Options Table</h2>

		<?php
			settings_fields( 'aho_settings' );
			do_settings_sections( 'aho_settings' );
			submit_button();
		?>

	</form>
	<?php
}

/**
 * Get all option
 *
 * @param $args array
 *
 * @return array
 */
function aho_get_all_option( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'option_id',
        'order'      => 'ASC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'option-all';
    $items     = wp_cache_get( $cache_key, 'a-healthier-option' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->options . ' ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'] );

        wp_cache_set( $cache_key, $items, 'a-healthier-option' );
    }

    return $items;
}

/**
 * Fetch all option from database
 *
 * @return array
 */
function aho_get_option_count() {
    global $wpdb;

    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->options );
}

function aho_get_all_options_size() {
	return size_format( mb_strlen( serialize( wp_load_alloptions() ) ) );
}

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class AHO_Options_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'option',
            'plural'   => 'options',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No options found', 'a-healthier-option' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'option_name':
                return $item->option_name;

	        case 'option_value':
	            return $item->option_value;

            case 'size':
                return number_format( mb_strlen( $item->option_value ) );

            case 'autoload':
                return $item->autoload;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'option_name'  => __( 'Name', 'a-healthier-option' ),
            'option_value' => __( 'Value', 'a-healthier-option' ),
            'autoload'     => __( 'Autoload', 'a-healthier-option' ),
            'size'         => __( 'Size (in bytes)', 'a-healthier-option' ),

        );

        return $columns;
    }

    /**
     * Render the designation name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_option_name( $item ) {

        $actions           = array();
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=options-health&action=edit&id=' . $item->option_id ), $item->option_id, __( 'Edit this item', 'a-healthier-option' ), __( 'Edit', 'a-healthier-option' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=options-health&action=delete&id=' . $item->option_id ), $item->option_id, __( 'Delete this item', 'a-healthier-option' ), __( 'Delete', 'a-healthier-option' ) );

        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=options-health&action=view&id=' . $item->option_id ), $item->option_name, $this->row_actions( $actions ) );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array( 'name', 'size', 'autoload' ),
        );

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'trash'  => __( 'Move to Trash', 'a-healthier-option' ),
        );
        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="option_id[]" value="%d" />', $item->option_id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=sample-page' );

        foreach ($this->counts as $key => $value) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $this->items  = aho_get_all_option( $args );

        $this->set_pagination_args( array(
            'total_items' => aho_get_option_count(),
            'per_page'    => $per_page
        ) );
    }
}
