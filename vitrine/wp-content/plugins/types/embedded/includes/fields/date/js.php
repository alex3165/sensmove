<?php

/**
 * Renders inline JS.
 * TODO this seems DEPRECATED and not used anymore, need to check (although I do not know where)
 */
function wpcf_fields_date_meta_box_js_inline() {

    $date_format = wpcf_get_date_format();
    $date_format = _wpcf_date_convert_wp_to_js( $date_format );

    $date_format_note = '<span style="margin-left:10px"><i>' . esc_js( sprintf( __( 'Input format: %s',
                                    'wpcf' ), wpcf_get_date_format_text() ) ) . '</i></span>';
    $year_range = fields_date_timestamp_neg_supported() ? '1902:2037' : '1970:2037';

    ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function(){
            wpcfFieldsDateInit('');
        });
        function wpcfFieldsDateInit(div) {
            if (jQuery.isFunction(jQuery.fn.datepicker)) {
                jQuery(div+' .wpcf-datepicker').each(function(index) {
                    if (!jQuery(this).is(':disabled') && !jQuery(this).hasClass('hasDatepicker')) {
                        jQuery(this).datepicker({
                            showOn: "button",
                            buttonImage: "<?php echo WPCF_EMBEDDED_RES_RELPATH; ?>/images/calendar.gif",
                            buttonImageOnly: true,
                            buttonText: "<?php
    _e( 'Select date', 'wpcf' );

    ?>",
                            dateFormat: "<?php echo $date_format; ?>",
                            altFormat: "<?php echo $date_format; ?>",
                            changeMonth: true,
                            changeYear: true,
                            yearRange: "<?php echo $year_range; ?>",
                            onSelect: function(dateText, inst) {
                                jQuery(this).trigger('wpcfDateBlur');
                            }
                        });
                        jQuery(this).next().after('<?php echo $date_format_note; ?>');
                        // Wrap in CSS Scope
                        jQuery("#ui-datepicker-div").each(function(){
                            if (!jQuery(this).hasClass('wpcf-jquery-ui-wrapped')) {
                                jQuery(this).wrap('<div class="wpcf-jquery-ui" />')
                                .addClass('wpcf-jquery-ui-wrapped');
                            }
                        });
                    }
                });
            }
        }
        //]]>
    </script>
    <?php
}

/**
 * AJAX window JS.
 */
function wpcf_fields_date_editor_form_script() {

    ?>
    <script type="text/javascript">
        // <![CDATA[
        jQuery(document).ready(function(){
            jQuery('input[name|="wpcf[style]"]').change(function(){
                if (jQuery(this).val() == 'text') {
                    jQuery('#wpcf-toggle').slideDown();
                } else {
                    jQuery('#wpcf-toggle').slideUp();
                }
            });
            if (jQuery('input[name="wpcf[style]"]:checked').val() == 'text') {
                jQuery('#wpcf-toggle').show();
            }
        });
        // ]]>
    </script>
    <?php
}