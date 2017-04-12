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

function aho_autoload_column_is_indexed() {
	global $wpdb;

	$indicies = wp_list_pluck( $wpdb->get_results( 'SHOW INDEX FROM ' . $wpdb->options ), 'Column_name' );

	return in_array( 'autoload', $indicies, true );
}

function aho_index_autoload_column() {
	global $wpdb;
	return add_clean_index( $wpdb->options, 'autoload' );
}

function aho_settings_init() {

	register_setting( 'aho_settings', 'aho_settings' );

	add_settings_section(
		'aho_aho_settings_section',
		'',
		'aho_settings_section_callback',
		'aho_settings'
	);

}

add_action( 'admin_init', 'aho_settings_init' );

function aho_settings_section_callback() {
	$health_matrix = aho_get_healh_matrix_rows();
	?>
	<p>It can be pretty easy for your options table to get unweildy.</p>
	<p>Depending on the configuration of your server, your database, and your object cache - what WordPress intended to be a simple (and relatively small) table of options can turn into the main culprit behind your site's slow speed.</p>
	<p>Below, we've included a health matrix for your options table. It measure a lot of technical stats about your table, makes recommendations, and gives you a simple way to implement those recommendations. As always, you should totally make a backup of your database before doing anything, really, ever.</p>

	<h3>Health Matrix</h3>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Status</th>
				<th>Recommendation</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $health_matrix as $heuristic ) : ?>
				<tr>
					<td><?php echo wp_kses_post( $heuristic['title'] ); ?></td>
					<td><?php echo esc_html( $heuristic['status']() ); ?></td>
					<td><?php echo wp_kses_post( $heuristic['action']() ); ?></td>
					<td><?php echo esc_html( $heuristic['recommendation']() ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

function aho_get_healh_matrix_rows() {
	return array(
		array(
			'title'  => 'Using an external object cache?',
			'status'         => function() {
				$using = wp_using_ext_object_cache();

				if ( ! $using ) {
					return 'No';
				}

				$dropins = get_dropins();

				return 'Using ' . ['object-cache.php']['Name'];
			},
			'action'         => function() {
				$using = wp_using_ext_object_cache();

				if ( ! $using ) {
					return 'No';
				}

				$dropins = get_dropins();

				return 'Using ' . ['object-cache.php']['Name'];
			},
			'recommendation' => function() {
				$using = wp_using_ext_object_cache();

				if ( ! $using ) {
					return 'Consider using an object cache, like Memcached or Redis.';
				}

				$dropins = get_dropins();

				return 'Using ' . ['object-cache.php']['Name'];
			}
		),
		array(
			'title' => 'Options Table Size',
			'status'         => function() {
				$count         = aho_get_option_count();
				$maybe_too_big = $count > apply_filters( 'aho_too_many_options', 1000 );

				return $maybe_too_big ? 'Too many options! (' . $count . ')' : 'Healthy amount of options (' . $count . ')';
			},
			'action'         => function() {
				$count         = aho_get_option_count();
				$maybe_too_big = $count > apply_filters( 'aho_too_many_options', 1000 );

				return $maybe_too_big ? 'See table above.' : 'No action required';
			},
			'recommendation' => function() {
				$count         = aho_get_option_count();
				$maybe_too_big = $count > apply_filters( 'aho_too_many_options', 1000 );

				return $maybe_too_big ? 'Too many options! (' . $count . ')' : 'Healthy amount of options (' . $count . ')';
			},
		),
		array(
			'title' => 'Options Table Engine',
			'status'         => function() {
				return aho_is_innodb() ? 'InnoDB' : 'MyISAM';
			},
			'action'         => function() {
				return aho_is_innodb() ? 'No action required' : 'Update Options Table to InnoDB engine.';
			},
			'recommendation' => function() {
				return aho_is_innodb() ? 'No recommendation' : 'We recommend updating your options table engine to InnoDB. Before doing so, we highly recommend backing up your database.';
			},
		),
		array(
			'title' => 'Autoload Index',
			'status'         => function() {
				return aho_autoload_column_is_indexed() ? 'Autoload column indexed' : 'Autoload column unindexed';
			},
			'action'         => function() {
				return aho_autoload_column_is_indexed() ? 'No action required' : 'Index autoload column';
			},
			'recommendation' => function() {
				return aho_autoload_column_is_indexed() ? 'No recommendation' : 'We recommend indexing your autoload column, as that can significantly increase the speed of querying the options table.';
			},
		),
		array(
			'title'          => '<code>alloptions</code> cache size',
			'status'         => function() {
				$too_big = aho_get_all_options_size() > MB_IN_BYTES;

				return $too_big ? 'Too big - '. aho_get_all_options_size( true ) : 'Just fine - ' . aho_get_all_options_size( true );
			},
			'action'         => function() {
				$too_big = aho_get_all_options_size() > MB_IN_BYTES;

				return $too_big ? 'See list table above.' : 'You good';
			},
			'recommendation' => function() {
				$too_big = aho_get_all_options_size() > MB_IN_BYTES;

				return $too_big ? 'We recommend deleting options, or changing the autoload setting from "yes" to "no" until this value is under 1MB.' : 'You good';
			},
		),
		array(
			'title'          => 'Number of Transients',
			'status'         => function() {
				global $wpdb;
				$transients = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->options . " WHERE `option_name` LIKE '_transient_%'" );

				return $transients > apply_filters( 'aho_too_many_transients', 150 ) ? 'Too many!' : 'you good';
			},
			'action'         => function() {
				global $wpdb;
				$transients = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->options . " WHERE `option_name` LIKE '_transient_%'" );

				return $transients > apply_filters( 'aho_too_many_transients', 150 ) ? 'Download and use Transients Manager' : 'you good';
			},
			'recommendation' => function() {
				global $wpdb;
				$transients = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->options . " WHERE `option_name` LIKE '_transient_%'" );

				return $transients > apply_filters( 'aho_too_many_transients', 150 ) ? 'We recommend using a transients management plugin to clean up your transients - it is likely that you are using some plugins who are bad actors here.' : 'you good';
			},
		),
	);
}

function aho_get_health_score() {

}

/**
 * background-color: rgb($rgb)
 * @return [type] [description]
 */
function aho_get_health_color() {
	$health_score = aho_get_health_score();

	if ( 100 === $health_score ) {
		$health_score--;
	}

	if ( $health_score < 50 ) {
		// green to yellow
		$r = floor( 255 * ( $health_score / 50 ) );
		$g = 255;
	} else {
		// yellow to red
		$r = 255;
		$g = floor( 255 * ( ( 50 - $health_score % 50 ) / 50 ) );
	}

	$b = 0;

	return "$r,$g,$b";
}

function aho_options_page() {
	?>

	<div class="wrap">

	    <form method="post">
	        <input type="hidden" name="page" value="aho_list_table">
	        <?php
		        $list_table = new AHO_Options_List_Table();
		        $list_table->prepare_items();
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
	</div>
	<?php
}

/**
 * Get all option
 *
 * @param $args array
 *
 * @return array
 */
function aho_get_all_options( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'option_id',
        'order'      => 'ASC',
    );

    $args = wp_parse_args( $args, $defaults );

	if ( 'size' === $args['orderby'] ) {
		$args['orderby'] = 'CHAR_LENGTH(option_value)';
	}

    return $wpdb->get_results( 'SELECT * FROM ' . $wpdb->options . ' ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'] );
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

function aho_get_all_options_size( $format = false ) {
	$size = mb_strlen( serialize( wp_load_alloptions() ) );

	return $format ? size_format( $size ) : $size;
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

    public function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    public function no_items() {
        _e( 'No options found', 'a-healthier-option' );
    }

	public function get_sortable_columns() {
		return array(
			array( 'option_name', false ),
			array( 'autoload', false ),
			array( 'size', false )
		);
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
	            return strlen( $item->option_value ) < 45 ? $item->option_value : substr( $item->option_value, 0, 40 ) . '[...]';

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

        $this->items  = aho_get_all_options( $args );

        $this->set_pagination_args( array(
            'total_items' => aho_get_option_count(),
            'per_page'    => $per_page
        ) );
    }
}
