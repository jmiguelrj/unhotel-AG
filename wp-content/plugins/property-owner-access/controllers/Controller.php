<?php
class Controller {
    protected $blade;

    function __construct() {
        global $post, $blade;
        // Homey theme hack
        $post = get_post(get_option('page_on_front'));
        $this->blade = $blade;
        // Check if user is authorized
        checkAuthorized();
    }

    function redirectTo404() {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        get_template_part(404); 
        exit();
    }
}