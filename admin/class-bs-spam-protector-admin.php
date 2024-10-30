<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://neuropassenger.ru
 * @since      1.0.0
 *
 * @package    Bs_Spam_Protector
 * @subpackage Bs_Spam_Protector/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bs_Spam_Protector
 * @subpackage Bs_Spam_Protector/admin
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Spam_Protector_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bs_Spam_Protector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bs_Spam_Protector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bs-spam-protector-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bs_Spam_Protector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bs_Spam_Protector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bs-spam-protector-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function add_plugin_settings_page() {
        add_options_page( 'SPAM Protector', 'SPAM Protector', 'manage_options', $this->plugin_name, array( $this, 'render_settings_page' ) );
    }

    function render_settings_page() {
        ?>
        <h1 class="wp-heading-inline">
            <?php echo get_admin_page_title() ?>
        </h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'bs_spam_protector_general' );
            do_settings_sections( $this->plugin_name );
            submit_button();
            ?>
        </form>
        <?
    }

    public function add_plugin_settings() {
	    register_setting(
	        'bs_spam_protector_general',
            'bs_spam_protector_secret_key'
        );

        add_settings_section(
            'bs_spam_protector_general',
            'General Settings',
            array( $this, 'show_general_settings_section' ),
            $this->plugin_name
        );

        register_setting(
            'bs_spam_protector_general',
            'bs_spam_protector_log_checkbox'
        );

        register_setting(
            'bs_spam_protector_general',
            'bs_spam_protector_expiration_interval'
        );

        register_setting(
            'bs_spam_protector_general',
            'bs_spam_protector_time_check_severity'
        );

        add_settings_field(
            'bs_spam_protector_secret_key',
            'Secret key to generate a validation key',
            array( $this, 'show_secret_key_field' ),
            $this->plugin_name,
            'bs_spam_protector_general'
        );

        add_settings_field(
            'bs_spam_protector_log_checkbox',
            'Enable detailed log',
            array( $this, 'show_log_checkbox_field' ),
            $this->plugin_name,
            'bs_spam_protector_general'
        );

        add_settings_field(
            'bs_spam_protector_expiration_interval',
            'Expiration interval',
            array( $this, 'show_expiration_interval_field' ),
            $this->plugin_name,
            'bs_spam_protector_general'
        );

        add_settings_field(
            'bs_spam_protector_time_check_severity',
            'Severity of filling time check (between 1 and 100 percents)',
            array( $this, 'show_time_check_severity_field' ),
            $this->plugin_name,
            'bs_spam_protector_general'
        );
    }

    function show_general_settings_section() {
        // Block before fields
    }

    function show_secret_key_field() {
        $secret_key = get_option( 'bs_spam_protector_secret_key' );
        echo "<input type='text' class='regular-text bs_spam-protector-secret-key' name='bs_spam_protector_secret_key' value='" . (esc_attr( $secret_key ) ?? '') . "'>";
    }

    function show_log_checkbox_field() {
        $checkbox = get_option( 'bs_spam_protector_log_checkbox' );
        echo "<input type='checkbox' class='bs_spam-protector-log-checkbox' name='bs_spam_protector_log_checkbox'" . checked( $checkbox, 'on', false ) . ">";
        echo "<p>You can find the log of the plugin operation along this path - wp-content/bs_spam_protector.log.</p>";
    }

    function show_expiration_interval_field() {
        $expiration_interval = get_option( 'bs_spam_protector_expiration_interval' );
        echo "<input type='number' class='regular-text bs_spam_protector_expiration_interval' name='bs_spam_protector_expiration_interval' placeholder='12' value='" . (esc_attr( $expiration_interval ) ?? '') . "'>";
        echo "<p>The allowed time interval <strong>in hours</strong> between opening and submitting a form.</p>";
    }

    function show_time_check_severity_field() {
        $severity = get_option( 'bs_spam_protector_time_check_severity', false );
        echo "<input min='1' max='100' step='1' type='range' class='regular-text bs_spam_protector_time_check_severity' name='bs_spam_protector_time_check_severity' placeholder='50' oninput='document.getElementById(\"bs_time_check_severity\").value = this.value;' value='" . (esc_attr( $severity ) ?? '') . "'>";
        echo "<output id='bs_time_check_severity'>" . esc_attr( ( $severity ) ?? '' ) . "</output><span>%</span>";
        echo "<p>How stringent the fill time check should be. <br>Typically, 50% severity is appropriate for most cases.</p>";
    }

    public function do_upgrade() {
        // Expiration interval
        $expiration_interval = get_option( 'bs_spam_protector_expiration_interval', false );

        if ( version_compare( $this->version, '1.6.0', '>=' ) && $expiration_interval == false ) {
            update_option( 'bs_spam_protector_expiration_interval', 12 );
        }

        // Time check severity
        $severity = get_option( 'bs_spam_protector_time_check_severity', false );

        if ( version_compare( $this->version, '1.7.0', '>=' ) && $severity == false ) {
            update_option( 'bs_spam_protector_time_check_severity', 50 );
        }
    }

}
