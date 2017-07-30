<?php
if (!defined('ABSPATH')) {
    exit();
}
if (!class_exists('Rsupp_admin')) {

    class Rsupp_admin {

        public function __construct() {
            
        }

        public function rs_connection_with_upp() {

            $url = 'https://app.upp.io/rest/v1/accounts';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'api-key: ' . $_POST['apiKey']
            ));
            $output = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            $connection = json_decode($output)->success;

            if ($connection == 1) {
                $msg = 'connected';
                $check_already_connected = get_option('rs_upp_api_key');
                if ($check_already_connected == '') {
                    add_option('rs_upp_api_key', $_POST['apiKey'], '', 'yes');
                } else {
                    update_option('rs_upp_api_key', $_POST['apiKey']);
                }
            } else {
                $msg = 'notConnected';
            }
            $path = add_query_arg('message', $msg, $_SERVER['HTTP_REFERER']);
            wp_redirect($path, $status = 302);
            exit();
        }

        public function rsdisconnect_with_upp() {
            delete_option('rs_upp_api_key');
            $msg = 'Disconnected!';
            $path = add_query_arg('message', $msg, $_SERVER['HTTP_REFERER']);
            wp_redirect($path, $status = 302);
            exit();
        }

        public function connect_with_upp() {
            $connectCls = '';
            $readonly = 'not-active';
            $text_color = 'text-grey';
            $disconnecthref = '#';
            $formsUrl = '#';
            $allforms = 'All Forms';

            $check_already_connected = get_option('rs_upp_api_key');
            if ($check_already_connected != '') {
                $text_color = 'text-green';
                $connectCls = 'connected';
                $readonly = '';
                $disconnecthref = admin_url('admin.php?action=rsdisconnect_with_upp');
                $formsUrl = admin_url('admin.php?page=all_forms');
                $allforms = 'Your Forms';
            }
            $connect = '<h2><i>You are <span class="' . $text_color . '">connected</span> with <a href="app.upp.io">app.upp.io</a>!<a href="' . $disconnecthref . '" class="' . $readonly . '"> Disconnect</a></i></h2>';
            $connect .= '<h2><i>You are now ready to create <a href="' . admin_url('admin.php?page=all_forms') . '" class="' . $readonly . '">form here</a></i></h2>';
            ?>
            <div class="wrap rswrap">
                <h2 class="nav-tab-wrapper">
                    <a href="<?php echo $formsUrl ?>" class="nav-tab <?php echo $readonly ?>"><?php echo $allforms; ?></a>
                    <a href="<?php echo admin_url('admin.php?page=connect_with_upp') ?>" class="nav-tab <?php echo $connectCls; ?> active">Connect &nbsp;&nbsp;&nbsp;<i class="fa fa-circle" aria-hidden="true"></i></a>
                </h2>

                <h2>Please give UPP API key to connect this wordpress administration</h2>
                <div id="col-container" class="wp-clearfix">

                    <div id="col">
                        <div class="col-wrap">
                            <div class="form-wrap">
                                <?php echo $connect; ?>
                                <form id="connect-upp-form" action="<?php echo admin_url('admin.php'); ?>" method="post">
                                    <div class="form-field form-required term-name-wrap">
                                        <input name="apiKey" id="apiKey" value="<?php if (isset($check_already_connected) && $check_already_connected != '') echo $check_already_connected; ?>" size="40" aria-required="true" type="text">
                                        <i><a href='https://app.upp.io/'>Don't know where to find this key?</a></i>
                                    </div>

                                    <input type="hidden" name="action" value="rs_connection_with_upp" />
                                    <input type="button" value="Connect" class="button button-primary connect-upp-btn" />
                                    <input type="submit" name="submit" id="connect-upp-form-btn" class="button button-primary" value="Connect">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?php
        }

        public function all_forms() {

            $connect = '';
            $connectCls = '';
            $allforms = 'All Forms';

            $check_already_connected = get_option('rs_upp_api_key');
            if ($check_already_connected != '') {
                $connect = '<h2><i>You are connected with <a href="app.upp.io">app.upp.io</a>! <a href="">Disconnect</a></i></h2>';
                $connect .= '<h2><i>You are now ready to create <a href="' . admin_url('admin.php?page=all_forms') . '">form here</a></i></h2>';
                $connectCls = 'connected';
                $allforms = 'Your Forms';
            }
            ?>
            <div class="wrap rswrap">
                <h2 class="nav-tab-wrapper">
                    <a href="<?php echo admin_url('admin.php?page=all_forms') ?>" class="nav-tab active"><?php echo $allforms; ?></a>
                    <a href="<?php echo admin_url('admin.php?page=connect_with_upp') ?>" class="nav-tab <?php echo $connectCls; ?>">Connect &nbsp;&nbsp;&nbsp;<i class="fa fa-circle" aria-hidden="true"></i></a>
                </h2>
                <?php
                if ($check_already_connected == '') {
                    wp_die(
                            '<h1>' . __('Warning!') . '</h1>' .
                            '<p>' . __('Sorry, you are not allowed to view this page before connecting <a href="app.upp.io">app.upp.io</a>.') . '</p>', 403
                    );
                }
                ?>

                <div id="col-container" class="wp-clearfix">
                    <!--------------Left Area Start---------------------->
                    <div id="col-left">
                        <div class="col-wrap">
                            <div class="form-wrap">
                                <h2>Add New Upp Form</h2>
                                <form id="upp-add-form" method="post" action="<?php echo admin_url('admin.php'); ?>">
                                    <div class="form-field form-required term-name-wrap">
                                        <label for="tag-name"><b>Name</b><span class="upp-required">*</span></label>
                                        <input onkeyup="generateShortcode(this.value)" name="title" id="tag-name" type="text" value="" size="40" aria-required="true" >
                                        <p>The name is how it appears on your site.</p>
                                    </div>
                                    <div class="form-field term-slug-wrap">
                                        <label for="tag-slug"><b>ShortCode</b></label>
                                        <input name="shortcode" id="tag-slug" type="text" value="uppForm_" size="40" readonly>
                                        <p>The “shortcode” is the code you include in your template to show the form. It is usually all lower case and contains only letters, numbers and hyphens</p>
                                    </div>
                                    <div class="form-field term-description-wrap uppformfields">
                                        <label for="tag-description"><b>Formfields</b></label>
                                        <div class="accordion" id="accordion">

                                            <div class="group1">  
                                                <h3><a href="#">Name</a></h3>
                                                <div class="draggable1">
                                                    <input name="fields[defaultFieldName][]" id="tag-slug" type="text" value="Name" size="40" placeholder="Type label name">
                                                    <input name="fields[defaultLabel][]" id="tag-slug" type="hidden" value="" size="40" placeholder="Type label i.e(Work,Home,Mobile...)">
                                                    <input name="fields[defaultIdentifier][]" id="tag-slug" type="hidden" value="name" size="40" placeholder="Type Upp Identifier">
                                                    <input name="fields[defaultFieldType][]" id="tag-slug" type="hidden" value="text" size="40" placeholder="">
                                                    <br>
                                                    <input name="fields[defaultHiddenFieldValue][]" id="input-value1" type="hidden" value="" size="40" placeholder="Type Value...">
                                                    <br>
                                                    <input name="fields[defaultFieldStatus][]" id="defaultFieldStatus_1" type="hidden" value="enable" size="40" placeholder="">
                                                    <input onclick="makeHidden(1)" type="checkbox" id="make_hidden1" name="fields[make_hidden][]" value="0"> Make it hidden <br>
                                                    <a onclick="makeDisable(1)" id="disable1" class="disable" status="disable">Disable</a>
                                                </div>
                                                <a onclick="makeDisable(1)" id="enable1" class="enable" status="enable" style="display:none">Enable</a>
                                            </div>
                                            
                                            <div class="group2">
                                                <h3><a href="#">Email</a></h3>
                                                <div class="draggable2">
                                                    <input name="fields[defaultFieldName][]" id="tag-slug" type="text" value="Email" size="40" placeholder="Type label name">
                                                    <input name="fields[defaultLabel][]" id="tag-slug" type="text" value="" size="40" placeholder="Type label i.e(Work,Home,Mobile...)">
                                                    <input name="fields[defaultIdentifier][]" id="tag-slug" type="hidden" value="emails" size="40" placeholder="Type Upp Identifier">
                                                    <input name="fields[defaultFieldType][]" id="tag-slug" type="hidden" value="text" size="40" placeholder="">
                                                    <br>
                                                    <input name="fields[defaultHiddenFieldValue][]" id="input-value2" type="hidden" value="" size="40" placeholder="Type Value...">
                                                    <br>
                                                    <input name="fields[defaultFieldStatus][]" id="defaultFieldStatus_2" type="hidden" value="enable" size="40" placeholder="">
                                                    <input onclick="makeHidden(2)" type="checkbox" id="make_hidden2" name="fields[make_hidden][]" value="0"> Make it hidden<br>
                                                    <a onclick="makeDisable(2)" id="disable2" class="disable" status="disable">Disable</a>
                                                </div>
                                                <a onclick="makeDisable(2)" id="enable2" class="enable" status="enable" style="display:none">Enable</a>
                                            </div>

                                            <div class="group3">
                                                <h3><a href="#">Company Name</a></h3>
                                                <div class="draggable3">
                                                    <input name="fields[defaultFieldName][]" id="tag-slug" type="text" value="Company Name" size="40" placeholder="Type label name">
                                                    <input name="fields[defaultLabel][]" id="tag-slug" type="hidden" value="" size="40" placeholder="Type label i.e(Work,Home,Mobile...)">
                                                    <input name="fields[defaultIdentifier][]" id="tag-slug" type="hidden" value="companyName" size="40" placeholder="Type Upp Identifier">
                                                    <input name="fields[defaultFieldType][]" id="tag-slug" type="hidden" value="text" size="40" placeholder="">
                                                    <br>
                                                    <input name="fields[defaultHiddenFieldValue][]" id="input-value3" type="hidden" value="" size="40" placeholder="Type Value...">
                                                    <br>
                                                    <input name="fields[defaultFieldStatus][]" id="defaultFieldStatus_3" type="hidden" value="enable" size="40" placeholder="">
                                                    <input onclick="makeHidden(3)" type="checkbox" id="make_hidden3" name="fields[make_hidden][]" value="0"> Make it hidden<br>
                                                    <a onclick="makeDisable(3)" id="disable3" class="disable" status="disable">Disable</a>
                                                </div>
                                                <a onclick="makeDisable(3)" id="enable3" class="enable" status="enable" style="display:none">Enable</a>
                                            </div>

                                            <div class="group4">
                                                <h3><a href="#">Phone</a></h3>
                                                <div class="draggable4">
                                                    <input name="fields[defaultFieldName][]" id="tag-slug" type="text" value="Phone" size="40" placeholder="Type label name">
                                                    <input name="fields[defaultLabel][]" id="tag-slug" type="text" value="" size="40" placeholder="Type label i.e(Work,Home,Mobile...)">
                                                    <input name="fields[defaultIdentifier][]" id="tag-slug" type="hidden" value="phones" size="40" placeholder="Type Upp Identifier">
                                                    <input name="fields[defaultFieldType][]" id="tag-slug" type="hidden" value="text" size="40" placeholder="">
                                                    <br>
                                                    <input name="fields[defaultHiddenFieldValue][]" id="input-value4" type="hidden" value="" size="40" placeholder="Type Value...">
                                                    <br>
                                                    <input name="fields[defaultFieldStatus][]" id="defaultFieldStatus_4" type="hidden" value="enable" size="40" placeholder="">
                                                    <input onclick="makeHidden(4)" type="checkbox" id="make_hidden4" name="fields[make_hidden][]" value="0"> Make it hidden<br>
                                                    <a onclick="makeDisable(4)" id="disable4" class="disable" status="disable">Disable</a>
                                                </div>
                                                <a onclick="makeDisable(4)" id="enable4" class="enable" status="enable" style="display:none">Enable</a>
                                            </div>

                                            <div class="group5">
                                                <h3><a href="#">Address</a></h3>
                                                <div class="draggable5">
                                                    <input name="fields[defaultFieldName][]" id="tag-slug" type="text" value="Address" size="40" placeholder="Type label name">
                                                    <input name="fields[defaultLabel][]" id="tag-slug" type="hidden" value="" size="40" placeholder="Type label i.e(Work,Home,Mobile...)">
                                                    <input name="fields[defaultIdentifier][]" id="tag-slug" type="hidden" value="addresses" size="40" placeholder="Type Upp Identifier">
                                                    <input name="fields[defaultFieldType][]" id="tag-slug" type="hidden" value="select" size="40" placeholder="">
                                                    <br>
                                                    <input name="fields[defaultHiddenFieldValue][]" id="input-value5" type="hidden" value="" size="40" placeholder="Type Value...">
                                                    <br>
                                                    <input name="fields[defaultFieldStatus][]" id="defaultFieldStatus_5" type="hidden" value="enable" size="40" placeholder="">
                                                    <input onclick="makeHidden(5)" type="checkbox" id="make_hidden5" name="fields[make_hidden][]" value="0"> Make it hidden<br>
                                                    <a onclick="makeDisable(5)" id="disable5" class="disable" status="disable">Disable</a>
                                                </div>
                                                <a onclick="makeDisable(5)" id="enable5" class="enable" status="enable" style="display:none">Enable</a>
                                            </div>

                                        </div>

                                        <input type="hidden" id="count_field" name="count_field" value="5">  
                                        <div class="form-field term-slug-wrap">
                                            <input type="button" name="submit" onclick="generateCustomFieldForm()" id="submit" class="button button-default" value="Add Custom Field">
                                        </div> 
                                    </div>
                                    <div class="form-field form-required term-name-wrap">
                                        <label for="tag-name"><b>Tags</b></label>
                                        <textarea name="tags[]" placeholder="Type tags here.... tag1,tag2,tag3"></textarea> 
                                        <p>You can give some tag's here, added to UPP when this Accounts is saved.</p>
                                    </div>
                                    <div class="form-field form-required term-name-wrap">
                                        <label for="tag-name"><b>Redirect URL</b></label>
                                        <input name="redirect_url" value="" placeholder="http://your-site.com/your-page/" /> 
                                        <p>Type the url here you want to redirect after submitting this form from front end.</p>
                                    </div>
                                    <div class="form-field form-required term-name-wrap">
                                        <label for="tag-name"><b>Form Layout</b></label>
                                        <select name="form_layout">
                                            <option value="vertical">Vertical</option>
                                            <option value="horizontal">Horizontal</option>
                                        </select>
                                        <p>Select the layout you want to display on frontend.</p>
                                    </div>
                                    <input type="hidden" name="action" value="rsadd_uppform" />
                                    <p class="submit"><input type="button" name="submit" id="submit" class="button button-primary rs-add-upp-form" value="Add New Form">
                                    <input type="submit" name="submit" id="save-upp-form" class="button button-primary" value="Add New Form" >
                                    </p>
                                </form>
                            </div>

                        </div>
                    </div>
                    <!--------------Left Area End---------------------->

                    <!--------------Right Area Start---------------------->
                    <div id="col-right">
                        <div class="col-wrap">
                            <form action="<?php echo admin_url('admin.php'); ?>" method="post">

                                <div class="tablenav top">

                                    <div class="alignleft actions bulkactions">
                                        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label><select name="select_action" id="bulk-action-selector-top">
                                            <option value="-1">Bulk Actions</option>
                                            <option value="delete">Delete</option>
                                        </select>
                                        <input type="hidden" name="action" value="multidelete_rsuppform" />
                                        <input type="submit" id="doaction" class="button action" value="Apply">
                                    </div>
                                    <div class="tablenav-pages one-page"><span class="displaying-num">2 items</span>
                                        <span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                                            <span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Current Page</label><input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"><span class="tablenav-paging-text"> of <span class="total-pages">1</span></span></span>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
                                    <br class="clear">
                                </div>
                                <h2 class="screen-reader-text">Categories list</h2><table class="wp-list-table widefat fixed striped tags">
                                    <thead>
                                        <tr>
                                            <td id="cb" class="manage-column column-cb check-column">
                                                <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                                                <input id="cb-select-all-1" type="checkbox">
                                            </td>
                                            <th scope="col" id="name" class="manage-column column-name column-primary sortable desc">
                                                <a href="http://localhost/wp_upp/wp-admin/admin.php?">
                                                    <span>Title</span>
                                                    <span class="sorting-indicator"></span>
                                                </a>
                                            </th>
                                            <th scope="col" id="description" class="manage-column column-description sortable desc">
                                                <a href="http://localhost/wp_upp/wp-admin/admin.php?">
                                                    <span>Shortcode</span>
                                                    <span class="sorting-indicator"></span>
                                                </a>
                                            </th>
                                            <th scope="col" id="slug" class="manage-column column-slug sortable desc">
                                                <a href="http://localhost/wp_upp/wp-admin/admin.php?">
                                                    <span>Date</span>
                                                    <span class="sorting-indicator"></span>
                                                </a>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody id="the-list" data-wp-lists="list:tag">
                                        <?php
                                        $args = array(
                                            'posts_per_page' => -1,
                                            'post_type' => 'upp_forms',
                                            'order' => 'DESC',
                                            'orderby' => 'ID'
                                        );

                                        $all_forms = get_posts($args);
                                        foreach ($all_forms as $key => $value) {
                                            $meta = get_post_meta($value->ID);
                                            ?>
                                            <tr id="tag-11">
                                                <th scope="row" class="check-column">
                                                    <label class="screen-reader-text" for="cb-select-11">Select <?php echo $value->post_title ?></label>
                                                    <input type="checkbox" name="delete_tags[]" value="<?php echo $value->ID; ?>" id="cb-select-11">
                                                </th>
                                                <td class="name column-name has-row-actions column-primary" data-colname="Name">
                                                    <strong><a class="row-title" href="" aria-label="“test” (Edit)"><?php echo $value->post_title ?></a></strong>
                                                    <br>
                                                    <div class="row-actions">
                                                        <span class="edit">
                                                            <a href="<?php echo admin_url('admin.php?page=edit_rsuppform&id=' . $value->ID) ?>" aria-label="Edit “test”">Edit</a> | 
                                                        </span>
                                                        <span class="delete">
                                                            <a href="<?php echo admin_url('admin.php?action=delete_rsuppform&id=' . $value->ID) ?>" class="delete-tag aria-button-if-js" aria-label="Delete" role="button">Delete</a>
                                                        </span>
                                                        
                                                    </div>
                                                    <button type="button" class="toggle-row"><span class="screen-reader-textria-label">Edit</a> | </span><span class="inline hide-if-no-js"><a href="#" class="editinline aria-button-if-js" aria-label="Quick edit “test” inline" role="button">Quick&nbsp;Edit</a> ">Show more details</span></button>
                                                </td>
                                                <td class="description column-description" data-colname="shortcode"><p><?php echo $meta['uppShortcode'][0]; ?></p>
                                                </td>
                                                <td class="slug column-slug" data-colname="date"><?php echo $value->post_date ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                </table>


                            </form>


                        </div>
                    </div>

                    <!--------------Right Area End---------------------->

                </div>
            </div>

            <?php
        }

        public function edit_rsuppform() {

            $post = get_post($_GET['id']);
            if ($post->post_type != 'upp_forms') {
                wp_die(
                        '<h1>' . __('Warning!') . '</h1>' .
                        '<p>' . __('Sorry, you are not allowed to edit this page!') . '</p>', 403
                );
            }
            $content = json_decode($post->post_content);
            ?>
            <div id="col-container" class="wp-clearfix">
                <div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h2>
                                Edit Upp Form
                            </h2>
                            <h2>
                                <a href="<?php echo admin_url('admin.php?page=all_forms') ?>" class="nav-tab active">Go Back</a>
                            </h2><br>
                            
                            <form id="upp-edit-form" method="post" action="<?php echo admin_url('admin.php'); ?>">
                                <input type="hidden" name="post_id" value="<?php echo $_GET['id'] ?>">
                                <div class="form-field form-required term-name-wrap">
                                    <label for="tag-name"><b>Name</b><span class="upp-required">*</span></label>
                                    <input onkeyup="generateShortcode(this.value)" name="title" id="tag-name" type="text" value="<?php echo $post->post_title ?>" size="40" aria-required="true" >
                                    <p>The name is how it appears on your site.</p>
                                </div>
                                <div class="form-field term-slug-wrap">
                                    <label for="tag-slug"><b>ShortCode</b></label>
                                    <input name="shortcode" id="tag-slug" type="text" value="<?php echo $content->shortcode; ?>" size="40" readonly>
                                    <p>The “shortcode” is the code you include in your template to show the form. It is usually all lower case and contains only letters, numbers and hyphens</p>
                                </div>

                                <div class="form-field term-description-wrap uppformfields">
                                    <label for="tag-description"><b>Formfields</b></label>
                                    <div class="accordion" id="accordion">
                                        <?php foreach ($content->fields->defaultFieldName as $key => $value) { ?>
                                            <div class="group<?php echo $key ?>  <?php echo $content->fields->defaultFieldStatus[$key] ?>">  
                                                <h3><a href="#"><?php echo $content->fields->defaultFieldName[$key] ?></a></h3>
                                                <div class="draggable<?php echo $key ?>">
                                                    <input name="fields[defaultFieldName][]" id="tag-slug" type="text" value="<?php echo $content->fields->defaultFieldName[$key] ?>" size="40" placeholder="Type label name">
                                                    <input name="fields[defaultIdentifier][]" id="tag-slug" type="text" value="<?php echo $content->fields->defaultIdentifier[$key] ?>" size="40" placeholder="Type Upp Identifier">
                                                    <input name="fields[defaultFieldType][]" id="tag-slug" type="hidden" value="<?php echo $content->fields->defaultFieldType[$key] ?>" size="40" placeholder="">
                                                    <br>
                                                    <?php if ($content->fields->defaultHiddenFieldValue[$key] == '') { ?>
                                                        <input name="fields[defaultHiddenFieldValue][]" id="input-value1" type="hidden" value="" size="40" placeholder="Type Value...">
                                                    <?php } else { ?>
                                                        <input name="fields[defaultHiddenFieldValue][]" id="input-value1" type="text" value="<?php echo $content->fields->defaultHiddenFieldValue[$key] ?>" size="40" placeholder="Type Value...">
                                                    <?php } ?>
                                                    <br>
                                                    <input onclick="makeHidden('<?php echo $key ?>')" type="checkbox" id="make_hidden1" name="fields[make_hidden][]" value="0" <?php
                                                    if ($content->fields->defaultHiddenFieldValue[$key] != '') {
                                                        echo 'checked';
                                                    }
                                                    ?>> Make it hidden <br>
                                                    <input name="fields[defaultFieldStatus][]" id="defaultFieldStatus_<?php echo $key ?>" type="hidden" value="<?php echo $content->fields->defaultFieldStatus[$key] ?>" size="40" placeholder="">
                                                    <a onclick="makeDisable('<?php echo $key ?>')" id="disable<?php echo $key ?>" class="disable" status="disable">Disable</a>
                                                </div>
                                                <a onclick="makeDisable('<?php echo $key ?>')" id="enable<?php echo $key ?>" class="enable" status="enable">Enable</a>
                                            </div>
                                        <?php } ?>

                                        <?php
                                        $i = 1;
                                        $count = count($content->fields->defaultFieldName);
                                        if(isset($content->customfields->customfieldlabel) && !empty($content->customfields->customfieldlabel)){
                                        foreach ($content->customfields->customfieldlabel as $key => $value) {
                                            ?>
                                            <div class="group<?php echo $count + $i ?>">  
                                                <h3><a href="#"><?php echo $content->customfields->customfieldlabel[$key] ?></a></h3>
                                                <div class="draggable<?php echo $count + $i ?>">
                                                    <input onkeyup="customfieldLabel(this.value)" name="customfields[customfieldlabel][]" id="input-label<?php echo $count + $i ?>" type="text" value="<?php echo $content->customfields->customfieldlabel[$key] ?>" size="40" placeholder="Type label name">
                                                    <label for="label-name" class="label-name<?php echo $count + $i ?>"><b>Field Type</b></label>
                                                    <select name="customfields[customfieldType][]" onchange="customfieldType(this.value)">
                                                        <option value="text" <?php if ($content->customfields->customfieldType[$key] == 'text') echo 'selected'; ?>>Text</option>
                                                        <option value="number" <?php if ($content->customfields->customfieldType[$key] == 'number') echo 'selected'; ?>>Number</option>
                                                        <option value="textarea" <?php if ($content->customfields->customfieldType[$key] == 'textarea') echo 'selected'; ?>>Textarea</option>
                                                        <option value="checkbox" <?php if ($content->customfields->customfieldType[$key] == 'checkbox') echo 'selected'; ?>>Checkbox</option>
                                                        <option value="radio" <?php if ($content->customfields->customfieldType[$key] == 'radio') echo 'selected'; ?>>Radio</option>
                                                        <option value="select" <?php if ($content->customfields->customfieldType[$key] == 'select') echo 'selected'; ?>>Select</option>
                                                    </select>
                                                    <label for="label-name" class="label-name"><b>Feild value Format<span style="font-size:12px;"> ( except radio,checkbox or select please keep empty this field )</span></b></label>
                                                    <textarea name="customfields[customfieldValue][]" placeholder="Multiple value format for radio, checkbox and select field : a|b|c|d"><?php echo $content->customfields->customfieldValue[$key] ?></textarea>
                                                    <input name="customfields[customfieldIdentifier][]" id="tag-slug" type="text" value="<?php echo $content->customfields->customfieldIdentifier[$key] ?>" size="40" placeholder="Type Upp Identifier...">
                                                    <?php if ($content->customfields->customHiddenfieldValue[$key] == '') { ?>
                                                        <input name="customfields[customHiddenfieldValue][]" id="input-value<?php echo $count + $i ?>" type="hidden" value="<?php echo $content->customfields->customHiddenfieldValue[$key] ?>" size="40" placeholder="Type Value...">
                                                    <?php } else { ?>
                                                        <input name="customfields[customHiddenfieldValue][]" id="input-value<?php echo $count + $i ?>" type="text" value="<?php echo $content->customfields->customHiddenfieldValue[$key] ?>" size="40" placeholder="Type Value...">
                                                    <?php } ?>
                                                    <input onclick="makeHidden('<?php echo $count + $i ?>')" type="checkbox" id="make_hidden<?php echo $count + $i ?>" name="customfields[make_hidden][]" value="0" <?php
                                                    if ($content->customfields->customHiddenfieldValue[$key] != '') {
                                                        echo 'checked';
                                                    }
                                                    ?>> Make it hidden<br>
                                                    <a onclick="removeCustomfield('<?php echo $count + $i ?>')">Remove</a>
                                                </div>
                                            </div>    
                                            <?php
                                            $i++;
                                        }
                                        }
                                        ?>
                                    </div>

                                    <div class="form-field term-slug-wrap">
                                        <input type="button" name="submit" onclick="generateCustomFieldForm()" id="submit" class="button button-default" value="Add Custom Field">
                                    </div> 
                                </div>
            <?php $totalcount = $count + count($content->customfields->customfieldlabel) ?>
                                <input type="hidden" id="count_field" name="count_field" value="<?php echo $totalcount + 1; ?>">  
                                <div class="form-field form-required term-name-wrap">
                                    <label for="tag-name"><b>Tags</b></label>
                                    <textarea name="tags[]" placeholder="Type tags here.... tag1,tag2,tag3"><?php
                echo implode(',',$content->tags);
             ?></textarea> 
                                    <p>You can give some tag's here, added to UPP when this Accounts is saved.</p>
                                </div>
                                <div class="form-field form-required term-name-wrap">
                                    <label for="tag-name"><b>Redirect URL</b></label>
                                    <input name="redirect_url" value="<?php echo $content->redirect_url ?>" placeholder="http://your-site.com/your-page/" /> 
                                    <p>Type the url here you want to redirect after submitting this form from front end.</p>
                                </div>
                                <div class="form-field form-required term-name-wrap">
                                        <label for="tag-name"><b>Form Layout</b></label>
                                        <select name="form_layout">
                                            <option value="vertical" <?php if($content->form_layout == "vertical") {echo 'selected';} ?>>Vertical</option>
                                            <option value="horizontal" <?php if($content->form_layout == "horizontal") {echo 'selected';} ?>>Horizontal</option>
                                        </select>
                                        <p>Select the layout you want to display on frontend.</p>
                                    </div>
                                 <div class="form-field form-required term-name-wrap">
                                    <label for="tag-name"><b>Submit button text</b></label>
                                    <input name="submit_button_text" value="<?php echo $content->submit_button_text ?>" placeholder="Type submite button text" /> 
                                    <p>Type submit button text here , you want to display your front end form. Default will be "Submit".</p>
                                </div>
                                <input type="hidden" name="action" value="rsupdate_uppform" />
                                <p class="submit"><input type="button" name="submit" id="submit" class="button button-primary rs-edit-upp-form" value="Update Upp Form">
                                <input type="submit" name="submit" id="edit-upp-form" class="button button-primary" value="Update Form"></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function rsadd_uppform() {

            global $wpdb;
            $my_post = array(
                'post_title' => $_POST['title'],
                'post_content' => json_encode($_POST),
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'upp_forms'
            );
            
            // Insert the post into the database.
            wp_insert_post($my_post);

            $post_id = $wpdb->insert_id;
            $args = array(
                'post_type' => 'upp_forms',
                'post_status' => 'any',
                'meta_key' => 'uppShortcode',
                'meta_value' => $_POST['shortcode'],
            );
            $posts = get_posts($args);
            //echo $wpdb->last_query; 
            $meta_value = $_POST['shortcode'];
            if (!empty($posts)) {
                $meta_value = $_POST['shortcode'] . '_' . $post_id;
            }

            add_post_meta($post_id, 'uppShortcode', $meta_value);

            $msg = 'success';
            $path = add_query_arg('message', $msg, $_SERVER['HTTP_REFERER']);
            wp_redirect($path, $status = 302);
            exit();
        }

        public function rsupdate_uppform() {

            global $wpdb;
            $my_post = array(
                'ID' => $_POST['post_id'],
                'post_title' => $_POST['title'],
                'post_content' => json_encode($_POST),
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'upp_forms'
            );

            // Update the post into the database.
            wp_update_post($my_post);

            $post_id = $_POST['post_id'];
            $args = array(
                'post_type' => 'upp_forms',
                'post_status' => 'any',
                'meta_key' => 'uppShortcode',
                'meta_value' => $_POST['shortcode'],
                'post__not_in' => array($post_id)
            );
            $posts = get_posts($args);

            $meta_value = $_POST['shortcode'];
            if (!empty($posts)) {
                $meta_value = $_POST['shortcode'] . '_' . $post_id;
            }

            update_post_meta($post_id, 'uppShortcode', $meta_value);

            $msg = 'success';
            $path = add_query_arg('message', $msg, $_SERVER['HTTP_REFERER']);
            wp_redirect($path, $status = 302);
            exit();
        }

        public function delete_rsuppform() {

            global $wpdb;
            $post_id = $_GET['id'];
            wp_delete_post($post_id);
            delete_post_meta($post_id, 'uppShortcode');
            $msg = 'success';
            $path = add_query_arg('message', $msg, $_SERVER['HTTP_REFERER']);
            wp_redirect($path, $status = 302);
            exit();
        }

        public function multidelete_rsuppform() {

            if ($_POST['select_action'] == 'delete') {
                if (!empty($_POST['delete_tags'])) {
                    foreach ($_POST['delete_tags'] as $value) {
                        wp_delete_post($value);
                        delete_post_meta($value, 'uppShortcode');
                    }
                }
                $msg = 'success';
                $path = add_query_arg('message', $msg, $_SERVER['HTTP_REFERER']);
                wp_redirect($path, $status = 302);
                exit();
            }
        }

    }

}