<?php

/**
 * Class NL4WP_Form_Tags
 *
 * @access private
 * @ignore
 */
class NL4WP_Form_Tags {

	/**
	 * @var NL4WP_Dynamic_Content_Tags
	 */
	protected $tags;

	/**
	 * @var NL4WP_Form
	 */
	protected $form;


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tags = new NL4WP_Dynamic_Content_Tags( 'form' );
	}


	public function add_hooks() {
		add_filter( 'nl4wp_dynamic_content_tags_form', array( $this, 'register' ) );
		add_filter( 'nl4wp_form_response_html', array( $this, 'replace' ), 10, 2 );
		add_filter( 'nl4wp_form_content', array( $this, 'replace' ), 10, 2 );
		add_filter( 'nl4wp_form_redirect_url', array( $this, 'replace_in_url' ), 10, 2 );
	}

	/**
	 * @return array
	 */
	public function get() {
		return $this->tags->all();
	}

	/**
	 * @param array $tags
	 * @return array
	 */
	public function register( array $tags ) {

		/**
		 * @var NL4WP_Request
		 */
		$request = nl4wp('request');

		$tags['response'] = array(
			'description'   => __( 'Replaced with the form response (error or success messages).', 'newsletter-for-wp' ),
			'callback'      => array( $this, 'get_form_response' )
		);

		$tags['data'] = array(
			'description' => sprintf( __( "Data from the URL or a submitted form.", 'newsletter-for-wp' ) ),
			'callback'    => array( $this, 'get_data' ),
			'example'     => "data key='UTM_SOURCE' default='Default Source'"
		);

		$tags['subscriber_count'] = array(
			'description' => __( 'Replaced with the number of subscribers on the selected list(s)', 'newsletter-for-wp' ),
			'callback'    => array( $this, 'get_subscriber_count' )
		);

		$tags['email']  = array(
			'description' => __( 'The email address of the current visitor (if known).', 'newsletter-for-wp' ),
			'callback'    => array( $this, 'get_email' ),
		);

		$tags['current_url']  = array(
			'description' => __( 'The URL of the page.', 'newsletter-for-wp' ),
			'callback'    => 'nl4wp_get_current_url',
		);

		$tags['current_path'] = array(
			'description' => __( 'The path of the page.', 'newsletter-for-wp' ),
			'callback'    => array( $request, 'get_url' ),
		);

		$tags['date']         = array(
			'description' => sprintf( __( 'The current date. Example: %s.', 'newsletter-for-wp' ), '<strong>' . date( 'Y/m/d' )  . '</strong>' ),
			'replacement' => date( 'Y/m/d' )
		);

		$tags['time']         = array(
			'description' => sprintf( __( 'The current time. Example: %s.', 'newsletter-for-wp' ),  '<strong>' . date( 'H:i:s' ) . '</strong>'),
			'replacement' => date( 'H:i:s' )
		);

		$tags['language']     = array(
			'description' => sprintf( __( 'The site\'s language. Example: %s.', 'newsletter-for-wp' ),  '<strong>' . get_locale() . '</strong>' ),
			'callback'    => 'get_locale',
		);

		$tags['ip']           = array(
			'description' => sprintf( __( 'The visitor\'s IP address. Example: %s.', 'newsletter-for-wp' ), '<strong>' . nl4wp('request')->get_client_ip() . '</strong>' ),
			'callback'    => array( $request, 'get_client_ip' )
		);

		$tags['user']      = array(
			'description' => sprintf( __( "The property of the currently logged-in user.", 'newsletter-for-wp' ) ),
			'callback'    => array( $this, 'get_user_property' ),
			'example'     => "user property='user_email'"
		);

		return $tags;
	}

	/**
	 * Replaces the registered tags in the given string
	 *
	 * @hooked `nl4wp_form_message_html`
	 * @hooked `nl4wp_form_content`
	 *
	 * @param string $string
	 * @param NL4WP_Form $form
	 *
	 * @return string
	 */
	public function replace( $string, NL4WP_Form $form ) {
		$this->form = $form;
		$string = $this->tags->replace( $string );
		return $string;
	}

	/**
	 * @hooked `nl4wp_form_redirect_url`
	 *
	 * @param            $string
	 * @param NL4WP_Form $form
	 *
	 * @return string
	 */
	public function replace_in_url( $string, NL4WP_Form $form ) {
		$this->form = $form;
		$string = $this->tags->replace_in_url( $string );
		return $string;
	}

	/**
	 * Returns the number of subscribers on the selected lists (for the form context)
	 *
	 * @return int
	 */
	public function get_subscriber_count() {
		$newsletter = new NL4WP_NewsLetter();
		return $newsletter->get_subscriber_count( $this->form->get_lists() );
	}

	/**
	 * Returns the form response
	 *
	 * @return string
	 */
	public function get_form_response() {
		return $this->form->get_response_html();
	}

	/**
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function get_data( $args = array() ) {

		if( empty( $args['key'] ) ) {
			return '';
		}

		$default = isset( $args['default'] ) ? $args['default'] : '';

		/**
		 * @var NL4WP_Request $request
		 */
		$request = nl4wp('request');
		return esc_html( $request->params->get( $args['key'], $default ) );
	}

	/*
	 * Get property of currently logged-in user
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_user_property( $args = array() ) {
		$property = empty( $args['property'] ) ? 'user_email' : $args['property'];
		$user = wp_get_current_user();

		if( $user instanceof WP_User ) {
			return $user->{$property};
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function get_email() {

		// first, try request
		$request = nl4wp('request');
		$email = $request->params->get( 'EMAIL', '' );
		if( $email ) {
			return $email;
		}

		// then , try logged-in user
		if( is_user_logged_in() ) {
			$user = wp_get_current_user();
			return $user->user_email;
		}

		// TODO: Read from cookie? Or add $_COOKIE support to {data} tag?

		return '';
	}

}