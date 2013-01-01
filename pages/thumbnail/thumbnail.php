<?php
if (isset($_REQUEST['thumb'])) {
    include_once(TP_SOURCEPATH . '/easyphpthumbnail/easyphpthumbnail.class.php');
    // Your full path to the images
    $dir = str_replace(chr(92),chr(47),getcwd()) . '/gfx/';
    // Create the thumbnail
    $thumb = new easyphpthumbnail;
    $thumb -> Thumbsize = 80;
    $thumb -> Createthumb($dir . basename($_REQUEST['thumb']));
}
?>