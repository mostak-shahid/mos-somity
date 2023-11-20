<?php

// Loading WP_List_Table class file
// We need to load it as it's not automatically loaded by WordPress
if (!class_exists('WP_List_Table')) {
      require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Extending class
class Mos_Somity_Deposits_List_Table extends WP_List_Table
{
    // Here we will add our code

    // define $table_data property
    private $table_data;

    // Get table data
    private function get_table_data( $search = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'mos_deposits';
        
        if ( !empty($search) ) {
            return $wpdb->get_results(
                "SELECT {$wpdb->prefix}mos_deposits.*, {$wpdb->prefix}users.display_name FROM {$wpdb->prefix}mos_deposits LEFT JOIN {$wpdb->prefix}users ON {$wpdb->prefix}mos_deposits.user_id =  {$wpdb->prefix}users.ID WHERE {$wpdb->prefix}users.display_name Like '%{$search}%' OR '{$wpdb->prefix}mos_deposits.apply_date' Like '%{$search}%' OR '{$wpdb->prefix}mos_deposits.status' Like '%{$search}%'",
                ARRAY_A
            );
        } else {
            return $wpdb->get_results(
                "SELECT {$wpdb->prefix}mos_deposits.*, {$wpdb->prefix}users.display_name FROM {$wpdb->prefix}mos_deposits LEFT JOIN {$wpdb->prefix}users ON {$wpdb->prefix}mos_deposits.user_id =  {$wpdb->prefix}users.ID",
                ARRAY_A
            );
        }
    }

    // Define table columns
    function get_columns()
    {
        $columns = array(
                'cb'            => '<input type="checkbox" />',
                'display_name'          => __('Name', 'mos-admin-table'),
                'source'          => __('Source', 'mos-admin-table'),
                'amount'          => __('Amount', 'mos-admin-table'),
                'apply_date'          => __('Apply Date', 'mos-admin-table'),
                'status'         => __('Status', 'mos-admin-table'),
        );
        return $columns;
    }

    // Bind table with columns, data and all
    function prepare_items()
    {
        //data
        if ( isset($_POST['s']) ) {
            $this->table_data = $this->get_table_data($_POST['s']);
        } else {
            $this->table_data = $this->get_table_data();
        }

        $columns = $this->get_columns();
        $hidden = ( is_array(get_user_meta( get_current_user_id(), 'managetoplevel_page_mos_list_tablecolumnshidden', true)) ) ? get_user_meta( get_current_user_id(), 'managetoplevel_page_mos_list_tablecolumnshidden', true) : array();
        $sortable = $this->get_sortable_columns();
        $primary  = 'name';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);

        usort($this->table_data, array(&$this, 'usort_reorder'));

        /* pagination */
        $per_page = $this->get_items_per_page('elements_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $per_page, // items to show on a page
                'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));
        
        $this->items = $this->table_data;
    }

    // set value for each column
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'display_name':
            case 'source':
            case 'amount':
            case 'apply_date':
            case 'status':
            default:
                return $item[$column_name];
        }
    }

    // Add a checkbox in the first column
    function column_cb($item)
    {
        return sprintf(
                '<input type="checkbox" name="element[]" value="%s" />',
                $item['ID']
        );
    }

    // Define sortable column
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'display_name'  => array('display_name', false),
            'apply_date' => array('apply_date', false),
            'status'   => array('status', true)
        );
        return $sortable_columns;
    }

    // Sorting function
    function usort_reorder($a, $b)
    {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'ID';

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    // Adding action links to column
    function column_display_name($item){
        $actions = [];
        $author_obj = get_user_by('id', $item['user_id']);       
        if ($item['status'] == 'pending') {            
            $actions['active'] = sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Approve', 'mos-admin-table') . '</a>', $_REQUEST['page'], 'active', $item['ID']);
        } 
        elseif ($item['status'] == 'active'){
            $actions['close'] = sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Close', 'mos-somity') . '</a>', $_REQUEST['page'], 'close', $item['ID']);
        } 
        elseif ($item['status'] == 'close') {
            $actions['reactive'] = sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Restore', 'mos-somity') . '</a>', $_REQUEST['page'], 'reactive', $item['ID']);
        }
        return sprintf('%1$s %2$s', $author_obj->display_name . " (ID: ".$item['user_id'].")", $this->row_actions($actions));
    }

    // To show bulk action dropdown
    function get_bulk_actions()
    {
            $actions = array(
                    'delete_all'    => __('Delete', 'mos-admin-table'),
                    'regect_all' => __('Reject', 'mos-admin-table')
            );
            return $actions;
    }

}

// Adding menu
function mos_somity_deposits_add_menu_items() {
 
	global $mos_sample_page;
 
	// add settings page
	//$mos_sample_page = add_menu_page(__('Mos List Table', 'mos-admin-table'), __('Mos List Table', 'mos-admin-table'), 'manage_options', 'mos_list_table', 'mos_list_init');
    $mos_sample_page = add_submenu_page( 
        'mos-somity', 
        'Deposits', 
        'Deposits', 
        'manage_options', 
        'mos-somity', 
        'mos_somity_options_deposits_page_html' 
    );
    //add_submenu_page('mos-somity', 'Deposits', 'Deposits', 'manage_options', 'mos-somity', 'mos_somity_options_page_html');
 
	add_action("load-$mos_sample_page", "mos_somity_deposits_screen_options");
}
add_action('admin_menu', 'mos_somity_deposits_add_menu_items');

// add screen options
function mos_somity_deposits_screen_options() {
 
	global $mos_sample_page;
    global $table;
 
	$screen = get_current_screen();
 
	// get out of here if we are not on our settings page
	if(!is_object($screen) || $screen->id != $mos_sample_page)
		return;
 
	$args = array(
		'label' => __('Elements per page', 'mos-admin-table'),
		'default' => 2,
		'option' => 'elements_per_page'
	);
	add_screen_option( 'per_page', $args );

    $table = new Mos_Somity_Deposits_List_Table();

}

add_filter('set-screen-option', 'mos_somity_deposits_table_set_option', 10, 3);
function mos_somity_deposits_table_set_option($status, $option, $value) {
    return $value;
}


// Plugin menu callback function
function mos_somity_options_deposits_page_html(){
    // Creating an instance
    $table = new Mos_Somity_Deposits_List_Table();
    ?>
    
	<div class="wrap mos-plugin-wrapper">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <?php
    echo '<form method="post">';
    // Prepare table
    $table->prepare_items();
    // Search form
    $table->search_box('search', 'search_id');
    // Display table
    $table->display();
    echo '</div></form>';
    ?>
    </div>
    <?php
}
function mos_somity_deposits_application_delete(){    
    global $wpdb;
    $mos_deposits_table = $wpdb->prefix . 'mos_deposits';
    if (isset($_GET['action']) && $_GET['page'] == "mos-somity") {
        $ID = intval($_GET['id']);
        if ($ID){
            if ($_GET['action'] == "active") {
                $wpdb->update(
                    $mos_deposits_table,
                    array(
                        'status' => 'active',
                        'approved_date' => date('Y-m-d')
                    ),
                    array( 'ID' => $ID ),
                );
            } else if ($_GET['action'] == "close") {
                $wpdb->update(
                    $mos_deposits_table,
                    array(
                        'status' => 'close',	// string
                    ),
                    array( 'ID' => $ID ),
                );
            } else if ($_GET['action'] == "reactive") {
                $wpdb->update(
                    $mos_deposits_table,
                    array(
                        'status' => 'active',	// string
                    ),
                    array( 'ID' => $ID ),
                );
            }
        }
    }
}
add_action('admin_head', 'mos_somity_deposits_application_delete');

//Popup
function mos_somity_deposits_admin_popup_content(){
    add_thickbox();
    ?>
    <div id="my-content-id" style="display:none;">
        <p>Loading...</p>
    </div>
    <?php
}
add_action('admin_footer', 'mos_somity_deposits_admin_popup_content');
