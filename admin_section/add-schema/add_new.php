<?php
add_action( 'admin_menu', 'saswp_add_new_data_menu' );
add_action( 'admin_init', 'saswp_add_new_init');
add_action( 'admin_footer', 'saswp_add_new_svg_sprite');
add_action( 'wp_ajax_saswp_add_new_save_steps_data', 'saswp_add_new_save_steps_data', 10, 0 );
$saswp_add_data_type_config = array(
				'installer_dir' => 'admin_section',
				'plugin_title'  => esc_html__( ucfirst( 'Schema & Structured Data for WP' ), 'schema-and-structured-data-for-wp'),
				'start_steps' => 1,
				'total_steps' => 3,
				'installerpage' => 'saswp_add_new_data_type',
				'dev_mode' => false, 
				'steps' => array(
								1=>array(
								'title'=>esc_html__('Select Schema', 'schema-and-structured-data-for-wp'),
								'description'=>esc_html__('Where would you like to enable the schema?','schema-and-structured-data-for-wp'),
								'fields'=>saswp_add_new_schema_type_selection(),
								),
                                                                2=>array(
								'title'=>esc_html__('Placement', 'schema-and-structured-data-for-wp'),
								'description'=>esc_html__('Where would you like this to be displayed?','schema-and-structured-data-for-wp'),
								'fields'=>saswp_add_new_placement_selection(),
								),
                                                                3=>array(
									'title'=>esc_html__('Enjoy', 'schema-and-structured-data-for-wp'),
									'description'=>esc_html__('Navigate to ', 'schema-and-structured-data-for-wp'),
									'fields'=>'',
									),
								
							),
				'current_step'=>array(
							'title'=>'',
							'step_id'=>1
							)
			);
	
	function saswp_add_new_data_menu(){
		saswp_add_new_init();
                
	}

	function saswp_add_new_init(){
		// Exit if the user does not have proper permissions
		if(! current_user_can( 'manage_options' ) ) {
			return ;
		}		
		saswp_add_new_steps_call();                 
	}

	function saswp_add_new_steps_call(){
		global $saswp_add_data_type_config;
		if ( empty( $_GET['page'] ) || $saswp_add_data_type_config['installerpage'] !== $_GET['page'] ) {
			return;
		}
		 if ( ob_get_length() ) {
			ob_end_clean();
		} 
		$step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) :  $saswp_add_data_type_config['start_steps'];
		$title = $saswp_add_data_type_config['steps'][$step]['title'];
		$saswp_add_data_type_config['current_step']['step_id'] = $step;
		
		// Use minified libraries if dev mode is turned on.
		$suffix = '';
                wp_enqueue_media ();
                
                
                // Enqueue styles.
		wp_enqueue_style( 'saswp-timepicker-js', SASWP_PLUGIN_URL. $saswp_add_data_type_config['installer_dir']. '/css/jquery.timepicker' . $suffix . '.css' , array( 'wp-admin' ), '0.1');
		// Enqueue javascript.
		wp_enqueue_script( 'saswp-timepicker-css', SASWP_PLUGIN_URL. $saswp_add_data_type_config['installer_dir']. '/js/jquery.timepicker' . $suffix . '.js' , array( 'jquery-core' ), '0.1' );
                
                
		// Enqueue styles.
		wp_enqueue_style( 'saswp_add_new', SASWP_PLUGIN_URL. $saswp_add_data_type_config['installer_dir']. '/css/saswp-add-new' . $suffix . '.css' , array( 'wp-admin' ), '0.1');
		// Enqueue javascript.
		wp_enqueue_script( 'saswp_add_new', SASWP_PLUGIN_URL. $saswp_add_data_type_config['installer_dir']. '/js/saswp-add-new' . $suffix . '.js' , array( 'jquery-core' ), '0.1' );
		//wp_enqueue_script( 'saswp_install_script', SASWP_PLUGIN_URL. '/admin_section/js/main-script.js' , array( 'jquery-core' ), '0.1' );
                
                wp_enqueue_script( 'structure_admin', SASWP_PLUGIN_URL. $saswp_add_data_type_config['installer_dir']. '/js/structure_admin' . $suffix . '.js' , array( 'jquery' ), '0.1' );
		
                wp_localize_script( 'structure_admin', 'saswp_app_object', array(
			'ajax_url'      		=> admin_url( 'admin-ajax.php' ),						
		) );
                
                
		wp_localize_script( 'saswp_add_new', 'saswp_add_new_params', array(
			'ajaxurl'      		=> admin_url( 'admin-ajax.php' ),
			'wpnonce'      		=> wp_create_nonce( 'saswp_add_new_nonce' ),
			'pluginurl'		=> SASWP_DIR_URI,
		) );
		

		ob_start();
		saswp_add_new_header(); ?>
		<div class="merlin__wrapper">
            <div class="saswp_install_wizard"><?php esc_html_e('ADD NEW SCHEMA','schema-and-structured-data-for-wp'); ?></div>
			<div class="merlin__content merlin__content--<?php echo esc_attr( strtolower( $title ) ); ?>">
				<?php
				// Content Handlers.
				$show_content = true;

				if ( ! empty( $_REQUEST['save_step'] ) && isset( $saswp_add_data_type_config['current_step']['steps'] ) ) {
					//saswp_save_steps_data();
				}

				if ( $show_content ) {
					saswp_add_new_show_steps_body();
				} ?>

			<?php saswp_add_new_step_output_bottom_dots(); ?>

			</div>

			<?php echo sprintf( '<a class="return-to-dashboard" href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=saswp' ) ), esc_html__( 'Return to dashboard', 'schema-and-structured-data-for-wp' ) ); ?>

		</div>

		<?php saswp_add_new_install_footer(); 
		exit;
	}
	
	function saswp_add_new_show_steps_body(){
		global $saswp_add_data_type_config;
		if($saswp_add_data_type_config['total_steps']==$saswp_add_data_type_config['current_step']['step_id']){
			call_user_func('saswp_add_new_finish_page');
		}else{
			if(function_exists('saswp_add_new_step'.$saswp_add_data_type_config['current_step']['step_id'])){
				call_user_func('saswp_add_new_step'.$saswp_add_data_type_config['current_step']['step_id']);
			}else{
				call_user_func('saswp_add_new_finish_page');
			}
		}
	}
	function saswp_add_new_header() {
		global $saswp_installer_config;
		
		// Get the current step.
		$current_step = strtolower( $saswp_installer_config['steps'][$saswp_installer_config['current_step']['step_id']]['title'] ); ?>

		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title><?php echo esc_attr(ucwords($current_step)); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="merlin__body merlin__body--<?php echo esc_attr( $current_step ); ?>">
		<?php
	}
	
	
	
	function saswp_add_new_step1(){
		global $saswp_add_data_type_config;
		$stepDetails = $saswp_add_data_type_config['steps'][$saswp_add_data_type_config['current_step']['step_id']];
		?>

		<div class="merlin__content--transition">

			<div class="saswp_branding"></div>
			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>
			
			<h1><?php echo esc_attr($stepDetails['title']); ?></h1>

			<p><?php echo isset($stepDetails['description'])? $stepDetails['description'] : ''; ?></p>
			
			
			
		</div>
		<form action="" method="post">
			
			<ul class="merlin__drawer--import-content">
                            
                            <li>
                             <?php 
                                     $post =array();
                                     echo saswp_schema_type_meta_box_callback($post);
					
				?>   
                            </li>
				
			</ul>
			

			<footer class="merlin__content__footer">
				<?php saswp_add_new_skip_button(); ?>
				
				<a id="skip" href="<?php echo esc_url( saswp_add_new_step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__( 'Skip', 'schema-and-structured-data-for-wp' ); ?></a>
				
				<a href="<?php echo esc_url( saswp_add_new_step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_logo">
					<span class="merlin__button--loading__text"><?php echo esc_html__( 'Next', 'schema-and-structured-data-for-wp' ); ?></span><?php echo saswp_add_new_loading_spinner(); ?>
				</a>
				
				<?php wp_nonce_field( 'saswp_add_new_nonce' ); ?>
			</footer>
		</form>
	<?php
	}
        
        function saswp_add_new_step2(){
		global $saswp_add_data_type_config;
		$stepDetails = $saswp_add_data_type_config['steps'][$saswp_add_data_type_config['current_step']['step_id']];
		?>

		<div class="merlin__content--transition">

			<div class="saswp_branding"></div>
			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>
			
			<h1><?php echo esc_attr($stepDetails['title']); ?></h1>
			<p><?php echo isset($stepDetails['description'])? $stepDetails['description'] : ''; ?></p>
		</div>
		<form action="" method="post">
			
                    <div id="saswp_amp_select" class="postbox">
                        <ul class="merlin__drawer--import-content">
                            
                            <li>
                    
				<?php 
                                        $last_post_id ='';
                                        if(isset($_GET['step'])){
                                        $step =     $_GET['step']; 
                                        
                                        if($step == 2){
                                        $last_post_id = json_decode(get_transient('saswp_last_post_id'), true); 
                                        $last_post_id =  $last_post_id['post_id'];       
                                        }                                        
                                        $post = get_post($last_post_id);
                                        if($post){
                                         echo saswp_select_callback($post);                                               
                                        }
                                        }
				?>   
                             
                            </li>
                            <li>
                                 <input type="hidden" name="saswp_post_id" id="saswp_post_id" value="<?php echo esc_attr($last_post_id); ?>">   
                            </li>
			</ul>
                        </div>
                   

			<footer class="merlin__content__footer">
				<?php saswp_add_new_skip_button(); ?>
				
				<a id="skip" href="<?php echo esc_url( saswp_add_new_step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__( 'Skip', 'schema-and-structured-data-for-wp' ); ?></a>
				
				<a href="<?php echo esc_url( saswp_add_new_step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_logo">
					<span class="merlin__button--loading__text"><?php echo esc_html__( 'Next', 'schema-and-structured-data-for-wp' ); ?></span><?php echo saswp_add_new_loading_spinner(); ?>
				</a>
				
				<?php wp_nonce_field( 'saswp_add_new_nonce' ); ?>
			</footer>
		</form>
	<?php
	}
	
	
        function saswp_add_new_step3(){
		global $saswp_add_data_type_config;
		$stepDetails = $saswp_add_data_type_config['steps'][$saswp_add_data_type_config['current_step']['step_id']];
		?>

		<div class="merlin__content--transition">

			<div class="saswp_branding"></div>
			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>
			
			<h1><?php echo esc_attr($stepDetails['title']); ?></h1>

			<p><?php echo isset($stepDetails['description'])? $stepDetails['description'] : ''; ?></p>
			
			
			
		</div>
		<form action="" method="post">
			
			<ul class="merlin__drawer--import-content">
				<?php 
					echo $stepDetails['fields'];
				?>
			</ul>
			

			<footer class="merlin__content__footer">
				<?php saswp_add_new_skip_button(); ?>
				
				<a id="skip" href="<?php echo esc_url( saswp_add_new_step_next_link() ); ?>" class="merlin__button merlin__button--skip merlin__button--proceed"><?php echo esc_html__( 'Skip', 'schema-and-structured-data-for-wp' ); ?></a>
				
				<a href="<?php echo esc_url( saswp_add_new_step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="save_logo">
					<span class="merlin__button--loading__text"><?php echo esc_html__( 'Next', 'schema-and-structured-data-for-wp' ); ?></span><?php echo saswp_add_new_loading_spinner(); ?>
				</a>
				
				<?php wp_nonce_field( 'saswp_add_new_nonce' ); ?>
			</footer>
		</form>
	<?php
	}

       

		
	function saswp_add_new_save_steps_data(){    
                 if ( ! isset( $_POST['wpnonce'] ) ){
                 return; 
                 }
                 if ( !wp_verify_nonce( $_POST['wpnonce'], 'saswp_add_new_nonce' ) ){
                    return;  
                 }                 
                if(isset($_POST['schema_type'])){                    
                $schema_type = $_POST['schema_type'];    
                $user_id = get_current_user_id();
                $schema_post = array(
                    'post_author' => $user_id,
                    'post_date' => date("Y-m-d"),                                        
                    'post_title' => ucfirst($schema_type),                    
                    'post_status' => 'publish',                    
                    'post_name' =>  ucfirst($schema_type),                    
                    'post_type' => 'saswp',                                                            
                );                                      
                $post_id = wp_insert_post($schema_post);                                  
                      update_post_meta( $post_id, 'schema_type', esc_attr( $schema_type ) );
                
                if ( isset( $_POST['saswp_business_type'] ) ){
                     update_post_meta( $post_id, 'saswp_business_type', esc_attr( $_POST['saswp_business_type'] ) );    
                }                    
                if ( isset( $_POST['saswp_business_name'] ) ){
                     update_post_meta( $post_id, 'saswp_business_name', esc_attr( $_POST['saswp_business_name'] ) );   
                }                    
                set_transient('saswp_last_post_id', json_encode(array('post_id'=>$post_id))); 
                }    
                                
                if(isset($_POST['data_group_array']) && isset($_POST['saswp_post_id'])){
                $post_id = sanitize_text_field($_POST['saswp_post_id']);    
                $post_data_group_array = array();
                $temp_condition_array  = array();
                $show_globally =false;
                $post_data_group_array = $_POST['data_group_array'];
                foreach($post_data_group_array as $groups){        
                    foreach($groups['data_array'] as $group ){              
                      if(array_search('show_globally', $group))
                      {
                        $temp_condition_array[0] =  $group;  
                        $show_globally = true;              
                      }
                    }
                }
                if($show_globally){
                unset($post_data_group_array);
                $post_data_group_array['group-0']['data_array'] = $temp_condition_array;                 
                }                                
                update_post_meta(
                    $post_id, 
                    'data_group_array', 
                    $post_data_group_array 
                  );                         
                }                
		wp_send_json(
			array(
				'done' => 1,
				'message' => "Stored Successfully",
                                'post_id' => $post_id
			)
		);
                
	}
	
	
	function saswp_add_new_skip_button(){
		?>
		<a href="<?php echo esc_url(  saswp_add_new_step_next_link() ); ?>" class="merlin__button merlin__button--skip"><?php echo esc_html__( 'Skip', 'schema-and-structured-data-for-wp' ); ?></a>
		<?php
	}
	function saswp_add_new_finish_page() {
		global $saswp_add_data_type_config;
		// Theme Name.
		$plugin_title 					= $saswp_add_data_type_config['plugin_title'];
		// Strings passed in from the config file.
		$strings = null;

		
		$allowed_html_array = array(
			'a' => array(
				'href' 		=> array(),
				'title' 	=> array(),
				'target' 	=> array(),
			),
		);

		update_option( 'saswp_installer_completed', time() ); ?>

		<div class="merlin__content--transition">

			<div class="saswp_branding"></div>
			
			<h1><?php echo esc_html( 'Schema Added Successfully. Have fun!' ); ?></h1>		

		</div> 

		<footer class="merlin__content__footer merlin__content__footer--fullwidth">
			
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=saswp' ) ); ?>" class="merlin__button merlin__button--blue merlin__button--fullwidth merlin__button--popin"><?php echo esc_html( 'Let\'s Go' ); ?></a>
			
			
			<ul class="merlin__drawer merlin__drawer--extras">

				<li><?php //echo wp_kses( $link_1, $allowed_html_array ); ?></li>
				<li><?php //echo wp_kses( $link_2, $allowed_html_array ); ?></li>
				<li><?php //echo wp_kses( $link_3, $allowed_html_array ); ?></li>

			</ul>

		</footer>

	<?php
	}

	function saswp_add_new_loading_spinner(){
		global $saswp_add_data_type_config;
		$spinner = SASWP_DIR_NAME. $saswp_add_data_type_config['installer_dir']. '/images/spinner.php';

		// Retrieve the spinner.
		get_template_part(  $spinner );
	}
	
	function saswp_add_new_svg_sprite() {
		global $saswp_add_data_type_config;
		// Define SVG sprite file.
		$svg = SASWP_DIR_NAME. $saswp_add_data_type_config['installer_dir'] . '/images/sprite.svg' ;

		// If it exists, include it.
		if ( file_exists( $svg ) ) {
			require_once apply_filters( 'merlin_svg_sprite', $svg );
		}
	}
	function saswp_add_new_step_next_link() {
            
		global $saswp_add_data_type_config;
                
		$step = $saswp_add_data_type_config['current_step']['step_id'] + 1;
                
                $query_string = add_query_arg( 'step', $step ); 
                
                return $query_string;                                        
	}
	
	function saswp_add_new_install_header() {
		global $saswp_add_data_type_config;
		
		// Get the current step.
		$current_step = strtolower( $saswp_add_data_type_config['steps'][$saswp_add_data_type_config['current_step']['step_id']]['title'] ); ?>

		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title><?php echo esc_attr(ucwords($current_step)); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="merlin__body merlin__body--<?php echo esc_attr( $current_step ); ?>">
		<?php
	}
	
	
	function saswp_add_new_install_footer() {
		?>	 
		</body>
		<?php do_action( 'admin_footer' ); ?>
		<?php do_action( 'admin_print_footer_scripts' ); ?>
		</html>
		<?php
	}
	
	function saswp_add_new_makesvg( $args = array() ){
		// Make sure $args are an array.
		if ( empty( $args ) ) {
			return __( 'Please define default parameters in the form of an array.', 'accelerated-mobile-pages' );
		}

		// Define an icon.
		if ( false === array_key_exists( 'icon', $args ) ) {
			return __( 'Please define an SVG icon filename.', 'accelerated-mobile-pages' );
		}

		// Set defaults.
		$defaults = array(
			'icon'        => '',
			'title'       => '',
			'desc'        => '',
			'aria_hidden' => true, // Hide from screen readers.
			'fallback'    => false,
		);

		// Parse args.
		$args = wp_parse_args( $args, $defaults );

		// Set aria hidden.
		$aria_hidden = '';

		if ( true === $args['aria_hidden'] ) {
			$aria_hidden = ' aria-hidden="true"';
		}

		// Set ARIA.
		$aria_labelledby = '';

		if ( $args['title'] && $args['desc'] ) {
			$aria_labelledby = ' aria-labelledby="title desc"';
		}

		// Begin SVG markup.
		$svg = '<svg class="icon icon--' . esc_attr( $args['icon'] ) . '"' . $aria_hidden . $aria_labelledby . ' role="img">';

		// If there is a title, display it.
		if ( $args['title'] ) {
			$svg .= '<title>' . esc_html( $args['title'] ) . '</title>';
		}

		// If there is a description, display it.
		if ( $args['desc'] ) {
			$svg .= '<desc>' . esc_html( $args['desc'] ) . '</desc>';
		}

		$svg .= '<use xlink:href="#icon-' . esc_html( $args['icon'] ) . '"></use>';

		// Add some markup to use as a fallback for browsers that do not support SVGs.
		if ( $args['fallback'] ) {
			$svg .= '<span class="svg-fallback icon--' . esc_attr( $args['icon'] ) . '"></span>';
		}

		$svg .= '</svg>';

		return $svg;
	
	}
	
	/**
	 * Adds data attributes to the body, based on Customizer entries.
	 */
	function saswp_add_new_svg_allowed_html() {

		$array = array(
			'svg' => array(
				'class' => array(),
				'aria-hidden' => array(),
				'role' => array(),
			),
			'use' => array(
				'xlink:href' => array(),
			),
		);

		return $array;

	}
	
	function saswp_add_new_step_output_bottom_dots(){
		global $saswp_add_data_type_config;
		?>
		<ol class="dots">

			<?php for( $i = 1; $i<$saswp_add_data_type_config['total_steps']; $i++ ) :

				$class_attr = '';
				$show_link = false;

				if ( $i === $saswp_add_data_type_config['current_step']['step_id'] ) {
					$class_attr = 'active';
				} elseif ( $saswp_add_data_type_config['current_step']['step_id'] >  $i) {
					$class_attr = 'done';
					$show_link = true;
				} ?>

				<li class="<?php echo esc_attr( $class_attr ); ?>">
					<a href="<?php echo esc_url( add_query_arg( 'step', $i ) ); ?>" title="<?php echo esc_attr( $saswp_add_data_type_config['current_step']['title'] ); ?>"></a>
				</li>

			<?php endfor; ?>

		</ol>
		<?php
	}


        function saswp_add_new_schema_type_selection(){
               
        }
        function saswp_add_new_placement_selection(){
               
        }
        

?>