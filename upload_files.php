<?php

require_once 'access.class.php';

$user = new flexibleAccess();
// must be logged in to upload files
if (!$user->is_loaded()) {
  header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php?location='.$_SERVER['PHP_SELF']);
}

?>
<html>
<head><title>Upload Files to Filing Cabinet</title></head>
<body>
<form enctype="multipart/form-data" action="uploader.php" method="POST">
<!-- <input type="hidden" name="MAX_FILE_SIZE" value="10485760" /> -->
<table>
  <tr>
    <th></th>
    <th>Files to upload</th>
    <th>Comma separated list of labels</th>
    <th>Sequence</th>
  </tr>
<?php
$amount = 10;
for ($i = 1; $i <= $amount; ++$i):
?>
  <tr>
    <td>File <?php echo $i ?></td>
    <td><input name='uploadedfile_<?php echo $i ?>' type='file' /></td>
    <td><input name='labels_<?php echo $i ?>' type='text' /><br /></td>
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
