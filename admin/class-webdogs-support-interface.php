<?php

defined( 'WPINC' ) or die;
/**
 * @package   Webdogs
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2016 WP Theming
 */


class Webdogs_Interface {

	
	/**
	 * Generates the tabs that are used in the options menu
	 */
	static function wds_tabs() {
		$counter = 0;
		$options = & Webdogs_Options::_wds_options();
		$options = apply_filters( 'wds_options', $options );
		$menu = array();

		$indexes = array_values( array_map( 'absint', wp_list_pluck( array_values($options), 'order' ) ) );

		foreach ( $options as $value ) {
			// Heading for Navigation
			if ( $value['type'] == "heading" ) {

				$counter++;

				if( isset( $value['order'] ) ) {
					$index = $value['order'];

				} elseif( !in_array( $counter, $indexes ) ) {
					$index = $counter;
					$indexes[] = $counter;

				} else {
					while ( in_array( $counter, $indexes )) {
						$counter++; }
					$index = $counter;
					$indexes[] = $counter;
				}
		
				$class = '';
				$class = ! empty( $value['id'] ) ? $value['id'] : $value['name'];
				$class = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower($class) );
				$menu[ $index ] = '<a id="'. $class . '-tab" class="nav-tab ' . $class . '-tab" title="' . esc_attr( $value['name'] ) . '" href="' . esc_attr( '#'. $class . '-section' ) . '">' . esc_html( $value['name'] ) . '</a>';
			}
		}
		// sort numeric 
		// for custom ordering. 
		// from disordered source.
		ksort( $menu, SORT_NUMERIC );

		$menu = implode( "\n", $menu );

		return $menu;
	}

	/**
	 * Generates the tabs that are used in the options menu
	 */
	static function wds_submit() { ?>
				</div>
				<div id="wds-submit">
					<input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'webdogs-support' ); ?>" />
					
					<?php Self::wds_maintenance_notification(); ?>

					<input type="submit" class="reset-button button-secondary hide" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'webdogs-support' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'webdogs-support' ) ); ?>' );" />
					<div class="clear"></div>
				</div>
			</form>

		<?php
	}
	/**
	 * Generates the tabs that are used in the options menu
	 */
	static function wds_maintenance_notification() { 

	 	$prev_proof = get_option( 'wds_maintenance_notification_proof' ); 
	 	$next_notice = wp_next_scheduled( 'wds_scheduled_notification' ); 

	 	if( $prev_proof ) : ?>

			<p class="wds_notification_events">
				<span class="wds_last_notification_sent" data-prev-notice="<?php echo $prev_proof; ?>">
					<?php printf(__('Last notification: %s'), date(' F j, Y' , $prev_proof ) ); ?>	
				</span>

					<?php if( $next_notice ): ?>

					<span class="wds_notification_scheduled" data-next-notice="<?php echo $next_notice; ?>"><?php print( __('Maintanace notifications are scheduled.') ); ?></span>
				
				<?php else: ?>

					<span class="wds_notification_scheduled" data-next-notice="">
					</span>	

				<?php endif; ?>

			</p>

		<?php elseif( $next_notice ): ?>

			<p class="wds_notification_events"><span class="wds_notification_scheduled" data-next-notice="<?php echo $next_notice; ?>"><?php print( __('Maintanace notifications are scheduled.') ); ?></span></p>
		
		<?php endif; 
	}

	/**
	 * Generates the options fields that are used in the form.
	 */
	static function wds_fields() {

		global $allowedtags;
		$wds_settings = get_option( 'webdogs_support' );

		// Gets the unique option id
		if ( isset( $wds_settings['id'] ) ) {
			$option_name = $wds_settings['id'];
		}
		else {
			$option_name = 'webdogs-support';
		};

		$settings = get_option($option_name);
		$options = & Webdogs_Options::_wds_options();

		$options = apply_filters( 'wds_options', $options );

		$counter = 0;
		$wrapper = 0;
		$form = 0;
		$menu = '';

		foreach ( $options as $value ) {

			$val = '';
			$select_value = '';
			$output = '';

			// Wrap all options
			if ( ( $value['type'] != "heading" ) && ( $value['type'] != "form" ) && ( $value['type'] != "info" ) ) {

				// Keep all ids lowercase with no spaces
				$value['id'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($value['id']) );

				$id = 'section-' . $value['id'];

				$class = 'section';

				$help_text = '';
				$help = '';

				if ( isset( $value['type'] ) ) {
					$class .= ' section-' . $value['type'];
				}
				if ( isset( $value['class'] ) ) {
					$class .= ' ' . $value['class'];
				}
				if ( isset( $value['desc'] ) ) {
					$help_text .= ' title="' . strip_tags( $value['desc'] ) . '"';
					$help = '<button type="button" class="dashicons dashicons-editor-help"'.$help_text.'><span class="screen-reader-text">'. esc_html( $value['desc'] )  .'</span></button>';
				}

				$output .= '<div id="' . esc_attr( $id ) .'" class="' . esc_attr( $class ) . '">'."\n";
				if ( isset( $value['name'] ) ) {
					$output .= '<h4 class="heading">' . esc_html( $value['name'] ). $help . '</h4>' . "\n";
				}
				if ( $value['type'] == 'scheme' ) {

					$output .= get_submit_button( __( 'Preview', 'admin-color-schemes' ), 'secondary preview-scheme alignright hide-if-no-js', 'preview_scheme', false );
					$output .= get_submit_button( __( 'Clear', 'admin-color-schemes' ), 'secondary preview-scheme alignright hide-if-no-js', 'clear_scheme', false );

				}
				if ( $value['type'] != 'editor' ) {
					$output .= '<div class="option">' . "\n" . '<div class="controls">' . "\n";
				}
				else {
					$output .= '<div class="option">' . "\n" . '<div>' . "\n";
				}
			}

			// Set default value to $val
			if ( isset( $value['std'] ) ) {
				$val = $value['std'];
			}

			// If the option is already saved, override $val
			if ( ( $value['type'] != 'heading' ) && ( $value['type'] != 'form' ) && ( $value['type'] != 'info') ) {
				if ( isset( $settings[($value['id'])]) ) {
					$val = $settings[($value['id'])];
					// Striping slashes of non-array options
					if ( !is_array($val) ) {
						$val = stripslashes( $val );
					}
				}
			}

			// If there is a description save it for labels
			$explain_value = '';
			if ( isset( $value['desc'] ) ) {
				$explain_value = $value['desc'];
			}

			if ( has_filter( 'wds_' . $value['type'] ) ) {
				$output .= apply_filters( 'wds_' . $value['type'], $option_name, $value, $val );
			}


			switch ( $value['type'] ) {



				// Basic text input
				case 'text':
					$output .= '<input id="' . esc_attr( $value['id'] ) . '" class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" type="text" value="' . esc_attr( $val ) . '" />';
					break;



				// Password input
				case 'password':
					$output .= '<input id="' . esc_attr( $value['id'] ) . '" class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" type="password" value="' . esc_attr( $val ) . '" />';
					break;



				// Textarea
				case 'textarea':
					$rows = '8';

					if ( isset( $value['settings']['rows'] ) ) {
						$custom_rows = $value['settings']['rows'];
						if ( is_numeric( $custom_rows ) ) {
							$rows = $custom_rows;
						}
					}

					$val = stripslashes( $val );
					$output .= '<textarea id="' . esc_attr( $value['id'] ) . '" class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" rows="' . $rows . '">' . apply_filters( 'wds_'.$value['id'], esc_textarea( $val ) ) . '</textarea>';
					break;



				// Select Box
				case 'select':
					$output .= '<select class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '">';

					foreach ($value['options'] as $key => $option ) {
						$output .= '<option'. selected( $val, $key, false ) .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
					}
					$output .= '</select>';
					break;



				// Radio Box
				case "radio":
					$name = $option_name .'['. $value['id'] .']';
					foreach ($value['options'] as $key => $option) {
						$id = $option_name . '-' . $value['id'] .'-'. $key;
						$output .= '<input class="of-input of-radio" type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="'. esc_attr( $key ) . '" '. checked( $val, $key, false) .' /><label for="' . esc_attr( $id ) . '">' . esc_html( $option ) . '</label>';
					}
					break;



				// Image Selectors
				case "images":
					$name = $option_name .'['. $value['id'] .']';
					foreach ( $value['options'] as $key => $option ) {
						$selected = '';
						if ( $val != '' && ($val == $key) ) {
							$selected = ' of-radio-img-selected';
						}
						$output .= '<input type="radio" id="' . esc_attr( $value['id'] .'_'. $key) . '" class="of-radio-img-radio" value="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" '. checked( $val, $key, false ) .' />';
						$output .= '<div class="of-radio-img-label">' . esc_html( $key ) . '</div>';
						$output .= '<img src="' . esc_url( $option ) . '" alt="' . $option .'" class="of-radio-img-img' . $selected .'" onclick="document.getElementById(\''. esc_attr($value['id'] .'_'. $key) .'\').checked=true;" />';
					}
					break;



				// Checkbox
				case "checkbox":
					$output .= '<input id="' . esc_attr( $value['id'] ) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" '. checked( $val, 1, false) .' />';
					$output .= '<label class="explain" for="' . esc_attr( $value['id'] ) . '">' . wp_kses( $explain_value, $allowedtags) . '</label>';
					break;



				// Multicheck
				case "multicheck":
					foreach ($value['options'] as $key => $option) {
						$checked = '';
						$label = $option;
						$option = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($key));

						$id = $option_name . '-' . $value['id'] . '-'. $option;
						$name = $option_name . '[' . $value['id'] . '][' . $option .']';

						if ( isset($val[$option]) ) {
							$checked = checked($val[$option], 1, false);
						}

						$output .= '<input id="' . esc_attr( $id ) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr( $name ) . '" ' . $checked . ' /><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
					}
					break;



				// Color picker
				case "color":
					$default_color = '';
					if ( isset($value['std']) ) {
						if ( $val !=  $value['std'] )
							$default_color = ' data-default-color="' .$value['std'] . '" ';
					}
					$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '" class="of-color"  type="text" value="' . esc_attr( $val ) . '"' . $default_color .' />';

					break;



				// Uploader
				case "upload":
					$val = ( !empty($val) ) ? $val : admin_url( 'images/wordpress-logo.svg' );
					$output .= Webdogs_Media_Uploader::wds_uploader( $value['id'], $val, null );

					break;



				// Typography
				case 'typography':

					unset( $font_size, $font_style, $font_face, $font_color );

					$typography_defaults = array(
						'size' => '',
						'face' => '',
						'style' => '',
						'color' => ''
					);

					$typography_stored = wp_parse_args( $val, $typography_defaults );

					$typography_options = array(
						'sizes' => wds_recognized_font_sizes(),
						'faces' => wds_recognized_font_faces(),
						'styles' => wds_recognized_font_styles(),
						'color' => true
					);

					if ( isset( $value['options'] ) ) {
						$typography_options = wp_parse_args( $value['options'], $typography_options );
					}

					// Font Size
					if ( $typography_options['sizes'] ) {
						$font_size = '<select class="of-typography of-typography-size" name="' . esc_attr( $option_name . '[' . $value['id'] . '][size]' ) . '" id="' . esc_attr( $value['id'] . '_size' ) . '">';
						$sizes = $typography_options['sizes'];
						foreach ( $sizes as $i ) {
							$size = $i . 'px';
							$font_size .= '<option value="' . esc_attr( $size ) . '" ' . selected( $typography_stored['size'], $size, false ) . '>' . esc_html( $size ) . '</option>';
						}
						$font_size .= '</select>';
					}

					// Font Face
					if ( $typography_options['faces'] ) {
						$font_face = '<select class="of-typography of-typography-face" name="' . esc_attr( $option_name . '[' . $value['id'] . '][face]' ) . '" id="' . esc_attr( $value['id'] . '_face' ) . '">';
						$faces = $typography_options['faces'];
						foreach ( $faces as $key => $face ) {
							$font_face .= '<option value="' . esc_attr( $key ) . '" ' . selected( $typography_stored['face'], $key, false ) . '>' . esc_html( $face ) . '</option>';
						}
						$font_face .= '</select>';
					}

					// Font Styles
					if ( $typography_options['styles'] ) {
						$font_style = '<select class="of-typography of-typography-style" name="'.$option_name.'['.$value['id'].'][style]" id="'. $value['id'].'_style">';
						$styles = $typography_options['styles'];
						foreach ( $styles as $key => $style ) {
							$font_style .= '<option value="' . esc_attr( $key ) . '" ' . selected( $typography_stored['style'], $key, false ) . '>'. $style .'</option>';
						}
						$font_style .= '</select>';
					}

					// Font Color
					if ( $typography_options['color'] ) {
						$default_color = '';
						if ( isset($value['std']['color']) ) {
							if ( $val !=  $value['std']['color'] )
								$default_color = ' data-default-color="' .$value['std']['color'] . '" ';
						}
						$font_color = '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][color]' ) . '" id="' . esc_attr( $value['id'] . '_color' ) . '" class="of-color of-typography-color  type="text" value="' . esc_attr( $typography_stored['color'] ) . '"' . $default_color .' />';
					}

					// Allow modification/injection of typography fields
					$typography_fields = compact( 'font_size', 'font_face', 'font_style', 'font_color' );
					$typography_fields = apply_filters( 'wds_typography_fields', $typography_fields, $typography_stored, $option_name, $value );
					$output .= implode( '', $typography_fields );

					break;



				// Background
				case 'background':

					$background = $val;

					// Background Color
					$default_color = '';
					if ( isset( $value['std']['color'] ) ) {
						if ( $val !=  $value['std']['color'] )
							$default_color = ' data-default-color="' .$value['std']['color'] . '" ';
					}
					$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][color]' ) . '" id="' . esc_attr( $value['id'] . '_color' ) . '" class="of-color of-background-color"  type="text" value="' . esc_attr( $background['color'] ) . '"' . $default_color .' />';

					$default_image = admin_url( 'images/wordpress-logo.svg' );
					if ( isset( $value['std']['image'] ) ) {
						if ( $val !=  $value['std']['image'] )
							$default_image = $value['std']['image'];
					}
					// Background Image
					if ( !isset($background['image']) || empty( $background['image'] )) {
						$background['image'] = $default_image;
					}
					$output .= Webdogs_Media_Uploader::wds_uploader( $value['id'], $background['image'], null, esc_attr( $option_name . '[' . $value['id'] . '][image]' ) );
					
					$class = 'of-background-properties';
					if ( '' == $background['image'] ) {
						$class .= ' hide';
					}
					$output .= '<div class="' . esc_attr( $class ) . '">';

					// Background Repeat
					$output .= '<select class="of-background of-background-repeat" name="' . esc_attr( $option_name . '[' . $value['id'] . '][repeat]'  ) . '" id="' . esc_attr( $value['id'] . '_repeat' ) . '">';
					$repeats = wds_recognized_background_repeat();

					foreach ($repeats as $key => $repeat) {
						$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['repeat'], $key, false ) . '>'. esc_html( $repeat ) . '</option>';
					}
					$output .= '</select>';

					// Background Position
					$output .= '<select class="of-background of-background-position" name="' . esc_attr( $option_name . '[' . $value['id'] . '][position]' ) . '" id="' . esc_attr( $value['id'] . '_position' ) . '">';
					$positions = wds_recognized_background_position();

					foreach ($positions as $key=>$position) {
						$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['position'], $key, false ) . '>'. esc_html( $position ) . '</option>';
					}
					$output .= '</select>';

					// Background Attachment
					$output .= '<select class="of-background of-background-attachment" name="' . esc_attr( $option_name . '[' . $value['id'] . '][attachment]' ) . '" id="' . esc_attr( $value['id'] . '_attachment' ) . '">';
					$attachments = wds_recognized_background_attachment();

					foreach ($attachments as $key => $attachment) {
						$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['attachment'], $key, false ) . '>' . esc_html( $attachment ) . '</option>';
					}
					$output .= '</select>';
					$output .= '</div>';

					break;



				// Admin color scheme
				case 'scheme':

					$scheme = $val;

					$class = 'of-scheme-properties color-scheme-pickers';

					$output .= '<div class="' . esc_attr( $class ) . '">';

					
					$output .= '<div class="of-scheme bottom-pad alignright small section">';
					
					// $output .= '<p class="explain">' . esc_html( $explain_value ) . '</p>';


					$output .= wp_nonce_field( Webdogs_Admin_Color_Schemes::NONCE, '_acs_ofnonce', null, false );

					$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][name]' ) . '" id="' . esc_attr( $value['id'] . '_name' ) . '" class="of-hidden of-scheme"  type="hidden" value="' . esc_attr( get_bloginfo( 'name' ) ) . '" />';
					


					$output .= '<label class="explain" for="' . esc_attr( $value['id'] . '_must_use' ) . '">';
					$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][must_use]' ) . '" id="' . esc_attr( $value['id'] . '_must_use' ) . '"  class="checkbox of-input of-scheme" type="checkbox" '. checked( @$scheme['must_use'], 'on', false ) .' />';
					$output .= ' Must use?</label>';
				
					$output .= '<p class="explain">' . esc_html( $value['must_use'] ) . '</p>';
					
					$output .= '<p class="button-group"><button class="button show-basic-scheme hide-if-no-js active button-secondary">Basic</button><button class="button show-advanced-scheme hide-if-no-js button-secondary">Advanced</button></p>';

					$output .= '</div>';

					$output .= '<div class="of-scheme section section-color-scheme-pickers">';


					$default = array();

					$admin_schemes = Webdogs_Admin_Color_Schemes::get_instance();

					$scheme = $admin_schemes->get_color_scheme();

					$loops = $admin_schemes->get_colors( 'basic' );

					foreach ( $loops as $handle => $nicename ):

						//  Color
						$default[$handle] = '';

						if ( isset( $value['std'][$handle] ) && isset( $scheme->{$handle} ) ) {
							if ( $scheme->{$handle} !=  $value['std'][$handle] )
								$default[$handle] = ' data-default-color="' . $value['std'][$handle] . '" ';
						}
						
						$sass_handle = ' data-handle="$'.strtolower( str_replace( '_','-', $handle ) ).'"';

						$output .= '<div class="color-scheme-picker">';
						$output .= '<p class="explain">' . esc_html( $nicename ) . '</p>';
						$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][' . $handle . ']' ) . '" id="' . esc_attr( $value['id'] . '_'. $handle ) . '" class="of-color of-scheme"  type="text" value="' . esc_attr( $scheme->{$handle} ) . '"' . $default[ $handle ] . $sass_handle .' />';
						$output .= '</div>';
					endforeach;

					// $output .= '<button id="preview" class="button small-button preview-scheme hide-if-no-js" data-nonce="'. wp_create_nonce( 'admin-color-schemes-save' ) .'">Preview</button>';

					$loops = $admin_schemes->get_colors( 'advanced' );


					$output .= '</div>';

					$output .= '<div class="advanced-color-scheme-pickers section inset clear top-border bottom-pad hide-if-js">';

					foreach ( $loops as $handle => $nicename ):

						//  Color
						$default[$handle] = '';

						if ( isset( $value['std'][$handle] ) && isset( $scheme->{$handle} ) ) {
							if ( $scheme->{$handle} !=  $value['std'][$handle] )
								$default[$handle] = ' data-default-color="' . $value['std'][$handle] . '" ';
						}
						$sass_handle = ' data-handle="$'.strtolower( str_replace( '_','-', $handle ) ).'"';

						$output .= '<div class="color-scheme-picker">';
						$output .= '<p class="explain">' . esc_html( $nicename ) . '</p>';
						$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][' . $handle . ']' ) . '" id="' . esc_attr( $value['id'] . '_'. $handle ) . '" class="of-color of-scheme"  type="text" value="' . esc_attr( $scheme->{$handle} ) . '"' . $default[ $handle ] . $sass_handle .' />';
						$output .= '</div>';
					endforeach;


					$output .= '</div>';

					$output .= '</div>';

					break;



				// Editor
				case 'editor':
					$output .= '<p class="explain">' . wp_kses( $explain_value, $allowedtags ) . '</p>'."\n";
					echo $output;
					$textarea_name = esc_attr( $option_name . '[' . $value['id'] . ']' );
					$default_editor_settings = array(
						'textarea_name' => $textarea_name,
						'media_buttons' => false,
						'tinymce' => array( 'plugins' => 'wordpress,wplink' )
					);
					$editor_settings = array();
					if ( isset( $value['settings'] ) ) {
						$editor_settings = $value['settings'];
					}
					$editor_settings = array_merge( $default_editor_settings, $editor_settings );
					wp_editor( $val, $value['id'], $editor_settings );
					$output = '';
					break;



				// Info
				case "info":
					$id = '';
					$wrap = array(
						'start'=>false,
						'end'=>false);
					$class = 'section';

					if ( isset( $value['wrap'] ) && is_array( $value['wrap'] ) ) {
						
						$wrap['start'] = isset( $value['wrap']['start'] );
						  $wrap['end'] = isset( $value['wrap']['end'] );

						if ( $wrap['start'] ) {
							$wrapper++;
						}
						elseif ( $wrap['end'] ) {
							$wrapper--;
						}
						
					}
					if ( isset( $value['id'] ) && ! $wrap['end'] ) {
						$id = 'id="' . esc_attr( $value['id'] ) . '" ';
					}
					if ( isset( $value['type'] ) && ! $wrap['end'] ) {
						$class .= ' section-' . $value['type'];
					}
					if ( isset( $value['class'] ) && ! $wrap['end'] ) {
						$class .= ' ' . $value['class'];
					}
					if ( isset( $value['wrap']['class'] ) ) {
						$wrap_class = $value['wrap']['class'];
					}
					
					if( ! isset( $value['wrap'] ) ) {

						$output .= '<div ' . $id . 'class="' . esc_attr( $class ) . '">' . "\n";

						if( isset($value['name']) || isset( $value['desc'] ) ){

							if ( isset($value['name']) ) {
								$output .= '<h4 class="heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";
							}
							if ( isset( $value['desc'] ) ) {
								$output .= '<p class="explain">' . $value['desc'] . '</p>' . "\n";
							}
						} 

					} else {

						if( $wrap['start'] ) { 
							$output .= '<div ' . $id . 'class="' . esc_attr( $wrap_class ) . '">' . "\n";
						}

						if( isset($value['name']) || isset( $value['desc'] ) ){

							$output .= '<div class="' . esc_attr( $class ) . '">' . "\n";

							if ( isset($value['name']) ) {
								$output .= '<h4 class="heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";
							}
							if ( isset( $value['desc'] ) ) {
								$output .= '<p class="explain">' . $value['desc'] . '</p>' . "\n";
							}
							$output .= '</div>' . "\n";
						}

					}
					if( ! isset( $value['wrap'] ) || ( $wrap['end'] && 0 <= $wrapper ) ) {
						if ( $wrap['end'] ) {
							$output .= '<div class="clear"></div>' . "\n";
						}
						$output .= '</div>' . "\n";
					}
					break;



				// Heading for Navigation
				case "heading":
					if ( 0 < $wrapper && $form ) {
						$output .= str_repeat( '</div>'."\n", $wrapper );
						$wrapper = 0;
					}
					$counter++;
					if ( $counter >= 2 && $form ) {
						$output .= '</div>'."\n";
					}
					
					$class = '';
					$class = ! empty( $value['id'] ) ? $value['id'] : $value['name'];
					$class = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($class) );
					$output .= '<div id="' . $class . '-section" class="group ' . $class . '">';
					$output .= '<h3>' . esc_html( $value['name'] ) . '</h3>' . "\n";
					
					if ( isset( $value['function'] ) ) {
						// Keep all ids lowercase with no spaces
						$value['id'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower( ! empty( $value['id'] ) ? $value['id'] : $value['name'] ) );

						$id = 'section-' . $value['id'];

						$class = 'section';
						if ( isset( $value['type'] ) ) {
							$class .= ' section-function';
						}
						if ( isset( $value['class'] ) ) {
							$class .= ' ' . $value['class'];
						}

						$output .= '<div id="' . esc_attr( $id ) .'" class="' . esc_attr( $class ) . '">'."\n";

						add_action( "wds_function_{$id}", $value['function'] );
						ob_start();
							do_action( "wds_function_{$id}" );
						$output .= ob_get_clean();
						$output .= '</div>';
					}
					break;



				// form
				case "form":
					$id = '';
					$wrap = array(
						'start'=>false,
						'end'=>false);
					$class = 'inline';
					$properties = "";

					if ( isset( $value['options'] ) && is_array( $value['options'] ) ) {
						foreach ($value['options'] as $prop => $val)
							$properties .= sprintf('%s="%s"', sanitize_key( $prop ), esc_attr( $val ) );
					}
					if ( isset( $value['wrap'] ) && is_array( $value['wrap'] ) ) {
						
						$wrap['start'] = isset( $value['wrap']['start'] );
						  $wrap['end'] = isset( $value['wrap']['end'] );

						if ( $wrap['start'] ) {
							$form = 1;
						}
					}

					if ( ! isset( $value['wrap']['end'] ) || isset( $value['wrap']['start'] ) ) {
					
						$output .= "<form ";
						$output .= $properties;

						if ( isset( $value['id'] ) ) {
							$output .= 'id="' . esc_attr( $value['id'] ) . '" ';
						}
						if ( isset( $value['name'] ) ) {
							$output .= 'name="' . esc_attr( $value['name'] ) . '" ';
						}
						$output .= ">";
					}

					break;

			}

			// END SWITCH OUTPUT

			if ( ( $value['type'] != "heading" ) && ( $value['type'] != "form" ) && ( $value['type'] != "info" ) ) {
				$output .= '</div>';
				if ( ( $value['type'] != "checkbox" ) && ( $value['type'] != "editor" ) && ( $value['type'] != "scheme" ) ) {
					$output .= '<p class="explain">' . wp_kses( $explain_value, $allowedtags) . '</p>'."\n";
				}
				$output .= '</div></div>'."\n";
			}

			echo $output;

			if ( $value['type'] === "form" && isset( $value['wrap']['start'] ) && null== $value['wrap']['end'] ) {
				settings_fields( 'webdogs-support' );
			}elseif ( $value['type'] === "form" && isset( $value['wrap']['end'] ) && null == $value['wrap']['start'] ) {
				/* <submit></form> */
				
				if ( $form ) { 
					$form=0;
					Webdogs_Interface::wds_submit(); 
				}
			}
		}

		// Outputs closing divs
		if ( 0 < $wrapper ) {
			echo str_repeat( '</div>'."\n", $wrapper );
			$wrapper = 0;
		}

		// Outputs closing div if there tabs
		if ( Webdogs_Interface::wds_tabs() != '' ) {
			// echo '</div>';
		}

		// OUTPUT DYNAMIC JS 
		// FOR CONDITIONAL FIEILD UX 
		Self::wds_rules();

		// var_export(Webdogs_Login_Logo::$instance);

	}

	/**
	 * Generates the tabs that are used in the options menu
	 */
	static function wds_rules() {
		$counter = 0;
		$options = &Webdogs_Options::_wds_options();
		$options = apply_filters( 'wds_options', $options );

		/////////////////
		// JQUERY SCRIPT 
		// 	SELECTORS 
		//  METHODS
		// 	WRAPPERS 
		/////////////////
		/////////////////
		$format = array( 

			'this' => "jQuery('#section-%1\$s :input')",

			'on' => "%1\$s.on('%2\$s', %3\$s).trigger('%2\$s');\n\n\t",

			'val' => "jQuery('#section-%1\$s :input')%2\$s%3\$s.val()",

			'field' => "jQuery('#section-%1\$s')%2\$s",

			'target' => "jQuery('#%1\$s')%2\$s",

			'filter' => ".filter('%s')",

			'not' => ".not('%s')",

			'find' => ".find('%s')",

			'case' => "%s()",

			'exec' => "%s(%s)",

			'func' => "function(){ \n\t\t\t\t%s\n\t\t\t}",

			'return' => "function(){ \n\t\t\t\treturn %s;\n\t\t\t}",

			'pass' => "function( val ){ \n\t\t\t\t%s;\n\t\t\t}",

			'cases' => "\n\t\t\t\tcase '%1\$s':\n\t\t\t\t\twds_options['callback']['switch'][%2\$d]();\n\t\t\t\t\t\tbreak;\n\t\t\t\t\t",

			'passes' => "\n\t\t\t\tvar val = wds_options['return'][%1\$d]();\n\t\t\t\twds_options['callback']['pass'][%2\$d]( val );\n\t\t\t", 

			'switches' => "\n\t\t\t\tvar val = wds_options['return'][%1\$d]();\n\t\t\t\tswitch( val ) { \n\t\t\t\t\t%2\$s\n\t\t\t\t}\n\t\t\t", 

		);


		$allrule = array();
		$allfunc = array();
		$allswch = array();
		$allvalu = array();

		$rule_index = wp_list_pluck( array_values( $options ), 'rule', 'id' );

		foreach ( $rule_index as $id => $rule ) {

			$filter = isset($rule['filter'])? sprintf( $format['filter'], $rule['filter'] ):"";
			$not    = isset($rule['not'])   ? sprintf( $format['not'],    $rule['not']    ):"";
			$find   = isset($rule['find'])  ? sprintf( $format['find'],   $rule['find']   ):"";
			$on     = $rule['on'];

			if ( isset( $rule['set'] ) ) {

				$el     = sprintf( $format['this'],  $rule['id']);
				// $on     = sprintf( $format['on'],    $rule['on']);
				$val    = sprintf( $format['val'],   $rule['id'], $filter, $not);
				$field  = sprintf( $format['field'], $id, $find);

				///////////////////////////
				// SUPER MAGIC ARRAY SET //  PLEASE... 
				///////////////////////////  DO NOT TOUCH THIS !
			  	$set = array_combine(
					(call_user_func_array( 'array_merge', 
						(array_map( 
							(function($v){ 
								return array_map('strval', ( is_array($v)?$v:array($v)));
							}),
							array_values( $rule['set'] )
						))
					)), 
					(array_map(
						(function( $va, $i ) use ( $rule, $format ) { 
							$va['value']=strval( $i ); 
							foreach( $rule['set'] as $method=>$value ) { 
								if( is_array( $value ) && in_array( $i, $value ) ) { 
									array_push( $va['method'], sprintf( $format['case'], $method ) ); 
								} elseif( $i == $value ) {
									array_push( $va['method'], sprintf( $format['case'], $method ) ); 
								} 
							} 
							return $va; 
						}), 
						(array_fill_keys( 
							(call_user_func_array( 'array_merge',
								(array_map(
									(function($v){
										return array_map('strval', ( is_array($v)?$v:array($v)));
									}), 
									array_values($rule['set']) 
								))
							)), 
							array('value'=> null,'method'=>array() )
						)), 
						(call_user_func_array( 'array_merge',
							(array_map(
								(function($v){ 
									return array_map('strval', ( is_array($v)?$v:array($v)));
								}), 
								array_values($rule['set'])
							))
						))
					))
  				);
				
				array_map( 
					(function( $v ) use ( $rule, $el, $on, $val, &$allrule, &$allfunc, &$allvalu, &$allswch, $set, $field ) {  
						
						if( is_array( $set[ strval($v) ]['method'] ) )
							$set[ strval($v) ]['method'] = implode('.', $set[ strval($v) ]['method'] );

						$callback =	implode('.', array( $field, $set[ strval($v) ]['method'] ) );

						////////////////////////////
						// SET MASTER METHOD INDEXES
						////////////////////////////
						array_push( $allfunc, $callback );
						$allfunc = array_unique( $allfunc );
						//INDEX FOR TRIGGERED METHODS
						$funcindex = strval(array_search( $callback, $allfunc ));

						array_push( $allvalu, $val );
						$allvalu = array_unique( $allvalu );
						//INDEX FOR VALUE RETURNING METHODS
						$valuindex = strval( array_search( $val, $allvalu ) );

						//////////////////////
						// SET COMPILER ARRAYS
						//////////////////////
						if( ! isset( $allrule[ $el ][ $on ]['switch']['val'][ strval($valuindex) ]['case'][ strval($v) ]['callback'] ) )
							$allrule[ $el ][ $on ]['switch']['val'][ strval($valuindex) ]['case'][ strval($v) ]['callback'] = array();
						
						if( ! isset( $allswch['val'][ strval($valuindex) ]['case'][ strval($v) ]['callback'] ) )
							$allswch['val'][ strval($valuindex) ]['case'][ strval($v) ]['callback'] = array();

						array_push( $allswch['val'][ strval($valuindex) ]['case'][ strval($v) ]['callback'], strval($funcindex) );
						array_push( $allrule[ $el ][ $on ]['switch']['val'][ strval($valuindex) ]['case'][ strval($v) ]['callback'], strval($funcindex) );

						return $v; 
					}), 
					array_keys( $set )
				);
			}



			if( isset( $rule['exe'] ) ) {

				$el     = sprintf( $format['this'],   $id);
				// $on     = sprintf( $format['on'],     $rule['on']);
				$val    = sprintf( $format['val'],    $id, $filter, $not );
				$target = sprintf( $format['target'], $rule['id'], $find );

				///////////////////////////
				// SUPER MAGIC ARRAY SET //  PLEASE... 
				///////////////////////////  DO NOT TOUCH THIS !
				$set = call_user_func_array( 'array_merge',
					(array_map(
						(function( $va ) use ( $rule, $format ) { 
							foreach( $rule['exe'] as $method => $value ) { 
								if( is_array( $value ) && ! empty( $value ) ) { 
					  				array_push( $va['method'], sprintf( $format['exec'], $method, implode(', ', $value ) ) );
					  			} else { 
					  				array_push( $va['method'], sprintf( $format['exec'], $method, $value ) ); 
					  			} 
							} 
							return $va; 
						}),
						(array_fill( 0, sizeof($rule['exe']), array( 'method'=>array() )))
					))
  				);

				if( is_array($set['method']) )
					$set['method'] = implode('.', $set['method'] );

				$callback =	implode('.', array( $target, $set['method'] ) );

				////////////////////////////
				// SET MASTER METHOD INDEXES
				////////////////////////////
				array_push( $allfunc, $callback );
				$allfunc = array_unique( $allfunc );
				//INDEX FOR TRIGGERED METHODS
				$funcindex = array_search( $callback, $allfunc );

				array_push( $allvalu, $val );
				$allvalu = array_unique( $allvalu );
				//INDEX FOR VALUE RETURNING METHODS
				$valuindex = strval( array_search( $val, $allvalu ) );

				//////////////////////
				// SET COMPILER ARRAYS
				//////////////////////
				$func = array();
				$func['val'] = strval($valuindex);
				$func['callback'] = strval($funcindex);

				if( ! isset( $allrule[ $el ][ $on ]['function'] ) )
					$allrule[ $el ][ $on ]['function'] = array();

				array_push( $allrule[ $el ][ $on ]['function'], $func );
			}
		}
		//////////////////////////////////
		// COMPOUND COMPILER DATA ARRAY // 
		//////////////////////////////////

		$wds_options = array( 'condition' => $allrule, 'callback' => $allfunc, 'return' => $allvalu, 'switch' => $allswch );
		
		$i = 0;
		$switch_callbacks = array();

		$conditions = "";
		foreach ($wds_options['condition'] as $elem => $triggers ) {

			foreach( $triggers as $trigger => $actions ) {
				$trigger_actions = "";
				if( isset( $actions['switch'] ) && !empty( $actions['switch'] ) ) {
					$switches = "";
					foreach ( $actions['switch']['val'] as $val => &$case ) {
						$cases = "";
						if( isset( $case['case'] ) && !empty( $case['case'] ) ){
							foreach ($case['case'] as $value => &$callback ) {
								if( isset( $callback['callback'] ) && !empty( $callback['callback'] ) ) {
									$switch_callbacks[ $i ] = sprintf( $format['func'], implode( ";\n\t\t\t\t", array_map( (function( $v ) use ( $wds_options ){ return $wds_options['callback'][ $v ]; }), $callback['callback'] ) ) );
									$callback['callback'] = $i;
									$cases .= sprintf($format['cases'], strval($value), $i );
									$i++;
								}
							}
						}
						$switches .= sprintf($format['switches'], strval($val), $cases );
					}
					$trigger_actions .= $switches;
				}
				if( isset( $actions['function'] ) && !empty( $actions['function'] ) ){
					$passes = "";
					foreach ( $actions['function'] as &$action ) {
						$passes .= sprintf($format['passes'], $action['val'], $action['callback'] );
					}
					$trigger_actions .= $passes;
				}
				$conditions .= sprintf($format['on'], $elem, $trigger, sprintf($format['func'], $trigger_actions ));
			}
		}

// OUTPUT THE WDS OPTIONS OBJECT
// JS LIB FOR CONDITIONAL FIELDS 
?>

<script type="text/javascript">

var wds_options = {

	return: [ 

			<?php echo implode( ",\n\t\t\t", array_map( function($v) use ( $format ){ return sprintf( $format['return'], $v ); }, $wds_options['return'] ) ); ?>

		],

	callback: {

		switch: [ 

			<?php echo implode( ",\n\t\t\t", $switch_callbacks ); ?>
			
		],

		pass: [ 

			<?php echo implode( ",\n\t\t\t", array_map( function($v) use ( $format ){ return sprintf( $format['pass'], $v ); }, $wds_options['callback'] ) ); ?>

		]
	}
};
	<?php echo $conditions; ?>

</script>
<?

		return $allrulez;
	}
}