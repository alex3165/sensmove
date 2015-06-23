<?php
require_once 'class.textarea.php';

/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.wysiwyg.php $
 * $LastChangedDate: 2014-10-29 15:57:36 +0000 (Wed, 29 Oct 2014) $
 * $LastChangedRevision: 1016002 $
 * $LastChangedBy: iworks $
 *
 */
class WPToolset_Field_Wysiwyg extends WPToolset_Field_Textarea
{
    protected $_settings = array('min_wp_version' => '3.3');

    public function metaform()
    {

        $attributes = $this->getAttr();
        $form = array();
        $markup = '';
        if ( is_admin() ) {
            $markup .= '<div class="form-item form-item-markup">';
            $markup .= sprintf( '<label class="wpt-form-label wpt-form-textfield-label">%s</label>', $this->getTitle() );
        }
        $markup .= stripcslashes($this->getDescription());
        $markup .= $this->_editor($attributes);
        if ( is_admin() ) {
            $markup .= '</div>';
        }
        $form[] = array(
            '#type' => 'markup',
            '#markup' => $markup
        );
        return $form;
    }

    protected function _editor(&$attributes)
    {

        if (isset($attributes['readonly'])&&$attributes['readonly']=='readonly') {
            add_filter( 'tiny_mce_before_init',  array(&$this, 'tiny_mce_before_init_callback'));
        }

        //EMERSON: Rewritten to set do_concat to TRUE so WordPress won't echo styles directly to the browser
        //This will fix a lot of issues as WordPress will not be echoing content to the browser before header() is called
        //This fix is important so we will not be necessarily adding ob_start() here in this todo:
        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/185336518/comments#282283111
        //Using ob_start in that code will have some side effects of some styles from other plugins not being properly loaded.

        global $wp_styles;
        $wp_styles->do_concat=TRUE;
        ob_start();
        wp_editor( $this->getValue(), $this->getId(),
            array(
                'wpautop' => true, // use wpautop?
                'media_buttons' => $this->_data['has_media_button'], // show insert/upload button(s)
                'textarea_name' => $this->getName(), // set the textarea name to something different, square brackets [] can be used here
                'textarea_rows' => get_option( 'default_post_edit_rows', 10 ), // rows="..."
                'tabindex' => '',
                'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                'editor_class' => 'wpt-wysiwyg', // add extra class(es) to the editor textarea
                'teeny' => false, // output the minimal editor config used in Press This
                'dfw' => false, // replace the default fullscreen with DFW (needs specific DOM elements and css)
                'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array(),
            ) );
        return ob_get_clean() . "\n\n";
        $wp_styles->do_concat=FALSE;
   }

    /*RICCARDO: removed anonymous function for retrocompatibility */
    public function tiny_mce_before_init_callback($args)
    {
        // do you existing check for published here
        if ( 1 == 1 )
            $args['readonly'] = 1;

        return $args;
    }

}
