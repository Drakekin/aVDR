<?php
    function trimit(&$value) {
    	$value = trim($value, ",");
    }
    function alphanumericAndSpace( $string ) {
        $temp = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
        $temp = strtolower($temp);
        return trim($temp);
    }
    function gettotaldiceserved() {
        $sql = "SELECT highest FROM rolls";
        $result = mysql_query($sql) or die(mysql_error());
        return mysql_num_rows($result);
    }
?>
