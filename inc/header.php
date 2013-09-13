<?php require_once("./lib/LIB_project1.php"); ?>
<?php require_once("./lib/P2_Utils.class.php"); ?>
<?php require_once("./lib/RSSFeed.class.php"); ?>
<!DOCTYPE html>
<html <?php P2_Utils::is_admin(); ?>>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>2CHAINZ - <?php echo current_page(); ?> | Project 2 | Vlad Ionescu</title>
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
	<script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>
    <script src="http://people.rit.edu/~vxi6514/539/project2/script.js" type="text/javascript"></script>
    <link href='http://fonts.googleapis.com/css?family=Dosis:400,500,600,700|PT+Sans:400,700,400italic,700italic|Gentium+Basic:700italic' rel='stylesheet' type='text/css'>
    <link type="text/css" rel="stylesheet" media="screen" href="/~vxi6514/539/project2/style.css" />
</head>
<body>
	<header id="main-header">
    	<h1><a href="/~vxi6514/539/project2/">2 CHAINZ</a></h1>
    	<?php nav_menu(); ?>
        
        <div id="banner">
        	<?php P2_Utils::banner(); ?>
            <p><?php echo user_info(); ?></p>
        </div>
	</header>
    <div id="page-wrap">