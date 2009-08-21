<?
/*
Adding a user to a table with email activation.
IMPORTANT:
should validate the data first, but don't have to addslashes() as the class does this operation.
*/

require_once 'environment.php';
require_once 'access.class.php';
$user = new flexibleAccess();

if (!empty($_GET['activate'])){
	//This is the actual activation. User got the email and clicked on the special link we gave him/her
	$hash = $user->escape($_GET['activate']);
	$res = $user->query("SELECT `{$user->tbFields['active']}` FROM `{$user->dbTable}` WHERE `activationHash` = '$hash' LIMIT 1",__LINE__);
	if ( $rec = mysql_fetch_array($res) ){
		if ( $rec[0] == 1 )
			echo 'Your account is already activated. <a href="login.php">Login</a>';
		else{
			//Activate the account:
			if ($user->query("UPDATE `{$user->dbTable}` SET `{$user->tbFields['active']}` = 1 WHERE `activationHash` = '$hash' LIMIT 1", __LINE__))
				echo 'Account activated. You may <a href="login.php">login</a> now';
			else
				echo 'Unexpected error. Please contact an administrator';
		}
	}else{
		echo 'User account does not exists';
	}
}

if (!empty($_POST['username'])){
  // Register user:
  
  // Get an activation hash and mail it to the user
  $hash = $user->randomPass(100);
  while( mysql_num_rows($user->query("SELECT * FROM `{$user->dbTable}` WHERE `activationHash` = '$hash' LIMIT 1"))==1)//We need a unique hash
  	  $hash = $user->randomPass(100);
  //Adding the user. The logic is simple. We need to provide an associative array, where keys are the field names and values are the values :)
  $data = array(
  	'username' => $_POST['username'],
  	'email' => $_POST['email'],
  	'password' => $_POST['pwd'],
  	'activationHash' => $hash,
  	'active' => 0
  );
  $rows_inserted = $user->insertUser($data); //The method returns the userID of the new user or 0 if the user is not added
  if (!$rows_inserted > 0)
  	echo '<p>Could not register user.</p>';
  else {
  	//Here is the mail that the user will get:
	$email = 'Activate your user account by visiting : '. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] .'?activate='.$hash;
	echo $_POST['email'];
	$activation_sent = mail($_POST['email'], 'Filing Cabinet: Activate your account', $email);
	if (!$activation_sent) {
	    echo '<p>User registered, but failed to send activation instructions to email address!</p>';
	    echo '<p>'.$email.'</p>';
	} else {
	    echo '<p>User registered. Instructions on how to activate your account have been sent to your email address.</p>';
  	}
  	mail($admin_email, '[Filing Cabinet] new user', 'A new user has registered. '.$_POST['username'].' --- '.$_POST['email']);
  }
}


echo '<h1>Register</h1>
	<p><form method="post" action="'.$_SERVER['PHP_SELF'].'" />
	 username: <input type="text" name="username" /><br /><br />
	 password: <input type="password" name="pwd" /><br /><br />
	 email: <input type="text" name="email" /><br /><br />
	 <input type="submit" value="Register user" />
	</form>
	</p>';

?>
