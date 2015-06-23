<?php

/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.taxonomy.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */

require_once 'class.textfield.php';

class WPToolset_Field_Taxonomy extends WPToolset_Field_Textfield
{
    public $values = "";
    public $objValues;

    public function init() {
        $this->objValues = array();

        $terms = wp_get_post_terms(CredForm::$current_postid, $this->getName(), array("fields" => "all"));
        $i = 0;
        foreach ($terms as $n => $term) {
            $this->values .= ($i==0) ? $term->slug : ",".$term->slug;
            $this->objValues[$term->slug] = $term;
            $i++;
        }
		
        add_action( 'wp_footer', array($this, 'javascript_autocompleter') );
    }

    public function javascript_autocompleter() {
            echo '<script type="text/javascript">
                    jQuery(document).ready(function() {
                            initTaxonomies("'. $this->values .'", "'.$this->getName().'", "'.WPTOOLSET_FORMS_RELPATH.'", "'.$this->_nameField.'");
                    });
            </script>';
    }

    public function metaform()
    {
        $use_bootstrap = array_key_exists( 'use_bootstrap', $this->_data ) && $this->_data['use_bootstrap'];
        $attributes = $this->getAttr();
		$taxonomy = $this->getName();
		
        $metaform = array();
        $metaform[] = array(
            '#type' => 'hidden',
            '#title' => '',
            '#description' => '',
            '#name' => $taxonomy,
            '#value' => $this->values,
            '#attributes' => array(
                
            ),
            '#validate' => $this->getValidationData(),
        );
        $metaform[] = array(
            '#type' => 'textfield',
            '#title' => '',
            '#description' => '',
            '#name' => "tmp_".$taxonomy,
            '#value' => $this->getValue(),
            '#attributes' => array(
				'data-taxonomy' => $taxonomy,
				'data-taxtype' => 'flat',
				'class' => $use_bootstrap ? 'inline wpt-new-taxonomy-title js-wpt-new-taxonomy-title' : 'wpt-new-taxonomy-title js-wpt-new-taxonomy-title',
            ),
            '#validate' => $this->getValidationData(),
            '#before' => $use_bootstrap ? '<div class="form-group">' : '',
        );

        /**
         * add button
         */
        $metaform[] = array(
            '#type' => 'button',
            '#title' => '',
            '#description' => '',
            '#name' => "new_tax_button_".$taxonomy,
            '#value' => apply_filters('toolset_button_add_text', esc_attr( $attributes['add_text'] )),
            '#attributes' => array(
				'class' => $use_bootstrap ? 'btn btn-default wpt-taxonomy-add-new js-wpt-taxonomy-add-new' : 'wpt-taxonomy-add-new js-wpt-taxonomy-add-new',
				'data-taxonomy' => $taxonomy,
            ),

            '#validate' => $this->getValidationData(),
            '#after' => $use_bootstrap ? '</div>' : '',
        );

		$before = sprintf(
			'<div class="tagchecklist tagchecklist-%s"></div>',
			$this->getName()
		);
		$after = $this->getMostPopularTerms();
		if ( $use_bootstrap ) {
			$before = '<div class="form-group">'.$before;
			$after .= '</div>';
		}
		$show = isset($attributes['show_popular']) && $attributes['show_popular'] == 'true';
		/**
		 * show popular button
		 */
		$metaform[] = array(
			'#type' => 'button',
			'#title' => '',
			'#description' => '',
			'#name' => "sh_".$taxonomy,
			'#value' => apply_filters('toolset_button_show_popular_text', esc_attr( $attributes['show_popular_text'] )),
			'#attributes' => array(
				'class' => $use_bootstrap ? 'btn btn-default popular wpt-taxonomy-popular-show-hide js-wpt-taxonomy-popular-show-hide' : 'popular wpt-taxonomy-popular-show-hide js-wpt-taxonomy-popular-show-hide',
				'data-taxonomy' => $this->getName(),
				'data-show-popular-text' => apply_filters('toolset_button_show_popular_text', esc_attr( $attributes['show_popular_text'] )),
				'data-hide-popular-text' => apply_filters('toolset_button_hide_popular_text', esc_attr( $attributes['hide_popular_text'] )),
				'data-after-selector' => 'js-show-popular-after',
				'style' => $show ? '' : 'display:none;'
			),
			'#before' => $before,
			'#after' => $after,
		);
		
        $this->set_metaform($metaform);
        return $metaform;
    }

    private function buildTerms($obj_terms) {
        $tax_terms=array();
        foreach ($obj_terms as $term)
        {
            $tax_terms[]=array(
                'name'=>$term->name,
                'count'=>$term->count,
                'parent'=>$term->parent,
                'term_taxonomy_id'=>$term->term_taxonomy_id,
                'term_id'=>$term->term_id
            );
        }
        return $tax_terms;
    }

    private function buildCheckboxes($index, &$childs, &$names)
    {
        if (isset($childs[$index]))
        {
            foreach ($childs[$index] as $tid)
            {
                $name = $names[$tid];
                ?>
                <div style='position:relative;line-height:0.9em;margin:2px 0;<?php if ($tid!=0) echo 'margin-left:15px'; ?>' class='myzebra-taxonomy-hierarchical-checkbox'>
                    <label class='myzebra-style-label'><input type='checkbox' name='<?php echo $name; ?>' value='<?php echo $tid; ?>' <?php if (isset($values[$tid])) echo 'checked="checked"'; ?> /><span class="myzebra-checkbox-replace"></span>
                        <span class='myzebra-checkbox-label-span' style='position:relative;font-size:12px;display:inline-block;margin:0;padding:0;margin-left:15px'><?php echo $names[$tid]; ?></span></label>
                    <?php
                    if (isset($childs[$tid]))
                        echo $this->buildCheckboxes($tid,$childs,$names);
                    ?>
                </div>
            <?php
            }
        }
    }

    public function getMostPopularTerms()
    {
        $use_bootstrap = array_key_exists( 'use_bootstrap', $this->_data ) && $this->_data['use_bootstrap'];
		
		$term_args = array(
            'number' => 10,
            'orderby' => 'count',
            'order' => 'DESC'
        );
        $terms = get_terms(array($this->getName()), $term_args);
        if ( empty( $terms ) ) {
            return '';
        }
        $max = -1;
        $min = PHP_INT_MAX;
        foreach($terms as $term) {
            if ( $term->count < $min ) {
                $min = $term->count;
            }
            if ( $term->count > $max ) {
                $max = $term->count;
            }
        }
        $add_sizes = $max > $min;
		
		if ( $use_bootstrap ) {
			$content = sprintf(
				'<div class="shmpt-%s form-group wpt-taxonomy-show-popular-list js-show-popular-after" style="display:none">',
				$this->getName()
			);
		} else {
			$content = sprintf(
				'<div class="shmpt-%s wpt-taxonomy-show-popular-list js-show-popular-after" style="display:none">',
				$this->getName()
			);
		}

        foreach($terms as $term) {
            $style = '';
            if ( $add_sizes ) {
                $font_size = ( ( $term->count - $min ) * 10 ) / ( $max - $min ) + 8;
                $style = sprintf( ' style="font-size:%fem;"', $font_size/10 );
            }
            $clases = array('wpt-taxonomy-popular-add', 'js-wpt-taxonomy-popular-add');
            $clases[] = 'tax-'.$term->slug;
            $clases[] = 'taxonomy-'.$this->getName().'-'.$term->term_id;
            $content .= sprintf(
                '<a href="#" class="%s" data-slug="%s" data-name="%s" data-taxonomy="%s"%s>%s</a> ',
                implode(' ', $clases ),
                $term->slug,
                $term->name,
                $this->getName(),
                $style,
                $term->name
            );
        }
        $content .= "</div>";
        return $content;
    }

}
