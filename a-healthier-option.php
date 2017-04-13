<?php
/*
Plugin Name: A Healthier Option
Description: Ever had an unweildy options table slow you down and cause crazy errors? Maybe you have, but don't know it? A Healther Option helps your options table get back into shape.
Version: 0.1.0
Author: Zao, Lisa League
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

define( 'AHO_BAD_EMOJI'  , '😢' );
define( 'AHO_OKAY_EMOJI' , '😐' );
define( 'AHO_GOOD_EMOJI' , '😊' );
define( 'AHO_GREAT_EMOJI', '😁' );

function aho_core_options() {
	return apply_filters( 'aho_core_options', array(
		'siteurl',
		'home', 2 => 'blogname', 3 => 'blogdescription', 4 => 'users_can_register', 5 => 'admin_email', 6 => 'start_of_week', 7 => 'use_balanceTags', 8 => 'use_smilies', 9 => 'require_name_email', 10 => 'comments_notify', 11 => 'posts_per_rss', 12 => 'rss_use_excerpt', 13 => 'mailserver_url',
		'mailserver_login', 15 => 'mailserver_pass', 16 => 'mailserver_port', 17 => 'default_category', 18 => 'default_comment_status', 19 => 'default_ping_status', 20 => 'default_pingback_flag', 21 => 'posts_per_page', 22 => 'date_format', 23 => 'time_format', 24 => 'links_updated_date_format', 25 => 'comment_moderation', 26 => 'moderation_notify',
		'permalink_structure', 28 => 'rewrite_rules', 29 => 'hack_file', 30 => 'blog_charset', 31 => 'moderation_keys', 32 => 'active_plugins', 33 => 'category_base', 34 => 'ping_sites', 35 => 'comment_max_links', 36 => 'gmt_offset', 37 => 'default_email_category', 38 => 'recently_edited', 39 => 'template',
		'stylesheet', 41 => 'comment_whitelist', 42 => 'blacklist_keys', 43 => 'comment_registration', 44 => 'html_type', 45 => 'use_trackback', 46 => 'default_role', 47 => 'db_version', 48 => 'uploads_use_yearmonth_folders', 49 => 'upload_path', 50 => 'blog_public', 51 => 'default_link_category', 52 => 'show_on_front',
		'tag_base', 54 => 'show_avatars', 55 => 'avatar_rating', 56 => 'upload_url_path', 57 => 'thumbnail_size_w', 58 => 'thumbnail_size_h', 59 => 'thumbnail_crop', 60 => 'medium_size_w', 61 => 'medium_size_h', 62 => 'avatar_default', 63 => 'large_size_w', 64 => 'large_size_h', 65 => 'image_default_link_type',
		'image_default_size', 67 => 'image_default_align', 68 => 'close_comments_for_old_posts', 69 => 'close_comments_days_old', 70 => 'thread_comments', 71 => 'thread_comments_depth', 72 => 'page_comments', 73 => 'comments_per_page', 74 => 'default_comments_page', 75 => 'comment_order', 76 => 'sticky_posts', 77 => 'widget_categories', 78 => 'widget_text',
		'widget_rss', 80 => 'uninstall_plugins', 81 => 'timezone_string', 82 => 'page_for_posts', 83 => 'page_on_front', 84 => 'default_post_format', 85 => 'link_manager_enabled', 86 => 'finished_splitting_shared_terms', 87 => 'site_icon', 88 => 'medium_large_size_w', 89 => 'medium_large_size_h', 90 => 'initial_db_version', 91 => 'moderation_keys',
		'recently_edited', 93 => 'blacklist_keys', 94 => 'uninstall_plugins'
	) );
}
function aho_is_innodb() {
	global $wpdb;

	$engine = $wpdb->get_row( $wpdb->prepare( "SHOW TABLE STATUS WHERE `Name` = %s", $wpdb->options ) )->Engine;

	return 'InnoDB' === $engine;
}

function aho_alter_options_table_engine() {
	$inno = aho_is_innodb();

	if ( $inno ) {
		return false;
	}

	global $wpdb;
	return $wpdb->query( 'ALTER TABLE ' . $wpdb->options . ' ENGINE=InnoDB;' );
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

function aho_turn_off_autoload( $option_name ) {
	global $wpdb;

	return $wpdb->update(
		$wpdb->options,
		array( 'autoload' => 'no' ),
		array( 'option_name' => $option_name ),
		array( '%s' ),
		array( '%s' )
	);
}

function aho_index_autoload_column() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
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
	<style type="text/css">
		table.health-matrix td,
		table.health-matrix th {
		    padding: 1em;
		    border: 1px solid #ccc;
		}
		table.health-matrix td.status {
			text-align: center;
			font-size: 3em;
		}
		table.health-matrix td.recommendation {
			max-width: 500px
		}
		.column-autoload .spinner {
			float: none;
		}
	</style>
	<script>
	jQuery( document ).ready( function( $ ) {

		// Autoload toggles
		$( '.autoload-toggle' ).on( 'click', function( evt ) {
			var $t = $( this );
			evt.preventDefault();

			$t.siblings( '.spinner' ).css( 'visibility', 'visible' );
			$.post(
				ajaxurl,
				{
					action      : 'aho_process_actions',
					aho_action  : 'autoload_toggle',
					option_name : $t.data( 'option-name' )
				},
				function( response ) {
					$t.siblings( '.spinner' ).hide();
					if ( response.success ) {
						$t.siblings( '.autoload-text' ).text( 'no' );
						$t.remove();
					}
				},
				'json'
			);
		} );

		// Index / engine toggles
		$( '.aho-action-submit' ).on( 'click', function(evt) {
			var $t = $( this );
			evt.preventDefault();
			$.post(
				ajaxurl,
				{
					action      : 'aho_process_actions',
					aho_action  : $t.data( 'action' )
				},
				function( response ) {
					if ( response.success ) {
						$t.parent.text( 'No action required.' );
					}
				},
				'json'
			);

		} );

		// Engine toggles
	} );
	</script>
	<table class="health-matrix">
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
					<td><em><?php echo wp_kses_post( $heuristic['title'] ); ?></td>
					<td class="status"><?php echo esc_html( $heuristic['status']() ); ?></td>
					<td class="recommendation"><?php echo wp_kses_post( $heuristic['recommendation']() ); ?></td>
					<td><?php echo wp_kses_post( $heuristic['action']() ); ?></td>
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
					return AHO_BAD_EMOJI;
				}

				$dropins = get_dropins();

				return AHO_GREAT_EMOJI;
			},
			'action'         => function() {
				$using = wp_using_ext_object_cache();

				if ( ! $using ) {
					return 'No action required.';
				}

				$dropins = get_dropins();

				return 'Using ' . $dropins['object-cache.php']['Name'];
			},
			'recommendation' => function() {
				$using = wp_using_ext_object_cache();

				if ( ! $using ) {
					return 'Consider using an object cache, like <a href="https://wordpress.org/plugins/memcached-redux/">Memcached</a> or <a href="https://wordpress.org/plugins/redis-cache/">Redis</a>.';
				}

				$dropins = get_dropins();

				return 'Using ' . ['object-cache.php']['Name'];
			}
		),
		array(
			'title' => 'Options Table Size',
			'status'         => function() {
				$count         = aho_get_option_count();
				$maybe_too_big = apply_filters( 'aho_too_many_options', 1000 );

				if ( $count < ( $maybe_too_big / 2 ) ) {
					return AHO_GREAT_EMOJI;
				}

				if ( $count < $maybe_too_big ) {
					return AHO_GOOD_EMOJI;
				}

				if ( $count < ( $maybe_too_big * 2 ) ) {
					return AHO_OKAY_EMOJI;
				}

				return AHO_BAD_EMOJI;

			},
			'action'         => function() {
				$count         = aho_get_option_count();
				$maybe_too_big = $count > apply_filters( 'aho_too_many_options', 1000 );

				return $maybe_too_big ? 'See table below.' : 'No action required.';
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
				return aho_is_innodb() ? AHO_GOOD_EMOJI : AHO_OKAY_EMOJI;
			},
			'action'         => function() {
				return aho_is_innodb() ? 'No action required.' : submit_button( 'Update to InnoDB engine', 'primary aho-action-submit', 'submit', true, array( 'data-action' => 'update-engine' ) );
			},
			'recommendation' => function() {
				return aho_is_innodb() ? 'No recommendation' : 'We recommend updating your options table engine to InnoDB. Before doing so, we highly recommend backing up your database.';
			},
		),
		array(
			'title' => 'Autoload Index',
			'status'         => function() {
				$is_innodb = aho_is_innodb();
				$indexed   = aho_autoload_column_is_indexed();

				// Ideally, we're running InnoDB engine w/ autoload indexed
				if ( $indexed && $is_innodb ) {
					return AHO_GREAT_EMOJI;
				}

				// Otherwise, MyISAM w/no index is alright
				if ( ! $indexed && ! $is_innodb ) {
					return AHO_GOOD_EMOJI;
				}

				// Not great, but easily fixed is InnoDb without an index on autoload
				if ( ! $indexed && $is_innodb ) {
					return AHO_OKAY_EMOJI;
				}

				// And really not good is an autoload index on MyISAM
				return AHO_BAD_EMOJI;
			},
			'action'         => function() {
				$is_innodb = aho_is_innodb();
				$indexed   = aho_autoload_column_is_indexed();

				// Ideally, we're running InnoDB engine w/ autoload indexed
				if ( $indexed && $is_innodb ) {
					return 'No action required';
				}

				// Otherwise, MyISAM w/no index is alright
				if ( ! $indexed && ! $is_innodb ) {
					return 'No action required.';
				}

				// Not great, but easily fixed is InnoDb without an index on autoload
				if ( ! $indexed && $is_innodb ) {
					return submit_button( 'Add Index to Autoload', 'primary aho-action-submit', 'submit', true, array( 'data-action' => 'add-index' ) );
				}

				// And really not good is an autoload index on MyISAM
				return submit_button( 'Update engine to InnoDB', 'primary aho-action-submit', 'submit', true, array( 'data-action' => 'update-engine' ) );
			},
			'recommendation' => function() {
				$is_innodb = aho_is_innodb();
				$indexed   = aho_autoload_column_is_indexed();

				// Ideally, we're running InnoDB engine w/ autoload indexed
				if ( $indexed && $is_innodb ) {
					return 'You are good to go!';
				}

				// Otherwise, MyISAM w/no index is alright
				if ( ! $indexed && ! $is_innodb ) {
					return 'You have a standard MyISAM engine, with no index on autoload. This is fine. However, you may find increased performance by changing your options table engine to InnoDB and adding an autoload index. As always, be sure to backup your database before making any changes to it.';
				}

				// Not great, but easily fixed is InnoDb without an index on autoload
				if ( ! $indexed && $is_innodb ) {
					return 'Your options table has the InnoDB engine, with no index on autoload. You may find increased performance by adding an autoload index. As always, be sure to backup your database before making any changes to it.';
				}

				// And really not good is an autoload index on MyISAM
				return 'You have a standard MyISAM engine, with an index on autoload. Many tests seem to indicate this is a poor configuration. We recommend changing your options table engine to InnoDB. As always, be sure to backup your database before making any changes to it.';
			},
		),
		array(
			'title'          => '<code>alloptions</code> cache size',
			'status'         => function() {
				$count         = aho_get_all_options_size();
				$maybe_too_big = apply_filters( 'aho_cache_bucket_limit', MB_IN_BYTES );

				if ( $count < ( $maybe_too_big * .5 ) ) {
					return AHO_GREAT_EMOJI;
				}

				if ( $count < ( $maybe_too_big * .75 ) ) {
					return AHO_GOOD_EMOJI;
				}

				if ( $count <= ( $maybe_too_big * .95 ) ) {
					return AHO_OKAY_EMOJI;
				}

				return AHO_BAD_EMOJI;

			},
			'action'         => function() {
				$count         = aho_get_all_options_size();
				$maybe_too_big = apply_filters( 'aho_cache_bucket_limit', MB_IN_BYTES );

				if ( $count < ( $maybe_too_big * .5 ) ) {
					return 'No action required';
				}

				if ( $count < ( $maybe_too_big * .75 ) ) {
					return 'No action required.';
				}

				if ( $count <= ( $maybe_too_big * .95 ) ) {
					return 'Consider pruning some options';
				}

				return 'Prune options via table below.';
			},
			'recommendation' => function() {
				$display_size  = aho_get_all_options_size( true );
				$count         = aho_get_all_options_size();
				$maybe_too_big = apply_filters( 'aho_cache_bucket_limit', MB_IN_BYTES );

				if ( $count < ( $maybe_too_big * .5 ) ) {
					return 'No recommendation at this time.';
				}

				if ( $count < ( $maybe_too_big * .75 ) ) {
					return 'Your <code>alloptions</code> bucket is near 75% capacity (' . $display_size .', well, ' . $count . ' bytes to be exact). While not currently problematic, you may want to consider pruning some options by either deleting unused options, or setting the autoload value on options that are infrequently accessed from "yes" to "no".';
				}

				if ( $count <= ( $maybe_too_big * .95 ) ) {
					return 'Your <code>alloptions</code> bucket is near 95% capacity (' . $display_size .', well, ' . $count . ' bytes to be exact). While not yet problematic, we would recommend pruning some options by either deleting unused options, or setting the autoload value on options that are infrequently accessed from "yes" to "no".';
				}

				return 'Your <code>alloptions</code> bucket is near or over capacity (' . $display_size .', well, ' . $count . ' bytes to be exact). This is problematic - we would recommend pruning some options by either deleting unused options, or setting the autoload value on options that are infrequently accessed from "yes" to "no".';
			},
		),
		array(
			'title'          => 'Number of Transients',
			'status'         => function() {
				global $wpdb;
				$transients = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->options . " WHERE `option_name` LIKE '_transient_%'" );

				$maybe_too_big = apply_filters( 'aho_too_many_transients', 150 );

				if ( $transients < ( $maybe_too_big * .5 ) ) {
					return AHO_GREAT_EMOJI;
				}

				if ( $transients < ( $maybe_too_big * .75 ) ) {
					return AHO_GOOD_EMOJI;
				}

				if ( $transients <= ( $maybe_too_big * .95 ) ) {
					return AHO_OKAY_EMOJI;
				}

				return AHO_BAD_EMOJI;
			},
			'action'         => function() {
				global $wpdb;
				$transients = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->options . " WHERE `option_name` LIKE '_transient_%'" );

				return $transients > apply_filters( 'aho_too_many_transients', 150 ) ? '<a class="button button-primary href="https://wordpress.org/plugins/transients-manager/">Download and use Transients Manager</a>' : 'No action required.';
			},
			'recommendation' => function() {
				global $wpdb;
				$transients = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->options . " WHERE `option_name` LIKE '_transient_%'" );

				return $transients > apply_filters( 'aho_too_many_transients', 150 ) ? 'With ' . $transients . ' transients, your database has over our recommended limit of ' . apply_filters( 'aho_too_many_transients', 150 ) . '. We recommend using a transients management plugin to clean up your transients - it is likely that you are using some plugins who are bad actors here.' : 'No recommendation, ' . $transients .' transients is well within our recommended limit.';
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
		<h2>A Healthier Options Table</h2>
		<?php
			settings_fields( 'aho_settings' );
			do_settings_sections( 'aho_settings' );
		?>

	    <form method="post" action="">
	        <input type="hidden" name="page" value="aho_list_table">
	        <?php
		        $list_table = new AHO_Options_List_Table();
		        $list_table->prepare_items();
		        $list_table->display();
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
        'orderby'    => 'size',
        'order'      => 'DESC',
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

	protected function get_sortable_columns() {
		return array(
			'option_name' => array( 'option_name', true ),
			'autoload'    => array( 'autoload', true ),
			'size'        => array( 'size', true )
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
    public function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'option_name':
                return $item->option_name;

	        case 'option_value':
	            return strlen( $item->option_value ) < 45 ? $item->option_value : substr( $item->option_value, 0, 40 ) . '[...]';

            case 'size':
                return number_format( mb_strlen( $item->option_value ) );

            case 'autoload':
                return 'no' === $item->autoload ? $item->autoload  : '<span class="autoload-text">' . $item->autoload . '</span><br /><a class="autoload-toggle" data-option-name="' . $item->option_name . '"href="#">Turn autoload off</a><span class="spinner"></span>';

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
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
    public function column_option_name( $item ) {

        $actions = array();

		if ( ! in_array( $item->option_name, aho_core_options(), true ) ) {

			$url = admin_url( 'tools.php?page=a_healthier_option&action=delete&id=' . $item->option_name );

			if ( isset( $_GET['paged'] ) ) {
				$url = add_query_arg( 'paged', absint( $_GET['paged'] ), $url );
			}

			$actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $url, $item->option_name, __( 'Delete this option', 'a-healthier-option' ), __( 'Delete', 'a-healthier-option' ) );
		}

        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'tools.php?page=a_healthier_option&action=view&id=' . $item->option_id ), $item->option_name, $this->row_actions( $actions ) );
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="option_id[]" value="%d" />', $item->option_id
        );
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    public function prepare_items() {

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

function aho_process_actions() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( $_REQUEST  );
	}

	if ( ! isset( $_REQUEST['action'] ) ) {
		return;
	}

	if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) ) {
		delete_option( $_GET['id'] );
		wp_safe_redirect( remove_query_arg( array( 'action', 'id' ) ) );
		exit;
	}

	if ( ! isset( $_REQUEST['aho_action'] ) ) {
		wp_send_json_error();
	}

	$success = false;

	switch ( $_REQUEST['aho_action'] ) {

		case 'autoload_toggle':
			if ( isset( $_REQUEST['option_name'] ) ) {
				$success = aho_turn_off_autoload( $_REQUEST['option_name'] );
			}
			break;

		case 'update-engine':
			$success = aho_alter_options_table_engine();
			break;

		case 'add-index':
		 	$success = aho_index_autoload_column();
			break;

		default:

			break;
	}

	if ( $success ) {
		wp_send_json_success();
	}

	wp_send_json_error();

}

add_action( 'wp_ajax_aho_process_actions'       , 'aho_process_actions' );
add_action( 'load-tools_page_a_healthier_option', 'aho_process_actions' );
