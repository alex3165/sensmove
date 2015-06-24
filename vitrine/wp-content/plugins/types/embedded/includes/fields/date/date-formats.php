<?php
/*
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * Set date formats
 * TODO Document this
 */
global $supported_date_formats, $supported_date_formats_text;
$supported_date_formats = array('F j, Y', //December 23, 2011
    'Y/m/d', // 2011/12/23
    'm/d/Y', // 12/23/2011
    'd/m/Y', // 23/22/2011
    'd/m/y', // 23/22/11
);

$supported_date_formats_text = array('F j, Y' => 'Month dd, yyyy',
    'Y/m/d' => 'yyyy/mm/dd',
    'm/d/Y' => 'mm/dd/yyyy',
    'd/m/Y' => 'dd/mm/yyyy',
    'd/m/y' => 'dd/mm/yy',
);

