<?php
/*
/
/	LIB_project1.php for Project 1 - 539 (Programming for the World Wide Web aka Server Side Programming)
/	
/	Vlad Ionescu 2013, RIT Student, 2nd year Information Security & Forensics major
/
/	This file contains support functions for the Project 1 news site.
/	Issues/comments can be addressed to vxi6514@rit.edu
/
*/

// FUNCTION		current_page
// PARAMETERS	-
// RETURNS		string of current page's name
// ECHOS		-
function current_page() {
	// gets position for last / in URL, moves forward one, and takes everything from there to the fourth character from the end (the file extension and period)
	// then capitalizes the first letter and returns the string
	return ucfirst(substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], "/")+1, -4));
}

// FUNCTION		sanitize
// PARAMETERS	$string to sanitize
// RETURNS		clean string
// ECHOS		-
function sanitize($string) {
	$string = trim($string);
	$string = stripslashes($string);
	$string = htmlentities($string);
	$string = strip_tags($string, array("<br />", "<a>", "<b>", "<i>", "<script>"));
	return $string;
}

// FUNCTION		validate
// PARAMETERS	$field to validate, and reference to &status string
// RETURNS		clean string on success, or false on failure
// ECHOS		-
function validate($field, &$status) {
	if($_POST[$field] !== '') { // check for contents (required)
		$clean = sanitize($_POST[$field]); // if field has contents, sanitize them
		if($clean == '') {
			$status .= 'Please enter a valid string for ' . $field . '.<br/>'; // if there's nothing left after sanitization add an error
			return false;
		} else {
			return $clean; // if there's something left after sanitization, return it
		}
	} else { // if empty, add error to status
		$status .= 'Please enter something in ' . $field . '.<br/>';
		return false;	
	}
}

// FUNCTION		nav_menu
// PARAMETERS	-
// RETURNS		-
// ECHOS		nav elements in <a> tags (current page has a class="current" attribute) wrapped in a <nav> container
function nav_menu() {
	// get the current page to know which nav item to set class="current"
	$current_page = current_page();
	
	$nav = '<nav>';	// create nav wrapper
	$nav .= '<a href="./index.php"';
	$nav .= ($current_page == "Index") ? ' class="current"': ''; // if current page is index, make home have the current class
	$nav .= '>Home</a> | <a href="./news.php"';
	$nav .= ($current_page == "News") ? ' class="current"': ''; // see two lines above me for explanation
	$nav .= '>News</a> | <a href="./classifieds.php"';
	$nav .= ($current_page == "Classifieds") ? ' class="current"': ''; // see two lines above me for explanation
	$nav .= '>Classifieds</a> | <a href="./services.php"';
	$nav .= ($current_page == "Services") ? ' class="current"': ''; // see two lines above me for explanation
	$nav .= '>Services</a> | <a href="./admin.php"';
	$nav .= ($current_page == "Admin") ? ' class="current"': ''; // see two lines above me for explanation
	$nav .= '>Admin</a>';
	$nav .= '</nav>'; // close nav wrapper
	
	// print nav menu out wherever function was invoked
	echo $nav;
}

// FUNCTION		banner
// PARAMETERS	-
// RETURNS		-
// ECHOS		<img> of banner ad
function banner() {
	// open file with banner locations
	$lines = file("./flatfile/banners.txt");
	
	// storage arrays
	$urls = array();
	$counts = array();
	
	// make two parallel arrays, one with all the URLs, and another with the amount of times each banner has shown
	foreach($lines as $line) {
		$tokens = explode("%%", $line);
		$urls[] = $tokens[0];
		$counts[] = $tokens[1];
	}
	
	// sort the arrays together by the number of times
	// each banner has been shown, going from least to greatest
	array_multisort($counts, SORT_ASC, SORT_NUMERIC, $urls, SORT_ASC, SORT_NUMERIC);
	
	// take the first url, output it as a banner
	echo '<img src="' . $urls[0] . '" alt="Banner ad" />';
	
	// increment the first count by one
	$counts[0] = $counts[0] + 1;
	
	// implode the arrays back together
	//$new_file = '';
	//for($i=0; $i<count($urls); $i++) {
	//	$new_file .= $urls[$i] . "%%" . $counts[$i] . "\n";
	//}
	$new_file = array();
	for($i=0; $i<count($urls); $i++) {
		$tokens = array(trim($urls[$i]), trim($counts[$i]));
		$new_file[] = implode("%%", $tokens) . "\n";
	}
	
	// rewrite the banners.txt file
	file_put_contents("./flatfile/banners.txt", $new_file);
}

// FUNCTION		user_info
// PARAMETERS	-
// RETURNS		string of user's browser, OS, time requested, times visited the site, and geographic location
// ECHOS		-
function user_info() {
	// cookie based visit counter
	$visits;
	if(isset($_COOKIE['visits'])) { // if cookie already exists (user has been here before), increment by one
		$visits = $_COOKIE['visits'];
		$visits++;
		setcookie('visits', $visits, time()+60*60*24*30);
	} else { // otherwise set the visit number to 1
		$visits = 1;
		setcookie('visits', 1, time()+60*60*24*30);
	}

	// get the parsed user agent string in an object
	$ua = get_browser();
		
	// setup the string to return
	// browser and OS detection, as well as time the page was requested
	$return = 'You are using ' . $ua->browser . ' on ' . $ua->platform . '.';
	$return .= ' The page loaded at ' . date("g:i:sa") . '.<br/>';
	
	// print out message based on how many times the user has visited the page (logged by cookie)
	if($visits > 10) {
		$return .= 'You\'ve been here before';
	} else {
		$words = array('zero', 'first', 'second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth');
		$return .= 'This is your ' . $words[$visits] . ' visit';
	}
	
	// query hostip.info for country based on host's IP
	$return .= ', welcome ' . file_get_contents("http://api.hostip.info/country.php?ip=".$_SERVER['REMOTE_ADDR']) . ' visitor!';
	
	return $return;
}

// FUNCTION		letter_from_the_editor
// PARAMETERS	-
// RETURNS		-
// ECHOS		<img> with 240x240 profile picture, <h3> with title, and <p> with content. all read from file
function letter_from_the_editor() {
	// load file
	$file = file_get_contents("./flatfile/editorial.txt");
	
	// if loading is successful, output the editorial letter. else, throw error
	if($file) {
		// split string by %%
		$tokens = explode("%%", $file);
		
		// first token is img src, second is title, third is contents
		echo '<img src="'. $tokens[0] . '" width="240" height="240" alt="This is me!" />';
		echo "<h3>" . html_entity_decode($tokens[1], ENT_COMPAT | ENT_HTML5) . "</h3>";
		echo "<p>" . html_entity_decode($tokens[2], ENT_COMPAT | ENT_HTML5) . "</p>";
	} else {
		echo "[Error loading editorial.txt - check that file exists in correct directory and reload the page]";
	}
}

// FUNCTION		news_items
// PARAMETERS	$num_items to display, $page to offset items by
// RETURNS		-
// ECHOS		<article>s containing a 200x200 <img>, <h3> with title, <time> posted, and <p> excerpt. all read from file
function news_items($num_items, $page = 1) {
	// load file
	$lines = file("./flatfile/news.txt");
	
	// check that $num_items isn't more than the number of available news items
	$item_count = count($lines);
	$num_items = ($num_items > $item_count) ? $item_count : $num_items;
	
	// set the offset
	if($page == 0) $page = 1;
	$page = $page * $num_items - $num_items;

	// if loading is successful, do things. else, throw error
	if($lines):
		// print as many news items as required
		for($i=0; $i<$num_items; $i++) {
			// check that we aren't going too far
			if(($page+$i) >= count($lines)) break;
			
			// split line by %% delimiter
			$line = $lines[$page+$i];
			$tokens = explode("%%", $line);
			
			// print article
			echo '<article>';
			echo '<img src="' . $tokens[1] . '" width="200" height="200" alt="Featured image for this post" />';
			echo '<h3>' . html_entity_decode($tokens[2], ENT_COMPAT | ENT_HTML5) . '</h3>';
			echo '<time datetime="' . date("Y-m-d", $tokens[0]) . '">' . date("n.j.y ga", $tokens[0]) . '</time>';
			echo '<p>' . html_entity_decode($tokens[3], ENT_COMPAT | ENT_HTML5) . '</p>';
			echo '<a href="./news.php?id=' . $tokens[0] . '" class="more">&gt;</a>';
			echo '</article>';
		}
	else:
		echo "[Error loading news.txt - check that the file exists in correct directory and reload the page]";
	endif;
}

// FUNCTION		pagination
// PARAMETERS	current $page you're on
// RETURNS		-
// ECHOS		prev and next links, and page numbers
function pagination($page) {
	// number of items to show per page
	global $nr_show;
	$nr_show = (isset($_GET['nr_show'])) ? $_GET['nr_show'] : 5;
	$nr_show_get = 'nr_show=' . $nr_show;

	echo '<nav id="pagination">';
	
	// back button
	if($page) echo '<a href="./news.php?' . $nr_show_get . '&amp;page=' . ($page - 1) . '" title="Go back one page">&lt; Prev</a>';
	else echo '<a href="./news.php' . $nr_show_get . '" title="Go back one page">&lt; Prev</a>';
	
	// number of items to display per page
	echo '<form method="GET" action="' . $_SERVER['PHP_SELF'] . '" id="nr_show_select">';
	echo '<label for="nr_show">Items per page</label>';
	echo '<select name="nr_show" id="nr_show" onchange="this.form.submit();">';
	echo '<option value="3"' . (($_GET['nr_show'] == 3) ? ' selected' : '') . '>3</option>';
	echo '<option value="5"' . (($_GET['nr_show'] == 5 || !isset($_GET['nr_show'])) ? ' selected' : '') . '>5</option>';
	echo '<option value="10"' . (($_GET['nr_show'] == 10) ? ' selected' : '') . '>10</option>';
	echo '<option value="15"' . (($_GET['nr_show'] == 15) ? ' selected' : '') . '>15</option>';
	echo '</select>';
	echo '</form>';
	
	// load news.xml to count the nr of items in it
	$dom = new DOMDocument();
	$dom->load("./xml/news.xml");
	$news_items = $dom->getElementsByTagName("item")->length;
	
	// page listing
	$total_pages = ceil($news_items / $nr_show); // get the nr of lines in the news file, divide that by the nr of items per page, and round up if necessary
	
	echo '<span>Page: ';
	for($i=1; $i<=$total_pages; $i++) {
		echo '<a href="./news.php?' . $nr_show_get . '&amp;page=' . $i . '" title="Go to page ' . $i . '"';
		if($i == $page) echo ' class="current"';
		echo '>' . $i . '</a> ';
	}
	echo '</span>';
	
	// forward button
	if($page && (($page + 1) > $total_pages)) $page = $total_pages - 1;
	if($page) {
		echo '<a href="./news.php?' . $nr_show_get . '&amp;page=' . ($page + 1) . '" title="Go forward one page" id="next">Next &gt;</a>';
	} else {
		echo '<a href="./news.php?' . $nr_show_get;
		echo ($total_pages > 1) ? '&amp;page=2"' : '"';
		echo ' title="Go forward one page" id="next">Next &gt;</a>';
	}
	
	echo '</nav>';
}

// FUNCTION		editorial_field
// PARAMETERS	specific $field to retrieve from file
// RETURNS		contents of the specified field
// ECHOS		-
function editorial_field($field) {	
	// open the file
	$file = file_get_contents("./flatfile/editorial.txt");
	
	// if loading is successful, do stuff. else, throw error
	if($file) {
		// split string by %%
		$tokens = explode("%%", $file);
		
		// first token is img src, second is title, third is contents
		// decide which one is needed by the parameter
		switch ($field) {
			case 'url':
				return $tokens[0];
			case 'title':
				return html_entity_decode($tokens[1], ENT_COMPAT | ENT_HTML5);
			case 'contents':
				return html_entity_decode($tokens[2], ENT_COMPAT | ENT_HTML5);
			default:
				return "[Error - parameter invalid]";
		}
	} else {
		return "[Error loading editorial.txt - check that file exists in correct directory and reload the page]";
	}
}
?>