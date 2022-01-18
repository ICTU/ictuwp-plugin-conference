<?php

/**
 * @link                https://wbvb.nl
 * @package             ictuwp-plugin-conference
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Conference post types and taxonomies
 * Plugin URI:          https://github.com/ICTU/Gebruiker-Centraal---Inclusie---custom-post-types-taxonomies
 * Description:         Plugin for conference.gebruikercentraal.nl to register custom post types and custom taxonomies
 * Version:             2.0.4
 * Version description: Small CSS changes to adapt to 2021 styling of old theme.
 * Author:              Paul van Buuren
 * Author URI:          https://wbvb.nl/
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         ictuwp-plugin-conference
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//========================================================================================================

add_action( 'plugins_loaded', array( 'ICTU_GC_conference', 'init' ), 10 );

//========================================================================================================

define( 'ICTU_GC_CONF_ARCHIVE_CSS', 'ictu-gcconf-archive-css' );
define( 'ICTU_GC_CONF_BASE_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'ICTU_GC_CONF_ASSETS_URL', trailingslashit( ICTU_GC_CONF_BASE_URL ) );
define( 'ICTU_GC_CONF_VERSION', '2.0.4' );

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

if ( ! defined( 'CONF_SHOW_DATETIMES' ) ) {
//  define( 'CONF_SHOW_DATETIMES', true );
	define( 'CONF_SHOW_DATETIMES', false );
}

if ( WP_DEBUG ) {
//	define( 'CONF_DEBUG', false );
	define( 'CONF_DEBUG', true );
} else {
	define( 'CONF_DEBUG', false );
}


/** ----------------------------------------------------------------------------------------------------
 * Function for making metadata
 */
function cnf_make_meta( $meta ) {
	$meta_data  = '';
	$meta_items = '';

	foreach ( $meta as $item ) {
		if ( $item ) {
			if ( is_array( $item ) && isset( $item['type'] ) ) {
				//print_r($item);
				$meta_items .= '<span class="meta_data meta-data--with-icon ' . $item['type'] . '">' . $item['name'] . '</span>';
			} else {
				$meta_items .= '<span class="meta_data">' . $item . '</span>';
			}
		}
	}

	if ( $meta_items ) {
		$meta_data = '<span class="meta-data">';
		$meta_data .= $meta_items;
		$meta_data .= '</span>';
	}

	return $meta_data;
}

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

			$this->fn_ictu_gcconf_setup_actions();

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Hook this plugins functions into WordPress
		 */
		private function fn_ictu_gcconf_setup_actions() {

			add_action( 'init', array( $this, 'fn_ictu_gcconf_register_post_types' ) );

			add_action( 'init', array( $this, 'fn_ictu_gcconf_add_rewrite_rules' ) );

			// make sure the breadcrumb is useful for our CPTs
			add_filter( 'genesis_single_crumb', array( $this, 'fn_ictu_gcconf_filter_breadcrumb' ), 10, 2 );
			add_filter( 'genesis_page_crumb', array( $this, 'fn_ictu_gcconf_filter_breadcrumb' ), 10, 2 );
			add_filter( 'genesis_archive_crumb', array( $this, 'fn_ictu_gcconf_filter_breadcrumb' ), 10, 2 );
			add_filter( 'genesis_tax_crumb', array( $this, 'fn_ictu_gcconf_filter_breadcrumb' ), 10, 2 );

			add_image_size( SPEAKER_IMG_SIZE, 148, 171, true );

			// add a page temlate name
			$this->templates                     = array();
			$this->template_conf_overviewpage    = 'conf-overviewpage.php';
			$this->template_conf_contenttypepage = 'conf-contenttypepage.php';

			// add the page template to the templates list
			add_filter( 'theme_page_templates', array( $this, 'fn_ictu_gcconf_add_page_templates' ) );

			// activate the page filters
			add_action( 'template_redirect', array( $this, 'fn_ictu_gcconf_frontend_use_page_template' ) );

			// add styling and scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'fn_ictu_gcconf_register_frontend_style_script' ) );

		}


		/** ----------------------------------------------------------------------------------------------------
		 * Hides the custom post template for pages on WordPress 4.6 and older
		 *
		 * @param array $post_templates Array of page templates. Keys are filenames, values are translated names.
		 *
		 * @return array Expanded array of page templates.
		 */
		function fn_ictu_gcconf_add_page_templates( $post_templates ) {

			$post_templates[ $this->template_conf_overviewpage ]    = _x( 'Conferentie programmapagina', "naam template", 'ictuwp-plugin-conference' );
			$post_templates[ $this->template_conf_contenttypepage ] = _x( 'Conferentie overzicht contenttype', "naam template", 'ictuwp-plugin-conference' );

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
		function fn_ictu_gcconf_tax_loop() {

			global $post;

			$colcount = 'grid--col-3';

			if (
				( is_tax( ICTU_GCCONF_CT_LOCATION ) ) ||
				( is_tax( ICTU_GCCONF_CT_SESSIONTYPE ) ) ||
				( is_tax( ICTU_GCCONF_CT_LEVEL ) ) ||
				( is_tax( ICTU_GCCONF_CT_COUNTRY ) ) ||
				( is_tax( ICTU_GCCONF_CT_TIMESLOT ) )
			) {
				$colcount = 'grid--col-2';
			}

			if ( have_posts() ) :

				echo '<div class="archive-list grid ' . $colcount . '">';

				while ( have_posts() ) : the_post();

					// do loop stuff
					$type = get_post_type( $post );

					$args = array(
						'ID'       => $post->ID,
						'titletag' => 'h2',
					);

					if ( $type === ICTU_GCCONF_CPT_SPEAKER ) {

						echo $this->fn_ictu_gcconf_frontend_write_speakercard( $args );

					} elseif ( $type === ICTU_GCCONF_CPT_KEYNOTE ) {

						echo $this->fn_ictu_gcconf_frontend_write_keynotecard( $args );

					} else {

						echo $this->fn_ictu_gcconf_frontend_write_sessioncard( $args );

					}


				endwhile;
				/** end of one post **/

				echo '</div>';

				do_action( 'genesis_after_endwhile' );

			else : /** if no posts exist **/
				do_action( 'genesis_loop_else' );
			endif;
			/** end loop **/

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

		public function gcconf_template_append_blocks() {

			global $post;

			$the_id = $post->ID;

			if ( have_rows( 'blocks' ) ) {

				$sectioncounter = 0;

				// loop through the rows of data
				while ( have_rows( 'blocks' ) ) : the_row();

					$sectioncounter ++;

					$section_title    = get_sub_field( 'block_title' );
					$block_title_id   = get_sub_field( 'block_title_id' );
					$block_free_text  = get_sub_field( 'block_free_text' );
					$block_extra_type = get_sub_field( 'block_extra_type' );
					$block_time       = get_sub_field( 'block_time' );
					$time             = '';
					$section_css      = 'section-block';

					$headertitle_tag     = 'h3';
					$headertitle_tag_sub = 'h4';

					if ( $block_time ) {
						$time        = '<div class="date-badge"><span class="dag multiple">' . $block_time . '</span></div>';
						$section_css .= ' has-time';
					}


					if ( ! $section_title ) {
						$headertitle_tag     = 'h2';
						$headertitle_tag_sub = 'h3';
						$block_title_id      = 'section' . $the_id . '_' . $sectioncounter;
						$sectionblockstart   = '<section class="' . $section_css . '">';
					} else {

						if ( $block_title_id ) {
							$title_id = sanitize_title( $block_title_id );
						} else {
							$title_id = sanitize_title( $section_title );
						}

						$sectionblockstart = '<section aria-labelledby="' . $title_id . '" class="' . $section_css . '">';
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

						if ( $posts ):

							foreach ( $posts as $post ):

								setup_postdata( $post );

								$section_title = get_the_title( $post->ID );
								$title_id      = sanitize_title( $section_title );
								$my_em_event   = em_get_event( $post );
								$times         = $my_em_event->output( '#_EVENTTIMES' );
								$town          = $my_em_event->output( '#_LOCATIONTOWN' );

								echo '<div class="card no-image card--event" aria-labelledby="' . $title_id . '">';
								echo '<' . $headertitle_tag . ' class="card__title"><a class="arrow-link" href="' . get_permalink( $post->ID ) . '"><span class="arrow-link__text">' . $section_title . '</span><span class="arrow-link__icon"></span></a></' . $headertitle_tag . '>';

								if ( $times || $town ) {

									echo '<div class="meta">';
									echo '<dl class="dl-time-location">';
									if ( $times ) {
										echo '<dt>' . _x( 'Time', 'Event times', 'ictuwp-plugin-conference' ) . '</dt><dd class="event-times">' . $times . '</dd> ';
									}
									if ( $town ) {
										echo '<dt>' . _x( 'Location', 'Event city', 'ictuwp-plugin-conference' ) . '</dt><dd class="event-location">' . $town . '</dd> ';
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

					} // -----------------------------------------------------------------------------------------
					elseif ( 'keynotes' === $block_extra_type ) {

						$posts = get_sub_field( 'block_keynotes' );

						if ( $posts ):

							foreach ( $posts as $post ):

								setup_postdata( $post );

								$args = array(
									'ID'       => $post->ID,
									'titletag' => 'h2',
									'echo'     => false
								);

								echo $this->fn_ictu_gcconf_frontend_write_keynotecard( $args );

							endforeach;

							wp_reset_postdata();

						endif;

					} // -----------------------------------------------------------------------------------------
					elseif ( 'speakers' === $block_extra_type ) {

						$posts = get_sub_field( 'block_speakers' );

						if ( $posts ):

							echo '<div class="grid grid--col-3 speakers">';

							foreach ( $posts as $post ):

								setup_postdata( $post );

								$args = array(
									'ID'       => $post->ID,
									'titletag' => 'h2',
									'echo'     => false
								);

								echo $this->fn_ictu_gcconf_frontend_write_speakercard( $args );

							endforeach;

							echo '</div>'; // . speakers

							wp_reset_postdata();

						endif;

					} // -----------------------------------------------------------------------------------------
					elseif ( 'sessions' === $block_extra_type ) {

						if ( have_rows( 'block_sessions' ) ):

							// loop through rows (sub repeater)
							while ( have_rows( 'block_sessions' ) ): the_row();

								$block_sessions_session = get_sub_field( 'block_sessions_session' );

								if ( is_object( $block_sessions_session ) ) {

									$block_sessions_session_time     = get_sub_field( 'block_sessions_session_time' );
									$block_sessions_session_location = get_sub_field( 'block_sessions_session_location' );

									$args = array(
										'ID'               => $block_sessions_session->ID,
										'titletag'         => 'h2',
										'echo'             => true,
										'session_time'     => $block_sessions_session_time,
										'session_location' => $block_sessions_session_location,

									);

									echo $this->fn_ictu_gcconf_frontend_write_sessioncard( $args );

								}

							endwhile;

						endif;

					}
					// -----------------------------------------------------------------------------------------


					echo '</div> <!-- // .section-content -->' . "\n";

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
		public function fn_ictu_gcconf_frontend_append_links( $args = [] ) {

			global $post;

			$defaults = array(
				'echo'     => true,
				'showfoto' => false
			);

			$return = '';

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );

			if ( ! $post->ID ) {
				return;
			}

			if ( have_rows( 'extra_info_repeater', $post->ID ) ) {

				$return = '<div class="links"><h2 class="visuallyhidden">' . _x( 'Links', 'extra links for type', 'ictuwp-plugin-conference' ) . '</h2>';

				$count     = count( get_field( 'extra_info_repeater', $post->ID ) );
				$make_list = false;

				if ( $count > 1 ) {
					$make_list = true;
				}

				// loop through the rows of data
				while ( have_rows( 'extra_info_repeater', $post->ID ) ) : the_row();

					// display a sub field value
					$url      = get_sub_field( 'extra_info_repeater_url' );
					$desc     = get_sub_field( 'extra_info_repeater_shortdescription' );
					$type     = get_sub_field( 'extra_info_repeater_type' );
					$linktext = get_sub_field( 'extra_info_repeater_linktext' );

					if ( $type && $url ) {
						switch ( $type ) {
							case 'video':
								$return .= '<div class="video">';
								$return .= '<h3 class="video__title"><a href="' . $url . '">' . $linktext . '</a></h3>';
								$return .= '<div class="videoWrapper">';
								$return .= wp_oembed_get( $url );
								if ( $desc ) {
									$return .= '<p>' . $desc . '</p>';
								}

								$return .= '</div></div>';
								break;
							default:
								$return .= '<a href="' . $url . '" class="extra-link extra-link--' . $type . '">' . $linktext . '</a>';
						}
					}


				endwhile;

				$return .= '</div>';

			}

			if ( $args['showfoto'] ) {

			}

			if ( $args['echo'] ) {
				echo $return;
			} else {
				return $return;
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

			$defaults = array(
				'echo' => true
			);

			$return = '';

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );

			if ( ! $post->ID ) {
				return;
			}

			$list_of_speakers = get_field( 'relation_x1315x_speaker_vv_session', $post->ID );

			if ( $list_of_speakers ) {

				$return .= '<div class="speakers"><h2 class="visuallyhidden">' . _x( 'Speakers', 'speaker type', 'ictuwp-plugin-conference' ) . '</h2>';

				foreach ( $list_of_speakers as $speaker ):

					$args2 = array(
						'ID'   => $speaker->ID,
						'echo' => false,
						'type' => 'author'
					);

					$return .= $this->fn_ictu_gcconf_frontend_write_speakercard( $args2 );

				endforeach;

				$return .= '</div>';

			} else {
				$return .= '<p></div>';
			}

			if ( $args['echo'] ) {
				echo $return;
			} else {
				return $return;
			}


		}

		//========================================================================================================

		/**
		 * Register frontend stylesheet. Dummy code to enable inline CSS in header should that be necessary
		 *
		 * @return void
		 */
		public function fn_ictu_gcconf_register_frontend_style_script() {

			global $post;

			if ( defined( 'ID_SKIPLINKS' ) ) {
				$dependencies = array( ID_SKIPLINKS ); // only load CSS file AFTER the ID_SKIPLINKS css file has been loaded
				$file         = dirname( __FILE__ ) . '/css/frontend-conf.css';
				$versie       = filemtime( $file );
				wp_enqueue_style( ICTU_GC_CONF_ARCHIVE_CSS, trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/frontend-conf.css', $dependencies, $versie, 'all' );
			}


		}

		//========================================================================================================

		/**
		 * Modify page content if using a specific page template.
		 *
		 * @return void
		 */
		public function fn_ictu_gcconf_frontend_use_page_template() {

			global $post;

			$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

			if ( $this->template_conf_overviewpage == $page_template ) {

				remove_filter( 'genesis_post_title_output', 'gc_wbvb_sharebuttons_for_page_top', 15 );

				//* Force full-width-content layout
				add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

				// append content
				add_action( 'genesis_entry_content', array( $this, 'gcconf_template_append_blocks' ), 12 );
				//
				add_action( 'genesis_after_entry_content', array( $this, 'gcconf_content_for_noblocks_page' ), 15 );

				// add extra class, to make the title BIGGERDER
				add_filter( 'genesis_attr_entry', array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

			} elseif ( $this->template_conf_contenttypepage == $page_template ) {

				remove_filter( 'genesis_post_title_output', 'gc_wbvb_sharebuttons_for_page_top', 15 );

				// remove social media
				remove_filter( 'genesis_entry_header', 'gc_wbvb_page_append_sokmet' );

				//* Force full-width-content layout
				add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

				// append content
				add_action( 'genesis_entry_content', array( $this, 'gcconf_template_append_blocks' ), 12 );

				//
				add_action( 'genesis_after_entry_content', array( $this, 'gcconf_content_for_noblocks_page' ), 15 );

				// add extra class, to make the title BIGGERDER
				add_filter( 'genesis_attr_entry', array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

			} elseif ( is_post_type_archive( ICTU_GCCONF_CPT_KEYNOTE ) ) {

				/** Replace the standard loop with our custom loop */
				remove_action( 'genesis_loop', 'genesis_do_loop' );
				remove_action( 'genesis_loop', 'gc_wbvb_archive_loop' );

			} elseif (
				( is_tax( ICTU_GCCONF_CT_LOCATION ) ) ||
				( is_tax( ICTU_GCCONF_CT_SESSIONTYPE ) ) ||
				( is_tax( ICTU_GCCONF_CT_LEVEL ) ) ||
				( is_tax( ICTU_GCCONF_CT_COUNTRY ) ) ||
				( is_tax( ICTU_GCCONF_CT_TIMESLOT ) )
			) {

				//Removes Title and Description on Archive, Taxonomy, Category, Tag
				remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
				add_action( 'genesis_before_loop', 'gc_wbvb_add_taxonomy_description', 15 );

				/** Replace the standard loop with our custom loop */
				remove_action( 'genesis_loop', 'genesis_do_loop' );
				remove_action( 'genesis_loop', 'gc_wbvb_archive_loop' );

				add_action( 'genesis_loop', array( $this, 'fn_ictu_gcconf_tax_loop' ) );


			} elseif ( ICTU_GCCONF_CPT_SPEAKER == get_post_type() ) {

				//* Force full-width-content layout
				add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

				// add extra class, to make the title BIGGERDER
				add_filter( 'genesis_attr_entry', array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

				// append speaker image
				add_action( 'genesis_entry_content', array( $this, 'gcconf_append_speaker_image' ), 6 );

				// append speaker country info
				add_action( 'genesis_entry_content', array( $this, 'gcconf_append_speaker_country' ), 7 );

				// append weblinks
				add_action( 'genesis_entry_content', array( $this, 'gcconf_append_speaker_weblinks' ), 12 );


			} elseif ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {

				// add extra class, to make the title BIGGERDER
				add_filter( 'genesis_attr_entry', array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

				add_action( 'genesis_entry_content', array( $this, 'gcconf_append_session_location_time' ), 8 );

				add_action( 'genesis_entry_content', array( $this, 'fn_ictu_gcconf_frontend_append_links' ), 12 );

				add_action( 'genesis_entry_content', array( $this, 'fn_ictu_gcconf_frontend_append_speakers' ), 14 );

			} elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {

				// add extra class, to make the title BIGGERDER
				add_filter( 'genesis_attr_entry', array( $this, 'fn_ictu_gcconf_add_class_inleiding_to_entry' ) );

				add_action( 'genesis_entry_content', array( $this, 'gcconf_append_session_location_time' ), 8 );

				add_action( 'genesis_entry_content', array( $this, 'fn_ictu_gcconf_frontend_append_links' ), 12 );

				add_action( 'genesis_entry_content', array( $this, 'fn_ictu_gcconf_frontend_append_speakers' ), 14 );

			}


			//=================================================

			add_filter( 'genesis_post_info', array( $this, 'fn_ictu_gcconf_frontend_filter_postinfo' ), 10, 2 );

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Append country taxonomy to speaker single
		 *
		 * @return string $return
		 */
		public function gcconf_append_speaker_country() {

			global $post;

			$return = '';

			/*
			$country = '';

			$countrycounter = 0;
			$county_term    = wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_COUNTRY );
			$jobtitle       = get_field( 'speaker_jobtitle', $post->ID );


			if ( $county_term && ! is_wp_error( $county_term ) ) {

				foreach ( $county_term as $term ) {

					$countrycounter ++;

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
				} else {
					$return .= $jobtitle . $country;
				}

				$return .= '</p>';
			}*/

			echo $return;

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Append related keynotes or sessions to speaker single
		 *
		 * @param array $args
		 *
		 * @return void ($args['echo'] = true) or $return (HTML)
		 */
		public function gcconf_speaker_append_links_sessions_keynotes( $args = [] ) {

			global $post;

			$return = '';

			$defaults = array(
				'ID'                  => 0,
				'addkeynotessessions' => true,
				'sectiontitle'        => false,
				'titletag'            => 'h2',
				'echo'                => true
			);

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );

			if ( ! $args['ID'] ) {
				return;
			}


			if ( $args['addkeynotessessions'] ) {
				// we should show all sessions / keynotes for this speaker

				$titlea           = '';
				$titlearray       = array();
				$keynotessessions = '<div class="archive-list grid">';
				$objects          = get_field( 'relation_x1315x_speaker_vv_session', $args['ID'] );

				if ( $objects ) {

					foreach ( $objects as $post ):
						// loop through all related sessions / keynotes

						$posttype = get_post_type( $post->ID );
						$args2    = array(
							'ID'           => $post->ID,
							'titletag'     => 'h3',
							'echo'         => false,
							'speakerimage' => false,
							'speakernames' => false,
						);

						if ( $posttype === ICTU_GCCONF_CPT_KEYNOTE ) {
							// current post is a keynote
							$keynotessessions .= $this->fn_ictu_gcconf_frontend_write_keynotecard( $args2 );

						} else {
							// current post is a session
							$keynotessessions .= $this->fn_ictu_gcconf_frontend_write_sessioncard( $args2 );

						}

					endforeach;

				}

				$keynotessessions .= '</div>';

				wp_reset_postdata();

				$return .= $keynotessessions;

			}

			// check for any links related to this speaker
			if ( have_rows( 'speaker_links', $args['ID'] ) ):

				$return .= '<div class="speaker-links">';

				if ( $args['sectiontitle'] ) {

					$return .= '<' . $args['titletag'] . '>' . sprintf( __( 'Find %s on social media', 'ictuwp-plugin-conference' ), get_the_title( get_the_ID() ) ) . '</' . $args['titletag'] . '>';

				}

				$return .= '<ul class="social-media">';

				// loop through rows (sub repeater)
				while ( have_rows( 'speaker_links', $args['ID'] ) ): the_row();

					$speaker_link_url  = get_sub_field( 'speaker_link_url' );
					$speaker_link_text = get_sub_field( 'speaker_link_text' );
					$speaker_link_type = get_sub_field( 'speaker_link_type' );

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
			} else {
				return $return;
			}

		}


		/** ----------------------------------------------------------------------------------------------------
		 * Append any links / socmed profiles to speaker single
		 *
		 * @return void
		 */
		public function gcconf_append_speaker_weblinks() {

			global $post;
			echo $this->gcconf_speaker_append_links_sessions_keynotes( array(
				'ID'           => $post->ID,
				'echo'         => false,
				'sectiontitle' => _x( 'Links', 'Header text speaker links', 'ictuwp-plugin-conference' )
			) );
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
		public function fn_ictu_gcconf_frontend_write_keynotecard( $args = array() ) {

			$defaults = array(
				'ID'           => 0,
				'titletag'     => 'h3',
				'speakerimage' => true,
				'speakernames' => true,
				'echo'         => true
			);

			$return = "\n";

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );

			if ( ! $args['ID'] ) {
				return;
			}

			$section_title    = get_the_title( $args['ID'] );
			$title_id         = sanitize_title( $section_title );
			$excerpt          = get_the_excerpt( $args['ID'] );
			$metainfo_time    = '';
			$list_of_speakers = get_field( 'speaker_session_keynote_relations', $args['ID'] );
			$speakernames     = '';

			if ( CONF_SHOW_DATETIMES ) {
				$time_term     = wp_get_post_terms( $args['ID'], ICTU_GCCONF_CT_TIMESLOT );
				$location_term = wp_get_post_terms( $args['ID'], ICTU_GCCONF_CT_LOCATION );
			} else {
				$time_term     = '';
				$location_term = '';
			}

			if ( $time_term || $location_term ) {

				$time_term_counter     = 0;
				$location_term_counter = 0;

				$metainfo_time = '<dl class="dl-time-location">';

				if ( $time_term && ! is_wp_error( $time_term ) ) {
					$metainfo_time .= '<dt>' . _x( 'Time', 'Event times', 'ictuwp-plugin-conference' ) . '</dt>';

					foreach ( $time_term as $term ) {
						$time_term_counter ++;
						$parentname = '';
						if ( $location_term_counter > 1 ) {
							$metainfo_time .= '<dd class="event-times">, ' . $parentname . $term->name . '</dd> ';
						} else {
							$metainfo_time .= '<dd class="event-times">' . $parentname . $term->name . '</dd> ';
						}

					}

				}
				if ( $location_term && ! is_wp_error( $location_term ) ) {
					$metainfo_time .= '<dt>' . _x( 'Session location', 'session location taxonomy', 'ictuwp-plugin-conference' ) . '</dt>';

					foreach ( $location_term as $term ) {
						$location_term_counter ++;
						if ( $location_term_counter > 1 ) {
							$metainfo_time .= '<dd class="event-location">, ' . $term->name . '</dd> ';
						} else {
							$metainfo_time .= '<dd class="event-location">' . $term->name . '</dd> ';
						}

					}

				}

				$metainfo_time .= '</dl>';

			}

			if ( $list_of_speakers && $args['speakernames'] ) {

				$speakercounter = 0;
				$speakernames   = '<dl class="dl-speaker-names keynote">';
				$speakernames   .= '<dt>';

				if ( count( $list_of_speakers ) > 1 ) {
					$speakernames .= _x( 'Speakers', 'speaker type', 'ictuwp-plugin-conference' );
				} else {
					$speakernames .= _x( 'Speaker', 'speaker type', 'ictuwp-plugin-conference' );
				}
				$speakernames .= '</dt>';

				foreach ( $list_of_speakers as $speaker ):

					$speakercounter ++;
					$countrynames = '';
					$speakernames .= '<dd>';

					$county_term = wp_get_post_terms( $speaker->ID, ICTU_GCCONF_CT_COUNTRY );

					if ( $county_term && ! is_wp_error( $county_term ) ) {
						$countrynames   = ' (';
						$countrycounter = 0;

						foreach ( $county_term as $term ) {

							$countrycounter ++;

							if ( $countrycounter > 1 ) {
								$countrynames .= ', ';
							}

							$countrynames .= $term->name;
						}
						$countrynames .= ')';
					}


					$speakernames .= get_the_title( $speaker ) . $countrynames;
					$speakernames .= '</dd>';

				endforeach;

				$speakernames .= '</dl>';
			}


			$return = '<div class="card card--keynote' . ( $args['speakerimage'] ? ' l-with-image' : '' ) . '" aria-labelledby="' . $title_id . '">' . "\n";
			$return .= '<' . $args['titletag'] . ' class="card__title">';
			$return .= '<a class="arrow-link" href="' . get_permalink( $args['ID'] ) . '"><span class="arrow-link__text">' . $section_title . '</span><span class="arrow-link__icon"></a>';
			$return .= '</' . $args['titletag'] . '>' . "\n";

			if ( $list_of_speakers && $args['speakerimage'] ) {

				$return .= '<div class="l-content-wrapper">';
				$return .= '<div class="card__image">';

				foreach ( $list_of_speakers as $speaker ):

					if ( has_post_thumbnail( $speaker ) ) {
						$return .= get_the_post_thumbnail( $speaker, SPEAKER_IMG_SIZE, array( 'class' => 'speaker-thumbnail thumbnail alignleft' ) );
					} else {
						//
						$arr_speaker_images = get_field( 'fallback_for_speaker_images', 'option' );
						if ( is_array( $arr_speaker_images ) ) {
							$randomid = array_rand( $arr_speaker_images, 1 );
							$return   .= wp_get_attachment_image( $arr_speaker_images[ $randomid ], SPEAKER_IMG_SIZE, false, array( 'class' => 'speaker-thumbnail thumbnail alignleft' ) );
						}

					}

				endforeach;

				$return .= '</div>'; // .card__image
				$return .= '<div class="speaker-bio">';
				if ( $speakernames || $metainfo_time ) {
					$return .= '<div class="meta-data">' . $speakernames . $metainfo_time . '</div>';
				}
				$return .= wp_strip_all_tags( $excerpt );
				$return .= '</div>'; // .speaker-bio
				$return .= '</div>'; // .l-content-wrapper

			} else {
				$return .= '<span class="speaker-bio">';
				$return .= wp_strip_all_tags( $excerpt );
				$return .= '</span>';
			}


			//fn_ictu_gcconf_frontend_append_links
			if ( have_rows( 'extra_info_repeater', $args['ID'] ) ) {

				$count     = count( get_field( 'extra_info_repeater', $args['ID'] ) );
				$make_list = false;

				if ( $count > 1 ) {
					$make_list = true;
				}

				( $make_list ? $return .= '<ul class="extra-links">' : $return = '' );
				// loop through the rows of data
				while ( have_rows( 'extra_info_repeater', $args['ID'] ) ) : the_row();

					// display a sub field value
					$url      = get_sub_field( 'extra_info_repeater_url' );
					$desc     = get_sub_field( 'extra_info_repeater_shortdescription' );
					$type     = get_sub_field( 'extra_info_repeater_type' );
					$linktext = get_sub_field( 'extra_info_repeater_linktext' );

					if ( $url && $linktext ) {
						$link = '<a href="' . $url . '" class="extra-link extra-link--' . $type . '" ><span class="visuallyhidden">' . $type . '</span> ' . $linktext . '</a>';

						( $make_list ? $return .= '<li class="extra-links__item">' . $link . '</li>' : $return .= $link );
					}


				endwhile;

				( $make_list ? $return .= '</ul>' : $return = '' );

			}


			$return .= '</div>';

			if ( $args['echo'] ) {
				echo $return;
			} else {
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
		public function fn_ictu_gcconf_frontend_write_sessioncard( $args = array() ) {

			global $post;

			$defaults = array(
				'ID'               => 0,
				'session_time'     => null,
				'session_location' => null,
				'speakerimage'     => true,
				'speakernames'     => true,
				'titletag'         => 'h3',
				'echo'             => true
			);

			$return = '';

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );

			if ( ! $args['ID'] ) {
				return;
			}

//			fn_ictu_gcconf_extra_update_speaker_relationfield( $args['ID'] );

			if ( CONF_SHOW_DATETIMES ) {
				$time_term     = get_term_by( 'id', $args['session_time'], ICTU_GCCONF_CT_TIMESLOT );
				$location_term = get_term_by( 'id', $args['session_location'], ICTU_GCCONF_CT_LOCATION );
			} else {
				$time_term     = '';
				$location_term = '';
			}


			$list_of_speakers = get_field( 'relation_x1315x_speaker_vv_session', $post->ID );
			$section_title    = get_the_title( $args['ID'] );
			$title_id         = sanitize_title( $section_title );

			$metainfo = [];

			$metainfo[] = ( $time_term ? _x( 'Timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' ) . $time_term->name : '' );
			$metainfo[] = ( $time_term ? _x( 'Session location', 'session location taxonomy', 'ictuwp-plugin-conference' ) . $location_term->name : '' );

			if ( $list_of_speakers && $args['speakernames'] ) {
				$label    = _x( 'Speaker', 'speaker type', 'ictuwp-plugin-conference' );
				$speakers = [];

				if ( count( $list_of_speakers ) > 1 ) {
					$label = _x( 'Speakers', 'speaker type', 'ictuwp-plugin-conference' );
				}

				foreach ( $list_of_speakers as $speaker ):
					$speakers[] = get_the_title( $speaker );
				endforeach;


				if ( $speakers ) {
					$speakers            = implode( ', ', $speakers );
					$metainfo[2]['name'] = ( $label ? '<label>' . $label . ':</label> ' : '' ) . $speakers;
					$metainfo[2]['type'] = 'speaker';
				}
			}

			$return .= '<section class="card card--session" aria-labelledby="' . $title_id . '">';
			$return .= '<' . $args['titletag'] . ' class="card__title">' .
					   '<a class="arrow-link" href="' . get_permalink( $args['ID'] ) . '"><span class="arrow-link__text">' . $section_title . '</span><span class="arrow-link__icon"></span></a></' . $args['titletag'] . '>';

			if ( $metainfo ) {
				$return .= cnf_make_meta( $metainfo );
			}

			$excerpt = get_the_excerpt( $args['ID'] );
			$return  .= '<p>' . wp_strip_all_tags( $excerpt ) . '</p>';


			//fn_ictu_gcconf_frontend_append_links
			if ( have_rows( 'extra_info_repeater', $args['ID'] ) ) {

				$count = count( get_field( 'extra_info_repeater', $args['ID'] ) );

				$make_list = false;

				if ( $count > 1 ) {
					$make_list = true;

					$return .= '<ul class="extra-links">';
				}

				// loop through the rows of data
				while ( have_rows( 'extra_info_repeater', $args['ID'] ) ) : the_row();

					// display a sub field value
					$url      = get_sub_field( 'extra_info_repeater_url' );
					$desc     = get_sub_field( 'extra_info_repeater_shortdescription' );
					$type     = get_sub_field( 'extra_info_repeater_type' );
					$linktext = get_sub_field( 'extra_info_repeater_linktext' );

					$download_attribute = '';

					if ( $url && $linktext ) {
						$link   = '<a href="' . $url . '" class="extra-link extra-link--' . $type . '">' . $linktext . '</a>';
						$return .= ( $make_list ? '<li>' . $link . '</li>' : $link );
					}


				endwhile;

				$return .= ( $make_list ? '</ul>' : '' );


			}

			$return .= '</section>'; // .card card--session

			if ( $args['echo'] ) {
				echo $return;
			} else {
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
		public function fn_ictu_gcconf_frontend_write_speakercard( $args = array() ) {
			$return = '';

			$defaults = array(
				'ID'      => 0,
				'echo'    => true,
				'type'    => 'card',
				'classes' => 'card card--speaker',
			);

			// Parse incoming $args into an array and merge it with $defaults
			$args = wp_parse_args( $args, $defaults );

			if ( ! $args['ID'] ) {
				return;
			}

			/* Set vars */
			$section_title = get_the_title( $args['ID'] );
			$type          = $args['type'];
			$is_author     = false;

			/* Set image */
			if ( has_post_thumbnail( $args['ID'] ) ) {
				$image = '<figure class="' . $args['type'] . '__image">' . get_the_post_thumbnail( $args['ID'], SPEAKER_IMG_SIZE ) . '</figure>';
			} else {
				$arr_speaker_images = get_field( 'fallback_for_speaker_images', 'option' );
				if ( is_array( $arr_speaker_images ) ) {
					$randomid = array_rand( $arr_speaker_images, 1 );
					$image    = '<figure class="' . $args['type'] . '__image">' . wp_get_attachment_image( $arr_speaker_images[ $randomid ], SPEAKER_IMG_SIZE, false ) . '</figure>';
				}
			}

			/* Set metadata */
			$meta_data  = '';
			$meta_items = [];

			$meta_items[] = ( get_field( 'speaker_jobtitle', $args['ID'] ) ? get_field( 'speaker_jobtitle', $args['ID'] ) : '' );
			$objects      = get_field( 'speaker_session_keynote_relations', $args['ID'] );
			$county_term  = wp_get_post_terms( $args['ID'], ICTU_GCCONF_CT_COUNTRY );

			foreach ( $meta_items as $meta ) {
				$meta_data .= '<span class="meta-data__item">' . $meta . '</span>';
			}

			// Overrides for author box

			if ( $args['type'] === 'author' ) {
				$is_author       = true;
				$title_tag       = 'h2';
				$excerpt         = ( get_the_excerpt( $args['ID'] ) ? '<p class="excerpt">' . wp_strip_all_tags( get_the_excerpt( $args['ID'] ) ) . '</p>' : '' );
				$args['classes'] = 'author author--box';
			} else {
				$title_tag = 'h3';
				$excerpt   = '';
			}


			$return = "\n" . '<section class="' . $args['classes'] . ( $image ? ' l-with-image' : '' ) . '" itemprop="author" itemscope="itemscope" itemtype="http://schema.org/Person">';

			// Set image if there is any
			$return .= ( $image ? $image : '' );

			// Set section / card
			$return .= "\n" . '<div class="' . $type . '__content">';
			$return .= '<' . $title_tag . ' class="' . $type . '__title"><a class="arrow-link" href="' . get_permalink( $args['ID'] ) . '">' .
					   '<span class="arrow-link__text">' . $section_title . '</span><span class="arrow-link__icon"></span>' .
					   '</a></' . $title_tag . '>';
			$return .= ( $meta_data ? '<div class="meta-data">' . $meta_data . '</div>' : '' );
			$return .= ( $excerpt ? $excerpt : '' );
			$return .= '</div>';
			$return .= '</section>' . "\n";


			if ( $args['echo'] ) {
				echo $return;
			} else {
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
		public function gcconf_content_for_noblocks_page() {


			global $post;

			if ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {
			} elseif ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {
			} elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {
			} elseif ( is_page() ) {

				$the_id            = $post->ID;
				$type              = '';
				$docheck           = false;
				$postcounter       = 0;
				$colcount          = 'grid--col-2';
				$speaker_page      = get_field( 'themesettings_conference_speakers', 'option' );
				$keynote_page      = get_field( 'themesettings_conference_keynotes', 'option' );
				$session_page      = get_field( 'themesettings_conference_sessions', 'option' );
				$userselectedposts = get_field( 'template_conf_contenttypepage_filter', $the_id );

				if ( ! 'filter_ja' == $userselectedposts ) {
					$userselectedposts = 'filter_nee';
				}

				// what kind of content are we looking at?
				if ( $speaker_page->ID === $the_id ) {
					// it is the speaker page
					$docheck = true;
					$type    = ICTU_GCCONF_CPT_SPEAKER;
				} elseif ( $keynote_page->ID === $the_id ) {
					// it is the keynote page
					$docheck = true;
					$type    = ICTU_GCCONF_CPT_KEYNOTE;
				} elseif ( $session_page->ID === $the_id ) {
					// it is the session page
					$docheck = true;
					$type    = ICTU_GCCONF_CPT_SESSION;
				}

				if ( $type === ICTU_GCCONF_CPT_SPEAKER ) {
					$colcount = 'grid--col-3';
				}

				// later toegevoegd: de mogelijkheid om te kiezen welke posts (speakers, sessions, keynotes) op
				// deze pagina getoond worden.
				// als 'template_conf_contenttypepage_filter' op 'filter_ja' staat, dan laten we alleen de
				// posts zien die de gebruiker heeft geselecteerd. Anders laten we alle posts zien, in de standaard
				// volgorde, namelijk alfabetisch op titel

				if ( 'filter_ja' == $userselectedposts ) {

					$posts = get_field( 'template_conf_contenttypepage_select_posts', $the_id );

					if ( $posts ):

						echo '<div class="grid ' . $colcount . ' ' . $type . '">';

						foreach ( $posts as $post ):
							setup_postdata( $post );

							$currentposttype = get_post_type( $post );
							$args            = array(
								'ID'       => $post,
								'titletag' => 'h2',
							);

							if ( $currentposttype === ICTU_GCCONF_CPT_SPEAKER ) {

								echo $this->fn_ictu_gcconf_frontend_write_speakercard( $args );

							} elseif ( $currentposttype === ICTU_GCCONF_CPT_KEYNOTE ) {

								echo $this->fn_ictu_gcconf_frontend_write_keynotecard( $args );

							} else {

								echo $this->fn_ictu_gcconf_frontend_write_sessioncard( $args );

							}

						endforeach;

						echo '</div>';

						wp_reset_postdata();

					endif;

				} else {

					$args = array(
						'posts_per_page' => - 1,
						'post_status'    => 'publish',
						'order'          => 'ASC',
						'orderby'        => 'post_title'
					);


					if ( $docheck && ! have_rows( 'blocks' ) ) {
						// no content has been selected for this page, so list ALL published content for this CPT

						$args['post_type'] = $type;

						$posts_for_cpt = new WP_query( $args );

						if ( $posts_for_cpt->have_posts() ) {


							echo '<div class="grid ' . $colcount . ' ' . $type . '">';

							while ( $posts_for_cpt->have_posts() ) : $posts_for_cpt->the_post();

								$postcounter ++;

								$currentposttype = get_post_type( $post );
								$args            = array(
									'ID'       => $post->ID,
									'titletag' => 'h2',
								);

								if ( $currentposttype === ICTU_GCCONF_CPT_SPEAKER ) {

									echo $this->fn_ictu_gcconf_frontend_write_speakercard( $args );

								} elseif ( $currentposttype === ICTU_GCCONF_CPT_KEYNOTE ) {

									echo $this->fn_ictu_gcconf_frontend_write_keynotecard( $args );

								} else {

									echo $this->fn_ictu_gcconf_frontend_write_sessioncard( $args );

								}

							endwhile;

							echo '</div>';

						}

						wp_reset_query();

					}
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
		public function gcconf_append_speaker_image() {

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
		public function gcconf_append_session_location_time() {

			global $post;

			$session_type  = wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_SESSIONTYPE );
			$session_level = wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_LEVEL );
			$metainfo      = '';

			if ( CONF_SHOW_DATETIMES ) {
				$time_term     = wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_TIMESLOT );
				$location_term = wp_get_post_terms( $post->ID, ICTU_GCCONF_CT_LOCATION );
			} else {
				$time_term     = '';
				$location_term = '';
			}

			$names    = [];
			$metainfo = [];

			if ( $time_term && ! is_wp_error( $time_term ) ) {

				foreach ( $time_term as $term ) {
					$names[] = $term->name;
				}

				$metainfo[0]['name'] = implode( ', ', $names );
				$metainfo[0]['type'] = 'time';
			}

			if ( $location_term && ! is_wp_error( $location_term ) ) {

				foreach ( $location_term as $term ) {
					$locations[] = $term->name;
				}

				$metainfo[1]['name'] = implode( ', ', $names );
				$metainfo[1]['type'] = 'time';
			}

			if ( $session_level && ! is_wp_error( $session_level ) ) {
				$level = [];

				foreach ( $session_level as $term ) {
					$level[] = $term->name;
				}

				$metainfo[2]['name'] = implode( ', ', $level );
				$metainfo[2]['type'] = 'level';
			}

			if ( $session_type && ! is_wp_error( $session_type ) ) {
				$type = [];

				foreach ( $session_type as $term ) {
					$type[] = $term->name;
				}

				$metainfo[3]['name'] = implode( ', ', $type );
				$metainfo[3]['type'] = 'session-type';
			}

			if ( $metainfo ) {

				echo cnf_make_meta( $metainfo );

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
				"name"                  => _x( 'Sessions', 'session type', 'ictuwp-plugin-conference' ),
				"singular_name"         => _x( 'Session', 'session type', 'ictuwp-plugin-conference' ),
				"menu_name"             => _x( 'Sessions', 'session type', 'ictuwp-plugin-conference' ),
				"all_items"             => _x( 'All sessions', 'session type', 'ictuwp-plugin-conference' ),
				"add_new"               => _x( 'Add new session', 'session type', 'ictuwp-plugin-conference' ),
				"add_new_item"          => _x( 'Add new session', 'session type', 'ictuwp-plugin-conference' ),
				"edit_item"             => _x( 'Edit session', 'session type', 'ictuwp-plugin-conference' ),
				"new_item"              => _x( 'New session', 'session type', 'ictuwp-plugin-conference' ),
				"view_item"             => _x( 'View session', 'session type', 'ictuwp-plugin-conference' ),
				"search_items"          => _x( 'Search session', 'session type', 'ictuwp-plugin-conference' ),
				"not_found"             => _x( 'No sessions found', 'session type', 'ictuwp-plugin-conference' ),
				"not_found_in_trash"    => _x( 'No sessions found', 'session type', 'ictuwp-plugin-conference' ),
				"featured_image"        => __( 'Featured image', 'ictuwp-plugin-conference' ),
				"archives"              => __( 'Archives', 'ictuwp-plugin-conference' ),
				"uploaded_to_this_item" => __( 'Uploaded media', 'ictuwp-plugin-conference' ),
			);

			$args = array(
				"label"               => _x( 'Sessions', 'session type', 'ictuwp-plugin-conference' ),
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
				"name"                  => _x( 'Keynotes', 'keynotes type', 'ictuwp-plugin-conference' ),
				"singular_name"         => _x( 'Keynote', 'keynotes type', 'ictuwp-plugin-conference' ),
				"menu_name"             => _x( 'Keynotes', 'keynotes type', 'ictuwp-plugin-conference' ),
				"all_items"             => _x( 'All keynotes', 'keynotes type', 'ictuwp-plugin-conference' ),
				"add_new"               => _x( 'Add new keynote', 'keynotes type', 'ictuwp-plugin-conference' ),
				"add_new_item"          => _x( 'Add new keynote', 'keynotes type', 'ictuwp-plugin-conference' ),
				"edit_item"             => _x( 'Edit keynote', 'keynotes type', 'ictuwp-plugin-conference' ),
				"new_item"              => _x( 'New keynote', 'keynotes type', 'ictuwp-plugin-conference' ),
				"view_item"             => _x( 'View keynote', 'keynotes type', 'ictuwp-plugin-conference' ),
				"search_items"          => _x( 'Search keynote', 'keynotes type', 'ictuwp-plugin-conference' ),
				"not_found"             => _x( 'No keynotes found', 'keynotes type', 'ictuwp-plugin-conference' ),
				"not_found_in_trash"    => _x( 'No keynotes found', 'keynotes type', 'ictuwp-plugin-conference' ),
				"featured_image"        => __( 'Featured image', 'ictuwp-plugin-conference' ),
				"archives"              => __( 'Archives', 'ictuwp-plugin-conference' ),
				"uploaded_to_this_item" => __( 'Uploaded media', 'ictuwp-plugin-conference' ),
			);

			$args = array(
				"label"               => _x( 'Keynotes', 'Stappen label', 'ictuwp-plugin-conference' ),
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
				"name"                  => _x( 'Speakers', 'speaker type', 'ictuwp-plugin-conference' ),
				"singular_name"         => _x( 'Speaker', 'speaker type', 'ictuwp-plugin-conference' ),
				"menu_name"             => _x( 'Speakers', 'speaker type', 'ictuwp-plugin-conference' ),
				"all_items"             => _x( 'All speakers', 'speaker type', 'ictuwp-plugin-conference' ),
				"add_new"               => _x( 'Add new speaker', 'speaker type', 'ictuwp-plugin-conference' ),
				"add_new_item"          => _x( 'Add new speaker', 'speaker type', 'ictuwp-plugin-conference' ),
				"edit_item"             => _x( 'Edit speaker', 'speaker type', 'ictuwp-plugin-conference' ),
				"new_item"              => _x( 'Edit speaker', 'speaker type', 'ictuwp-plugin-conference' ),
				"view_item"             => _x( 'View speaker', 'speaker type', 'ictuwp-plugin-conference' ),
				"search_items"          => _x( 'Search speaker', 'speaker type', 'ictuwp-plugin-conference' ),
				"not_found"             => _x( 'No speakers found', 'speaker type', 'ictuwp-plugin-conference' ),
				"not_found_in_trash"    => _x( 'No speakers found', 'speaker type', 'ictuwp-plugin-conference' ),
				"featured_image"        => __( 'Featured image', 'ictuwp-plugin-conference' ),
				"archives"              => __( 'Archives', 'ictuwp-plugin-conference' ),
				"uploaded_to_this_item" => __( 'Uploaded media', 'ictuwp-plugin-conference' ),
			);

			$args = array(
				"label"               => _x( 'Speakers', 'speaker type', 'ictuwp-plugin-conference' ),
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
				"name"          => _x( 'Timeblocks', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name" => _x( 'Timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' )
			);

			$labels = array(
				"name"                  => _x( 'Timeblocks', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name"         => _x( 'Timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"menu_name"             => _x( 'Timeblocks', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"all_items"             => _x( 'All timeblocks', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"add_new"               => _x( 'Add timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"add_new_item"          => _x( 'Add timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"edit_item"             => _x( 'Edit timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"new_item"              => _x( 'New timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"view_item"             => _x( 'View timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"search_items"          => _x( 'Search timeblock', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"not_found"             => _x( 'No timeblocks found', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"not_found_in_trash"    => _x( 'No timeblocks found', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"featured_image"        => __( 'Featured image', 'ictuwp-plugin-conference' ),
				"archives"              => __( 'Archives', 'ictuwp-plugin-conference' ),
				"uploaded_to_this_item" => __( 'Uploaded media', 'ictuwp-plugin-conference' ),
			);

			$args = array(
				"label"              => _x( 'Timeblocks', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"labels"             => $labels,
				"public"             => true,
				"hierarchical"       => true,
				"label"              => _x( 'Timeblocks', 'timeblock taxonomy', 'ictuwp-plugin-conference' ),
				"show_ui"            => true,
				"show_in_menu"       => true,
				"show_in_nav_menus"  => true,
				"query_var"          => true,
				"rewrite"            => array( 'slug' => ICTU_GCCONF_CT_TIMESLOT, 'with_front' => true, ),
				"show_admin_column"  => false,
				"show_in_rest"       => false,
				"rest_base"          => "",
				"show_in_quick_edit" => true,
			);
			register_taxonomy( ICTU_GCCONF_CT_TIMESLOT, array(
				ICTU_GCCONF_CPT_KEYNOTE,
				ICTU_GCCONF_CPT_SESSION
			), $args );


			// ---------------------------------------------------------------------------------------------------
			// Kosten taxonomie voor methode
			$labels = array(
				"name"          => _x( 'Session type', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name" => _x( 'Session types', 'session type taxonomy', 'ictuwp-plugin-conference' )
			);

			$labels = array(
				"name"                  => _x( 'Session types', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name"         => _x( 'Session type', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"menu_name"             => _x( 'Session types', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"all_items"             => _x( 'All session types', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"add_new"               => _x( 'Add session type', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"add_new_item"          => _x( 'Add session type', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"edit_item"             => _x( 'Edit session type', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"new_item"              => _x( 'New session type', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"view_item"             => _x( 'View session type', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"search_items"          => _x( 'Search session type', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"not_found"             => _x( 'No session types found', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"not_found_in_trash"    => _x( 'No session types found', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"featured_image"        => __( 'Featured image', 'ictuwp-plugin-conference' ),
				"archives"              => __( 'Archives', 'ictuwp-plugin-conference' ),
				"uploaded_to_this_item" => __( 'Uploaded media', 'ictuwp-plugin-conference' ),
			);

			$args = array(
				"label"              => _x( 'Session types', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"labels"             => $labels,
				"public"             => true,
				"hierarchical"       => true,
				"label"              => _x( 'Session types', 'session type taxonomy', 'ictuwp-plugin-conference' ),
				"show_ui"            => true,
				"show_in_menu"       => true,
				"show_in_nav_menus"  => true,
				"query_var"          => true,
				"rewrite"            => array( 'slug' => ICTU_GCCONF_CT_SESSIONTYPE, 'with_front' => true, ),
				"show_admin_column"  => false,
				"show_in_rest"       => false,
				"rest_base"          => "",
				"show_in_quick_edit" => true,
			);
			register_taxonomy( ICTU_GCCONF_CT_SESSIONTYPE, array( ICTU_GCCONF_CPT_SESSION ), $args );

			// ---------------------------------------------------------------------------------------------------
			// Expertise taxonomie voor methode
			$labels = array(
				"name"          => _x( 'Session levels', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name" => _x( 'Session level', 'session level taxonomy', 'ictuwp-plugin-conference' )
			);

			$labels = array(
				"name"                  => _x( 'Session levels', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name"         => _x( 'Session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"menu_name"             => _x( 'Session levels', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"all_items"             => _x( 'All session levels', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"add_new"               => _x( 'Add session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"add_new_item"          => _x( 'Add session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"edit_item"             => _x( 'Edit session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"new_item"              => _x( 'New session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"view_item"             => _x( 'View session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"search_items"          => _x( 'Search session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"not_found"             => _x( 'No search session levels found', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"not_found_in_trash"    => _x( 'No search session levels found', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"featured_image"        => __( 'Featured image', 'ictuwp-plugin-conference' ),
				"archives"              => __( 'Archives', 'ictuwp-plugin-conference' ),
				"uploaded_to_this_item" => __( 'Uploaded media', 'ictuwp-plugin-conference' ),
			);

			$args = array(
				"label"              => _x( 'Session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"labels"             => $labels,
				"public"             => true,
				"hierarchical"       => true,
				"label"              => _x( 'Session level', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"show_ui"            => true,
				"show_in_menu"       => true,
				"show_in_nav_menus"  => true,
				"query_var"          => true,
				"rewrite"            => array( 'slug' => ICTU_GCCONF_CT_LEVEL, 'with_front' => true, ),
				"show_admin_column"  => false,
				"show_in_rest"       => false,
				"rest_base"          => "",
				"show_in_quick_edit" => true,
			);
			register_taxonomy( ICTU_GCCONF_CT_LEVEL, array( ICTU_GCCONF_CPT_SESSION ), $args );

			// ---------------------------------------------------------------------------------------------------
			// Expertise taxonomie voor methode
			$labels = array(
				"name"          => _x( 'Session locations', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name" => _x( 'Session location', 'session location taxonomy', 'ictuwp-plugin-conference' )
			);

			$labels = array(
				"name"                  => _x( 'Session locations', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name"         => _x( 'Session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"menu_name"             => _x( 'Session locations', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"all_items"             => _x( 'All session locations', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"add_new"               => _x( 'Add session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"add_new_item"          => _x( 'Add session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"edit_item"             => _x( 'Edit session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"new_item"              => _x( 'New session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"view_item"             => _x( 'View session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"search_items"          => _x( 'Search session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"not_found"             => _x( 'No search session locations found', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"not_found_in_trash"    => _x( 'No search session locations found', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"featured_image"        => __( 'Featured image', 'ictuwp-plugin-conference' ),
				"archives"              => __( 'Archives', 'ictuwp-plugin-conference' ),
				"uploaded_to_this_item" => __( 'Uploaded media', 'ictuwp-plugin-conference' ),
			);

			$args = array(
				"label"              => _x( 'Session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"labels"             => $labels,
				"public"             => true,
				"hierarchical"       => true,
				"label"              => _x( 'Session location', 'session location taxonomy', 'ictuwp-plugin-conference' ),
				"show_ui"            => true,
				"show_in_menu"       => true,
				"show_in_nav_menus"  => true,
				"query_var"          => true,
				"rewrite"            => array( 'slug' => ICTU_GCCONF_CT_LOCATION, 'with_front' => true, ),
				"show_admin_column"  => false,
				"show_in_rest"       => false,
				"rest_base"          => "",
				"show_in_quick_edit" => true,
			);
			register_taxonomy( ICTU_GCCONF_CT_LOCATION, array(
				ICTU_GCCONF_CPT_SESSION,
				ICTU_GCCONF_CPT_KEYNOTE
			), $args );

			// ---------------------------------------------------------------------------------------------------
			// Expertise taxonomie voor methode
			$labels = array(
				"name"          => _x( 'Countries', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name" => _x( 'Country', 'session level taxonomy', 'ictuwp-plugin-conference' )
			);

			$labels = array(
				"name"                  => _x( 'Countries', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"singular_name"         => _x( 'Country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"menu_name"             => _x( 'Countries', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"all_items"             => _x( 'All countries', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"add_new"               => _x( 'Add country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"add_new_item"          => _x( 'Add country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"edit_item"             => _x( 'Edit country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"new_item"              => _x( 'New country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"view_item"             => _x( 'View country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"search_items"          => _x( 'Search country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"not_found"             => _x( 'No countries found', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"not_found_in_trash"    => _x( 'No countries found', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"featured_image"        => __( 'Featured image', 'ictuwp-plugin-conference' ),
				"archives"              => __( 'Archives', 'ictuwp-plugin-conference' ),
				"uploaded_to_this_item" => __( 'Uploaded media', 'ictuwp-plugin-conference' ),
			);

			$args = array(
				"label"              => _x( 'Country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"labels"             => $labels,
				"public"             => true,
				"hierarchical"       => true,
				"label"              => _x( 'Country', 'session level taxonomy', 'ictuwp-plugin-conference' ),
				"show_ui"            => true,
				"show_in_menu"       => true,
				"show_in_nav_menus"  => true,
				"query_var"          => true,
				"rewrite"            => array( 'slug' => ICTU_GCCONF_CT_COUNTRY, 'with_front' => true, ),
				"show_admin_column"  => false,
				"show_in_rest"       => false,
				"rest_base"          => "",
				"show_in_quick_edit" => true,
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
		public
		function fn_ictu_gcconf_filter_breadcrumb(
			$crumb = '', $args = ''
		) {

			global $post;

			$span_before_start   = '<span class="breadcrumb-link-wrap" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
			$span_between_start  = '<span itemprop="name">';
			$span_before_end     = '</span>';
			$brief_page_overview = '';

			if ( is_singular( ICTU_GCCONF_CPT_SESSION ) || is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {

				$crumb = get_the_title( get_the_id() );

			}

			// -------------------------------------------------------------------------------------------------

			if ( is_singular( ICTU_GCCONF_CPT_SPEAKER ) ) {

				$brief_page_overview = get_field( 'themesettings_conference_speakers', 'option' );

			} elseif ( is_singular( ICTU_GCCONF_CPT_KEYNOTE ) ) {

				$brief_page_overview = get_field( 'themesettings_conference_keynotes', 'option' );

			} elseif ( is_singular( ICTU_GCCONF_CPT_SESSION ) ) {

				$brief_page_overview = get_field( 'themesettings_conference_sessions', 'option' );

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

if ( ! function_exists( 'gc_wbvb_breadcrumbstring' ) ) {

	function gc_wbvb_breadcrumbstring( $currentpageID, $args ) {

		global $post;

		$crumb       = '';
		$postcounter = 0;

		if ( $currentpageID ) {
			$crumb       = '<a href="' . get_permalink( $currentpageID ) . '">' . get_the_title( $currentpageID ) . '</a>' . $args['sep'] . ' ' . get_the_title( $post->ID );
			$postparents = get_post_ancestors( $currentpageID );

			foreach ( $postparents as $postparent ) {
				$postcounter ++;
				$crumb = '<a href="' . get_permalink( $postparent ) . '">' . get_the_title( $postparent ) . '</a>' . $args['sep'] . $crumb;
			}
		}

		return $crumb;

	}

}

//========================================================================================================

// For the bidirectional saving to work, please activate the plugin 'ACF Post-2-Post'

//========================================================================================================

function fn_ictu_gcconf_extra_update_speaker_relationfield( $postid ) {

	$return                  = '';
	$list_of_speakers        = get_field( 'speaker_session_keynote_relations', $postid );
	$reservelijst            = get_field( 'speakers', $postid );
	$speakers_from_post_meta = get_post_meta( $postid, 'speaker_session_keynote_relations' );

	if ( ! $list_of_speakers ) {

		$list_of_speakers = get_field( 'speakers', $postid );

		if ( $list_of_speakers ) {

			$updatearray = array();

			foreach ( $list_of_speakers as $speaker ):

				$return .= '<li><a href="' . get_the_permalink( $postid ) . '">' . get_the_title( $postid ) . '</a> - <a href="' . get_the_permalink( $speaker ) . '">' . get_the_title( $speaker ) . '</a></li>';

				$updatearray[ $speaker ] = $speaker;

			endforeach;

			update_field( 'speaker_session_keynote_relations', $updatearray, $postid );

		} else {
			$return .= '<li>Geen sprekers</li>';
		}

		$return .= '</ul>';

	} else {

		foreach ( $list_of_speakers as $speaker ):

			$return .= '<li>Want: <a href="' . get_the_permalink( $postid ) . '">' . get_the_title( $postid ) . '</a> - <a href="' . get_the_permalink( $speaker ) . '">' . get_the_title( $speaker ) . '</a></li>';

		endforeach;

		$return .= '</ul>';


	}

	if ( WP_DEBUG && CONF_DEBUG ) {

		echo $return;
		echo '</div>';

	}

}

//========================================================================================================

function fn_ictu_gcconf_footer_disable_tuesday() {
	?>
	<script>
		jQuery(document).ready(function () {
			//put your js code here

			if (jQuery("#days2").length) {
				jQuery("#days2").attr('disabled', 'disabled');
			}

			if (jQuery("#separator-days2").length) {
				jQuery("#separator-days2").addClass('disabled');
			}

		})
	</script>

	<?php
}

add_action( 'wp_footer', 'fn_ictu_gcconf_footer_disable_tuesday' );

//========================================================================================================

/**
 * Load plugin textdomain.
 */
add_action( 'init', 'fn_ictu_gcconf_load_plugin_textdomain' );

function fn_ictu_gcconf_load_plugin_textdomain() {

	load_plugin_textdomain( 'ictuwp-plugin-conference', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

//========================================================================================================

/**
 * Have the ACF (advanced custom fields) plugin read the settings from this plugin's acf-json folder
 *
 * @since    1.0.0
 */
function fn_ictu_gcconf_add_acf_folder( $paths ) {

	// append path
	$paths[] = plugin_dir_path( __FILE__ ) . 'acf-json';

	// return
	return $paths;

}

add_filter( 'acf/settings/load_json', 'fn_ictu_gcconf_add_acf_folder' );

//========================================================================================================

/**
 * Have the ACF (advanced custom fields) plugin save settings to a json file in this plugin's acf-json folder
 *
 * @since    1.0.0
 */
function fn_ictu_gcconf_acf_json_save_point( $path ) {

	// update path
	$path = plugin_dir_path( __FILE__ ) . 'acf-json';

	// return
	return $path;

}

add_filter( 'acf/settings/save_json', 'fn_ictu_gcconf_acf_json_save_point' );

//========================================================================================================
