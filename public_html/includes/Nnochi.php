<?php 

/* 
 * Nnochi v1.0.0
 * 
 * Simple template engine that replace variables in the template
 * This library for now can only replace variable. 
 * Insert value into template using this tag fromat: <h1><%= page_title %></h1>
 * This fromat is not yet supported <h1><%= user.age %></h1>
 * 
 * Author: Attamah Celestine
 */

class Nnochi {
     // render the file
     public function render($file_path, $pass_variables = []) {
         $string_file; // hold the readed file as a string
         $patterns = [];
         $replacements = [];

         // check if file exist
         if (file_exists($file_path)) {
             // read the file as a string
             $string_file = file_get_contents($file_path);

             // create pattern and replacement
             foreach ($pass_variables as $key => $value) {
                 $patterns[] = '/<%=\s?' . $key . '\s?%>/';
                 $replacements[] = $value;
             }

             // apply replacement
             $string_file = preg_replace($patterns, $replacements, $string_file);

             // replace varibles that are not defined
             return preg_replace('/<%=\s?.*\s?%>/', 'undefined', $string_file);

         } else {
             throw new Exception("File can't be found.");
         }
     }
 }

?>