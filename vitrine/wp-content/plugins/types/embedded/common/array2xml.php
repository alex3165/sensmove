<?php

if (!class_exists('ICL_Array2XML')) {

    /**
     * Converts array to XML
     */
    class ICL_Array2XML
    {
    
        var $text;
        var $arrays, $keys, $node_flag, $depth, $xml_parser;
    
        function array2xml($array, $root) {
            $this->depth = 1;
            $this->text = "<?xml version=\"1.0\" encoding=\""
                    . get_option('blog_charset'). "\"?>\r\n<$root>\r\n";
            $this->text .= $this->array_transform($array);
            $this->text .="</$root>";
            return $this->text;
        }
    
        function array_transform($array) {
            $output = '';
            $indent = str_repeat(' ', $this->depth * 4);
            $child_key = false;
            if (isset($array['__key'])) {
                $child_key = $array['__key'];
                unset($array['__key']);
            }
            foreach ($array as $key => $value) {
                $key = str_replace(' ', '___032___', $key); // encode spaces
                if (!is_array($value)) {
                    if (empty($key)) {
                        continue;
                    }
                    $key = $child_key ? $child_key : $key;
                    $output .= $indent . "<$key>" . htmlspecialchars($value, ENT_QUOTES) . "</$key>\r\n";
                } else {
                    $this->depth++;
                    $key = $child_key ? $child_key : $key;
                    $output_temp = $this->array_transform($value);
                    if (!empty($output_temp)) {
                        $output .= $indent . "<$key>\r\n";
                        $output .= $output_temp;
                        $output .= $indent . "</$key>\r\n";
                    }
                    $this->depth--;
                }
            }
            return $output;
        }
    
    }
}
