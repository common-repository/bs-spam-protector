<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://neuropassenger.ru
 * @since      1.0.0
 *
 * @package    Bs_Spam_Protector
 * @subpackage Bs_Spam_Protector/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Bs_Spam_Protector
 * @subpackage Bs_Spam_Protector/includes
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Spam_Protector_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
        // Remove secret key
        delete_option( 'bs_spam_protector_secret_key' );
	}

}
