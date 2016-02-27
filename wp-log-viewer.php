<?php

/*
Plugin Name: WP Log Viewer
Plugin URI: https://github.com/bradmkjr/wp-log-viewer
Description: Monolog WordPress Log Viewer Plugin
Author: Bradford Knowlton
Author URI: http://bradknowlton.com/
Version: 1.0.2
GitHub Plugin URI: https://github.com/bradmkjr/wp-log-viewer
GitHub Branch: master

*/

if(!class_exists('WP_Log_List_Table')){
	include_once plugin_dir_path( __FILE__ ) . 'includes/wp-log-list-table.inc.php';
}


// Hook for adding admin menus
add_action('admin_menu', 'wp_add_pages');
// action function for above hook
function wp_add_pages() {
	// Add a new submenu under Tools:
	$log_hook = add_management_page('Log Viewer', __('Log Viewer','wp_log_viewer'), __('Log Viewer','wp_log_viewer'), 'manage_options', 'log-viewer', 'wp_log_viewer');
	add_action( "load-$log_hook", 'add_log_options' );
}

function add_log_options() {
	global $logListTable;
	$option = 'per_page';
	$args = array(
		'label' => 'Log Entries',
		'default' => 15,
		'option' => 'log_entries_per_page'
	);
	add_screen_option( $option, $args );
	//Create an instance of our package class...
	$logListTable = new WP_Log_List_Table();
}


// ss_sublevel_page() displays the page content for the first submenu
// of the custom Test Toplevel menu
function wp_log_viewer() {
	global $logListTable;
	//Fetch, prepare, sort, and filter our data...
	$logListTable->prepare_items();
?>
    <div class="wrap">
    	<?php echo "<h2>" . __( 'WordPress Log', 'menu-test' ) . "</h2>"; ?>
    	<?php
	global $bulk_counts;
	global $bulk_messages;
	$bulk_messages['log'] = array(
		'updated' => _n( '%s log updated.', '%s logs updated.', $bulk_counts['updated'] ),
		'locked' => _n( '%s log not updated, somebody is editing it.', '%s logs not updated, somebody is editing them.', $bulk_counts['locked'] ),
		'deleted' => _n( '%s log permanently deleted.', '%s logs permanently deleted.', $bulk_counts['deleted'] ),
		'trashed' => _n( '%s log moved to the Trash.', '%s logs moved to the Trash.', $bulk_counts['trashed'] ),
		'untrashed' => _n( '%s log restored from the Trash.', '%s logs restored from the Trash.', $bulk_counts['untrashed'] ),
	);
	$post_type = 'log';
	$messages = array();
	foreach ( $bulk_counts as $message => $count ) {
		if ( isset( $bulk_messages[ $post_type ][ $message ] )  && $count ){
			$messages[] = sprintf( $bulk_messages[ $post_type ][ $message ], number_format_i18n( $count ) );
		}
	}
	if ( $messages )
		echo '<div id="message" class="updated"><p>' . join( ' ', $messages ) . '</p></div>';
	unset( $messages );
?>
<?php $logListTable->views(); ?>

   		 <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="log-filter" method="get">
        	<?php // $logListTable->search_box( $post_type_object->labels->search_items, 'post' ); ?>

        	<input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="post_type" class="post_type_page" value="<?php echo $post_type; ?>" />
            <!-- Now we can render the completed list table -->
            <?php $logListTable->display() ?>
        </form>
    </div>
    <?php
}