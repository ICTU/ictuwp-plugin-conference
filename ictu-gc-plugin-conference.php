<?php

/**
 * @link                https://wbvb.nl
 * @package             ictu-gc-plugin-conference
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Conference post types and taxonomies
 * Plugin URI:          https://github.com/ICTU/Gebruiker-Centraal---Inclusie---custom-post-types-taxonomies
 * Description:         Plugin for conference.gebruikercentraal.nl to register custom post types and custom taxonomies
 * Version:             1.1.3
 * Version description: Better layout for terms in meta information.
 * Author:              Paul van Buuren
 * Author URI:          https://wbvb.nl/
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         ictu-gc-plugin-conference
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//========================================================================================================

add_action( 'plugins_loaded', array( 'ICTU_GC_conference', 'init' ), 10 );

//========================================================================================================

define( 'ICTU_GC_CONF_ARCHIVE_CSS',	'ictu-gcconf-archive-css' );  
define( 'ICTU_GC_CONF_BASE_URL',    trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'ICTU_GC_CONF_ASSETS_URL',	trailingslashit( ICTU_GC_CONF_BASE_URL ) );
define( 'ICTU_GC_CONF_VERSION',		'1.1.3' );

if ( ! defined( 'ICTU_GCCONF_CPT_SPEAKER' ) ) {
  define( 'ICTU_GCCONF_CPT_SPEAKER', 'speaker' );   // slug for custom taxonomy 'speaker'
}

if ( ! defined( 'ICTU_GCCONF_CPT_SESSION' ) ) {
  define( 'ICTU_GCCONF_CPT_SESSION', 'session' );   // slug for custom taxonomy 'session' (i.e. workshop)
}

if ( ! defined( 'ICTU_GCCONF_CPT_KEYNOTE' ) ) {
  define( 'ICTU_GCCONF_CPT_KEYNOTE', 'keynote' );  // slug for custom post type 'keynote'
}

if ( ! defined( 'ICTU_GCCONF_CT_TIMESLOT' ) ) {
  define( 'ICTU_GCCONF_CT_TIMESLOT', 'timeslot' );  // slug for custom taxonomy 'timeslot'
}

if ( ! defined( 'ICTU_GCCONF_CT_LOCATION' ) ) {
  define( 'ICTU_GCCONF_CT_LOCATION', 'location' );  // slug for custom taxonomy 'location'
}

if ( ! defined( 'ICTU_GCCONF_CT_SESSIONTYPE' ) ) {
  define( 'ICTU_GCCONF_CT_SESSIONTYPE', 'sessiontype' );  // slug for custom taxonomy 'sessiontype'
}

if ( ! defined( 'ICTU_GCCONF_CT_LEVEL' ) ) {
  define( 'ICTU_GCCONF_CT_LEVEL', 'expertise' );  // slug for custom taxonomy 'expertise' (workshop level)
}

if ( ! defined( 'ICTU_GCCONF_CT_COUNTRY' ) ) {
  define( 'ICTU_GCCONF_CT_COUNTRY', 'speakercountry' );  // slug for custom taxonomy for a speaker's country
}

if ( ! defined( 'SPEAKER_IMG_SIZE' ) ) {
  define( 'SPEAKER_IMG_SIZE', 'speaker-image-size' );
}

define( 'CONF_DEBUG', false );
// define( 'CONF_DEBUG', true );


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

		// make sure the breadcrumb is useful for our CPTs
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
		
		fn_ictu_gcconf_load_plugin_textdomain( 'ictu-gc-plugin-conference', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
	}

    /** ----------------------------------------------------------------------------------------------------
	* Hides the custom post template for pages on WordPress 4.6 and older
	*
	* @param array $post_templates Array of page templates. Keys are filenames, values are translated names.
	* @return array Expanded array of page templates.
	*/
	function fn_ictu_gcconf_add_page_templates( $post_templates ) {
	
		$post_templates[$this->template_conf_overviewpage]				= _x( 'Conf. Overview page', "naam template",  'ictu-gc-plugin-conference' );    
		return $post_templates;
		
	}

	
	/** ----------------------------------------------------------------------------------------------------
	* Add a custom loop, used by these CPTs:
	* ICTU_GCCONF_CT_LOCATION
	* ICTU_GCCONF_CT_SESSIONTYPE
	* ICTU_GCCONF_CT_LEVEL
	* ICTU_GCCONF_CT_COUNTRY
	* ICTU_GCCONF_CT_TIMESLOT
	*
	* @return void
	*/
	function fn_ictu_gcconf_tax_loop(  ) {

		global $post;		

		$colcount = 'grid--col-3';

		if ( 
			( is_tax( ICTU_GCCONF_CT_LOCATION ) ) ||
			( is_tax( ICTU_GCCONF_CT_SESSIONTYPE ) ) ||
			( is_tax( ICTU_GCCONF_CT_LEVEL ) ) ||
			( is_tax( ICTU_GCCONF_CT_COUNTRY ) ) ||
			( is_tax( ICTU_GCCONF_CT_TIMESLOT ) ) 
		)  {
			$colcount = 'grid--col-2';
		}	

		if ( have_posts() ) :
			
			echo '<div class="archive-list grid ' . $colcount . '">';
			
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
	* For the 'conf-overviewpage.php' template, add the extra content blocks
	* type of blocks:
	* - 'events' (events through the Events Manager plugin)
	* - 'keynotes' 
	* - 'speakers'
	* - 'sessions'
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
			                $my_em_event       	= em_get_event( $post );
							$times 				= $my_em_event->output( '#_EVENTTIMES' );
							$town 				= $my_em_event->output( '#_LOCATIONTOWN' );

							echo '<div class="card no-image card--event" aria-labelledby="' . $title_id . '">';
							echo '<' . $headertitle_tag . ' id="' . $title_id . '"><a href="' . get_permalink( $post->ID ) . '">' . $section_title . '<span class="btn btn--arrow"></span></a></' . $headertitle_tag . '>';

							if ( $times || $town ) {

								echo '<div class="meta">';
								echo '<dl class="dl-time-location">';
								if ( $times ) {
									echo '<dt>' . _x( 'Time', 'Event times', 'ictu-gc-plugin-conference' ) . '</dt><dd class="event-times">' . $times . '</dd> ';	
								}	
								if ( $town ) {
									echo '<dt>' . _x( 'Location', 'Event city', 'ictu-gc-plugin-conference' ) . '</dt><dd class="event-location">' . $town . '</dd> ';	
								}	
								echo '</dl>';
								echo '</div>';
								
							}
													
							echo '<p>';
							echo $my_em_event->output( '#_EVENTEXCERPT{999}' );
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
	* Adds the connected speaker(s) for a session or keynote
	*
	* @param array $args
	*
	* @return void ($args['echo'] = true) or $return (HTML)
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

		$list_of_speakers = get_field( 'speaker_session_keynote_relations', $post->ID );

		if ( ! $list_of_speakers ) {
			// extra safety
			$list_of_speakers = get_field( 'speakers', $post->ID );
		}

		if ( $list_of_speakers ) {
			
			$return .= '<div class="speakers"><h2 class="visuallyhidden">' . _x( 'Speakers', 'speaker type', 'ictu-gc-plugin-conference' ) . '</h2>';

			foreach( $list_of_speakers as $speaker ):

				$args2  = array(
							'ID'				=> $speaker,
							'titletag'			=> 'h3',
							'fulldesc'			=> true,						
							'addcountry'		=> true,					
							'echo'				=> false,
							'cardcss'			=> 'card--authorbox no-line'
						);
						
				$return .= $this->fn_ictu_gcconf_frontend_write_speakercard( $args2 );		
				
			endforeach;

			$return .= '</div>';
			
		}
		else {
			$return .= '<p></div>';
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
	* Register frontend stylesheet. Dummy code to enable inline CSS in header should that be necessary
	*
	* @return void
	*/
	public function fn_ictu_gcconf_register_frontend_style_script( ) {
	
		global $post;
	
		$infooter = true;
		
		wp_enqueue_style( ICTU_GC_CONF_ARCHIVE_CSS, trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/frontend-conf.css', array(), ICTU_GC_CONF_VERSION, 'all' );
		/*
		$header_css     = '';
		$acfid          = get_the_id();
		$page_template  = get_post_meta( $acfid, '_wp_page_template', true );
		
		if ( !is_admin() && ( $this->template_conf_overviewpage == $page_template ) ) {
			
			if( have_rows('home_template_keynotes') ):

				
			endif; 
			
		}


		if ( $header_css ) {
			wp_add_inline_style( ICTU_GC_CONF_ARCHIVE_CSS, $header_css );
		}
		*/

		
    }

    //========================================================================================================

    /**
    * Modify page content if using a specific page template.
	*
	* @return void
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
			// add extra class, to make the title BIGGERDER
			add_filter( 'genesis_attr_entry', 	  		array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

		}
		elseif ( is_post_type_archive( ICTU_GCCONF_CPT_KEYNOTE ) )  {

			/** Replace the standard loop with our custom loop */
			remove_action( 'genesis_loop', 'genesis_do_loop' );
			remove_action( 'genesis_loop', 'gc_wbvb_archive_loop' );
			
		}
		elseif ( 
			( is_tax( ICTU_GCCONF_CT_LOCATION ) ) ||
			( is_tax( ICTU_GCCONF_CT_SESSIONTYPE ) ) ||
			( is_tax( ICTU_GCCONF_CT_LEVEL ) ) ||
			( is_tax( ICTU_GCCONF_CT_COUNTRY ) ) ||
			( is_tax( ICTU_GCCONF_CT_TIMESLOT ) ) 
			)  {
			
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

			// add extra class, to make the title BIGGERDER
			add_filter( 'genesis_attr_entry', 	  		array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

			// append speaker image
			add_action( 'genesis_entry_content', 		array( $this, 'fn_ictu_gcconf_frontend_speaker_featured_image' ), 6 ); 		

			// append speaker country info
			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_speaker_append_country' ), 7 ); 			

			// append weblinks
			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_speaker_append_weblinks' ), 12 ); 		
			
					
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {

			// add extra class, to make the title BIGGERDER
			add_filter( 'genesis_attr_entry', 	  		array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_sessionkeynote_location_time' ), 8 );
			
			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_append_speakers' ), 12 ); 			
	
		}
		elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {

			// add extra class, to make the title BIGGERDER
			add_filter( 'genesis_attr_entry', 	  		array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_sessionkeynote_location_time' ), 8 );
			
			add_action( 'genesis_entry_content',  		array( $this, 'fn_ictu_gcconf_frontend_append_speakers' ), 12 ); 			
	
		}


		//=================================================
		
		add_filter( 'genesis_post_info',   array( $this, 'fn_ictu_gcconf_frontend_filter_postinfo' ), 10, 2 );
		
	}

    /** ----------------------------------------------------------------------------------------------------
     * Append country taxonomy to speaker single
	*
	* @return string $return 
	*/
	public function fn_ictu_gcconf_frontend_speaker_append_country() {
		
		global $post;

		$return			= '';
		$country		= '';

		$countrycounter = 0;
		$county_term	= wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_COUNTRY );
		$jobtitle 		= get_field( 'speaker_jobtitle', $post->ID );
		
		if ( $county_term && ! is_wp_error( $county_term ) ) { 

			foreach ( $county_term as $term ) {

				$countrycounter++;
				
				if ( $countrycounter > 1 ) {
					$country .= ', ';
				}
				
				$country .= $term->name;	
			}	
		}	

		if ( $jobtitle || $country ) {

			$return = '<p class="speaker-country-jobtitle">';
			
			if ( $jobtitle && $country ) {
				$return .= $jobtitle . ' - ' . $country;
			}
			else {
				$return .= $jobtitle . $country;
			}
			
			$return .= '</p>';
		}

		echo $return;

	}

    /** ----------------------------------------------------------------------------------------------------
	* Append related keynotes or sessions to speaker single
	*
	* @param array $args
	*
	* @return void ($args['echo'] = true) or $return (HTML)
	*/
	public function fn_ictu_gcconf_frontend_speaker_append_links_sessions_keynotes( $args = [] ) {
		
		global $post;

		$return = '';

	    $defaults = array (
	        'ID'					=> 0,
	        'addkeynotessessions'	=> true,
	        'sectiontitle' 			=> false,
	        'titletag'				=> 'h2',
	        'echo' 					=> true
	    );
	     
	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $args['ID'] ) {
			return;
		}

		if ( $args['addkeynotessessions'] ) {

			$titlea 			= '';		
			$titlearray 		= array();		
			$keynotessessions 	= '<div class="archive-list grid">';
			$objects 			= get_field( 'speaker_session_keynote_relations', $args['ID'] );
				
			if ( $objects ) {
	
				foreach( $objects as $post):
					
					$posttype	= get_post_type($post );
					$args2 		= array( 
						'ID'			=> get_the_ID(),
						'titletag'		=> 'h3',
						'echo'			=> false,
						'speakerimage'	=> false,
						'speakernames'	=> false,
					);

					if ( $posttype === ICTU_GCCONF_CPT_KEYNOTE ) {
					
						$keynotessessions .=  $this->fn_ictu_gcconf_frontend_write_keynotecard( $args2 );
						
					}
					else {
					
						$keynotessessions .=  $this->fn_ictu_gcconf_frontend_write_sessioncard( $args2 );
						
					}

				endforeach;
	
			}			

			$keynotessessions .= '</div>';

			wp_reset_postdata(); 			

			$return .= $keynotessessions;		

		}			

		if( have_rows('speaker_links', $args['ID'] ) ):

			$return .= '<div class="speaker-links">';		
			
			if ( $args['sectiontitle'] ) {

				$return .= '<' . $args['titletag'] . '>' . sprintf( __( 'Find %s on social media', 'gebruikercentraal' ), get_the_title( get_the_ID() )  ) . '</' . $args['titletag'] . '>';

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
	* Append any links / socmed profiles to speaker single
	*
	* @return void
	*/
	public function fn_ictu_gcconf_frontend_speaker_append_weblinks() {
		
		global $post;
		echo $this->fn_ictu_gcconf_frontend_speaker_append_links_sessions_keynotes( array( 'ID' => $post->ID, 'echo' => false, 'sectiontitle' => _x( 'Links', 'Header text speaker links', 'gebruikercentraal' ) ) );
		echo '</span>'; // .speaker-bio
		
	}


    /** ----------------------------------------------------------------------------------------------------
	* Post info: do not write any post info
	*
	* @return void
	*/
    public function fn_ictu_gcconf_frontend_filter_postinfo() {
    
    	return '';
  
	}

    /** ----------------------------------------------------------------------------------------------------
	* Add rewrite rules if necessary (hint: not necessary for this plugin)
	*
	* @return void
	*/
    public function fn_ictu_gcconf_add_rewrite_rules() {
    
    	return '';
  
	}


	/** ----------------------------------------------------------------------------------------------------
	* Show speaker(s) for a keynote
	*
	* @param array $args
	*
	* @return void ($args['echo'] = true) or $return (HTML)
	*/
    public function fn_ictu_gcconf_frontend_write_keynotecard( $args = [] ) {

	    $defaults = array (
	        'ID'			=> 0, 
	        'titletag'		=> 'h3', 
	        'speakerimage'	=> true,
	        'speakernames'	=> true,
	        'echo' 			=> true
	    );
	    
	    $return = "\n";

	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $args['ID'] ) {
			return;
		}

		fn_ictu_gcconf_extra_update_speaker_relationfield( $args['ID'] );

		$section_title 		= get_the_title( $args['ID'] );
		$title_id			= sanitize_title( $section_title );
		$excerpt 			= get_the_excerpt( $args['ID'] );
		$metainfo_time		= '';
		$list_of_speakers 	= get_field( 'speaker_session_keynote_relations', $args['ID'] );
		$speakernames 		= '';

		$time_term			= wp_get_post_terms( $args['ID'], ICTU_GCCONF_CT_TIMESLOT );
		$location_term		= wp_get_post_terms( $args['ID'], ICTU_GCCONF_CT_LOCATION );
		

		if ( $time_term || $location_term ) {

			$time_term_counter = 0;
			$location_term_counter = 0;
			
			$metainfo_time = '<dl class="dl-time-location">';

			if ( $time_term && ! is_wp_error( $time_term ) ) {
				$metainfo_time .= '<dt>' . _x( 'Time', 'Event times', 'ictu-gc-plugin-conference' ) . '</dt>';

			    foreach ( $time_term as $term ) {
				    $time_term_counter++;
				    $parentname = '';
				    if ( $location_term_counter > 1 ) {
						$metainfo_time .= '<dd class="event-times">, ' . $parentname . $term->name . '</dd> ';	
				    }
				    else {
						$metainfo_time .= '<dd class="event-times">' . $parentname . $term->name . '</dd> ';	
				    }
					
			    }

			}	
			if ( $location_term && ! is_wp_error( $location_term ) ) {
				$metainfo_time .= '<dt>' . _x( 'Session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ) . '</dt>';

			    foreach ( $location_term as $term ) {
				    $location_term_counter++;
				    if ( $location_term_counter > 1 ) {
						$metainfo_time .= '<dd class="event-location">, ' . $term->name . '</dd> ';	
				    }
				    else {
						$metainfo_time .= '<dd class="event-location">' . $term->name . '</dd> ';	
				    }
					
			    }

			}	
			
			$metainfo_time .= '</dl>';
			
		}

		if ( $list_of_speakers && $args['speakernames'] ) {

			$speakercounter = 0;
			$speakernames = '<dl class="dl-speaker-names keynote">';
			$speakernames .= '<dt>';
			
			if ( count( $list_of_speakers ) > 1 ) {
				$speakernames .= _x( 'Speakers', 'speaker type', 'ictu-gc-plugin-conference' );
			}
			else {
				$speakernames .= _x( 'Speaker', 'speaker type', 'ictu-gc-plugin-conference' );
			}
			$speakernames .= '</dt>';
			
			foreach( $list_of_speakers as $speaker ):

				$speakercounter++;
				$countrynames = '';
				$speakernames .= '<dd>';
				
				$county_term			= wp_get_post_terms( $speaker, ICTU_GCCONF_CT_COUNTRY );
		
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
				
				
				$speakernames .= get_the_title( $speaker ) . $countrynames;		
				$speakernames .= '</dd>';

			endforeach;
			
			$speakernames .= '</dl>';
		}


		$return = '<div class="card card--keynote" aria-labelledby="' . $title_id . '">';
		$return .= '<' . $args['titletag'] . ' id="' . $title_id . '"><a href="' . get_permalink( $args['ID'] ) . '">' . $section_title . '<span class="btn btn--arrow"></span></a></' . $args['titletag'] . '>';

		if ( $list_of_speakers && $args['speakerimage'] ) {

			$return .= '<span class="speaker-image">';

			foreach( $list_of_speakers as $speaker ):

				if ( has_post_thumbnail( $speaker ) ) {
					$return .= get_the_post_thumbnail( $speaker, SPEAKER_IMG_SIZE, array( 'class' => 'speaker-thumbnail thumbnail alignleft' ) );
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
			if ( $speakernames || $metainfo_time ) {
				$return .= '<div class="meta">' . $speakernames . $metainfo_time . '</div>';
			}
			$return .= wp_strip_all_tags( $excerpt );
			$return .= '</span>';
			
		}	
		else {
			$return .= '<span class="speaker-bio">';
			$return .= wp_strip_all_tags( $excerpt );
			$return .= '</span>';
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
	* Show speaker(s) for a session
	*
	* @param array $args
	*
	* @return void ($args['echo'] = true) or $return (HTML)
	*/
    public function fn_ictu_gcconf_frontend_write_sessioncard( $args = [] ) {

	    $defaults = array (
	        'ID'				=> 0, 
	        'session_time'		=> null, 
	        'session_location'	=> null, 
			'speakerimage'		=> true,
			'speakernames'		=> true,
	        'titletag'			=> 'h3', 
	        'echo' 				=> true
	    );

	    $return = '';

	    // Parse incoming $args into an array and merge it with $defaults
	    $args = wp_parse_args( $args, $defaults );

		if ( ! $args['ID'] ) {
			return;
		}

		fn_ictu_gcconf_extra_update_speaker_relationfield( $args['ID'] );

		$time_term 			= get_term_by( 'id', $args['session_time'], ICTU_GCCONF_CT_TIMESLOT );
		$location_term		= get_term_by( 'id', $args['session_location'], ICTU_GCCONF_CT_LOCATION );
		
		$list_of_speakers 	= get_field( 'speaker_session_keynote_relations', $args['ID'] );
		$section_title 		= get_the_title( $args['ID'] );
		$title_id			= sanitize_title( $section_title );

		$metainfo			= '';

		if ( $time_term || $location_term ) {
			
			$metainfo .= '<dl class="dl-time-location">';
			if ( $time_term ) {
				$metainfo .= '<dt>' . _x( 'Timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ) . '</dt><dd class="event-times">' . $time_term->name . '</dd> ';	
			}	
			if ( $location_term ) {
				$metainfo .= '<dt>' . _x( 'Session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ) . '</dt><dd class="event-location">' . $location_term->name . '</dd> ';	
			}	
			$metainfo .= '</dl>';
			
		}

		if ( $list_of_speakers && $args['speakernames'] ) {
			$speakercounter = 0;
			$metainfo .= '<dl class="dl-speaker-names session">';
			$metainfo .= '<dt>';
			
			if ( count( $list_of_speakers ) > 1 ) {
				$metainfo .= _x( 'Speakers', 'speaker type', 'ictu-gc-plugin-conference' );
			}
			else {
				$metainfo .= _x( 'Speaker', 'speaker type', 'ictu-gc-plugin-conference' );
			}
			$metainfo .= '</dt>';
			
			foreach( $list_of_speakers as $speaker ):
				$speakercounter++;
				$metainfo .= '<dd>';
				$metainfo .= get_the_title( $speaker );		
				if ( ( $speakercounter < count( $list_of_speakers ) ) && ( count( $list_of_speakers ) > 1 ) ) {
					$metainfo .= ',&nbsp;';		
				}
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
	* Show extra info about a speaker
	*
	* @param array $args
	*
	* @return void ($args['echo'] = true) or $return (HTML)
	*/
    public function fn_ictu_gcconf_frontend_write_speakercard( $args = [] ) {

	    $defaults = array (
	        'ID'							=> 0, 
	        'titletag'						=> 'h3', 
	        'titlelink'						=> true, 
	        'fulldesc'						=> false, 
	        'addspeakerlinks'				=> false, 
	        'addcountry'					=> false,
	        'addkeynotessessions'			=> false,
	        'echo' 							=> true,
	        'cardcss'						=> 'card--speaker',
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
		
		$jobtitle 			= get_field( 'speaker_jobtitle', $args['ID'] );
		$objects 			= get_field( 'speaker_session_keynote_relations', $args['ID'] );

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
		
		$return				= '<div class="card ' . $args['cardcss'] . '" id="' . $title_id . '">' . $image;
		$return			   .= '<div class="speaker-info">';
		$return			   .= '<' . $args['titletag'] . '>';

		if ( $args['titlelink'] ) {
			$return		   .= '<a href="' . get_permalink( $args['ID'] ) . '">';
			$return		   .= $section_title;

			if ( 'card--authorbox no-line' === $args['cardcss'] ) {
				$return	   .= '<span class="btn btn--arrow"></span>';
			}

			$return		   .= '</a>';
		}
		else {
			$return		   .= $section_title;
		}		
		$return			   .= '</' . $args['titletag'] . '>';
		

		if ( $args['addcountry'] ) {

			$county_term			= wp_get_post_terms( $args['ID'], ICTU_GCCONF_CT_COUNTRY );
	
			if ( $county_term && ! is_wp_error( $county_term ) ) { 

				$country = '';
				$countrycounter = 0;
				
				foreach ( $county_term as $term ) {
	
					$countrycounter++;
					
					if ( $countrycounter > 1 ) {
						$country	.= ', ';
					}
					
					$country .=  $term->name;	
				}	


				if ( $jobtitle || $country ) {
		
					$return .= '<p class="speaker-country-jobtitle">';
					
					if ( $jobtitle && $country ) {
						$return .= $jobtitle . ' - ' . $country;
					}
					else {
						$return .= $jobtitle . $country;
					}
					
					$return .= '</p>';
				}
			}	
		}
		
		if ( $args['fulldesc'] ) {

			$excerpt 		= get_the_excerpt( $args['ID'] );
			$return		   .= '<p class="excerpt">' . wp_strip_all_tags( $excerpt ) . '</p>';

		}
		else {
			
			$return		   .= '<p class="speaker-jobtitle">' . $jobtitle . '</p>';

		}


		if ( $args['addkeynotessessions'] ) {
			
			$objects = get_field( 'speaker_session_keynote_relations', $args['ID'] );
			$return .= '<h3 class="visuallyhidden">Links</h3><ul>';		
				
			foreach( $objects as $post):

				$return .= '<li><a href="' . get_the_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></li>';		

			endforeach;

			$return .= '</ul>';		
			
			wp_reset_postdata(); 			

		}			
			
		if ( $args['addspeakerlinks'] ) {
			
			if( have_rows('speaker_links', $args['ID'] ) ) {
	
				$return .= '<div class="speaker-links">';		
				if ( $args['speakerlinks_sectiontitle'] ) {

					$return .= '<' . $args['speakerlinks_sectiontitletag'] . '>' . sprintf( __( 'Find %s on social media', 'gebruikercentraal' ), get_the_title( get_the_ID() )  ) . '</' . $args['speakerlinks_sectiontitletag'] . '>';

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
		$return			   .= '<span class="diagonal-bg-block">&nbsp;</<span>';
		$return		   	   .= '</div>';

		if ( $args['echo'] ) {
			echo $return;
		}
		else {
			return $return;
		}
  
	}

    /** ----------------------------------------------------------------------------------------------------
	* Show ALL available content for a certain posttype.
	* First we check to see if the page we are looking at is 1 of the 3 pages in the site's theme
	* settings designated to perform a special role ('themesettings_conference_speakers', etc)
	* if this page has NO special blocks with selected content, then we will list all
	* content for this particular content type
	* \0/
	*
	* @return void
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
			
			// what kind of content are we looking at?
			if ( $speaker_page->ID === $the_id ) {
				// it is the speaker page
				$docheck	= true;	
				$type		= ICTU_GCCONF_CPT_SPEAKER;
			}
			elseif ( $keynote_page->ID === $the_id ) {
				// it is the keynote page
				$docheck	= true;	
				$type		= ICTU_GCCONF_CPT_KEYNOTE;
			}
			elseif ( $session_page->ID === $the_id ) {
				// it is the session page
				$docheck	= true;	
				$type		= ICTU_GCCONF_CPT_SESSION;
			}

			if ( $docheck && !have_rows('blocks') ) {
				// no content has been selected for this page, so list ALL published content for this CPT

				$args = array(
					'post_type'             =>  $type,
					'posts_per_page'        =>  -1,
					'post_status'     		=> 'publish',
					'order'                 =>  'ASC',
					'orderby'               =>  'post_title'
				  );

				$posts_for_cpt = new WP_query( $args );
		
				if ( $posts_for_cpt->have_posts() ) {

					$colcount = 'grid--col-2';

					if ( $type === ICTU_GCCONF_CPT_SPEAKER ) {
						$colcount = 'grid--col-3';
					}					

					echo '<div class="grid ' . $colcount . ' ' . $type . '">';
					
					while ( $posts_for_cpt->have_posts() ) : $posts_for_cpt->the_post();
					
						$postcounter++;

						$args = array( 
							'ID'			=> $post->ID,
							'titletag'		=> 'h2',
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
	* Add a speaker image before the content
	*
	* @return void
	*/
    public function fn_ictu_gcconf_frontend_speaker_featured_image() {
	    
	    global $post;

		if ( has_post_thumbnail( $post ) ) {
			echo '<span class="speaker-image">';
			echo get_the_post_thumbnail( $post, SPEAKER_IMG_SIZE, array( 'class' => 'speaker-thumbnail thumbnail alignright' ) );
			echo '</span>';
		}
		echo '<span class="speaker-bio">';

		
	}


    
    /** ----------------------------------------------------------------------------------------------------
	* Adds meta info  to session or keynot
	*
	* @return void
	*/
    public function fn_ictu_gcconf_frontend_sessionkeynote_location_time() {
	    
	    global $post;

		$time_term 							= wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_TIMESLOT );
		$location_term						= wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_LOCATION );
		$session_type						= wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_SESSIONTYPE );
		$session_level						= wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_LEVEL );
		$metainfo 							= '';

		if ( $time_term || $location_term || $session_type || $session_level ) {
			
			$metainfo .= '<dl class="dl-time-location">';

			if ( $time_term && ! is_wp_error( $time_term ) ) {
				$time_term_counter = 0;
				$metainfo .= '<dt>' . _x( 'Time', 'Event times', 'ictu-gc-plugin-conference' ) . '</dt>';

			    foreach ( $time_term as $term ) {
				    $time_term_counter++;
				    $parentname = '';

				    if ( $time_term_counter > 1 ) {
						$metainfo .= '<dd class="event-times">, ' . $parentname . $term->name . '</dd> ';	
				    }
				    else {
						$metainfo .= '<dd class="event-times">' . $parentname . $term->name . '</dd> ';	
				    }
					
			    }
				
			}	
			if ( $location_term && ! is_wp_error( $location_term ) ) {
				$location_term_counter = 0;
				$metainfo .= '<dt>' . _x( 'Session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ) . '</dt>';

			    foreach ( $location_term as $term ) {
				    $location_term_counter++;
				    
				    if ( $location_term_counter > 1 ) {
						$metainfo .= '<dd class="event-location">, ' . $term->name . '</dd> ';	
				    }
				    else {
						$metainfo .= '<dd class="event-location">' . $term->name . '</dd> ';	
				    }
					
			    }
			}	
			if ( $session_level && ! is_wp_error( $session_level ) ) {
				$location_term_counter = 0;
				$metainfo .= '<dt>' . _x( 'Session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ) . '</dt>';

			    foreach ( $session_level as $term ) {
				    $location_term_counter++;
				    if ( $location_term_counter > 1 ) {
						$metainfo .= '<dd class="event-level">, ' . $term->name . '</dd> ';	
				    }
				    else {
						$metainfo .= '<dd class="event-level">' . $term->name . '</dd> ';	
				    }
				    
					
			    }
			}	
			if ( $session_type && ! is_wp_error( $session_type ) ) {
				$location_term_counter = 0;
				$metainfo .= '<dt>' . _x( 'Session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ) . '</dt>';

			    foreach ( $session_type as $term ) {
				    $location_term_counter++;
				    if ( $location_term_counter > 1 ) {
						$metainfo .= '<dd class="event-type">, ' . $term->name . '</dd> ';	
				    }
				    else {
						$metainfo .= '<dd class="event-type">' . $term->name . '</dd> ';	
				    }
				    

			    }
			}	
			$metainfo .= '</dl>';
			
		}

		if ( $metainfo ) {

			echo '<div class="meta">' . $metainfo . '</div>';

		}


	}
	


    /** ----------------------------------------------------------------------------------------------------
	* Do actually register the post types we need
	*
	* @return void
	*/
    public function fn_ictu_gcconf_register_post_types() {
      
      // ---------------------------------------------------------------------------------------------------
      // custom post type voor 'keynote'

    	$labels = array(
    		"name"                  => _x( 'Sessions', 'session type', 'ictu-gc-plugin-conference' ),
    		"singular_name"         => _x( 'Session', 'session type', 'ictu-gc-plugin-conference' ),
    		"menu_name"             => _x( 'Sessions', 'session type', 'ictu-gc-plugin-conference' ),
    		"all_items"             => _x( 'All sessions', 'session type', 'ictu-gc-plugin-conference' ),
    		"add_new"               => _x( 'Add new session', 'session type', 'ictu-gc-plugin-conference' ),
    		"add_new_item"          => _x( 'Add new session', 'session type', 'ictu-gc-plugin-conference' ),
    		"edit_item"             => _x( 'Edit session', 'session type', 'ictu-gc-plugin-conference' ),
    		"new_item"              => _x( 'New session', 'session type', 'ictu-gc-plugin-conference' ),
    		"view_item"             => _x( 'View session', 'session type', 'ictu-gc-plugin-conference' ),
    		"search_items"          => _x( 'Search session', 'session type', 'ictu-gc-plugin-conference' ),
    		"not_found"             => _x( 'No sessions found', 'session type', 'ictu-gc-plugin-conference' ),
    		"not_found_in_trash"    => _x( 'No sessions found', 'session type', 'ictu-gc-plugin-conference' ),
    		"featured_image"        => __( 'Featured image', 'ictu-gc-plugin-conference' ),
    		"archives"              => __( 'Archives', 'ictu-gc-plugin-conference' ),
    		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gc-plugin-conference' ),
    		);
    
    	$args = array(
			"label"                 => _x( 'Sessions', 'session type', 'ictu-gc-plugin-conference' ),
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
    		"name"                  => _x( 'Keynotes', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"singular_name"         => _x( 'Keynote', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"menu_name"             => _x( 'Keynotes', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"all_items"             => _x( 'All keynotes', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"add_new"               => _x( 'Add new keynote', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"add_new_item"          => _x( 'Add new keynote', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"edit_item"             => _x( 'Edit keynote', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"new_item"              => _x( 'New keynote', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"view_item"             => _x( 'View keynote', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"search_items"          => _x( 'Search keynote', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"not_found"             => _x( 'No keynotes found', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"not_found_in_trash"    => _x( 'No keynotes found', 'keynotes type', 'ictu-gc-plugin-conference' ),
    		"featured_image"        => __( 'Featured image', 'ictu-gc-plugin-conference' ),
    		"archives"              => __( 'Archives', 'ictu-gc-plugin-conference' ),
    		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gc-plugin-conference' ),
    		);
    
    	$args = array(
    		"label"                 => _x( 'Keynotes', 'Stappen label', 'ictu-gc-plugin-conference' ),
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
    		"name"                  => _x( 'Speakers', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"singular_name"         => _x( 'Speaker', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"menu_name"             => _x( 'Speakers', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"all_items"             => _x( 'All speakers', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"add_new"               => _x( 'Add new speaker', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"add_new_item"          => _x( 'Add new speaker', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"edit_item"             => _x( 'Edit speaker', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"new_item"              => _x( 'Edit speaker', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"view_item"             => _x( 'View speaker', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"search_items"          => _x( 'Search speaker', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"not_found"             => _x( 'No speakers found', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"not_found_in_trash"    => _x( 'No speakers found', 'speaker type', 'ictu-gc-plugin-conference' ),
    		"featured_image"        => __( 'Featured image', 'ictu-gc-plugin-conference' ),
    		"archives"              => __( 'Archives', 'ictu-gc-plugin-conference' ),
    		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gc-plugin-conference' ),
    		);
    
    	$args = array(
			"label"                 => _x( 'Speakers', 'speaker type', 'ictu-gc-plugin-conference' ),
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
      		"name"                  => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"menu_name"             => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"all_items"             => _x( 'All timeblocks', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new"               => _x( 'Add timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new_item"          => _x( 'Add timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"edit_item"             => _x( 'Edit timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"new_item"              => _x( 'New timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"view_item"             => _x( 'View timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"search_items"          => _x( 'Search timeblock', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found"             => _x( 'No timeblocks found', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found_in_trash"    => _x( 'No timeblocks found', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gc-plugin-conference' ),
      		"archives"              => __( 'Archives', 'ictu-gc-plugin-conference' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gc-plugin-conference' ),
      		);
  
      	$args = array(
      		"label"                 => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"                  => _x( 'Timeblocks', 'timeblock taxonomy', 'ictu-gc-plugin-conference' ),
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
      		"name"                  => _x( 'Session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Session types', 'session type taxonomy', 'ictu-gc-plugin-conference' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Session types', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"menu_name"             => _x( 'Session types', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"all_items"             => _x( 'All session types', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new"               => _x( 'Add session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new_item"          => _x( 'Add session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"edit_item"             => _x( 'Edit session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"new_item"              => _x( 'New session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"view_item"             => _x( 'View session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"search_items"          => _x( 'Search session type', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found"             => _x( 'No session types found', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found_in_trash"    => _x( 'No session types found', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gc-plugin-conference' ),
      		"archives"              => __( 'Archives', 'ictu-gc-plugin-conference' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gc-plugin-conference' ),
      		);
  
      	$args = array(
      		"label"                 => _x( 'Session types', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"                  => _x( 'Session types', 'session type taxonomy', 'ictu-gc-plugin-conference' ),
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
      		"name"                  => _x( 'Session levels', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Session level', 'session level taxonomy', 'ictu-gc-plugin-conference' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Session levels', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"menu_name"             => _x( 'Session levels', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"all_items"             => _x( 'All session levels', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new"               => _x( 'Add session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new_item"          => _x( 'Add session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"edit_item"             => _x( 'Edit session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"new_item"              => _x( 'New session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"view_item"             => _x( 'View session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"search_items"          => _x( 'Search session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found"             => _x( 'No search session levels found', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found_in_trash"    => _x( 'No search session levels found', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gc-plugin-conference' ),
      		"archives"              => __( 'Archives', 'ictu-gc-plugin-conference' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gc-plugin-conference' ),
      		);
  
      	$args = array(
      		"label"               => _x( 'Session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"               => _x( 'Session level', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
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
      		"name"                  => _x( 'Session locations', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Session location', 'session location taxonomy', 'ictu-gc-plugin-conference' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Session locations', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"menu_name"             => _x( 'Session locations', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"all_items"             => _x( 'All session locations', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new"               => _x( 'Add session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new_item"          => _x( 'Add session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"edit_item"             => _x( 'Edit session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"new_item"              => _x( 'New session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"view_item"             => _x( 'View session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"search_items"          => _x( 'Search session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found"             => _x( 'No search session locations found', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found_in_trash"    => _x( 'No search session locations found', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gc-plugin-conference' ),
      		"archives"              => __( 'Archives', 'ictu-gc-plugin-conference' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gc-plugin-conference' ),
      		);
  
      	$args = array(
      		"label"               => _x( 'Session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"               => _x( 'Session location', 'session location taxonomy', 'ictu-gc-plugin-conference' ),
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
      	register_taxonomy( ICTU_GCCONF_CT_LOCATION, array( ICTU_GCCONF_CPT_SESSION, ICTU_GCCONF_CPT_KEYNOTE ), $args );

      // ---------------------------------------------------------------------------------------------------
      // Expertise taxonomie voor methode
      	$labels = array(
      		"name"                  => _x( 'Countries', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Country', 'session level taxonomy', 'ictu-gc-plugin-conference' )
      		);
      
      	$labels = array(
      		"name"                  => _x( 'Countries', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"singular_name"         => _x( 'Country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"menu_name"             => _x( 'Countries', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"all_items"             => _x( 'All countries', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new"               => _x( 'Add country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"add_new_item"          => _x( 'Add country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"edit_item"             => _x( 'Edit country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"new_item"              => _x( 'New country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"view_item"             => _x( 'View country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"search_items"          => _x( 'Search country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found"             => _x( 'No countries found', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"not_found_in_trash"    => _x( 'No countries found', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"featured_image"        => __( 'Featured image', 'ictu-gc-plugin-conference' ),
      		"archives"              => __( 'Archives', 'ictu-gc-plugin-conference' ),
      		"uploaded_to_this_item" => __( 'Uploaded media', 'ictu-gc-plugin-conference' ),
      		);
  
      	$args = array(
      		"label"               => _x( 'Country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
      		"labels"              => $labels,
      		"public"              => true,
      		"hierarchical"        => true,
      		"label"               => _x( 'Country', 'session level taxonomy', 'ictu-gc-plugin-conference' ),
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
	*
	*
	* @param string $crumb
	* @param array $args
	*
	* @return void ($args['echo'] = true) or $return (HTML)
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
	* add extra class .inleiding to page template .entry
	*
	* @return void ($args['echo'] = true) or $return (HTML)
	*/
	function fn_ictu_gcconf_add_class_inleiding_to_entry( $attributes ) {
		
		$attributes['class'] .= ' inleiding';
		return $attributes;
		
	}
	
    
    /** ----------------------------------------------------------------------------------------------------
	* add extra class .speaker-bio to page template .entry
	*
	* @return void ($args['echo'] = true) or $return (HTML)
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

if (! function_exists( 'bidirectional_acf_update_value' ) ) {

	function bidirectional_acf_update_value( $value, $post_id, $field  ) {
	
		// vars
		$field_name   = $field['name'];
		$field_key    = $field['key'];
		$global_name  = 'is_updating_' . $field_name;
		
		$debugstring = 'bidirectional_acf_update_value';
		
		$debugstring .= "value='" . implode( ", ", $value ) . "'";
		$debugstring .= ", post_id='" . $post_id . "'";
		$debugstring .= " (type=" . get_post_type( $post_id ) . ")";
		$debugstring .= ", field_key='" . $field_key . "'";
		$debugstring .= ", field_name='" . $field_name . "'";
	
		// bail early if this filter was triggered from the update_field() function called within the loop below
		// - this prevents an inifinte loop
		if( !empty($GLOBALS[ $global_name ]) ) return $value;
	
		// set global variable to avoid inifite loop
		// - could also remove_filter() then add_filter() again, but this is simpler
		$GLOBALS[ $global_name ] = 1;
	
		// loop over selected posts and add this $post_id
		if( is_array($value) ) {
	
			foreach( $value as $post_id2 ) {
	
				// load existing related posts
				$value2 = get_field($field_name, $post_id2, false);
				
				
				// allow for selected posts to not contain a value
				if( empty($value2) ) {
					
					$value2 = array();
					
				}
	
				// bail early if the current $post_id is already found in selected post's $value2
				if( in_array($post_id, $value2) ) continue;
	
				// append the current $post_id to the selected post's 'related_posts' value
				$value2[] = $post_id;
	
				// update the selected post's value (use field's key for performance)
				update_field($field_key, $value2, $post_id2);
				
			}
		
		}
		
		
		// find posts which have been removed
		$old_value = get_field($field_name, $post_id, false);
		
		if( is_array($old_value) ) {
			
			foreach( $old_value as $post_id2 ) {
				
				// bail early if this value has not been removed
				if( is_array($value) && in_array($post_id2, $value) ) continue;
				
				
				// load existing related posts
				$value2 = get_field($field_name, $post_id2, false);
				
				
				// bail early if no value
				if( empty($value2) ) continue;
				
				
				// find the position of $post_id within $value2 so we can remove it
				$pos = array_search($post_id, $value2);
				
				
				// remove
				unset( $value2[ $pos] );
				
				
				// update the un-selected post's value (use field's key for performance)
				update_field($field_key, $value2, $post_id2);
				
			}
			
		}
	
	  // reset global varibale to allow this filter to function as per normal
	  $GLOBALS[ $global_name ] = 0;
	
	  // return
	  return $value;
	    
	}
    
}

//========================================================================================================

//add_filter('acf/update_value/name=speakers', 'bidirectional_acf_update_value', 10, 3);

add_filter('acf/update_value/name=speaker_session_keynote_relations', 'bidirectional_acf_update_value', 10, 3);

//========================================================================================================

function fn_ictu_gcconf_extra_update_speaker_relationfield( $postid ) {

	$return = '';
	$list_of_speakers 			= get_field( 'speaker_session_keynote_relations', $postid );
	$reservelijst 				= get_field( 'speakers', $postid );
	$speakers_from_post_meta 	= get_post_meta( $postid, 'speaker_session_keynote_relations' );

	if ( WP_DEBUG && CONF_DEBUG ) {
			
		echo '<div style="display: unset; display: block; border: 10px solid black; padding: 1em;">';
		
		dovardump( $list_of_speakers, 'juiste lijst' );
		
		echo '<hr style="border: 2px solid red;">';
		
		dovardump( $speakers_from_post_meta, 'post meta' );
		
		echo '<hr style="border: 2px solid red;">';
		
		dovardump( $reservelijst, 'oude lijst' );
		
		echo '<hr style="border: 2px solid red;">';
	
	}	

	if ( ! $list_of_speakers ) {

		$return = '<h1>fn_ictu_gcconf_extra_update_speaker_relationfield: ' . get_the_title( $postid ) . '</h1><ul>';
		
		$list_of_speakers = get_field( 'speakers', $postid );
		
		if ( $list_of_speakers ) {
			
			$updatearray = array();
			
			foreach( $list_of_speakers as $speaker ):
				
				$return .= '<li><a href="' . get_the_permalink( $postid) . '">' . get_the_title( $postid )  . '</a> - <a href="' . get_the_permalink( $speaker ) . '">' . get_the_title( $speaker )  . '</a></li>';
				
				$updatearray[ $speaker ] = $speaker;

			endforeach;

			update_field( 'speaker_session_keynote_relations', $updatearray, $postid );
			
		}
		else {
			$return .= '<li>Geen sprekers</li>';
		}

		$return .= '</ul>';
		
	}
	else {

		$return = '<h1>fn_ictu_gcconf_extra_update_speaker_relationfield: GEEN update</h1><br><ul>';
		
		foreach( $list_of_speakers as $speaker ):
			
			$return .= '<li>Want: <a href="' . get_the_permalink( $postid) . '">' . get_the_title( $postid )  . '</a> - <a href="' . get_the_permalink( $speaker ) . '">' . get_the_title( $speaker )  . '</a></li>';

		endforeach;

		$return .= '</ul>';


	}

	if ( WP_DEBUG && CONF_DEBUG ) {
		
		echo  $return;
		echo '</div>';

	}		

}		

//========================================================================================================

function fn_ictu_gcconf_footer_disable_tuesday() { 
?>
	<script>
	jQuery(document).ready(function(){
	    //put your js code here

		if ( jQuery( "#days2" ).length ) {
		    jQuery( "#days2" ).attr('disabled','disabled');
		}
	    
		if ( jQuery( "#separator-days2" ).length ) {
		    jQuery( "#separator-days2" ).addClass('disabled');
		}
	    
	})
	</script>

<?php 
}
 
//add_action('wp_footer', 'fn_ictu_gcconf_footer_disable_tuesday'); 

//========================================================================================================

