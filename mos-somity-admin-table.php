<?php

// Loading WP_List_Table class file
// We need to load it as it's not automatically loaded by WordPress
if (!class_exists('WP_List_Table')) {
      require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Extending class
class Mos_Somity_List_Table extends WP_List_Table
{
    // Here we will add our code

    // define $table_data property
    private $table_data;

    // Get table data
    private function get_table_data( $search = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'mos_skim_user';

        if ( !empty($search) ) {
            return $wpdb->get_results(
                "SELECT * from {$table} WHERE 'user_id' Like '%{$search}%' OR 'apply_date' Like '%{$search}%' OR 'status' Like '%{$search}%'",
                ARRAY_A
            );
        } else {
            return $wpdb->get_results(
                "SELECT * from {$table}",
                ARRAY_A
            );
        }
    }

    // Define table columns
    function get_columns()
    {
        $columns = array(
                'cb'            => '<input type="checkbox" />',
                'user_id'          => __('User ID', 'mos-admin-table'),
                'skim_details'          => __('Details', 'mos-admin-table'),
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
            case 'id':
            case 'user_id':
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
            'user_id'  => array('user_id', false),
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
    function column_skim_details($item)
    {
        $actions = [];
        $output = '';
        if ($item['skim_details'] ) {
            $skim_details = json_decode($item['skim_details'], true);
            $output = $skim_details['title'] . "<br/>" .$skim_details['amount'];
        }
        // if ($item['p_cv']) {
        //     $actions['cv'] = '<a class="mos-action mos-action-cv" data-cv="'.$item['p_cv'].'" href="?post_type=job&page='.$_REQUEST['page'].'&action=cv&element='.$item['ID'].'">' . __('View CV', 'mos-admin-table') . '</a>';
            
        // }
        // if ($item['p_cover_letter']) {
        //     $actions['cover-letter'] = '<a class="mos-action mos-action-data thickbox" data-html="'.$item['p_cover_letter'].'" href="#TB_inline?&width=600&height=550&inlineId=my-content-id">' . __('Cover Letter', 'mos-admin-table') . '</a>';
        // }
        // if ($item['p_additional_info']) {
        //     $actions['additional-info'] = '<a class="mos-action mos-action-data thickbox" data-html="'.$p_additional_info_html.'" href="#TB_inline?&width=600&height=550&inlineId=my-content-id">' . __('Additional Info', 'mos-admin-table') . '</a>';
        // }
        /*$actions = array(
                
                'cv'      => '<a class="mos-action mos-action-cv" data-cv="'.$item['p_cv'].'" href="?post_type=job&page='.$_REQUEST['page'].'&action=cv&element='.$item['ID'].'">' . __('View CV', 'mos-admin-table') . '</a>',
                
                'cover-letter'      => '<a class="mos-action mos-action-data thickbox" data-html="'.$item['p_cover_letter'].'" href="#TB_inline?&width=600&height=550&inlineId=my-content-id">' . __('Cover Letter', 'mos-admin-table') . '</a>',
                
                'additional-info'      => '<a class="mos-action mos-action-data thickbox" data-html="'.$p_additional_info_html.'" href="#TB_inline?&width=600&height=550&inlineId=my-content-id">' . __('Additional Info', 'mos-admin-table') . '</a>',
                
                'delete'    => sprintf('<a href="?post_type=job&page=%s&action=%s&element=%s">' . __('Delete', 'mos-admin-table') . '</a>', $_REQUEST['page'], 'delete', $item['ID']),
        );*/
        $actions['delete'] = sprintf('<a href="?post_type=job&page=%s&action=%s&element=%s">' . __('Delete', 'mos-admin-table') . '</a>', $_REQUEST['page'], 'delete', $item['ID']);
        return sprintf('%1$s %2$s', $output, $this->row_actions($actions));
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
function my_add_menu_items() {
 
	global $mos_sample_page;
 
	// add settings page
	//$mos_sample_page = add_menu_page(__('Mos List Table', 'mos-admin-table'), __('Mos List Table', 'mos-admin-table'), 'manage_options', 'mos_list_table', 'mos_list_init');
    $mos_sample_page = add_submenu_page( 
        'mos-somity', 
        'Table', 
        'Table', 
        'manage_options', 
        'job-applications', 
        'mos_list_init' 
    );
 
	add_action("load-$mos_sample_page", "mos_somity_screen_options");
}
add_action('admin_menu', 'my_add_menu_items');

// add screen options
function mos_somity_screen_options() {
 
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

    $table = new Mos_Somity_List_Table();

}

add_filter('set-screen-option', 'test_table_set_option', 10, 3);
function test_table_set_option($status, $option, $value) {
    return $value;
}


// Plugin menu callback function
function mos_list_init(){
    // Creating an instance
    $table = new Mos_Somity_List_Table();
    echo '<div class="wrap"><h2>Mos Admin Table</h2>';
    echo '<form method="post">';
    // Prepare table
    $table->prepare_items();
    // Search form
    $table->search_box('search', 'search_id');
    // Display table
    $table->display();
    echo '</div></form>';
}
function mos_application_delete(){
    // delete
    //http://alpha-bd.test/wp-admin/edit.php?page=job-applications&action=delete&element=3
    if (isset($_GET['action']) && $_GET['page'] == "job-applications" && $_GET['action'] == "delete") {
        global $wpdb;
        $elementID = intval($_GET['element']);
        if ($elementID)
        $wpdb->delete( $wpdb->prefix . 'mos_skim_user', array( 'ID' => $elementID ) );
        //... do operation
    }
}
add_action('admin_head', 'mos_application_delete');

//Popup
function mos_admin_popup_content(){
    add_thickbox();
    ?>
    <div id="my-content-id" style="display:none;">
        <p>Loading...</p>
    </div>
    <?php
}
add_action('admin_footer', 'mos_admin_popup_content');
