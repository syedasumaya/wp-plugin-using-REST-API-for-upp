<?php

/*
  Plugin Name: UPP API
  Plugin URI: http://therssoftware.com
  Description: This is a plugin to connect with https://app.upp.io
  Version: 1.0
  Author: Syeda Sumaya Yesmin
  Author URI: https://github.com/syedasumaya
  Text Domain: rs_upp
  License: http://therssoftware.com
 */
if (!defined('ABSPATH')) {
    exit();
}
if (!class_exists('Rs_upp')) {

    class Rs_upp {

        public function __construct() {

            register_activation_hook(__FILE__, array($this, 'upp_activate'));
            register_deactivation_hook(__FILE__, array($this, 'upp_deactivate'));
            register_uninstall_hook(__FILE__, array($this, 'upp_uninstall'));
            require_once(plugin_dir_path(__FILE__) . 'inc/rsupp_admin.php');
            add_action('init', array($this, 'rsupp_forms_reg_post_type'),10);
            add_action('init', array($this, 'rsupp_forms_post'),11);
            add_action('wp_enqueue_scripts', array($this, 'rsupp_front_enqueue_styles'));
            add_action('admin_enqueue_scripts', array($this, 'rsupp_admin_enqueue_styles'));
            add_action('admin_menu', array($this, 'rsupp_admin_menu'));
            add_action('admin_action_allforms', array('rsupp_admin', 'all_forms'));
            add_action('admin_action_rs_connection_with_upp', array('rsupp_admin', 'rs_connection_with_upp'));
            add_action('admin_action_rsdisconnect_with_upp', array('rsupp_admin', 'rsdisconnect_with_upp'));
            add_action('admin_action_rsadd_uppform', array('rsupp_admin', 'rsadd_uppform'));
            add_action('admin_action_delete_rsuppform', array('rsupp_admin', 'delete_rsuppform'));
            add_action('admin_action_multidelete_rsuppform', array('rsupp_admin', 'multidelete_rsuppform'));
            add_action('admin_action_edit_rsuppform', array('rsupp_admin', 'edit_rsuppform'));
            add_action('admin_action_rsupdate_uppform', array('rsupp_admin', 'rsupdate_uppform'));

            $formTitle = $this->generateShortCode();

            foreach ($formTitle as $value) {
                add_shortcode('uppForm_' . $value, array($this, 'rs_get_site_content'));
            }
        }

        public function upp_activate() {
            // trigger our function that registers the custom post type
            $this->rsupp_forms_reg_post_type();

            // clear the permalinks after the post type has been registered
            flush_rewrite_rules();
        }

        public function upp_deactivate() {
            // our post type will be automatically removed, so no need to unregister it
            // clear the permalinks to remove our post type's rules
            flush_rewrite_rules();
        }
        
        public function upp_uninstall() {
            $option_name = 'rs_upp_api_key';
            delete_option($option_name);
        }

        //Add all frontend script
        public function rsupp_front_enqueue_styles() {
            wp_enqueue_style('rsuppstyle', plugins_url('assets/css/rsuppfront.css', __FILE__));
            wp_enqueue_script('rsuppscript', plugins_url('assets/js/jquery-validation/jquery.validate.min.js', __FILE__));
            wp_enqueue_script('rsuppscript1', plugins_url('assets/js/custom_front.js', __FILE__));
        }

        // Add all backend script
        public function rsupp_admin_enqueue_styles() {
            wp_enqueue_style('newstyle1', plugins_url('assets/css/rsupp.css', __FILE__));
            wp_enqueue_style('newstyle2', plugins_url('assets/font-awesome/css/font-awesome.min.css', __FILE__));
            wp_enqueue_style('newstyle3', 'http://necolas.github.com/normalize.css/2.0.1/normalize.css');
            wp_enqueue_script('jquery-ui-accordion');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('newscript1', plugins_url('assets/js/jquery-validation/jquery.validate.min.js', __FILE__));
            wp_enqueue_script('newscript2', plugins_url('assets/js/custom.js', __FILE__));
        }

        // Menu Page Registrtion
        public function rsupp_admin_menu() {
            add_menu_page('UPP API', 'UPP API', 'manage_options', 'connect_with_upp', array('rsupp_admin', 'connect_with_upp'), 'dashicons-images-alt2');
            add_submenu_page('', 'All Forms', 'All Forms', 'manage_options', 'all_forms', array('rsupp_admin', 'all_forms'));
            add_submenu_page('', 'Edit Forms', 'Edit Forms', 'manage_options', 'edit_rsuppform', array('rsupp_admin', 'edit_rsuppform'));
        }

        //Register Custom Post type as Forms
        public function rsupp_forms_reg_post_type() {

            $args = array(
                'label' => __('UPP Forms', 'textdomain'),
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => false,
                'show_in_menu' => false,
                'query_var' => true,
                'rewrite' => array('slug' => 'rsuppforms', 'with_front' => FALSE),
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => null,
                'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
            );
            register_post_type('upp_forms', $args);

            
        }
        
        public function rsupp_forms_post(){
            
            if ('POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['action']) && $_POST['action'] == "upp_post") {

                $this->upp_post();
            }
        }

        public function generateShortCode() {

            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'upp_forms',
                'order' => 'DESC',
                'orderby' => 'ID'
            );

            $all_forms = get_posts($args);

            $formTitle = array();
            if (isset($all_forms) && !empty($all_forms)) {
                foreach ($all_forms as $key => $form) {
                    $title = preg_replace('/[\s-]+/', '', strtolower($form->post_title));
                    $formTitle[] = $title;
                }
            }
            return $formTitle;
        }

        public function rs_get_site_content($atts, $content = null, $tag) {

            global $wp;
            $current_url = home_url(add_query_arg(array(), $wp->request));

            $html = '';
            $args = array(
                'meta_key' => 'uppShortcode',
                'meta_value' => $tag,
                'post_type' => 'upp_forms',
                'post_status' => 'any'
            );
            $posts = get_posts($args);
            $error = '';

            if (isset($_GET['message']) && $_GET['message'] == 'failed') {
                $error = '<span class="rs-upp-error-msg">Email and Company name fields are required!</span><br>';
            }


            foreach ($posts as $key => $value) {

                $form = json_decode($value->post_content, true);


                $html.='<div class="rs-upp-form-wrap  ' . $form["form_layout"] . '" id="' . $tag . '">';
                $html.='<h2>' . $form["title"] . '</h2>';
                $html.='<form method="post" action="' . $current_url . '/" class="uppForm">';

                $html .= $error;
                if ($form["submit_button_text"] != '') {
                    $submit = $form["submit_button_text"];
                } else {
                    $submit = 'Submit';
                }
                foreach ($form["fields"]["defaultFieldName"] as $key => $value) {
                    if ($form["form_layout"] == 'horizontal') {
                        $placeholder = $form["fields"]["defaultFieldName"][$key];
                    } else {
                        $placeholder = '';
                    }
                    if ($form["fields"]["defaultFieldStatus"][$key] != 'disable') {
                        $type = $form["fields"]["defaultFieldType"][$key];
                        if ($form["fields"]["defaultHiddenFieldValue"][$key] != '') {
                            $type = 'hidden';
                        }

                        if ($form["fields"]["defaultFieldType"][$key] == 'text') {
                            if ($form["fields"]["defaultLabel"][$key] != '') {
                                $label = '(' . $form["fields"]["defaultLabel"][$key] . ')';
                            } else {
                                $label = '';
                            }
                            $html.='<div class = "element-input">';
                            if ($type != 'hidden') {
                                $html.='<label class="title">' . $form["fields"]["defaultFieldName"][$key] . ' ' . $label . '</label>';
                            }
                            if ($form["fields"]["defaultIdentifier"][$key] == 'phones' || $form["fields"]["defaultIdentifier"][$key] == 'emails') {
                                $html.='<input class="large" name="' . $form["fields"]["defaultIdentifier"][$key] . '[value]" type = "' . $type . '" value="' . $form["fields"]["defaultHiddenFieldValue"][$key] . '" placeholder="' . $placeholder . '">';
                                $html.='<input class= "large" name="' . $form["fields"]["defaultIdentifier"][$key] . '[label]" type = "hidden" value="' . $form["fields"]["defaultLabel"][$key] . '" placeholder="' . $placeholder . '">';
                            } else {
                                $html.='<input class= "large" name="' . $form["fields"]["defaultIdentifier"][$key] . '" type = "' . $type . '" value="' . $form["fields"]["defaultHiddenFieldValue"][$key] . '" placeholder="' . $placeholder . '">';
                            }
                            $html.='</div>';
                        } else {
                            //Adresss
                            if ($type != 'hidden') {
                                $html.='<div class = "element-input">';
                                $html.='<label class="address">' . $form["fields"]["defaultFieldName"][$key] . ':</label><br>';
                                $html.='</div>';
                                $html.='<div class = "element-input">';
                                $html.='<label class="title">Street</label>';
                                $html.='<input name = "' . $form["fields"]["defaultIdentifier"][$key] . '[street]" id = "tag-slug" type = "text" value = ""  placeholder = "">';
                                $html.='</div>';
                                $html.='<div class = "element-input">';
                                $html.='<label class="title">Street Number</label>';
                                $html.='<input name = "' . $form["fields"]["defaultIdentifier"][$key] . '[streetNumber]" id = "tag-slug" type = "text" value = ""  placeholder = "">';
                                $html.='</div>';
                                $html.='<div class = "element-input">';
                                $html.='<label class="title">City</label>';
                                $html.='<input name = "' . $form["fields"]["defaultIdentifier"][$key] . '[city]" id = "tag-slug" type = "text" value = "" placeholder = "">';
                                $html.='</div>';
                                $html.='<div class = "element-input">';
                                $html.='<label class="title">Postal Code</label>';
                                $html.='<input name = "' . $form["fields"]["defaultIdentifier"][$key] . '[postcode]" id = "tag-slug" type = "text" value = ""  placeholder = "">';
                                $html.='</div>';
                                $country = json_decode(file_get_contents(WP_CONTENT_DIR . '/plugins/rs-upp/assets/country.json'));
                                $html.='<div class = "element-input">';
                                $html.='<label class="title">Country</label>';
                                $html.='<select name="' . $form["fields"]["defaultIdentifier"][$key] . '[country]">
                                <option value="">Select Country</option>';
                                foreach ($country as $value) {
                                    $html.='<option value="' . $value->Code . '">' . $value->Name . '</option>';
                                }
                                $html.='</select>';
                                $html.='</div>';
                            } else {
                                $html.='<div class = "element-input">';
                                $html.='<div><input class= "large" name="' . $form["fields"]["defaultIdentifier"][$key] . '[street]" type = "' . $type . '" value="' . $form["fields"]["defaultHiddenFieldValue"][$key] . '"></div>';
                                $html.='</div>';
                            }
                        }
                    }
                }
                if (isset($form["customfields"]["customfieldlabel"]) && !empty($form["customfields"]["customfieldlabel"])) {
                    foreach ($form["customfields"]["customfieldlabel"] as $key => $value) {
                        $type = $form["customfields"]["customfieldType"][$key];

                        if ($form["fields"]["customHiddenfieldValue"][$key] != '') {
                            $type = 'hidden';
                        }

                        if ($form["customfields"]["customfieldType"][$key] == 'text' || $form["customfields"]["customfieldType"][$key] == 'number') {
                            $html.='<div class = "element-input">';
                            if ($type != 'hidden') {
                                $html.='<label class="title">' . $form["customfields"]["customfieldlabel"][$key] . '</label>';
                            }
                            $html.='<input class= "large" name="customFields[' . $form["customfields"]["customfieldIdentifier"][$key] . ']" type = "' . $type . '" value="' . $form["customfields"]["customHiddenfieldValue"][$key] . '">';
                            $html.='</div>';
                        }

                        if ($form["customfields"]["customfieldType"][$key] == 'textarea') {
                            $html.='<div class = "element-input">';
                            if ($type != 'hidden') {
                                $html.='<label class="title">' . $form["customfields"]["customfieldlabel"][$key] . '</label>';
                            }
                            $html.='<div><textarea name="customFields[' . $form["customfields"]["customfieldIdentifier"][$key] . ']" class = "large ' . $type . '">' . $form["customfields"]["customHiddenfieldValue"][$key] . '</textarea></div>';
                            $html.='</div>';
                        }
                        if ($form["customfields"]["customfieldType"][$key] == 'radio') {
                            $html.='<div class = "element-input">';
                            $values = explode('|', $form["customfields"]["customfieldValue"][$key]);
                            $html.='<fieldset>';
                            $html.='<legend id="title5" class="desc">';
                            $html.= $form["customfields"]["customfieldlabel"][$key];
                            $html.='</legend>';
                            foreach ($values as $val) {

                                $html.='<div>';
                                $html.='<input class= "large" name="customFields[' . $form["customfields"]["customfieldIdentifier"][$key] . ']" type = "' . $type . '" value="' . $val . '">';
                                $html.='<label class="choice" for="Field5_0">' . ucwords($val) . '</label>';
                                $html.='</div>';
                            }
                            $html.=' </fieldset>';
                            $html.='</div>';
                        }
                        if ($form["customfields"]["customfieldType"][$key] == 'checkbox') {
                            $html.='<div class = "element-input">';
                            $values = explode('|', $form["customfields"]["customfieldValue"][$key]);
                            $html.='<fieldset>';
                            $html.='<legend id="title5" class="desc">';
                            $html.= $form["customfields"]["customfieldlabel"][$key];
                            $html.='</legend>';
                            foreach ($values as $val) {
                                $html.='<div>';
                                $html.='<input class= "large" name="customFields[' . $form["customfields"]["customfieldIdentifier"][$key] . ']" type = "' . $type . '" value="' . $val . '"> ';
                                $html.='<label class="choice" for="Field5_0">' . ucwords($val) . '</label>';
                                $html.='</div>';
                            }
                            $html.=' </fieldset>';
                            $html.='</div>';
                        }

                        if ($form["customfields"]["customfieldType"][$key] == 'select') {
                            $html.='<div class = "element-input">';
                            $values = explode('|', $form["customfields"]["customfieldValue"][$key]);
                            $html.='<div><label class="title">' . $form["customfields"]["customfieldlabel"][$key] . '</label></div>';
                            $html.='<select name="customFields[' . $form["customfields"]["customfieldIdentifier"][$key] . ']">';
                            foreach ($values as $val) {
                                $html.='<option value="' . $val . '">' . ucwords($val) . '</option>';
                            }
                            $html.='</select>';
                            $html.='</div>';
                        }
                    }
                }
                $html.='<div class = "element-input">';
                if (!empty($form["tags"])) {
                    if (strpos($form["tags"][0], ',') !== false) {
                        $tags = explode(',', $form["tags"][0]);
                    } else {
                        $tags[] = $form["tags"][0];
                    }
                    foreach ($tags as $tag) {
                        $html.='<input class= "large" name="tags[]" type = "hidden" value="' . $tag . '">';
                    }
                }
                $html.='<input class= "large" name="redirect_url" type = "hidden" value="' . $form["redirect_url"] . '">';
                $html.='<input class= "large" name="form_page_url" type = "hidden" value="' . $current_url . '">';
                $html.='<input type="hidden" name="action" value="upp_post" />';
                $html.='<input id="saveUppForm" name="saveForm" type="button" value="' . $submit . '">';
                $html.='</div>';


                $html.='</form>';
                $html.='</div>';
            }
            return $html;
        }

        public function upp_post() {

            global $wp;
            $current_url = $_POST['form_page_url'];
            if ($_POST['redirect_url'] != '') {
                $redirect_url = $_POST['redirect_url'];
            } else {
                $redirect_url = $current_url;
            }

            $api_key = get_option('rs_upp_api_key');
            if ((isset($_POST['emails']['value']) && $_POST['emails']['value'] == '') || (isset($_POST['companyName']) && $_POST['companyName'] == '')) {
                $msg = 'failed';
                $path = add_query_arg('message', $msg, $_POST['form_page_url']);
                wp_redirect($path, $status = 302);
                exit();
            }
            foreach ($_POST as $key => $value) {

                if ($key != 'redirect_url' && $key != 'action' && $key != 'saveForm') {
                    if (is_array($_POST[$key])) {
                        if ($key == 'tags') {
                            $tags = array();
                            for ($x = 0; $x < count($_POST[$key]); $x++) {
                                $tags[$x] = new stdClass();
                                $tags[$x]->text = $_POST[$key][$x];
                            }
                            $data[$key] = isset($_POST[$key]) ? $tags : '';
                        } else if ($key == 'customFields') {
                            $data[$key] = isset($_POST[$key]) ? (object) $_POST[$key] : '';
                        } else {
                            $data[$key][] = isset($_POST[$key]) ? (object) $_POST[$key] : '';
                        }
                    } else {
                        $data[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
                    }
                }
            }

            $url = 'https://app.upp.io/rest/v1/accounts';
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            // receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'api-key: ' . $api_key
            ));

            $server_output = curl_exec($ch);
            $put = json_decode($server_output)->success;
            $curl_error = curl_error($ch);

            curl_close($ch);
            
            //echo '<pre>'; print_r($server_output); exit;

            if ($put == 1) {
                wp_redirect($redirect_url);
                exit();
            }
        }

    }

}
new Rs_upp();


