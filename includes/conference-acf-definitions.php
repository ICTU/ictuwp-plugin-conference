<?php

/*
// Gebruiker Centraal - conference-acf-definitions.php
// ----------------------------------------------------------------------------------
// ACF definities voor conference plugin
// ----------------------------------------------------------------------------------
// @package   ictu-gc-posttypes-inclusie
// @author    Paul van Buuren
// @license   GPL-2.0+
// @version   0.0.1
// @desc.     Eerste opzet.
// @link      https://github.com/ICTU/Gebruiker-Centraal---Inclusie---custom-post-types-taxonomies
 */


if( ! function_exists('ictu_gcconf_initialize_acf_fields') ) {

	function ictu_gcconf_initialize_acf_fields() {
	
		if( function_exists('acf_add_local_field_group') ):


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
								'label' => 'block_title',
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
								'key' => 'field_5d93502647396',
								'label' => 'block_title_id',
								'name' => 'block_title_id',
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
								'key' => 'field_5d93503e47397',
								'label' => 'block_free_text',
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
								'label' => 'block_extra_type',
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
									'none' => 'No extra items',
									'keynotes' => 'Keynotes',
									'events' => 'Events',
									'sessions' => 'Sessions',
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
					array(
						'key' => 'field_5d933fd673c87',
						'label' => 'Speakers',
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
									'website' => 'Website',
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

		endif;
    }
}    
    
