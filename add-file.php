<?php

require_once 'environment.php';
require_once 'access.class.php';
require_once 'common.php';

/* need to upgrade to PHP 5.3 to use this
function get_mime_type($filename)
{
    $finfo = finfo_open(FILEINFO_MIME);
    $mimetype = finfo_file($finfo, $filename);
    finfo_close($finfo);
    return $mimetype;
}
*/

// connect to the database
$db_link = mysql_connect(localhost, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);
// must be logged in to upload files
if (!($user->is_loaded() and $user->is_active())) {
  header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$rel_web_path.'/listview.php');
}


$file_path = $upload_path/$_POST['filename'];

// the type of the file may not have been detected properly, so detect again with a better method
$mime_type = mime_content_type($file_path);

// split and trim the labels
$labels = parseLabels($_POST['labels']);

// use semaphore locking
while (file_exists('.lock_cabinet') && ((time() - filemtime('.lock_cabinet')) < 1800)) {
    sleep(50);
}
@touch('.lock_cabinet');

  // get id of row we're about to insert
  $qShowStatus = "SHOW TABLE STATUS LIKE 'Files'";
  $qShowStatusResult = mysql_query($qShowStatus) or die ( 'Query failed: ' . mysql_error() . '<br />' . $qShowStatus );
  $row = mysql_fetch_assoc($qShowStatusResult);
  $fileID = $row['Auto_increment'];


  // make the file name correspond to the id in the database
  $target_path =  'file_' . $fileID;

  if(move_uploaded_file($file_path, $target_path)) {
    // archive the file
    echo 'archiver says: ' . exec("7za a -mx=9 -y $archive_path $target_path", $output, $return_value) . '<br />';
    if ($return_value == 0) {
        // insert the file data into the database
        $qFilename = addslashes($_POST['filename']);
        if ($_POST['rename'])
        {
            $qFilename = addslashes($_POST['rename']);
        }
        mysql_query(
          "INSERT INTO Files (filename, type, size, owner, permissions)
          VALUES('".$qFilename."', '".addslashes($mime_type)."', ". sprintf("%u", filesize($file_path)) .",
          '".addslashes($user->get_property('username'))."', ".(($_POST['public'] == 'on')?'1':'0').")"
        ); 
        // now safe to unlock because the next increment will give a new file id.
        @unlink('.lock_cabinet');

        // insert the label data into the database
        foreach ($labels as $label) {
          if ($label != '') {
            mysql_query(
              "INSERT INTO Labels (file_id, label_name)
              VALUES(".$fileID.", '".addslashes($label)."')"
            );
          }
        }
        
        // record sequence data
        if (isset($_POST['sequence'])) {
            mysql_query(
                "UPDATE Files
                SET next_file_id = " . $fileID . "
                WHERE id = " . $_POST['sequence']
            );
        }
        
        // remove the file from the upload location
        exec('rm ' . $target_path);
    } else {
        @unlink('.lock_cabinet');
        echo 'There was an error archiving the file: ' . $_POST['filename'];
    }
  } else {
    @unlink('.lock_cabinet');
    echo 'There was an error uploading the file: ' . $_POST['filename'];
  }

mysql_close();
?>
