<?php

class RP4WP_Manager_Settings_Messages {

	const TRANSIENT_KEY = 'rp4wp_settings_messages';

	/**
	 * Add message
	 *
	 * @param $message
	 * @param string $type
	 */
	public function add_message( $message, $type = 'updated' ) {
		$messages   = $this->get_messages();
		$messages[] = array( 'body' => $message, 'type' => $type );
		set_transient( self::TRANSIENT_KEY, $messages );
	}

	/**
	 * Get all messages
	 *
	 * @return array
	 */
	public function get_messages() {
		$messages = get_transient( self::TRANSIENT_KEY );
		if ( false === $messages ) {
			$messages = array();
		}

		return $messages;
	}

	/**
	 * Print messages
	 */
	public function print_messages() {

		// Get messages
		$messages = $this->get_messages();

		// Display messages
		if ( count( $messages ) > 0 ) {
			foreach ( $messages as $message ) {
				// @todo echo messages
				echo '<div class="' . $message['type'] . '"><p><strong>' . $message['body'] . '</strong></p></div>' . PHP_EOL;
			}
		}

	}

	/**
	 * Clear messages
	 */
	public function clear_messages() {
		delete_transient( self::TRANSIENT_KEY );
	}

}