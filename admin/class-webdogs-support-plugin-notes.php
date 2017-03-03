<?php
/*
PluginName: Oomph Plugin Notes
PluginURI: http://www.oomphinc.com/plugins-modules/oomph-plugin-notes
Description: Add usage notes to your plugins
Author: Ben Doherty @ Oomph, Inc.
Version: 0.1.0
AuthorURI: http://www.oomphinc.com/people/bdoherty/
License: GPLv2 or later

		Copyright © 2016 Oomph, Inc. <http://oomphinc.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @package Oomph Plugin Notes
 */
class Webdogs_Support_Plugin_Notes {
	// Store plugin notes in this option
	const OPTION = 'wds_plugin_notes';

	// Capability to see hidden tags
	const CAPABILITY = 'manage_support';

	// Nonce action
	const NONCE = 'wds-plugin-notes';

    /**
     * wds_l10n
     */
    public function plugin_notes_l10n( $l10n = array() ) {
        return $l10n + array(
			'save' => __( 'Save', 'webdogs-support' ),
			'cancel' => __( 'Cancel', 'webdogs-support' )
		);
    }

	/**
     * localize_script
     *
     * @since 2.3.4
     */
	public function localize_script( $localize_script = array() ) {
		global $current_screen;

		if( $current_screen->base === 'plugins' ) {
			return $localize_script + array( 'nonce' => wp_create_nonce( Self::NONCE ) );
		}

		return $localize_script;
	}

	/**
	 * Enqueue scripts used in this module on the plugin screen and add
	 * script data.
	 *
	 * @action admin_enqueue_scripts
	 */
	public function enqueue_scripts() {
		global $current_screen;

		if( $current_screen->base !== 'plugins' ) {
			return;
		}
		wp_enqueue_script( WEBDOGS_SUPPORT_ID . '-plugin-notes', plugin_dir_url( dirname(__FILE__) ) . 'admin/js/plugin-notes.js', array(), Webdogs_Options::VERSION, true );	
	}


	/**
	 * Return a link that can be clicked to edit the current notes
	 */
	function edit_link() {
		if( current_user_can( Self::CAPABILITY ) ) {
			return '<a href="javascript:void(0);" class="plugin-notes-edit">' . __( 'Edit notes' ) . '</a>';
		}

		return '';
	}

	/**
	 * Save the notes for the plugin and return a marked-up version of those notes for
	 * display.
	 *
	 * @action wp_ajax_oomph-plugin-notes-save
	 */
	public function save_plugin_notes() {
		if( !current_user_can( Self::CAPABILITY ) ) {
			wp_send_json_error();
		}

		$all_notes = get_option( Self::OPTION );

		if( !is_array( $all_notes ) ) {
			$all_notes = array();
		}

		$input = filter_input_array( INPUT_POST, array(
			'plugin' => FILTER_REQUIRE_SCALAR,
			'notes' => FILTER_REQUIRE_SCALAR,
			'nonce' => FILTER_REQUIRE_SCALAR
		) );

		extract( $input );

		if( !wp_verify_nonce( $nonce, Self::NONCE ) ) {
			wp_send_json_error();
		}

		$all_notes[$plugin] = $notes;

		update_option( Self::OPTION, $all_notes );

		wp_send_json_success( array( 'markup' => wp_kses_post( nl2br( trim($notes) ) . ( empty(trim($notes)) ? "" : "&nbsp;| " ) ) . $this->edit_link() ) );
	}


	/**
	 * Insert notes field to plugin description by prepending the container to the first plugin
	 * meta item.
	 *
	 * @filter plugin_row_meta
	 */
	public function insert_plugin_notes( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		$id = $plugin_file;
		// $id = sanitize_title( $plugin_data['Name'] );

		$all_notes = get_option( Self::OPTION, array() );

		if( !is_array( $all_notes ) ) {
			$all_notes = array();
		}

		$notes = isset( $all_notes[$id] ) ? $all_notes[$id] : '';

		$classes = "plugin-notes-container editable wrap";

		$last_index = sizeof($plugin_meta)-1;

		$plugin_meta[$last_index] .= '<div data-plugin-notes="' . esc_attr( $notes ) . '" class="' . esc_attr( $classes ) . '">' . wp_kses_post( nl2br( trim($notes) ) . (empty(trim($notes)) ? "" : "&nbsp;| ")) . $this->edit_link() . '</div>';

		return $plugin_meta;
	}

	/**
	 * Add notes to plugin array data
	 *
	 * @filter wds_site_get_plugins
	 */
	public function add_plugin_notes( $plugins = array() ) {

		$all_notes = get_option( Self::OPTION, array() );

		if( !is_array( $all_notes ) ) {
			$all_notes = array();
		}

		foreach ($plugins as $plugin_file => $plugin )
			$plugins[$plugin_file]['Notes'] = ( empty( $all_notes[$plugin_file] ) ) ? '' : wp_kses_post( trim($all_notes[$plugin_file]) );
	
		return $plugins;
	}
}
