<?php


namespace JFB\SelectAutocomplete;


abstract class BaseAjaxHandler {

	public $search;
	public $form_id;
	public $field_name;

	private $need_to_filter = true;

	abstract public function type(): string;

	abstract public function get_field_options(): array;

	public function __construct() {
		$action = "jet_forms_select_autocomplete__{$this->type()}";

		add_action( "wp_ajax_{$action}", array( $this, 'on_request' ) );
		add_action( "wp_ajax_nopriv_{$action}", array( $this, 'on_request' ) );
	}

	public function on_request() {
		$this->form_id    = absint( $_POST['formId'] );
		$this->field_name = esc_attr( $_POST['fieldName'] );
		$this->search     = isset( $_POST['term'] ) ? sanitize_text_field( trim( $_POST['term'] ) ) : '';

		if ( ! empty( $_POST['postId'] ) ) {
			$this->handle_post_id();;
		}

		$options = $this->get_field_options();

		if ( ! $this->search ) {
			array_walk( $options, array( $this, 'prepare_option' ) );

			wp_send_json_success( array_values( $options ) );
		}

		if ( $this->is_need_to_filter() ) {
			$options = array_filter( $options, array( $this, 'filter_options' ) );
		}

		array_walk( $options, array( $this, 'prepare_option' ) );

		wp_send_json_success( array_values( $options ) );
	}


	public function filter_options( $option ) {
		$haystack = apply_filters( 'jet-forms/select-autocomplete', $option['label'] . $option['value'], $option );

		$callback = apply_filters(
			'jet-forms/select-autocomplete/filter-callback',
			false,
			$this
		);

		if ( is_callable( $callback ) ) {
			return call_user_func( $callback, $haystack, $this->search, $option, $this );
		}

		return ( false !== mb_stripos( $haystack, wp_unslash( $this->search ) ) );
	}

	protected function prepare_option( array &$option ) {
		$option['id']   = $option['value'];
		$option['text'] = $option['label'];

		if ( ! empty( $option['object_id'] ) ) {
			unset( $option['object_id'] );
		}

		unset(
			$option['value'],
			$option['label'],
		);
	}

	protected function handle_post_id() {
		global $post;
		$post_id = absint( $_POST['postId'] ?? '' );

		$post = get_post( $post_id );
	}

	/**
	 * @return bool
	 */
	public function is_need_to_filter(): bool {
		return $this->need_to_filter;
	}

	/**
	 * @param bool $need_to_filter
	 */
	public function set_need_to_filter( bool $need_to_filter ) {
		$this->need_to_filter = $need_to_filter;
	}

}