<?php
/**
 * @package   Options_Framework
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2016 WP Theming
 */

class Options_Framework_Interface {

	
	/**
	 * Generates the tabs that are used in the options menu
	 */
	static function optionsframework_tabs() {
		$counter = 0;
		$options = & Options_Framework::_optionsframework_options();
		$options = apply_filters( 'of_options', $options );
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
				$class = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower($class) ) . '-tab';
				$menu[ $index ] = '<a id="options-group-'. $index . '-tab" class="nav-tab ' . $class .'" title="' . esc_attr( $value['name'] ) . '" href="' . esc_attr( '#options-group-'. $index ) . '">' . esc_html( $value['name'] ) . '</a>';
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
	static function optionsframework_submit() { ?>
				</div>
				<div id="optionsframework-submit">
					<input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'options-framework' ); ?>" />
					<?php $prev_proof = get_option( 'wd_maintenance_notification_proof' ); ?>
					<?php $next_notice = wds_create_daily_notification_schedule(); ?>
					<?php if($prev_proof) : ?>
						<p class="wd_notification_events">
							<span class="wd_last_notification_sent" data-prev-notice="<?php echo $prev_proof; ?>">
								<?php printf(__('Last notification: %s'), date(' F j, Y' , $prev_proof ) ); ?>	
							</span>
							<span class="wd_notification_scheduled" data-next-notice="">
							</span>	
						</p>
					<?php elseif( wds_create_daily_notification_schedule() ): ?>
						<p class="wd_notification_events"><span class="wd_notification_scheduled" data-next-notice="<?php echo $next_proof; ?>"><?php print( __('Maintanace notifications are scheduled.') ); ?></span></p>
					<?php endif; ?>
					<input type="submit" class="reset-button button-secondary hide" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'options-framework' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'options-framework' ) ); ?>' );" />
					<div class="clear"></div>
				</div>
			</form>

		<?php
	}
	/**
	 * Generates the options fields that are used in the form.
	 */
	static function optionsframework_fields() {

		global $allowedtags;
		$optionsframework_settings = get_option( 'optionsframework' );

		// Gets the unique option id
		if ( isset( $optionsframework_settings['id'] ) ) {
			$option_name = $optionsframework_settings['id'];
		}
		else {
			$option_name = 'optionsframework';
		};

		$settings = get_option($option_name);
		$options = & Options_Framework::_optionsframework_options();

		$options = apply_filters( 'of_options', $options );

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
				if ( isset( $value['type'] ) ) {
					$class .= ' section-' . $value['type'];
				}
				if ( isset( $value['class'] ) ) {
					$class .= ' ' . $value['class'];
				}

				$output .= '<div id="' . esc_attr( $id ) .'" class="' . esc_attr( $class ) . '">'."\n";
				if ( isset( $value['name'] ) ) {
					$output .= '<h4 class="heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";
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

			if ( has_filter( 'optionsframework_' . $value['type'] ) ) {
				$output .= apply_filters( 'optionsframework_' . $value['type'], $option_name, $value, $val );
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
					$output .= '<textarea id="' . esc_attr( $value['id'] ) . '" class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" rows="' . $rows . '">' . apply_filters( 'of_'.$value['id'], esc_textarea( $val ) ) . '</textarea>';
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
					$output .= Options_Framework_Media_Uploader::optionsframework_uploader( $value['id'], $val, null );

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
						'sizes' => of_recognized_font_sizes(),
						'faces' => of_recognized_font_faces(),
						'styles' => of_recognized_font_styles(),
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
					$typography_fields = apply_filters( 'of_typography_fields', $typography_fields, $typography_stored, $option_name, $value );
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

					// Background Image
					if ( !isset($background['image']) || empty( $background['image'] )) {
						$background['image'] = admin_url( 'images/wordpress-logo.svg' );
					}
					$output .= Options_Framework_Media_Uploader::optionsframework_uploader( $value['id'], $background['image'], null, esc_attr( $option_name . '[' . $value['id'] . '][image]' ) );
					
					$class = 'of-background-properties';
					if ( '' == $background['image'] ) {
						$class .= ' hide';
					}
					$output .= '<div class="' . esc_attr( $class ) . '">';

					// Background Repeat
					$output .= '<select class="of-background of-background-repeat" name="' . esc_attr( $option_name . '[' . $value['id'] . '][repeat]'  ) . '" id="' . esc_attr( $value['id'] . '_repeat' ) . '">';
					$repeats = of_recognized_background_repeat();

					foreach ($repeats as $key => $repeat) {
						$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['repeat'], $key, false ) . '>'. esc_html( $repeat ) . '</option>';
					}
					$output .= '</select>';

					// Background Position
					$output .= '<select class="of-background of-background-position" name="' . esc_attr( $option_name . '[' . $value['id'] . '][position]' ) . '" id="' . esc_attr( $value['id'] . '_position' ) . '">';
					$positions = of_recognized_background_position();

					foreach ($positions as $key=>$position) {
						$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['position'], $key, false ) . '>'. esc_html( $position ) . '</option>';
					}
					$output .= '</select>';

					// Background Attachment
					$output .= '<select class="of-background of-background-attachment" name="' . esc_attr( $option_name . '[' . $value['id'] . '][attachment]' ) . '" id="' . esc_attr( $value['id'] . '_attachment' ) . '">';
					$attachments = of_recognized_background_attachment();

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


					$output .= wp_nonce_field( Options_Framework_Admin_Color_Schemes::NONCE, '_acs_ofnonce', null, false );

					$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][name]' ) . '" id="' . esc_attr( $value['id'] . '_name' ) . '" class="of-hidden of-scheme"  type="hidden" value="' . esc_attr( get_bloginfo( 'name' ) ) . '" />';
					


					$output .= '<label class="explain" for="' . esc_attr( $value['id'] . '_must_use' ) . '">';
					$output .= '<input name="' . esc_attr( $option_name . '[' . $value['id'] . '][must_use]' ) . '" id="' . esc_attr( $value['id'] . '_must_use' ) . '"  class="checkbox of-input of-scheme" type="checkbox" '. checked( @$scheme['must_use'], 'on', false ) .' />';
					$output .= ' Must use?</label>';
				
					$output .= '<p class="explain">' . esc_html( $value['must_use'] ) . '</p>';
					
					$output .= '<p class="button-group"><button class="button show-basic-scheme hide-if-no-js active button-secondary">Basic</button><button class="button show-advanced-scheme hide-if-no-js button-secondary">Advanced</button></p>';

					$output .= '</div>';

					$output .= '<div class="of-scheme section section-color-scheme-pickers">';


					$default = array();

					$admin_schemes = Options_Framework_Admin_Color_Schemes::get_instance();

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
						// $output .= '</div>' . "\n";

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
					$output .= '<div id="options-group-' . $value['order'] . '" class="group ' . $class . '">';
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

						add_action( "of_function_{$id}", $value['function'] );
						ob_start();
							do_action( "of_function_{$id}" );
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

						if ( $wrap['end'] ) {
							/*if ( 0 < $wrapper ) {
								$output .= str_repeat( '</div>'."\n", $wrapper );
								$wrapper = 0;
							}*/
							/*$wrapper = 0;
							if ( $counter >= 2 ) {
								$output .= '</div>'."\n";
							}*/

						} elseif ( $wrap['start'] ) {
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
			if ( isset( $value['rule'] ) && ! empty( $value['id'] ) ) {
				$output .= Options_Framework_Interface::optionsframework_rule( $value['rule'], $value['id'] );
			}
			if ( ( $value['type'] != "heading" ) && ( $value['type'] != "form" ) && ( $value['type'] != "info" ) ) {
				$output .= '</div>';
				if ( ( $value['type'] != "checkbox" ) && ( $value['type'] != "editor" ) && ( $value['type'] != "scheme" ) ) {
					$output .= '<p class="explain">' . wp_kses( $explain_value, $allowedtags) . '</p>'."\n";
				}
				$output .= '</div></div>'."\n";
			}

			echo $output;

			if ( $value['type'] === "form" && isset( $value['wrap']['start'] ) && null== $value['wrap']['end'] ) {
				settings_fields( 'optionsframework' );
			}elseif ( $value['type'] === "form" && isset( $value['wrap']['end'] ) && null == $value['wrap']['start'] ) {
				/* <submit></form> */
				
				if ( $form ) { 
					$form=0;
					Options_Framework_Interface::optionsframework_submit(); 
				}
			}
		}

		if ( 0 < $wrapper ) {
			echo str_repeat( '</div>'."\n", $wrapper );
			$wrapper = 0;
		}

		// Outputs closing div if there tabs
		if ( Options_Framework_Interface::optionsframework_tabs() != '' ) {
			// echo '</div>';
		}

	}

	/**
	 * Generates the tabs that are used in the options menu
	 */
	static function optionsframework_rule($rule=null, $id=null) {

		if(is_null($rule)||is_null($id)){ return; }

		$set = isset($rule['set'])?$rule['set']:"";
		$exe = isset($rule['exe'])?$rule['exe']:"";

		if((empty($set)||!is_array($set))&&(empty($exe)||!is_array($exe))){ return; }


		// exe

		elseif(!empty($exe)&&is_array($exe)){

			$format = array(
			'script' => "
<script type='text/javascript'>
	jQuery('#section-%1\$s :input').on('%2\$s',function(){ 
		var val    = jQuery('#section-%1\$s :input')%3\$s%4\$s.val(), 
		    target = jQuery('#%5\$s'); 
		%6\$s;
	}).trigger('%2\$s');
</script>",

			'exec' => "
				target.%s(%s);
				",

			'filter' => ".filter('%s')",

			'not' => ".not('%s')"
			);

			// global $optionsframework_rules; // $optionsframework_rules = ( isset( $optionsframework_rules ) ) ? $optionsframework_rules : "" ;
			$filter = isset($rule['filter'])? sprintf( $format['filter'], $rule['filter'] ):"";
			$not    = isset($rule['not'])   ? sprintf( $format['not'],    $rule['not']    ):"";

			$action ="";
			foreach ( $exe as $method => $value ) {
				$action .= sprintf( $format['exec'], $method, $value );
			}
			
			$jrule = sprintf( $format['script'], $id, $rule['on'], $filter, $not, $rule['id'], $action );

		}


		//set

		elseif(!empty($set)&&is_array($set)){

			$format = array(
			'script' => "
<script type='text/javascript'>
	jQuery('#section-%1\$s :input').on('%2\$s',function(){ 
		var val   = jQuery('#section-%1\$s :input')%3\$s%4\$s.val(), 
		    field = jQuery('#%5\$s').closest('.section'); 
		switch (val) {
			%6\$s
		}
	}).trigger('%2\$s');
</script>",

			'case' => "
			case '%s':
				field.%s();
				break;
				",

			'filter' => ".filter('%s')",

			'not' => ".not('%s')"
			);

			// global $optionsframework_rules; // $optionsframework_rules = ( isset( $optionsframework_rules ) ) ? $optionsframework_rules : "" ;
			$filter = isset($rule['filter'])? sprintf( $format['filter'], $rule['filter'] ):"";
			$not    = isset($rule['not'])   ? sprintf( $format['not'],    $rule['not']    ):"";

			$cases ="";
			foreach ( $set as $value => $method ) {
				$cases .= sprintf( $format['case'], $method, $value );
			}
			
			$jrule = sprintf( $format['script'], $rule['id'], $rule['on'], $filter, $not, $id, $cases );

		}
		return $jrule;
	}

}