<?php

include_once 'forms.php';

class Enlimbo_Control_Forms extends Enlimbo_Forms_Wpcf {
	
	private $_urlParam = '';
	     
	public function isSubmitted($id = '')    {
		if(!empty($id)) {
			$this->_urlParam = $id;
		}
		
		if(empty($this->_urlParam)) {
     		return false;
     	}
     	
		return isset($_GET[$this->_urlParam]);
    }
    
    public function renderElements($elements) {
    	if(isset($elements['field'])) {
    		$this->_urlParam = $elements['field']['#name'];
    	}
    	
    	return parent::renderElements($elements);
    }
}
