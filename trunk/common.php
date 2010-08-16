<?php
// this file hold functions for common use

// parse the string of the MIME type stored in the db to the file name of the icon for that type
function mimeFilename ($mimeType)
{
    //replace slashes with dashes and add a prefix and postfix
    return 'gnome-mime-' . str_replace('/', '-', $mimeType) . '.png';
}

// generate page head
function head($title = 'Filing Cabinet', $scripts = array())
{
    return headA($scripts) . headB($title);
}

function headA($scripts)
{
    $out = "
<html>
<head>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"filingcabinet-default.css\" />
";
    foreach ($scripts as $type => $script)
    {
        $out .= "<script type=\"$type\" src=\"$script\"></script>\n";
    }
    return $out;
}

function headB($title)
{
    return "
    <title>$title</title>
</head>
<body>
";
}

// generate page foot
function foot()
{
    return "
</body>
</html>
";
}

// generate tab menufunction head ($title = 'Filing Cabinet')
function tabMenu($logged = True, $username = '')
{
    if ($logged) {
        $ref = 'login.php?logout=1';
        $txt = 'Logout';
    } else {
        $ref = 'login.php';
        $txt = 'Login';
    }
    return "
<div id='tabs'>
  <ul>
    <li></li>
    <li><a href=\"listview.php\"><span>List Files</span></a></li>
    <li><a href=\"upload_files.php\"><span>Upload</span></a></li>
    <li><a href=\"$ref\"><span>$txt</span></a></li>
    <li id=\"username\">$username</li>
  </ul>
</div>
";
}

// translate comma-seperated string into array of labels
function parseLabels($str)
{
    $labels = explode(',', $str);
    foreach ($labels as $id => $label) {
        $labels[$id] = strtolower(trim($label));
    }
    return $labels;
}

/**
 * Returns a human readable filesize
 *
 * @author      wesman20 (php.net)
 * @author      Jonas John
 * @version     0.3
 * @link        http://www.jonasjohn.de/snippets/php/readable-filesize.htm
 */
function HumanReadableFilesize($size) {
 
    // Adapted from: http://www.php.net/manual/en/function.filesize.php
 
    $mod = 1024;
 
    $units = explode(' ','B KiB MiB GiB TiB PiB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
 
    return round($size, 2) . ' ' . $units[$i];
}

?>
