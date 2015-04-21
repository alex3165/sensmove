<?php
/*
 * Skype select button form.
 */

if ( !defined( 'ABSPATH' ) ) {
    die( 'Security check' );
}

if ( !isset( $data ) ) {
    $data = array();
}

$data = array_merge( array(
    'button_style' => 'btn1',
        ), (array) $data );
?>
<h3><?php _e( 'Select a button from below', 'wpcf' ); ?></h3>
<table border="0" cellpadding="0" cellspacing="0" width="445">
    <colgroup>
        <col span="1" width="223">
        <col span="1" width="222">
    </colgroup>
    <tbody>
        <tr>
            <td colspan="1" rowspan="1">
                <label for="btn1">
                    <input <?php if ( $data['button_style'] == 'btn1' ) echo 'checked="checked" ';?>id="btn1" name="button_style" tabindex="2" value="btn1" type="radio" />  
                    <img alt="" id="btn1-img" src="http://www.skypeassets.com/i/legacy/images/share/buttons/call_green_white_153x63.png" height="63" width="153" />
                </label>
            </td>
            <td colspan="1" rowspan="1"> 
                <label for="btn2"> 
                    <input <?php if ( $data['button_style'] == 'btn2' ) echo 'checked="checked" '; ?>id="btn2" name="button_style" tabindex="3" value="btn2" type="radio" />  
                    <img alt="" id="btn2-img" src="http://www.skypeassets.com/i/legacy/images/share/buttons/call_blue_white_124x52.png" height="52" width="125" /> 
                </label> 
            </td>
        </tr>
        <tr>
            <td colspan="1" rowspan="1"> 
                <label for="btn3"> 
                    <input <?php if ( $data['button_style'] == 'btn3' ) echo 'checked="checked" '; ?>id="btn3" name="button_style" tabindex="4" value="btn3" type="radio" />  
                    <img alt="" id="btn3-img" src="http://www.skypeassets.com/i/legacy/images/share/buttons/call_green_white_92x82.png" height="82" width="92" /> 
                </label> 
            </td>
            <td colspan="1" rowspan="1"> 
                <label for="btn4"> 
                    <input <?php if ( $data['button_style'] == 'btn4' ) echo 'checked="checked" '; ?>id="btn4" name="button_style" tabindex="5" value="btn4" type="radio" />  
                    <img alt="" id="btn4-img" src="http://www.skypeassets.com/i/legacy/images/share/buttons/call_blue_transparent_34x34.png" height="34" width="34" /> 
                </label> 
            </td>
        </tr>
    </tbody>
</table> 

<h3><?php _e( 'Skype buttons with status', 'wpcf' ); ?></h3>  
<p><?php _e( 'If you choose to show your Skype status, your Skype button will always reflect your availability on Skype. This status will be shown to everyone, whether they’re in your contact list or not.', 'wpcf' ); ?></p>

<table border="0" cellpadding="0" cellspacing="0" width="445">
    <colgroup>
        <col span="1" width="223">
        <col span="1" width="222">
    </colgroup>
    <tbody>
        <tr>
            <td colspan="1" rowspan="1"> 
                <label for="btn5"> 
                    <input <?php if ( $data['button_style'] == 'btn5' ) echo 'checked="checked" '; ?>id="btn5" name="button_style" tabindex="6" value="btn5" type="radio" />  
                    <img alt="" id="btn5-img" src="http://www.skypeassets.com/i/legacy/images/share/buttons/anim_balloon.gif" height="60" width="150" /> 
                </label> 
            </td>
            <td colspan="1" rowspan="1"> 
                <label for="btn6"> 
                    <input <?php if ( $data['button_style'] == 'btn6' ) echo 'checked="checked" '; ?>id="btn6" name="button_style" tabindex="7" value="btn6" type="radio" />  
                    <img alt="" id="btn6-img" src="http://www.skypeassets.com/i/legacy/images/share/buttons/anim_rectangle.gif" height="44" width="182" /> 
                </label> 
            </td>
        </tr>
    </tbody>
</table> 