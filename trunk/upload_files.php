<?php

require_once 'access.class.php';

$user = new flexibleAccess();
// must be logged in to upload files
if (!($user->is_loaded() and $user->is_active())) {
  header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$rel_web_path.'/login.php?location='.urlencode('upload_files.php'));
}

?>
<html>
<head><title>Upload Files to Filing Cabinet</title></head>
<body>
<p>View <a href="listview.php">files in cabinet.</a></p>
<p><?php echo $user->get_property('username') ?> <a href="login.php?logout=1">Logout</a></p>
<form enctype="multipart/form-data" action="uploader.php" method="POST">
<!-- <input type="hidden" name="MAX_FILE_SIZE" value="10485760" /> -->
<table>
  <tr>
    <th></th>
    <th>Files to upload</th>
    <th>Comma separated list of labels</th>
    <th>Allow public access</th>
    <th>Sequence</th>
  </tr>
<?php
$amount = 10;
for ($i = 1; $i <= $amount; ++$i):
?>
  <tr>
    <td>File <?php echo $i ?></td>
    <td><input name='uploadedfile_<?php echo $i ?>' type='file' /></td>
    <td><input name='labels_<?php echo $i ?>' type='text' /></td>
    <td><input name='public_<?php echo $i ?>' type='checkbox' /></td>
    <td>Followed in sequence by <select name='sequence_<?php echo $i ?>'>
    <?php
    for ($j = 1; $j <= $amount; ++$j){
      if ($j == $i) {
        echo "<option value='$j' selected>[No sequence]</option>\n";
      } else {
        echo "<option value='$j'>File $j</option>\n";
      }
    }
    ?>
    </select></td>
  </tr>
<?php
endfor;
?>
</table> <br />
<input type='hidden' name='amount' value='<?php echo $amount ?>' />
<input type='submit' value='Upload Files' />
</form>
</body>
</html>