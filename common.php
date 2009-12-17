<?php
// this file hold functions for common use

// parse the string of the MIME type stored in the db to the file name of the icon for that type
function mimeFilename ($mimeType)
{
    //replace slashes with dashes and add a prefix and postfix
    return 'gnome-mime-' . str_replace('/', '-', $mimeType) . '.png';
}

?>
