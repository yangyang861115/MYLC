<?
ob_start();
// ###########################  REQUIRED (at load) ######################################
// this must be at the head of every script...
$loadConfig = true; // to load configs
include ("CORRY.php");
#include ("../corry.eph");

if (empty($pVals)) $mySess->polltype = "";
elseif (!empty($pVals)) {
	if ($mySess->polltype == "") { // this handles the initial vote
		if (isset($pVals['choice'])) $pVals['choice'];
		else $choice = "error";
		$pollID = doQuery("INSERT INTO poll (pl_age, pl_gender, pl_country, pl_reason, pl_choice) VALUES
						  ('".$pVals['age']."', '".$pVals['gender']."', '".strtolower($pVals['cntry'])."', '".$pVals['reason']."', '$choice')", "insert");
		$mySess->pollID = $pollID;
		$mySess->polltype = "postpoll";
		#$query = "INSERT INTO poll";
		#$recNoDB = doDb ($query, "insert", "ssss", $pVals, "", "yes1234");
	  } // if ($pVals['form'] == "vote") {
	elseif ($mySess->polltype == "postpoll") {
		if (numbIt($mySess->pollID, "not")) bugger("W", "pO.23 - Invalid PollID [$mySess->pollID]", "In order to post name you must have poll ID Defined.");
		doQuery("UPDATE poll SET pl_email = '".$pVals['email']."', pl_name = '".$pVals['nameline']."' WHERE plID = $mySess->pollID", "update");
		$mySess->polltype = "postname";
	  } // elseif ($pVals['form'] == "postname") {
	else { bugger ("A", "pVals Shower 10", $pVals); bugger("W", "pO.23 - Invalid Form Type [".$pVals['form']."]", "You must define actions for this form type."); }
	
} // 
toSess($mySess);
$a = $mySess->polltype;
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- <meta property="og:url"                content="http://www.nytimes.com/2015/02/19/arts/international/when-great-minds-dont-think-alike.html" />
	<meta property="og:type"               content="article" />
	<meta property="og:title"              content="When Great Minds Don’t Think Alike" />
	<meta property="og:description"        content="How much does culture influence creative thinking?" />
	<meta property="og:image"              content="http://static01.nyt.com/images/2015/02/19/arts/international/19iht-btnumbers19A/19iht-btnumbers19A-facebookJumbo-v2.jpg" />
    -->
    
    <meta property="og:url"                content="http://yourlifecount.com/poll.php" />
	<meta property="og:type"               content="website" />
	<meta property="og:title"              content="What's In a Name?" />
	<meta property="og:description"        content="Offering you simple, practical and powerful ways to help you make your life count. There are four main elements towards this end: WALK, PRAY, CARE, SHARE." />
	<meta property="og:image"              content="http://yourlifecount.com/img/fbcover.jpg" />
    <meta property="fb:app_id" content="1189489097735194"/>
	<!-- <meta property="og:site_name" content="yourlifecount.com"/>
	<meta property="og:title" content="Making Your Life Count" />
	<meta property="og:description" content="xxx" />
	<meta property="og:type" content="xxx:photo">
	<meta property="og:url" content="http://www.example.com/content/xxx"/>
    <meta property="og:image" content="http://yourlifecount.com/img/fbcover.jpg"/> -->

    <title>Make/Making Your Life Count</title>

<!-- Favicon and Touch Icons
========================================================= -->
	<link rel="shortcut icon" href="favico.ico" type="image/x-icon">
	<link rel="icon" href="img/favico.ico" type="image/x-icon">
	<link href="img/apple-touch-icon-114x114.png" rel="apple-touch-icon-precomposed" sizes="114x114">
	<link href="img/apple-touch-icon-72x72.png" rel="apple-touch-icon-precomposed" sizes="72x72">
	<link href="img/apple-touch-icon.png" rel="apple-touch-icon-precomposed">

<!-- Bootstrap Core CSS
========================================================= -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">

<!-- Custom Fonts
========================================================= -->
    <link href='http://fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Roboto+Slab:100,200,300' rel='stylesheet' type='text/css'>    
    <!-- <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css" type="text/css"> Optional to serve Font Awesome locally --> 
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

<!-- Animate
========================================================= -->
    <link rel="stylesheet" href="css/animate.min.css" type="text/css">

<!-- Custom CSS
========================================================= -->
    <link rel="stylesheet" href="css/bootstrap-switch.css" type="text/css">
    <link rel="stylesheet" href="css/butter.css" type="text/css" />
    
    

    <script src="js/modernizr.custom.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->


</head>

<body id="page-top">
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand page-scroll" href="#page-top"><span class="glyphicon glyphicon-grain"></span> __________ your life count</a>
            </div>
        </div>
        <!-- /.container-fluid -->
    </nav>
   <? if (empty($pVals)) { ?>
    <header>
        <div class="header-content">
            <div class="header-content-inner">
                <span class="logo-icon glyphicon glyphicon-random"></span>
                <h4>What's in a Name?</h4>
                <span class="h2">Can you give us a minute to help us decide?</span>
                <hr>
                <p>We are launching a new Website and need to pick a name</p>
                <p>People want their life to count for something by making a difference. We want our domain name to tie into that purpose.</p>
                <a href="#poll" class="btn btn-primary btn-xl">Vote Now<i class="fa fa-arrow-down"></i></a></div>
        </div>
	</header>
   <? } // if (empty($pVals)) { ?>

<div class="diag-section s_3">
   <section class="bg-primary" id="poll">
   <? if (empty($pVals)) { ?>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2 text-center">
                    <h2 class="section-heading">To set the stage...</h2>
                    <hr class="light">
					<p class="justify text-faded-light">
                    	<span class="glyphicon glyphicon-grain"></span> The two options are: "Making Your Life Count" or "Make Your Life Count"<br />
                        <span class="glyphicon glyphicon-grain"></span> "Make" implies more of a imperative or call to action.<br />
                        <span class="glyphicon glyphicon-grain"></span> "Making" implies an ongoing activity and more of a process.<br />
                   </p>
                   <p class="justify text-faded-light">You can <b>help us</b> by letting us know which name you like better and why.</p>
                </div>
            </div>
        </div>
<? } elseif ($a == "postpoll") { // if (empty($pVals)) { ?>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2 text-center">
                    <h2 class="section-heading">Thank You</h2>
                    <hr class="light">
                    <p class="justify text-faded-light">Your response has been recorded. Would you like to find out the results or even participate in a focus group to talk about the
                    name and this project a little more.?</p>
					<p class="justify text-faded-light">Just give us your name and email and we will get back to you.</p>
                </div>
            </div>
        </div>
<? } elseif ($a == "postname") { // if (empty($pVals)) { ?>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2 text-center">
                    <h2 class="section-heading">We got it <?=$pVals['nameline']?></h2>
                </div>
            </div>
        </div>
<? } // else  { ?>
    </section>
<div class="diag-section s_1">
      <div class="diagonal diagonal_bottom pre_s_2"></div>
</div>
<div class="diag-section s_2">
      <div class="diag-section-content">
	   <? if (empty($pVals)) { ?>
            <section id="formhead">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-lg-offset-2 text-center">
                            <h2 class="section-heading">What do You Think?</h2>
                            <hr class="primary">
                            <div class="subtitle text-muted text-center">
                                If you have an alternative name you would like to offer, 
                                write it in the box. Thanks.
                            </div>
                        </div>
                 	</div><!-- end row -->
                </div> <!-- end container -->
            </section> <!-- end formhead section -->
            <section id="form">
            <!--- form begins -->
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-lg-offset-2 text-center">
                                <form name="voter" id="voter" method="post" novalidate>
									<div class="form-group">
								        <div class="row">
							              <div class="col-xs-3 selectContainer">
							                <label class="control-label">Age Group</label>
								                <select class="form-control" name="age">
								                    <option value="">Pick</option>
								                    <option value="u25">0 to 25</option>
								                    <option value="u40">26 to 40</option>
								                    <option value="u60">41 to 60</option>
								                    <option value="u99">61-125</option>
								                </select>
							              </div>
							              <div class="col-xs-3 selectContainer">
							                <label class="control-label">Gender</label>
								                <select class="form-control" name="gender">
								                    <option value="">Pick</option>
								                    <option value="f">Female</option>
								                    <option value="m">Male</option>
								                </select>
							              </div>
							              <div class="col-xs-6 selectContainer">
							                <label class="control-label">Country of Residence</label>
	                                            <select class="form-control bfh-countries" id="cntry" name="cntry" data-country="US"></select>
							              </div>
								        </div>
									</div>                                        
									<p>Choose (click) One of the Following</p>
									<div class="form-group">
                                        <div class="row">
                                       	  <div class="form-group col-xs-12">
                                        	<div class="btn-group" data-toggle="buttons">
										    	<label class="btn btn-primary">
											      <input name="choice" id="make" type="radio" value="make"> Make Your Life Count
											    </label>
											    <label class="btn btn-primary"><input name="choice" id="other" type="radio" value="other"> &bull; Other &bull; </label>
											    <label class="btn btn-primary">
											      <input name="choice" id="making" type="radio" value="making"> Making Your Life Count
											    </label>
											</div>
                                          </div>
                        				</div>                
								    </div>
                                    <div class="form-group">
								        <label class="control-label">Reason for Your Choice</label>
								        <textarea class="form-control" name="reason" rows="8"></textarea>
								    </div>
                                    <div id="success"></div>
                                    <div class="row">
                                        <div class="form-group col-xs-12">
                                            <button type="submit" name="response" class="btn btn-primary btn-xl wow tada"><b>Save</b> Your Vote<i class="fa fa-send"></i></button>
                                        </div>
                                    </div>
                                </form>
            <!--- end form -->
                            </div>
                        </div>
                    </div>
                </section>
		<? } elseif ($a == "postpoll") { // if (empty($pVals)) { ?>
            <section id="formhead">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-lg-offset-2 text-center">
                            <h2 class="section-heading">Tell Me More!</h2>
                            <hr class="primary">
                            <div class="subtitle text-muted text-center">
                              <p>Be assured that your info is private and we'll not start sending you a bunch of emails.</p>
                              <p>If you want to stop, let us know.</p>
                            </div>
                        </div>
                 	</div><!-- end row -->
                </div> <!-- end container -->
            </section> <!-- end formhead section -->
            <section id="form">
<!--- form begins -->
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-lg-offset-2 text-center">
                                <form name="namer" id="nameform" method="post" novalidate>
                                    <div class="row">
                                        <div class="form-group col-xs-12">
                                            <!-- <label>Name</label> -->
                                            <input type="text" name="nameline" class="form-control" placeholder=" Your Name." id="name" required data-validation-required-message="Please enter your name.">
                                            <p class="help-block text-danger"></p>
                                        </div>
                                    </div>
                                    <div class="row control-group">
                                        <div class="form-group col-xs-12">
                                            <!-- <label>Email Address</label> -->
                                            <input type="email" name="email" class="form-control" placeholder=" Your Email." id="email" required data-validation-required-message="Please enter a valid email address.">
                                            <p class="help-block text-danger"></p>
                                        </div>
                                    </div>
    
                                    <div id="success"></div>
                                    <div class="row">
                                        <div class="form-group col-xs-12">
                                            <button type="submit" class="btn btn-primary btn-xl wow tada">Send <i class="fa fa-send"></i></button>
                                        </div>
                                    </div>
                                </form>
            <!--- end form -->
                                </div>
                        </div>
                    </div>
                </section>
		<? } elseif ($a == "postname") { // if (empty($pVals)) { ?>
            <section id="formhead">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-lg-offset-2 text-center">
                            <div class="subtitle text-muted text-center">
                              <p>You can expect to hear from us in the days ahead.</p>
                              <p>If you are curious and want to see the Website being built, you can go to the test site at:<br />
                              	 <a href="http://makingyourlifecount.net">MYLC Concept Test Site</p>
                            </div>
                        </div>
                 	</div><!-- end row -->
                </div> <!-- end container -->
            </section> <!-- end formhead section -->
        <? } // elseif ($a == "postpoll")  ?>
			</div> <!-- end map/form zoom -->
      </div>
<div class="diag-section s_3">
      <div class="diagonal diagonal_top post_s_2"></div>
</div>
    
<footer id="footer" class="bg-primary">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 text-center">
                	<span class="h4"><span class="glyphicon glyphicon-grain"></span> ______ Your Life Count</span><br>
                    100 Lake Hart Drive, Orlando, FL 32832<br> tel. +1 (407) 826-2100 | questions. <a href="mailto:MYLC@cruglobal.freshdesk.com">mylc@cruglobal.freshdesk.com</a><br>
                </div>
                <div class="col-lg-3 text-center">
                    <ul class="social-icons icon-circle icon-zoom list-unstyled list-inline"> 
                        <!-- <li> <a href="#"><i class="fa fa-twitter"></i></a></li> 
                        <li> <a href="#"><i class="fa fa-google-plus"></i></a></li>  -->
                        <li> <a href="facebook.com/yourlifecount"><i class="fa fa-facebook"></i></a></li> 
                    </ul>                    
                </div>
                <div class="col-lg-3 text-center">
                	<div class="fb-like"
					  data-share="true"
					  data-width="450"
					  data-show-faces="true">
					</div>
                </div>    
			</div><!-- end row -->	
    	</div> <!-- end container -->
	</footer>

    <!-- jQuery -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    
    <!-- Bootstrap FormHelpers -->
    <script src="js/bootstrap-formhelpers.min.js"></script>    

    <!-- Plugin JavaScript -->
    <script src="js/jquery.easing.min.js"></script>
    <script src="js/jquery.fittext.js"></script>
    <script src="js/wow.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/bootstrap-switch.js"></script>    
    <script src="js/butter.js"></script>

    <!-- Diagonal -->
    <script src="js/diagonal.js"></script>

    <!-- Google Map -->
    <script src="js/googlemap.js"></script>

    <!-- Contact Form -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>
    

</body>
</html>
