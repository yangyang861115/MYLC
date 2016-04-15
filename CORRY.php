<?
// loadCore.php
// Version Release 3 (June 13, 2012)
// this is the common load file to load up basic parameters all the time

session_start(); // this starts the session
date_default_timezone_set('GMT');
error_reporting( E_ALL | E_STRICT );
ini_set('display_errors','On');

if (is_dir("../private/")) $loadDir = "../private"; 
elseif (is_dir("../../private")) $loadDir = "../../private";
else die ("Error: Private Directory is Missing (lC.14)");  

if (isset($loadSimple)) ; // take no action
elseif (isset($coreOnly)) ; // do not load these extra files
else {
	#include ($loadDir."/shared/mtwj/makeMime.php");
	#include ($loadDir."/shared/mtwj/html2text.php");
	#include ($loadDir."/shared/mtwj/validateEmail.php");
	#require_once("phpcreditcard.php"); // this pulls the file on demand
} // if (!$loadSimple) {

// #+#+#+##+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+##+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+##
// #+#+#+##+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+##+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+##
// #+#+#+##+#+#+#+#+#+#+#+#+#+#+#+#+#+ START OF STANDARD CORE FUNCTIONS +#+#+#+#+#+#+#+#+#+#+#+#+#+#+##+#+#

##############################################################
#####  Database and initial landing functions      ###########
##############################################################

function getConfigs($mySrvr) { // this looks for and finds the configs file
	global $conFigs, $loadDir;
	if (file_exists($loadDir."/refnc/coreFigs.php")) require_once($loadDir."/refnc/coreFigs.php");
	else bugger ("W", "load.15", "Configuration file in <b>[".getcwd()."]</b> on server ($mySrvr || ".$_SERVER['SERVER_ADDR'].") and load ($loadDir) is missing or PERMISSIONS incorrect...");
} // end function getConfigs()

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    elseif(isset($_SERVER['HTTP_X_FORWARDED'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    elseif(isset($_SERVER['HTTP_FORWARDED'])) $ipaddress = $_SERVER['HTTP_FORWARDED'];
    elseif(isset($_SERVER['REMOTE_ADDR'])) $ipaddress = $_SERVER['REMOTE_ADDR'];
    else $ipaddress = 'UNKNOWN';
    return $ipaddress;
} // function get_client_ip() {

function toSess($mySess, $all=false) {
	global $conFigs;
	if ($all) $idRst = "id = '-1', "; else $idRst = "";
	$doSess = str_replace("'", "\'",serialize($mySess));
	if (isset($mySess)) doQuery("UPDATE seszions SET ".$idRst." mySess = '$doSess' WHERE phpsez = '".session_id()."'", "update", "", "poplog");
} // function toSess($mySess) {

function bugger($what, $where, $rawInput="", $stop=false) {
	#####################################
	### UPDATED BUGGER - January 2015 ###
	#####################################

  	global $siteKey, $siteSecure, $id, $loadDir, $conFigs, $rmtk, $dbServer, $ref, $mySess;
  	if (!isset($rmtk)) $rmtk = "";
  	if (!strstr($rmtk, ".") && $rmtk != "") { $rmtk = "inValid[$rmtk]"; ticket("bugger.95 - Invalid rmtk [$rmtk]", "Invalid RMTK value.... Auto stop occurred"); }
  	if ($what != "A" && $what != "V" && $what != "W" && $what != "U") {
		$msg = "With where [$where] and input [$rawInput] must define action.";
		$sub = "lC.52 - Unknown Type [$what]";
		if ($_SERVER['REMOTE_ADDR'] != $rmtk) ticket($sub,$msg);
		else bugger ("W", $sub,$msg);
	} // if ($what != "A" && $what != "V" && $what != "W" && $what != "U") {

	if ($_SERVER['REMOTE_ADDR'] != $rmtk && $what == "A") return "";

	$bugMsg = "";
	if ($rawInput == "") $input = ""; // do nothing
	elseif ($what == "A" || $what == "L" || $what == "V") $input = $rawInput; // do nothing
	elseif (is_array($rawInput)) $input = '<font class="bodycopy">'.implode("||", $rawInput).'</font>';
	else $input = '<font class="bodycopy">'.$rawInput.'</font>';
  
  	if ($what == "A") $clr = "green";
  	elseif ($what == "V") $clr = "#990000";
  	elseif ($what == "W" || $what == "U") $clr = "red"; 
  	else $clr = "blue";
  
  	$saysR = '<b><font color="'.$clr.'">Error Referenced ||</font></b> <i>'.$where.'</i><b><font color="'.$clr.'">||</font></b>: '; // 
	if ($what == "Q") $bugMsg .= $saysR.'Query is ['.$input.']<hr size=1>';
	elseif ($what == "A" && $input == "") { $bugMsg .= $saysR.'The input is <font color="990000"><b>EMPTY!</b></font><hr size=3 noshade color="990000">'; }
	elseif ($what == "A" && is_array($input)) { 
  		if ($_SERVER['REMOTE_ADDR'] === $rmtk) { print '<br />'.$saysR.'The array values are:<br>'; print_r($input); print '<hr size=10 noshade>'; }
		else $bugMsg .= $saysR.'The array values are:<br>'.rollIt($input).'<hr size=10 noshade>';
	  } // elseif ($what == "A" && is_array($input)) { 
	elseif ($what == "A" && is_object($input)) { 
  		if ($_SERVER['REMOTE_ADDR'] === $rmtk) { print '<br />'.$saysR.'The <b><font color="#995555">OBJECT</font></b> values are:<br>'; print_r($input); print '<hr color="#995555" size=10 noshade>'; }
  		else $bugMsg .= $saysR.'The <b><font color="#995555">OBJECT</font></b> values are:<br>'.rollIt($input).'<hr color="#995555" size=10 noshade>'; 
	  } // elseif ($what == "A" && is_array($input)) { 
	elseif ($what == "A") { $bugMsg .= $saysR.'The <b>STRING</b> (<i>not array</i>) value is ['.$input.'] <hr size=3 noshade>'; }
	elseif ($what == "W") $bugMsg .= $saysR.$input.' <a href="'.$siteSecure.'/help.php?a=contact&s='.$siteKey.'" target="new">Contact Webmaster</a>'; 
	elseif ($what == "U") $bugMsg .= '<big><red>INVALID URL PARAMETERS</red></big> at ['.$where.']<br />There is a problem with the URL you are using '.
  										'<br />HOST: <b>'.$_SERVER['HTTP_HOST'].'</b><br />SCRIPT: <b>'.$_SERVER['SCRIPT_NAME'].'</b><br />URI: <b>'.$_SERVER['REQUEST_URI'].'</b><br />'.
  										'The following specific error occurred: '.$input;
	elseif ($what == "V") {
		if ($_SERVER['REMOTE_ADDR'] === $rmtk) { 
			print '<h2>Current Defined Functions</h2>';
			$dummy = get_defined_functions();
			$rtn = ""; 
			foreach ($dummy['user'] as $key => $val) $rtn .= $val.' - ';
			print rtrim($rtn, ' - ').'<hr size=5 color=sienna />';
	
	  		$vars = $input;
			$skipArray = array('GLOBALS', '_COOKIE', '_SESSION', '_FILES', '_SERVER', '_GET',
						   'statusPaint', 'conFigs', 'siteInfo', 'profile', 'html2text_elements');
			$showSkip = "";
			foreach ($skipArray as $line) {
				unset($vars[$line]);
				$showSkip .= $line.'||';
			} // foreach ($skipArray as $line) {
		
			print '<hr size=10 color="red"><b>Variable Dump at ['.$saysR.']</b><hr size=10 color="red">';
			print '&nbsp;&nbsp;Skipping Variables: [ '.rtrim($showSkip, "||").' ]<br>';
			print '<div align="left">';
			foreach ($vars as $key => $row) { 
				print '<font color="red"><b>'.$key.'</b></font>: ';
				print_r($row);
				print '<hr size=1 width=200>';
			} // foreach ($vars as $key => $row) { 
			print '</div>';
	  	} else die ('COm.136 Variable dump not availabile from IP ['.$_SERVER['REMOTE_ADDR'].']');
      } // end if $what == "V"
	else die ('fGnr.774: bugger for ('.$what.') not defined...');

  	// this inputs into the logger
	if ($what == "U") ; // no action for "U" 
	elseif ($_SERVER['REMOTE_ADDR'] != $rmtk) {   // only include this if it is not me!!!
 		$tme = date("Y-m-d h:m");
	  	// this records the error
		if ($id > 0) {
			$dummy = doQuery("SELECT CONCAT(firstname, ' ', lastname) AS nameline, email FROM popLog WHERE id = $id", "single", "", "poplog");
			if ($dummy['nameline'] > "") $nameline = $dummy['nameline']; 
			if ($dummy['email'] > "") $outmail = $dummy['email']; 
		} // if ($id > 0) {
		if (!isset($nameline)) $nameline = $_SERVER['HTTP_HOST']; 
		if (!isset($outmail)) $outmail = "support@intre.org";

		$query = "INSERT INTO buggers (`timestmp`, `ip`, `id`, `site`, `scrpt`, `getvars`, `errMsg`) VALUES 
  			  ('$tme', '".$_SERVER['REMOTE_ADDR']."', '$id', '".$_SERVER['HTTP_HOST']."', '".$_SERVER['SCRIPT_FILENAME']."', '".str_replace("'", "\'", $_SERVER['QUERY_STRING'])."', '".str_replace("'", "\'", strip_tags($bugMsg))."')";
  		doQuery($query, "insert", "", "poplog");
	
		$item = str_replace("=", " is ", $_SERVER['QUERY_STRING']);
		$item = explode("&", $item);
	
		$localized = "Host: [".$_SERVER['HTTP_HOST']."] and URL [".$_SERVER['PHP_SELF']."||";
		if (is_array($item)) foreach ($item as $key => $val) $localized .= $key.' ['.$val.']|-|';
		if (is_array($ref)) {
			$nte = "Ref is array: ";
			foreach ($ref as $nKey => $nVal) $nte .= $nKey.'='.$nVal.' || ';
			$ref = rtrim($nte, ' || ');
		} // if (is_array($ref)) {
		$localized .= "] dbSrv [$dbServer] siteKey [$siteKey] and ID [$id] and ref is [$ref] javaOn [".$mySess->javaOn."] IP [".$_SERVER['REMOTE_ADDR']."] ";
		$localized .= "Script: [".$_SERVER['SCRIPT_FILENAME']." and QueryString [".$_SERVER['QUERY_STRING']."]";
		$pVals = "";
		if (is_array($_POST)) { 
			foreach ($_POST as $key => $val) {
				if ($key == "ccNumb" || $key == "gurCC") $val = ccHide($val, "short");
				$pVals .= $key.' ['.$val.']|>|'; 
			} // foreach ($_POST as $key => $val) {
		} // if (is_array($_POST)) {
		quikSendHTML ("Support", "bugger.support@cruglobal.freshdesk.com", $nameline, $outmail, "", "", 
						strip_tags($saysR), strip_tags($bugMsg)." ".$localized."<hr />".$pVals, "text"); // this sends out the email 
	} // if ($_SERVER['REMOTE_ADDR'] == $rmtk) {   

  	if ($_SERVER['REMOTE_ADDR'] === $rmtk) { 
		print '<br />Paul only sees this: ('.$rmtk.'/'.$_SERVER['REMOTE_ADDR'].') <hr />'; 
  		print $bugMsg;
		print '<hr />';
		if ($stop) die("Original Bugger Request Included a forced STOP!");
  	} else { // when not my IP address
		if ($outmail == "support@intre.org" || $what == "U") $outcopy = ""; 
		else $outcopy = "A copy of the support ticket was sent to: <b>".$outmail."</b>.";
  		print '<h1>There was a problem completing your request...</h1>';
		
		if ($what == "U") print $bugMsg;
		else print 
  			'<h3>Technical Issue: '.$saysR.'</h3>'.
  			'<p>An error occurred while processing your request. This could be caused by a programming error, inconsistent or incomplete stored data related to you or your account, or a transient infrastructure problem.</p>'.
			'<p>A support ticket has been created and our Tier 3 Team has been notified. '.$outcopy.'</p>'.
			'<p>You may be contacted by Tier 3 support and it really helps us if you can remember what you did just prior to this error. So if you would, please take a moment to jot yourself a note
			or better yet, send us a note by clicking <a href="http://cruglobal.freshdesk.com/support/tickets/new">here</a> while it is fresh on your mind. Thanks.</p><p>&nbsp;</p>'.
			'<p>You may be able to retry this operation. Click the back button on your browser to return to the previous page and try again.</p>'.
			'<p>If you want to start over at the default page for this system, <a href="'.$siteSecure.'/index.php">click here</a>. If this does not work, log out completely, log back in, and try the operation again. </p>'.
			'<p>You may wish to copy/paste some of your input into a temporary text file, so that you will not have to retype it all when you come back.</p>';
		
		print '<h2>Continued Problems</h2>'.
 	   		'<p>If you continue to have problems, please <a href="'.$siteSecure.'/help.php?a=contact&s='.$siteKey.'" target="new">Contact Webmaster</a></p>'.
	   		'<p>We apologize for the inconvenience</p>';
	   
		print '<h2>Site Administrators</h2>'.
	   		'<p>If you are a site administrator, you may want to run a integrity check from your attendee listing. That will often surface any problems with invalid data.</p>'.
	   		'<p>Additionally, you may consult the help center or create a technical support ticket if need be.</p>';	
	   
	   	print 'You may now return to the site <a href="index.php">home page</a>';
  	} // else when not my IP address
 	if ($what == "W" || $what == "V" || $what == "U") exit; // stop the script 	   
 } // bugger($what, $where, $input) {

function mydo($val, $die=true) { 
	global $rmtk, $id;
	if ($_SERVER['REMOTE_ADDR'] != $rmtk) return ""; // don't show for others
	elseif ($die) die ("PMK ONLY from [$rmtk]<br />".$val); 
	else print '<center>'.$val.'</center>'; 
} // function mydie($val) { 

function db_connect($db="") {
  global $conFigs, $loadDir;
  
  if ($db == "") {
	  $dbServer = $conFigs->dbServer;
	  $dbUsername = $conFigs->dbUsername;
	  $dbPassword = $conFigs->dbPassword;
	  $dbDatabase = $conFigs->dbDatabase;
    } // if ($db == "") {
  elseif (substr($db,0,4) == "DBO>") { // special case when all that is different is the Database name
  	  $dbServer = $conFigs->dbServer;
	  $dbUsername = $conFigs->dbUsername;
	  $dbPassword = $conFigs->dbPassword;
	  $dbDatabase = substr($db, 4);
    } // elseif (substr($db,0,4) == "DBO:") {
  elseif ($db > "") require($loadDir."/Core/altDB.php");
  else bugger ("W", "cOm.22", "Actions for DB type of [$db] are not defined.");

  $conn = mysql_connect($dbServer,$dbUsername, $dbPassword);
  if($conn==false) bugger("W", "cMn.254 - DB Connect Fail", "Unable to connect to database [$dbDatabase / $dbUsername]");
  else $db_selected = mysql_select_db($dbDatabase, $conn);
  disconnectMySQL(); // close the DB connection
  
  // make foo the current db
  if (!$db_selected) {
  	$msg = 'Server is ['.$_SERVER['SERVER_NAME'].'] Cannot use <b>'.$dbDatabase.'</b> : ' . mysql_error();
	bugger("W", "cMn.260 - No DB Selected", $msg);  
    } // if (!$db_selected) {
  else return $conn;
} // function db_ connect()

function disconnectMySQL(){
	@mysql_close();
} // function disconnectMySQL(){
	
function doClose($idAct=true, $clrIt = false) {
	global $mySess,$id,$recno;
	// ( [myIP] => 73.168.94.205 [created] => 1421177156 [id] => 490 [lang] => 00 [lesson] => [javaOn] => yes [siteKey] => 0 )
	if ($idAct) $id = -1; 
	if ($clrIt) { 
		unset($mySess, $id, $recno); 
		doQuery("DELETE FROM seszions WHERE phpsez = '".session_id()."' LIMIT 1", "delete", "", "poplog");
		toSess($mySess, true); 
		// #FORK: once "cookies" are created, you need to get this to remove the cookie as well...
		header ('location: index.php'); 
		exit; 
	} // if ($clrIt) { 
	if (isset($mySess->guest)) $guest = $mySess->guest;
	$mySess = (object) array("myIP" => $mySess->myIP, "created" => $mySess->created, "id" => $id, "lang" => $mySess->lang, "javaOn" => $mySess->javaOn, 
							 "navlog" => $mySess->navlog, "contnu" => $mySess->contnu, "sbansw" => $mySess->sbansw, "navttl" => $mySess->navttl);
	if (isset($guest)) $mySess->guest = $guest;
	$dummy = getTT("ttlLogin", "lC.72"); 
	toSess($mySess, true);
} // end function doClose

function sr($str) {
	return str_replace("'", "\'", $str);
} // function sr

function doDb ($query, $type="multi", $defs="", $vals="", $db="", $showQ="") {
	global $loadDir, $conFigs, $recno, $id, $rmtk;
	// defs are of the following kind
	/* i = corresponding variable has type integer
	   d = corresponding variable has type double
	   s = corresponding variable has type string
	   b = corresponding variable is a blob and will be sent in packets */
  
	// this creates the DB connection and errs out if failure
	if ($db == "") {
		DEFINE ('DB_HOST', $conFigs->dbServer);
		DEFINE ('DB_USER', $conFigs->dbUsername);
	  	DEFINE ('DB_PASSWORD', $conFigs->dbPassword);
	  	DEFINE ('DB_NAME', $conFigs->dbDatabase);
      } // if ($db == "") {
	elseif ($db == "poplog") { // special case when all that is different is the Database name
  		DEFINE ('DB_HOST', $conFigs->dbServer_poplog);
		DEFINE ('DB_USER', $conFigs->dbUsername_poplog);
		DEFINE ('DB_PASSWORD', $conFigs->dbPassword_poplog);
		DEFINE ('DB_NAME', $conFigs->dbDatabase_poplog);
	  } // if ($db == "poplog") {
	else bugger ("W", "lC.281", "doBind Actions for DB type of [$db] are not defined.");
	$dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (mysqli_connect_errno()) bugger("W", "lC.286 - DB Connect Fail [".mysqli_connect_error()."]", "Unable to connect to database [$dbDatabase / $dbUsername]");
	
	// preliminary value checks (for integrity)
	$rtn = "";
	if ($type == "") $type = "multi";
	elseif (substr($type,0,6) == "utf8::") { mysqli_set_charset($dbc,"utf8"); $type = substr($type,6); }

	// if using query show... it prints out the query
	if ($showQ == "yes") die ('lC.289:  <font color="red">Plain old <b><i>yes</i></b> as a search term is not good enough.  You must put in some locator (<i>e.g. rEx.123</i>)</font>');
	elseif ($showQ > "") print 'lC.290: With reference of (<font color="red">'.$showQ.'</font>) for type ['.$type.'] the query is ['.$query.']<br>'; 

	// this is where the query is performed (and options are checked) 
	if ($type == "ALL" || $type == "exist" || $type == "fields" || $type == "tables" || $type == "guid") bugger("W", "lC.300 - definition needed for [$type]", "This is the first use of this function... please get this set up.");
	#elseif ($type == "multi" || $type == "keyFirst") $result = mysqli_query($dbc, $query, MYSQLI_USE_RESULT);
	// NOTE IF YOU EVER GET TO A SUPER BIG RESULT, YOU"ll NEED TO FIGURE OUT HOW TO USE THE PREVIOUS STATEMENT. For now, next works fine.. (see FAILfish)
	elseif ($type == "multi" || $type == "keyFirst") $result = mysqli_query($dbc, $query);
	elseif ($type == "pair" || $type == "single" || $type == "iso" || $type == "simple") $result = mysqli_query($dbc, $query);
	elseif ($type == "insert") {
		
		$link = $dbc;
#$stmt = mysqli_prepare("INSERT INTO polls (`age`, `gender`, `cntry`, `reason`, `make`) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($link, "INSERT INTO polls VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'ssss', $age, $gender, $country, $reason);

$age = 'u25';
$gender = 'm';
$country = "az";
$reason = "This is a big test";

/* execute prepared statement */
mysqli_stmt_execute($stmt);

printf("%d Row inserted.\n", mysqli_stmt_affected_rows($stmt));

/* close statement and connection */
mysqli_stmt_close($stmt);

mysqli_close($link);


		die ("At stop point 318");
		
		if (!is_array($vals)) bugger("W", "lC.299 Invalid Insert Array", "The value of vals must be an array. Yours is [$vals]");
		else $iCnt = count($vals);
		
		$names = $data = "";
		$V = array();
		$cnt = 0;
		foreach ($vals as $key => $row) {
			$cnt++;
			$names .= "`".$key."`, ";
			$data .= "?, ";
			$V[$cnt] = $row;
		} // foreach ($vals as $key => $row) {
		#mydo("Value of sdata is [$data]", false);
		#bugger ("A", "v vals", $V);			
		#$query .= " (".rtrim($names, "`, ").") VALUES (".rtrim($data, "?, ").")";
		$query .= " (".rtrim($names, ", ").") VALUES (".substr($data, 0, -2).")";
		mydo("At 329: Query values are [$query]", false);
		bugger ("A", "dbc at 330", $dbc);
		
		#$stmt = mysqli_prepare($dbc, $query);
		
		$stmt = mysqli_stmt_init($dbc);
#		bugger ("A", "statement.301", $stmt);
		switch ($iCnt) {
			case 1 : mysqli_stmt_bind_param($stmt, $defs, $V[1]);	break;
			case 2 : mysqli_stmt_bind_param($stmt, $defs, $V[1], $V[2]); break;
			case 3 : mysqli_stmt_bind_param($stmt, $defs, $V[1], $V[2], $V[3]);	break;
			case 4 : mysqli_stmt_bind_param($stmt, $defs, $V[1], $V[2], $V[3], $V[4]); break;
			case 5 : mysqli_stmt_bind_param($stmt, $defs, $V[1], $V[2], $V[3], $V[4], $V[5]); break;
			default : bugger("W", "lC.320 - Add options for [$iCnt]", "You need to add more actions for an input statement with these values.");			
		} // switch ($iCnt) {
		mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);
		die ("Arrected Rows are: [".$affected_rows."]");
		mydo( "AT 321: The affected rows are [$affected_rows]");

		
/*

function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}		

		
		
//add as many as you need 

$var_types[] = 's'; 
$var_types[] = 's'; 
//add as many as you need 

// 
// Now generate the proper string we need 
// to pass to the function 
// 
$type_string = ''; 

foreach( $var_types as $type ) { 
     $type_string .= $type; 
} 

// 
// Call the function using call_user_func_array 
// 
$params = $vars; 
array_unshift($params, $defs); //set our type string as the first parameter 

$res = call_user_func_array( 'mysqli_stmt_bind_param', $params );  

bugger ("A", "res.327", $res); mydo("At stop point 327");
		

		
		
$res = call_user_func_array(array($stmt, 'bind_param'), refValues($params));

		
        $stmt = mysqli_prepare($dbc, $query);
		$time = mktime();
		$dte = date("Y-m-d");
		$kind = 'toast';
        mysqli_stmt_bind_param($stmt, 'iss', $time, $kind, $dte);
        mysqli_stmt_execute($stmt);
        
        $affected_rows = mysqli_stmt_affected_rows($stmt);
	mydo( "CANNED TEST: The affected rows are [$affected_rows]");
*/     
		
/*		$type = "left";
		$args = array("arg1","arg2","10");
		array_unshift($args,$type);

		$display = call_user_func_array('setFunction', $args);
		bugger ("A", "display.316", $display);
		
		
		foreach($param as $par) {
		      $args.='$param['.intval($i).'],';
		      ++$i; 
	      }
		$args=substr($args,0,-1);
        
		
		
		
		function my_wrapper($vals) {
			$param = func_get_args();
		  	bugger ("A", "param.314", $param);
			$args = implode(",", $param[0]);
			bugger ("A", "args.318", $args); mydo ("At argument stop point");
			
			eval("mysqli_stmt_bind_param($this->getStmt(),$args);");
		}
		
		my_wrapper($vals);
		
#            $obj = $this->stmt;
 #           eval('mysqli_stmt_bind_param($obj, '.$args.');'); // this is ok
	*/	
		
		function refValues($arr){ 
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+ 
    { 
        $refs = array(); 
        foreach($arr as $key => $value) 
            $refs[$key] = &$arr[$key]; 
        return $refs; 
    } 
    return $arr; 
} 

		
		
		if (!is_array($vals)) bugger("W", "lC.299 Invalid Insert Array", "The value of vals must be an array. Yours is [$vals]");
		bugger ("A", "vals.300 with defs [$defs]", $vals);
		$stmt = mysqli_prepare($dbc, $query);
		bugger ("A", "statement.301", $stmt);
		#$display = 
		call_user_func_array(array($stmt, "mysqli_stmt_bind_param"),refValues($vals));



		
		#array_unshift($vals,$stmt,$defs);
		#$display = call_user_func_array('mysqli_stmt_bind_param', $vals);

		#mysqli_stmt_bind_param($stmt, $defs, $var);						
		mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);
		die ("Arrected Rows are: [".$affected_rows."]");
		mydo( "AT 321: The affected rows are [$affected_rows]");
		$cnt = 0;
		foreach ($vals as $var) {
			$cnt++;
			bugger ("A", "At 304 with Count [$cnt]", $var);
			mysqli_stmt_bind_param($stmt, $defs, $var);						
			mysqli_stmt_execute($stmt);
            $affected_rows = mysqli_stmt_affected_rows($stmt);
			mydo( "The affected rows are [$affected_rows]");
        
	        #mysqli_stmt_bind_param($stmt, 'ssiissssss', $or_name, $or_type, $or_parent, 
    	    #	$or_contact, $or_website,$or_alias, $or_scope, 
        	#	$or_country, $or_address,$or_status);

		} // foreach ($vals as $val) {
		mydo("At end of insert test");		
	  } // elseif ($type == "insert") {
	else bugger("W", "lC.301 - Undefined type [$type]", "You need to get action for this type defined."); // old platform checks... get rid of when needed.
	
	if (!$result) {
  		$bugMsg= "";
	  	if ($type == "insert" && mysqli_errno($conn) == "1062") {
			$bugMsg = '<hr size="10" color="plum"><b>DUPLICATE ENTRY</b><br /><font color="red">Your mysql action has resulted in a duplicate entry for the following query</font><br />';
			$bugMsg .= $query.'<br /><hr size="10" color="plum">';
		  } // 	if ($type == "insert" && mysql_errno($conn) == "1062") {
		else { // all other errors
	  		$bugMsg = '<hr size="10" color="gold"><b>Query Syntax Error</b><br />';
			$bugMsg .='<font color="red"><b>You have an ERROR in your query SYNTAX!</font></b><br /><br />';
			$bugMsg .= "MySQLi Err Numb (".mysqli_errno($dbc).")<br /><font color='#6633FF'>(".mysqli_error($dbc).")<br>".$_SERVER['REQUEST_URI']."</font><br />".
				  "Query is [<font color='#009900'>".$query."</font>]<hr size=10 color=gold>";
		} // end of other errors
		if ($_SERVER['REMOTE_ADDR'] != $rmtk) ticket("MySqli Error#372 for type [$type]", $bugMsg); 
	  	if ($_SERVER['REMOTE_ADDR'] === $rmtk) print 'lC.316 BugMessage is ['.$bugMsg.']<br />';
		else print '<head><style type="text/css">#limit { max-width: 500px; } </style></head><body><div id="limit">
			<h1>WARNING: Unrecoverable Error</h1><p>The script encountered a Major error while attempting to access 
			the Database. You may click the link below to try to recover to the main home page. A ticket has been created
			and our Tier 3 Tech team has been notified.</p>
			<p>Could you please take a moment and send an email to: <a href="mailto:support@cruglobal.freshdesk.com">support@cruglobal.freshdesk.com</a>
			and describe exactly what you were doing when this error occurred. Put <b>Error@ 372</b> in the subject line. 
			That way we can let you know when this error is fixed.</p>
			<p>We apologize for the inconvenience. Thanks for your patience.</p></div>
			<p>&nbsp;</p><p>To attemp a recovery, please <a href="'.$conFigs->siteSecure.'/index.php?a=clear">Click Here</a></p></body>'; 
		exit; // stop script from firing
	  } // if (!$result) {

	elseif ($result == "") {
	  if (($type == "insert" && !strstr($query, "INSERT")) ||
  		  ($type == "delete" && !strstr($query, "DELETE")) ||	
  		  ($type == "empty" && !strstr($query, "EMPTY")) ||	
	  	  ($type == "replace" && !strstr($query, "REPLACE")) ||	
	  	  ($type == "select" && !strstr($query, "SELECT")) ||	
	  	  ($type == "update" && !strstr($query, "UPDATE")) ||
	  	  ($type == "change" && !strstr($query, "UPDATE"))
		 ) bugger("W", "fDb.57", "Type of [".$type."] mismatch for query [".$query."]");
	  return ('ERROR: ['.$query.']'); // if not an error, then return QUERY statement
	  } // end if ($result == "") {
	else $rcnt = $result->num_rows; # $rcnt = mysqli_num_rows($result);  FAILfish == this is where the row count doesn't work when you use the USE_RESULT feature
		
	if ($rcnt === 0) $rtn = ""; // this is when empty
	elseif ($type == "iso" || $type == "single") {
		if ($rcnt === 0) $rtn = false; #""; 
		elseif ($type == "iso" && $rcnt == 1 && $result->field_count == 1) $rtn = current(mysqli_fetch_assoc($result)); 
		elseif ($type == "single" && $rcnt == 1) $rtn = mysqli_fetch_assoc($result); 
		else $rtn = 'ERROR: ['.$type.'] Error: Row or Field count wrong. ROWS ['.$rcnt.'] and  FIELDS ['.$result->field_count.'] when query is ['.$query.']';
	  } // if ($type == "iso") {
	elseif ($type == "simple") { // this returns an array of type array(item1, item2, item3);
	    $summary = array();
   		while (($row=mysqli_fetch_array($result)) !=false) {
	    	$summary[] = $row[key($row)];
    	} // end of while statement
		$rtn = $summary;
  	  } // end if ($type == "simple")
	elseif (numbIt($rcnt, "pos")) {	
		$rtn = "";
	    $summary = array();
    	while (($row=mysqli_fetch_assoc($result)) !=false) {
			if ($type == "keyFirst") { // takes first element and makes it a key
				$keyID = array_shift($row);
				$summary[$keyID] = $row;
			  } // if ($type == "keyFirst") {
			elseif ($type == "asKey") { // returns just the keys
				$keyID = array_shift($row);
				$summary[$keyID] = "";
			  } // if ($type == "asKey") {
			elseif ($type == "pair") { // creats key/value pair
				$keyID = array_shift($row);
				$summary[$keyID] = $row[key($row)];
			  } // if ($type == "pair"  
		    elseif ($type == "multi") $summary[] = ($row); // normal return
			else bugger("W", "lC.378 - Invalid type [$type]", "You must define return value for this type.");
	    } // end of while statement
        $rtn = $summary;  // this is simply to transfer things back with the array...
	  } // elseif (numbIt($rcnt, "pos")) {	
	else $rtn = 'ERROR: ['.$query.']'; //

	// Free and close resources
	if (isset($stmt)) mysqli_stmt_close($stmt);
	if (isset($result)) mysqli_free_result($result); 
	mysqli_close($dbc); // close connection
	return $rtn; 
#	if ($type == "multi") 
/*
	switch ($type) {
    	case "add" : $rtn = true; break; // this is when you add to a non-Auto Incremented field
	  	case "insert" : 
			$newID = mysql_insert_id($conn);
			if ($newID > 0) $rtn = $newID;
			elseif ($newID == "0") { // if comes back zero, means it is a non-incrementable field and we need to do the return value now
		 		outBind();
				return "";
			  } // elseif ($newID == "0") {
			elseif ($newID == false) $rtn = false;
			else bugger("W", "cOm.119", "Action for newID of [$newID] is not defined.");
			break;
		case "delete" : $rtn = true; break;
		case "replace" : $rtn = true; break;
	    case "empty" : $rtn = true; break;
		case "select" : $rtn = true; break;
		case "update" : // cannot use mysql_affected_rows because if no change is done, it returns zero... which implies the update didn't work
			 			$rtn = true; break;
	    case "change" : $rtn =  mysql_affected_rows(); break;
	    case "count" : $rtn = mysqli_num_rows($result); break;
  } // switch ($type)
*/
} // function doDb($query, $vals) {	

function doQuery ($query, $type="multi", $showQ="", $db="") {
  global $conFigs, $recno, $id, $rmtk;
  $conn = db_connect($db);
  $rtn = "";

  if ($type == "") $type = "multi";
  elseif (substr($type,0,6) == "utf8::") { mysql_set_charset('utf8',$conn); $type = substr($type,6); }

  if ($type == "UPDATE" || $type == "INSERT" || $type == "DELETE" || $type == "REPLACE" || $type == "SELECT" || $type == "CHANGE") bugger("W", "lC.246 Invalid doQuery type [$type]", "Proper lower case type value is needed."); 
  if (strstr($query, "INSERT") && strstr($query, "UPDATE") && strstr($query, "ON DUPLICATE KEY") && $type == "insert") ; // this is okaybugger("W", "cOm.249", "For replace/update query type must be 'replace' but it is <b>[$type]</b>: <i>$query</i>.");	
  elseif (strstr($query, "REPLACE") && $type != "replace") bugger("W", "cOm.248", "For replace query type must be 'replace' but it is <b>[$type]</b>: <i>$query</i>.");	
  elseif (strstr($query, "UPDATE") && $type != "update") bugger("W", "cOm.251", "For update query type must be 'update' but it is <b>[$type]</b>: <i>$query</i>.");	
  elseif (strstr($query, "INSERT") && $type != "insert") bugger("W", "cOm.252", "For insert query type must be 'insert' but it is <b>[$type]</b>: <i>$query</i>.");	
  elseif (strstr($query, "UPDATE") && !strstr($query, "WHERE")) bugger("W", "cOm.253", "For update query type must have a WHERE statement</b>: <i>$query</i>.");	

  #if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36') {  die ('221: opps: Update problem with query ['.$query.']'); }
  if (!$conn) return "Could not connect to database server - please try later.";
  if ($showQ == "yes") die ('fDb.54:  <font color="red">Plain old <b><i>yes</i></b> as a search term is not good enough.  You must put in some locator (<i>e.g. rEx.123</i>)</font>');
  elseif ($showQ > "") print 'fDb.42. With reference of (<font color="red">'.$showQ.'</font>) for type ['.$type.'] the query is ['.$query.']<br>'; 
  
  if (substr($showQ,0,3) == "die") die ('fDb.45: stopper point');

  if ($type == "ALL") { // for ALL, you put the table name in the QUERY field...
  	$query = "SELECT * FROM $query";
	$type = "multi";
  } // if ($type == "ALL")
  
  if ($type == "exist") { // table name is in the query field
	$table = $query;
	$dummy = mysql_query('DESC '.$table, $conn);
	if ($dummy > "") return true; else return false;
	exit;
  } //  if ($type == "exist") {
  
  if ($type == "fields") { 
  	// unique use of $query... it is the table name
	#if ($_SERVER['REMOTE_ADDR'] == $rmtk) { print 'cOm.69: Query is ['.$query.'] and type is ['.$type.']<br />'; }
	$table = $query;
	$dummy = mysql_query('DESC '.$table, $conn);  
	if ($_SERVER['REMOTE_ADDR'] === $rmtk) { bugger ("A", "dummy168", $dummy); }          
	if ($dummy == "") return "ERROR: Table [".$table."] does not exist";
	else { // when table is fine...
		if ($db == "") $db = $conFigs->dbDatabase;
		elseif (substr($db,0,4) == "DBO>") $db = substr($db,4); // trims if a DBO type
		#die ('db is ['.$db.'] and table is ['.$table.']'); 
		if (!$result = mysql_list_fields($db, $table, $conn)) bugger("W", "cOm.65", "Field Name Query for table [".$query."] failed.");
	  	if (mysql_num_fields($result)) {
		  $dummy = array();
		  for ($index=0; $index < mysql_num_fields($result); ++$index) {
			   $dummy[] = mysql_field_name($result, $index);
	       } // end for ($index=0...
		  disconnectMySQL(); 
	 	  $summary = array();
		  foreach ($dummy as $key=>$value) $summary[$value] = "";
	      return $summary;  // this is simply to transfer things back with the array...
		} // end if (mysql_num_fields($result)>0) {
	} // else when table is fine	
  } // end if ($type == "fields")  

  if ($type == "tables") {
    // unique use of query...  it is the name of the database
	// EXAMPLE OF USE: if (!in_array($srchTable, doQuery("admin", "tables"))) return "";
#  	$result = mysql_list_tables($conFigs->localHost.'_'.$query, $conn);
  	$result = mysql_list_tables($query, $conn);
  	if (mysql_num_rows($result) == 0) return 0; // this is when empty
    $summary = array();
	while (list ($temp) = mysql_fetch_array ($result)) {
    	$summary[] = $temp;
	} // while (list ($temp) = mysql_fetch_array ($result)) {
	disconnectMySQL();
	return $summary; // returns the tables
  } // if ($type == "tables") {


  if ($type == "guid") {
	 $result = mysql_fetch_assoc(mysql_query("SELECT UUID() AS GUID", $conn));
	 disconnectMySQL();
	 return $result["GUID"];
  } // if ($type == "guid") {
	  
	  
  $result = mysql_query($query, $conn);
  if (mysql_errno($conn) > 0) {
  	$bugMsg= "";
  	if ($type == "insert" && mysql_errno($conn) == "1062") {
		$bugMsg = '<hr size="10" color="plum"><b>DUPLICATE ENTRY</b><br /><font color="red">Your mysql action has resulted in a duplicate entry for the following query</font><br />';
		$bugMsg .= $query.'<br /><hr size="10" color="plum">';
	  } // 	if ($type == "insert" && mysql_errno($conn) == "1062") {
	else { // all other errors
	  	$bugMsg = '<hr size="10" color="gold"><b>Query Syntax Error</b><br />';
		$bugMsg .='<font color="red"><b>You have an ERROR in your query SYNTAX!</font></b><br /><br />';
		$bugMsg .= "My SQL Error Number (".mysql_errno($conn).")<br /><font color='#6633FF'>(".mysql_error($conn).")<br>".$_SERVER['REQUEST_URI']."</font><br />".
			  "Query is [<font color='#009900'>".$query."</font>]<hr size=10 color=gold>";
	} // end of other errors
	if ($_SERVER['REMOTE_ADDR'] != $rmtk) ticket("MySql Error# 372 for type [$type]", $bugMsg); 
  	if ($_SERVER['REMOTE_ADDR'] === $rmtk) print 'BugMessage is ['.$bugMsg.']<br />';
	else print '
	<head>
	    <style type="text/css">#limit { max-width: 500px; } </style>
	</head>
	<body>
    	<div id="limit">
		<h1>WARNING: Unrecoverable Error</h1><p>The script encountered a Major error while attempting to access 
		the Database. You may click the link below to try to recover to the main home page. A ticket has been created
		and our Tier 3 Tech team has been notified.</p>
		<p>Could you please take a moment and send an email to: <a href="mailto:support@cruglobal.freshdesk.com">support@cruglobal.freshdesk.com</a>
		and describe exactly what you were doing when this error occurred. Put <b>Error@ 372</b> in the subject line. 
		That way we can let you know when this error is fixed.</p>
		<p>We apologize for the inconvenience. Thanks for your patience.</p></div>
		<p>&nbsp;</p><p>To attemp a recovery, please <a href="'.$conFigs->siteSecure.'/index.php?a=clear">Click Here</a></p>
	</body>';
	exit; // stop script from firing
  } // if (mysql_errno($conn) > 0) {
  
  if ($result == "") {
	  if (($type == "insert" && !strstr($query, "INSERT")) ||
  		  ($type == "delete" && !strstr($query, "DELETE")) ||	
  		  ($type == "empty" && !strstr($query, "EMPTY")) ||	
	  	  ($type == "replace" && !strstr($query, "REPLACE")) ||	
	  	  ($type == "select" && !strstr($query, "SELECT")) ||	
	  	  ($type == "update" && !strstr($query, "UPDATE")) ||
	  	  ($type == "change" && !strstr($query, "UPDATE"))
		  ) bugger("W", "fDb.57", "Type of [".$type."] mismatch for query [".$query."]");
	  return ('ERROR: ['.$query.']'); // error in query
  } // end if ($result == "") {

  switch ($type) {
    case "add" : $rtn = true; break; // this is when you add to a non-Auto Incremented field
  	case "insert" : 
		$newID = mysql_insert_id($conn);
		if ($newID > 0) $rtn = $newID;
		elseif ($newID == "0") { // if comes back zero, means it is a non-incrementable field and we need to do the return value now
	 		disconnectMySQL();
			return "";
		  } // elseif ($newID == "0") {
		elseif ($newID == false) $rtn = false;
		else bugger("W", "cOm.119", "Action for newID of [$newID] is not defined.");
		break;
	case "delete" : $rtn = true; break;
	case "replace" : $rtn = true; break;
    case "empty" : $rtn = true; break;
	case "select" : $rtn = true; break;
	case "update" : // cannot use mysql_affected_rows because if no change is done, it returns zero... which implies the update didn't work
		 			$rtn = true; break;
    case "change" : $rtn =  mysql_affected_rows(); break;
    case "count" : $rtn = mysql_num_rows($result); break;
    case "single" :
	     if (mysql_num_rows($result)>1) $rtn =  "ERROR: Multiple Rows returned for expected single result";
		 else $rtn = mysql_fetch_assoc($result); 
		 break;
	case "iso" : // used when you are sending only one element to get		
		 $rowCnt = mysql_num_rows($result);	
		 if ($rowCnt == 0) $rtn = ""; // if blank, then whoa...
	     elseif ($rowCnt > 1) $rtn =  "ERROR: Multiple Rows returned for expected single result";
		 else { // when only one row is returned...
		 	$dummy = mysql_fetch_assoc($result);
			#print 'Dummy is ['; print_r($dummy); print ']<br />';
			if (count($dummy) > 1) $rtn =  "ERROR: Iso Request can only have ONE element.  [$query]";
		 	else $rtn = reset($dummy); // this returns just the first element of the array
			#print 'Return is ['.$rtn.']<br />';
		 } // end when only one row is returned...		
		 break;
	case "multi" : $rtn = ""; break;	 
  } // switch ($type)
  if ($type == "simple") { // this returns an array of type array(item1, item2, item3);
    $summary = array();
   	while (($row=mysql_fetch_assoc($result)) !=false) {
    	$summary[] = $row[key($row)];
    } // end of while statement
	$rtn = $summary;
    } // end if ($type == "simple")
  else ; // no action, just a marker
  	
  if ($rtn == "" && $type != "iso") { // if iso don't process if blank...
    if (mysql_num_rows($result) == 0) $rtn =  0; // this is when empty
  	elseif (mysql_num_rows($result)>0) {
	    $summary = array();
    	while (($row=mysql_fetch_assoc($result)) !=false) {
			if ($type == "keyed") {
				$cnt = 0;
				foreach ($row as $key =>$value) {
					if ($cnt==0) {
						$id = $value;
						$cnt=1;
					  } // end if ($cnt=0) {	
					else {
						$summary[$id] = $value;
						$cnt = 0;
					} // end 
				} // end foreach ($row as $key =>$value) {	
			  } // end if ($type == "keyed"
			if ($type == "keyFirst") {
				$keyID = array_shift($row);
				$summary[$keyID] = $row;
			  } // if ($type == "keyFirst") {
			elseif ($type == "asKey") {
				$keyID = array_shift($row);
				$summary[$keyID] = "";
			  } // if ($type == "keyFirst") {
			elseif ($type == "pair") {
				$keyID = array_shift($row);
				$summary[$keyID] = $row[key($row)];
			  } // if ($type == "stacked"  
		    else $summary[] = ($row);
	    } // end of while statement
        $rtn = $summary;  // this is simply to transfer things back with the array...
	  } // end if (mysql_num_rows($result)>0) {
	else $rtn = 'ERROR: ['.$query.']'; // 
  } // if ($rtn == "") {
  disconnectMySQL(); // this disconnects the database
  return $rtn;
} // end function doQuery  

if (isset($loadSimple)) ; // take no action
else { // not LoadSimple
  function rollIt($dataIn) { // this puts array into savable form
	$rtn = ""; 
	if (is_array($dataIn)) {
		foreach ($dataIn as $key => $val) {
			if (is_array($val)) $val = serialize($val);
			$rtn .= $key.' => '.$val.' || ';
		} // foreach ($dataIn as $key => $val) {
	} // if (is_array($dataIn)) {
	return rtrim($rtn, '|| ');
  } // function rollIt($dataIn) { // this puts array into savable form

  function numbIt($num, $type="") {  // performs various functions with numbers
 	global $paySymb;
	if ($paySymb == "") $paySymb = "$";
	switch ($type) {
      case "round" : return number_format($num,0,".",","); break; // returns rounded whole number
      case "money" : return $paySymb.number_format($num,2,".",","); break;
      case "moneyNum" : return $paySymb.number_format($num,0,".",","); break; // number rounded to nearest whole dollar
      case "moneyRev" : return $paySymb.number_format($num*(-1),2,".",","); break; // returns reverse (opposite) value from entereed.
	  case "ordinal" : 
	  	$suffixes = array("st", "nd", "rd");
    	$lastDigit = $num % 10;
	    if(($num < 20 && $num > 9) || $lastDigit == 0 || $lastDigit > 3) return $num."th";
		return $num.$suffixes[$lastDigit - 1];
	  	break;
	  case "pos" : // checks if great than zero
		$num = 1*$num; 
	  	if (!isset($num)) return false;
	  	elseif ($num == "" || $num == 0) return false;
		elseif (is_numeric($num) && $num > 0) return true;
		else return false;
		break;
	  case "grt" : // checks if equal to or greather than zero
		$num = 1*$num; 
	  	if (!isset($num)) return false;
		elseif ($num === 0) return true;
	  	elseif ($num == "" || $num == 0) return false;
		elseif (is_numeric($num) && $num > 0) return true;
		else return false;
		break;
	  case "zero" : // checks if zero
		if (!is_numeric($num)) return false;
		$num = 1*$num; 
		if ($num === 0) return true;
		else return false;
		break;
	  case "not" : // checks if number is NOT a plus number
		if (is_numeric($num) && $num > 0) return false;
		else return true;
		break;
	  case "cents" : // checks if there is some monetary amount to process
		$num = 1*$num; 
	    if ($num <= .01	|| $num >= .01) return true;
		else return false;
		break;
      default : return number_format($num,2,".",","); // default is two decimal integer
    } // end switch ($type)
 } // end function numbIt

  function timeIt($timeIn, $output, $lctr="") {
  // this takes $timeIn in YYYY-MM-DD format except for "dayCount" && "seconds && exact"
  // returns desired output
  // if you send this a blank time it returns todays date...
  // $lctr is an optional script loacator for errors identifier.
  if ($output == "MDY") {  // used to conver MM/DD/YYYY or M/D/YY to standard format  Must be separated by /
  	 $dummy = explode("/", $timeIn);
	 if (strlen($dummy[0]) == 1) $mo = "0".$dummy[0]; else $mo = $dummy[0];
	 if (strlen($dummy[1]) == 1) $da = "0".$dummy[1]; else $da = $dummy[1];
	 if (strlen($dummy[2]) == 4) $yr = $dummy[2]; 
	   elseif ($dummy[2] < date("y")) $yr = "20".$dummy[2];
	   else $yr = "19".$dummy[2];
	 return $yr.'-'.$mo.'-'.$da; // this returns the correct format  
  } // end if ($output == "MDY") {
  if ($output == "seconds" || $output == "12hr" || $output == "exact") {
  	  // $timeIn must be of format: YYYY-MM-DD HH:MM:SS
	  if (!isset($timeIn)) $timeIn = date("Y-m-d H:i:s");
	  elseif ($timeIn == "") date("Y-m-d H:i:s");
	  if (timeIt(substr($timeIn,0,10), "valid")) $d = explode("-", substr($timeIn,0,10));
	  else $d = explode("-", date("Y-m-d"));
	  $t = substr($timeIn,11,8);
	  if (substr($t,2,1) == ":" && substr($t,5,1) == ":") $t = explode(":", $t);
	  else $t = explode(":", "12:00:00");
  	  $input = mktime($t[0], $t[1], $t[2], $d[1], $d[2], $d[0]);
	  
	  if ($output == "seconds") return time()-$input;
	  elseif ($output == "exact") return date("l F j, Y \\a\\t H:i", $input); // Friday October 12, 2012 at 12:14
	  else return date("g:ia", $input); 
	  exit;
  } // if ($output == "seconds") {	  
  if ($output == "EOM") { // this gives the last day of the month... in YYYY-MM-DD format
    // input format for $timeIn is mm/yy
	 $yr = substr($timeIn,3,2);
	 if ($yr > 75) $Year = '19'.$yr; else $Year = '20'.$yr;
	 $EOM = $Year.'-'.substr($timeIn,0,2).'-';
     switch (substr($timeIn,0,2)) {
	   case "02" : $EOM .= '28'; break; 
	   case "04" : $EOM .= '30'; break;
	   case "06" : $EOM .= '30'; break;
	   case "09" : $EOM .= '30'; break;
	   case "11" : $EOM .= '30'; break;
	   default : $EOM .= '31';
	 } // end switch
 	 if (date("L", mktime(0,0,0,02,28,$yr)) && substr($timeIn,0,2) == "02" ) $EOM = substr($EOM,0,8)."29";
     return $EOM;
  } // end if ($output == "EOM")
  
  if ($output == "valid") {
  	if ($timeIn == "" || !isset($timeIn) || strlen($timeIn) != 10) return false;
  	$dummy = substr($timeIn,0,4)*1;
	if ($dummy < 1 || $dummy > 9999) return false;
	if (!is_numeric($dummy)) return false;
	$mo = substr($timeIn,5,2);
	$da = substr($timeIn,8,2);
	$yr = substr($timeIn,0,4);
	if (!is_numeric($mo) || $mo < 1 || $mo > 12) return false;
	if (!is_numeric($da) || $da < 1 || $da > 31) return false;
	if (!is_numeric($yr) || $yr < 1 || $mo > 12) return false;
    if (checkdate($mo, $da, $yr)) return true;
	else return false;
  } // if ($output == "valid") {
	  
  if ($output == "isEnd") {
  	if (strstr($timeIn, "||")) {
		$item = explode("||", $timeIn);
		if (timeIt($item[1], "valid")) $dte = $item[1];
		elseif (timeIt($item[0], "valid")) $dte = $item[0];
		else $dte = "invalid";
      } // if (strstr($row['e_dates'], "||")) {
  	elseif (timeIt($timeIn, "valid")) $dte = $timeIn;
  	else $dte = "invalid";
	return $dte;
  } // if ($output == "isEnd") {
	  
  if ($output == "dayCount" || $output == "range" || $output == "rangeOut" ) { // RangeOut does NOT include the last day of the range 
	// timeIn must be of format 2007-09-05>2007-09-07, if not adjust so calculating from today.... 
	// range returns a string of dates separated by a pip |
  	if (!strstr($timeIn, ">")) $timeIn = date("Y-m-d", time()).'>'.$timeIn; // if you don't include the > it puts todays date as the firs tin the range.
    $curDay = $startDay = mktime(0, 0, 0, substr($timeIn,5,2), substr($timeIn,8,2), substr($timeIn,0,4));
	if ($output == "rangeOut") $endDay = (mktime(0, 0, 0, substr($timeIn,16,2), substr($timeIn,19,2), substr($timeIn,11,4))) - (60*60*24);
	else $endDay = mktime(0, 0, 0, substr($timeIn,16,2), substr($timeIn,19,2), substr($timeIn,11,4));

	$days = round(($endDay-$startDay)/(3600*24), 0);
	if ($output == "dayCount") return $days;
	if ($output == "rangeOut" && $days == 0) return date("Y-m-d", $endDay);
	if ($days <=0) bugger("W", "cOm.1456", "When doing a RANGE, the first date must be earlier than the second. (Locator: $lctr])");
	$range = date("Y-m-d", $curDay);
	for ($i = 0; $i < $days; $i++) {
		$curDay = $curDay + (3600*24); // this adds a days worth of seconds to the maketime
		$range .= '|'.date("Y-m-d", $curDay);		
	} // for ($i = 0; $i <= 99; $i++)
	return $range; #($endDay-$startDay/(3600*60*24));
   } // end if ($output == "dayCount")
  elseif ($output == "unixday") { // pulls the unix range for a day inputted as 2014-09-29
  	 $strt = mktime(0, 0, 0, substr($timeIn,5,2), substr($timeIn,8,2), substr($timeIn,0,4));
	 return $strt.':'.($strt+(60*60*24)-1);
  } // elseif ($output == "unixday") { 

  if ($output == "lessOne" || $output == "plusOne") {
#    $newDay = (mktime(12, 0, 0, substr($timeIn,5,2), substr($timeIn,8,2), substr($timeIn,0,4))-(60*60*24));
    $currDay = mktime(0, 0, 0, substr($timeIn,5,2), substr($timeIn,8,2), substr($timeIn,0,4));
	if ($output == "lessOne") $newDay = $currDay - (60*60*24); 
	else $newDay = $currDay + (60*60*24); 
	return date("Y-m-d",$newDay); 
  } // end if ($output == "lessOne"

  if ($output == "split") {
    // must be of form YYYY-MM-DD>YYYY-MM-DD>speerator text
	if ($timeIn == "") $timeIn = ">";
	if (!strstr($timeIn, ">")) bugger("W", "cOm.1595", "Incorrect <i>timeIt</i> separator.  Should be<b> > </b>but is [".$timeIn."] (Locator: $lctr)");
  	$dummy = explode(">", $timeIn);
	if ($dummy[0] == "" || strlen($dummy[0]) != 10) $dummy[0] = "1970-01-01";
	if ($dummy[1] == "" || strlen($dummy[1]) != 10) $dummy[1] = "2099-12-31";
	if ($dummy[2] == "") $dummy[2] = "to";
	$item = explode("-", $dummy[0]);
	$rtn = date("l F j, Y", mktime('00', '00', '00', $item[1], $item[2], $item[0])).' '.$dummy[2].' '; 
	$item = explode("-", $dummy[1]);
	$rtn .= date("l F j, Y", mktime('00', '00', '00', $item[1], $item[2], $item[0])); 
    return $rtn;
  } // end if output == "split"
  
  if ($output == "back") {
  	if (!strstr($timeIn, "::")) bugger("W", "dEm.128", "Time In must be of format YYYY-MM-DD::99 but you have [$timeIn] (Locator: $lctr])");
	$date = explode("::", $timeIn);
	$item = explode("-", $date[0]);
	$dummy = mktime('00', '00', '00', $item[1], $item[2], $item[0]); // this is the date time
	$dayBack = $date[1]; // this is the number of days back you want to check for an increase.
	return date("Y-m-d", ($dummy - ($dayBack*24*60*60))); // this returns the days back in YYYY-MM-DD format
  } // if ($output == "back") {

  if (strstr($timeIn, "-") && $timeIn > "") { // if timeIn exists and looks like right format..
  	  if (strstr($timeIn, ":")) { // has minutes
		$dummy = explode(":", substr($timeIn,11));
		$hr = $dummy[0]; $mn = $dummy[1]; $sc = $dummy[2];
		$timeIn = substr($timeIn,0,10);
	    } // if (strstr($timeIn, ":") { // has minutes
	  else $hr = $mn = $sc = 0;
  	  $dummy = explode("-", $timeIn);
	  $currYear = $dummy[0];
	  $currMon = $dummy[1];
	  $currDay = $dummy[2];
	  #$currMon = substr($timeIn,5,2);  $currDay = substr($timeIn,8,2);  $currYear = substr($timeIn,0,4);
	  // because of daylight savings time, this is the best way to do this...
	  if ($output == "day1") $tomorrowTime = mktime($hr, $mn, $sc, $currMon, $currDay+1, $currYear);
	    else $unixTime = mktime($hr, $mn, $sc, $currMon, $currDay, $currYear);
	  $valid = "";

	  if ($currMon == "02" && date("L", mktime(0,0,0,02,28,$currYear))) $EOM = '29';
	   elseif ($currMon == "02") $EOM = '28';
	   elseif ($currMon == "04" || $currMon == "06" || $currMon == "09" || $currMon == "11") $EOM = '30';
	   else $EOM = '31';

  	  if ($currYear < 1000 || $currYear > 9999) $valid = "Year is Wrong";  // years must be between 1000 and 9999
  	   elseif (strlen($currMon) != 2 || strlen($currDay) != 2) $valid = "Month (".$currMon.") or Day (".$currDay.") string length incorrect"; // string length of month and date must be two
  	   elseif ($currMon < 1 || $currMon > 12) $valid = "[".$currMon."] Month is out of range 01 to 12";  // valid months 1-12
  	   elseif ($currDay < 1 || $currDay*1 > $EOM) $valid = "Day is out of valid range for this month (01 to ".$EOM.")."; // valid days as calculated
  	  if ($output == "valid") return $valid;
      elseif ($valid > "") bugger("W", "fDm.131", "The input date of [$timeIn] and OUTPUT [$output] has error: [".$valid."]. (Locator: $lctr])"); 
    } // if (strstr($timeIn, "-") && $timeIn > "")
  elseif ($timeIn > "" && ($timeIn > 0 || $timeIn < 4102531200)) $unixTime = $timeIn; // when value comes in as valid time...
  elseif ($output == "valid") bugger("W", "fDe.130", "For type VALID the timeIn cannot be blank. (Locator: $lctr])");
  else $unixTime = time();
  
  #mydo("NICE 1 = [".date("l F j, Y H:i", $unixTime)."] and NOW TIME [".date("l F j, Y H:i")."]",false);
  
  switch ($output) { // this returns possible outputs
    case "unix" : return $unixTime; break;
	case "text" : return date("D, M jS", $unixTime); break; // Fri, Oct 12th
	case "date" : return date("Y-m-d",$unixTime); break; // 2012-10-12
    case "abbv" : return date("Md", $unixTime); break; //  Dec03
    case "chat" : return date("M d H:s", $unixTime); break; //  Dec03
    case "shrt" : return date("y M d", $unixTime); break; //  Dec03
	case "full" : return date("l F j, Y", $unixTime); break;  // Thursday December 3, 2006
	case "mini" : return date("D M j, Y", $unixTime); break;  // Fri Oct 12, 2012
	case "time" : return date("Y-m-d H:i:s", $unixTime); break; // 2012-10-12 15:48:50
	case "all"  : return date("l F j, Y \\a\\t H:i", (time()+(60*60*4))).' GMT'; break; // GIVES THE CURRENT GMT Time  Saturday October 13, 2012 at 14:53 GMT
#	case "days" : return round((time()-$timeIn)/(60*60*24), 1); break;
	case "days" : return round((time()-$unixTime)/(60*60*24), 1); break;
	case "nice" : return date("l F j, Y \\a\\t H:i", $unixTime); break; // Friday October 12, 2012 at 12:14
	case "day1" : return date("Y-m-d", $tomorrowTime); break; 
	default : bugger("W", "fDm.148", "UnixTime type of ($output) not defined. (Locator: $lctr])");
  } // end switch ($output)	
 } // end function timeIt($timeIn, $output) {

 function getTT($data, $loc="") {
	global $l,$mySess;
	if ($l == "en" || $l == "master") $lang = "00";
	else $lang = $l;
	$lesn = 1; 
	$data = rtrim($data, "|"); // trims right pip if there is one.
	// loc requires format of two letter script code, colon, then line number
	if (numbIt(substr($loc,3), "pos")) ; // all okay   FORK:
	elseif ($loc == "" || (substr($loc,2,1) != ":" && substr($loc,2,1) != ".")) bugger("W", "cCm.534 - Location Required [$loc]", "You cannot use getTT without the location defined with proper format.[$data]");
	elseif (numbIt(substr($loc,3), "pos")) ; // all okay
	$item = substr_count($data,"|")+1;
	$whr = "p_code = '".str_replace("|", "' OR p_code = '", $data)."'"; 
	$rtn = doQuery("SELECT p_code, p_phrase FROM phrase WHERE p_lang LIKE '$lang' AND ($whr)", "pair");
	if ($rtn == "") ; // no return
	elseif (count($rtn) == $item) return $rtn; // if matches, means all found, so RETURN!!!
	elseif ($lang == "00" || $lang == "master" || $lang == "en") {
		$tckTxt = "The following problems occurred with this query: [$whr]<br />";
		$list = explode("|", $data);
		$probCnt = 0;
		$tckFix = "";
		foreach ($list as $key) {
			if (!isset($rtn[$key])) {
				$probCnt++;
				$tckFix .= $key.' || ';
				$rtn[$key] = "MISSING: Phrase [$key] not in master table";
			} // if (!isset($rtn[$key])) {
		} // foreach ($list as $row) {
		if (numbIt($probCnt, "pos")) $tckTxt .= 'These phrases were not found in MASTER table: '.rtrim($tckFix, " || ").'<br /><br />';
		$keyCount = doQuery("SELECT p_code, count(pID) AS totl FROM phrase WHERE p_lang = '00' GROUP BY p_code ORDER BY totl DESC", "pair");
		foreach ($keyCount as $key => $val) {
			if ($val > 1) $tckTxt .= "Duplicate p_code value in PHRASE table for [$key]<br />";
		} // foreach ($keyCount as $key => $val) {
		ticket("lC.726 - Invalid Phrase Count at Locator [$loc]", $tckTxt, "");
		return $rtn;
	} // elseif ($lang == "00" || $lang == "master" || $lang == "en") {
	
	$rtn = array();
	$lwiki = doQuery("SELECT p_code, pID, p_phrase, '' AS p_replace FROM phrase WHERE p_lang LIKE '00' AND ($whr)", "keyFirst");
	foreach ($lwiki as $key => $row) {
		if (numbIt($row['pID'], "pos")) {
			if ($lang != "00") $lTpe = "utf8::multi"; else $lTpe = "multi"; 
			$item = doQuery("SELECT w_text, (length(w_votes)-length(replace(w_votes,'|','')))/1 as pipCount ".
						   "FROM wiki_language ".
						   "WHERE w_study = 0 AND w_lang = '$lang' AND w_lesson = $lesn AND w_screen = ".$row['pID']." ".
						   "ORDER BY pipCount DESC", $lTpe);
		  } // if (numbIt($row['pID'], "pos")) {
		else bugger("W", "lC.744 - Invalid rowID", "This value should always be defined [".$row['pID']."] for where ($whr)");	  
		if (empty($item)) { // to launch a new Wiki_lang 	
			$line = googAPI($row['p_phrase'], $lang);
			
			
			if (substr($line,0,6) == "ERROR:") bugger("W", "lC.713 - Translation Error for [$lang]", "The phrase [".$row['p_phrase']."] was translated [$line]");
			else {
				if ($lang != "00") $inphr = "utf8::insert"; else $inphr = "insert";				
#				doQuery("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
				doQuery("INSERT INTO wiki_language (w_id, w_study, w_lang, w_lesson, w_screen, w_text, w_votes) VALUES ".
						 "(0, 0, '$lang', $lesn, ".$row['pID'].", '".str_replace("'", "\'", $line)."', '|')", $inphr);
			} // else 
		  } // if (empty($item)) { // to launch a new Wiki_lang 			   
		elseif (is_array($item)) $line = $item[0]['w_text']; 
		else bugger("W", "lC.716 - Translation Error for [$key]", "There was an error in language retrieval for [".$row['pID']."] giving [$item]");
		$rtn[$key] = $line;
	} // foreach ($lwiki as $key => $row) {
	$txt = "";
	$dummy = explode("|", $data);
	foreach ($dummy as $row) {
		if (!isset($rtn[$row])) $rtn[$row] = "";
		if ($rtn[$row] == "") {
			$rtn[$row] = "Translation missing for [$row]"; 
			$txt .= 'Master Translation Missing for ['.$row.'] when inbound data was ['.$data.']<br />';
		} // if ($rtn[$row] == "") {
	} // foreach ($dummy as $row) {
	if ($txt > "") ticket("Translation Text Error at Location [$loc]", "<h1>Master Translation Errors!!!</h1>".$txt, "", false);
	return $rtn;	
 } // function getTT($data) {
	  
 function db_cleanPost($postVals, $skipList="", $kind="add") {
  // skipList are fields to not include in post.  It is a string starting with |>item1<||>item2<|
  // skipList are fields to not include in post.  It is a string starting with :item1:item2:  I THINK IT'S THIS ONE>>>
  // $kind determines if you're adding slashes or stripping
	if (is_object($postVals)) { // if coming in as object, this converts to array
		$dummy = $postVals;
		$postVals = array();
		foreach ($dummy as $item => $val) $postVals[$item] = $val;
	} // if (is_object($postVals)) {

	if (is_array($postVals)) {
	  $outArray = array();
	  foreach ($postVals as $item => $value) {
	    if (!strstr($skipList, ':'.$item.':')) { // the : are front AND back boundaries to denote keyfield names
		  if (strstr($item, "email")) $value = strtolower($value);
		  if ($kind == "add") $outArray[$item] = str_replace("'", "\'", $value);
			else $outArray[$item] = stripslashes($value);
	    } // end if ($item != "newAct") {
	  } // end foreach ($_POST as $item => $value) {
	} // if (is_array($postVals)) {
	else $outArray = $postVals;
    return $outArray;
 } // function db_cleanPost($postVals, ...)
 
 function mkSet($dataIn, $vals, $type="arr") {
	 // $dataIn is the current array/class, $vals are string separated by pips  $type gives output arr or class
	 // if you send an empty string for dataIn it will create the appropriate entry
	$dummy = explode("|", $vals); // this must be at front so class build can read

	 if (!isset($dataIn)) $cur = "";
	 if (empty($dataIn)) $cur = "";
	 elseif (is_array($dataIn)) $cur = "arr";
	 elseif (class_exists($dataIn)) $cur = "cls";
	 else bugger("W", "cMn.2626 = Invalid Data In Type", "You must define action for type of [$type] when dataIn is [$dataIn]");
	 if ($cur == "" && $type == "arr") $dataIn = array();
	 elseif ($cur == "" && $type == "cls") {
		 	$txt = $dummy[0];
			$dataIn = (object) array($txt => '');
/*  THIS HOW IT WAS
			class toastr {
				var $txt;
			} // class helpVals {
			$dataIn = new toastr; 
*/
	 } // elseif ($cur == "" && $type == "cls") {

	if ($vals > "") {
		foreach ($dummy as $row) {
			if ($type == "arr") { if (!isset($dataIn[$row])) $dataIn[$row] = ""; }
			elseif ($type == "cls") { if (!isset($dataIn->$row)) $dataIn->$row = ""; }
			else bugger("W", "cMn.2627 - Invalid type [$type]", "You must define action for this type");
		} // foreach ($dummy as $row) {		
	} // if ($vals > "") {
	return $dataIn;	
 } // function mkSet($vals, $type="array") {
	 
 function getUser($where, $what="id,logKey,firstname,lastname,email,nameline") {
	##### SPECIAL CASES ###################################################
	## $where begins "username LIKE" then $what value should be password ##
	#######################################################################
#	if ($where == "") mkSet($user=array(), "id|email|firstname|lastname|mobile|language");
#	else $user = doQuery("SELECT $what FROM popLog WHERE $where", "single", "", "poplog");
	if ($where == "email=''") return array("id"=>0, "rsultGU" => "bademail");
	if (numbIt($where, "pos") && $what == "nameline") return doQuery("SELECT CONCAT (firstname,' ',lastname) FROM popLog WHERE id = $where", "utf8::iso", "", "poplog");
	if (numbIt($where, "pos")) $where = "id=$where";
	if (strstr($what, "nameline")) $what = str_replace("nameline", "CONCAT (firstname,' ',lastname) AS nameline", $what);
	if (substr($where,0,13) == "username LIKE") { // this is a login intercept
		#$user = array("id"=>0,"logKey"=>"","firstname"=>"","lastname"=>"","email"=>"","result"=>"");
		$dummy = doQuery("SELECT recno,popno,passwd FROM users WHERE $where", "single", "", "poplog");
		if ($dummy == "") return array("id"=>0, "rsultGU" => "nouser");
		elseif (!is_array($dummy)) return array ("id"=>0, "rsultGU" => "DBerror769"); // The Database has an error. 
		elseif (md5(strtolower($what)) == $dummy['passwd']) {
			if (numbIt($dummy['popno'], "pos")) {
				$user = doQuery("SELECT id,logKey,firstname,lastname,email FROM popLog WHERE id = ".$dummy['popno'], "single", "", "poplog");
				$uK = substr($user['logKey'],0,2);
				if ($uK != "un") {
					if ($uK == "em" || $uK == "gm" || $uK == "fb" || $uK == "tk" || $uK == "rl") {
						doquery("UPDATE popLog SET logKey = 'un:".$dummy['recno']."' WHERE id = ".$dummy['popno']." LIMIT 1", "update", "", "poplog");
						ticket("lC.778 - USER RECORD UPDATE for ID [".$user['id']."]", "FORK: The OLD logKey was [".$user['logKey']."] with email [".$user['email']."] Set this up as auto send email.", $user['email']);
						$user = doQuery("SELECT id,logKey,firstname,lastname,email FROM popLog WHERE id = ".$dummy['popno'], "single", "", "poplog");
					} // if ($usrKey == "em" || $usrKey == "gm" || $usrKey == "fb" || $usrKey == "tk" || $usrKey == "rl") {
				} // 
				if (substr($user['logKey'],3) == $dummy['recno']) {
					$id = logMeIn($user['id'], $user['email'], "lC.851");
					if (numbIt($id,"pos")) $user['rsultGU'] = "found"; 
					else {
						ticket("lC.778 - Failed Log In", "This Failed for For ID of [".$user['id']."] and email [".$user['email']."]");
						$user['rsultGU'] = "logmefail";
					} // 
			  	} // if (substr($user['logKey'],3) == $dummy['recno']) {
				else return array ("id"=>0, "rsultGU" => "DBmismatch"); // Database entries do not match. When table values do not match
			  } // if (numbIt($dummy['popno'], "pos")) {
			else {
				$tck = "";
				foreach ($dummy as $key => $val) $tck .= $key.' = '.$val.' || ';
				ticket("lC.836: Error in popLog search", "The popLog didn't work. The WHERE statment was [$where] and ticket: [$tck]");
				$user['rsultGU'] = "logmefail";
			} // 
		  } // if (md5(strtolower($what)) == $dummy['passwd']) {
		else return array ("id"=>0, "rsultGU" => "PWerror");
	  } // if (substr($where,0,13)...
	else $user = doQuery("SELECT $what FROM popLog WHERE $where", "utf8::single", "", "poplog");
	#if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36') {bugger ("A", "user.898", $user);}
	if (is_array($user)) {
		if (count($user) == 1) return array_shift($user);
		else ; // this is fine..
	  } // if (is_array($user)) {
	elseif (substr($user,0,5) == "ERROR") bugger("W", "lC.886 - Error PopLog Table", "You need to find out what's up: [$user] with where [$where] and [$what]"); 
		
	if (!isset($user['logKey'])) ; // continue, this is okay
	elseif (substr($user['logKey'],0,3) == "un:") {
		$dummy = doQuery("SELECT * FROM users WHERE recno = ".substr($user['logKey'],3), "single", "", "poplog");
		if ($dummy['popno'] != $user['id']) bugger("W", "lC.789 - Invalid USER", "With LogKey [".$user['logKey']."] and ID [".$user['id']."] userTable does not match [".$dummy['popid']."]");
		else $user = array_merge($user,$dummy);
	} // if ($what == "*" && substr($user['logKey'],0,3) == "un") {
	return $user;
 } // function getUser() {
	 
 function postUser($pVals) {
	if (!is_array($pVals)) bugger("W", "lC.790 - Empty pVals", "You should never get here without defined array");
	$pVals = mkSet($pVals, "popID|email|firstname|lastname|mobile|language");

	if (!validEmail($pVals['email'])) {
		global $id,$a; 
		$nte = "";
		foreach ($pVals as $key => $val) $nte .= $key.' || ';
		ticket("iC.62 - RETURNED ERROR: Invalid Email [".$pVals['email']."]", "How get here with invalid email and id [$id] and aVal [$a]<br />".$nte); 	  
		return "ERROR: Invalid Email||".$pVals['email'];
	  } // if (!validEmail($pVals['email'])) {
	else $email = strtolower($pVals['email']);
	$isUser = getUser("email LIKE '$email'");
	if (numbIt($pVals['popID'], "not") && empty($isUser)) { // this is a new user
		if (isset($isUser['popID'])) bugger("W", "lC.795 - User Exists", "You should never get here");
		// define poplogger
		if (!isset($pVals['logKey'])) $popLogger = "em:".$pVals['email'];
		else { // when it is set
			$item = substr($pVals['logKey'],0,2);
			if ($item != "fb" && $item != "gm" && $item != "tk" && $item != "rl" && $item != "un" && $item != "em") { 
				$msg = ""; 
				foreach ($pVals as $key => $val) { $msg .= $key." = ".$val." || "; }
				ticket ("lC.827 - Invalide logKey type [$item]", "These are the pVals: $msg"); 
			} // if ($item != "fb" && $it...
			if ($pVals['logKey'] > "") $popLogger = $pVals['logKey'];
			else bugger("W", "FORK: lC.799 - Undefined PopLog [".$pVals['logKey']."]", "You need to define popLog actions for this type.");
		} // else { // when it is set
		$guid = doQuery("", "guid");
		$query = "INSERT INTO popLog (guid, logKey, email, agree, created, lastLog) VALUES
					('$guid', '$popLogger', '".$pVals['email']."', '', '".date("Y-m-d H:i:s")."', '')";
		$pVals['popID'] = doQuery($query, "insert", "", "poplog");
		if (numbIt($pVals['popID'], "pos")) {
			if (isset($pVals['recno'])) {
				if ($pVals['recno'] == "new") $pVals['recno'] = doQuery("INSERT INTO users (popno, username) VALUE (".$pVals['popID'].", '$username')", "insert", "", "poplog"); 
				$pVals['logKey'] = "un:".$pVals['recno'];
			} // if (isset($pVals['recno'])) {
			$isUser = getUser("id = ".$pVals['popID']);
		  } // if (numbIt($pVals['popID'], "pos") 
		else bugger("W", "lC.804 Invalid id [".$pVals['popID']."]", "Insert of new Log entry did not work!  [$query]");
	  } // insert of new record	is complete	
 	elseif (numbIt($pVals['popID'], "not")) bugger("W", "lC.808 - Duplicate Post for [$email]", "You are trying to do new post but record already exists [".$isUser['id']."]");
	elseif ($pVals['popID'] != $isUser['id'] && numbIt($isUser['id'], "pos")) bugger("W", "lC.809 - Dup Email [$email]", "Your Record [".$pVals['popID']."] Current Holder [".$isUser['id']."]"); 
	// if email is different, check if okay to change
	if ($email != $isUser['email']) { // this should already be caught with javaScript but if not, this fixes
		$isEmail = doQuery("SELECT id,email FROM popLog WHERE email LIKE '$email'", "pair", "", "poplog");
		if (is_array($isEmail)) bugger("W", "FORK: cC810 - Email Exists", "You cannot change to this email [$email] in that it already exists isUser[".$isUser['email']."] with popLogID [".$isEmail['id']."].");	
	} // if ($email != $isUser['email']) { 

	$queryP = "";
	$queryU = "";
	if (isset($pVals['remember'])) $pVals['remember'] = "on";
	elseif (isset($pVals['username'])) $pVals['remember'] = "";
	if (isset($pVals['password'])) {
		if (substr($pVals['password'],0,5) == "ID:_:") {
			$dummy = substr(rtrim($pVals['password'], ":_:"),5);
			if (numbIt($dummy, "pos")) unset($pVals['password'], $pVals['passwordconfirm']);
		} // if (substr($pVals['password'],0,5) == "ID:_:") {
	} // if (isset($pVals['password'])) {

	foreach ($pVals as $key => $val) {
		if ($key == "popID" || $key == "confirmPassword" || $key == "recno" || $key == "id") continue;
		elseif ($key == "password") $queryU .= "passwd = '".md5(strtolower($val))."', ";
		elseif ($key == "username" || $key == "remember" || $key == "hint" || $key == "hintanswer" || $key == "passless") 
				$queryU .= $key." = '".$val."', ";
		elseif ($key == "logKey" || $key == "firstname" || $key == "lastname" || $key == "email" || $key == "mobile" || $key == "country" ||
				$key == "region" || $key == "city" || $key == "gender" || $key == "birthdate" || $key == "language" || $key == "agree" ||
				$key == "rmber" || $key == "lastIP" || $key == "lastLog" || $key == "visits") $queryP .= $key." = '".$val."', ";
#		else $queryP .= $key." = '".str_replace("'", "\'", $val)."', ";
		else ticket("lC.987 - Undefinded Field [$key]", "You must define the action (or remove field) for this key field"); #$queryP .= $key." = '".$val."', ";
	} // foreach ($pVals as $key => $val) {
	if ($queryP > "" && numbIt($pVals['popID'], "pos")) {
#		if (strstr($pVals['logKey'], "fb:")) { // temp chek
#			$msg = ""; foreach ($pVals as $key => $val) $msg .= $key.'='.$val.' || ';
#			ticket("lC.944 - Temp Check: Facebook Watch (remove when stable)", "Check to make sure this updated okay [$msg] with query [$queryP]");
#		} // temp chck
		$queryP = "UPDATE popLog SET ".rtrim($queryP, ", ")." WHERE id = ".$pVals['popID']." LIMIT 1";
		if (!doQuery($queryP, "utf8::update", "", "poplog")) bugger("W", "lC.902 Update Failure ID [".$pVals['popID']."]", "Update failed for query [$queryP]");
	} // if ($queryP > "") {
	if ($queryU > "" && numbIt($pVals['recno'], "pos")) {
		if (!isset($pVals['remember'])) $days = 0; elseif ($pVals['remember'] > "") $days = 30; else $days = 0;
		cookieSet($pVals['popID'], $pVals['username'], $days);
		$queryU = "UPDATE users SET ".rtrim($queryU, ", ")." WHERE recno = ".$pVals['recno']." LIMIT 1";
		if (!doQuery($queryU, "utf8::update", "", "poplog")) bugger("W", "lC.906 Update Failure ID [".$pVals['recno']."]", "Update failed for query [$queryU]");
	} // if ($queryP > "") {
	#mydo("at 904 with queryU of [$queryU] and md5 of val [".md5($pVals['password'])."]");	
	return $isUser;
	 
###########################33  BELOW IS REFERENCE FOR whEN YOU WorK With USerNAME Stuff ################3	 

/*	

	if ($noLog == "") $_SESSION['logged'] = $popLogger.":".$id; // update in light of potential popLogger type change

	if ($actType == "un") { // update of the users table...
		$query = "UPDATE users SET 
					popno = $id, 
					username = '".$pWrk['username']."', ";
		if ($pWrk['passwd'] != "^existing:^") $query .= "passwd = '".md5(strtolower($pWrk['passwd']))."', ";
		$query .= " hint = '".$pWrk['hint']."', 
					hintanswer = '".$pWrk['hintanswer']."',
					passless = '".$pWrk['passless']."' ".
				"WHERE recno = $recno LIMIT 1";
		doQuery($query, "update", "", "poplog");		 	
    } // if ($actType == "un" && $usrErr == ""{
	
	if (!isset($_COOKIE[$cName]["popLog"])) $_COOKIE[$cName]["popLog"] = "";

	if ($noLog > "") ; // skip this step
	elseif ($_COOKIE[$cName]["popLog"] > "" && $pWrk['rmber'] == "") cookieSet($cName, $id, ""); // this forces an erase when you want it off...
	elseif ($pWrk['rmber'] > "") cookieSet($cName, $id, "on");
	// if not on and cookie not set, do nothing...
    return $id; // return the popLog ID
*/	
 } // function postUser ($pvals)
 
  function makeUser($pVals) {
	if (!isset($pVals['email'])) return "ERROR: Invalid Email";	  
	$dummy = getUser("email='".$pVals['email']."'", "id,email,firstname,lastname");
	if (is_array($dummy)) return $dummy; 
	// if user doesn't exist, create new one
	if (!isset($pVals['firstname']) && !isset($pVals['lastname']) && isset($pVals['nameline'])) {
		$nameline = explode(" ", $pVals['nameline']);
		$cnt = count($nameline);
		if ($cnt == 1) $pVals['lastname'] = $nameline[0];
		else { // for more than one name puts all as first name
			$item = "";
			$pVals['lastname'] = end($nameline);
			unset($nameline[$cnt-1]);
			foreach ($nameline as $row) $item .= $row.' ';
			$pVals['firstname'] = rtrim($item, " ");
		} // else { // for more than one name puts all as first name
	} // if (isset($pVals['nameline'])) {
	unset($pVals['nameline'], $pVals['type']);
	return postUser($pVals);
  } // function makeUser($pVals) {
 
  function logMeIn($id, $email, $loc) {
	global $mySess, $cName, $l, $me;
	$dummy = explode(" - ", $loc);
	if (isset($dummy[1])) $who = $dummy[1]; else $who = ""; 
	$loc = $dummy[0];
	if (numbIt(substr($loc,3), "not") || substr($loc,2,1) != ".") bugger("W", "lC.989 - Invalid Locator [$loc]", "You must define a locator code. The who is [$who]");
	if (empty($mySess)) ticket ("lC.1079 - missing mySess", "You should never have missing mySess when id [$id] and email [$email]");
	if (numbIt($id, "pos") && validEmail($email)) $dummy = getUser("id = $id AND email = '$email'", "id,logKey,firstname,lastname,email,lastLog");
	else bugger("W", "lC.991 - Cannot Login Error at Locator [$loc]", "With id [$id] and email [$email] you should not get here");
	if (!is_array($dummy) || !isset($dummy['id'])) ticket ("lC.1081 - Invalid popLog for ID [$id]", "With email [$email] you have invalid return: ".$dummy);
	if (numbIt($dummy['id'], "pos")) {
		$mySess->id = $id;
		$lK = substr($dummy['logKey'],0,2);
		if ($lK != "em") $mySess->usr = $lK;
		$doSess = str_replace("'", "\'",serialize($mySess));
		doQuery("UPDATE seszions SET id = $id, mySess = '$doSess' WHERE phpsez = '".session_id()."'", "update", "", "poplog");
		if (!isset($dummy['lastLog'])) $visitUp = "";
		elseif (strstr($dummy['lastLog'],"0000-00-00")) $visitUp = "";
		elseif (timeIt($dummy['lastLog'], "seconds") > 3600) $visitUp = ", visits=visits+1"; else $visitUp = ""; // only updates the visits if it has been more than an hour since the last visit
		doQuery("UPDATE popLog SET lastIP = '".$_SERVER['REMOTE_ADDR']."', lastLog = '".date("Y-m-d H:i:s")."'".$visitUp." ".
				"WHERE id = $id LIMIT 1", "update", "", "poplog");
		#$me = (object) array(getUser("id = $id"));
		

/* FORK		if (function_exists('cookieSet')) {
			cookieSet($cName, $id, "on"); // set the cookie
	  	  } // if (function_exists(visitSet) && function_exists(cookieSet)) {
		else ticket("lC.1086 - missing functions", "The Functions visitSet and cookieSet are missing which should never happen");
*/		
	  } // if (numbIt($dummy['id'], "pos")) {
	else $id = -1;	  
	return $id;  
 } // function logMeIn
 
 function sendVerCode($id, $toName, $toEmail, $urlOnly=false) {  // $url only means that it returns the URL but doesn't send out the verification code
	global $siteKey, $siteInfo, $conFigs, $attnTrans,$l;
	$tt = getTT("title|aIDSendIntro|verCode|pwSendIntro|timeDelay|vCodeIs|alsoGo|nofwd|valCdeRes", "cc:792");
	if ($toEmail == "" || numbIt($id, "not")) bugger("W", "cOm.568 - Missing Parameters", "You must have email [$toEmail] and id [$id] defined."); 
	else $toEmail = strtolower($toEmail); 
	#if ($toEmail == "") return "needEmail";
	#elseif (numbIt($id, "not")) return "needID"; 

	$currTime = time();  
	$curVer = doQuery("SELECT ver_email, ver_code, ver_key, ver_sent, ver_popID, ver_count FROM verCodes WHERE ver_email = '$toEmail'", "single", "", "poplog");
	if (!is_array($curVer)) $curVer = array("ver_email" => '', "ver_code" => '', "ver_key" => '', "ver_sent" => '', "ver_sent" => '', "ver_count" => '');
	$timeDelay = 60*60*6; // 60 seconds, 60 minutes, 6 hours = so thus code holds for six hours
	if (numbIt($curVer['ver_sent'], "pos") && ($currTime < $curVer['ver_sent'] + $timeDelay) && numbIt($curVer['ver_code'])) $verCode = $curVer['ver_code'];
	else $verCode = rand(100000, 999999);
	$diff = $currTime - $curVer['ver_sent'];
	if (numbIt($curVer['ver_key'], "pos")) $verKey = $curVer['ver_key'];
	else $verKey = rand(100000, 999999);
	
	if (!isset($curVer['ver_popID'])) $curVer['ver_popID'] = "";
	if (numbIt($curVer['ver_popID'],"not")) {
		$item = doQuery("SELECT id FROM popLog WHERE email = '$toEmail'", "iso", "", "poplog");
		if (numbIt($item, "pos")) {
			$curVer['ver_popID'] = $item;
			doQuery("UPDATE verCodes SET ver_popID = $item WHERE ver_email = '$toEmail' LIMIT 1", "update", "", "poplog");
		} // if (numbIt($item, "pos")) {
	} // if (numbIt($curVer['ver_popID'],"not")) {
	
	if ($curVer['ver_email'] > "") {
		if ($id != $curVer['ver_popID']) bugger("W", "lC.1012 - Invalid Match", "The input ID [$id] for email [$toEmail] does not match VerCODE ID [".$curVer['ver_popID']."]");
		$verCnt = $curVer['ver_count'] + 1;
		doQuery("UPDATE verCodes ".
				"SET ver_code = $verCode, ver_key = $verKey, ver_sent = '$currTime', ver_popID = $id, ver_count = $verCnt ".
				"WHERE ver_email LIKE '$toEmail' LIMIT 1", "update", "", "poplog");
	  } // if ($curVer['ver_sent'] > 0) {	
	else doQuery("INSERT INTO verCodes (`ver_email`, `ver_code`, `ver_key`, `ver_sent`, `ver_popID`, `ver_count`) VALUES ".
				"('$toEmail', '$verCode', '$verKey', '$currTime', '$id', 1)", "insert", "", "poplog");

	$ran3 = substr($toEmail,2,1).substr($toEmail,0,1).substr($toEmail,1,1);
	if (numbIt($siteKey, "pos") && $conFigs->siteType == "evntcore") {
		bugger("W", "FORKlC.590 - Unfinished.", "You need to yet set this up for eventCORE functions");
		$dummy = doQuery("SELECT aID, a_email FROM attend WHERE a_email LIKE '".strtolower($toEmail)."' AND a_event LIKE $siteKey ORDER BY aID");
		if (!empty($dummy)) {
			$row = $dummy[0]; // pick the first one
			$url = logLink($row);
		  } // if (!empty($dummy))	
		else $url = $conFigs->siteSecure."/index.php?a=dirLog&site=$siteKey&e=$toEmail&i=".substr($verCode,3).($verKey*strlen($toEmail)).$ran3.substr($verCode,0,3);
		$msg = $tt["title"].' <b>'.$siteInfo['e_title'].'</b><br /><br />';
		$msg .= $tt["aIDSendIntro"].'<br /><br />';
		$msg .= $tt["verCode"].'<br /><br />'; 
	} else { // when site is not defined...
		$msg = $tt["pwSendIntro"].'<br /><br />';
		$msg .= $tt["verCode"].'<br /><br />';
		#$url = $conFigs->siteSecure."/index.php?a=dirLog&e=$toEmail&i=".substr($verCode,3).($verKey*strlen($toEmail)).$ran3.substr($verCode,0,3);
		$url = $conFigs->siteSecure."/index.php?a=dirLog&e=$toEmail&i=".substr($verCode,3).($verKey*strlen($toEmail)).$ran3.substr($verCode,0,3);
	} // end else
	if ($l != "00") $url .= "&lang=$l";	
	if ($urlOnly) return $url;
	$msg .= $tt["timeDelay"].' <b>'.timeIt("", "all").'</b><br /><br />';
	$msg .= $tt['vCodeIs']." [<b>".$verCode."</b>]<br /><br />";
	
	$msg .= $tt['alsoGo'].': <a href="'.$url.'">'.$url.'</a><br /><br />';
	$msg .= $tt['nofwd'];
	$rtn = quikSendHTML ($toName, $toEmail, $conFigs->siteOwner, $conFigs->siteSender, '', '', $tt['valCdeRes'].' ('.$verCode.')', $msg, "html"); // this sends out the email
	#Array ( [0] => Array ( [email] => paul.konstanski@cru.org [status] => sent [_id] => 38d31f14b8674d0ba0baa40fe890039d [reject_reason] => ) )
 } // end function sendVerCode ()
 
  function quikSendHTML ($toPerson, $toEmail, $fromPerson, $fromEmail, $cc, $bcc, $subj, $msg, $type="html", $txt="", $attach="") {
	global $mandrill;
	$replyTo = $fromEmail;
	if (!strstr($msg, "<body>") && $type == "html") $msg = '<body>'.$msg.'</body>';
	elseif ($type != "html" && $txt > "") bugger("W", "cMn.2035 - Invalid Txt Entry for type [$type]", "You should never have a text entry for nonHTML [$txt]");
	elseif  (isset($mandrill)) ; // no action, mandril will convert.
	elseif ($type != "html") $msg = html2text($msg); // for sendMailer
	if (!validEmail($toEmail)) bugger("W", "cOm.1755 - Invalid to Email", "To Email is [$toEmail] and From email is [$fromEmail] Subject: [$subj] and Message [$msg]"); 
	if (!validEmail($fromEmail)) bugger("W", "cOm.1848 - Invalid to or From Email", "To Email is [$toEmail] and From email is [$fromEmail] Subject: [$subj] and Message [$msg]"); 

#if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36') { sendMailer ($toPerson, $toEmail, $fromPerson, $fromEmail, $replyTo, $cc, $bcc, 0, $subj, $msg, $type); mydo ("1158: at sendMailer point"); }
	if (isset($mandrill)) return sendMandrill ($toPerson, $toEmail, $fromPerson, $fromEmail, $replyTo, $cc, $bcc, 0, $subj, $msg, $txt);  	
	else return sendMailer ($toPerson, $toEmail, $fromPerson, $fromEmail, $replyTo, $cc, $bcc, 0, $subj, $msg, $type); 
 } // function quikSendHTML ($toPerson, $toEmail, $fromPerson, $fromEmail, $cc, $bcc, $subj, $msg) {
 
 function sendMandrill($toPerson, $toEmail, $fromPerson, $fromEmail, $replyTo, $cc, $bcc, $priority=0, $subj, $html, $txt=null) { 
 	global $mandrill, $rmtk;
	try {
		$toArr = array('email' => $toEmail, 'name' => $toPerson, 'type' => 'to');
		if ($cc > "") $toArr[] = array('email' => $cc, 'name' => null, 'type' => 'cc');
		
#		if ($txt == "") $txt = null; #html2text($html);

		/* uncomment if used below 
		
		
		"attachments": [
            {
                "type": "text/plain",
                "name": "myfile.txt",
                "content": "ZXhhbXBsZSBmaWxl"
            }
        ],
     
		
	"attachments" => array(
        array(
            'content' => $attachment_encoded,
            'type' => "application/pdf",
            'name' => 'file.pdf',
        )	
		
		$globalArr = array('name' => 'merge1', 'content' => 'merge1 content');
		$mergeArr = array('rcpt' => 'recipient.email@example.com', 'vars' => array( 
						array('name' => 'merge2', 'content' => 'merge2 content')
               			)
            		);
		$attachArr = array('type' => 'text/plain', 'name' => 'myfile.txt', 'content' => 'ZXhhbXBsZSBmaWxl');
		$imageArr = array('type' => 'image/png', 'name' => 'IMAGECID', 'content' => 'ZXhhbXBsZSBmaWxl');
		*/
		#if ($_SERVER['REMOTE_ADDR'] == '$rmtk') { print '<textarea rows=10 cols=100>'.$html.'</textarea>'; }
		
		
		$manSend = array(
        	'html' => $html,
	        'text' => $txt,
	        'subject' => $subj,
	        'from_email' => $fromEmail, 
	        'from_name' => $fromPerson,
	        'to' => array($toArr),
	        'headers' => array('Reply-To' => $fromEmail),
	        'important' => false,
	        'track_opens' => false,
	        'track_clicks' => false,
	        'auto_text' => true,
	        'auto_html' => null,
	        'inline_css' => null,
	        'url_strip_qs' => null,
	        'preserve_recipients' => null,
	        'view_content_link' => null,
	        'bcc_address' => $bcc,
	        'tracking_domain' => null,
	        'signing_domain' => null,
			/* // I tried to get these to work, but they were not working... need more testing (ALso uncomment above arrays as needed)
			'merge' => true,
	        'global_merge_vars' => array($globalArr),
	        'merge_vars' => array($mergeArr),
	        'tags' => array('password-resets'),
    	    'subaccount' => 'customer-123',
        	'google_analytics_domains' => array('example.com'),
	        'google_analytics_campaign' => 'message.from_email@example.com',
    	    'metadata' => array('website' => 'www.example.com'),
        	'recipient_metadata' => array( array('rcpt' => 'recipient.email@example.com', 'values' => array('user_id' => 123456))),
	        'attachments' => array($attachArr),
    	    'images' => array($imageArr),
			*/
	        'return_path_domain' => null
    );
	
	if ($attach > "") {
		
		//$attachment = file_get_contents($attach);
		$attachment = "This is a test of a simple text file line";
		$attachment_encoded = base64_encode($attachment);
		$aExt = "txt";
		$aName = "myfile.txt";

		switch ($atype) {
			case "pdf" : $attchTpe = "application/pdf"; break;
			case "txt" : $attchTpe = "text/plain"; break;
			default : bugger("W", "lC.1600 - invalid attachment type [$atype]", "You need to define the attachment type.");
		} // switch ($attach['type']) {
					
		$attachArr = array('type' => $attchTpe, 'name' => $aName, 'content' => 'ZXhhbXBsZSBmaWxl');
		$manSend['attachments'] = $attachArr;
	} // if (is_array($attachArr)) {

    $async = false;
	
    #$ip_pool = 'Main Pool';

	#$newTxt = iconv("utf-8", "ascii//TRANSLIT", $manSend['html']);
	$manSend['html'] = mb_convert_encoding($manSend['html'], 'ISO-8859-15', 'UTF-8');
	
	#if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36') { $manSend['subject'] .= " [$cares] characters."; $manSend['html'] = substr($manSend['html'],0,$cares); }
    $send_at = '2013-01-01 00:00:00';
#    $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at); // this is what wasn't working.
    #if ($_SERVER['REMOTE_ADDR'] == $rmtk) { print_r($manSend); }
    $result = $mandrill->messages->send($manSend);
	// return is [email] => recipient.email@example.com  [status] => sent [reject_reason] => hard-bounce [_id] => abc123abc123abc123abc123abc123
    #if ($_SERVER['REMOTE_ADDR'] == $rmtk) { print "<hr color=red size=20 />"; print_r($result); exit; }
	} catch(Mandrill_Error $e) {
	    // Mandrill errors are thrown as exceptions
		ticket("CmN.1810: Mandrill Error Occured ".get_class($e), "Error Message is: [".$e->getMessage()."]");
    	throw $e;
	}
	#if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36') bugger ("A", "result from Mandrill Send.1212", $result); 
	
	if ($result[0]['status'] == "sent") return true;
	elseif ($result[0]['status'] == "queued") {
		$file = "bulksend.txt";
		$action = date("Y-m-d H:i:s").": lC.1186 - Queued status okay. Confirm was sent for [$toPerson] with email [$toEmail].\n";
		file_put_contents($file, $action, FILE_APPEND | LOCK_EX);
		return true;
		#$sub = "lC.1186 - Queued status okay. Confirm was sent";
	  } // elseif ($result[0]['status'] == "queued") {
	elseif ($result[0]['status'] == "rejected" && $toEmail == $result[0]['email'] && !isset($result[1])) return $result[0]['reject_reason'];
	else {
		$item = "";
		if (isset($result[1])) $sub = "lC.1185 - Multiple Failed Mandrill Send Rows";
		else $sub = "cc.1086 Failed Single Mandrill Send";

		foreach ($result as $key => $val) {
			if (!isset($val)) $item .= "Undefined key <b>[".$val."]</b>";
			elseif (is_array($val)) {
				$item .= $key.' = ARRAY: ';
				foreach ($val as $keyA => $valA) {
				    $item .= $keyA.' = <b>'.$valA.'</b> || ';
				} // foreach ($val as $key1 => $val1) {
			  } // elseif (is_array($val)) {
			else $item .= $key.' = <b>'.$val.'</b> || ';
		} // foreach ($result as $key => $val) {
		$msg = "Mandrill send unsucessful: ".$item;
	    mail("ticket.support@cruglobal.freshdesk.com", $sub,$msg); 
		if ($result[0]['status'] == "queued") return true; // queued is okay.
	} // else {
	return $result;
 } //  function sendMandrill($toPerson, $toEmail, $fromPerson, $fromEmail, $replyTo, $cc, $bcc, $priority = 0, $subj, $html, $txt)

 function sendMailer($toPerson, $toEmail, $fromPerson, $fromEmail, $replyTo, $cc, $bcc, $priority=0, $subj= 'No Subject', $msg, $html) {
 	global $siteInfo,$loadDir,$rmtk;
	require_once ($loadDir."/Core/class.phpmailer.php"); // for sending from php
 	$mail = new PHPMailer;
	$mail->FromName = $fromPerson;
	$mail->From = $fromEmail;
	#if ($_SERVER['REMOTE_ADDR'] == '$rmtk') { $toEmail = "paul.konstanski@cru.org"; }
	$mail->addAddress($toEmail, $toPerson);  // Add a recipient
	$mail->addReplyTo($replyTo);
	if ($cc > "") $mail->addCC($cc);
	if ($bcc > "") $mail->addBCC($bcc);

	if ($html == "text") $mail->isHTML(false);                                  // Set email format to HTML
	else $mail->isHTML(true);

	$mail->Subject = $subj; #'Here is the subject';
	$mail->msgHTML($msg); // this sets up the messages

	$mail->SMTPDebug = 0; // 1 gives minor debugging  2 gives maximum debugging
	$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
	
	/*  THESE ARE OPTIONAL FIELDS IT DIDN"T WORK WHEN I TRIED TO FIGURE THESE OUT
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'mail.chloedog.org';  // Specify main and backup server
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = 'eventcore&intre.org';                            // SMTP username
	$mail->Password = 'Password1980';                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
	*/

	#  These are the required fields... converted above
	#$mail->From = 'eventcore@example.org';
	#$mail->FromName = 'EventCORE TEST';
	#$mail->addAddress('paul.konstanski@cru.org', 'Paul Konstanski');  // Add a recipient
	#$mail->addAddress('paul.konstanski@cru.org');               // Name is optional
	#$mail->addAddress('dave.fischer@gatewayfellowship.com');               // Name is optional
	#$mail->addReplyTo('info@examp.org', 'Information');
	#$mail->addCC('cc@example.com');
	#$mail->addBCC('bcc@example.com');

	#$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
	#$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	#$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	#$mail->isHTML(true);                                  // Set email format to HTML
	#$mail->Subject = $subj; #'Here is the subject';
	#$mail->Body    = $msg; #'This is the HTML message body <b>in bold!</b>';
	#$mail->AltBody = "";
/*

if ($_SERVER['REMOTE_ADDR'] == '$rmtk') { 
	$who = substr(strtolower($_SERVER['SERVER_NAME']),4,8);
    mail("paul.konstanski@cru.org","FULL TEXT MESSAGE DEFAULT - $who",$msg,"From: $fromEmail\n"); 
	print 'Send One at '.date('h:i:s').' from ['.$fromEmail.']<br />'; sleep(5);
	mail("paul.konstanski@cru.org","SIMPLE TXT MESSAGE DEFAULT - $who","THis is a simple message","From: $fromEmail\n");
	print 'Send Two at '.date('h:i:s').' from ['.$fromEmail.']<br />'; sleep(5);
	mail("paul.konstanski@cru.org","FULL TEXT MESSAGE DAD - $who",$msg,"From: dad@konstanski.com\n"); 
	print 'Send Three at '.date('h:i:s').' from DAD<br />'; sleep(5);
	mail("paul.konstanski@cru.org","SIMPLE TXT MESSAGE DAD - $who","THis is a simple message","From: dad@konstanski.com\n"); 
	print 'Send Four at '.date('h:i:s').' from DAD<br />'; 
} // if ($_SERVER['REMOTE_ADDR'] == '$rmtk') {
*/
	if(!$mail->send()) return false; 
	else return true;
 } // function testMailer

function sendMergeLttr($type, $row) {
	global $conFigs, $lang, $tt, $tl, $langTtl, $tot;
	
	### MINIMUM REQUIRED FIELDS TO SEND A BASIC LETTER  ##################
	###   |filename|stdy|id|firstname|lastname|email|
	### MINIMU REQUIRED TL FIELDS
	###    For All:  		$tl = getTT("pgttl|prehead|";
	###    For baseless		notstrt|pgttl|prehead|darkword|hello|introline
	###    For simple       mainbody|custlink|$tl = 

	$filename = $row['filename'];

	#bugger ("A", "tt.955 -- TT values", $tt); bugger ("A", "tl.955b TL Values", $tl); bugger ("A", "tl.955c - Language Ttitles", $langTtl); mydo ("with lang [$lang] and tot [$tot] and filename [$filename]");

	if (!isset($tot)) $tot = 0; 
	if (!isset($row['stdy'])) $stdy = 0; elseif ($row['stdy'] > "") $stdy = $row['stdy']; else $stdy = 0;
	if (!isset($row['last'])) $last = 0; elseif ($row['last'] > "") $last = $row['last']; else $last = 0;
	if (!isset($row['ldate'])) $lday = ""; elseif ($row['ldate'] == "") $lday = $tt['notstrt']; else $lday = timeIt($row['ldate'], "nice");
	$item = explode(".", $last);
	$lsn = $item[0];
					
	$file = file_get_contents ("includes/mail/".$filename);
	
	$popID = $row['id'];
	$toF = $row['firstname'];
	$toP = $toF.' '.$row['lastname'];
	$toE = $row['email'];
	$crtd = date("Y-m-d H:i:s");
	$url = sendVerCode($popID, $toP, $toE, true);
	$urlUn = $url.'&s=stop';
			
	// every file type has these settings
	$file = str_replace("||pgttl||", $tl['pgttl'], $file);
	$file = str_replace("||prehead||", $tl['prehead'], $file);
	if ($lang != "master") $ext = "&lang=$lang"; else $ext = ""; // lang extension
	if (isset($tl['darkword'])) $file = str_replace("||darkword||", $tl['darkword'], $file); // this is single dark word centered under logo
	if (isset($tl['hello'])) $file = str_replace("||firstname||", $tl['hello'].' '.$toF.':', $file); // customized salutation
	if (isset($tl['introline'])) $file = str_replace("||introline||", $tl['introline'], $file); // 

	// unique to a style
	if ($filename == "simple.html") { 
		$urlMo = $conFigs->siteSecure."/hello.php".$ext; // more info
		$urlBe = $url.'&s=invite';
		$urlSt = $url.'&s=settings';
		$file = str_replace("||mainbody||", $tl['mainbody'], $file);
		$file = str_replace("||custlink||", $tl['custlink'], $file);
	  } // if ($filename == "baseless.html") {	
	elseif ($filename == "baseless.html") { 
		$urlMo = $conFigs->siteSecure."/hello.php".$ext; // more info
		$urlBe = $url.'&s=invite';
		$urlSt = $url.'&s=settings';
		$file = str_replace("||mainbody||", $tl['mainbody'], $file);
		$file = str_replace("||custlink||", $tl['custlink'], $file);
		$file = str_replace("||urllink||", '<a href="'.$urlBe.'">'.$urlBe.'</a>', $file);
		$file = str_replace("||closer||", $tl['closer'], $file);
		$file = str_replace("||reply||", $tl['reply'], $file);
		$file = str_replace("||urlmore||", '<a href="'.$urlMo.'">'.$tl['showcls'].'</a>', $file);
	  } // if ($filename == "baseless.html") {	
	elseif ($filename == "twobutton.html") { 
		if ($type == "remndr" || $type == "lttrfour" || $type == "lttrfive" || $type == "lttrsix") {
			if (!isset($langTtl[$lang][$stdy][$lsn])) {
				$ttl = 	doQuery("SELECT title FROM lessons WHERE stdy = $stdy AND lessLang = '$lang' AND ref = $lsn", "iso");
				if ($ttl == "") bugger("W", "aD.244 - nonEnglish not yet ready for [$lang]", "For Study [$stdy] and Lesson [$lsn] It should never get here because language should be defined.");
				$langTtl[$lang][$stdy][$lsn] = $ttl;
	  		  } // if (!isset($langTtl[$lang])) {
			else $ttl = $langTtl[$lang][$stdy][$lsn];
			$urlOne = $url.'&s=study';
			$urlTwo = $url.'&s=settings';
			$file = str_replace("||mainbody||", $tl['mainbody'], $file);

			if ($type == "remndr" || $type == "lttrfour") {
				if ($row['freq'] == "day") $freq = $tl['pace_day'];
				elseif ($row['freq'] == "wk") $freq = $tl['pace_wk'];
				else $freq = $tl['pace_man'];
				$file = str_replace("||lstLess||", $tl['lesn'].' - '.$ttl, $file);
				$file = str_replace("||lstDate||", $lday, $file);
				$file = str_replace("||paceset||", $freq, $file);
			  } // if ($type == "remndr" || $type == "lttrfour") {
			elseif ($type == "lttrfive") $file = str_replace("||misttl||", '<b>'.$ttl.' (#'.$lsn.')</b>', $file);
			elseif ($type == "lttrsix")  $file = str_replace("||lstDate||", $lday, $file);

		  } // if ($type == "remndr") {
		elseif ($type == "lttrtwo" || $type == "lttrthree") {
			$urlOne = $url.'&s=begin';
			if ($type == "lttrtwo") $s = "postpone";
			elseif ($type == "lttrthree") $s = "settings";
			$urlTwo = $url.'&s='.$s;
			$file = str_replace("||mainbody||", $tl['mainbody'], $file);
		  } // elseif ($type == "lttrtwo") {
		else bugger("W", "aD.223 - Missing Type Action [$type]", "You need to define the specific actions for this letter.");		  

		$msSpc = ""; // for Microsoft space
		// button one
		$dummy = explode("||", $tl['buttOne']);
		$file = str_replace("||butt_one||", mailButtMaker($urlOne, $dummy[0]), $file);
		$file = str_replace("||butt_oneE||", $dummy[1], $file);
		$msSpc .= msftButtMaker($dummy[0], $urlOne, "#3E9329").chr(012);
		$msSpc .= '<p>'.$dummy[1].'</p><p>&nbsp;</p>'.chr(012);
		// button two

		$dummy = explode("||", $tl['buttTwo']);
		$file = str_replace("||butt_two||", mailButtMaker($urlTwo, $dummy[0]), $file);
		$file = str_replace("||butt_twoE||", $dummy[1], $file);
		$msSpc .= msftButtMaker($dummy[0], $urlTwo, "#4eaeeb").chr(012);
		$msSpc .= '<p>'.$dummy[1].'<p>&nbsp;</p></p>'.chr(012);
		// microsoft space
		$file = str_replace("||butt_msft||", $msSpc, $file);
				
		if ($type == "lttrtwo" || $type == "lttrthree") {
			$urlMo = $conFigs->siteSecure."/hello.php".$ext; // more info
			$tl['showcls'] = "Essentials24.org";
			$file = str_replace("||closer||", $tl['closer'], $file);
			$file = str_replace("||urlmore||", '<a href="'.$urlMo.'">'.$tl['showcls'].'</a>', $file);
		  } // if ($type == "lttrtwo" || $type == "lttrthree") 
		else $file = str_replace("||closer||", $tl['closer'], $file);
	  } // if ($filename == "twobutton.html") {	
	else bugger("W", "aD.235: fileName of [$filename] not defined.", "You need to define file actions");
	#print "For popID [$popID] the last date is [".$row['ldate']."]<br />";

	if (isset($tl['dontemail']) || isset($tl['unsub'])) {
		// this is the bottom unsubscribe section that all must have.
		$file = str_replace("||dontemail||", $tl['dontemail'], $file);
		$file = str_replace("||unsub||", '<a href="'.$urlUn.'">'.$tl['unsub'].'</a>', $file);
	} // if (isset($tl['dontemail']) || isset($tl['unsub'])) {
		
	// this puts it into the bulk mail sending queue
	#if ($lang != "00") $inphr = "utf8::insert"; else $inphr = "insert";
	$inphr = "insert"; // for mailchimp to work, this must be saved in Standard FORMAT, not UTF8	
	if ($lang == "master" || $lang == "00") $doLng = "en"; else $doLng = $lang;
	$tot++;	
#	doQuery("INSERT INTO blkwatch (bw_lang, bw_study, bw_lesson, bw_popID, bw_toP, bw_toE, bw_fromP, bw_fromE, bw_salute, bw_sub, bw_msg, bw_when, bw_type) VALUES ".
#			"('$doLng', $stdy, $lsn, $popID, '".sr($toP)."', '$toE', '".$row['fromP']."', '".$row['fromE']."', '".sr($toF)."', '".sr($row['sub'])."', '".sr($file)."', '$crtd', '$type')", $inphr);
	doQuery("INSERT INTO blkwatch (bw_lang, bw_study, bw_lesson, bw_popID, bw_toP, bw_toE, bw_fromP, bw_fromE, bw_salute, bw_sub, bw_msg, bw_when, bw_type) VALUES ".
			"('$doLng', $stdy, $lsn, $popID, '".addslashes($toP)."', '$toE', '".addslashes($row['fromP'])."', '".$row['fromE']."', '".addslashes($toF)."', '".addslashes($row['sub'])."', '".addslashes($file)."', '$crtd', '$type')", $inphr); 
	return 	"PopID [$popID] type [".$type."_".$doLng."] Study [$stdy] lesson [$lsn] Subject: ".substr($row['sub'],0,10)." To: $toP ($toE)<br />";
} // function sendMergeLttr ($tl)

 function ticket($sub, $msg, $from="", $shwHst=true) {
 	global $conFigs, $siteKey, $id, $mySess;
	if ($from == "") $from = $conFigs->siteSender;
 	if (strstr($from, "^")) {
	 	$dummy = explode("^", $from);
		$frmperson = $dummy[0];
		$frmemail = $dummy[1];
 	  } // if (strstr("^", $from)) {
	elseif (strstr($from, "@")) $frmemail = $from;
	else $frmperson = $frmemail = "";
 	if (validEmail($frmemail)) ; // no action, this is okay
	elseif (!validEmail($frmemail) && validEmail($conFigs->siteSender)) $frmemail = $conFigs->siteConEmail; 
	else $frmemail = "paul.konstanski@gmail.com";
	if (!isset($frmperson)) {
		$dummy = explode("@", $frmemail);
		$frmperson = str_replace(".", " ", $dummy[0]);
	} // if (!isset($frmperson)) {
	$ipr = "|".substr(str_replace(".", "", $_SERVER['REMOTE_ADDR']),-4);

	if ($shwHst) {
		$localized = "<hr /><p>Host: [".$_SERVER['HTTP_HOST']."] and URL [".$_SERVER['PHP_SELF']."|| </p><p>GET [";
		if (is_array($_GET)) foreach ($_GET as $key => $val) $localized .= $key.' ['.$val.']|-|';

		if (!isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = "";
		$localized .= "]</p><p>dbSrv [$conFigs->dbServer] siteKey [$siteKey] and ID [$id]</p>
					<p>ref is [".$_SERVER['HTTP_REFERER']."] javaOn [".$mySess->javaOn."] IP [".$_SERVER['REMOTE_ADDR']."]</p>";
		$localized .= "<p>Script: [".$_SERVER['SCRIPT_FILENAME']." and QueryString [".$_SERVER['QUERY_STRING']."]</p>";
		if (numbIt($id, "pos")) {
			$dummy = doQuery("SELECT email, CONCAT(firstname, ' ', lastname) AS nameline FROM popLog WHERE id = $id", "single", "", "poplog");
			$localized .= "<p>Contact for Logged In Person --  Name: <b>".stripslashes($dummy['nameline'])."</b> (".$dummy['email'].")</p>";
		} // if (numbIt($id, "pos")) {

		$pVals = "";
		if (is_array($_POST)) { 
			foreach ($_POST as $key => $val) {
				if ($key == "ccNumb" || $key == "gurCC") $val = ccHide($val, "short");
				$pVals .= $key.' ['.$val.']|>|'; 
			} // foreach ($_POST as $key => $val) {
		} // if (is_array($_POST)) {
		$sndTxt = $localized."<hr /><p>POST VALS: ".$pVals."</p><hr /><p>".$msg."</p><hr />";
	  } // if ($shwHst)	
	else $sndTxt = $msg;
#	quikSendHTML ("Support", "paul.konstanski@cru.org", $frmperson, $frmemail, "", "", $sub.' '.$ipr, $sndTxt, "html"); // this sends out the email
	quikSendHTML ("Support", "ticket.support@cruglobal.freshdesk.com", $frmperson, $frmemail, "", "", $sub.' '.$ipr, $sndTxt, "html"); // this sends out the email
 } // function ticket ()
 
 function mailChimp($type, $mergeVars, $chimp){
	global $loadDir;
	require_once ($loadDir."/shared/MCAPI.class.php");
    // grab your List's Unique Id by going to http://admin.mailchimp.com/lists/
    // Click the "settings" link for the list - the Unique Id is at the bottom of that page. 
	// grab an API Key from http://admin.mailchimp.com/account/api/

	$api = new MCAPI($chimp['api']);
    $list_id = $chimp['list'];
	$myEmail = $mergeVars['EMAIL'];
	#bugger ("A", "mergeVars [$type]", $mergeVars); #exit; mailchimptest
	if (!isset($mergeVars['etype'])) $mergeVars['etype'] = "html";
	if ($mergeVars['etype'] == "html") $eType = "EMAILTYPE_HTML"; else $eType = "EMAILTYPE_TEXT";
    if ($list_id == "") return "ERROR:nolst";
	if ($myEmail == "") return "ERROR:noemail";
	if (!isset($mergeVars['status'])) $mergeVars['status'] = "";
	if (!isset($mergeVars['since'])) $mergeVars['since'] = null;
	
	switch ($type) {
		case "members"  : return $api->listMembers($list_id, $mergeVars['status'], $mergeVars['since'], 0, 1000); break;
		case "mergeV"   : return $api->listMergeVars($list_id); break;
		case "lists"  : return $api->lists($list_id); break;
		case "groups" 	: return $api->listInterestGroupings($list_id); break;
		case "user"		: return $api->listMemberInfo($list_id, $myEmail); break;
		case "userID"   : $dummy = $api->listMemberInfo($list_id, $myEmail);
						  if ($dummy['success'] == 1) {
							if (count($dummy['data']) > 1) return false; 
							else return $dummy['data'][0]['data']['id'];
						  } // if ($dummy['success'] == 1) {
						  else return false;
						  break;
		case "add" 		: // to subscribe someone to a list 
	    	#function listSubscribe($id, $email_address, $merge_vars=NULL, $email_type='html', $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false) {
			if (isset($chimp['dopt'])) $double_optin = $chimp['dopt']; else $double_optin = true;
			if (isset($chimp['upex'])) $update_existing = $chimp['upex']; else $update_existing = false;
			if (isset($chimp['repl'])) $replace_interests = $chimp['upex']; else $replace_interests = true;
			if (isset($chimp['sndw'])) $send_welcome = $chimp['sndw']; else $send_welcome = false;

			$retval = $api->listSubscribe($list_id, $myEmail, $mergeVars , $eType, $double_optin, $update_existing, $replace_interests, $send_welcome);
		    if (!$retval) $retval = $api->listUpdateMember($list_id, $myEmail, $mergeVars, 'text', false); // if false, try an update
			if ($retval) return $api->listMemberInfo($list_id, $myEmail); // return successful api read
			break;
		case "update" : return $api->listUpdateMember($list_id, $myEmail, $mergeVars, '', true);	
		case "delete" : 
			if (isset($mergeVars['delete'])) $delete_member = $mergeVars['delete']; else $delete_member = false;
			if (isset($mergeVars['goodbye'])) $send_goodbye = $mergeVars['goodbye']; else $send_goodbye = true;
			if (isset($mergeVars['notify'])) $send_notify = $mergeVars['notify']; else $send_notify = true;
			return $api->listUnsubscribe($list_id, $myEmail, $delete_member, $send_goodbye, $send_notify);
		default : bugger("W", "mYf-mailchmp.1005", "MailChimp Actions for type [$type] is not defined.");	
	} // switch ($type) {
		
	#bugger ("A", "mYf.for Type [$type] the retVal is", $retval);
		
	if ($api->errorCode){
		echo "Unable to update member infoski!\n";
		echo "\tCode=".$api->errorCode."\n";
		echo "\tMsg=".$api->errorMessage."\n";
	  } 
	else echo "Returned: ".$retval."\n";
    return '<b>Error:</b>&nbsp; ' . $api->errorMessage;
 } // function mailChimp($type, $dataIn){

 
 function myMailChimp($actn, $dataIn, $myChimp="") { 
	global $conFigs, $arID;
	# LESSONS: 1ffd48c61f
	# GENERAL: 9666a295c8

	if (is_object($dataIn)) $dataIn = get_object_vars ($dataIn);
	$chimp = array("api" => $conFigs->MCapi, "list" => $conFigs->MClist); 
	if (isset($myChimp['api'])) $chimp['api'] = $myChimp['api']; 
	if (isset($myChimp['list'])) $chimp['list'] = $myChimp['list']; 
	if (isset($myChimp['sndw'])) $chimp['sndw'] = $myChimp['sndw']; else $chimp['sndw'] = 'false'; // to stop welcome
	if (isset($myChimp['dopt'])) $chimp['dopt'] = $myChimp['dopt']; else $chimp['dopt'] = 'false'; // to stop welcome
	
	if ($actn == "listInfo") {
		$data = array();
		$dummy = mailChimp("mergeV", $dataIn, $chimp);
		foreach ($dummy as $row) {
			$data[$row['name']] = array("type" => $row['field_type'], "tag" => $row['tag'], "fldID" => $row['id']);
			#print 'Field: <b>'.$row['name'].'</b> is <i>'.$row['field_type'].'</i> tagged as <red>'.$row['tag'].'</red> ('.$row['id'].')<br />';
		} // foreach ($dummy as $key => $val) {
		$dummy = mailChimp("groups", $dataIn, $chimp);
		if (!empty($dummy)) {
			foreach ($dummy as $row) {
				$data[$row['name']] = array("type" => $row['form_field'], "tag" => $row['name'], "fldID" => $row['id']);
				#print 'Field: <b>'.$row['name'].'</b> is <i>'.$row['field_type'].'</i> tagged as <red>'.$row['tag'].'</red> ('.$row['id'].')<br />';
			} // foreach ($dummy as $key => $val) {
		} // if (!empty)) {
		return $data;
	  } // if ($actn == "listInfo")
	elseif ($actn == "user") { // gets user info and returns as an array
		// dataIn required: EMAIL 
		$dummy = mailChimp("user", $dataIn, $chimp); 
		if ($dummy['success'] == 1) {
			if (count($dummy['data']) > 1) bugger("W", "myF.1057", "Multiple Users Returned... should only return ONE!");
			else $dummy = $dummy['data'][0];
			$data = array();
			foreach ($dummy as $key => $val) {
				if ($key == "merges" || $key == "geo") {
					foreach ($val as $key2 => $val2) {
						if ($key2 == "GROUPINGS") {
							foreach ($val2 as $row) {
								$data[$row['name']] = $row['groups'];
							} // foreach ($val2 as $row) {
						  } // if ($key2 == "GROUPINGS") {
						else $data[$key2] = $val2;
					} // foreach ($val as $key2 => $val2) {
				  } // if ($key == "merges") {
				elseif (empty($val)); // no action
				else $data[$key] = $val;  
			} // foreach ($dummy as $key => $val) {	
		  } // if ($dummy['success'] == 1) {
		elseif ($dummy['success'] == 0 && $dummy['errors'] == 1 && $dummy['data'][0]['error'] == "The email address passed does not exist on this list") return "";  
		else { // when we don't knwo the error
			$dtaRtn = $cmpRtn = "";
			if (is_array($dataIn)) {
				foreach ($dataIn as $key => $val) {
					$dtaRtn .= $key.' = '.$val.' |=| ';
				} // foreach ($dataIn as $key => $val) {
			  } // if (is_array($dataIn)) {
			else $dtaRtn = $dataIn;	  
			if (is_array($chimp)) {
				foreach ($chimp as $key => $val) {
					$cmpRtn .= $key.' = '.$val.' |=| ';
				} // foreach ($dataIn as $key => $val) {
			  } // if (is_array($dataIn)) {
			else $cmpRtn = $chimp;	  
			$bugMsg = "Essenstials Success Val: [".$dummy['success']."] and Error Numb: [".$dummy['errors']."] and Data Vals: <br /><br />";
			foreach ($dummy['data'] as $row) $bugMsg .= "Email => ".$row['email_address'].' || Error => '.$row['error'].' |::|';
			$bugMsg .= ':::::: DataIn ['.$dtaRtn.'] and chimp is ['.$cmpRtn.']<br />';
			ticket("Mail Chimp Unspecified Error.1163", strip_tags($bugMsg));
			if ($_SERVER['REMOTE_ADDR'] == '67.175.147.10') { 
				bugger ("A", "dummy.1165", $dummy);
				bugger ("A", "dummy.1165", $dataIn);
				die ("data error is [".$dummy['data'][0]['error']."]");
			} // if ($_SERVER['REMOTE_ADDR'] == '67.175.147.10') { 			
			$data = "";
		} // else { // when we don't knwo the error
		return $data;
	  } // if ($actn == "user") {
	elseif ($actn == "add") {
		// to determine fields for adding, uncomment the next line and then make note of field names
		#$dataIn = array("EMAIL" => "paul.konstanski@cru.org", "status" => "updated", "since" => $daysAgo); // just need to set up a basic dataIn entry
		#$dummy = myMailChimp("listInfo", $dataIn); bugger ("A", "lIstNInfo.1174", $dummy); exit;
		return mailChimp("add", $dataIn, $chimp);
	  } // elseif ($actn == "add"  
	elseif ($actn == "members") return mailChimp("members", $dataIn, $chimp); 
	elseif ($actn == "update") {
		$dummy = mailChimp("user", $dataIn, $chimp);
		if ($dummy['success'] == 1) return mailChimp("update", $dataIn, $chimp);  
		else { // when you ADD a record
			$chimp['sndw'] = 'false'; // to stop welcome
			$chimp['dopt'] = 'false'; // to stop double opt in
			return mailChimp("add", $dataIn, $chimp);
		} // else {
	  } // elsief ($actn == "update")
	elseif ($actn == "progress") {
		$dummy = mailChimp("update", $dataIn, $chimp);  

/*	    $merge_vars = Array( 
        'EMAIL' => $myEmail,
        'FNAME' => $dataIn['fname'], 
        'LNAME' => $dataIn['lname'],
		'COUNTRY' => $dataIn['cntry'],
		'EMAILTYPE' => $eType,
		'GROUPINGS' => $grpArr,
		'JOINED' => date("m/d/Y", mktime())
    );
*/		
	
	  } //  
	elseif ($actn == "delete") {
		return mailChimp("delete", $dataIn, $chimp);
	} // elseif ($actn == "delete
	else { bugger ("A", "dataIn", $dataIn); bugger("W", "myFntc.225", "mailChimp Actions for type [$actn] are not defined."); }
} // function myMailChimp($actn, $dataIn) { 

function shortWords($txt, $lngth) {
	if ($lngth <= 3) return $txt; // always return full text when less than 3
	if (strlen($txt) <= $lngth) return $txt;
	$dummy = str_word_count ($txt, 2);
	$rtn = "";
	foreach ($dummy as $key => $val) {
		if ($key >= $lngth) {
			if ($rtn > "") return rtrim($rtn, " ").'...';
			else return substr($txt,0,($lngth-3)).'...';
		} // if ($key > $lngth) {
		else $rtn .= $val.' ';
	} // foreach ($dummy as $key => $val) {
	return rtrim($rtn, " ").'...';
} // function shortWords($txt, $lngth) {

function brokegoogAPI($text, $target, $source="en") {
	
	// SUPPORT: https://groups.google.com/forum/#!forum/google-translate-api
	// Language codes https://developers.google.com/adwords/api/docs/appendix/languagecodes
	// https://developers.google.com/doubleclick-search/v2/standard-error-responses  Error Response Codes
	// https://console.developers.google.com/project/essentials24-go?duration=PT6H  (This is the actual LOGIN Account)
	if ($target == "pi") return "PI*".$text;
	
	$api_key = 'AIzaSyAA_yuOVk5dYIan-Nwp2AdZMuY6BxVKnhg';
	$rtn = "";
    $url = 'https://www.googleapis.com/language/translate/v2?key='.$api_key.'&q='.rawurlencode($text);
    $url .= '&target='.$target;
    if($source) $url .= '&source='.$source;
	$url .= '&format=text';
	
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);                 
    curl_close($ch);

	if(!mb_detect_encoding($response, 'UTF-8', true)) bugger("W", "lC.1487 - Google Translate Error", "error: no utf-8");
	 else { // when UTF-8
	  $obj = json_decode($response,true);
	  if($obj === null) bugger("W", "lC.1490 - Google Translate Decode Error", "error: json_decode failed (or google returned 'null')");
	 } // // when UTF-8
	
	if($obj != null) {
	    if(isset($obj['error'])) $rtn = "ERROR: ".$obj['error']['message'];
	    else { // when no error
        	$rtn = $obj['data']['translations'][0]['translatedText'];
	        if(isset($obj['data']['translations'][0]['detectedSourceLanguage'])) //this is set if only source is not available.
    	        $rtn .= "Detecte Source Languge : ".$obj['data']['translations'][0]['detectedSourceLanguage'];     
	    } // when no error
	} // if($obj != null) {
	else $rtn = "UNKNOW ERROR";
	#print 'At cc1444: with text ['.$text.'] the trans is ['.$rtn.']<br />';
	return $rtn;
 } // function brokegoogAPI
 
 function googAPI($text, $target, $source="en") {
	// SUPPORT: https://groups.google.com/forum/#!forum/google-translate-api
	// Language codes https://developers.google.com/adwords/api/docs/appendix/languagecodes
	// https://developers.google.com/doubleclick-search/v2/standard-error-responses  Error Response Codes
	// https://console.developers.google.com/project/essentials24-go?duration=PT6H  (This is the actual LOGIN Account)
	if ($target == "pi") return "PI*".$text;
	
	#$api_key = 'AIzaSyB4qYLR7RTgqyWK8ABhhpCjS0ZJLJoPZq0';
	$api_key = 'AIzaSyAA_yuOVk5dYIan-Nwp2AdZMuY6BxVKnhg';
	$rtn = "";
    $url = 'https://www.googleapis.com/language/translate/v2?key='.$api_key.'&q='.rawurlencode($text);
    $url .= '&target='.$target;
    if($source) $url .= '&source='.$source;
	$url .= '&format=text';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	// Two above SSL line is for my localhost, may be it's already auto-set in your server   
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);       
    curl_close($ch);
    $obj = json_decode($response,true);
    if(array_key_exists('data', $obj)) {  // If the translating done well, the obj will have ['data'], unless it don't contain that key 
		$rtn = $obj['data']['translations'][0]['translatedText'];
		$lttrs = array("A", "B", "H", "I", "L", "O", "P", "S", "U");
		foreach ($lttrs as $upr) {
			$lwr = strtolower($upr); 
			$rtn = str_replace("<".$upr, "<".$lwr, $rtn);
			$rtn = str_replace($upr.">", $lwr.">", $rtn);
		} // foreach ($lttrs as $key) { 
		$rtn = str_replace("</ ", "</", $rtn); // this fixes something that causes problems...
    	return $rtn; 
	  } // if(array_key_exists('data', $obj)) {  // If the translating done well, the obj will have ['data'], unless it don't contain that key 
    else 
    	return "-trans wrong! for $target (".substr($text,0,20).")-"; // You can change it to blank ""
 } // function googAPI

function bibleGrab($type, $ref, $lang, $loctr) {
	global $loadDir;
	// this sets up the 
	$vS = 0; $vE = 999; // default for whole chapter
	$rtn = "";
	if (strstr($ref, " ")) {
		$item = explode(" ", $ref);
		if (count($item) == 2) {
			$book = bibleAbbr($item[0]);
			$refnc = $item[1];
			$line = explode(":", $refnc);
		  } // if (count($item == 2) {		
		elseif (count($item) == 3) { // this is when there is a preface  
			$book = $item[0].bibleAbbr($item[1]);
			$refnc = $item[2];
			$line = explode(":", $refnc);
		  } // elseif (count($item) == 3) { // this is when there is a preface  		
		$chpt = $line[0];
		if (strstr($line[1], "-")) {
			$item = explode("-", $line[1]);
			$vS = $item[0];
			if (numbIt($item[1], "pos")) $vE = $item[1]; else $vE = $vS;
		  } // if (strstr($line[1], "-")) {
		else $vS = $vE = $line[1];
	  } // if ($ref > "") 
	elseif ($type == "lang") {
		$langCde = substr($ref,0,3); // get the Language Code
		$rtnArr = array();
	  } // elseif ($type == "lang") {
	else bugger("W", "cOm.1840 - Undefined Action for [$ref]", "Not sure what to do when ref erence not formatted correctly at Locator [$loctr]");  

	$damID = doQuery("SELECT la_bible FROM language WHERE la_code = '$lang'", "iso", "", "poplog");
	if (substr($damID,0,6) == "ERROR:") bugger("W", "lang.1873 - Error", "With DAM ID: [$damID] and Locator [$loctr]");
	elseif ($damID == "") return "needAPI"; // sends warning back
	
	#mydo("REfer is [$ref] with book [$book] and chapter [$chpt] and start verse is [$vS] and end is [$vE] and DamID [$damID]");
	#$url = "http://www.bible.is/ENGKJV/Gen/1";
	$url = "http://www.bible.is/".$damID."/".$book."/".$chpt; 
   	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   	$response = curl_exec($ch);                 
    curl_close($ch);
	#if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36') { print 'RAW RESPONSE IS for lang ['.$lang.'] and URL ['.$url.']: <textarea rows=20 cols=100>'.$response.'</textarea><hr />'; var_dump(json_decode($response)); exit;		}

	if ($response == "[]") $response = "";
	elseif ($response > "") {
		$item = explode('<article id="bible-article">', $response);
		$item = explode('</article>', $item[1]);
		$bible = explode('<span class="verse-container" id="verse-container', $item[0]);
		foreach ($bible as $key => $row) {
				if ($key < $vS) continue; // just skip the o entry
				elseif ($key > $vE) continue;
				$item = explode('<span class="verse-text">', $row);
				$item = explode('</span></span></span></span>', $item[1]);
				$rtn .= $item[0].' ';
				#print '<br />For Key ['.$key.']: <textarea rows=3 cols=100>'.$vrs.'</textarea>';
			} // 
	} // else if response returned something...
	return $rtn;
} // function bibleGrab($type, $ref, $lang, $loctr) {

 function bibleAPI($type, $ref, $lang, $loctr) {
	global $loadDir;
	#$url = "http://dbt.io/library/chapter";
	# FOR HELP WITH KEY CONCEPTS: http://www.digitalbibleplatform.com/docs/core-concepts/
	#  FOR HELP WITH USER FLOWS  https://www.digitalbibleplatform.com/docs/user-flows/
	if ($type == "text" || $type == "book") { $url = "http://dbt.io/text/verse"; $ext = "N2ET"; }
	elseif ($type == "list") { $url = "http://dbt.io/library/book"; $ext = "N2ET"; }
	elseif ($type == "audio") bugger("W", "cMn.1828 - Audio Failure", "Actions for Audio Translation not yet defined."); #$url = "http://dbt.io/audio/path";
	elseif ($type == "lang") $url = "http://dbt.io/library/volume";
	else bugger("W", "lC.1454 - Bible Action Error [$type]", "You need to define action for this type of action at Locator [$loctr].");
	$api = "2c6c13d5a3c0ac6271acf84204fff53e";
	$vS = $vE = $mU = "";
	if (strstr($ref, " ")) {
		$item = explode(" ", $ref);
		if (count($item) == 2) {
			$book = bibleAbbr($item[0]);
			$refnc = $item[1];
			$line = explode(":", $refnc);
		  } // if (count($item == 2) {		
		elseif (count($item) == 3) { // this is when there is a preface  
			$book = $item[0].bibleAbbr($item[1]);
			$refnc = $item[2];
			$line = explode(":", $refnc);
		  } // elseif (count($item) == 3) { // this is when there is a preface  		
		$chpt = $line[0];
		if (strstr($line[1], "-")) {
			$item = explode("-", $line[1]);
			$vS = $item[0];
			if (numbIt($item[1], "pos")) $vE = $item[1]; else $vE = $vS;
		  } // if (strstr($line[1], "-")) {
		else $vS = $vE = $line[1];
	  } // if ($ref > "") 
	elseif ($type == "lang") {
		$langCde = substr($ref,0,3); // get the Language Code
		$rtnArr = array();
	  } // elseif ($type == "lang") {
	else bugger("W", "cOm.1840 - Undefined Action for [$ref]", "Not sure what to do when ref erence not formatted correctly at Locator [$loctr]");  

	#require_once($loadDir."/Core/dbt.php");
	#$dbt = new Dbt ($api);
	$damID = doQuery("SELECT la_bible FROM language WHERE la_code = '$lang'", "iso", "", "poplog");
	if (substr($damID,0,6) == "ERROR:") bugger("W", "lang.1873 - Error", "With DAM ID: [$damID] and Locator [$loctr]");
	elseif ($damID == "") return "needAPI"; // sends warning back
	
	if ($type == "text" || $type == "book") $parms = "?key=".$api."&dam_id=".$damID.$ext."&book_id=".$book."&chapter_id=".$chpt;
	elseif ($type == "list") $parms = "?key=".$api."&dam_id=".$damID.$ext;
	elseif ($type == "lang") $parms = "?key=".$api."&language_code=".$langCde;
	$ext;
	if ($vS > "") $parms .= "&verse_start=".$vS;
	if ($vE > "") $parms .= "&verse_end=".$vE;
	if ($mU > "") $parms .= "&markup=".$mU;
	$parms .= "&v=2";
#	$parms = "?key=".$api."&dam_id=".$damID."&v=".$vrs;
	$url = $url.$parms;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);                 
    curl_close($ch);
	if ($response == "[]") $response = "";
	elseif ($response > "") $bible = json_decode($response);

	#if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36') { print 'RAW RESPONSE IS for lang ['.$lang.'] and URL ['.$url.']: <textarea rows=20 cols=100>'.$response.'</textarea><hr />'; var_dump(json_decode($response)); exit;} 
	#mydo("1747: At bibleAPI with response <textarea rows=5 cols=120>$response</textarea>", false);

	if ($response == "" && $type == "text") return bibleGrab("text", $ref, $lang, $loctr.'_GRAB');
	#mydo("1750: AFTER bibleAPI with response <textarea rows=10 cols=120>$response</textarea>");
	if ($response == "") {
		#ticket ("lC.1619 - Failed Bible Translation Request [$lang]", "For type [$type] and damID [$damID] with book [$book] and [$chpt] and Locator [$loctr]");
		return "";
/*		v=2&key=' + dbtKey + '&media=text&delivery=web&language_family_code=' + escape(languageCode)
http://dbt.io/text/verse?dam_id=ENGESVOTET&book_id=gen&chapter_id=4&verse_start=1&verse_end=3&v=2
http://dbt.io/text/verse?key=2c6c13d5a3c0ac6271acf84204fff53e&dam_id=ENGESVOTET&book_id=gen&chapter_id=4&verse_start=1&verse_end=3&v=2


http://dbt.io/text/verse?key=2c6c13d5a3c0ac6271acf84204fff53e&dam_id=ENGKJVN1ET&book_id=1john&chapter_id=5&verse_start=11&verse_end=13&v=2
http://dbt.io/text/verse?key=2c6c13d5a3c0ac6271acf84204fff53e&dam_id=ENGKJVN1ET&book_id=1John&chapter_id=5&verse_start=11&verse_end=13&v=2	
*/		
	  } // if ($response == "") {
	elseif ($type == "lang") return $bible;	  
	#To decode, see this: http://php.net/manual/en/simplexml.examples-basic.php
	#$m = count($bible);
	$rtn = "";
	foreach ($bible as $row) {
		#if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36') { print_r($row); print '<hr />'; mydo("Invalid Bible Call for DamID [$damID]", false); }
		if (isset($row->Title)) {
			$msg = "Title is [".$row->Title."] with type [$type] ref [$ref] language [$lang] damID [$damID] URL [$url] Locator [$loctr] ";
			#mydo($msg);
			ticket("lC.1504: Invalid Bible API Call with ID [$damID]", $msg);
			return $row->Title;
		} // if (isset($row->Title)) {
		if ($type == "text") $rtn .= $row->verse_text;
		elseif ($type == "book") return $row->book_name.' '.$refnc;
		elseif ($type == "list") { print_r($row); print '<hr />'; }
		else ticket("lC.1660 - Bible Actions undefined", "YOu must define actions for type [$type] and lang [$lang] with ref [$ref] from locat [$loctr]");
	} // foreach ($bible as $row) 
	return $rtn;
 } // function bibleAPI($type, $ref, $lang) {

 function bibleAbbr($bkIn) {
	 switch ($bkIn) {
		 case "Genesis" : return "Gen";
		 case "Exodus" : return "Exod";
		 case "Leviticus" : return "Lev";
		 case "Numbers" : return "Num";
		 case "Deuteronomy" : return "Deut";
		 case "Joshua" : return "Josu";
		 case "Judges" : return "Judg";
		 case "Ruth" : return "Ruth";
		 case "Samuel" : return "Sam"; // for multi named books, leave off the number
		 case "Kings" : return "Kgs"; 
		 case "Chronicles" : return "Chr";
		 case "Ezra" : return "Ezra";
		 case "Nehemiah" : return "Neh";
		 case "Esther" : return "Esth";
		 case "Job" : return "Job";
		 case "Psalms" : return "Ps";
		 case "Proverbs" : return "Prov";
		 case "Ecclesiastes" : return "Eccl";
		 case "Song of Solomon" : return "Song";
		 case "Song of Songs" : return "Song";
		 case "Isaiah" : return "Isa";
		 case "Jeremiah" : return "Jer";
		 case "Lamentations" : return "Lam";
		 case "Ezekiel" : return "Ezek";
		 case "Daniel" : return "Dan";
		 case "Hosea" : return "Hos";
		 case "Joel" : return "Joel";
		 case "Amos" : return "Amos";
		 case "Obadiah" : return "Obad";
		 case "Jonah" : return "Jonah";
		 case "Micah" : return "Mic";
		 case "Nahum" : return "Nah";
		 case "Habakkuk" : return "Hab";
		 case "Zephaniah" : return "Zeph";
		 case "Haggai" : return "Hag";
		 case "Zechariah" : return "Zech";
		 case "Malachi" : return "Mal";
		 case "Matthew" : return "Matt";
		 case "Mark" : return "Mark";
		 case "Luke" : return "Luke";
		 case "John" : return "John";
		 case "Acts" : return "Acts";
		 case "Romans" : return "Rom";
		 case "Corinthians" : return "Cor";
		 case "Galatians" : return "Gal";
		 case "Ephesians" : return "Eph";
		 case "Philippians" : return "Phil";
		 case "Colossians" : return "Col";
		 case "Thessalonians" : return "Thess";
		 case "Timothy" : return "Tim";
		 case "Titus" : return "Titus";
		 case "Philemon" : return "Phlm";
		 case "Hebrews" : return "Heb";
		 case "James" : return "Jas";
		 case "Peter" : return "Pet";
		 case "Jude" : return "Jude";
		 case "Revelations" : return "Rev";
	 	 default : return $bkIn;
	 } // switch
 } // funciton bibleAbbr

 function lgDrop($curLang, $flgs = true) {
	$lgOpts = "";
	$dummy = doQuery("SELECT la_code, la_name, la_display, la_flag, la_place FROM language WHERE la_stat LIKE 'yes' ORDER BY la_name", "utf8::keyFirst", "", "poplog");
	foreach ($dummy as $key => $row) {
		if ($key == "en" && $curLang == "00") $sel = " SELECTED"; elseif ($key == $curLang) $sel = " SELECTED"; else $sel = "";
		if ($row['la_display'] == "") { // this updates the language file
			$row['la_display'] = googAPI($row['la_name'], $key);
			doQuery("UPDATE `language` SET la_display = '".$row['la_display']."' WHERE la_code = '$key' LIMIT 1", "update", "", "poplog");
		} // if ($row['la_display'] == "") {
		if ($sel > "") $dsply = $row['la_display'];
		elseif ($row['la_display'] == ucfirst($row['la_name'])) $dsply = $row['la_display'];
		else $dsply = $row['la_display'].' - '.ucfirst($row['la_name']);
		if ($flgs) $flgs = 'data-iconurl="images/flag/'.$row['la_flag'].'.png"'; else $flgs = '';
		$lgOpts .= '<option value="'.$key.'" class = "'.strtolower($row['la_name']).'" '.$flgs.$sel.'>'.$dsply.'</option>';
	} // foreach ($dummy as $key => $row) {
	return $lgOpts;
 } // function lgDrop
 
 function tierArr ($strngIn, $sepA="^", $sepB="!:") {
 	// this takes in a string that has two separators in it.  The first separator separates out the first group, the second separator does the second.
	// what is returned is an array
 	$rtnArr = array();
	$dummy = explode($sepA, $strngIn);
	if (is_array($dummy)) {
		foreach ($dummy as $row) {
			$line = explode($sepB, $row);
			if ($line[0] > "") $rtnArr[$line[0]] = $line[1];	
		} // foreach ($dummy as $row) {
	} // if (is_array($dummy)) {
	return $rtnArr;
 } // function tierArr ($arrIn, $sepA, $sepB) {

 function subval_sort($arrA, $subkey, $sort="sort") { // sort array array_sort array sort
    foreach($arrA as $key=>$val) $arrB[$key] = strtolower($val[$subkey]);

    if($arrB) {
        $sort($arrB);
        foreach($arrB as $key=>$val) {
            $arrC[] = $arrA[$key];
        }
        return $arrC;
    } // if($arrB) {
 } // function subval_sort($arrA, $subkey, $sort="sort") {

 function validEmail($email) {
	 return filter_var($email, FILTER_VALIDATE_EMAIL) 
       	&& preg_match('/@.+\./', $email) 
        && !preg_match('/@\[/', $email) 
        && !preg_match('/".+@/', $email) 
        && !preg_match('/=.+@/', $email);
 } // function validEmail($email)
 
 
function quantile($curPos, $group, $totSize) {
	// $curPos = current position (to determine which group you are in)
	// $group = the size iterations of the group
	// $totSize = total size of sample to knoww when you're done.
	$iterations = ceil($totSize / $group);
	if ($curPos > $totSize) return $iterations;
	elseif (numbIt($curPos, "not")) return 1;

	for ($x = 0; $x < $iterations; $x++) {
		for ($i = 0; $i <= $totSize; $i++) {
			if (($i > $group * ($x)) && ($i <= $group * ($x+1))) {
				$isGrp = $x+1;
				if ($curPos == $i) return $isGrp;
			}
		} // for ($i = 0; $i < $totSize; $i++) {
	} // for ($x = 0; $x < $iterations; $x++) {
	return 0; // if you get this far it is an error and it returns zero
 } // function quantiles
 
   function ccHide($cardNumb, $disType="") {
	// cardNumb is the full number in
	// disType = what to display; long = is both type and show;  text leaves off the extra text warnings...
	if ($cardNumb == "CheckSend") { $cardNumb = 9; $head = '<font class="msgHead"><b>Note</b>:</font> '; }
	  else $head = '<font class="msgHead"><b>Opps...</b>:</font> ';
	if ($disType == "short") $head = '';
	
	$cardNumb = str_replace(" ", "", $cardNumb);
	switch (substr($cardNumb,0,1)) {
		case 3 : $type = 'AmExp: '; $show = 'XXXX XXXXXX X'.substr($cardNumb,11); break;
		case 4 : $type = 'Visa: ';
			if (strlen($cardNumb) == 16) $show = 'XXXX XXXX XXXX '.substr($cardNumb,12); 
				else $show .= 'XXXX XXXX XX '.substr($cardNumb,10); 
			break;
	   	case 5 : $type = 'MC: '; $show = 'XXXX XXXX XXXX '.substr($cardNumb,12); break;
	   	case 6 : $type = 'Disc: '; $show = 'XXXX XXXX XXXX '.substr($cardNumb,12); break;
		case 9 : $show = $head.'Guaranteed by Check'; break; // uses 9 for mailed in check...
		case "" : $show = $head.'Credit Card Information Missing!'.$cardNumb; break;
	    default : $show = $head.$cardNumb;
	} // switch (substr($cardNumb,0,1)) 
	if ($disType == "short" && $cardNumb > "") return $show;
	elseif ($disType == "short") ; // do nothing when nothing is there
	elseif ($disType == "long") return $type.$show;
	  else return $show;
 } // ccHide ($cardNumb)
 
 function gcxValidate($a) {
	global $loadDir;
	include_once($loadDir.'/CAS/CAS.php');
	phpCAS::setDebug();
	if ($a == "rl") phpCAS::client(CAS_VERSION_2_0,'signin.cru.org',443,'cas',false); // initialize phpCAS
	else phpCAS::client(CAS_VERSION_2_0,'thekey.me',443,'cas',false); // initialize phpCAS
	phpCAS::setNoCasServerValidation(); // no SSL validation for the CAS server
	phpCAS::forceAuthentication(); // force CAS authentication
	// phpCAS::getUser(); // get user info...
	// at this step, the user has been authenticated by the CAS server
	// and the user's login name can be read with phpCAS::getUser().
	// this puts the return value into an array
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, phpCAS::getServiceResponse(), $tags);
	xml_parser_free($parser);

    #bugger ("A", "gCx.26", $tags);
	$gcx = array();
	foreach ($tags as $row) { // this sets up the gcx array
		if ($row["type"] == "complete") $gcx[$row["tag"]] = $row["value"];
	} // end foreach ($tags as $row)
	// the gcx array is now used by the next phase of the script
	return $gcx;
 } // end function gcxValidate($a)
  
 function fbValidate ($srcRoot="images/auth") {
#	global $facebook, $conFigs, $loadDir, $id;
	global $conFigs, $id, $mySess;
	$fbParms = $conFigs->fb; // this is a shortcut for this function only
	$fbParms['outlink'] = $conFigs->siteSecure."/index.php?a=social_fb";  // FORK: Just change this in the APP when you're ready.

	if (isset($_REQUEST["code"])) $code = $_REQUEST["code"];
	else $code = "";
	$sParms = 'email,user_birthday,user_hometown,user_location,';
	if ($code > "") {
     	$token_url = "https://graph.facebook.com/oauth/access_token?"."client_id=".$fbParms['appId'].
					 "&redirect_uri=".urlencode($fbParms['outlink'])."&client_secret=".$fbParms['secret']."&code=".$code;
		$return = false;
		try {
			$subj = $msg = "";
			$response = run_curl($token_url);
			$item = json_decode($response);
			if (isset($item->error)) {
				$item = $item->error;
				$msg = 'FB Validate Error is ['.$item->message.'] of type ['.$item->type.'] with code ['.$item->code.']. Full IP is ('.$_SERVER['REMOTE_ADDR'].')<br />';
				$subj .= "FB Validate Error.1695";
				mydo("lC.1794: With Sub [$subj] the msg is [$msg]");
			  } // if (isset($item->error)) {
			else { // when response is valid
			    $graph_url = "https://graph.facebook.com/me?".$response; 
				$dummy = json_decode(run_curl($graph_url));
				if ($dummy == "") $dummy = (object) array('email' => '');
				// FOR LISTING OF RETURNED FIELDS, See Evernote "Facebook / Gmail / Relay Return"
				if (!isset($dummy->email)) $dummy->email = "";
				if (!validEmail($dummy->email)) {
					$msg = "Invalid FB Email [".$dummy->email."]:<br />FB Return Vars: [$graph_url]: ";
					if (isset($dummy->username)) $dummy->email = $dummy->username."@facebook.com";
					else $dummy->email = "Unknown@facebook.com";
					$subj .= "FB Login.394";
					mydo("lC.1807: With Sub [$subj] the msg is [$msg]");
				} // if (!validEmail($user['email'])) {
		    } // when response is valid
			if (!isset($dummy)) $dummy = "";
			$item = "";
			$user = array();
			if ($dummy > "") {
				$keyMsg = "";
				foreach ($dummy as $key => $val) {
					if ($key == "education" || $key == "hometown" || $key == "quotes" || $key == "work" || $key == "languages") continue;   // skip these fields caus of complications
					elseif ($key == "bio" || $key == "link" || $key == "name" || $key == "timezone" || $key == "updated_time") continue;
					elseif ($key == "username" || $key == "verified" || $key == "favorite_athletes" || $key == "favorite_teams") continue;
					elseif ($key == "religion" || $key == "sports" || $key == "middle_name") continue;
					elseif ($key == "location") ; // no skip
					else $item .= "<b>$key</b>=$val || ";
					
					if ($key == "location") { $line = explode(",", $val->name); $user['city'] = $line[0]; $user['region'] = $line[1]; }
					elseif ($key == "locale") { $line = explode("_", $val); $user['language'] = $line[0]; $user['country'] = strtolower($line[1]); }
					else { // no double handled
						switch($key) {
							case "first_name" : $key = "firstname"; break;
							case "last_name"  : $key = "lastname"; break;
							case "birthday"   : $key = "birthdate"; $val = timeIt($val, "MDY"); break;
							case "gender"	  : $val = substr($val,0,1); break;
							case "email"	  : break;
							case "id"		  : break; 
							default : 
								if (is_array($val)) { $val = "array"; }
								$keyMsg .= "lC.1736 - undefined FB field [$key] With value of [$val]. Fix to exception or used field.<br />"; 
								unset($key); break;
						} // switch($key) {
						if (isset($key)) $user[$key] = $val;
					} // else no double handled.	
					if ($keyMsg > "") { 
						$msg .= '<br />'.$keyMsg; 
						if ($subj > "") $subj .= ' || ';
						$subj .= "FB Key Fields Error 1746";
					} // if ($keyMsg > "") { 
				} // foreach ($dummy as $key => $val) {
			} // if (is_object($dummy)) {
			if (isset($_REQUEST['state'])) $reqSt = $_REQUEST['state']; else $reqSt = "";
			if (!isset($mySess->state)) $mySess->state = "";
			if (isset($_SERVER['HTTP_REFERER'])) $srvRef = $_SERVER['HTTP_REFERER']; else $srvRef = "none.1823";
			if ($mySess->state != $reqSt) {
				if ($subj > "") $subj .= " || ";
				$subj .= "WARNING FB ERROR STATE! (lC.1405)";
				$msg .= " >>>> WARNING session state [".$mySess->state."] and Request State [".$reqSt."] DO NOT MATCH!!!<br /><br />
							   The states do not match. You may be a victim of CSRF.<br /><br />";
				$msg .= "The Current SITE is [".$_SERVER['HTTP_HOST']."] but the referring site was: [".$srvRef."]<br />"; 			   
			} // if ($mySess->state != $_REQUEST['state']) {
			if ($msg > "") ticket ($subj, $msg.'<hr />'.$item); // puts forth message and adds dump items.
			if (numbIt($user['id'], "pos")) return $user;
			else { header ('location: index.php'); exit; } // just return to index after sending message.
		} catch (Exception $e) {
			return false; 
		} // end try/catch (Exception $e) {
	  } // if ($code) {
	elseif (empty($code)) {
		$mySess->state = md5(uniqid(rand(), TRUE)); // CSRF protection
		toSess($mySess);
		$dialog_url = "https://www.facebook.com/dialog/oauth?client_id=".$fbParms['appId']."&redirect_uri=".urlencode($fbParms['outlink'])."&state=".$mySess->state."&scope=".$sParms;
		echo("<script> top.location.href='".$dialog_url."'</script>"); // this is where it goes out to get the authentication....
		exit; // to prevent from continuing...
	} // elseif (empty($code)) {
 } // function fbValidate
  
 function gplusValidate() {
	global $conFigs, $mySess;
	$dummy = $conFigs->gmail;
	$dummy = array("key" => "435190836109-mo7poai96835vjo4qi9uc3tgpjit7umm.apps.googleusercontent.com", "secret" => "2NSduzGnqR54cGYs-9R22JQk");
	$dummy['callback'] = $conFigs->siteSecure."/index.php?a=social_gm"; //FORK Fix this in config when ready...
	// Create a state token to prevent request forgery. Store it in the session for later validation.
	define('KEY', $dummy['key']);
	define('SECRET', $dummy['secret']);
	define('CALLBACK_URL', $dummy['callback']);
	define('AUTHORIZATION_ENDPOINT', 'https://accounts.google.com/o/oauth2/auth');
	define('ACCESS_TOKEN_ENDPOINT', 'https://accounts.google.com/o/oauth2/token');
	#define('SCOPE', 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email');
	define('SCOPE', 'openid%20email%20profile');
#	define('STATE', 'security_token%3D'.$mySess->gplus.'%26url%3D'.CALLBACK_URL);

	# NEW: https://developers.google.com/identity/protocols/OpenIDConnect
	# OLD: http://googlecodesamples.com/hybrid/index.php?openid_mode=checkid_setup&openid_identifier=google.com/accounts/o8/id
	if (!isset($_GET['code'])) {
	  	$mySess->gplus = md5(rand());
		toSess($mySess); 
		// construct the authentication request to Google 
		$auth_url = AUTHORIZATION_ENDPOINT
	    	      . "?redirect_uri=".CALLBACK_URL
		          . "&client_id=".KEY
		          . "&scope=".SCOPE 
		          . "&state=".$mySess->gplus
		          . "&response_type=code";
		//forward user to Gmail auth page
		header("Location: $auth_url");
		exit; // to keep the rest of the script from running
	  } // if ($_GET['code'] == "") {
	elseif (isset($_GET['code'])) {
		//construct POST object for access token fetch request
		$postvals = array('grant_type' => 'authorization_code',
        	          'client_id' => KEY,
            	      'client_secret' => SECRET,
                	  'code' => $_GET["code"],
	                  'redirect_uri' => CALLBACK_URL);

		$subj = $msg = "";
		//get JSON access token object (with refresh_token parameter)
		$return = run_curl(ACCESS_TOKEN_ENDPOINT, 'POST', $postvals);
		$token = json_decode($return);

		if (isset($token->error)) {
			if ($token->error == "invalid_grant") ; // 
			else { // for other errors
				$msg = 'GPlus Error is ['.$token->error.'] described as ['.$token->error_description.']<br />';
				$subj .= "GPlus Validate Error.1810";
				mydo("At error spot 1810 with subjet [$subj] and msg [$msg].<br />This normally would send ticket and return to index.php");
				ticket ($subj, $msg);
			} // else { // for other errors				
			header ('location: index.php'); exit;
		  } // if (isset($item->error)) {
		elseif (isset($token->access_token)) { // when response is valid
			$headers = array("Accept: application/atom+xml");
			$contact_url = "https://www.googleapis.com/plus/v1/people/me?access_token=$token->access_token";
			$dummy = json_decode(run_curl($contact_url, 'GET', $headers)); // this returns the result...
			$item = "";
			$user = array();
			if ($dummy > "") {
				$keyMsg = ""; 
				foreach ($dummy as $key => $val) {
					if ($key == "kind" || $key == "etag" || $key == "objectType" || $key == "image" || $key == "cover") continue;   // skip these fields caus of complications
					elseif ($key == "displayName" || $key == "url" || $key == "isPlusUser" || $key == "circledByCount" || $key == "verified") continue;
					elseif ($key == "occupation" || $key == "organizations" || $key == "placesLived" || $key == "domain") continue;
					elseif ($key == "relationshipStatus") continue;
					elseif ($key == "verified_email") continue;
					elseif ($key == "name") ; // okay arrays to pass
					elseif (is_object($val)) { bugger ("A", "forKey [$key] the Array is", $val); exit; }
#					else $item .= "<b>$key</b>=$val || ";
					
					switch($key) {
						case "name"	:
							if (isset($val->givenName)) $user['firstname'] = $val->givenName;
							if (isset($val->familyName)) $user['lastname'] = $val->familyName;
							unset ($key); break;
						case "emails" : 
							$line = $val[0];
							$user['email'] = $line->value;
							unset($key); break;
						case "language" : break;
						case "skills" : break; // photography, photo and video editing, cooking
						case "braggingRights" : break; // one of the first batch to finish quarterm on time
						case "aboutMe" : break;	// photogtapher-cook during his spare time. the internet is his office and playground. follower of Jesus
						case "tagline" : break; // YOLO
#						case "birthday"    : $val = timeIt($val, "MDY"); break;
						case "gender"	   : $val = substr($val,0,1); break;
#						case "locale"      : $key = "language"; break;
						case "id"			: break;
						default : 
							if (is_array($val)) { 
								$dm = "ARRAY: ";
								foreach ($val as $keyDM => $valDM) $dm .= $keyDM.'='.$valDM.' || '; 
								$val = $dm;
								unset ($dm, $keyDM, $valDM);
							} // if (is_array($val)) { 
							$keyMsg .= "lC.1853 - undefined GM field [$key] With value of [$val]. Fix to exception or used field.<br />"; 
							unset($key); break;
					} // switch($key) {
					if (isset($key)) $user[$key] = $val;
					if ($keyMsg > "") { 
						$msg .= '<br />'.$keyMsg; 
						if ($subj > "") $subj .= ' || ';
						$subj .= "GooglePlus Key Fields Error";
					} // if ($keyMsg > "") { 
				} // foreach ($dummy as $key => $val) {
			} // if (is_object($dummy)) {
			if (isset($_REQUEST['state'])) $reqSt = $_REQUEST['state']; else $reqSt = "";
			if (!isset($mySess->gplus)) $mySess->gplus = "";
			if (isset($_SERVER['HTTP_REFERER'])) $srvRef = $_SERVER['HTTP_REFERER']; else $srvRef = "none.1956";
			if ($mySess->gplus != $reqSt) {
				$subj = "WARNING gPlus ERROR STATE! (lC.1835)";
				$msg .= " >>>> WARNING session state [".$mySess->gplus."] and Request State [".$reqSt."] DO NOT MATCH!!!<br /><br />
							   The states do not match. You may be a victim of CSRF.<br /><br />";
				$msg .= "The Current SITE is [".$_SERVER['HTTP_HOST']."] but the referring site was: ".$srvRef."]<br />"; 			   
			} // if ($mySess->state != $_REQUEST['state']) {
			if ($msg > "") ticket ($subj, $msg.'<hr />'.$item); // puts forth message and adds dump items.
			if (isset($user['skills'])) unset($user['skills']);
			if (isset($user['aboutMe'])) unset($user['aboutMe']);
			return $user;
		  } // elseif (isset($dummy->access_token))
		else bugger("W", "lC.1836 - Undefined gMail Response", "If you ever get this you need to expand out the returned vars to see what's up.");
	} // elseif ($_GET['code'] > "") {
 } // function gplusValidate()

 /***************************************************************************
 * Function: Run CURL  - Used by GoogleLog
 * Description: Executes a CURL request
 * Parameters: url (string) - URL to make request to
 *             method (string) - HTTP transfer method
 *             headers - HTTP transfer headers
 *             postvals - post values
 **************************************************************************/
 function run_curl($url, $method = 'GET', $postvals = null){
    $ch = curl_init($url);
    
    //GET request: send headers and return data transfer
    if ($method == 'GET'){
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1
        );
        curl_setopt_array($ch, $options);
    //POST / PUT request: send post object and return data transfer
    } else {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $postvals,
            CURLOPT_RETURNTRANSFER => 1
        );
        curl_setopt_array($ch, $options);
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
 } // function run_curl($url, $method = 'GET', $postvals = null){

 /***************************************************************************
 * Function: Refresh Access Token - Used by GoogleLog
 * Description: Refreshes an expired access token
 * Parameters: key (string) - application consumer key
 *             secret (string) - application consumer secret
 *             refresh_token (string) - refresh_token parameter passed in
 *                to fetch access token request.
 **************************************************************************/
 function refreshToken($refresh_token){
    //construct POST object required for refresh token fetch
    $postvals = array('grant_type' => 'refresh_token',
                      'client_id' => KEY,
                      'client_secret' => SECRET,
                      'refresh_token' => $refresh_token);
    //return JSON refreshed access token object
    return json_decode(run_curl(ACCESS_TOKEN_ENDPOINT, 'POST', $postvals));
 }
 
 function cookieSet($id, $username, $days=0) { // uses cookie unless specfied otherwise...
 	if (numbIt($days, "pos")) { $expTime = time()+(3600*24*$days); $rmbr = "on"; }
	else { $expTime = time()-3600; $rmbr = ""; }
	$guid = doQuery("SELECT guid FROM popLog WHERE id = $id", "iso", "", "poplog");
	$cVal = $username.'|::|'.$guid.'|::|'.$id;
	setcookie('popval', $cVal, $expTime);
	doQuery("UPDATE popLog SET rmber = '$rmbr' WHERE id = $id LIMIT 1", "update", "", "poplog");
 } // function cookieSet() {
   
 function OLDcookieSet($cookieName, $id, $doRemb) { // uses cookie unless specfied otherwise...
	if (empty($cookieName) || empty($id)) bugger("W", "pop.21", "Cookie name [$cookieName] or ID [$popIn] cannot be blank!");
	elseif (strstr($id, ":")) { // if contains : means coming in with old popIn value... just find ID
		$c = substr_count($id, ":"); // finds position of ID  (old popVal was un:recno:id
		$dummy = explode(":", $id);
		$id = $dummy[$c]; 
	} // elseif (strstr($id, ":")) {

	if ($id == "erase" && $doRemb > "") bugger("W", "pOp.89", "Erase Cookie cannot have a value in the Remember Slot [$doRemb].");
	elseif ($id == "erase") ; // placeholder (handled below when doRemb is blank
	else { // when not doing an erase
		$curPop = doQuery("SELECT id, logKey, rmber FROM popLog WHERE id = ".$id, "single", "", "poplog");
		$cTpe = substr($curPop['logKey'],0,2);
		
		if ($cTpe == "un") $cookVal = $curPop['logKey'].':'.$id; // this sets up the cookie value
		else $cookVal = partnerType($cTpe); // for all other types
		if ($cookVal == "") bugger("W", "pOp.102", "Inbound cookVal cannot be blank!");
	} // else when not doing an erase 		

	if ($doRemb == "") {
		if (!isset($curPop['rmber'])) $curPop['rmber'] = "";
		if ($curPop['rmber'] > "") doQuery("UPDATE popLog SET rmber = '' WHERE id = ".$id, "update", "", "poplog");
		$expTime = time()-3600;
		// note you must erase cookies from the root directory, not a sub directory...
		setcookie($cookieName."[popLog]", "", $expTime);
		$_COOKIE[$cookieName]['popLog'] = ""; // this is needed to update current session
		unset($_COOKIE[$cookieName]['popLog']);
	  } // if ($popIn == "erase")
    else { // when doRemb is set
		if ($curPop['rmber'] == "") doQuery("UPDATE popLog SET rmber = 'on' WHERE id = ".$id, "update", "", "poplog");
    	$expTime = time()+(3600*24*30); // 30 days
		setcookie($cookieName."[popLog]", $cookVal, $expTime);
		$_COOKIE[$cookieName]['popLog'] = $cookVal; #$cookVal; // this is needed to update current session
	} // else / when doRemb is set...	
 } // function cookieSet() {
	 
 function menuMaker($arrayIn, $fldNameIn, $currentIn, $classIn="", $default="") {
  	// if $fldNameIn is BLANK, then it means you are doing a return option only!!!
  	// $arrayIn is the field names as the key and the display values for the array
  	// $fldNameIn is the nameValue of this field (w/o the H, M extension)
  	// $fldCntIn is a value of the current field count to be added to the end of the
  	global $rmtk;
	if ($default == "" && is_array($arrayIn)) {
		// if default isn't defined, it puts dashes equivalent to longest value in array
		$maxlen = max(array_map('strlen', $arrayIn));
		if ($maxlen < 3) $maxlen = 3; // set to minimum of three.  Can always override. 
		for ($i = 0; $i <= $maxlen; $i++) {
			#$default .= "&#150;";
			$default .= "-";
		} // for ($i = 0; $i <= 99; $i++)
	} // if ($default == "") {
	if ($classIn > "") $showClass = ' class="'.$classIn.'"'; else $showClass = "";

	$arr = array();

if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36xxx') {
bugger ("A", "with Default [$default]", $arrayIn, true);
}

	$optPaint = '<option value="">'.$default.'</option>';
	foreach ($arrayIn as $val => $disp) {
	  if ($val == $currentIn && $currentIn > "") $sel = ' SELECTED'; else $sel = "";
	  #if ($_SERVER['REMOTE_ADDR'] =='$rmtk') { print '<center>The current IN ['.$currentIn.'] and WIth val of ['.$val.'] and displ ['.$disp.'] the sel is ['.$sel.']</center>'; }
   	  $optPaint .= '<option value="'.$val.'"'.$sel.'>'.$disp.'</option>';
	} // end foreach ($useArray) as $row) {

	if ($fldNameIn == "") return $optPaint;
	else return '<select id="'.$fldNameIn.'" name="'.$fldNameIn.'"'.$showClass.'>'.chr(012).$optPaint.'</select>';
 } // end function menuMaker



	 
 #function menuMaker($arrayIn, $fldNameIn, $classIn, $currentIn, $translator="", $default="") {
 function menuMakerORIG($arrayIn, $fldNameIn, $currentIn, $classIn, $default="") {
  // if $fldNameIn is BLANK, then it means you are doing a return option only!!!
  // $arrayIn is the field names as the key and the display values for the array
  // $fldNameIn is the nameValue of this field (w/o the H, M extension)
  // $fldCntIn is a value of the current field count to be added to the end of the
  // $translator 
  global $rmtk;
	if ($default == "" && is_array($arrayIn)) {
		// if default isn't defined, it puts dashes equivalent to longest value in array
		$maxlen = max(array_map('strlen', $arrayIn));
		if ($maxlen < 3) $maxlen = 3; // set to minimum of three.  Can always override. 
		for ($i = 0; $i <= $maxlen; $i++) {
			#$default .= "&#150;";
			$default .= "-";
		} // for ($i = 0; $i <= 99; $i++)
	} // if ($default == "") {
	if ($classIn > "") $showClass = ' class="'.$classIn.'"'; else $showClass = "";

	$arr = array();

	if (!isset($arrayIn)) ; // no action
	elseif ($translator == "placed") { // placed is used when you want to have language transportablity
	 								   // it uses the placement in the array as the locator.
							   		   // key is you cannot change places once you've started compiling results
		$trans = 0;		  
		foreach ($arrayIn as $value) {
		  $trans++; // this adds one to the trans value...  gotta start with 1...
      	  $arr[$trans] = $value;
		} // end foreach ($useArray) as $row) {
	  } // end if ($type == "translator == "placed"
	elseif ($translator > "") {
		$currentIn = strtolower($currentIn);
		foreach ($arrayIn as $key => $value) {
		  $trans = strtolower($translator[$key]);
      	  $arr[$trans] = $value;
		} // end foreach ($useArray) as $row) {
	  } // end if ($type == "language" 
	elseif (is_array($arrayIn)) ; // no action
	elseif ($arrayIn == "phoneCode" || $arrayIn == "state") bugger("W", "cMn.651 - MenuBuildError", "You should never get to menuBuilder of [$arrayIn] any more");
/*	elseif ($arrayIn == "phoneCode") {
    	$dummy = doQuery("SELECT loc_name, loc_phone FROM location WHERE loc_type = 'cntry' ORDER BY loc_name");
		foreach ($phoneList as $row) {
			$arr[$row["loc_phone"]] = $row["loc_name"].' ('.$row["loc_phone"].')';
		} // end foreach ($useArray) as $row) {
	 } // if ($arrayIn == "phoneCode") {
    elseif ($arrayIn == "state") {
    	$dummy = doQuery("SELECT loc_name, loc_abb FROM location WHERE loc_type = 'state' ORDER BY loc_name");
		foreach ($dummy as $row) {
      		$arr[$row["loc_abb"]] = $row["loc_name"];
		} // end foreach ($useArray) as $row) {
	  } // end if $arrayIn == "state"
*/	  
	elseif ($arrayIn == "day") { 
  		for ($cnt = 1; $cnt <= 31; $cnt++ ) {
			if ($cnt < 10) $vCnt = "0".$cnt; else $vCnt = $cnt;
			$arr[$vCnt] = $cnt;
  		} // for ($cnt = 1; $cnt <= 31; $cnt++ ) {
	  } // elseif ($arrayIn == "day") {
    elseif ($arrayIn == "month") {
		// NON-STADARD use of translator...
		if (strstr($translator, ":|:")) $dummy = explode(":|:", $translator);
		 else $dummy = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		$id = 0;
		foreach ($dummy as $item) {
			$id++;
			if ($id < 10) $mStr = '0'.$id; else $mStr = $id;
			$arr[$mStr] = $item;
		} // end foreach ($dummy as $item) {
      } // elseif ($arrayIn == "month") {
    elseif (substr($arrayIn,0,4) == "year") {
		$rev=false;
		if (strstr($arrayIn, ":")) {
			$dummy = explode (":", $arrayIn);
			$arrayIn = $dummy[0]; // this should be the word "year"
			$yearOne = $dummy[1]; // this is the start year
			if ($dummy[2] > 0) $yearTwo = $dummy[2]; else $yearTwo = date('Y'); // this is the end year 
			if ($dummy[3] == "D") $rev=true; // this makes the year in descending order
		  } // if (strstr($arrayIn, ":")) {
		else { // when not a defined string, set default to 125 years ago for the start and this year for the year
			$yearOne = date('Y')-125;
			$yearTwo = date('Y');
		} // end default year
		$addVal = $yearTwo-$yearOne;
  		for ($cnt = $yearOne; $cnt <= $yearTwo; $cnt++ ) {
			if ($rev) {
				$putYr = $yearOne+$addVal;
				$addVal--;
			  } // end if ($rev)
			else $putYr = $cnt;	
			$arr[$putYr] = $putYr;
  		} // $cnt = $yearOne; $cnt <= $yearTwo; $cnt++ ) {
	  } // end if $arrayIn == "year"
	else ; // no action, filler  
	 
	if (!empty($arr) && is_array($arr)) $arrayIn = $arr; 

if ($_SERVER['REMOTE_ADDR'] == '68.45.32.36xxx') {
bugger ("A", "with Default [$default]", $arrayIn, true);
}

	$optPaint = '<option value="">'.$default.'</option>';
	foreach ($arrayIn as $val => $disp) {
	  if ($val == $currentIn && $currentIn > "") $sel = ' SELECTED'; else $sel = "";
	  #if ($_SERVER['REMOTE_ADDR'] =='$rmtk') { print '<center>The current IN ['.$currentIn.'] and WIth val of ['.$val.'] and displ ['.$disp.'] the sel is ['.$sel.']</center>'; }
   	  $optPaint .= '<option value="'.$val.'"'.$sel.'>'.$disp.'</option>';
	} // end foreach ($useArray) as $row) {

	if ($fldNameIn == "") return $optPaint;
	else return '<select id="'.$fldNameIn.'" name="'.$fldNameIn.'"'.$showClass.'>'.chr(012).$optPaint.'</select>';
 } // end function menuMaker
 
 function forceDownload($filename, $docPath) {
  	global $conFigs;
  	// script source was: http://www.elouai.com/force-download.php
  	// required for IE, otherwise Content-disposition is ignored
	if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

	#print 'For File ('.$filename.') the current dir is ['.getcwd().'] and siteRoot is ['.$conFigs->siteRoot.'] and localSite is ['.$_SESSION["localSite"].']<br>';
	#exit;

	$localfile = $docPath.'/'.$filename;

  	if (!is_file($localfile)) { // if file not found, then switch to notfound image
		header ('location: index.php?a=miss_down&i='.$filename); 
		exit; 
	} // end if (!is_file($localfile)) {
	  // addition by Jorg Weske
	  $file_extension = strtolower(substr(strrchr($filename,"."),1));
	  $filesize = filesize($localfile);
	  if (!$filesize > 0) bugger("W", "fMa.338", "Cannot get filesize for [".$localfile."]"); 
	  #print 'FileSize is ['.$filesize.']<br>';
	  #die ('for directory ('.getcwd().') localName is ['.$localname.']');

	  switch( $file_extension ) {
		case "pdf": $ctype="application/pdf"; break;
		case "exe": $ctype="application/octet-stream"; break;
		case "zip": $ctype="application/zip"; break;
	    case "doc": $ctype="application/msword"; break;
	    case "xls": $ctype="application/vnd.ms-excel"; break;
	    case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
	    case "css": $ctype="text/css"; break;
	    case "gif": $ctype="image/gif"; break;
	    case "png": $ctype="image/png"; break;
	    case "jpeg": $ctype="image/jpg"; break;
	    case "jpg": $ctype="image/jpg"; break;
	    #default: $ctype="application/force-download";
		default: $ctype="application/unknown";
	  } // switch ($file_extension)
	
	  header("Pragma: public"); // required
	  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT\n");
	  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	  #header("Content-type: application/zip;\n"); //or yours?
	  header("Content-Type: $ctype;\n");
	  header("Content-Transfer-Encoding: binary");
	  header("Content-Length: $filesize;\n");
	  header("Content-Disposition: attachment; filename=\"$filename\";\n\n");
	  readfile($localfile);
  } // end function forceDownload($filename)

} // else { // not LoadSimple if (!$loadSimple) {

// #+#+#+##+#+#+#+#+#+#+#+#+#+#+#+#+#+  END OF STANDARD CORE FUNCTIONS #+#+#+#+#+#+#+#+#+#+#+#+#+#+#+##+#+#
// #+#+#+##+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+##+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+##



// ###########################  INTERCEPTS & DECLARATIONS TO ALWAYS PERFORM  ######################################
$ref = getenv("HTTP_REFERER");
$mySrvr = ltrim($_SERVER['SERVER_NAME'], "www.");
$sN = ltrim($_SERVER['SCRIPT_NAME'], "/");
$c=$v=$_=""; // some universal variables to declare as empty
$pgBody=$pgTtl=""; // page settings
$errArr=array();
$rmtk = "68.45.32.36"; // default Arden for early bugger checks.

#========================  START LOAD OF ADDITIONAL CORES  ====================================
#require_once ($loadDir."/Core/demographs.php");
#require_once ($loadDir."/Core/tableWorks.php"); // for performing table functions
#========================  END of COMMON CORE LOADER   ====================================

$conFigs = (object) array('siteOwner' => '');
$retVals = $popVals = (object) array('id' => '');

//--> this loads the standard config file
if ($loadConfig) getConfigs($mySrvr);
if (!isset($conFigs->timezone)) $conFigs->timezone = 'America/Indianapolis';
date_default_timezone_set($conFigs->timezone);

#if (!isset($conFigs->mandrill) && is_file($loadDir."/Core/Mandrill.php")) { // this sets up the mandrill sender if so configured.
if (!isset($mandrill) && is_file($loadDir."/Core/Mandrill.php")) { // this sets up the mandrill sender if so configured.
	require_once ($loadDir."/Core/Mandrill.php"); 
	$mandrill = new Mandrill($conFigs->mandrill);
} // Me only 	
#mydo("The session ID is [".session_id()."]");
$mySess = doQuery("SELECT mySess FROM seszions WHERE phpsez LIKE '".session_id()."'", "iso", "", "poplog");
if ($mySess == "") { // if new visit with this session, enter it in the DB
	$myIP = get_client_ip();
	if (isset($_SERVER['HTTP_REFERER'])) $refr = str_replace("'", "", $_SERVER['HTTP_REFERER']); else $refr = ""; 
	if (isset($_GET['src'])) $src = $_GET['src']; else $src = "";
	$crtd = time();
	$mySess = serialize(array("myIP" => $myIP, "created" => $crtd, "id" => $id, "email" => "", "nameline" => "", "lang" => ""));
	$rtnVal = doQuery("INSERT INTO seszions (phpsez, mySrvr, mySess, java, ip, refurl, src_code, created) VALUES 
					  ('".session_id()."', '$mySrvr', '$mySess', 'yes', '$myIP', '".$refr."', '".$src."', '".$crtd."')", "insert", "", "poplog");
} // if ($mySess == "") { // if new visit with this session, enter it in the DB

$mySess = (object) unserialize($mySess);
if (!isset($mySess->javaOn)) $mySess->javaOn = "yes";
if (isset($_GET['j'])) $j = $mySess->javaOn = $_GET['j']; 
if ($mySess->javaOn == "no") $jS = false; 
else $jS = true;

if (isset($mySess->id)) $id = $mySess->id;
elseif (!isset($id)) $mySess->id = $id = -1;
if (isset($mySess->nolg)) { // this removes the nolg session if it's set and you are logged in
	if (numbIt($id, "pos") && numbIt($mySess->nolg, "pos")) unset($mySess->nolg);
} // if (isset($mySess['nolg'])) {

if (isset($mySess->stdy)) $stdy = $mySess->stdy; else $stdy = 0;
	
if (!isset($mySess->siteKey)) $mySess->siteKey = 0;
#bugger ("A", "Cookie62", $_COOKIE); bugger ("A", "Session", $_SESSION); bugger ("A", "mySess.62", $mySess); 
if (!isset($mySess->lang)) $mySess->lang = $l = "00"; 
elseif ($mySess->lang > "") $l = $mySess->lang;
else $l = $mySess->lang = "00";

if (!isset($mySess->langopts)) $mySess->langopts = $lgOpts = lgDrop($l);
else $lgOpts = $mySess->langopts;

// sets up initial log
if (!isset($mySess->navlog)) { $dummy = getTT("ttlLogin|ttlLogout", "lC.72"); $mySess->navlog = $dummy['ttlLogin']."^".$dummy['ttlLogout']; }
if (!isset($mySess->contnu)) { $dummy = getTT("continue", "lC.73"); $mySess->contnu = $dummy['continue']; }
if (!isset($mySess->sbansw)) { $dummy = getTT("sbansw", "lC.78"); $mySess->sbansw = $dummy['sbansw']; }
if (!isset($mySess->navttl)) { $dummy = getTT("about|mypro|media|contact|help|transltr|cnglang|grpmode", "lC.74"); $mySess->navttl = $dummy; }

if (!isset($mySess->myIP)) $mySess->myIP = get_client_ip();
if (!isset($mySess->created)) $mySess->created = time();
if (!isset($mySess->email)) $mySess->email = "";
if (!isset($mySess->nameline)) $mySess->nameline = "";

if (numbIt($mySess->siteKey,"pos")) ; // skip intercept
elseif ($ref == "") session_unset(); // this clears if you're coming from empty site
elseif ($ref > "") {
	$ref = explode("://", getenv("HTTP_REFERER"));
	$ref = explode("/", $ref[1]);
	if ($ref[0] != $_SERVER['HTTP_HOST']) session_unset(); // this clears session if you're coming from another site
} // elseif ($ref > "") {

if(isset($_GET['jump'])) { // when coming in as a jump...
	$dummy = explode("&", $_SERVER['QUERY_STRING']); // split string 
	$qry = ""; // just to make sure clear
	foreach ($dummy as $row) {
		if (substr($row,0,5) == "jump=") $url = substr($row,5).'?';
		else { // when not jump
		  switch (substr($row,0,1)) {
		  	case "a" : $qry .= $row.'&'; break;
			case "i" : $qry .= $row.'&'; break;
			default : die ("lOa.18 Jump Option for type [$row] is not defined. (".$_SERVER['QUERY_STRING'].")"); break;	
		  } // switch (substr($row,0,1)) {
		} // esle when not jump  
	} // end foreach $dummy as $row
	$url .= rtrim($qry, "&"); 
	header('location: '.$url); 
	exit; 
} // if($_GET['jump'] > "") {

// This is used to test if running script has authorization to use the "load" files 
if (isset($id)) $line = $id; else $line = 0;
$ra = $_SERVER['REMOTE_ADDR'];
if ($ra == "68.45.32.36" || // home  
	$ra == "166.171.184.193" || // ipad lite 
	#OLDHOME: 73.168.94.205 (Jan 15)  67.175.147.10 (Oct 14)
	$ra == "69.138.18.213" || // maggies
	$ra == "108.52.138.100" || // daniel
	$ra == "24.128.202.159" ||  // hyannis
	$ra == "71.43.146.106" || // pioneers FLORIDA
	$ra == "184.157.210.11" || // pound house 
	$ra == "50.79.203.170" || // town and country
	$ra == "68.205.238.62" // bostroms
	) $rmtk = $ra; // set to current if one of these
elseif ($line === 1 || $line == "1") $rmtk = $ra;	
else $rmtk = "";

// sets error reporting
if ($_SERVER['REMOTE_ADDR'] === $rmtk) {
	error_reporting( E_ALL | E_STRICT );
	ini_set('display_errors','On');
	unset($err);
	if (!isset($_SERVER['SERVER_ADDR'])) $_SERVER['SERVER_ADDR'] = ""; 
	if ($_SERVER['SERVER_NAME'] == "intre.org") $err = 2039; 
#	elseif ($_SERVER['SERVER_NAME'] == "essentials24.org") $err = 2039; 
#	elseif ($_SERVER['SERVER_NAME'] == "www.arrowheadregistration.org") $err = 2039; 
#	elseif ($_SERVER['SERVER_NAME'] == "chloedog.org") $err = 2039; // 
#	elseif ($_SERVER['SERVER_ADDR'] == "72.10.32.120") ; // 
	else $err = 22527;
	if (isset($err)) ini_set('error_reporting', $err);
  } // if ($_SERVER['REMOTE_ADDR'] === $rmtk) {
else ini_set('display_errors','Off');

$a=$e=$i=$j=$s=$x=""; // this is just to make sure all inbound variables are cleared...
foreach ($_GET as $item => $value) {
#  	if ($item == "fb_source" || $item == "code" || $item == "state" || $item == "count" || $item=="fb_bmpos" || $item == "ref" || $item == "fref" || $item == "signed_request") $item = "fb";
  	if ($item == "code" || $item == "state" || $item == "ticket") $item = "skip";
	if ($item == "authuser" || $item == "num_sessions" || $item == "hd" || $item == "prompt" || $item == "access_denied") $item = "skip";
	if ($item == "session_state") $item = "sr"; // this is how gplus sends back it's request
	
	switch ($item) {
		case "a" 	: $a = $value; break;
		case "e" 	: $e = $value; break;
		case "i" 	: $i = $value; break;
		case "j" 	: $j = $value; break;
		case "s" 	: $s = $value; break;
		case "x"    : $x = $value; break;
		case "sr"   : $signed_request = $value; break;
		case "skip" : break; // no action needed for skipped passed value
		case "error" : $errArr[] = $value; break;
		case "lang" : 
			if ($value == "en") $value = "00";
			$mySess->lang = $l = $value; 
			$mySess->machine = "ask";
			unset($mySess->navlog, $mySess->contnu, $mySess->sbansw, $mySess->navttl, $mySess->actsnc, $mySess->langopts); // unsets the languages specific options
			$mySess->langopts = lgDrop($l);
			toSess($mySess);
			$url = ltrim($_SERVER['SCRIPT_NAME'], "/");
			if (isset($_SERVER['QUERY_STRING'])) {
				$txt = "";
				$dummy = explode("&", $_SERVER['QUERY_STRING']);
				foreach ($dummy as $row) {
					$line = explode("=", $row);
					if ($line[0] != "lang") $txt .= $line[0]."=".$line[1]."&";
				} // foreach ($dummy as $row) {
			} // if (isset($_SERVER['REQUEST_URI']) {
			if ($txt > "") $url .= "?".rtrim($txt,"&"); 
			header ('location: '.$url); 
			exit;
		case "site" : header ('location: index.php?s='.$value); exit; // to reset everything
		case "stdy" : $mySess->stdy = $value; break; // this sets a new study
		case "src" : $src = $value; break;
		default : bugger("U", "lCr.177", "Invalid Query String [$item = $value]");
    } // end switch ($item)
} // foreach ($_GET as $item => $value) {

if ($_POST) $pVals = db_cleanPost($_POST); // if post exists, clean it
else $pVals = array();

if (!isset($mySess->stdy)) $mySess->stdy = 1; // default study if not set
$stdy = $mySess->stdy;

if (numbIt($id, "not")); // take not admin action
elseif (!isset($mySess->adOpts) && numbIt($id, "pos")) {
	if (!isset($siteKey) && numbIt($stdy, "pos")) $siteKey = $stdy;
	$dummy = doQuery("SELECT count(oID) FROM operations WHERE o_user = $id AND o_site = $siteKey AND o_status = 'Y'", "iso");
	if (numbIt($dummy, "pos")) $mySess->adOpts = $adOpts = $dummy;
  } // elseif (!isset($mySess->adOpts) && numbIt($id, "pos")) {
elseif (isset($mySess->adOpts)) $adOpts = $mySess->adOpts;
else mydo("lC.201: You should never get here with id [$id] and MySESS [$mySess->adOpts]");

toSess($mySess);