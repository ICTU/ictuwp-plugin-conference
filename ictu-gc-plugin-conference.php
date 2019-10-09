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

if ( ! defined( 'ICTU_GCCONF_CT_COUNTRY' ) ) {
  define( 'ICTU_GCCONF_CT_COUNTRY', 'speakercountry' );  // slug for custom taxonomy for a speaker's country
}







define( 'ICTU_GC_ARCHIVE_CSS',		'ictu-gc-header-css' );  
define( 'ICTU_GC_FOLDER',           'do-stelselplaat' );
define( 'ICTU_GC_BASE_URL',         trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'ICTU_GC_ASSETS_URL',		trailingslashit( ICTU_GC_BASE_URL ) );
define( 'ICTU_GC_INCL_VERSION',		'0.0.2' );

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

		add_filter( 'genesis_single_crumb',   array( $this, 'filter_breadcrumb' ), 10, 2 );
		add_filter( 'genesis_page_crumb',     array( $this, 'filter_breadcrumb' ), 10, 2 );
		add_filter( 'genesis_archive_crumb',  array( $this, 'filter_breadcrumb' ), 10, 2 ); 				
		add_filter( 'genesis_tax_crumb',  	  array( $this, 'filter_breadcrumb' ), 10, 2 ); 				
		
		
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
	* custom loop
	*/
	function ictu_gcconf_tax_loop(  ) {
	
		if ( have_posts() ) :
			
			echo '<div class="archive-list">';
			
			while ( have_posts() ) : the_post();
				
				// do loop stuff
				$getid        	= get_the_ID();
				$posttype     	= get_post_type( $getid );
				$permalink    	= get_permalink( $getid );
				$publishdate  	= get_the_date();
				$theID        	= 'featured_image_post_' . $getid;
				$the_image_ID 	= 'image_' . $theID; // HIERO, the loop
				$extra_cssclass = ' ' . $posttype;
				$class 			= 'feature-image noimage';
				$bluh			= '';
				$image			= [];
				

				$speakers 							= get_field('speakers', $getid );
				
				echo '<section class="entry' . $extra_cssclass . '" itemscope itemtype="http://schema.org/SocialMediaPosting" id="' . $theID . '">';
				echo '<a href="' . $permalink . '" itemprop="url">';
					
				echo '<h2 class="entry-title" itemprop="headline">' . get_the_title() . '</h2></header>';
				echo '<div class="excerpt">';
				echo get_the_excerpt( $getid );
				echo '</div>';

				if ( $speakers ) {
					echo '<div><h3>' . _x( 'Speakers', 'ictu-gcconf-posttypes' ) . '</h3>';
					foreach( $speakers as $speaker ):
						echo $this->ictu_gcconf_speakerinfo( array( 'ID' => $speaker->ID ) );		
					endforeach;
					echo '</div>';
				}
				
				echo '</a>';
				echo '</section>';
			
			endwhile; /** end of one post **/
	
			echo '</div>';
	
			do_action( 'genesis_after_endwhile' );
	
		else : /** if no posts exist **/
			do_action( 'genesis_loop_else' );
		endif; /** end loop **/
		

		
	}

	

    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
     
	public function ictu_gcconf_speakerinfo( $args = [] ) {

	    $defaults = array (
	        'showname'		=> true,
	        'dolink'		=> false,
	        'titletag'		=> 'h3', 
	        'ID'			=> 0, 
	        'fulldesc'		=> false, 
	        'socmedlinks'	=> false,
	        'echo' 			=> true
	    );
	     
	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $args['ID'] ) {
			return;
		}

		$return = '<div class="div-speaker card no-image">';

		if ( $args['fulldesc'] ) {
			$return = '<div class="div-speaker full-description card no-image">';
		}
		
		
		if ( $args['showname'] ) {
			if ( $args['dolink'] ) {
				$return .= '<' . $args['titletag'] . ' class="div-speaker-name"><a href="' . get_the_permalink( $args['ID'] ) . '">' . get_the_title( $args['ID'] ) . '<span class="btn btn--arrow"></span></a></' . $args['titletag'] . '>';		
			}
			else {
				$return .= '<' . $args['titletag'] . ' class="div-speaker-name">' . get_the_title( $args['ID'] ) . '</' . $args['titletag'] . '>';		
			}

		}

		
		if ( $args['fulldesc'] ) {
			$excerpt = get_the_excerpt( $args['ID'] );
			$return .= '<p>' . get_the_post_thumbnail( $args['ID'], 'thumbnail', array( 'class' => 'speaker-photo thumbnail alignleft' ) );
			$return .= wp_strip_all_tags( $excerpt );
			if ( $args['socmedlinks'] ) {
				$return .= $this->ictu_gc_frontend_weblinks( array( 'ID' => $args['ID'], 'echo' => false, ) );
			}
			$return .= '</p>';
		}
		else {
			$return .= get_the_post_thumbnail( $args['ID'], 'thumbnail', array( 'class' => 'speaker-photo thumbnail alignleft' ) );
		}

		$return .= '</div>';

		if ( $args['echo'] ) {
			echo $return;
		}
		else {
			return $return;
		}

	}


    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
     
	public function ictu_gc_frontend_home_after_content() {

		global $post;
		
		$the_id = $post->ID;

		if( have_rows('blocks') ) {
			
			$sectioncounter = 0;
			
			// loop through the rows of data
			while ( have_rows('blocks') ) : the_row();
			
				$sectioncounter++;

				$section_title 			= get_sub_field( 'block_title' );
				$block_title_id			= get_sub_field( 'block_title_id' );
				$block_free_text		= get_sub_field( 'block_free_text' );
				$block_extra_type		= get_sub_field( 'block_extra_type' );
				$block_time				= get_sub_field( 'block_time' );
				$time 					= '';
				$section_css			= 'section-block';
				
				$headertitle_tag		= 'h3';
				$headertitle_tag_sub	= 'h4';

				if ( $block_time ) {
					$time			= '<div class="date-badge"><span class="dag multiple">' . $block_time . '</span></div>';
					$section_css	.= ' has-time';
				}

				
				if ( ! $section_title ) {
					$headertitle_tag		= 'h2';
					$headertitle_tag_sub	= 'h3';
					$block_title_id 		= 'section' . $the_id . '_' . $sectioncounter;
					$sectionblockstart 		= '<section class="' . $section_css . '">';
				}
				else {

					if ( $block_title_id ) { 
						$title_id		= sanitize_title( $block_title_id );
					}
					else {
						$title_id		= sanitize_title( $section_title );
					}
					
					$sectionblockstart 		= '<section aria-labelledby="' . $title_id . '" class="' . $section_css . '">';
				}

				echo $sectionblockstart;

				if ( $section_title ) {
					echo '<header><h2 id="' . $title_id . '">' . $time;
					echo $section_title . '</h2></header>';
				}				
				
				echo '<div class="section-content">';
				
				if ( $block_free_text ) {
					echo $block_free_text;
				}

				if ( 'events' === $block_extra_type ) {
					
					$posts = get_sub_field( 'block_events' );
					
					if( $posts ):

						foreach( $posts as $post):
						
							setup_postdata($post);

							$section_title 		= get_the_title( $post->ID );
							$title_id			= sanitize_title( $section_title );
			                $EM_Event       	= em_get_event( $post );

							echo '<div class="card no-image" aria-labelledby="' . $title_id . '" class="div-session">';
							echo '<' . $headertitle_tag . ' id="' . $title_id . '"><a href="' . get_permalink( $post->ID ) . '">' . $section_title . '<span class="btn btn--arrow"></span></a></' . $headertitle_tag . '>';
							
							echo '<p>';
							echo $EM_Event->output( '#_EVENTEXCERPT{999}' );
							echo '</p>';
							echo '</div>';


						endforeach;
						
						wp_reset_postdata(); 

					endif;
					
				}
				elseif ( 'keynotes' === $block_extra_type ) {

					$posts = get_sub_field( 'block_keynotes' );

					if( $posts ):

						foreach( $posts as $post):
						
							setup_postdata($post);

							$section_title 		= get_the_title( $post->ID );
							$title_id			= sanitize_title( $section_title );
							$speakers 			= get_field('speakers', $post->ID );

							$time_term			= get_term_by( 'id', $post->ID, ICTU_GCCONF_CT_TIMESLOT );
							$location_term		= get_term_by( 'id', $post->ID, ICTU_GCCONF_CT_LOCATION );

							echo '<div class="div-session card" aria-labelledby="' . $title_id . '">';
							// card

							echo '<' . $headertitle_tag . ' id="' . $title_id . '"><a href="' . get_permalink( $post->ID ) . '">' . $section_title . '<span class="btn btn--arrow"></span></a></' . $headertitle_tag . '>';

							if ( $speakers ) {
								
								$postcounter    = 0;
								$countrycounter    = 0;
								
								echo '<p>';
								
								foreach( $speakers as $speaker ):
									$postcounter++;
									
									if ( $postcounter > 1 ) {
										echo ', ';
									}
									else {
										echo ' - ';
									}
									echo get_the_title( $speaker->ID );		

									$county_term			= wp_get_post_terms( $speaker->ID, ICTU_GCCONF_CT_COUNTRY );
							
									if ( $county_term && ! is_wp_error( $county_term ) ) { 
										echo ' (';	
										foreach ( $county_term as $term ) {

											$countrycounter++;
											
											if ( $countrycounter > 1 ) {
												echo ', ';
											}
											
											echo $term->name;	
										}	
										echo ') ';	
									}	
									
									
								endforeach;

								echo '</p>';
								
							}

							echo '<p>';
							if ( $speakers ) {
								foreach( $speakers as $speaker ):
									echo  get_the_post_thumbnail( $speaker->ID, 'thumbnail', array( 'class' => 'speaker-photo thumbnail alignleft' ) );
								endforeach;
							}

							if ( $time_term || $location_term ) {
							
								echo '<div class="time-location">';		
								if ( $time_term ) {
									echo '<a href="' . get_term_link( $time_term ) . '">' . $time_term->name . '</a> ';	
								}	
								if ( $location_term ) {
									echo '<a href="' . get_term_link( $location_term ) . '">' . $location_term->name . '</a> ';	
								}	
								echo '</div>';
								
							}
							

							$excerpt = get_the_excerpt( $post->ID );
							echo wp_strip_all_tags( $excerpt );
							echo '</p>';							

							echo '</div>';		

						endforeach;
						
						wp_reset_postdata(); 

					endif;
					
				}
				elseif ( 'speakers' === $block_extra_type ) {

					$posts = get_sub_field( 'block_speakers' );

					if( $posts ):

						echo '<div class="grid grid--col-2">';

						foreach( $posts as $post):
						
							setup_postdata($post);

							$section_title 		= get_the_title( $post->ID );
							$title_id			= sanitize_title( $section_title );
							$speakers 			= get_field('speakers', $post->ID );

							$time_term			= get_term_by( 'id', $post->ID, ICTU_GCCONF_CT_TIMESLOT );
							$location_term		= get_term_by( 'id', $post->ID, ICTU_GCCONF_CT_LOCATION );

							echo '<div aria-labelledby="' . $title_id . '" class="div-session">';
							echo '<' . $headertitle_tag . ' id="' . $title_id . '"><a href="' . get_permalink( $post->ID ) . '">' . $section_title . '</a></' . $headertitle_tag . '>';
							
							$extraattr			= array( 'class' => 'speaker-photo thumbnail alignleft' );
							$size 				= 'thumbnail';
							
							$image = get_the_post_thumbnail( $post->ID, $size, $extraattr );
							
							if ( ! $image ) {

								$arr_speaker_images 	= get_field('fallback_for_speaker_images', 'option');
								
								if ( $arr_speaker_images ) {
									$randomid = array_rand( $arr_speaker_images, 1 );
									$image = wp_get_attachment_image( $arr_speaker_images[ $randomid ], $size, false, $extraattr );
								}
							
							}
							echo $image;

							echo get_the_excerpt( $post->ID );

							echo '</div>';		

						endforeach;

						echo '</div>';		
						
						wp_reset_postdata(); 

					endif;
					
				}				
				elseif ( 'sessions' === $block_extra_type ) {
					
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


							echo '<div class="card no-image" aria-labelledby="' . $title_id . '" class="div-session">';
							echo '<' . $headertitle_tag . ' id="' . $title_id . '"><a href="' . get_permalink( $block_sessions_session->ID ) . '">' . $section_title . '<span class="btn btn--arrow"></span></a></' . $headertitle_tag . '>';

							if ( $speakers ) {
								echo '<div class="speakers"><' . $headertitle_tag_sub . '>' . _x( 'Speakers', 'ictu-gcconf-posttypes' ) . '</' . $headertitle_tag_sub . '>';
								foreach( $speakers as $speaker ):
									echo $this->ictu_gcconf_speakerinfo( array( 'ID' => $speaker->ID ) );		
								endforeach;
								echo '</div>';
							}

							if ( $time_term || $location_term ) {
							
								echo '<p class="time-location">';		
								if ( $time_term ) {
									echo '<a href="' . get_term_link( $time_term ) . '">' . $time_term->name . '</a> ';	
								}	
								if ( $location_term ) {
									echo '<a href="' . get_term_link( $location_term ) . '">' . $location_term->name . '</a> ';	
								}	
								echo '</p>';
								
							}

							echo get_the_excerpt( $block_sessions_session->ID );

							echo '</div>';		
						
						endwhile;
					
					endif;
				
				}

				echo '</div>'; // .section-content

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
	public function ictu_gcconf_frontend_append_speakers( $args = [] ) {
		
		global $post;

	    $defaults = array (
	        'showname' => true,
	        'dolink' => false,
	        'echo' => true
	    );
	     
	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );
	    
	    $return = '';

		$speakers 							= get_field('speakers', $post->ID );

		if ( $speakers ) {
			$return = '<div class="speakers"><h2>' . _x( 'Speakers', 'ictu-gcconf-posttypes' ) . '</h2>';
			$return = '<div class="grid grid--col-2">';
			foreach( $speakers as $speaker ):
				$return .= $this->ictu_gcconf_speakerinfo( array( 'ID' => $speaker->ID, 'dolink' => true, 'echo' => false, 'fulldesc' => true, 'socmedlinks' => true ) );		
			endforeach;
			$return .= '</div>';
			$return .= '</div>';
		}

		if ( $args['echo'] ) {
			echo $return;
		}
		else {
			return $return;
		}


	}


    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
	public function ictu_gc_frontend_session_before_content() {
		
		global $post;

		echo '<p>TEST : ictu_gc_frontend_session_before_content </p>';

	}


    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
	public function ictu_gc_frontend_session_after_content() {
		
		global $post;

		echo '<p>TEST : ictu_gc_frontend_session_after_content </p>';

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
		elseif ( is_post_type_archive( ICTU_GCCONF_CPT_KEYNOTE ) )  {

			/** Replace the standard loop with our custom loop */
			remove_action( 'genesis_loop', 'genesis_do_loop' );
			remove_action( 'genesis_loop', 'gc_wbvb_archive_loop' );
//			add_action( 'genesis_loop', array( $this, 'ictu_gcconf_tax_loop' ) );
			
		}
		elseif ( is_tax( ICTU_GCCONF_CT_TIMESLOT ) )  {
			
			//Removes Title and Description on Archive, Taxonomy, Category, Tag
			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			add_action( 'genesis_before_loop', 'gc_wbvb_add_taxonomy_description', 15 );
			
			/** Replace the standard loop with our custom loop */
			remove_action( 'genesis_loop', 'genesis_do_loop' );
			remove_action( 'genesis_loop', 'gc_wbvb_archive_loop' );
			add_action( 'genesis_loop', array( $this, 'ictu_gcconf_tax_loop' ) );
			

		}
		elseif ( ICTU_GCCONF_CPT_SPEAKER == get_post_type( ) )  {

			// Prepend job title
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_speaker_append_jobtitle' ), 6 ); 		

			// Prepend job title
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_speaker_append_country' ), 5 ); 		
					
			// append speaker image
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_speaker_featured_image' ), 8 ); 		

			// append weblinks
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_speaker_append_weblinks' ), 12 ); 		
					
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {

			//* Remove standard header
//			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
//			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
//			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

//			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_session_before_content' ), 8 ); 				
//			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_session_after_content' ), 12 ); 				

			
//			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_append_title' ), 9 ); 				

			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_append_speakers' ), 12 ); 			
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_append_vaardigheden' ), 17 ); 			


			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_sessionkeynote_speakers' ), 20 ); 				
	
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {

			//* Remove standard header
//			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
//			remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
//			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

//			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_after_content' ), 8 ); 				

							
//			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_append_title' ), 9 ); 				

			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_append_speakers' ), 12 ); 			
			add_action( 'genesis_entry_content',  array( $this, 'ictu_gc_frontend_keynote_append_vaardigheden' ), 17 ); 			


			add_action( 'genesis_entry_content',  array( $this, 'ictu_gcconf_frontend_sessionkeynote_speakers' ), 20 ); 				
	
		}


		//=================================================
		
		add_filter( 'genesis_post_info',   array( $this, 'filter_postinfo' ), 10, 2 );
		
	}

    /** ----------------------------------------------------------------------------------------------------
     * Append vaardigheden to keynote single
     */
	public function ictu_gcconf_frontend_append_speakersx() {
	
		global $post;
	
	    $keynote_tips 			= get_field( 'keynote_tips', $post->ID );
	    $keynote_tips_titel		= get_field( 'keynote_tips_titel', $post->ID );
	    $keynote_tips_inleiding	= get_field( 'keynote_tips_inleiding', $post->ID );

		$section_title = ( $keynote_tips_titel ) ? $keynote_tips_titel : _x( 'Tips', 'titel op keynote-pagina', 'ictu-gcconf-posttypes' );
		$title_id       = sanitize_title( $section_title . '-' . $post->ID );
	
	    if( $keynote_tips ):

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
     * Append country tax to speaker single
     */
	public function ictu_gc_frontend_speaker_append_country() {
		
		global $post;

		$countrycounter = 0;
		$county_term			= wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_COUNTRY );

		if ( $county_term && ! is_wp_error( $county_term ) ) { 
			echo '<p><strong>(';	
			foreach ( $county_term as $term ) {

				$countrycounter++;
				
				if ( $countrycounter > 1 ) {
					echo ', ';
				}
				
				echo $term->name;	
			}	
			echo ')</strong></p>';	
		}	

		
	}


    /** ----------------------------------------------------------------------------------------------------
     * Append job title to keynote single
     */
	public function ictu_gc_frontend_speaker_append_jobtitle() {
		
		global $post;
		
		$jobtitle = get_field( 'speaker_jobtitle', $post->ID );
		
		if ( $jobtitle ) {
			echo '<p class="jobtitle">' . $jobtitle . '</p>';
		}
		
		
	}


    /** ----------------------------------------------------------------------------------------------------
     * Append country tax to speaker single
     */
	public function ictu_gc_frontend_weblinks( $args = [] ) {
		
		global $post;

		$return = '';

	    $defaults = array (
	        'ID'			=> 0,
	        'sectiontitle' 	=> false,
	        'titletag'		=> 'h2',
	        'echo' 			=> true
	    );
	     
	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $args['ID'] ) {
			return;
		}

		if( have_rows('speaker_links', $args['ID'] ) ):

			$return .= '<div class="speaker-links">';		
			if ( $args['sectiontitle'] ) {
				$return .= '<' . $args['titletag'] . '>' . $args['sectiontitle'] . '</' . $args['titletag'] . '>';		
			}
			$return .= '<ul class="social-media">';		
		
			// loop through rows (sub repeater)
			while( have_rows('speaker_links', $args['ID'] ) ): the_row();
			
				$speaker_link_url 		= get_sub_field( 'speaker_link_url' );
				$speaker_link_text 		= get_sub_field( 'speaker_link_text' );
				$speaker_link_type		= get_sub_field( 'speaker_link_type' );

				if ( $speaker_link_url ) {
					$return .= '<li><a href="' . $speaker_link_url . '" class="' . $speaker_link_type . '">';
					$return .= $speaker_link_text . '</a></li>';
				}
			
			endwhile;

			$return .= '</ul>';		
			$return .= '</div>';		
		
		endif;

		if ( $args['echo'] ) {
			echo $return;
		}
		else {
			return $return;
		}

		
	}


    /** ----------------------------------------------------------------------------------------------------
     * Append vaardigheden to keynote single
     */
	public function ictu_gc_frontend_speaker_append_weblinks() {
		
		global $post;
		
//		$this->ictu_gc_frontend_weblinks( array( 'ID' => $post->ID, 'sectiontitle' => sprintf( _x( '%s on the web', 'stappen', 'gebruikercentraal' ), get_the_title( $post ) ) ) );
		$this->ictu_gc_frontend_weblinks( array( 'ID' => $post->ID, 'sectiontitle' => _x( 'Links', 'Header text speaker links', 'gebruikercentraal' ) ) );
//		$this->ictu_gc_frontend_weblinks( array( 'ID' => $post->ID ) );
		
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
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {
		}
		elseif ( is_page( ) ) {
			
			$the_id 		= $post->ID;
			$type 			= '';
			$docheck 		= false;
			$speaker_page 	= get_field('themesettings_conference_speakers', 'option');
			$keynote_page 	= get_field('themesettings_conference_keynotes', 'option');
			$session_page	= get_field('themesettings_conference_sessions', 'option');
			$postcounter 	= 0;
			
			if ( $speaker_page->ID === $the_id ) {
				$docheck	= true;	
				$type		= ICTU_GCCONF_CPT_SPEAKER;
			}
			elseif ( $keynote_page->ID === $the_id ) {
				$docheck	= true;	
				$type		= ICTU_GCCONF_CPT_KEYNOTE;
			}
			elseif ( $session_page->ID === $the_id ) {
				$docheck	= true;	
				$type		= ICTU_GCCONF_CPT_SESSION;
			}
			
			if ( $docheck && !have_rows('blocks') ) {

				$args = array(
				    'post_type'             =>  $type,
					'posts_per_page'        =>  -1,
//					'order'                 =>  'ASC',
//					'orderby'               =>  'post_title'
				    
				  );
				$sidebarposts = new WP_query( $args );
		
				if ($sidebarposts->have_posts()) {

					echo '<div class="grid grid--col-2">';
					
					while ($sidebarposts->have_posts()) : $sidebarposts->the_post();
					
						$postcounter++;

						$speakers 							= get_field('speakers', $sidebarposts->ID );
		
						echo '<div class="card no-image">';
						echo '<h2><a href="' . get_the_permalink( $sidebarposts->ID ) . '">' . get_the_title( $sidebarposts->ID ) . '<span class="btn btn--arrow"></span></a></h2>';
						echo '<p>' . get_the_excerpt( $sidebarposts->ID ) . '</p>'; // excerpt

						if ( $speakers ) {
							$speakercounter = 0;
							echo '<p>' . _x( 'Speakers', 'ictu-gcconf-posttypes' ) . ' : ';
							foreach( $speakers as $speaker ):
								$speakercounter++;
								if ( $speakercounter > 1 ) {
									echo ', ';
								}
								echo get_the_title( $speaker->ID );		
							endforeach;
							echo '</p>';
						}

						
						echo '</div>';

					endwhile;
					
					echo '</div>';
				
				}					
				
				wp_reset_query();
				
			}
		
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

		if ( has_post_thumbnail( $post ) ) {
			echo '<div class="thumbnail alignright">';
			the_post_thumbnail( BLOG_SINGLE_TABLET );
			echo '</div>';
		}

		
	}


    
    /** ----------------------------------------------------------------------------------------------------
     * Prepends a title before the content
     */
    public function ictu_gcconf_frontend_sessionkeynote_speakers() {
	    
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
      // Expertise taxonomie voor methode
      	$labels = array(
      		"name"                  => _x( 'Countries', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Country', 'session level taxonomy', 'ictu-gcconf-posttypes' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Countries', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"singular_name"         => _x( 'Country', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"menu_name"             => _x( 'Countries', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"all_items"             => _x( 'All countries', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new"               => _x( 'Add country', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"add_new_item"          => _x( 'Add country', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"edit_item"             => _x( 'Edit country', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"new_item"              => _x( 'New country', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"view_item"             => _x( 'View country', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"search_items"          => _x( 'Search country', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found"             => _x( 'No countries found', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"not_found_in_trash"    => _x( 'No countries found', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gcconf-posttypes' ),
      		"archives"              => __( 'Archives', 'ictu-gcconf-posttypes' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gcconf-posttypes' ),
      		);
  
      	$args = array(
      		"label"               => _x( 'Country', 'Digibeter label', 'ictu-gcconf-posttypes' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"               => _x( 'Country', 'session level taxonomy', 'ictu-gcconf-posttypes' ),
      		"show_ui"             => true,
      		"show_in_menu"        => true,
      		"show_in_nav_menus"   => true,
      		"query_var"           => true,
      		"rewrite"             => array( 'slug' => ICTU_GCCONF_CT_COUNTRY, 'with_front' => true, ),
      		"show_admin_column"   => false,
      		"show_in_rest"        => false,
      		"rest_base"           => "",
      		"show_in_quick_edit"  => true,
      	);
      	register_taxonomy( ICTU_GCCONF_CT_COUNTRY, array( ICTU_GCCONF_CPT_SPEAKER ), $args );

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
			$postcounter    = 0;
			
			foreach( $term_list as $term_single ) {
				
				$postcounter++;
				$term_link = get_term_link( $term_single );
				
				if ( $postcounter > 1 ) {
					$return .= ', '; //do something here
				}
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

		$span_before_start  	= '<span class="breadcrumb-link-wrap" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
		$span_between_start 	= '<span itemprop="name">';
		$span_before_end    	= '</span>';
		$brief_page_overview	= '';

		if ( is_singular( ICTU_GCCONF_CPT_SESSION ) || is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {
		
			$crumb = get_the_title( get_the_id() ) ;
		
		}

		// -------------------------------------------------------------------------------------------------
		
		if ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {

			$brief_page_overview        = get_field('themesettings_conference_speakers', 'option');

		}
		elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {

			$brief_page_overview        = get_field('themesettings_conference_keynotes', 'option');

		}
		elseif ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {

			$brief_page_overview        = get_field('themesettings_conference_sessions', 'option');

		}

		// -------------------------------------------------------------------------------------------------
		
		if ( $brief_page_overview ) {

			$actueelpagetitle = get_the_title( $brief_page_overview );
			
			if ( $brief_page_overview ) {
				$crumb = gc_wbvb_breadcrumbstring( $brief_page_overview, $args );
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
		$postcounter = 0;
		
		if ( $currentpageID ) {
			$crumb = '<a href="' . get_permalink( $currentpageID ) . '">' . get_the_title( $currentpageID ) .'</a>' . $args['sep'] . ' ' . get_the_title( $post->ID );
			$postparents = get_post_ancestors( $currentpageID );
	
			foreach( $postparents as $postparent ) {
				$postcounter ++;
				$crumb = '<a href="' . get_permalink( $postparent ) . '">' . get_the_title( $postparent ) .'</a>' . $args['sep'] . $crumb;
			}
		}
		
		return $crumb;
		
	}

}

//========================================================================================================
