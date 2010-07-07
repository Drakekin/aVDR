<?php
    /**
     * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
     * This is a function like imagecopymerge but it handle alpha channel well!!!
     **/
    function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);
         
        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        $opacity = 100 - $opacity;
         
        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    }
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
