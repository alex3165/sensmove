<?php

/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.taxonomyhierarchical.php $
 * $LastChangedDate: 2015-03-16 12:03:31 +0000 (Mon, 16 Mar 2015) $
 * $LastChangedRevision: 1113864 $
 * $LastChangedBy: iworks $
 *
 */
include_once 'class.textfield.php';

class WPToolset_Field_Taxonomyhierarchical extends WPToolset_Field_Textfield {

    protected $child;
    protected $names;
    protected $values = array();
    protected $valuesId = array();
    protected $objValues;

    public function init() {
        global $post;

        $this->objValues = array();
        if (isset($post)) {
            $terms = wp_get_post_terms($post->ID, $this->getName(), array("fields" => "all"));
            foreach ($terms as $n => $term) {
                $this->values[] = $term->slug;
                $this->valuesId[] = $term->term_id;
                $this->objValues[$term->slug] = $term;
            }
        }

        $all = $this->buildTerms(get_terms($this->getName(), array('hide_empty' => 0, 'fields' => 'all')));



        $childs = array();
        $names = array();
        foreach ($all as $term) {
            $names[$term['term_id']] = $term['name'];
            if (!isset($childs[$term['parent']]) || !is_array($childs[$term['parent']]))
                $childs[$term['parent']] = array();
            $childs[$term['parent']][] = $term['term_id'];
        }

//        ksort($childs);

        $this->childs = $childs;
        $this->names = $names;
    }

    public function enqueueScripts() {
        
    }

    public function enqueueStyles() {
        
    }

    public function metaform() {
        $use_bootstrap = array_key_exists('use_bootstrap', $this->_data) && $this->_data['use_bootstrap'];
        $attributes = $this->getAttr();
        $taxname = $this->getName();
        $res = '';
        $metaform = array();
        $build_what = '';

        if (array_key_exists('display', $this->_data) && 'select' == $this->_data['display']) {
            $metaform = $this->buildSelect();
            $build_what = 'select';
        } else {
            $res = $this->buildCheckboxes(0, $this->childs, $this->names, $metaform);
            $this->set_metaform($res);
            $build_what = 'checkboxes';
        }

        /**
         * TODO
         *
         * Use this to get the taxonomy labels for the "Add new" event
         *
         * $taxobject = get_taxonomy( $taxname );
         */
        /**
         * "Add new" button
         */
        $metaform[] = array(
            '#type' => 'button',
            '#title' => '',
            '#description' => '',
            '#name' => "btn_" . $taxname,
            '#value' => apply_filters('toolset_button_add_new_text', esc_attr($attributes['add_new_text'])),
            '#attributes' => array(
                'style' => 'display:none;',
                'data-taxonomy' => $taxname,
                'data-build_what' => $build_what,
                'data-after-selector' => 'js-wpt-hierarchical-taxonomy-add-new-' . $taxname,
                'data-open' => apply_filters('toolset_button_add_new_text', esc_attr($attributes['add_new_text'])),
                'data-close' => apply_filters('toolset_button_cancel_text', esc_attr(__('Cancel', 'wpv-views'))), // TODO adjust the button value depending on open/close action
                'class' => $use_bootstrap ? 'btn btn-default wpt-hierarchical-taxonomy-add-new-show-hide js-wpt-hierarchical-taxonomy-add-new-show-hide' : 'wpt-hierarchical-taxonomy-add-new-show-hide js-wpt-hierarchical-taxonomy-add-new-show-hide',
            ),
            '#validate' => $this->getValidationData(),
        );

        // Input for new taxonomy

        if ($use_bootstrap) {
            $container = '<div style="display:none" class="form-group wpt-hierarchical-taxonomy-add-new js-wpt-hierarchical-taxonomy-add-new-' . $taxname . '" data-taxonomy="' . $taxname . '">';
        } else {
            $container = '<div style="display:none" class="wpt-hierarchical-taxonomy-add-new js-wpt-hierarchical-taxonomy-add-new-' . $taxname . '" data-taxonomy="' . $taxname . '">';
        }

        /**
         * The textfield input
         */
        $metaform[] = array(
            '#type' => 'textfield',
            '#title' => '',
            '#description' => '',
            '#name' => "new_tax_text_" . $taxname,
            '#value' => '',
            '#attributes' => array(
                'data-taxonomy' => $taxname,
                'data-taxtype' => 'hierarchical',
                'class' => $use_bootstrap ? 'inline wpt-new-taxonomy-title js-wpt-new-taxonomy-title' : 'wpt-new-taxonomy-title js-wpt-new-taxonomy-title',
            ),
            '#validate' => $this->getValidationData(),
            '#before' => $container,
        );

        /**
         * The select for parent
         */
        $metaform[] = array(
            '#type' => 'select',
            '#title' => '',
            '#options' => array(array(
                    '#title' => $attributes['parent_text'],
                    '#value' => -1,
                )),
            '#default_value' => 0,
            '#description' => '',
            '#name' => "new_tax_select_" . $taxname,
            '#attributes' => array(
                'data-parent-text' => $attributes['parent_text'],
                'data-taxonomy' => $taxname,
                'class' => 'js-taxonomy-parent wpt-taxonomy-parent'
            ),
            '#validate' => $this->getValidationData(),
        );

        /**
         * The add button
         */
        $metaform[] = array(
            '#type' => 'button',
            '#title' => '',
            '#description' => '',
            '#name' => "new_tax_button_" . $taxname,
            '#value' => apply_filters('toolset_button_add_text', esc_attr($attributes['add_text'])),
            '#attributes' => array(
                'data-taxonomy' => $taxname,
                'data-build_what' => $build_what,
                'class' => $use_bootstrap ? 'btn btn-default wpt-hierarchical-taxonomy-add-new js-wpt-hierarchical-taxonomy-add-new' : 'wpt-hierarchical-taxonomy-add-new js-wpt-hierarchical-taxonomy-add-new',
            ),
            '#validate' => $this->getValidationData(),
            '#after' => '</div>',
        );

        return $metaform;
    }

    private function buildTerms($obj_terms) {
        $tax_terms = array();
        foreach ($obj_terms as $term) {
            $tax_terms[] = array(
                'name' => $term->name,
                'count' => $term->count,
                'parent' => $term->parent,
                'term_taxonomy_id' => $term->term_taxonomy_id,
                'term_id' => $term->term_id
            );
        }
        return $tax_terms;
    }

    private function buildSelect() {
        $attributes = $this->getAttr();

        $multiple = !isset($attributes['single_select']) || !$attributes['single_select'];

        $curr_options = $this->getOptions();
        $values = $this->valuesId;
        $options = array();
        if ($curr_options) {
            foreach ($curr_options as $name => $data) {
                $option = array(
                    '#value' => $name,
                    '#title' => $data['value'],
                    '#attributes' => array('data-parent' => $data['parent'])
                );
                if ($multiple && in_array($name, $values)) {
                    $option['#attributes']['selected'] = '';
                }

                $options[] = $option;
            }
        }

        /**
         * default_value
         */
        $default_value = null;
        if (count($this->valuesId)) {
            $default_value = $this->valuesId[0];
        }
        /**
         * form settings
         */
        $form = array();
        $select = array(
            '#type' => 'select',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName() . '[]',
            '#options' => $options,
            '#default_value' => isset($data['default_value']) && !empty($data['default_value']) ? $data['default_value'] : $default_value,
            '#validate' => $this->getValidationData(),
            '#class' => 'form-inline',
            '#repetitive' => $this->isRepetitive(),
        );

        if ($multiple) {
            $select['#attributes'] = array('multiple' => 'multiple');
        }

        if (count($options) == 0) {
            if (isset($select['#attributes'])) {
                $select['#attributes']['style'] = 'display:none';
            } else {
                $select['#attributes'] = array('style' => 'display:none');
            }
        }
        $form[] = $select;

        return $form;
    }

    private function getOptions($index = 0, $level = 0, $parent = -1) {
        if (!isset($this->childs[$index]) || empty($this->childs[$index])) {
            return;
        }
        $options = array();

        foreach ($this->childs[$index] as $one) {
            $options[$one] = array('value' => sprintf('%s%s', str_repeat('&nbsp;', 2 * $level), $this->names[$one]),
                'parent' => $parent);
            if (isset($this->childs[$one]) && count($this->childs[$one])) {
                foreach ($this->getOptions($one, $level + 1, $one) as $id => $data) {
                    $options[$id] = $data;
                }
            }
        }
        return $options;
    }

    private function buildCheckboxes($index, &$childs, &$names, &$metaform, $level = 0, $parent = -1) {
        if (isset($childs[$index])) {
            $level_count = count($childs[$index]);
            foreach ($childs[$index] as $tkey => $tid) {
                $name = $names[$tid];
                /**
                 * check for "checked"
                 */
                $default_value = false;
                if (isset($this->valuesId) && is_array($this->valuesId) && !empty($this->valuesId)) {
                    $default_value = in_array($tid, $this->valuesId);
                } else if (is_array($this->getValue())) {
                    $default_value = in_array($tid, $this->getValue());
                }
                $clases = array();
                $clases[] = 'tax-' . sanitize_title($names[$tid]);
                $clases[] = 'tax-' . $this->_data['name'] . '-' . $tid;
                /**
                 * filter: cred_checkboxes_class
                 * @param array $clases current array of classes
                 * @parem array $option current option
                 * @param string field type
                 *
                 * @return array
                 */
                $clases = apply_filters('cred_item_li_class', $clases, array('id' => $tid, 'name' => $name), 'taxonomyhierarchical');

                $item = array(
                    '#type' => 'checkbox',
                    '#title' => $names[$tid],
                    '#description' => '',
                    '#name' => $this->getName() . "[]",
                    '#value' => $tid,
                    '#default_value' => $default_value,
                    '#validate' => $this->getValidationData(),
                    '#before' => sprintf('<li class="%s">', implode(' ', $clases)),
                    '#after' => '</li>',
                    '#attributes' => array(
                        'data-parent' => $parent
                    ),
                    '#pattern' => '<BEFORE><PREFIX><ELEMENT><LABEL><ERROR><SUFFIX><DESCRIPTION><AFTER>',
                );

                if ($tkey == 0) {
                    if ($level > 0) {
                        $item['#before'] = '<li class="tax-children-of-' . $parent . '"><ul class="wpt-form-set-children wpt-form-set-children-level-' . $level . '" data-level="' . $level . '">' . $item['#before'];
                    } else {
                        $item['#before'] = '<ul class="wpt-form-set wpt-form-set-checkboxes wpt-form-set-checkboxes-' . $this->getName() . '" data-level="0">' . $item['#before'];
                    }
                }
                if ($tkey == ( $level_count - 1 )) {
                    $item['#after'] = '</li>';
                }

                $metaform[] = $item;

                if (isset($childs[$tid])) {
                    $metaform = $this->buildCheckboxes($tid, $childs, $names, $metaform, $level + 1, $tid);
                }
            }
        }

        if (count($metaform)) {
            if ($level == 0) {
                $metaform[count($metaform) - 1]['#after'] .= '</ul>';
            } else {
                $metaform[count($metaform) - 1]['#after'] .= '</ul></li>';
            }
        }

        return $metaform;
    }

}
