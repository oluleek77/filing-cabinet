<?
/*
Basic login example with php user class
http://phpUserClass.com
*/
require_once 'access.class.php';
$user = new flexibleAccess();
if ( $_GET['logout'] == 1 ) 
	$user->logout('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
if ( !$user->is_loaded() )
{
	//Login stuff:
	if ( isset($_POST['uname']) && isset($_POST['pwd'])){
	  if ( !$user->login($_POST['uname'],$_POST['pwd'],$_POST['remember'] )){// we don't have to use addslashes as access.class does the job
	    echo 'Wrong username and/or password';
	  }else{
	    //user is now loaded
	    header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	  }
	}
	echo '<h1>Login</h1>
	<p><form method="post" action="'.$_SERVER['PHP_SELF'].'" />
	 username: <input type="text" name="uname" /><br /><br />
	 password: <input type="password" name="pwd" /><br /><br />
	 <input type="submit" value="login" />
	</form>
	</p>';
}else{
  // User is loaded, so send them on to where they want to go.
  if isset($_GET['location']) {
    header('Location: http://'.$_SERVER['HTTP_HOST'].$_GET['location']);
  } else {
    // if no location is specified go to list view.
    header('Location: http://'.$_SERVER['HTTP_HOST'].'listview.php');
  }
  //echo '<a href="'.$_SERVER['PHP_SELF'].'?logout=1">logout</a>';
}
?>
