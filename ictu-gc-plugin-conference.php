<?php

/**
 * @link                https://wbvb.nl
 * @package             ictu-gc-posttypes-inclusie
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Conference post types and taxonomies
 * Plugin URI:          https://github.com/ICTU/Gebruiker-Centraal---Inclusie---custom-post-types-taxonomies
 * Description:         Plugin for conference.gebruikercentraal.nl to register custom post types and custom taxonomies
 * Version:             0.0.1
 * Version description: Eerste opzet.
 * Author:              Paul van Buuren
 * Author URI:          https://wbvb.nl/
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         ictu-gc-posttypes-inclusie
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//========================================================================================================

add_action( 'plugins_loaded', array( 'ICTU_GC_Conf_posttypes', 'init' ), 10 );

//========================================================================================================

if ( ! defined( 'ICTU_GCCONF_CPT_SPEAKER' ) ) {
  define( 'ICTU_GCCONF_CPT_SPEAKER', 'speaker' );   // slug for custom taxonomy 'document'
}

if ( ! defined( 'ICTU_GCCONF_CPT_SESSION' ) ) {
  define( 'ICTU_GCCONF_CPT_SESSION', 'session' );   // slug for custom taxonomy 'citaat'
}

if ( ! defined( 'ICTU_GCCONF_CPT_KEYNOTE' ) ) {
  define( 'ICTU_GCCONF_CPT_KEYNOTE', 'keynote' );  // slug for custom post type 'keynote'
}

if ( ! defined( 'ICTU_GCCONF_CT_TIMESLOT' ) ) {
  define( 'ICTU_GCCONF_CT_TIMESLOT', 'timeslot' );  // slug for custom taxonomy 'tijd'
}

if ( ! defined( 'ICTU_GCCONF_CT_LOCATION' ) ) {
  define( 'ICTU_GCCONF_CT_LOCATION', 'location' );  // slug for custom taxonomy 'mankracht'
}

if ( ! defined( 'ICTU_GCCONF_CT_SESSIONTYPE' ) ) {
  define( 'ICTU_GCCONF_CT_SESSIONTYPE', 'sessiontype' );  // slug for custom taxonomy 'mankracht'
}

if ( ! defined( 'ICTU_GCCONF_CT_LEVEL' ) ) {
  define( 'ICTU_GCCONF_CT_LEVEL', 'expertise' );  // slug for custom taxonomy 'mankracht'
}





define( 'ICTU_GC_ARCHIVE_CSS',		'ictu-gc-header-css' );  
define( 'ICTU_GC_FOLDER',           'do-stelselplaat' );
define( 'ICTU_GC_BASE_URL',         trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'ICTU_GC_ASSETS_URL',		trailingslashit( ICTU_GC_BASE_URL ) );
define( 'ICTU_GC_INCL_VERSION',		'0.0.1' );

//========================================================================================================

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */


if ( ! class_exists( 'ICTU_GC_Conf_posttypes' ) ) :

  class ICTU_GC_Conf_posttypes {
  
    /**
     * @var Conference plugin
     */
    public $conferenceobject = null;
  
    /** ----------------------------------------------------------------------------------------------------
     * Init
     */
    public static function init() {
  
      $conferenceobject = new self();
  
    }
  
    /** ----------------------------------------------------------------------------------------------------
     * Constructor
     */
    public function __construct() {

      $this->includes();
      $this->setup_actions();

    }
  
    /** ----------------------------------------------------------------------------------------------------
     * Hook this plugins functions into WordPress
     */
	private function includes() {
		
      require_once dirname( __FILE__ ) . '/includes/conference-acf-definitions.php';

    }
  
    /** ----------------------------------------------------------------------------------------------------
     * Hook this plugins functions into WordPress
     */
	private function setup_actions() {
		
		add_action( 'init', array( $this, 'ictu_gcconf_register_post_types' ) );
		add_action( 'init', 'ictu_gcconf_initialize_acf_fields' );

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'ictu_gc_add_rewrite_rules' ) );

//		add_filter( 'genesis_single_crumb',   array( $this, 'filter_breadcrumb' ), 10, 2 );
//		add_filter( 'genesis_page_crumb',     array( $this, 'filter_breadcrumb' ), 10, 2 );
//		add_filter( 'genesis_archive_crumb',  array( $this, 'filter_breadcrumb' ), 10, 2 ); 				
		
		
		add_action( 'genesis_entry_content',  array( $this, 'prepend_content' ), 8 ); 				
		add_action( 'genesis_entry_content',  array( $this, 'append_content' ), 15 ); 			
		
		// add a page temlate name
		$this->templates 						= array();
		$this->template_conf_overviewpage 		= 'conf-overviewpage.php';
		
		// add the page template to the templates list
		add_filter( 'theme_page_templates',   array( $this, 'ictu_gcconf_add_page_templates' ) );
		
		// activate the page filters
		add_action( 'template_redirect',      array( $this, 'ictu_gcconf_frontend_use_page_template' )  );
		
		// add styling and scripts
		add_action( 'wp_enqueue_scripts',     array( $this, 'ictu_gcconf_register_frontend_style_script' ) );

	}
    
    /** ----------------------------------------------------------------------------------------------------
     * Initialise translations
     */
	public function load_plugin_textdomain() {
		
		load_plugin_textdomain( 'ictu-gcconf-posttypes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
	}


	//========================================================================================================
	
	/**
	* Hides the custom post template for pages on WordPress 4.6 and older
	*
	* @param array $post_templates Array of page templates. Keys are filenames, values are translated names.
	* @return array Expanded array of page templates.
	*/
	function ictu_gcconf_add_page_templates( $post_templates ) {
	
		$post_templates[$this->template_conf_overviewpage]				= _x( 'Conf. Overview page', "naam template",  'ictu-gcconf-posttypes' );    
		return $post_templates;
		
	}

    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
     
	public function ictu_gc_frontend_home_after_content() {

		global $post;
		

			if( have_rows('blocks') ) {
				
				// loop through the rows of data
				while ( have_rows('blocks') ) : the_row();

					$section_title 		= get_sub_field( 'block_title' );
					$block_title_id		= get_sub_field( 'block_title_id' );
					$block_free_text	= get_sub_field( 'block_free_text' );
					$block_extra_type	= get_sub_field( 'block_extra_type' );
					
					if ( $block_title_id ) { 
						$title_id		= sanitize_title( $block_title_id );
					}
					else {
						$title_id		= sanitize_title( $section_title );
					}

					echo '<section aria-labelledby="' . $title_id . '" class="section-block">';
					echo '<h2 id="' . $title_id . '">' . $section_title . '</h2>';
					if ( $block_free_text ) {
						echo $block_free_text;
					}
					
//					echo '<p><strong>ID: ' . $title_id . '</strong></p>';
//					echo '<p><strong>Type block: ' . $block_extra_type . '</strong></p>';

					if ( 'sessions' === $block_extra_type ) {
						
						//$posts = get_field('relationship_field_name');	
						
						//	wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest o
						
						if( have_rows('block_sessions') ):
						
							// loop through rows (sub repeater)
							while( have_rows('block_sessions') ): the_row();
							
								$block_sessions_session 			= get_sub_field( 'block_sessions_session' );
								$block_sessions_session_time 		= get_sub_field( 'block_sessions_session_time' );
								$block_sessions_session_location	= get_sub_field( 'block_sessions_session_location' );

								$time_term 							= get_term_by( 'id', $block_sessions_session_time, ICTU_GCCONF_CT_TIMESLOT );
								$location_term						= get_term_by( 'id', $block_sessions_session_location, ICTU_GCCONF_CT_LOCATION );

								$speakers 							= get_field('speakers', $block_sessions_session->ID );
								$section_title 						= get_the_title( $block_sessions_session->ID );
								$title_id							= sanitize_title( $section_title );

								echo '<div aria-labelledby="' . $title_id . '">';
								echo '<h3 id="' . $title_id . '"><a href="' . get_permalink( $block_sessions_session->ID ) . '">' . $section_title . '</a></h3>';
								

								if ( $time_term ) {
									echo '<br><strong>' . $time_term->name . '</strong>';	
								}	
								if ( $location_term ) {
									echo ' <strong>' . $location_term->name . '</strong>';		
								}	
								if ( $speakers ) {
									foreach( $speakers as $speaker ):
										echo ' <a href="'. get_permalink( $speaker->ID ) . '">' . get_the_post_thumbnail( $speaker->ID, 'thumbnail' ) . " " . get_the_title( $speaker->ID ) . '</a>';		
									endforeach;
								}

								echo '</div>';		
							
							endwhile;
						
						endif;
					
					}


					echo '</section>';

				endwhile;
				
			}

	}


    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
	public function ictu_gc_frontend_keynote_single_content() {

		global $post;
		
		echo '<p>TEST : ictu_gc_frontend_keynote_single_content </p>';
		
	}


    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
	public function ictu_gc_frontend_keynote_before_content() {
		
		global $post;

		echo '<p>TEST : ictu_gc_frontend_keynote_before_content </p>';

	}


    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
	public function ictu_gc_frontend_stap_before_content() {

		global $post;

		echo '<p>TEST : ictu_gc_frontend_stap_before_content </p>';
		
	}


    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
	public function ictu_gc_frontend_home_before_content() {

		global $post;

		echo '<p>TEST : ictu_gc_frontend_home_before_content </p>';
		
	}    


    //========================================================================================================

    /**
     * Register frontend styles
     */
	public function ictu_gcconf_register_frontend_style_script( ) {
	
		global $post;
	
		$infooter = true;
		
		wp_enqueue_style( ICTU_GC_ARCHIVE_CSS, trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/frontend-conf.css', array(), ICTU_GC_INCL_VERSION, 'all' );
		
		$header_css     = '';
		$acfid          = get_the_id();
		$page_template  = get_post_meta( $acfid, '_wp_page_template', true );
		
		if ( !is_admin() && ( $this->template_conf_overviewpage == $page_template ) ) {
			
			if( have_rows('home_template_keynotes') ):

				
			endif; 
			
		}


		if ( $header_css ) {
			wp_add_inline_style( ICTU_GC_ARCHIVE_CSS, $header_css );
		}

		
    }

    //========================================================================================================

    /**
    * Modify page content if using a specific page template.
    */
	public function ictu_gcconf_frontend_use_page_template() {
		
		global $post;
		
		$page_template  = get_post_meta( get_the_ID(), '_wp_page_template', true );
		
		if ( $this->template_conf_overviewpage == $page_template ) {
			
			remove_filter( 'genesis_post_title_output', 'gc_wbvb_sharebuttons_for_page_top', 15 );
			
			//* Remove standard header
//			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
//			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
//			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

			//* Remove the post content (requires HTML5 theme support)
//			remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
			
			// append content
//			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_home_before_content' ), 8 ); 				
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_home_after_content' ), 12 ); 				
			
			
		}
		elseif ( ICTU_GCCONF_CPT_SPEAKER == get_post_type( ) )  {

			//* Remove standard header
//			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
//			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
//			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

//			add_action( 'genesis_before_loop',  array( $this, 'ictu_gc_frontend_stap_before_content' ), 8 ); 				

//			add_action( 'genesis_entry_header',  array( $this, 'ictu_gc_frontend_stap_append_title' ), 10 ); 				

			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_speaker_featured_image' ), 9 ); 				
	
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {

			//* Remove standard header
			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

			add_action( 'genesis_before_entry',  array( $this, 'ictu_gc_frontend_keynote_before_content' ), 8 ); 				

			
							
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_append_title' ), 9 ); 				

			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_append_speakers' ), 15 ); 			
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_append_vaardigheden' ), 17 ); 			


			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_sessionkeynot_speakers' ), 20 ); 				
	
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {

			//* Remove standard header
			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

			add_action( 'genesis_before_entry',  array( $this, 'ictu_gc_frontend_keynote_before_content' ), 8 ); 				

							
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_append_title' ), 9 ); 				

			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_append_speakers' ), 15 ); 			
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_append_vaardigheden' ), 17 ); 			


			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_sessionkeynot_speakers' ), 20 ); 				
	
		}


		//=================================================
		
		add_filter( 'genesis_post_info',   array( $this, 'filter_postinfo' ), 10, 2 );
		
	}

    /** ----------------------------------------------------------------------------------------------------
     * Append vaardigheden to keynote single
     */
	public function ictu_gcconf_frontend_append_speakers() {
	
		global $post;
	
	    $keynote_tips 			= get_field( 'keynote_tips', $post->ID );
	    $keynote_tips_titel		= get_field( 'keynote_tips_titel', $post->ID );
	    $keynote_tips_inleiding	= get_field( 'keynote_tips_inleiding', $post->ID );

		$section_title = ( $keynote_tips_titel ) ? $keynote_tips_titel : _x( 'Tips', 'titel op keynote-pagina', 'ictu-gcconf-posttypes' );
		$title_id       = sanitize_title( $section_title . '-' . $post->ID );
	
	    if( $keynote_tips ):
//	      endforeach;

	      $title_id       = sanitize_title( $section_title . '-' . $post->ID );
	    
	      echo '<h2 id="' . $title_id . '" class="">' . $section_title . '</h2>';

	      if ( $keynote_tips_inleiding ) {
		      echo '<p>' . $keynote_tips_inleiding . '</p>';
	      }

	      // loop through the rows of data
	      foreach( $keynote_tips as $post ):
	
	        setup_postdata( $post );
	
	        $theid 			= $post->ID;
	        $section_title  = get_the_title( $theid );
	        $section_text   = get_the_excerpt( $theid );
	        $title_id       = sanitize_title( $section_title );

	        echo '<section aria-labelledby="' . $title_id . '" class="vaardigheid">';
			echo '<h3 id="' . $title_id . '"><a href="' . get_permalink( $theid ) . '">' . $section_title . '</a></h3>';
	        echo $section_text;
	        echo '</section>';


	      endforeach;
	      
	      wp_reset_postdata();          
	      
	    endif;
		
	}

    /** ----------------------------------------------------------------------------------------------------
     * Append vaardigheden to keynote single
     */
	public function ictu_gc_frontend_keynote_append_vaardigheden() {
		
		global $post;
	
	    $keynote_vaardigheden 			= get_field( 'keynote_vaardigheden', $post->ID );
	    $keynote_vaardigheden_titel 		= get_field( 'keynote_vaardigheden_titel', $post->ID );
	    $keynote_vaardigheden_inleiding	= get_field( 'keynote_vaardigheden_inleiding', $post->ID );
	
	    if( $keynote_vaardigheden ):
	
	      $section_title = ( $keynote_vaardigheden_titel ) ? $keynote_vaardigheden_titel : _x( 'Vaardigheden', 'titel op keynote-pagina', 'ictu-gcconf-posttypes' );
	      $title_id       = sanitize_title( $section_title . '-' . $post->ID );
	    
	      echo '<h2 id="' . $title_id . '" class="">' . $section_title . '</h2>';
	      
	      if ( $keynote_vaardigheden_inleiding ) {
		      echo '<p>' . $keynote_vaardigheden_inleiding . '</p>';
	      }
	    
	      // loop through the rows of data
	      foreach( $keynote_vaardigheden as $post ):
	
	        setup_postdata( $post );
	
	        $theid = $post->ID;
	    
	        $section_title  = get_the_title( $theid );
	        $section_text   = get_the_excerpt( $theid );
	
	        $content        = $post->post_content;
	        $section_text   = apply_filters('the_content', $content);   
	
	        $section_link   = get_sub_field( 'home_template_teaser_link' );
	        $title_id       = sanitize_title( $section_title );
	    
	        echo '<section aria-labelledby="' . $title_id . '" class="vaardigheid">';
	        echo '<h3 id="' . $title_id . '">' . $section_title . '</h3>';
	        echo $section_text;
	
	        $vaardigheid_afraders   = get_field( 'vaardigheid_afraders', $theid );
	        $vaardigheid_aanraders  = get_field( 'vaardigheid_aanraders', $theid );
	
	        if( $vaardigheid_afraders || $vaardigheid_aanraders ):
	
	          echo '<div class="grid grid--col-2 dosdonts">';
	
	          if ( $vaardigheid_aanraders ) {
	            $section_title = _x( 'Aanraders', 'titel op Stap-pagina', 'ictu-gcconf-posttypes' );
	            echo '<div class="aanrader flexblock">';
	            echo '<h4 id="' . $title_id . '">' . $section_title . '</h4>';
	            echo '<ul>';
	            foreach( $vaardigheid_aanraders as $dingges ):
	              echo '<li>' . get_the_title( $dingges->ID ) . '</li>';
	            endforeach;
//	            wp_reset_postdata();          
	            echo '</ul>';
	            echo '</div>';
	          }
	          if ( $vaardigheid_afraders ) {
	            $section_title = _x( 'Afraders', 'titel op Stap-pagina', 'ictu-gcconf-posttypes' );
	            echo '<div class="afrader flexblock">';
	            echo '<h4 id="' . $title_id . '">' . $section_title . '</h4>';
	            echo '<ul>';
	            foreach( $vaardigheid_afraders as $dingges ):
	              echo '<li>' . get_the_title( $dingges->ID ) . '</li>';
	            endforeach;
//	            wp_reset_postdata();          
	            echo '</ul>';
	            echo '</div>';
	          }
	
	          echo '</div>';
	        
	        endif;

	        echo '</section>';
	    
	      endforeach;
	      
	      wp_reset_postdata();          
	      
	    endif;
	
	}
	
    /** ----------------------------------------------------------------------------------------------------
     * Add an archive title
     */
    public function ictu_gc_add_posttype_title() {
    
	    if ( ! is_post_type_archive( ICTU_GCCONF_CPT_KEYNOTE ) )
	        return;

		$headline	= '';
		$intro_text	= '';
		$class 		= 'taxonomy-description';

		if ( is_post_type_archive( ICTU_GCCONF_CPT_KEYNOTE ) ) {
			$class 		= 'posttype-description';
			$headline = sprintf( '<h1 class="archive-title">%s</h1>', _x( "keynotes", "Post type name", 'ictu-gcconf-posttypes' ) );
	    }
	
	    if ( $headline || $intro_text ) {
	        printf( '<div class="' . $class . '">%s</div>', $headline . $intro_text );
	    }
	    else {
	        echo '';
	    }
  
	}
    


    /** ----------------------------------------------------------------------------------------------------
     * Post info: do not write any post info
     */
    public function filter_postinfo() {
    
    	return '';
  
	}
    

    /** ----------------------------------------------------------------------------------------------------
     * A new version of the_loop for keynotes
     */
	public function ictu_gc_frontend_archive_keynote_loop() {

		// code for a completely custom loop
		global $post;

echo '<h1> ictu_gc_frontend_archive_keynote_loop </h1>';

		$args = array(
		    'post_type'             =>  ICTU_GCCONF_CPT_KEYNOTE,
			'posts_per_page'        =>  -1,
			'order'                 =>  'ASC',
			'orderby'               =>  'post_title'
		  );
		  
		$sidebarposts = new WP_query( $args );
		
		if ($sidebarposts->have_posts()) {
	
			echo '<div class="flexbox">';
			
			$postcounter = 0;
			
			while ($sidebarposts->have_posts()) : $sidebarposts->the_post();
			
				$postcounter++;
				
//				$keynote      	= get_field('keynote_avatar', $post->ID );
				$citaat         	= get_field('facts_citaten', $post->ID );

				echo $this->ictu_gc_keynote_card( $post, $citaat );

			endwhile;
			
			echo '</div>';
			
			wp_reset_query();
			
		}
	}
		
    /** ----------------------------------------------------------------------------------------------------
     * add content before the actual the_content
     */
    public function prepend_content( $thecontent ) {

      global $post;

      if ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {


      }
      elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {
	      
      }

    }

    /** ----------------------------------------------------------------------------------------------------
     * Add rewrite rules
     */
    public function ictu_gc_add_rewrite_rules() {
    
    	return '';
  
	}

    /** ----------------------------------------------------------------------------------------------------
     * Add content to the content 
     * \0/
     */
    public function append_content( $thecontent ) {

      global $post;
      
      if ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {

        if ( function_exists( 'get_field' ) ) {

          $auteur = get_field( 'citaat_auteur', $post->ID );
          
          if ( $auteur ) {
            echo '<p><cite>' . $auteur . '</cite></p>';
          }
          
        }
        
      }
      elseif ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {
      }
      elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {
      }
      
      if ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) || is_singular( 'page' ) ) {
	  	// 
      }

      return $thecontent;

    }

    
    /** ----------------------------------------------------------------------------------------------------
     * Prepends a title before the content
     */
    public function ictu_gcconf_frontend_speaker_featured_image() {
	    
	    global $post;
		
		
//		if ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) )  {
//			the_post_thumbnail('post-image');
//		}
//		else {
//			return;
//		}
		
		
	}


    
    /** ----------------------------------------------------------------------------------------------------
     * Prepends a title before the content
     */
    public function ictu_gcconf_frontend_sessionkeynot_speakers() {
	    
	    global $post;

		if ( function_exists( 'get_field' ) ) {
			
			$gerelateerdecontent = get_field( 'gerelateerde_content_toevoegen', get_the_id() );
			
			if ( $gerelateerdecontent == 'ja' ) {
				
				$section_title  = get_field( 'content_block_title', $post->ID );
				$title_id       = sanitize_title( $section_title . '-title' );
				$related_items  = get_field('content_block_items');
				
				echo '<section aria-labelledby="' . $title_id . '" class="border related-content">';
				echo '<h2 id="' . $title_id . '">' . $section_title . '</h2>';
				echo '<div class="flexbox cards">';
				
				// loop through the rows of data
				foreach( $related_items as $post ):
				
					setup_postdata( $post );
					
					$theid = $post->ID;
					
					$section_title  	= get_the_title( $theid );
					$section_text   	= get_the_excerpt( $theid );
					$section_link   	= get_sub_field( 'home_template_teaser_link' );
					$title_id       	= sanitize_title( $section_title );
					$block_id       	= sanitize_title( 'related_' . $theid );
					$imageplaceholder	= '';
					$image          	= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
					
					if ( $image[0] ) {
						$class = ' with-image';
						$imageplaceholder = '<div class="featured-image">&nbsp;</div>';
					}
					else {
						$class = ' no-image';
					}
					
					
					echo '<div class="flexblock' . $class . '" id="' . $block_id . '">' . $imageplaceholder;
					echo '<h3 id="' . $title_id . '"><a href="' . get_permalink( $theid ) . '">' . $section_title . '</a></h3>';
					echo "<p>"  . $section_text . "</p>";
					echo '</div>';
					
				endforeach;
				
				wp_reset_postdata();            
				
				echo '</div>';
				echo '</section>';
				
			}
			
			$handigelinks = get_field( 'handige_links_toevoegen', $post->ID );
			
			if ( $handigelinks == 'ja' ) {
				
				$section_title  = get_field( 'links_block_title', $post->ID );
				$title_id       = sanitize_title( $section_title . '-title' );
				
				echo '<section aria-labelledby="' . $title_id . '" class="border related-links">';
				echo '<h2 id="' . $title_id . '">' . $section_title . '</h2>';
				
				$links_block_items = get_field('links_block_items');
				
				if( $links_block_items ): 
				
					echo '<ul>';
					
					while( have_rows('links_block_items') ): the_row();
					
						$links_block_item_url         = get_sub_field('links_block_item_url');
						$links_block_item_linktext    = get_sub_field('links_block_item_linktext');
						$links_block_item_description = get_sub_field('links_block_item_description');
						
						echo '<li> <span><a href="' . esc_url( $links_block_item_url ) . '">' . sanitize_text_field( $links_block_item_linktext ) . '</a>';
						
						if ( $links_block_item_description ) {
							echo '<br>' . sanitize_text_field( $links_block_item_description );
						}
						
						echo '</span></li>';
						
					endwhile;
					
					echo '</ul>';
					
				endif; 
				
				echo '</section>';
				
			}
		}
	}
	
	
    /** ----------------------------------------------------------------------------------------------------
     * Prepends a title before the content
     */
    public function ictu_gc_frontend_keynote_append_title() {
	    
		global $post;
		
		$title	= sprintf( _x( 'Over %s', 'Label stappen', 'ictu-gcconf-posttypes' ), get_the_title( )  ); 
	    echo '<h2>' . $title. '</h2>';
	    
	}



    /** ----------------------------------------------------------------------------------------------------
     * Prepends a title before the content
     */
    public function ictu_gc_frontend_stap_append_title() {
	
		global $post;

		$section_title = _x( 'Tips', 'titel op Stap-pagina', 'ictu-gcconf-posttypes' );
		$title_id       = sanitize_title( $section_title . '-' . $post->ID );
		
		// force a title, but do not make it seeable
		echo '<h2 id="' . $title_id . '" class="visuallyhidden">' . $section_title . '</h2>';

    }

    /** ----------------------------------------------------------------------------------------------------
     * Prepends a title before the content
     */
    public function ictu_gc_keynote_card( $keynote, $citaat ) {

		if ( is_object( $citaat ) && 'WP_Post' == get_class( $citaat ) ) {
			$citaat_post    	= get_post( $citaat->ID );
			$citaat_auteur  	= sanitize_text_field( get_field( 'citaat_auteur', $citaat->ID ) );
			$content        	= '&ldquo;' . $citaat_post->post_content . '&rdquo;';
		}
		else {
			if ( $citaat[0]->post_content ) {
				$content        = '&ldquo;' . $citaat[0]->post_content . '&rdquo;';
				$citaat_auteur  = sanitize_text_field( get_field( 'citaat_auteur', $citaat[0]->ID ) );
			}
			else {
				return '';
			}
		}

		$content        	= apply_filters('the_content', $content);   
		
		if ( is_object( $keynote ) ) { 
			$keynote_ID		= $keynote->ID;
		}
		elseif( $keynote > 0 ) {
			$keynote_ID		= $keynote;
		}
		else {
			return;
		}

		$posttype       	= get_post_type( $keynote_ID );
		$title_id       	= sanitize_title( 'title-' . $posttype . '-' . $keynote_ID );
		$section_id     	= sanitize_title( 'section-' . $posttype . '-' . $keynote_ID );
		$keynotepoppetje	= 'poppetje-1';
		$cardtitle			= esc_html( get_the_title( $keynote->ID ) );
		
		// wat extra afbreekmogelijkheden toevoegen in de titel
		$cardtitle			= str_replace("laaggeletterden", "laag&shy;geletterden", $cardtitle);
		$cardtitle			= str_replace("gebruikssituaties", "gebruiks&shy;situaties", $cardtitle);
		

		if ( get_field('keynote_avatar', $keynote_ID ) ) {
			$keynotepoppetje	= get_field('keynote_avatar', $keynote_ID );
		}

		$return 	= '<section aria-labelledby="' . $title_id . '" class="card card--keynote ' . $keynotepoppetje . '" id="' . $section_id . '">';
		$return    .= '<div class="card__image"></div>';
		$return    .= '<div class="card__content">';
		$return    .=
      '<h2 id="' . $title_id . '">'.
      '<a href="' . get_permalink( $keynote->ID ) . '">'.
      '<span>' . _x( 'Ontwerpen voor', 'Home section keynote', 'ictu-gcconf-posttypes' ) . ' </span>'.
      '<span>' . $cardtitle . '</span>'.
      '<span class="btn btn--arrow"></span>'.
      '</a></h2>';
		$return    .= '<div class="tegeltje">' . $content . '<p><strong>' . $citaat_auteur . '</strong></p></div>';
		$return    .= '</div>';
		$return    .= '</section>';

		return $return;

    }

    /** ----------------------------------------------------------------------------------------------------
     * Do actually register the post types we need
     */
    public function ictu_gcconf_register_post_types() {
      
      // ---------------------------------------------------------------------------------------------------
      // custom post type voor 'keynote'

    	$labels = array(
    		"name"                  => _x( 'Sessions', 'session type', 'ictu-gcconf-posttypes' ),
    		"singular_name"         => _x( 'Session', 'session type', 'ictu-gcconf-posttypes' ),
    		"menu_name"             => _x( 'Sessions', 'session type', 'ictu-gcconf-posttypes' ),
    		"all_items"             => _x( 'All sessions', 'session type', 'ictu-gcconf-posttypes' ),
    		"add_new"               => _x( 'Add new session', 'session type', 'ictu-gcconf-posttypes' ),
    		"add_new_item"          => _x( 'Add new session', 'session type', 'ictu-gcconf-posttypes' ),
    		"edit_item"             => _x( 'Edit session', 'session type', 'ictu-gcconf-posttypes' ),
    		"new_item"              => _x( 'New session', 'session type', 'ictu-gcconf-posttypes' ),
    		"view_item"             => _x( 'View session', 'session type', 'ictu-gcconf-posttypes' ),
    		"search_items"          => _x( 'Search session', 'session type', 'ictu-gcconf-posttypes' ),
    		"not_found"             => _x( 'No sessions found', 'session type', 'ictu-gcconf-posttypes' ),
    		"not_found_in_trash"    => _x( 'No sessions found', 'session type', 'ictu-gcconf-posttypes' ),
    		"featured_image"        => __( 'Featured image', 'ictu-gcconf-posttypes' ),
    		"archives"              => __( 'Archives', 'ictu-gcconf-posttypes' ),
    		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gcconf-posttypes' ),
    		);
    
    	$args = array(
			"label"                 => _x( 'Sessions', 'session type', 'ictu-gcconf-posttypes' ),
			"labels"              => $labels,
			"menu_icon"           => "dashicons-analytics",      	
			"description"         => "",
			"public"              => true,
			"publicly_queryable"  => true,
			"show_ui"             => true,
			"show_in_rest"        => false,
			"rest_base"           => "",
			"has_archive"         => true,
			"show_in_menu"        => true,
			"exclude_from_search" => false,
			"capability_type"     => "post",
			"map_meta_cap"        => true,
			"hierarchical"        => false,
			"rewrite"             => array( "slug" => ICTU_GCCONF_CPT_SESSION, "with_front" => true ),
			"query_var"           => true,
			"supports"            => array( "title", "editor", "excerpt" ),		
		);
    	register_post_type( ICTU_GCCONF_CPT_SESSION, $args );

      // ---------------------------------------------------------------------------------------------------
      // custom post type voor 'keynote'

    	$labels = array(
    		"name"                  => _x( 'Keynotes', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"singular_name"         => _x( 'Keynote', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"menu_name"             => _x( 'Keynotes', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"all_items"             => _x( 'All keynotes', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"add_new"               => _x( 'Add new keynote', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"add_new_item"          => _x( 'Add new keynote', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"edit_item"             => _x( 'Edit keynote', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"new_item"              => _x( 'New keynote', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"view_item"             => _x( 'View keynote', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"search_items"          => _x( 'Search keynote', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"not_found"             => _x( 'No keynotes found', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"not_found_in_trash"    => _x( 'No keynotes found', 'keynotes type', 'ictu-gcconf-posttypes' ),
    		"featured_image"        => __( 'Featured image', 'ictu-gcconf-posttypes' ),
    		"archives"              => __( 'Archives', 'ictu-gcconf-posttypes' ),
    		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gcconf-posttypes' ),
    		);
    
    	$args = array(
    		"label"                 => _x( 'Keynotes', 'Stappen label', 'ictu-gcconf-posttypes' ),
    		"labels"              => $labels,
			"menu_icon"           => "dashicons-admin-network",      	
    		"description"         => "",
    		"public"              => true,
    		"publicly_queryable"  => true,
    		"show_ui"             => true,
    		"show_in_rest"        => false,
    		"rest_base"           => "",
			"has_archive"         => true,
    		"show_in_menu"        => true,
    		"exclude_from_search" => false,
    		"capability_type"     => "post",
    		"map_meta_cap"        => true,
    		"hierarchical"        => false,
    		"rewrite"             => array( "slug" => ICTU_GCCONF_CPT_KEYNOTE, "with_front" => true ),
    		"query_var"           => true,
    		"supports"            => array( "title", "editor", "excerpt" ),		
			);
    	register_post_type( ICTU_GCCONF_CPT_KEYNOTE, $args );
    

      // ---------------------------------------------------------------------------------------------------
      // custom post type voor 'speakers'
    	$labels = array(
    		"name"                  => _x( 'Speakers', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"singular_name"         => _x( 'Speaker', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"menu_name"             => _x( 'Speakers', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"all_items"             => _x( 'All speakers', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"add_new"               => _x( 'Add new speaker', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"add_new_item"          => _x( 'Add new speaker', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"edit_item"             => _x( 'Edit speaker', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"new_item"              => _x( 'Edit speaker', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"view_item"             => _x( 'View speaker', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"search_items"          => _x( 'Search speaker', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"not_found"             => _x( 'No speakers found', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"not_found_in_trash"    => _x( 'No speakers found', 'speaker type', 'ictu-gcconf-posttypes' ),
    		"featured_image"        => __( 'Featured image', 'ictu-gcconf-posttypes' ),
    		"archives"              => __( 'Archives', 'ictu-gcconf-posttypes' ),
    		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gcconf-posttypes' ),
    		);
    
    	$args = array(
			"label"                 => _x( 'Speakers', 'Speakers label', 'ictu-gcconf-posttypes' ),
			"labels"              => $labels,
			"menu_icon"           => "dashicons-businessperson",      		
			"description"         => "",
			"public"              => true,
			"publicly_queryable"  => true,
			"show_ui"             => true,
			"show_in_rest"        => false,
			"rest_base"           => "",
			"has_archive"         => true,
			"show_in_menu"        => true,
			"exclude_from_search" => false,
			"capability_type"     => "post",
			"map_meta_cap"        => true,
			"hierarchical"        => false,
			"rewrite"             => array( "slug" => ICTU_GCCONF_CPT_SPEAKER, "with_front" => true ),
			"query_var"           => true,
    		"supports"            => array( "title", "editor", "thumbnail", "excerpt" ),		
		);
    	register_post_type( ICTU_GCCONF_CPT_SPEAKER, $args );


    
      // ---------------------------------------------------------------------------------------------------
      // Timeblocks taxonomie voor keynotes & sessions
      	$labels = array(
      		"name"                  => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Timeblock', 'timeblock taxonomy', 'ictu-gcconf-posttypes' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Timeblock', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"menu_name"             => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"all_items"             => _x( 'All timeblocks', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new"               => _x( 'Add timeblock', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new_item"          => _x( 'Add timeblock', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"edit_item"             => _x( 'Bewerk tijdsinschatting', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"new_item"              => _x( 'Nieuwe tijdsinschatting', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"view_item"             => _x( 'Bekijk tijdsinschatting', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"search_items"          => _x( 'Zoek tijdsinschatting', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found"             => _x( 'Geen tijdsinschattingen gevonden', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found_in_trash"    => _x( 'Geen tijdsinschattingen gevonden in de prullenbak', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gcconf-posttypes' ),
      		"archives"              => __( 'Archives', 'ictu-gcconf-posttypes' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gcconf-posttypes' ),
      		);
  
      	$args = array(
      		"label"                 => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"                  => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gcconf-posttypes' ),
      		"show_ui"             => true,
      		"show_in_menu"        => true,
      		"show_in_nav_menus"   => true,
      		"query_var"           => true,
      		"rewrite"             => array( 'slug' => ICTU_GCCONF_CT_TIMESLOT, 'with_front' => true, ),
      		"show_admin_column"   => false,
      		"show_in_rest"        => false,
      		"rest_base"           => "",
      		"show_in_quick_edit"  => true,
      	);
      	register_taxonomy( ICTU_GCCONF_CT_TIMESLOT, array( ICTU_GCCONF_CPT_KEYNOTE, ICTU_GCCONF_CPT_SESSION ), $args );


      // ---------------------------------------------------------------------------------------------------
      // Kosten taxonomie voor methode
      	$labels = array(
      		"name"                  => _x( 'Session type', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Session types', 'session type taxonomy', 'ictu-gcconf-posttypes' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Session types', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Session type', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"menu_name"             => _x( 'Session types', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"all_items"             => _x( 'All session types', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new"               => _x( 'Add session type', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new_item"          => _x( 'Add session type', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"edit_item"             => _x( 'Edit session type', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"new_item"              => _x( 'New session type', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"view_item"             => _x( 'View session type', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"search_items"          => _x( 'Search session type', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found"             => _x( 'No session types found', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found_in_trash"    => _x( 'No session types found', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gcconf-posttypes' ),
      		"archives"              => __( 'Archives', 'ictu-gcconf-posttypes' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gcconf-posttypes' ),
      		);
  
      	$args = array(
      		"label"                 => _x( 'Session types', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"                  => _x( 'Session types', 'session type taxonomy', 'ictu-gcconf-posttypes' ),
      		"show_ui"             => true,
      		"show_in_menu"        => true,
      		"show_in_nav_menus"   => true,
      		"query_var"           => true,
      		"rewrite"             => array( 'slug' => ICTU_GCCONF_CT_SESSIONTYPE, 'with_front' => true, ),
      		"show_admin_column"   => false,
      		"show_in_rest"        => false,
      		"rest_base"           => "",
      		"show_in_quick_edit"  => true,
      	);
      	register_taxonomy( ICTU_GCCONF_CT_SESSIONTYPE, array( ICTU_GCCONF_CPT_SESSION ), $args );

      // ---------------------------------------------------------------------------------------------------
      // Expertise taxonomie voor methode
      	$labels = array(
      		"name"                  => _x( 'Session levels', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Session level', 'session level taxonomy', 'ictu-gcconf-posttypes' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Session levels', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Session level', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"menu_name"             => _x( 'Session levels', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"all_items"             => _x( 'All session levels', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new"               => _x( 'Add session level', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new_item"          => _x( 'Add session level', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"edit_item"             => _x( 'Edit session level', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"new_item"              => _x( 'New session level', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"view_item"             => _x( 'View session level', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"search_items"          => _x( 'Search session level', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found"             => _x( 'No search session levels found', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found_in_trash"    => _x( 'No search session levels found', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gcconf-posttypes' ),
      		"archives"              => __( 'Archives', 'ictu-gcconf-posttypes' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gcconf-posttypes' ),
      		);
  
      	$args = array(
      		"label"               => _x( 'Session level', 'Digibeter label', 'ictu-gcconf-posttypes' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"               => _x( 'Session level', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"show_ui"             => true,
      		"show_in_menu"        => true,
      		"show_in_nav_menus"   => true,
      		"query_var"           => true,
      		"rewrite"             => array( 'slug' => ICTU_GCCONF_CT_LEVEL, 'with_front' => true, ),
      		"show_admin_column"   => false,
      		"show_in_rest"        => false,
      		"rest_base"           => "",
      		"show_in_quick_edit"  => true,
      	);
      	register_taxonomy( ICTU_GCCONF_CT_LEVEL, array( ICTU_GCCONF_CPT_SESSION ), $args );

      // ---------------------------------------------------------------------------------------------------
      // Expertise taxonomie voor methode
      	$labels = array(
      		"name"                  => _x( 'Session locations', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Session location', 'session location taxonomy', 'ictu-gcconf-posttypes' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Session locations', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Session location', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"menu_name"             => _x( 'Session locations', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"all_items"             => _x( 'All session locations', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new"               => _x( 'Add session location', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new_item"          => _x( 'Add session location', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"edit_item"             => _x( 'Edit session location', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"new_item"              => _x( 'New session location', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"view_item"             => _x( 'View session location', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"search_items"          => _x( 'Search session location', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found"             => _x( 'No search session locations found', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found_in_trash"    => _x( 'No search session locations found', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gcconf-posttypes' ),
      		"archives"              => __( 'Archives', 'ictu-gcconf-posttypes' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gcconf-posttypes' ),
      		);
  
      	$args = array(
      		"label"               => _x( 'Session location', 'Digibeter label', 'ictu-gcconf-posttypes' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"               => _x( 'Session location', 'session location taxonomy', 'ictu-gcconf-posttypes' ),
      		"show_ui"             => true,
      		"show_in_menu"        => true,
      		"show_in_nav_menus"   => true,
      		"query_var"           => true,
      		"rewrite"             => array( 'slug' => ICTU_GCCONF_CT_LOCATION, 'with_front' => true, ),
      		"show_admin_column"   => false,
      		"show_in_rest"        => false,
      		"rest_base"           => "",
      		"show_in_quick_edit"  => true,
      	);
      	register_taxonomy( ICTU_GCCONF_CT_LOCATION, array( ICTU_GCCONF_CPT_SESSION ), $args );

		// ---------------------------------------------------------------------------------------------------

		// make tags available to keynotes and sessions
		register_taxonomy_for_object_type( 'post_tag', ICTU_GCCONF_CPT_KEYNOTE );
		register_taxonomy_for_object_type( 'post_tag', ICTU_GCCONF_CPT_SESSION );

		// ---------------------------------------------------------------------------------------------------
		
		// clean up after ourselves
    	flush_rewrite_rules();
  
    }

//
  
	/** ----------------------------------------------------------------------------------------------------
	* display taxonomies
	*/
	public function get_classifications( $theid = '', $taxonomy = '', $wrapper1 = 'dt', $wrapper2 = 'dd' ) {
		
		$return     = '';
		
		if ( $theid && $taxonomy ) {
	
			$args = array(
				'name' => $taxonomy
			);
			$output		= 'objects'; // or names
			
			$taxobject  = get_taxonomies( $args, $output ); 
			$tax_info   = array_values($taxobject)[0];
			$return     = '<' . $wrapper1 . '>' . $tax_info->label . '</' . $wrapper1 . '> <' . $wrapper2 . '>';
			$term_list  = wp_get_post_terms( $theid, $taxonomy, array("fields" => "all"));
			$counter    = 0;
			
			foreach( $term_list as $term_single ) {
				
				$counter++;
				$term_link = get_term_link( $term_single );
				
				if ( $counter > 1 ) {
					$return .= ', '; //do something here
				}
//				$return .= '<a href="' . esc_url( $term_link ) . '">' . $term_single->name . '</a>';
				$return .= $term_single->name;
			}
			
			$return .= '</' . $wrapper2 . '>';
			
		}
		
		return $return;
		
	}

    /** ----------------------------------------------------------------------------------------------------
     * filter the breadcrumb
     */
	public function filter_breadcrumb( $crumb = '', $args = '' ) {
		
		global $post;

		$span_before_start  = '<span class="breadcrumb-link-wrap" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
		$span_between_start = '<span itemprop="name">';
		$span_before_end    = '</span>';

		if ( is_singular( ICTU_GCCONF_CPT_SESSION ) || is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {
		
			$crumb = get_the_title( get_the_id() ) ;
		
		}
		// 'keynote'
		
		if ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {
			
			$crumb = 'poepje ' . ICTU_GCCONF_CPT_KEYNOTE . '<br>';
			
			$brief_page_overview        = get_field('themesettings_inclusie_keynotepagina', 'option');		// code hier
			
			if ( $brief_page_overview ) {

				$actueelpagetitle = get_the_title( $brief_page_overview );
				
				if ( $brief_page_overview ) {
					$crumb = gc_wbvb_breadcrumbstring( $brief_page_overview, $args );
				}
			}

			
		}
		
		return $crumb;
		
	}
    
    /** ----------------------------------------------------------------------------------------------------
     * add extra class to page template .entry
     */
	function add_class_inleiding_to_entry( $attributes ) {
		$attributes['class'] .= ' inleiding';
		return $attributes;
	}

}

endif;

//========================================================================================================

if (! function_exists( 'gc_wbvb_breadcrumbstring' ) ) {

	function gc_wbvb_breadcrumbstring( $currentpageID, $args ) {
	
		global $post;
		
		$crumb = '';
		$countertje = 0;
		
		if ( $currentpageID ) {
			$crumb = '<a href="' . get_permalink( $currentpageID ) . '">' . get_the_title( $currentpageID ) .'</a>' . $args['sep'] . ' ' . get_the_title( $post->ID );
			$postparents = get_post_ancestors( $currentpageID );
	
			foreach( $postparents as $postparent ) {
				$countertje ++;
				$crumb = '<a href="' . get_permalink( $postparent ) . '">' . get_the_title( $postparent ) .'</a>' . $args['sep'] . $crumb;
			}
		}
		
		return $crumb;
		
	}

}

//========================================================================================================
