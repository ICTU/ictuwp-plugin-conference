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

add_action( 'plugins_loaded', array( 'ICTU_GC_conference', 'init' ), 10 );

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

if ( ! defined( 'SPEAKER_IMG_SIZE' ) ) {
  define( 'SPEAKER_IMG_SIZE', 'speaker-image-size' );
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


if ( ! class_exists( 'ICTU_GC_conference' ) ) :

  class ICTU_GC_conference {
  
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

      $this->fn_ictu_gcconf_includes();
      $this->fn_ictu_gcconf_setup_actions();

    }
  
    /** ----------------------------------------------------------------------------------------------------
     * Hook this plugins functions into WordPress
     */
	private function fn_ictu_gcconf_includes() {
		
      require_once dirname( __FILE__ ) . '/includes/conference-acf-definitions.php';

    }
  
    /** ----------------------------------------------------------------------------------------------------
     * Hook this plugins functions into WordPress
     */
	private function fn_ictu_gcconf_setup_actions() {
		
		add_action( 'init', array( $this, 'fn_ictu_gcconf_register_post_types' ) );
		add_action( 'init', 'fn_ictu_gcconf_initialize_acf_fields' );

		add_action( 'plugins_loaded', array( $this, 'fn_ictu_gcconf_load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'fn_ictu_gcconf_add_rewrite_rules' ) );

		add_filter( 'genesis_single_crumb',   array( $this, 'fn_ictu_gcconf_filter_breadcrumb' ), 10, 2 );
		add_filter( 'genesis_page_crumb',     array( $this, 'fn_ictu_gcconf_filter_breadcrumb' ), 10, 2 );
		add_filter( 'genesis_archive_crumb',  array( $this, 'fn_ictu_gcconf_filter_breadcrumb' ), 10, 2 ); 				
		add_filter( 'genesis_tax_crumb',  	  array( $this, 'fn_ictu_gcconf_filter_breadcrumb' ), 10, 2 ); 				

		add_image_size( SPEAKER_IMG_SIZE, 148, 171, true );
		
		// add a page temlate name
		$this->templates 						= array();
		$this->template_conf_overviewpage 		= 'conf-overviewpage.php';
		
		// add the page template to the templates list
		add_filter( 'theme_page_templates',   array( $this, 'fn_ictu_gcconf_add_page_templates' ) );
		
		// activate the page filters
		add_action( 'template_redirect',      array( $this, 'fn_ictu_gcconf_frontend_use_page_template' )  );
		
		// add styling and scripts
		add_action( 'wp_enqueue_scripts',     array( $this, 'fn_ictu_gcconf_register_frontend_style_script' ) );

	}
    
    /** ----------------------------------------------------------------------------------------------------
     * Initialise translations
     */
	public function fn_ictu_gcconf_load_plugin_textdomain() {
		
		fn_ictu_gcconf_load_plugin_textdomain( 'ictu-gcconf-posttypes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
	}

    /** ----------------------------------------------------------------------------------------------------
	* Hides the custom post template for pages on WordPress 4.6 and older
	*
	* @param array $post_templates Array of page templates. Keys are filenames, values are translated names.
	* @return array Expanded array of page templates.
	*/
	function fn_ictu_gcconf_add_page_templates( $post_templates ) {
	
		$post_templates[$this->template_conf_overviewpage]				= _x( 'Conf. Overview page', "naam template",  'ictu-gcconf-posttypes' );    
		return $post_templates;
		
	}

	
    /** ----------------------------------------------------------------------------------------------------
	* custom loop
	*/
	function fn_ictu_gcconf_tax_loop(  ) {
	
		if ( have_posts() ) :
			
			echo '<div class="archive-list grid grid--col-3">';
			
			while ( have_posts() ) : the_post();

				
				// do loop stuff
				$type 			= get_post_type( $post );
				
				$args = array( 
					'ID'		=> get_the_ID(),
					'titletag'	=> 'h2',
				);
				
				if ( $type === ICTU_GCCONF_CPT_SPEAKER ) {
					
					echo $this->fn_ictu_gcconf_frontend_write_speakercard( $args );
					
				}
				elseif ( $type === ICTU_GCCONF_CPT_KEYNOTE ) {
				
					echo $this->fn_ictu_gcconf_frontend_write_keynotecard( $args );
					
				}
				else {
				
					echo $this->fn_ictu_gcconf_frontend_write_sessioncard( $args );
					
				}


			
			endwhile; /** end of one post **/
	
			echo '</div>';
	
			do_action( 'genesis_after_endwhile' );
	
		else : /** if no posts exist **/
			do_action( 'genesis_loop_else' );
		endif; /** end loop **/

	}



    /** ----------------------------------------------------------------------------------------------------
     * Handles the front-end display. 
     *
     * @return void
     */
     
	public function fn_ictu_gcconf_frontend_template_append_blocks() {

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


				// -----------------------------------------------------------------------------------------
				if ( 'events' === $block_extra_type ) {
					
					$posts = get_sub_field( 'block_events' );
					
					if( $posts ):

						foreach( $posts as $post):
						
							setup_postdata($post);

							$section_title 		= get_the_title( $post->ID );
							$title_id			= sanitize_title( $section_title );
			                $EM_Event       	= em_get_event( $post );
							$times 				= $EM_Event->output( '#_EVENTTIMES' );
							$town 				= $EM_Event->output( '#_LOCATIONTOWN' );

							echo '<div class="card no-image card--event" aria-labelledby="' . $title_id . '">';
							echo '<' . $headertitle_tag . ' id="' . $title_id . '"><a href="' . get_permalink( $post->ID ) . '">' . $section_title . '<span class="btn btn--arrow"></span></a></' . $headertitle_tag . '>';

							if ( $times || $town ) {

								echo '<div class="meta">';
								echo '<dl class="dl-time-location">';
								if ( $times ) {
									echo '<dt>' . _x( 'Time', 'ictu-gcconf-posttypes' ) . '</dt><dd class="event-times">' . $times . '</dd> ';	
								}	
								if ( $town ) {
									echo '<dt>' . _x( 'Location', 'ictu-gcconf-posttypes' ) . '</dt><dd class="event-location">' . $town . '</dd> ';	
								}	
								echo '</dl>';
								echo '</div>';
								
							}
													
							echo '<p>';
							echo $EM_Event->output( '#_EVENTEXCERPT{999}' );
							echo '</p>';
							echo '</div>';


						endforeach;
						
						wp_reset_postdata(); 

					endif;
					
				}
				// -----------------------------------------------------------------------------------------
				elseif ( 'keynotes' === $block_extra_type ) {

					$posts = get_sub_field( 'block_keynotes' );

					if( $posts ):

						foreach( $posts as $post):
						
							setup_postdata($post);

							$args = array( 
								'ID'		=> $post->ID,
								'titletag'	=> 'h2',
								'echo'		=> false
							);

//							echo '<h2>' . get_the_title( $post->ID ) . '</h2>';
							echo $this->fn_ictu_gcconf_frontend_write_keynotecard( $args );

						endforeach;
						
						wp_reset_postdata(); 

					endif;
					
				}
				// -----------------------------------------------------------------------------------------
				elseif ( 'speakers' === $block_extra_type ) {

					$posts = get_sub_field( 'block_speakers' );

					if( $posts ):

						echo '<div class="grid grid--col-3 speakers">';

						foreach( $posts as $post):
						
							setup_postdata($post);

							$args = array( 
								'ID'		=> $post->ID,
								'titletag'	=> 'h2',
								'echo'		=> false
							);
							
							echo $this->fn_ictu_gcconf_frontend_write_speakercard( $args );

						endforeach;

						echo '</div>';		
						
						wp_reset_postdata(); 

					endif;
					
				}				
				// -----------------------------------------------------------------------------------------
				elseif ( 'sessions' === $block_extra_type ) {
					
					if( have_rows('block_sessions') ):
					
						// loop through rows (sub repeater)
						while( have_rows('block_sessions') ): the_row();

							$block_sessions_session 			= get_sub_field( 'block_sessions_session' );

							if ( is_object( $block_sessions_session ) ) {

								$block_sessions_session_time 		= get_sub_field( 'block_sessions_session_time' );
								$block_sessions_session_location	= get_sub_field( 'block_sessions_session_location' );

								$args = array( 
									'ID'				=> $block_sessions_session->ID,
									'titletag'			=> 'h2',
									'echo'				=> true,
							        'session_time'		=> $block_sessions_session_time, 
							        'session_location'	=> $block_sessions_session_location, 
									
								);
							
//								echo '<h2>' . get_the_title( $block_sessions_session->ID ) . '</h2>';
								echo $this->fn_ictu_gcconf_frontend_write_sessioncard( $args );
							
							}

						endwhile;
					
					endif;
				
				}
				// -----------------------------------------------------------------------------------------


				echo "\n" . '</div> <!-- // .section-content -->' . "\n";

				echo '</section>' . "\n\n\n";

			endwhile;
			
		}

	}


    //========================================================================================================
    /**
     * Handles the front-end display. 
     *
     * @return void
     */
	public function fn_ictu_gcconf_frontend_append_speakers( $args = [] ) {
		
		global $post;

	    $defaults = array (
	        'echo' 			=> true
	    );

	    $return = '';

	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $post->ID ) {
			return;
		}

		$speakers 					= get_field( 'speakers', $post->ID );
		$speakers_from_pure_acf 	= get_field( 'speaker_session_keynote_relations', $post->ID );
		$speakers_from_post_meta 	= get_post_meta( $post->ID, 'speaker_session_keynote_relations' );

		if ( $speakers || $speakers_from_pure_acf || $speakers_from_post_meta ) {
			$return = '<div class="speakers"><h2 class="visuallyhidden">' . _x( 'Speakers', 'ictu-gcconf-posttypes' ) . '</h2>';
			$return .= '<div class="grid grid--col-2">';

			foreach( $speakers as $speaker ):

				$args2  = array(
							'ID'				=> $speaker->ID,
							'titletag'			=> 'h3',
							'fulldesc'			=> true,						
							'addspeakerlinks'	=> true,	
							'addcountry'		=> true,					
							'echo'				=> false
						);
				$return .= $this->fn_ictu_gcconf_frontend_write_speakercard( $args2 );		
				
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
     * Register frontend styles
     */
	public function fn_ictu_gcconf_register_frontend_style_script( ) {
	
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
	public function fn_ictu_gcconf_frontend_use_page_template() {
		
		global $post;
		
		$page_template  = get_post_meta( get_the_ID(), '_wp_page_template', true );
		
		if ( $this->template_conf_overviewpage == $page_template ) {
			
			remove_filter( 'genesis_post_title_output', 'gc_wbvb_sharebuttons_for_page_top', 15 );

			//* Force full-width-content layout
			add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

			// append content
			add_action( 'genesis_entry_content', 		array( $this, 'fn_ictu_gcconf_frontend_template_append_blocks' ), 12 ); 				

			// 
			add_action( 'genesis_after_entry_content',	array( $this, 'fn_ictu_gcconf_frontend_template_content_for_noblocks_page' ), 15 ); 			

			add_filter( 'genesis_attr_entry',			array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

		}
		elseif ( is_post_type_archive( ICTU_GCCONF_CPT_KEYNOTE ) )  {

			/** Replace the standard loop with our custom loop */
			remove_action( 'genesis_loop', 'genesis_do_loop' );
			remove_action( 'genesis_loop', 'gc_wbvb_archive_loop' );
			
		}
		elseif ( is_tax( ICTU_GCCONF_CT_TIMESLOT ) )  {
			
			//Removes Title and Description on Archive, Taxonomy, Category, Tag
			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			add_action( 'genesis_before_loop', 'gc_wbvb_add_taxonomy_description', 15 );
			
			/** Replace the standard loop with our custom loop */
			remove_action( 'genesis_loop', 'genesis_do_loop' );
			remove_action( 'genesis_loop', 'gc_wbvb_archive_loop' );
			add_action( 'genesis_loop', array( $this, 'fn_ictu_gcconf_tax_loop' ) );
			

		}
		elseif ( ICTU_GCCONF_CPT_SPEAKER == get_post_type( ) )  {

			//* Force full-width-content layout
			add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

			add_filter( 'genesis_attr_entry', 	  		array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

			add_filter( 'genesis_attr_entry-content', 	array( $this, 'fn_ictu_gcconf_add_class_speakerbio_to_entry' ) );

			// Prepend job title
			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_speaker_append_jobtitle' ), 6 ); 		

			// Prepend job title
			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_speaker_append_country' ), 5 ); 		
					
			// append speaker image
			add_action( 'genesis_after_entry_content', 	array( $this, 'fn_ictu_gcconf_frontend_speaker_featured_image' ), 8 ); 		

			// append weblinks
			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_speaker_append_weblinks' ), 12 ); 		
			
					
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {

			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_append_speakers' ), 12 ); 			

			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_sessionkeynote_speakers' ), 20 ); 				
	
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {

			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_append_speakers' ), 12 ); 			

			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_sessionkeynote_speakers' ), 20 ); 				
	
		}


		//=================================================
		
		add_filter( 'genesis_post_info',   array( $this, 'fn_ictu_gcconf_frontend_filter_postinfo' ), 10, 2 );
		
	}

    /** ----------------------------------------------------------------------------------------------------
     * Append country tax to speaker single
     */
	public function fn_ictu_gcconf_frontend_speaker_append_country() {
		
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
	public function fn_ictu_gcconf_frontend_speaker_append_jobtitle() {
		
		global $post;
		
		$jobtitle = get_field( 'speaker_jobtitle', $post->ID );
		
		if ( $jobtitle ) {
			echo '<p class="jobtitle">' . $jobtitle . '</p>';
		}
		
		
	}


    /** ----------------------------------------------------------------------------------------------------
     * Append country tax to speaker single
     */
	public function fn_ictu_gcconf_frontend_append_speaker_weblinks( $args = [] ) {
		
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
	public function fn_ictu_gcconf_frontend_speaker_append_weblinks() {
		
		global $post;
		
//		$this->fn_ictu_gcconf_frontend_append_speaker_weblinks( array( 'ID' => $post->ID, 'sectiontitle' => sprintf( _x( '%s on the web', 'stappen', 'gebruikercentraal' ), get_the_title( $post ) ) ) );
		$this->fn_ictu_gcconf_frontend_append_speaker_weblinks( array( 'ID' => $post->ID, 'sectiontitle' => _x( 'Links', 'Header text speaker links', 'gebruikercentraal' ) ) );
//		$this->fn_ictu_gcconf_frontend_append_speaker_weblinks( array( 'ID' => $post->ID ) );
		
	}


    


    /** ----------------------------------------------------------------------------------------------------
     * Post info: do not write any post info
     */
    public function fn_ictu_gcconf_frontend_filter_postinfo() {
    
    	return '';
  
	}
    

    /** ----------------------------------------------------------------------------------------------------
     * A new version of the_loop for keynotes
     */

		

    /** ----------------------------------------------------------------------------------------------------
     * COMMENT HERE
     */
    public function fn_ictu_gcconf_add_rewrite_rules() {
    
    	return '';
  
	}


    /** ----------------------------------------------------------------------------------------------------
     * COMMENT HERE
     */
    public function fn_ictu_gcconf_frontend_write_keynotecard( $args = [] ) {

	    $defaults = array (
	        'ID'			=> 0, 
	        'titletag'		=> 'h3', 
	        'echo' 			=> true
	    );
	    
	    $return = "\n";

	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $args['ID'] ) {
			return;
		}

		$section_title 						= get_the_title( $args['ID'] );
		$title_id							= sanitize_title( $section_title );
		$excerpt 							= get_the_excerpt( $args['ID'] );
		$metainfo 							= '';
		$speakers 							= get_field('speakers', $args['ID'] );
		$speakernames 						= '';

		if ( $speakers ) {
			$speakercounter = 0;
			$speakernames = '<dl class="dl-speaker-names">';
			$speakernames .= '<dt>';
			
			if ( count( $speakers ) > 1 ) {
				$speakernames .= _x( 'Speakers', 'ictu-gcconf-posttypes' );
			}
			else {
				$speakernames .= _x( 'Speaker', 'ictu-gcconf-posttypes' );
			}
			$speakernames .= '</dt>';
			
			foreach( $speakers as $speaker ):
				$speakercounter++;
				$countrynames = '';
				$speakernames .= '<dd>';
				
				$county_term			= wp_get_post_terms( $speaker->ID, ICTU_GCCONF_CT_COUNTRY );
		
				if ( $county_term && ! is_wp_error( $county_term ) ) { 
					$countrynames	= ' (';
					$countrycounter = 0;
					
					foreach ( $county_term as $term ) {
		
						$countrycounter++;
						
						if ( $countrycounter > 1 ) {
							$countrynames	.= ', ';
						}
						
						$countrynames .=  $term->name;	
					}	
					$countrynames	.= ')';
				}	
				
				
				$speakernames .= get_the_title( $speaker->ID ) . $countrynames;		
				$speakernames .= '</dd>';
			endforeach;
			
			$speakernames .= '</dl>';
		}


		$return = '<div class="card card--keynote" aria-labelledby="' . $title_id . '">';
		$return .= '<' . $args['titletag'] . ' id="' . $title_id . '"><a href="' . get_permalink( $args['ID'] ) . '">' . $section_title . '<span class="btn btn--arrow"></span></a></' . $args['titletag'] . '>';

		if ( $speakers ) {

			$return .= '<span class="speaker-image">';
			foreach( $speakers as $speaker ):

				if ( has_post_thumbnail( $speaker->ID ) ) {
					$return .= get_the_post_thumbnail( $speaker->ID, SPEAKER_IMG_SIZE, array( 'class' => 'speaker-thumbnail thumbnail alignleft' ) );
				}
				else {
					// 
					$arr_speaker_images	= get_field('fallback_for_speaker_images', 'option');
					$randomid = array_rand( $arr_speaker_images, 1 );
					$return .= wp_get_attachment_image( $arr_speaker_images[ $randomid ], SPEAKER_IMG_SIZE, false, array( 'class' => 'speaker-thumbnail thumbnail alignleft' ) );
				}

			endforeach;
			
			$return .= '</span>';
			$return .= '<span class="speaker-bio">';
			if ( $speakernames ) {
				$return .= '<div class="meta">' . $speakernames . '</div>';
			}
			$return .= wp_strip_all_tags( $excerpt );
			$return .= '</span>';
			
		}	
		else {
			$return .= '<span class="speaker-bio">';
			$return .= wp_strip_all_tags( $excerpt );
			$return .= '</span>';
		}

		if ( $metainfo ) {
			$return .= '<div class="meta">' . $metainfo . '</div>';
		}
		
		$return .= '</div>' .  "\n\n\n";		
  
		if ( $args['echo'] ) {
			echo $return;
		}
		else {
			return $return;
		}

	}


    /** ----------------------------------------------------------------------------------------------------
     * COMMENT HERE
     */
    public function fn_ictu_gcconf_frontend_write_sessioncard( $args = [] ) {

	    $defaults = array (
	        'ID'				=> 0, 
	        'session_time'		=> null, 
	        'session_location'	=> null, 
	        'titletag'			=> 'h3', 
	        'echo' 				=> true
	    );

	    $return = '';

	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $args['ID'] ) {
			return;
		}

		$time_term 							= get_term_by( 'id', $args['session_time'], ICTU_GCCONF_CT_TIMESLOT );
		$location_term						= get_term_by( 'id', $args['session_location'], ICTU_GCCONF_CT_LOCATION );
		
		$speakers 							= get_field( 'speakers', $args['ID'] );
		$section_title 						= get_the_title( $args['ID'] );
		$title_id							= sanitize_title( $section_title );

		$metainfo 							= '';

		if ( $time_term || $location_term ) {
			
			$metainfo .= '<dl class="dl-time-location">';
			if ( $time_term ) {
				$metainfo .= '<dt>' . _x( 'Time', 'ictu-gcconf-posttypes' ) . '</dt><dd class="event-times">' . $time_term->name . '</dd> ';	
			}	
			if ( $location_term ) {
				$metainfo .= '<dt>' . _x( 'Location', 'ictu-gcconf-posttypes' ) . '</dt><dd class="event-location">' . $location_term->name . '</dd> ';	
			}	
			$metainfo .= '</dl>';
			
		}

		if ( $speakers ) {
			$speakercounter = 0;
			$metainfo .= '<dl class="dl-speaker-names">';
			$metainfo .= '<dt>';
			
			if ( count( $speakers ) > 1 ) {
				$metainfo .= _x( 'Speakers', 'ictu-gcconf-posttypes' );
			}
			else {
				$metainfo .= _x( 'Speaker', 'ictu-gcconf-posttypes' );
			}
			$metainfo .= '</dt>';
			
			foreach( $speakers as $speaker ):
				$speakercounter++;
				$metainfo .= '<dd>';
				$metainfo .= get_the_title( $speaker->ID );		
				$metainfo .= '</dd>';
			endforeach;
			
			$metainfo .= '</dl>';
		}


		$return .= '<div class="card card--session" aria-labelledby="' . $title_id . '">';
		$return .= '<' . $args['titletag'] . ' id="' . $title_id . '"><a href="' . get_permalink( $args['ID'] ) . '">' . $section_title . '<span class="btn btn--arrow"></span></a></' . $args['titletag'] . '>';

		if ( $metainfo ) {
			$return .= '<div class="meta">' . $metainfo . '</div>';
		}
		
		$excerpt = get_the_excerpt( $args['ID'] );
		$return .= wp_strip_all_tags( $excerpt );
		
		$return .= '</div>';		
  
		if ( $args['echo'] ) {
			echo $return;
		}
		else {
			return $return;
		}
  
	}


    /** ----------------------------------------------------------------------------------------------------
     * COMMENT HERE
     */
    public function fn_ictu_gcconf_frontend_write_speakercard( $args = [] ) {

	    $defaults = array (
	        'ID'							=> 0, 
	        'titletag'						=> 'h3', 
	        'titlelink'						=> true, 
	        'fulldesc'						=> false, 
	        'addspeakerlinks'				=> false, 
	        'addcountry'					=> false,
	        'echo' 							=> true,
	        'speakerlinks_sectiontitletag' 	=> '',
	        'speakerlinks_sectiontitle' 	=> ''
	    );
	    $return = '';

	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $args['ID'] ) {
			return;
		}

		$extraattr 			= array( 'class' => 'speaker-thumbnail thumbnail alignleft' );
		
		$speaker_jobtitle 	= get_field( 'speaker_jobtitle', $args['ID'] );

		if ( has_post_thumbnail( $args['ID'] ) ) {
			$image			= get_the_post_thumbnail( $args['ID'], SPEAKER_IMG_SIZE, $extraattr );
		}
		else {
			$arr_speaker_images = get_field('fallback_for_speaker_images', 'option');
			$randomid		= array_rand( $arr_speaker_images, 1 );
			$image			= wp_get_attachment_image( $arr_speaker_images[ $randomid ], SPEAKER_IMG_SIZE, false, $extraattr );
		}
		$section_title 		= get_the_title( $args['ID'] );
		$title_id			= sanitize_title( $section_title . '-' . $args['ID'] );
		
		$return				= '<div class="card card--speaker" id="' . $title_id . '">' . $image;
		$return			   .= '<div class="speaker-info">';
		$return			   .= '<' . $args['titletag'] . '>';

		if ( $args['titlelink'] ) {
			$return		   .= '<a href="' . get_permalink( $args['ID'] ) . '">';
			$return		   .= $section_title;
			$return		   .= '</a>';
		}
		else {
			$return		   .= $section_title;
		}		
		$return			   .= '</' . $args['titletag'] . '>';
		

		if ( $args['addcountry'] ) {

			$county_term			= wp_get_post_terms( $args['ID'], ICTU_GCCONF_CT_COUNTRY );
	
			if ( $county_term && ! is_wp_error( $county_term ) ) { 
				$return	   .= '<p class="speaker-country">';
				$countrycounter = 0;
				
				foreach ( $county_term as $term ) {
	
					$countrycounter++;
					
					if ( $countrycounter > 1 ) {
						$return	.= ', ';
					}
					
					$return .=  $term->name;	
				}	
				$return	   .= '</p>';
			}	
						
		}
		if ( $args['fulldesc'] ) {
			$excerpt 		= get_the_excerpt( $args['ID'] );
			$return		   .= '<p class="excerpt">' . wp_strip_all_tags( $excerpt ) . '</p>';
		}
		else {
			$return		   .= '<p class="speaker-jobtitle">' . $speaker_jobtitle . '</p>';
		}

		if ( $args['addspeakerlinks'] ) {
			
			if( have_rows('speaker_links', $args['ID'] ) ) {
	
				$return .= '<div class="speaker-links">';		
				if ( $args['speakerlinks_sectiontitle'] ) {
					$return .= '<' . $args['speakerlinks_sectiontitletag'] . '>' . $args['speakerlinks_sectiontitle'] . '</' . $args['speakerlinks_sectiontitletag'] . '>';		
				}
				$return .= '<ul class="social-media speaker">';		
			
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
			
			}
		}
	
		
		$return		   	   .= '</div>';
		$return			   .= '<span class="verziering">&nbsp;</<span>';
		$return		   	   .= '</div>';

		if ( $args['echo'] ) {
			echo $return;
		}
		else {
			return $return;
		}
  
	}

    /** ----------------------------------------------------------------------------------------------------
     * Add content to the content 
     * \0/
     */
	public function fn_ictu_gcconf_frontend_template_content_for_noblocks_page( ) {


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

			$args = array();
			
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
					'order'                 =>  'ASC',
					'orderby'               =>  'post_title'
				  );

				$sidebarposts = new WP_query( $args );
		
				if ($sidebarposts->have_posts()) {

					$colcount = 'grid--col-2';

					if ( $type === ICTU_GCCONF_CPT_SPEAKER ) {
						$colcount = 'grid--col-3';
					}					

					echo '<div class="grid ' . $colcount . ' ' . $type . '">';
					
					while ( $sidebarposts->have_posts() ) : $sidebarposts->the_post();
					
						$postcounter++;

						$args = array( 
							'ID'		=> $post->ID,
							'titletag'	=> 'h2',
						);

						if ( $type === ICTU_GCCONF_CPT_SPEAKER ) {
							
							echo $this->fn_ictu_gcconf_frontend_write_speakercard( $args );
							
						}
						elseif ( $type === ICTU_GCCONF_CPT_KEYNOTE ) {

							echo $this->fn_ictu_gcconf_frontend_write_keynotecard( $args );
							
						}
						else {

							echo $this->fn_ictu_gcconf_frontend_write_sessioncard( $args );
							
						}

					endwhile;
					
					echo '</div>';
				
				}					
				
				wp_reset_query();
				
			}
		
		}
		
		if ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) || is_singular( 'page' ) ) {
		// 
		}

	}

    
    /** ----------------------------------------------------------------------------------------------------
     * Prepends a title before the content
     */
    public function fn_ictu_gcconf_frontend_speaker_featured_image() {
	    
	    global $post;

		if ( has_post_thumbnail( $post ) ) {
			echo '<span class="speaker-image">';
			echo get_the_post_thumbnail( $post, SPEAKER_IMG_SIZE, array( 'class' => 'speaker-thumbnail thumbnail alignright' ) );
			echo '</span>';
		}

		
	}


    
    /** ----------------------------------------------------------------------------------------------------
     * Prepends a title before the content
     */
    public function fn_ictu_gcconf_frontend_sessionkeynote_speakers() {
	    
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
     * Do actually register the post types we need
     */
    public function fn_ictu_gcconf_register_post_types() {
      
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


    /** ----------------------------------------------------------------------------------------------------
     * filter the breadcrumb
     */
	public function fn_ictu_gcconf_filter_breadcrumb( $crumb = '', $args = '' ) {
		
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
	function fn_ictu_gcconf_add_class_inleiding_to_entry( $attributes ) {
		$attributes['class'] .= ' inleiding';
		return $attributes;
	}
	
    
    /** ----------------------------------------------------------------------------------------------------
     * add extra class to page template .entry
     */
	function fn_ictu_gcconf_add_class_speakerbio_to_entry( $attributes ) {
		$attributes['class'] .= ' speaker-bio';
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
