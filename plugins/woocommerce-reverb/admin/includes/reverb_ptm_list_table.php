<?php
if(!class_exists('WP_List_Table')) :
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;

class Reverb_PTM_List_Table extends WP_List_Table {
	protected $product_type = 'product_cat';
	
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'plural' => '',
			'singular' => '',
			'ajax' => false,
			'screen' => null,
		) );

		$this->screen = convert_to_screen( $args['screen'] );

		add_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );

		if ( !$args['plural'] )
			$args['plural'] = $this->screen->base;

		$args['plural'] = sanitize_key( $args['plural'] );
		$args['singular'] = sanitize_key( $args['singular'] );

		$this->_args = $args;

		if ( $args['ajax'] ) {
			// wp_enqueue_script( 'list-table' );
			add_action( 'admin_footer', array( $this, '_js_vars' ) );
		}

		if ( empty( $this->modes ) ) {
			$this->modes = array(
				'list'    => __( 'List View' ),
				'excerpt' => __( 'Excerpt View' )
			);
		}

		if(get_option('wc_reverb_default_product_type_mappings')){
			$this->product_type = get_option('wc_reverb_default_product_type_mappings');
		}
		 
		
	}

	function get_columns(){
		$columns = array(
			'woo_cat' => 'WooCommerce Category', 
			'reverb_cat' => 'Reverb Category',
			'shipping_profile' => 'Shipping Profile'
			);
        
        return $columns;
	}

    function get_pagenum() {
        $pagenum = get_query_var('paged') ? get_query_var('paged') : 1;
        if (isset($_REQUEST['paged'])) {
            $pagenum = $_REQUEST['paged'];
        }
        return $pagenum;
    }

	function prepare_items(){
		
		global $wpdb, $woocommerce_reverb_api;
		
		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
		$categories = get_terms( $this->product_type, array('orderby' => 'name', 'hide_empty' => false, 'search' => $search) );
	
		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

		$per_page = 20;
        $current_page = $this->get_pagenum();
        
        $total_items = count($categories);
        
        $this->set_pagination_args(array('total_items' => $total_items, 'per_page' => $per_page));
        $this->items = array_slice($categories, (($current_page - 1) * $per_page), $per_page);
	}

	public function column_woo_cat($term){
		echo $term->name;
	}

	public function column_reverb_cat( $term ) {
		$post_id = "{$term->taxonomy}_{$term->term_id}";
		$value = get_field('reverb_category', $post_id);
		
		echo '<input type="text" data-term_id="' . $term->term_id . '" value="' . $value . '" class="reverb_cat_select" />';
	}

	public function column_shipping_profile($term){
		$post_id = "{$term->taxonomy}_{$term->term_id}";
		$value = get_field('shipping_profile', $post_id);
		
		echo '<input type="text" data-term_id="' . $term->term_id . '" value="' . $value . '" class="shipping_profile_select" />';
	}

	



}
?>