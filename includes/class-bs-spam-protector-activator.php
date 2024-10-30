<?php

/**
 * Fired during plugin activation
 *
 * @link       https://neuropassenger.ru
 * @since      1.0.0
 *
 * @package    Bs_Spam_Protector
 * @subpackage Bs_Spam_Protector/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Bs_Spam_Protector
 * @subpackage Bs_Spam_Protector/includes
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Spam_Protector_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        // Secret key generation
        $time = time();
        $secret_key = hash_hmac( 'sha256', $time, date( 'F', $time ) );
        update_option( 'bs_spam_protector_secret_key', $secret_key );
        update_option( 'bs_spam_protector_expiration_interval', 12 );
	}

}
