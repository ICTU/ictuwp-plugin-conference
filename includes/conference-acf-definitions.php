<?php

/*
// Conference Plugin (Gebruiker Centraal) - conference-acf-definitions.php
// ----------------------------------------------------------------------------------
// ACF definities voor conference plugin
// ----------------------------------------------------------------------------------
// @package   		ictuwp-plugin-conference
// @author    		Paul van Buuren
// @license   		GPL-2.0+
// @version   		2.0.2
// @desc.     		Renamed packages, checked translation.
// @link			https://github.com/ICTU/Gebruiker-Centraal---Inclusie---custom-post-types-taxonomies
 */


if( ! function_exists('fn_ictu_gcconf_initialize_acf_fields') ) {


	function fn_ictu_gcconf_initialize_acf_fields() {
	
		if( function_exists('acf_add_local_field_group') ) {

			//------------------------------------------------------------------------------------------------
			// blocks constellation
			acf_add_local_field_group(array(
				'key' => 'group_5d934c9673bba',
				'title' => 'Blocks for conference page',
				'fields' => array(
					array(
						'key' => 'field_5d934ff86ca91',
						'label' => 'blocks',
						'name' => 'blocks',
						'type' => 'repeater',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'collapsed' => 'field_5d93501247395',
						'min' => 0,
						'max' => 0,
						'layout' => 'row',
						'button_label' => '',
						'sub_fields' => array(
							array(
								'key' => 'field_5d93501247395',
								'label' => 'Title',
								'name' => 'block_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
							array(
								'key' => 'field_5d9aea653f7d6',
								'label' => 'Time',
								'name' => 'block_time',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
							array(
								'key' => 'field_5d93502647396',
								'label' => 'ID',
								'name' => 'block_title_id',
								'type' => 'text',
								'instructions' => 'The ID so you can directly link to this block. If you leave this empty, the title will be used to create an ID.',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
							array(
								'key' => 'field_5d93503e47397',
								'label' => 'Free text',
								'name' => 'block_free_text',
								'type' => 'wysiwyg',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'tabs' => 'all',
								'toolbar' => 'basic',
								'media_upload' => 1,
								'delay' => 0,
							),
							array(
								'key' => 'field_5d93507bb14f2',
								'label' => 'Add extra content?',
								'name' => 'block_extra_type',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array(
									'none' => 'No extra content',
									'keynotes' => 'Yes, add keynotes',
									'events' => 'Yes, add events',
									'sessions' => 'Yes, add sessions',
									'speakers' => 'Yes, add speakers',
								),
								'allow_null' => 0,
								'other_choice' => 0,
								'default_value' => 'none',
								'layout' => 'vertical',
								'return_format' => 'value',
								'save_other_choice' => 0,
							),
							array(
								'key' => 'field_5d93543710593',
								'label' => 'Sessions',
								'name' => 'block_sessions',
								'type' => 'repeater',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_5d93507bb14f2',
											'operator' => '==',
											'value' => 'sessions',
										),
									),
								),
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'collapsed' => '',
								'min' => 0,
								'max' => 0,
								'layout' => 'row',
								'button_label' => 'Add another session',
								'sub_fields' => array(
									array(
										'key' => 'field_5d9354969c462',
										'label' => 'Session',
										'name' => 'block_sessions_session',
										'type' => 'post_object',
										'instructions' => '',
										'required' => 0,
										'conditional_logic' => 0,
										'wrapper' => array(
											'width' => '',
											'class' => '',
											'id' => '',
										),
										'post_type' => array(
											0 => 'session',
										),
										'taxonomy' => '',
										'allow_null' => 0,
										'multiple' => 0,
										'return_format' => 'object',
										'ui' => 1,
									),
									array(
										'key' => 'field_5d9354b79c463',
										'label' => 'Time',
										'name' => 'block_sessions_session_time',
										'type' => 'taxonomy',
										'instructions' => '',
										'required' => 0,
										'conditional_logic' => 0,
										'wrapper' => array(
											'width' => '',
											'class' => '',
											'id' => '',
										),
										'taxonomy' => 'timeslot',
										'field_type' => 'radio',
										'allow_null' => 0,
										'add_term' => 1,
										'save_terms' => 0,
										'load_terms' => 0,
										'return_format' => 'id',
										'multiple' => 0,
									),
									array(
										'key' => 'field_5d9355be9ebbd',
										'label' => 'Location',
										'name' => 'block_sessions_session_location',
										'type' => 'taxonomy',
										'instructions' => '',
										'required' => 0,
										'conditional_logic' => 0,
										'wrapper' => array(
											'width' => '',
											'class' => '',
											'id' => '',
										),
										'taxonomy' => 'location',
										'field_type' => 'radio',
										'allow_null' => 0,
										'add_term' => 1,
										'save_terms' => 0,
										'load_terms' => 0,
										'return_format' => 'id',
										'multiple' => 0,
									),
								),
							),
							array(
								'key' => 'field_5d93618779c32',
								'label' => 'Events',
								'name' => 'block_events',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_5d93507bb14f2',
											'operator' => '==',
											'value' => 'events',
										),
									),
								),
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array(
									0 => 'event',
								),
								'taxonomy' => '',
								'filters' => array(
									0 => 'search',
									1 => 'taxonomy',
								),
								'elements' => '',
								'min' => '',
								'max' => '',
								'return_format' => 'object',
							),
							array(
								'key' => 'field_5d93622c77470',
								'label' => 'Keynotes',
								'name' => 'block_keynotes',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_5d93507bb14f2',
											'operator' => '==',
											'value' => 'keynotes',
										),
									),
								),
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array(
									0 => 'keynote',
								),
								'taxonomy' => '',
								'filters' => array(
									0 => 'search',
									1 => 'taxonomy',
								),
								'elements' => '',
								'min' => '',
								'max' => '',
								'return_format' => 'object',
							),
							array(
								'key' => 'field_5d9affb1f2725',
								'label' => 'Speakers',
								'name' => 'block_speakers',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_5d93507bb14f2',
											'operator' => '==',
											'value' => 'speakers',
										),
									),
								),
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array(
									0 => 'speaker',
								),
								'taxonomy' => '',
								'filters' => array(
									0 => 'search',
									1 => 'taxonomy',
								),
								'elements' => '',
								'min' => '',
								'max' => '',
								'return_format' => 'object',
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'page_template',
							'operator' => '==',
							'value' => 'conf-overviewpage.php',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));


			//------------------------------------------------------------------------------------------------
			// relation of a session / keynote and its speakers
			acf_add_local_field_group(array(
				'key' => 'group_5d933fd1c6977',
				'title' => 'Speaker / speakers for this session or keynote',
				'fields' => array(
/*
					array(
						'key' => 'field_5d933fd673c87',
						'label' => 'NIET GEBRUIKEN',
						'name' => 'speakers',
						'type' => 'relationship',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'speaker',
						),
						'taxonomy' => '',
						'filters' => array(
							0 => 'search',
						),
						'elements' => array(
							0 => 'featured_image',
						),
						'min' => '',
						'max' => '',
						'return_format' => 'id',
					),
*/					
					array(
						'key' => 'field_5da0567590bed',
						'label' => 'Speakers',
						'name' => 'speaker_session_keynote_relations',
						'type' => 'relationship',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'speaker',
						),
						'taxonomy' => '',
						'filters' => array(
							0 => 'search',
							1 => 'taxonomy',
						),
						'elements' => array(
							0 => 'featured_image',
						),
						'min' => '',
						'max' => '',
						'return_format' => 'id',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'session',
						),
					),
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'keynote',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));


			//------------------------------------------------------------------------------------------------
			// links and info for a speaker
			acf_add_local_field_group(array(
				'key' => 'group_5d933b3db71f1',
				'title' => 'Speaker info: title + links',
				'fields' => array(
					array(
						'key' => 'field_5d934539f178c',
						'label' => 'Job title',
						'name' => 'speaker_jobtitle',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5d933b43b0738',
						'label' => 'Links',
						'name' => 'speaker_links',
						'type' => 'repeater',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'collapsed' => 'field_5d933b8283558',
						'min' => 0,
						'max' => 0,
						'layout' => 'row',
						'button_label' => 'Add new link',
						'sub_fields' => array(
							array(
								'key' => 'field_5d933b6c83557',
								'label' => 'URL',
								'name' => 'speaker_link_url',
								'type' => 'url',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
							),
							array(
								'key' => 'field_5d933b8283558',
								'label' => 'Link text',
								'name' => 'speaker_link_text',
								'type' => 'text',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => 'Link',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
							array(
								'key' => 'field_5d933bc183559',
								'label' => 'Type',
								'name' => 'speaker_link_type',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array(
									'linkedin' => 'LinkedIn',
									'twitter' => 'Twitter',
									'facebook' => 'facebook',
									'personallink' => 'Website',
									'other' => 'Other',
								),
								'allow_null' => 0,
								'other_choice' => 0,
								'default_value' => 'other',
								'layout' => 'vertical',
								'return_format' => 'value',
								'save_other_choice' => 0,
							),
						),
					),
					array(
						'key' => 'field_5da0567590bee',
						'label' => 'Keynotes or sessions',
						'name' => 'speaker_session_keynote_relations',
						'type' => 'relationship',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => ICTU_GCCONF_CPT_SESSION,
							1 => ICTU_GCCONF_CPT_KEYNOTE,
						),
						'taxonomy' => '',
						'filters' => array(
							0 => 'search',
							1 => 'taxonomy',
						),
						'elements' => array(
							0 => 'featured_image',
						),
						'min' => '',
						'max' => '',
						'return_format' => 'object',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'speaker',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'acf_after_title',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));


			//------------------------------------------------------------------------------------------------
			// Theme settings
			acf_add_local_field_group(array(
				'key' => 'group_5d9b3421a5abc',
				'title' => 'Conference theme settings',
				'fields' => array(
					array(
						'key' => 'field_5d9b3433962bb',
						'label' => 'Speaker page',
						'name' => 'themesettings_conference_speakers',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'page',
						),
						'taxonomy' => '',
						'allow_null' => 0,
						'multiple' => 0,
						'return_format' => 'object',
						'ui' => 1,
					),
					array(
						'key' => 'field_5d9b350ad4a5e',
						'label' => 'Keynotes page',
						'name' => 'themesettings_conference_keynotes',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'page',
						),
						'taxonomy' => '',
						'allow_null' => 0,
						'multiple' => 0,
						'return_format' => 'object',
						'ui' => 1,
					),
					array(
						'key' => 'field_5d9b3cb4d0d6e',
						'label' => 'Sessions page',
						'name' => 'themesettings_conference_sessions',
						'type' => 'post_object',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'page',
						),
						'taxonomy' => '',
						'allow_null' => 0,
						'multiple' => 0,
						'return_format' => 'object',
						'ui' => 1,
					),
					array(
						'key' => 'field_5d9dedf5c7277',
						'label' => 'Fallback for speaker images',
						'name' => 'fallback_for_speaker_images',
						'type' => 'relationship',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'attachment',
						),
						'taxonomy' => '',
						'filters' => array(
							0 => 'search',
						),
						'elements' => array(
							0 => 'featured_image',
						),
						'min' => 1,
						'max' => '',
						'return_format' => 'id',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'options_page',
							'operator' => '==',
							'value' => 'instellingen',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));


			//------------------------------------------------------------------------------------------------
			// Velden voor versla
			acf_add_local_field_group(array(
				'key' => 'group_5de10c8cc1188',
				'title' => 'Velden voor verslag',
				'fields' => array(
					array(
						'key' => 'field_5de10cb8379d1',
						'label' => 'Links',
						'name' => 'extra_info_repeater',
						'type' => 'repeater',
						'instructions' => 'Een link naar een video of een geschreven verslag, blog, etc. Als je hier de URL invoert voor een video op Vimeo en je selecteert \'video\' als type, dan maakt het systeem er een embedded video van.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'collapsed' => 'field_5de10f0f379d3',
						'min' => 0,
						'max' => 0,
						'layout' => 'row',
						'button_label' => 'Add new link',
						'sub_fields' => array(
							array(
								'key' => 'field_5de10d31379d2',
								'label' => 'URL',
								'name' => 'extra_info_repeater_url',
								'type' => 'url',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => 'https://example.com/page',
							),
							array(
								'key' => 'field_5de10f0f379d3',
								'label' => 'Link-tekst',
								'name' => 'extra_info_repeater_linktext',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => 'Link',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
							array(
								'key' => 'field_5de10f46379d4',
								'label' => 'Type',
								'name' => 'extra_info_repeater_type',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array(
									'video' => 'Video',
									'website' => 'Blog',
									'presentation' => 'Link to presentation',
									'download' => 'Download',
								),
								'allow_null' => 0,
								'other_choice' => 0,
								'default_value' => 'website',
								'layout' => 'vertical',
								'return_format' => 'value',
								'save_other_choice' => 0,
							),
							array(
								'key' => 'field_5de1157e802b6',
								'label' => 'Korte beschrijving',
								'name' => 'extra_info_repeater_shortdescription',
								'type' => 'textarea',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'maxlength' => '',
								'rows' => '',
								'new_lines' => '',
							),
						),
					),
/*					
					array(
						'key' => 'field_5de11136b431f',
						'label' => 'Foto\'s',
						'name' => 'extra_info_images',
						'type' => 'image',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'array',
						'preview_size' => 'medium',
						'library' => 'all',
						'min_width' => '',
						'min_height' => '',
						'min_size' => '',
						'max_width' => '',
						'max_height' => '',
						'max_size' => '',
						'mime_types' => '',
					),
*/					
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'session',
						),
					),
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'keynote',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));

			acf_add_local_field_group(array(
				'key' => 'group_5df00a354531d',
				'title' => 'Keynotes, sessies of sprekers',
				'fields' => array(
					array(
						'key' => 'field_5df00baa46e03',
						'label' => 'Alles tonen of alleen een selectie?',
						'name' => 'template_conf_contenttypepage_filter',
						'type' => 'radio',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'filter_nee' => 'Toon alles',
							'filter_ja' => 'Toon alleen wat ik hieronder selecteer',
						),
						'allow_null' => 0,
						'other_choice' => 0,
						'default_value' => '',
						'layout' => 'vertical',
						'return_format' => 'value',
						'save_other_choice' => 0,
					),
					array(
						'key' => 'field_5df00a7cee7ac',
						'label' => 'Selecteer',
						'name' => 'template_conf_contenttypepage_select_posts',
						'type' => 'relationship',
						'instructions' => 'Selecteer keynotes, sessies of sprekers en zet ze op de juist volgorde op de pagina',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_5df00baa46e03',
									'operator' => '==',
									'value' => 'filter_ja',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array(
							0 => 'session',
							1 => 'speaker',
							2 => 'keynote',
						),
						'taxonomy' => '',
						'filters' => array(
							0 => 'search',
							1 => 'post_type',
							2 => 'taxonomy',
						),
						'elements' => '',
						'min' => 1,
						'max' => '',
						'return_format' => 'object',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'page_template',
							'operator' => '==',
							'value' => 'conf-contenttypepage.php',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'acf_after_title',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));

		} // if( function_exists('acf_add_local_field_group') ) {


    } //	function fn_ictu_gcconf_initialize_acf_fields() {
	
}    
