<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://neuropassenger.ru
 * @since      1.0.0
 *
 * @package    Bs_Spam_Protector
 * @subpackage Bs_Spam_Protector/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Bs_Spam_Protector
 * @subpackage Bs_Spam_Protector/public
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Spam_Protector_Public {

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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bs-spam-protector-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bs-spam-protector-public.js', array( 'jquery' ), false );
        $expiration_interval = get_option( 'bs_spam_protector_expiration_interval' );
        wp_localize_script( $this->plugin_name, 'bs_vars', array(
            'nonce'       =>  wp_create_nonce( 'cf7_bs_spam_protector' ),
            'expiration'  =>  time() + 60 * 60 * $expiration_interval,
            'ajaxUrl'     =>  admin_url( 'admin-ajax.php' ),
        ) );

    }

    public function ajax_get_validation_key() {
        $nonce = sanitize_key( $_POST['nonce'] );
        $form_id = sanitize_key( $_POST['form_id'] );
        $expiration = intval( $_POST['expiration'] );
        $secret_key = get_option( 'bs_spam_protector_secret_key' );
        $log_flag = get_option( 'bs_spam_protector_log_checkbox', false );

        if ( $log_flag ) {
            // Before sanitizing
            Bs_Spam_Protector_Functions::logit(array(
                'nonce'         =>  $_POST['nonce'],
                'form_id'       =>  $_POST['form_id'],
                'expiration'    =>  $_POST['expiration'],
                'secret_key'    =>  $secret_key,
            ), '[INFO]: Validation. STEP 1. Input data before sanitizing.');
        }

        // Nonce check
        if ( ! wp_verify_nonce( $nonce, 'cf7_bs_spam_protector' ) ) {
            $response = array(
                'message' => 'Security Error',
                'status'  => 'error',
            );

            // Expiration check
        } elseif ( time() > $expiration ) {
            $response = array(
                'message' => 'Expiration Error',
                'status'  => 'error',
            );

            // Create validation key
        } else {
            $validation_key = hash_hmac( 'md5', $nonce . $expiration . $form_id, $secret_key );
            $response = array(
                'validationKey'   => $validation_key,
                'status'    => 'ok',
            );
        }

        if ( $log_flag ) {
            // After sanitizing
            Bs_Spam_Protector_Functions::logit(array(
                'nonce'         =>  $nonce,
                'form_id'       =>  $form_id,
                'expiration'    =>  $expiration,
                'secret_key'    =>  $secret_key,
                'response'      =>  $response
            ), '[INFO]: Validation. STEP 1. Input data after sanitizing with response.');
        }

        wp_send_json( $response );
    }

    public function ajax_get_validation_meta() {
        $expiration_interval = get_option( 'bs_spam_protector_expiration_interval' );
        $expiration = time() + 60 * 60 * $expiration_interval;
        $nonce = wp_create_nonce( 'cf7_bs_spam_protector' );

        wp_send_json( array(
            'expiration'    =>  $expiration,
            'nonce'         =>  $nonce,
            'status'        =>  'ok'
        ) );
    }

    public function is_spam_submission( $spam ) {
        if ( $spam ) {
            return $spam;
        }

        $secret_key = get_option( 'bs_spam_protector_secret_key' );
        $submission = WPCF7_Submission::get_instance();
        $container_post_id = $submission->get_meta( 'container_post_id' );
        $log_flag = get_option( 'bs_spam_protector_log_checkbox', false );

        // Are the initialization fields filled?
        if ( empty( $_POST['bs_hf_nonce'] ) || empty( $_POST['bs_hf_expiration'] ) || empty( $_POST['bs_hf_validation_key'] )
            || empty( $_POST['bs_hf_form_id'] )  ) {
            $submission->add_spam_log( array(
                'agent' => 'bs_spam_protector',
                'reason' => "Validation fields are empty",
            ) );

            if ( $log_flag ) {
                Bs_Spam_Protector_Functions::logit(array(
                    'nonce'             =>  $_POST['bs_hf_nonce'],
                    'form_id'           =>  $_POST['bs_hf_form_id'],
                    'expiration'        =>  $_POST['bs_hf_expiration'],
                    'secret_key'        =>  $secret_key,
                    'validation_key'    =>  $_POST['bs_hf_validation_key'],
                    'container_post_id' =>  $container_post_id
                ), '[ERROR]: Validation. STEP 2. Input data before sanitizing. One of the required fields is empty.');
            }

            return $spam = true;
        }

        $nonce = sanitize_key( $_POST['bs_hf_nonce'] );
        $form_id = sanitize_key( $_POST['bs_hf_form_id'] );
        $expiration = intval( $_POST['bs_hf_expiration'] );
        $validation_key = sanitize_key( $_POST['bs_hf_validation_key'] );
        $actual_validation_key = hash_hmac( 'md5', $nonce . $expiration . $form_id, $secret_key );
        $filling_form_time = $this->get_filling_form_time();
        $expected_filling_form_time = $this->get_min_expected_filling_form_time();

        // Expiration time
        if ( time() > $expiration ) {
            $submission->add_spam_log( array(
                'agent' => 'bs_spam_protector',
                'reason' => "Validation key is expired",
            ) );

            if ( $log_flag ) {
                Bs_Spam_Protector_Functions::logit(array(
                    'nonce'                 =>  $nonce,
                    'form_id'               =>  $form_id,
                    'expiration'            =>  $expiration,
                    'secret_key'            =>  $secret_key,
                    'validation_key'        =>  $validation_key,
                    'actual_validation_key' =>  $actual_validation_key,
                    'container_post_id'     =>  $container_post_id
                ), '[ERROR]: Validation. STEP 2. Input data after sanitizing. Validation key is expired.');
            }

            return $spam = true;

            // Key validation
        } elseif ( $validation_key !== $actual_validation_key ) {
            $submission->add_spam_log( array(
                'agent' => 'bs_spam_protector',
                'reason' => "Invalid validation key",
            ) );

            if ( $log_flag ) {
                Bs_Spam_Protector_Functions::logit(array(
                    'nonce'                 =>  $nonce,
                    'form_id'               =>  $form_id,
                    'expiration'            =>  $expiration,
                    'secret_key'            =>  $secret_key,
                    'validation_key'        =>  $validation_key,
                    'actual_validation_key' =>  $actual_validation_key,
                    'container_post_id'     =>  $container_post_id
                ), '[ERROR]: Validation. STEP 2. Input data after sanitizing. Invalid validation key.');
            }

            return $spam = true;

            // Form filled out too quickly
        } elseif ( $filling_form_time < $expected_filling_form_time ) {
            $submission->add_spam_log(array(
                'agent' => 'bs_spam_protector',
                'reason' => "Form filled out too quickly (In " . $filling_form_time . " second(s), " . $expected_filling_form_time . " second(s) expected).",
            ));

            if ($log_flag) {
                Bs_Spam_Protector_Functions::logit(array(
                    'nonce'                 =>  $nonce,
                    'form_id'               =>  $form_id,
                    'expiration'            =>  $expiration,
                    'secret_key'            =>  $secret_key,
                    'validation_key'        =>  $validation_key,
                    'actual_validation_key' =>  $actual_validation_key,
                    'container_post_id'     =>  $container_post_id,
                    'filling_time'          =>  $filling_form_time,
                    'expected_filling_time' =>  $expected_filling_form_time
                ), '[ERROR]: Validation. STEP 2. Input data after sanitizing. Form filled out too quickly.');
            }

            return $spam = true;

            // Success submission
        } else {
            if ( $log_flag ) {
                Bs_Spam_Protector_Functions::logit(array(
                    'nonce'                         =>  $nonce,
                    'form_id'                       =>  $form_id,
                    'expiration'                    =>  $expiration,
                    'secret_key'                    =>  $secret_key,
                    'validation_key'                =>  $validation_key,
                    'actual_validation_key'         =>  $actual_validation_key,
                    'container_post_id'             =>  $container_post_id,
                    'filling_time'                  =>  $filling_form_time,
                    'expected_filling_time'         =>  $expected_filling_form_time
                ), '[INFO]: Validation. STEP 2. Submission created!');
            }

            return $spam = false;
        }
    }

    function get_filling_form_time() {
        $expiration = intval( $_POST['bs_hf_expiration'] );
        $expiration_interval = get_option( 'bs_spam_protector_expiration_interval' );
        $start_filling_form_timestamp = $expiration - 60 * 60 * $expiration_interval;
        $finish_filling_form_timestamp = time() - 2; // 2 - time for network delays

        return $finish_filling_form_timestamp - $start_filling_form_timestamp;
    }

    function get_min_expected_filling_form_time() {
        $log_flag = get_option( 'bs_spam_protector_log_checkbox', false );
        $form_filling_standards = array(
            'email'                 =>  1,
            'text'                  =>  1,
            'tel'                   =>  1,
            'radio'                 =>  1,
            'textarea'              =>  3,
            'file'                  =>  3,
            'checkbox'              =>  1,
            'url'                   =>  1,
            'password'              =>  1,
            'number'                =>  1,
            'date'                  =>  2,
            'select'                =>  1,
            'acceptance'            =>  1,
            'quiz'                  =>  1
        );
        $expected_filling_form_time = 0;

        $posted_data = WPCF7_Submission::get_instance()->get_posted_data();
        // Let's remove tech fields
        unset( $posted_data['bs_hf_nonce'] );
        unset( $posted_data['bs_hf_expiration'] );
        unset( $posted_data['bs_hf_validation_key'] );
        unset( $posted_data['bs_hf_form_id'] );

        foreach ( $posted_data as $field_name => $field_value ) {
            // Looking for the corresponding tag in the form code
            $field_type = $this->get_form_field_type_by_name( $field_name );
            if ( ! $field_type ) {
                continue;
            }

            // Skip empty fields
            if ( $field_type != 'file' && empty( $field_value ) ) {
                continue;
            }

            switch ( $field_type ) {
                // Separate time computation for files, because a user can pass an empty file field
                case 'file':
                    $expected_filling_field_time = $this->get_expected_filling_time_for_file_field_type( $field_name );
                    break;
                case 'textarea':
                    $expected_filling_field_time = $this->get_expected_filling_time_for_textarea_field_type( $field_name );
                    break;
                default:
                    $expected_filling_field_time = $form_filling_standards[$field_type];
                    break;
            }

            $expected_filling_form_time += $expected_filling_field_time;

            // Logging
            if ( $log_flag )
                Bs_Spam_Protector_Functions::logit( array(
                    'expected_filling_field_time'   =>  $expected_filling_field_time,
                    'expected_filling_form_time'    =>  $expected_filling_form_time,
                    'field_name'                    =>  $field_name,
                ), '[INFO]: Expected filling time for the ' . $field_type . ' field type' );
        }

        // Let's use the severity setting
        $severity = (int) get_option( 'bs_spam_protector_time_check_severity', 50 );
        $severity_divider = round( 100 / $severity );

        return round( $expected_filling_form_time / $severity_divider );
    }

    function get_form_field_type_by_name( $field_name ) {
        $form_tags = WPCF7_Submission::get_instance()->get_contact_form()->scan_form_tags();
        foreach ( $form_tags as $form_tag ) {
            if ( $form_tag->raw_name == $field_name ) {
                return $form_tag->basetype;
            }
        }
        return false;
    }

    function get_expected_filling_time_for_file_field_type( $field_name ) {
        if ( ! isset( $_FILES[$field_name] ) ) {
            return 0;
        }
        $file = $_FILES[$field_name];

        if ( ! empty( $file['tmp_name'] ) ) {
            return 3;
        } else {
            return 0;
        }
    }

    function get_expected_filling_time_for_textarea_field_type( $field_name ) {
        $textarea_content = $_POST[$field_name];
        $content_size = mb_strlen( $textarea_content );
        $expected_time = round( $content_size / 10 );

        return $expected_time;
    }

    public function prepare_data_for_flamingo_inbound( $args ) {
        // Let's save SPAM Protector fields to the meta section
        $args['meta']['bs_hf_nonce'] = $args['fields']['bs_hf_nonce'];
        $args['meta']['bs_hf_expiration'] = $args['fields']['bs_hf_expiration'];
        $args['meta']['bs_hf_validation_key'] = $args['fields']['bs_hf_validation_key'];
        $args['meta']['bs_hf_form_id'] = $args['fields']['bs_hf_form_id'];

        $filling_form_time = $this->get_filling_form_time();
        $expected_filling_form_time = $this->get_min_expected_filling_form_time();
        $args['meta']['filling_time'] = $filling_form_time;
        $args['meta']['expected_filling_time'] = $expected_filling_form_time;

        unset( $args['fields']['bs_hf_nonce'] );
        unset( $args['fields']['bs_hf_expiration'] );
        unset( $args['fields']['bs_hf_validation_key'] );
        unset( $args['fields']['bs_hf_form_id'] );

        return $args;
    }

}