<?php // check for valid admin section, else redirect to editorial
if($_GET['form'] != 'editorial' && $_GET['form'] != 'news' && $_GET['form'] != 'banners' && $_GET['form'] != 'ads' && $_GET['form'] != 'feeds') header("Location: admin.php?form=editorial");
?>
<?php include_once("./inc/header.php"); $status = ''; ?>

<?php // if password form was submitted, check for correct password
if(isset($_POST['password'])) {
	if(sanitize($_POST['password']) == 'birthday') setcookie("authed", "aye"); // if password is correct, set a cookie to authenticate
	else $status = 'The password you submitted is incorrect'; // if password is incorrect, set status
} ?>

<?php
$type = 'bad';
// if admin panel form was submitted, validate+sanitize inputs+set status, and (if applicable) write to file
if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST) && !isset($_POST['password']) && $_COOKIE['authed'] == 'aye') :
	if($_GET['form'] == 'editorial' || $_GET['form'] == 'news') {
		$url = validate('url', $status);
		$title = validate('title', $status);
		$contents = validate('contents', $status);
	}
	
	// if no problems arose, write to file
	if($status == '') {
		$mode; $filename; $line;
		
		// implode the line together
		if($_GET['form'] == 'news') {
			////////// pass the url, title, and contents to append function
			if(!P2_Utils::add_news($url, $title, $contents)) $filename = "news.xml";
			new RSSFeed();
		} elseif($_GET['form'] == 'editorial') {
			////////// write the editorial fields to the xml
			// open a new xml domdocument
			$dom = new DOMDocument("1.0", "UTF-8");
			
			// make the editorial container
			$editorial = $dom->createElement("editorial");
			
			// make the image, title, and content elements
			$image = $dom->createElement("image", $url);
			$title = $dom->createElement("title", $title);
			$content = $dom->createElement("content");
			
			// file the content element with $contents in a CDATA tag
			$cdata = $dom->createCDATASection($contents);
			$content->appendChild($cdata);
			
			// put it all together and save
			$editorial->appendChild($image);
			$editorial->appendChild($title);
			$editorial->appendChild($content);
			$dom->appendChild($editorial);
			
			$dom->preserveWhiteSpace = true;
			$dom->formatOutput = true;
			if(!$dom->save("./xml/editorial.xml")) $filename = "editorial.xml";
		} elseif($_GET['form'] == 'banners') {
			////////// if editing the banners, load the current xml
			$olddom = new DOMDocument();
			$olddom->load("./xml/banners.xml");
			$oldbanners = $olddom->getElementsByTagName("banner");
			
			// get the input from the submitted form, and populate arrays with the data
			$ids = array();
			$urls = array();
			$counts = array();
			for($i=1;$i<=$oldbanners->length;$i++) {
				$ids[] = $i;
				$urls[] = $_POST['url-' . $i];
				$counts[] = $_POST['count-' . $i];
			}

			// if a new banner was submitted, add it
			if(isset($_POST['new']) && $_POST['new']) {
				// append the banner to the arrays
				array_unshift($ids, $oldbanners->length + 1);
				array_unshift($urls, validate("new", $status));
				array_unshift($counts, "0");
			}

			// get the checked banners
			if(isset($_POST['remove'])) $remove = $_POST['remove'];

			// was anything checked?
			if(!empty($remove)) {
				// find the index of the banners checked for removal
				$remove_index = array();
				foreach($remove as $id) {
					$index = array_search(intval($id), $ids, true);
					if(!($index === false)) $remove_index[] = $index;
				}

				// remove the banner from the three arrays if it was checked
				foreach($remove_index as $index) {
					unset($ids[$index], $urls[$index], $counts[$index]);
				}
				
				// since banners were removed, the id scheme (sequential from 1, no gaps) is messed up
				// we need to rebuild it
				for($i=0; $i<count($ids); $i++) {
					$ids[$i] = $i + 1;
				}
				// make all the indicies in the other arrays sequential, no gaps
				$urls = array_values($urls);
				$counts = array_values($counts);
			}

			// save the banners to the xml, and if that fails, set the filename for the error message
			if(!P2_Utils::save_banners($ids, $urls, $counts)) $filename = "banner.xml";
		} elseif($_GET['form'] == 'ads') {
			////////// if editing the ads, load the current xml
			$olddom = new DOMDocument();
			$olddom->load("./xml/ads.xml");
			$oldads = $olddom->getElementsByTagName("ad");
			
			// get the input from the submitted form, and populate arrays with the data
			$ids = array();
			$urls = array();
			for($i=1;$i<=$oldads->length;$i++) {
				$ids[] = $i;
				$urls[] = $_POST['url-' . $i];
			}

			// if a new ad was submitted, add it
			if(isset($_POST['new']) && $_POST['new']) {
				// append the ads to the arrays
				array_unshift($ids, $oldads->length + 1);
				array_unshift($urls, validate("new", $status));
			}

			// get the checked ads
			if(isset($_POST['remove'])) $remove = $_POST['remove'];

			// was anything checked?
			if(!empty($remove)) {
				// find the index of the ads checked for removal
				$remove_index = array();
				foreach($remove as $id) {
					$index = array_search(intval($id), $ids);
					if(!($index === false)) $remove_index[] = $index;
				}

				// remove the ads from the two arrays if it was checked
				foreach($remove_index as $index) {
					unset($ids[$index], $urls[$index]);
				}
				
				// since ads were removed, the id scheme (sequential from 1, no gaps) is messed up
				// we need to rebuild it
				for($i=0; $i<count($ids); $i++) {
					$ids[$i] = $i + 1;
				}
				// make all the indicies in the other array are sequential, no gaps
				$urls = array_values($urls);
			}

			// save the ads to the xml, and if that fails, set the filename for the error message
			if(!P2_Utils::save_ads($ids, $urls)) $filename = "ads.xml";
		} elseif($_GET['form'] == 'feeds') {
			// get the feed URLs submitted and write the ext_feeds.xml file
			if(isset($_POST['selected_feeds'])) $feeds = $_POST['selected_feeds'];
			
			// if there are too many items checked, don't do anything
			if(count($feeds) > 3) {
				$filename = 'ext_feeds.xml because you selected too many options.';
			}elseif(!empty($feeds)) { // if there aren't too many items checked and the array isn't empty, save the selection
				// create the new xml document
				$dom = new DOMDocument("1.0", "UTF-8");
				$feeds_container = $dom->createElement("feeds");
				
				// make each selected URL as an element
				foreach($feeds as $url) {
					$feed = $dom->createElement("feed");
					$feed->setAttribute("url", $url);
					$feeds_container->appendChild($feed);
				}
				
				// put it together and save the XML
				$dom->appendChild($feeds_container);
				$dom->preserveWhiteSpace = true;
				$dom->formatOutput = true;
				if(!$dom->save("./xml/ext_feeds.xml")) $filename = "ext_feeds.xml";
			}
			
			// get the service URLs submitted and write the services.xml file
			if(isset($_POST['selected_services'])) $feeds = $_POST['selected_services'];
			
			// if there are too many items checked, don't do anything
			if(count($feeds) > 10) {
				$filename = 'services.xml because you selected too many options.';
			}elseif(!empty($feeds)) { // if there aren't too many items checked and the array isn't empty, save the selection
				// create the new xml document
				$dom = new DOMDocument("1.0", "UTF-8");
				$feeds_container = $dom->createElement("feeds");
				
				// make each selected URL as an element
				foreach($feeds as $url) {
					$feed = $dom->createElement("feed");
					$feed->setAttribute("url", $url);
					$feeds_container->appendChild($feed);
				}
				
				// put it together and save the XML
				$dom->appendChild($feeds_container);
				$dom->preserveWhiteSpace = true;
				$dom->formatOutput = true;
				if(!$dom->save("./xml/services.xml")) $filename = "services.xml";
			}
		}
		
		// tell the user the status of the save operation
		if(!$filename) {
			$status .= 'The changes have been made at ' . date("n.j.y ga");
			$type = 'good';
		} else {
			$status .= 'There was an error writing to ' . $filename;
			$type = 'bad';
		}
	}
endif; // end if admin panel submitted
?>

<?php if($_COOKIE['authed'] == 'aye') : // if authenticated, show admin area ?>
    <?php if($status != '') echo '<div id="status" class="' . $type . '">' . $status . '</div>'; // if status is set, show it ?>
    
    <section id="admin">
    	<a href="./admin.php?form=editorial" id="editorial_link" <?php if($_GET['form'] == 'editorial') echo 'class="current"'; ?>>Letter From The Editor</a>
        <a href="./admin.php?form=news" id="news_link" <?php if($_GET['form'] == 'news') echo 'class="current"'; ?>>Add News Item</a>
        
        <a href="./admin.php?form=banners" id="banners_link" class="right <?php if($_GET['form'] == 'banners') echo 'current'; ?>">Banners</a>
        <a href="./admin.php?form=ads" id="ads_link" class="right <?php if($_GET['form'] == 'ads') echo 'current'; ?>">Classifieds</a>
        <a href="./admin.php?form=feeds" id="feeds_link" class="right <?php if($_GET['form'] == 'feeds') echo 'current'; ?>">Feeds</a>
        
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; echo (isset($_GET['form'])) ? '?form=' . $_GET['form'] : ''; ?>" <?php if($_GET['form'] != 'editorial' && $_GET['form'] != 'news') echo ' class="push"'; ?>>
		<?php if($_GET['form'] == 'editorial' || $_GET['form'] == 'news') : ?>
        	<label for="url"><?php echo ($_GET['form'] == 'news') ? 'Featured Image URL (200x200 px)' : 'Profile Picture URL (240px wide)'; ?></label>
            <input type="url" name="url" id="url" value="<?php echo ($_GET['form'] == 'news') ? '' : P2_Utils::editorial_field('url'); ?>" required />
            <?php echo ($_GET['form'] != 'news' && P2_Utils::editorial_field('url') != '') ? '<img src="' . P2_Utils::editorial_field('url') . '" alt="Preview" />' : ''; ?>
            
        	<label for="title">Title</label>
            <input type="text" name="title" id="title" value="<?php echo ($_GET['form'] == 'news') ? '' : P2_Utils::editorial_field('title'); ?>" required />

        	<label for="contents"><?php echo ($_GET['form'] == 'news') ? 'News' : 'Letter Contents'; ?></label>
            <textarea name="contents" id="contents" required><?php echo ($_GET['form'] == 'news') ? '' : P2_Utils::editorial_field('contents'); ?></textarea>
		<?php elseif($_GET['form'] == 'banners'): ?>
        	<?php P2_Utils::banner("all"); ?>
        <?php elseif($_GET['form'] == 'ads'): ?>
        	<?php P2_Utils::ads("edit"); ?>
        <?php elseif($_GET['form'] == 'feeds'): ?>
        	<h2>External Feeds</h2>
            <p class="formdesc">Select up to 3 feeds to display on the front page</p>
            <?php
			// get the currently selected feeds from xml
			$selected_feeds = P2_Utils::get_selected_feeds();
			
			// print a list of feeds available to select
			foreach(P2_Utils::$feeds_list as $name => $feed) {
				$checked = false;
				if(in_array($feed, $selected_feeds)) $checked = true;
				echo '<span class="feedlist"><input type="checkbox" name="selected_feeds[]" value="' . $feed . '"';
				echo ($checked) ? ' checked' : '';
				echo '/>' . $name . '</span>';
			}
			?>
            
            <h2>Services (Classmates' Feeds)</h2>
            <p class="formdesc">Select up to 10 classmates to have their feeds displayed on the Services page</p>
            <?php
			// get every classmate's feed and print a list to select
			$dom = new DOMDocument();
			$dom->load("http://people.rit.edu/~dmgics/539/project2/rss_class.xml");
			$students = $dom->getElementsByTagName("student");
			
			// get the currently selected feeds from xml
			$selected_services = P2_Utils::get_selected_feeds("services.xml");
			
			// print a list of students available to select
			foreach($students as $student) {
				// extract the info from the xml
				$url = $student->getElementsByTagName("url")->item(0)->nodeValue;
				$name = $student->getElementsByTagName("first")->item(0)->nodeValue;
				$name .= " ";
				$name .= $student->getElementsByTagName("last")->item(0)->nodeValue;
				
				// print the checkbox for it
				$checked = false;
				if(in_array($url, $selected_services)) $checked = true;
				echo '<span class="feedlist"><input type="checkbox" name="selected_services[]" value="' . $url . '"';
				echo ($checked) ? ' checked' : '';
				echo '/>' . $name . '</span>';
			}
			?>
		<?php endif; ?>            
            <input type="submit" value="<?php echo ($_GET['form'] == 'news') ? 'POST' : 'SAVE'; ?>" />
        </form>
    </section>
<?php else: // if not authenticated, show password form ?>
	<h3 id="formtitle">Password</h3>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="password_form">
    	<input type="password" name="password" required />
        <?php if($status != '') echo '<span>' . $status . '</span>'; // if status is set, show it ?>
        <button type="submit"></button>
    </form>
<?php endif; // end auth checking ?>

<?php include_once("./inc/footer.php"); ?>