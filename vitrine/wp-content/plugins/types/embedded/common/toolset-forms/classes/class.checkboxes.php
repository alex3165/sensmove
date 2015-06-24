<?php
/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.checkboxes.php $
 * $LastChangedDate: 2015-01-16 14:28:15 +0000 (Fri, 16 Jan 2015) $
 * $LastChangedRevision: 1069430 $
 * $LastChangedBy: iworks $
 *
 */

require_once 'class.field_factory.php';

class WPToolset_Field_Checkboxes extends FieldFactory
{
    public function metaform()
    {
        global $post;
        $value = $this->getValue();
        $data = $this->getData();
        $name = $this->getName();               

        $form = array();
        $_options = array();
        if (isset($data['options'])) {
            foreach ( $data['options'] as $option_key => $option ) {

                $checked = isset( $option['checked'] ) ? $option['checked'] : !empty( $value[$option_key] );

                if (isset($post) && 'auto-draft' == $post->post_status && array_key_exists( 'checked', $option ) && $option['checked']) {
                    $checked = true;
                }

                // Comment out broken code. This tries to set the previous state after validation fails
                //$_values=$this->getValue();
                //if (!$checked&&isset($value)&&!empty($value)&&is_array($value)&&in_array($option['value'],$value)) {
                //    $checked=true;
                //}

                $_options[$option_key] = array(
                    '#value' => $option['value'],
                    '#title' => $option['title'],
                    '#type' => 'checkbox',
                    '#default_value' => $checked,
                    '#name' => $option['name']."[]",
                    //'#inline' => true,
                );

                if ( isset( $option['data-value'] ) ) {
                    //Fixing https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188528502/comments
                    $_options[$option_key]['#attributes'] = array('data-value' => $option['data-value']);
                }

                if ( !is_admin() ) {// TODO maybe add a doing_ajax() check too, what if we want to load a form using AJAX?
                    $clases = array(
                        'wpt-form-item',
                        'wpt-form-item-checkbox',
                        'checkbox-'.sanitize_title($option['title'])
                    );
                    /**
                     * filter: cred_checkboxes_class
                     * @param array $clases current array of classes
                     * @parem array $option current option
                     * @param string field type
                     *
                     * @return array
                     */
                    $clases = apply_filters( 'cred_item_li_class', $clases, $option, 'checkboxes' );
                    $_options[$option_key]['#before'] = sprintf(
                        '<li class="%s">',
                        implode(' ', $clases)
                    );
					$_options[$option_key]['#after'] = '</li>';
					$_options[$option_key]['#pattern'] = '<BEFORE><PREFIX><ELEMENT><LABEL><ERROR><SUFFIX><DESCRIPTION><AFTER>';
				}
            }
        }
        $metaform = array(
            '#type' => 'checkboxes',
            '#options' => $_options,
            '#description' => $this->getDescription(),
        );
        if ( is_admin() ) {
            $metaform['#title'] = $this->getTitle();
            $metaform['#after'] = '<input type="hidden" name="_wptoolset_checkbox[' . $this->getId() . ']" value="1" />';
        } else {
			$metaform['#before'] = '<ul class="wpt-form-set wpt-form-set-checkboxes wpt-form-set-checkboxes-' . $name . '">';
			$metaform['#after'] = '</ul>';
		}
        return array($metaform);
    }
}
