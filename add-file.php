<?php

require_once 'environment.php';
require_once 'access.class.php';
require_once 'common.php';
// library by Chris Jean http://chrisjean.com/2009/02/14/generating-mime-type-in-php-is-not-magic/ that gracefully falls back to less and less satisfactory methods for detecting a file's mime type
require_once 'mime_type_lib.php'; 

// connect to the database
$db_link = mysql_connect($db_host, $db_user, $db_password);
@mysql_select_db($database) or die( 'Unable to select database');

$user = new flexibleAccess($db_link);
// must be logged in to upload files
if (!($user->is_loaded() and $user->is_active())) {
  header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$rel_web_path.'/listview.php');
}
/*
if (isset($_POST['filename'])) {
echo $_POST['filename'];
} else {
echo "no file";
}
*/
$file_path = "$upload_path/{$_POST['filename']}";

// the type of the file may not have been detected properly, so detect again with a better method
$mime_type = get_file_mime_type($file_path);

// make it not writable so that filesize() doesn't fall over
exec("chmod -w $file_path");

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

  if(rename($file_path, $target_path)) {
    // archive the file
    $archiver_message = exec("$archiver a -mx=9 -y $archive_path $target_path", $output, $return_value);
    if ($return_value == 0) {
        // insert the file data into the database
        $qFilename = addslashes($_POST['filename']);
        if ($_POST['rename'])
        {
            $qFilename = addslashes($_POST['rename']);
        }
        mysql_query(
          "INSERT INTO Files (filename, type, size, owner, permissions)
          VALUES('".$qFilename."', '".addslashes($mime_type)."', ". $_POST['filesize'] .",
          '".addslashes($user->get_property('username'))."', ".($_POST['pub']).")"
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
        echo $_POST['filename'] . ' added. ID: <span class="file_id">' . $fileID . '</span> size: ' . $_POST['filesize'];
    } else {
        @unlink('.lock_cabinet');
        echo 'There was an error archiving the file: ' . $_POST['filename'] . '<br />Archiver says: ' . $archiver_message;
    }
  } else {
    @unlink('.lock_cabinet');
    echo 'There was an error uploading the file: ' . $_POST['filename'];
  }

mysql_close();
?>
