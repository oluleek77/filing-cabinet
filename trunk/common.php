<?php
// this file holds functions and values for common use

//delimiter for label filters when passed by URL
$crumbDelimiter = ',';

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
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head profile=\"http://www.w3.org/2005/10/profile\">
    <meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />
    <link rel=\"stylesheet\" type=\"text/css\" href=\"css/filingcabinet-default.css\" />
    <link rel=\"icon\" type=\"image/png\" href=\"images/favicon.png\" />
";
    foreach ($scripts as $script => $type)
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
<h1><img src=\"images/heading.png\" alt=\"Filing Cabinet Heading\" /></h1>
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

// generate file listing as table row
function fileListing($DBrow, $downloads = False)
{
    $out = "<tr>\n";
    // show MIME icon
    $out .= '<td><img src="images/mimetypes/16/' . mimeFilename($DBrow['type']) . "\" alt=\"{$DBrow['type']}\" /></td>\n";
    // filename linked fo fileview
    $out .= "<td><a href=\"fileview.php?id={$DBrow['id']}\">" . htmlspecialchars($DBrow['filename']) . "</a></td>\n";
    if ($downloads)
    {
        // show number of downloads
        $out .= "<td>({$DBrow['downloads']} downloads)</td>\n";
    }
    // provide a download button
    $out .= "<td><a href=\"download?id={$DBrow['id']}\"><img src=\"images/download-32.png\" alt=\"Download\" /></a></td>\n";
    // show filesize
    $out .= '<td>' . HumanReadableFilesize($DBrow['size']) . "</td>\n";
    $out .= "</tr>\n";
    return $out;
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
