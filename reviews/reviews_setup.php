<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
add_action( 'init', 'saswp_add_reviews_menu_links',20); 

add_action( 'manage_saswp_reviews_posts_custom_column' , 'saswp_reviews_custom_columns_set', 10, 2 );
add_filter( 'manage_saswp_reviews_posts_columns', 'saswp_reviews_custom_columns' );

/**
 * Function to register reviews post type
 * since @version 1.9
 */
function saswp_add_reviews_menu_links() {
                        
        $post_type = array(
	    'labels' => array(
	        'name' 			=> esc_html__( 'Reviews', 'schema-and-structured-data-for-wp' ),	        
	        'add_new' 		=> esc_html__( 'Add Review', 'schema-and-structured-data-for-wp' ),
	        'add_new_item'  	=> esc_html__( 'Edit Review', 'schema-and-structured-data-for-wp' ),
                'edit_item'             => esc_html__( 'Edit Review','schema-and-structured-data-for-wp'),                
	    ),
      	'public' 		=> true,
      	'has_archive' 		=> false,
      	'exclude_from_search'	=> true,
    	'publicly_queryable'	=> false,
        'show_in_menu'          => 'edit.php?post_type=saswp',                
        'show_ui'               => true,
	'show_in_nav_menus'     => false,			
        'show_admin_column'     => true,        
	'rewrite'               => false,        
    );
        
    register_post_type( 'saswp_reviews', $post_type );   
                                
}


function saswp_reviews_custom_columns_set( $column, $post_id ) {
                
            switch ( $column ) {       
                
                case 'saswp_reviewer_image' :
                    
                    $name = get_post_meta( $post_id, $key='saswp_reviewer_name', true);                      
                    
                    $image_url = get_post_meta( $post_id, $key='saswp_reviewer_image', true);
                    if(!$image_url){
                        $image_url = SASWP_PLUGIN_URL.'/admin_section/images/default_user.jpg';
                    }
                    $url = admin_url( 'post.php?post='.$post_id.'&action=edit' );
                    echo '<div class="saswp-image-preview">'
                       . '<a href="'.esc_url($url).'">'
                       . '<span><img height="65" width="65" src="'.esc_url($image_url).'" alt="Reviewer"></span>'
                       . '<span><strong>'.esc_attr($name).'</strong></span>'
                       . '</a>'
                       . '</div>';
                                                            
                    break;                 
                case 'saswp_review_rating' :
                    
                    $rating_val = get_post_meta( $post_id, $key='saswp_review_rating', true);                   
                    echo saswp_get_rating_html_by_value($rating_val);                                                                                                                                       
                    
                    break;
                case 'saswp_review_platform' :
                    
                    $platform = get_post_meta( $post_id, $key='saswp_review_platform', true);                   
                    echo '<span class="saswp-g-plus"><img src="'.SASWP_PLUGIN_URL.'/admin_section/images/reviews_platform_icon/'.esc_attr($platform).'-img.png'.'"/></span>';
                                                                                                                                                            
                    break;
                case 'saswp_review_date' :
                    
                    $name = get_post_meta( $post_id, $key='saswp_review_date', true);
                    echo esc_attr($name);
                                                                                                                                                            
                    break;                
               
            }
}

function saswp_reviews_custom_columns($columns) {    
    
    unset($columns);
    
    $columns['cb']                         = '<input type="checkbox" />';
    $columns['saswp_reviewer_image']       = '<a>'.esc_html__( 'Image', 'schema-and-structured-data-for-wp' ).'<a>';
    $columns['title']                      = esc_html__( 'Title', 'schema-and-structured-data-for-wp' );    
    $columns['saswp_review_rating']        = '<a>'.esc_html__( 'Rating', 'schema-and-structured-data-for-wp' ).'<a>';    
    $columns['saswp_review_platform']      = '<a>'.esc_html__( 'Platform', 'schema-and-structured-data-for-wp' ).'<a>';    
    $columns['saswp_review_date']          = '<a>'.esc_html__( 'Review Date', 'schema-and-structured-data-for-wp' ).'<a>';        
    
    return $columns;
}

function saswp_get_rating_html_by_value($rating_val){
            
        $starating = '';
        
        $starating .= '<div class="saswp-rvw-str">';
        for($j=0; $j<5; $j++){  

              if($rating_val >$j){

                    $explod = explode('.', $rating_val);

                    if(isset($explod[1])){

                        if($j <$explod[0]){

                            $starating.='<span class="str-ic"></span>';   

                        }else{

                            $starating.='<span class="half-str"></span>';   

                        }                                           
                    }else{

                        $starating.='<span class="str-ic"></span>';    

                    }

              } else{
                    $starating.='<span class="df-clr"></span>';   
              }                                                                                                                                
            }
        $starating .= '</div>';
        
        return $starating;
        
}

/**
 * Enqueue CSS and JS
 */
function saswp_enqueue_rateyo_script( $hook ) { 
    
            
        $post_type = '';
        
        $current_screen = get_current_screen(); 
       
        if(isset($current_screen->post_type)){                  
            $post_type = $current_screen->post_type;                
        }  
                
        if($post_type =='saswp_reviews'){
            
            $rating_val = get_post_meta( get_the_ID(), $key='saswp_review_rating', true);                   
                 
            $data = array(                                    
                'rating_val'                      => $rating_val, 
                'readonly'                        => false, 
            );

            $data = apply_filters('saswp_reviews_filter',$data,'saswp_reviews_data');

            wp_register_script( 'saswp-rateyo-js', SASWP_PLUGIN_URL . 'admin_section/js/jquery.rateyo.min.js', array('jquery'), SASWP_VERSION , true );                                        
            wp_localize_script( 'saswp-rateyo-js', 'saswp_reviews_data', $data );
            wp_enqueue_script( 'saswp-rateyo-js' );

            wp_enqueue_style( 'saswp-rateyo-css', SASWP_PLUGIN_URL . 'admin_section/css/jquery.rateyo.min.css', false , SASWP_VERSION );
        
        }
                    
}
add_action( 'admin_enqueue_scripts', 'saswp_enqueue_rateyo_script' );



add_action( 'init', 'saswp_create_platform_custom_taxonomy', 21 );
 

function saswp_create_platform_custom_taxonomy() {
 
  $labels = array(
    'name' => _x( 'Platforms', 'taxonomy general name' ),
    'singular_name' => _x( 'Platform', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Types' ),
    'all_items' => __( 'All Platform' ),        
    'edit_item' => __( 'Edit Platform' ), 
    'update_item' => __( 'Update Platform' ),
    'add_new_item' => __( 'Add New Platform' ),
    'new_item_name' => __( 'New Platform Name' ),
    'menu_name' => __( 'Platforms' ),
  ); 	
 
  register_taxonomy(
    'platform',array('saswp'), 
    array(
    'hierarchical' => false,
    'labels' => $labels,
    'public' => false,   
    'show_ui' => false,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'platform' ),
  ));
  
  $term_array = array('Facebook',
                    'Google', 
                    'Zomato', 
                    'Yelp', 
                    'Tripadvisor'
                );

  foreach($term_array as $term){
    
      if(!term_exists( $term, 'platform' )){
      
        wp_insert_term(
        $term, 
        'platform', 
        array(
        'slug' => $term,
       )
      );
        
   }
      
  }  
  
}


