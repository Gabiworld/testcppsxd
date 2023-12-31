<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | CPPS</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet">
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <meta charset="UTF-8">
    <meta name="description" content="The best theme for CPPS, ICE. Built by AmusingThrone">
    <meta name="keywords" content="cpps,ice,club penguin,free,theme, cpb">
    <meta name="author" content="AmusingThrone">
    <link rel="stylesheet" href="https://bootswatch.com/paper/bootstrap.min.css">
	<link rel="stylesheet" href="./assets/css/style.css">
	<link rel="stylesheet" type="text/css" href="./sweetalert-master/dist/sweetalert.css">
    <script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="https://www.google.com/recaptcha/api.js"></script>
	<script src="./sweetalert-master/dist/sweetalert.min.js"></script>
	
<body>

    <header>
        <h1><a href="#">CPPS Register</a></h1>
        <nav>
            <li><a href="/">Home</a></li>
            <li><a href="/register/">Register</a></li>
            <li><a href="/play/">Play</a></li>
        </nav>
    </header>
	
<?php

require("CensorWords.php");

use Snipe\BanBuilder\CensorWords;
class Registration extends PDO {
	private $config = [
		'Host' => 'localhost',
		'Database' => 'kitsune',
		'User' => 'root',
		'Pass' => '',
	];
	public function __construct(){
		parent::__construct('mysql:host='. $this->config['Host'] . ';dbname=' . $this->config['Database'], $this->config['User'], $this->config['Pass']);
	}
	public function encryptPassword($password, $md5 = true) {
		if($md5 !== false) {
			$password = md5($password);
		}
		$hash = substr($password, 16, 16) . substr($password, 0, 16);
		return $hash;
	}
	public function sendError($errorType, $message){
		switch($errorType){
			case "success":
				$error = "<div class=\"alert alert-success\">{$message}</div>";
			break;
			case "error":
				$error = "<div class=\"alert alert-danger\">{$message}</div>";
			break;
		}
		return $error;
	}
	public function getLoginHash($password, $staticKey) {
		$hash = $this->encryptPassword($password, false);
		$hash .= $staticKey;
		$hash .= 'Y(02.>\'H}t":E1';
		$hash = $this->encryptPassword($hash);
		$hash = password_hash($hash, PASSWORD_DEFAULT, [ 'cost' => 12 ]);
		return $hash;
	}

    public function addUser($username, $email, $password, $color){
        $hashedPassword = strtoupper(md5($password));
        $staticKey = 'e4a2dbcca10a7246817a83cd';
        $ip = $_SERVER['REMOTE_ADDR'];
        $fancyPassword = $this->getLoginHash($hashedPassword, $staticKey);
        $strQuery = "INSERT INTO penguins (ID, Username, Nickname, Password, Email, RegistrationDate, Color, IP, Inventory, CareInventory, Igloos, Floors, Locations, Furniture, Stamps, Buddies, Ignores, Redeemed, transformation, Tracks, invalidLogins, LoginKey, ConfirmationHash, hackedItem) VALUES (NULL, :username, :username, :password, :email, :TimeDate, :color, :ip, '', '', '1', '', '', '', '7', '', '', '', '', '', '', '', '', '')";
        $insertUser = $this->prepare($strQuery);
        $insertUser->bindValue(":username", $username);
        $insertUser->bindValue(":email", $email);
        $insertUser->bindValue(":TimeDate", time());
        $insertUser->bindValue(":password", $fancyPassword);
        $insertUser->bindValue(":color", $color);
        $insertUser->bindValue(":ip", $ip);
        $insertUser->execute();
        $insertUser->closeCursor();

        $penguinId = $this->lastInsertId();
		$this->addActiveIgloo($penguinId);
		$this->addColors($penguinId);
		return $penguinId;
    }

    private function addColors($penguinId) {
    	$insertStatement = $this->prepare("UPDATE `penguins` SET `Inventory` = '%14%1%2%3%4%5%6%7%8%9%10%11%12%13%15' WHERE ID = :Penguin;");
    	$insertStatement->bindValue(":Penguin", $penguinId);
    	$insertStatement->execute();
    	$insertStatement->closeCursor();
    }

    private function addActiveIgloo($penguinId) {
		$insertStatement = $this->prepare("INSERT INTO `igloos` (`ID`, `Owner`, `Furniture`) VALUES (NULL, :Owner, '');");
		$insertStatement->bindValue(":Owner", $penguinId);
		$insertStatement->execute();
		$insertStatement->closeCursor();
        $iglooId = $this->lastInsertId();
        
        $setActiveIgloo = $this->prepare("UPDATE `penguins` SET `Igloo` = :Igloo WHERE ID = :Penguin;");
        $setActiveIgloo->bindValue(":Igloo", $iglooId);
        $setActiveIgloo->bindValue(":Penguin", $penguinId);
        $setActiveIgloo->execute();
        $setActiveIgloo->closeCursor();
	}

    public function getID($penguinId){
        $strQuery = 'SELECT ID FROM penguins WHERE ID = :ID';
        $getID = $this->prepare($strQuery);
        $getID->bindValue(':ID', $penguinId);
        $getID->execute();
        $idExists = $getID->rowCount() > 0;
        return $idExists; 
    }

	public function usernameExists($username){
		$strQuery = 'SELECT Username FROM penguins WHERE Username = :username';
		$checkUsername = $this->prepare($strQuery);
		$checkUsername->bindValue(':username', $username);
		$checkUsername->execute();
		$usernameExists = $checkUsername->rowCount() > 0;
		return $usernameExists;
	}

	public function EmailExists($email){
		$strQuery = 'SELECT Email FROM penguins WHERE Email = :email';
		$checkEmail = $this->prepare($strQuery);
		$checkEmail->bindValue(':email', $email);
		$checkEmail->execute();
		$emailExists = $checkEmail->rowCount() > 0;
		return $emailExists;
	}
}
$db = new Registration();
if(isset($_POST) && !empty($_POST)){
	if(isset($_POST["username"],$_POST["email"], $_POST["password"], $_POST["repassword"], $_POST["penguinColor"], $_POST["g-recaptcha-response"]) && !empty($_POST["username"]) && !empty($_POST["email"]) && !empty($_POST["password"]) && !empty($_POST["repassword"]) && !empty($_POST["penguinColor"]) && !empty($_POST["g-recaptcha-response"])){
			$strUsername = $_POST["username"];
			$strEmail = $_POST["email"];
			$strPassword = $_POST["password"];
			$strRePassword = $_POST["repassword"];
			$intColor = $_POST["penguinColor"];
			$strCaptcha = $_POST["g-recaptcha-response"];
			$intIP = $_SERVER['REMOTE_ADDR'];
			$strSecretKey = 'yoursecretkey';
            $strResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LflQjYUAAAAAMv2ukgaqNhsGrjUkuXt39C4s8uJ".$strSecretKey."&response=".$strCaptcha."&remoteip=".$intIP);
			$strResponseKeys = json_decode($strResponse, true);
			//$strBadNames = array('fuck','nigger','cunt','twat','shit','bitch','whore','hoe','ass','bum','cock','dick',
			//'clit','pussy','dickhead','nigga','pervert','retard','aids','wanker','gay','niggerfaggot','niggerfaggot69','cancer','lecancer','troll','hacker','marty','memes','pigeon patrol','pigeonpatrol','anonymous','clubpenguinrewritten', 'Georgedoesfuckingtotalshitatfilters');
			$censor = new CensorWords;
			$cen = $censor->censorString($strUsername);
			if($db->usernameExists($strUsername)){
				$error = $db->sendError('error', 'There was an error!');
			}
			//elseif(in_array($strUsername, $strBadNames)){
			elseif(count($cen['matched']) > 0){
				$strBad = "";
				foreach ($cen['matched'] as $bad) {
					$strBad .= "$bad ";
				}
				$error = $db->sendError('error', 'This username is not allowed: ' . $strBad);
			}
			elseif($db->emailExists($strEmail)){
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif(strlen($strUsername) == 0){
				$error = sendError('error', 'You need to provide a name for your penguin.');
			}
			elseif(strlen($strUsername) < 4 || strlen($strUsername) > 21){
				$error = $db->sendError('error', 'Your penguin name is either too short or too long.');
			}
			elseif(preg_match_all("/[0-9]/", $strUsername) > 21){
				$error = $db->sendError('error', 'Your penguin name can only contain 21 numbers.');
			}
			elseif(!preg_match("/[A-z]/i", $strUsername)){
				$error = $db->sendError('error', 'Penguin names must contain at least 1 letter.');
			}
			elseif(preg_match('/[^a-z0-9\s]/i', $strUsername)){
				$error = $db->sendError('error', 'That username is not allowed.');
			}
			elseif(!filter_var($strEmail, FILTER_VALIDATE_EMAIL)){
				$error = $db->sendError('error', 'Your email isn\'t valid.');
			}
			elseif(strlen($strPassword) < 4) {
				$error = $db->sendError('error', "Your password is too short!");
			}
			elseif($strPassword != $strRePassword){
				$error = $db->sendError('error', "Passwords do not match!");
			}
			elseif ($strUsername == "Rockhopper") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Herbert") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Cadence") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Gary") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Rookie") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Sensei") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Franky") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Rocky") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "CeCe") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Brady") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "Kermit") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif ($strUsername == "McKenzie") {
				$error = $db->sendError('error', 'There was an error!');
			}
			elseif(!$strCaptcha){
				$error = $db->sendError('error', 'Please fill out the captcha.');
				die();
			}
			/*elseif(intval($strResponseKeys["success"]) !== 1) {
				$error = $db->sendError('error', 'Invalid validation!');
			}*/
			if(empty($error)){
				$db->addUser($strUsername, $strEmail, $strPassword, $intColor);
				echo '<script language="javascript">';
				echo 'window.onload = function () {';
				echo 'swal("Well done!", "You have successfully registered!", "success")';
				echo '};';
				echo '</script>';
			}
	} else
		{
			$error = $db->sendError('error', "Please complete all the fields.");
		}
}
?>

    <section class="ice">
        <div class="background-image"></div>
<div>
<center>
<div class="register-form">
<form method="POST" action="">
	<?php
	if(isset($error))
	{
		echo $error;
	}
	?>
	<div class="form-group">
	  <input type="text" name="username" class="form-control" placeholder="Penguin Name" min="4" maxlength="21" />
	</div>
<script>
$('input').keyup(function(){
    str = $(this).val()
    str = str.replace(/\s/g,'')
    $(this).val(str)
});
</script>
	<div class="form-group">
		<input type="email" name="email" class="form-control" placeholder="Email" id="inputDefault" maxlength="40" />

	</div>
	<div class="form-group">
		<input type="password" name="password" class="form-control" placeholder="Password" id="inputDefault" maxlength="1000" />
	</div>
	<div class="form-group">
		<input type="password" name="repassword" class="form-control" placeholder="Repeat Password" id="inputDefault" maxlength="1000" />
	</div>
  <div class="form-group">
      </div>
      <div style="margin-left: -160px;" class="g-recaptcha" data-sitekey="6LflQjYUAAAAACUSGyJQo9WFp5-jUUJ_ol7P9LPt"></div><br>
  <input type="submit" class="btn btn-success" value="Sign Up" style="margin-left: -342px;width: 111px;margin-top: -16px;"></input>
  <input type="hidden" value="1" id="penguinColorInput" name="penguinColor" />
</form>

	<div class="foo blue" id="c1" style="opacity: 1;" onclick="changeImage('./colors/1.png')"></div>
	<div class="foo green" id="c2" style="opacity: 0.5;" onclick="changeImage('./colors/2.png')"></div>
	<div class="foo pink" id="c3" style="opacity: 0.5;" onclick="changeImage('./colors/3.png')"></div>
	<div class="foo black" id="c4" style="opacity: 0.5;" onclick="changeImage('./colors/4.png')"></div>
	<div class="foo red" id="c5" style="opacity: 0.5;" onclick="changeImage('./colors/5.png')"></div>
	<div class="foo orange" id="c6" style="opacity: 0.5;" onclick="changeImage('./colors/6.png')"></div>
	<div class="foo yellow" id="c7" style="opacity: 0.5;" onclick="changeImage('./colors/7.png')"></div>
	<br>
	<div class="foo purple" id="c8" style="opacity: 0.5;" onclick="changeImage('./colors/8.png')"></div>
	<div class="foo brown" id="c9" style="opacity: 0.5;" onclick="changeImage('./colors/9.png')"></div>
	<div class="foo lightpink" id="c10" style="opacity: 0.5;" onclick="changeImage('./colors/10.png')"></div>
	<div class="foo darkgreen" id="c11" style="opacity: 0.5;" onclick="changeImage('./colors/11.png')"></div>
	<div class="foo lightblue" id="c12" style="opacity: 0.5;" onclick="changeImage('./colors/12.png')"></div>
	<div class="foo lightgreen" id="c13" style="opacity: 0.5;" onclick="changeImage('./colors/13.png')"></div>
	<div class="foo grey" id="c14" style="opacity: 0.5;" onclick="changeImage('./colors/14.png')"></div>

	<img id="imgDisp" alt="" src="./colors/1.png" style="margin-left:603px;margin-top: -397px;"/>

</div>

<script>
function changeImage(imgName)
{
    image = document.getElementById('imgDisp');
    image.src = imgName;
    var colorId = imgName.replace("./colors/", "");
    colorId = colorId.replace(".png", "");
    document.getElementById("penguinColorInput").value = colorId;
    for (i = 1; i < 15; i++) {
        document.getElementById("c" + i).style.opacity = 0.5;
    }
    document.getElementById("c" + colorId).style.opacity = 1;
}
</script>

</center>
  
</section>
</html>
</center>
    <footer>
        <ul>
            <li><a href="#" target="_blank"><i class="fa fa-rss-square"></i></a></li>
            <li><a href="https://twitter.com/amusingthrone" target="_blank"><i class="fa fa-twitter-square"></i></a></li>
            <li><a href="https://github.com/amusingthrone" target="_blank"><i class="fa fa-github-square"></i></a></li>
        </ul>
        <p>Theme by <a href="http://amusingthrone.com" target="_blank">AmusingThrone</a>.</p>
        <p>&copy; 2017 CPPS.</p>
    </footer>  
	</body>
</html>