<?php

if (!function_exists('add_action')) {
	echo '.';
	exit;
}

require_once dirname( __FILE__ ) . "/common.php";

add_action('admin_init', 'webling_admin_init');

function webling_admin_init() {
	register_setting(WEBLING_OPTIONS_GROUP, WEBLING_OPTIONS);
}

add_action('admin_menu', 'webling_admin_add_page');

function webling_admin_fieldlist() {
	$options = get_option(WEBLING_OPTIONS);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $options["host"] . "/api/1/config?apikey=" . $options["apikey"]);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$config = json_decode(curl_exec($curl), true);
	curl_close($curl);

    $output =  '
            <script>
            jQuery(document).ready(function() {
                items = jQuery("#felder").val().split(",");
                for (i in items) {
                    console.log(jQuery("#check" + String(items[i])), "#check" + String(items[i]));
                    jQuery("#check" + String(items[i]).trim()).prop("checked", true);
                }
                jQuery("#webling-sortable input").click(function() {
                    jQuery("#felder").val("");
                    jQuery("#webling-sortable input:checked").each(function() {
                        val = jQuery("#felder").val();
                        val += jQuery(this).val() + ", ";
                        jQuery("#felder").val(val);
                    })
                });
            });
            </script>
            <ul id="webling-sortable">';

    foreach (array_keys($config["member"]["properties"]) as $field) {
        $output .= '<li><input type="checkbox" id="check' . $field . '" value="' . $field . '" />' . $field . '</li>';
    }

    $output .= '</ul>';
    return $output;
}

function webling_admin_options_page(){
	$options = get_option(WEBLING_OPTIONS);

	echo '<div class="wrap">
        <h2>Webling Optionen</h2>
        <p>Die Webling Mitgliederliste wird automatisch vom angegebenen Server geladen und kann mit dem Shortcode [webling_mitgliederliste] anzeigt werden. 
        <p/>
        <form method="post" action="options.php" id="webling-form">';
            echo settings_fields(WEBLING_OPTIONS_GROUP);
            echo '<table class="form-table">
                <tr valign="top"><th scope="row">Webling-URL:</th>
                    <td><input type="text" name="'. WEBLING_OPTIONS.'[host]" value="'. $options['host'] .'" class="regular-text code" /> (z.B. demo1.webling.ch)</td>
                </tr>
                <tr valign="top"><th scope="row">API Key:</th>
                    <td><input type="text" name="'. WEBLING_OPTIONS.'[apikey]" value="'. $options['apikey'] .'" class="regular-text code" /></td>
                </tr>
                <tr valign="top"><th scope="row">Angezeigte Felder:</th>
                    <td><input type="text" name="'. WEBLING_OPTIONS.'[fields]" value="'. $options['fields'] .'" id="felder" class="regular-text code" style="width: 676px" /> (durch , getrennt. z.B. Vorname, Name, E-Mail)</td>
                </tr>
                <tr valign="top"><th scope="row">Feldliste:</th>
                    <td>';
                    try {
                        echo webling_admin_fieldlist();
                    } catch (Exception $e) {
                        echo '<div class="error"><p>' . $e->getMessage() . '</p></div>';
                    }
                    echo '</td>
                </tr>
                <tr valign="top"><th scope="row">Eigenes CSS:</th>
                    <td><textarea name="'. WEBLING_OPTIONS.'[css]" value="'. $options['css'] .'" rows="5" cols="40">
                    </textarea>
                </tr>
            </table>';

            echo '<p class="submit">
                <input type="submit" class="button-primary" value="Speichern" />
            </p>
        </form>
    </div>';
}

function webling_admin_add_page(){
	add_options_page('Webling', 'Webling', 'manage_options', WEBLING_MENU_SLUG, 'webling_admin_options_page');
}
