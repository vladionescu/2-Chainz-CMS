<?php
/*
/
/	P2_Utils.class.php for Project 2 - 539 (Programming for the World Wide Web aka Server Side Programming)
/	
/	Vlad Ionescu 2013, RIT Student, 2nd year Information Security & Forensics major
/
/	This file contains support functions for the Project 2 improved news site w. syndication.
/	Issues/comments can be addressed to vxi6514@rit.edu
/
/	ALL METHODS AND MEMBERS ARE STATIC
/
*/
class P2_Utils {
	// list of feeds available for user to select
	public static $feeds_list = array(
		// funny
		"Basic Instructions"	=>	"http://basicinstructions.net/basic-instructions/rss.xml",
		"Fake Science"			=>	"http://fakescience.org/rss",
		"The Onion"				=>	"http://feeds.theonion.com/theonion/daily",
		"XKCD"					=>	"http://xkcd.com/rss.xml",
		// photography
		"Strobist"				=>	"http://feeds.feedburner.com/blogspot/WOBq",
		"Scott Kelby"			=>	"http://www.scottkelby.com/blog/feed",
		"PetaPixel"				=>	"http://feeds.feedburner.com/PetaPixel",
		"Chase Jarvis"			=>	"http://feeds.feedburner.com/ChaseJarvis",
		"Joe McNally"			=>	"http://feeds.feedburner.com/joemcnally"
	);
	
	// PUBLIC STATIC is_admin
	// 2 chainz
	// no parameters, echos class="admin" if on admin pages
	public static function is_admin() {
		if(current_page() == "Admin") echo 'class="admin"';
	}
	
	// PUBLIC STATIC ext_feeds
	// echos a <section> with <article>s that are each the most recent item in the three
	// selected external rss feeds
	public static function ext_feeds() {
		$return;
		
		// $feeds is an array of links to RSS feeds
		$feeds = P2_Utils::get_selected_feeds();
		
		// lets display each one!
		foreach($feeds as $feed) {			
			////////// retrieve the feed
			$rss =  new DOMDocument();
			// if there is an error loading the feed, go to the next one
			if(!$rss->load($feed)) continue;
			
			// start things off with an section tag, because it will have a heading and contain an article
			$return .= "<section>\n";
			
			// get the title, link, and desc
			$title = $rss->getElementsByTagName("title")->item(0)->nodeValue;
			$time = strtotime($rss->getElementsByTagName("pubDate")->item(0)->nodeValue);
			$link = $rss->getElementsByTagName("link")->item(0)->nodeValue;
			$desc = $rss->getElementsByTagName("description")->item(0)->nodeValue;
			
			// arrange it nicely and stick it in the section
			$return .= "<h3><a href=\"$link\" title=\"$title\">$title</a></h3>\n";
			if(!empty($time) || !$time) $return .= '<time  datetime="' . date("Y-m-d", $time) . '">' . date("n.j.y", $time) . '</time>' . "\n";
			if(!empty($desc)) $return .= "<p class=\"desc\">$desc</p>\n";
			
			// get the first item
			$item = $rss->getElementsByTagName("item")->item(0);
			
			// make the article happen
			$return .= "<article>";

			// get the title, link, and desc of the item this time
			$title = $item->getElementsByTagName("title")->item(0)->nodeValue;
			$link = $item->getElementsByTagName("link")->item(0)->nodeValue;
			$desc = $item->getElementsByTagName("description")->item(0)->nodeValue;
			
			// populate the article with the rss feed's contents
			$return .= "<h4><a href=\"$link\" title=\"$title\">$title</a></h4>\n<p>$desc</p>\n";
			
			// this article (and section) is done!
			$return .= "</article>\n</section>";
		}
		
		// print out the rss feeds section
		echo $return;
	}
	
	// PUBLIC STATIC get_selected_feeds
	// returns an array of URLs to RSS feeds
	// the URLs are taken from a file called ext_feeds.xml unless otherwise specified
	public static function get_selected_feeds($file = "ext_feeds.xml") {
		$return = array();
		
		$dom = new DOMDocument();
		$dom->load("./xml/" . $file);
		$feeds = $dom->getElementsByTagName("feed");
		
		// take the URL attribute of each feed item and store its value in the $return array
		foreach($feeds as $feed) {
			$return[] = $feed->getAttribute("url");
		}
		
		return $return;
	}
	
	// FUNCTION		banner (v2 - based off same func in LIB_project1.php)
	// PARAMETERS	$mode (default "top") accepts either "top" or "all". "top" displays the next banner, "all" displays all of them in a form
	// RETURNS		-
	// ECHOS		<img> of banner ad
	public static function banner($mode = "top") {
		// open XML file with banner locations
		$dom = new DOMDocument();
		if($dom->load("./xml/banners.xml")) { // if it opened, do stuff, else throw error
			// get all the banners
			$banners = $dom->getElementsByTagName("banner");
			
			// storage arrays
			$urls = array();
			$counts = array();
			$ids = array();
			
			// make two parallel arrays, one with all the URLs, and another with the amount of times each banner has shown
			foreach($banners as $banner) {
				$urls[] = $banner->getAttribute("url");
				$counts[] = $banner->getAttribute("count");
				$ids[] = $banner->getAttribute("id");
			}
			
			// sort the arrays together by the number of times
			// each banner has been shown, going from least to greatest
			array_multisort($counts, SORT_ASC, SORT_NUMERIC, $urls, SORT_ASC, SORT_NUMERIC, $ids, SORT_ASC, SORT_NUMERIC);
			
			// if mode is to display the next banner
			if($mode == "top") {
				//take the first url, output it as a banner
				echo '<img src="' . $urls[0] . '" alt="Banner ad" />';
				
				// increment the first count by one
				$counts[0] = $counts[0] + 1;
				
				// update the xml
				if(!P2_Utils::save_banners($ids, $urls, $counts)) echo '[Error saving banner.xml]';
			} elseif($mode == "all") { // if mode is to display all banners in a form
				echo '<p class="formdesc">Tick the checkbox next to an item to remove it on save<br/>Counts are editable, URLs are not<br/>Banners are 370px x 44px</p>';
				
				// display every banner in order of counts with editable count fields
				for($i=0; $i<count($counts); $i++) {
					echo '<input type="checkbox" name="remove[]" value="' . $ids[$i] . '" />';
					echo '<input type="text" name="url-' . $ids[$i] . '" value="' . $urls[$i] . '" class="banner-indent" readonly />';
					echo 'Count: ';
					echo '<input type="number" min="0" name="count-' . $ids[$i] . '" value="' . $counts[$i] . '" />';
					echo '<img src="' . $urls[$i] . '" width="370" height="44" class="current-banner" />';
				}
				
				// display an extra field in case user wants to add a banner
				echo '<input type="url" name="new" class="banner-new" />';
				echo 'New banner to add';
			}
		} else {
			echo "[Error loading banner.xml - check that file exists in correct directory and reload the page]";
		}
	}
	
	// FUNCTION		save_banners
	// PARAMETERS	$ids, $urls, $counts. all parallel arrays of the banners to be saved to banners.xml
	// RETURNS		true on success, false on fail
	// ECHOS		-
	public static function save_banners($ids, $urls, $counts) {
		// create a new domdocument (we're making a new xml file entirely, because the items have to be updated and reordered)
		$newdom = new DOMDocument("1.0", "UTF-8");
		$bannersEl = $newdom->createElement("banners");
		
		// setup each individual banner
		for($i=0; $i<count($counts); $i++) {
			$bannerEl = $newdom->createElement("banner");
			$bannerEl->setAttribute("url", $urls[$i]);
			$bannerEl->setAttribute("count", $counts[$i]);
			$bannerEl->setAttribute("id", $ids[$i]);
			$bannerEl->setIdAttribute("id", true);
			$bannersEl->appendChild($bannerEl);
		}
		
		// finish the xml file
		$newdom->appendChild($bannersEl);
		$newdom->preserveWhiteSpace = true;
		$newdom->formatOutput = true;
		
		// save the xml file
		if($newdom->save("./xml/banners.xml")) return true;
		return false;
	}

	// FUNCTION		letter_from_the_editor (v2)
	// PARAMETERS	-
	// RETURNS		-
	// ECHOS		<img> with 240x240 profile picture, <h3> with title, and <p> with content. all read from xml
	public static function letter_from_the_editor() {
		// open XML file with editorial content
		$dom = new DOMDocument();
		if($dom->load("./xml/editorial.xml")) { // if it opened, do stuff, else throw error
			$tokens[0] = $dom->getElementsByTagName("image")->item(0)->nodeValue;
			$tokens[1] = $dom->getElementsByTagName("title")->item(0)->nodeValue;
			$tokens[2] = $dom->getElementsByTagName("content")->item(0)->nodeValue;
			
			// strip CDATA tags out of content item
			// this isn't really neccessary because the nodeValue strips it already it seems, but hey, extra security
			$tokens[2] = str_replace("<![CDATA[", "", $tokens[2]);
			$tokens[2] = str_replace("]]>", "", $tokens[2]);
			
			// first token is img src, second is title, third is contents
			echo '<img src="'. $tokens[0] . '" width="240" height="240" alt="This is me!" />';
			echo "<h3>" . html_entity_decode($tokens[1], ENT_COMPAT | ENT_HTML5) . "</h3>";
			echo "<p>" . html_entity_decode($tokens[2], ENT_COMPAT | ENT_HTML5) . "</p>";
		} else {
			echo "[Error loading editorial.xml - check that file exists in correct directory and reload the page]";
		}
	}

	// FUNCTION		news_items
	// PARAMETERS	$num_items to display, $page to offset items by
	// RETURNS		-
	// ECHOS		<article>s containing a 200x200 <img>, <h3> with title, <time> posted, and <p> excerpt. all read from xml
	public static function news_items($num_items, $page = 1) {
		// open XML file with editorial content
		$dom = new DOMDocument();
		if($dom->load("./xml/news.xml")) : // if it opened, do stuff, else throw error
		
		// get all the news items
		$items = $dom->getElementsByTagName("item");
		
		// check that $num_items isn't more than the number of available news items
		$item_count = $items->length;
		$num_items = ($num_items > $item_count) ? $item_count : $num_items;
		
		// set the offset
		if($page == 0) $page = 1;
		$page = $page * $num_items - $num_items;
	
		// print as many news items as required
		for($i=0; $i<$num_items; $i++) {
			// check that we aren't going too far
			if(($page+$i) >= $items->length) break;
			
			// get the particular news item we're going to display now
			$item = $items->item($page+$i);
			
			// extract the fields
			$id = $item->getAttribute("id");
			$date = $item->getAttribute("date");
			$image = $item->getElementsByTagName("image")->item(0)->nodeValue;
			$title = $item->getElementsByTagName("title")->item(0)->nodeValue;
			$content = $item->getElementsByTagName("content")->item(0)->nodeValue;
			
			// strip CDATA tags
			$title = str_replace("<![CDATA[", "", $title);
			$title = str_replace("]]>", "", $title);
			$content = str_replace("<![CDATA[", "", $content);
			$content = str_replace("]]>", "", $content);
			
			// print article
			echo '<article>';
			echo '<img src="' . $image . '" width="200" height="200" alt="Featured image for this post" />';
			echo '<h3>' . html_entity_decode($title, ENT_COMPAT | ENT_HTML5) . '</h3>';
			echo '<time datetime="' . date("Y-m-d", $date) . '">' . date("n.j.y ga", $date) . '</time>';
			echo '<div class="content-wrap">' . html_entity_decode($content, ENT_COMPAT | ENT_HTML5) . '</div>';
			echo '<a href="./news.php?id=' . $id . '" class="more">&gt;</a>';
			echo '</article>';
		}
		else:
			echo "[Error loading news.xml - check that the file exists in correct directory and reload the page]";
		endif;
	}

	// FUNCTION		news_item
	// PARAMETERS	$id of news item to display
	// RETURNS		-
	// ECHOS		specific <article> containing a 200x200 <img>, <h3> with title, <time> posted, and <p> excerpt. all read from xml
	public static function news_item($id) {
		// check that an ID was passed
		if(!isset($id) || empty($id)) { echo "[Error getting news item - no ID specified]"; return;}
		
		// open XML file with editorial content
		$dom = new DOMDocument();
		if($dom->load("./xml/news.xml")) : // if it opened, do stuff, else throw error
			// get the specified news item
			$item = $dom->getElementById($id);
			
			// extract the fields
			$id = $item->getAttribute("id");
			$date = $item->getAttribute("date");
			$image = $item->getElementsByTagName("image")->item(0)->nodeValue;
			$title = $item->getElementsByTagName("title")->item(0)->nodeValue;
			$content = $item->getElementsByTagName("content")->item(0)->nodeValue;
			
			// strip CDATA tags
			$title = str_replace("<![CDATA[", "", $title);
			$title = str_replace("]]>", "", $title);
			$content = str_replace("<![CDATA[", "", $content);
			$content = str_replace("]]>", "", $content);
			
			// print article
			echo '<article class="single">';
			echo '<img src="' . $image . '" width="200" height="200" alt="Featured image for this post" />';
			echo '<h3><a href="./news.php?id=' . $id . '">' . html_entity_decode($title, ENT_COMPAT | ENT_HTML5) . '</a></h3>';
			echo '<time datetime="' . date("Y-m-d", $date) . '">' . date("n.j.y ga", $date) . '</time>';
			echo '<div class="content-wrap">' . html_entity_decode($content, ENT_COMPAT | ENT_HTML5) . '</div>';
			echo '</article>';
			echo '<a href="./news.php">&lt; Back to news listings</a>';
		else:
			echo "[Error loading news.xml - check that the file exists in correct directory and reload the page]";
		endif;
	}
	
	// FUNCTION		editorial_field (v2)
	// PARAMETERS	specific $field to retrieve from file
	// RETURNS		contents of the specified field
	// ECHOS		-
	public static function editorial_field($field) {	
		// if loading is successful, do stuff. else, throw error
		$dom = new DOMDocument();
		if($dom->load("./xml/editorial.xml")) {			
			// decide which element's contents to return
			switch ($field) {
				case 'url':
					return $dom->getElementsByTagName("image")->item(0)->nodeValue;
				case 'title':
					return html_entity_decode($dom->getElementsByTagName("title")->item(0)->nodeValue, ENT_COMPAT | ENT_HTML5);
				case 'contents':
					$content = $dom->getElementsByTagName("content")->item(0)->nodeValue;
					$content = str_replace("<![CDATA[", "", $content);
					$content = str_replace("]]>", "", $content);
					return html_entity_decode($content, ENT_COMPAT | ENT_HTML5);
				default:
					return "[Error - parameter invalid]";
			}
		} else {
			return "[Error loading editorial.xml - check that file exists in correct directory and reload the page]";
		}
	}
	
	// FUNCTION		add_news (v2)
	// PARAMETERS	$url, $title, and $contents to append to news.xml
	// RETURNS		true on success, false on fail
	// ECHOS		-
	public static function add_news($url, $titletext, $contents) {	
		// load the current file or throw error
		$dom = new DOMDocument();
		if($dom->load("./xml/news.xml")) {		
			// get the news container
			$news = $dom->getElementsByTagName("news")->item(0);
			
			// make new news item
			$item = $dom->createElement("item");
			$item->setAttribute("id", $dom->getElementsByTagName("item")->length + 1);
			$item->setIdAttribute("id", true);
			$item->setAttribute("date", time());
			
			// item's image, title, and content
			$image = $dom->createElement("image", $url);
			$title = $dom->createElement("title");
			$title_cdata = $dom->createCDATASection($titletext);
			$title->appendChild($title_cdata);
			$content = $dom->createElement("content");
			$content_cdata = $dom->createCDATASection($contents);
			$content->appendChild($content_cdata);

			// put it all together
			$item->appendChild($image);
			$item->appendChild($title);
			$item->appendChild($content);
			$news->appendChild($item);
			
			// save the xml file
			$dom->preserveWhiteSpace = true;
			$dom->formatOutput = true;
			if($dom->save("./xml/news.xml")) return true;
			return false;
		} else {
			return "[Error loading news.xml - check that file exists in correct directory and reload the page]";
		}
	}
	
	// PUBLIC STATIC services
	// echos a <section> with 2 <article>s for each service in services.xml
	public static function services() {
		$return;
		
		// $feeds is an array of links to RSS feeds
		$feeds = P2_Utils::get_selected_feeds("services.xml");
		
		// lets display each one!
		foreach($feeds as $feed) {			
			////////// retrieve the feed
			$rss =  new DOMDocument();
			// if there is an error loading the feed, save an error message and go to the next one
			if(!$rss->load($feed)) { 
				$return .= "There was an error loading this feed: $feed\n";
				continue;
			}
			
			// start things off with an section tag, because it will have a heading and contain an article
			$return .= "<section>\n";
			
			// get the title, link, and desc
			$title = $rss->getElementsByTagName("title")->item(0)->nodeValue;
			$time = strtotime($rss->getElementsByTagName("pubDate")->item(0)->nodeValue);
			$link = $rss->getElementsByTagName("link")->item(0)->nodeValue;
			$desc = $rss->getElementsByTagName("description")->item(0)->nodeValue;
			
			// arrange it nicely and stick it in the section
			$return .= "<h3><a href=\"$link\" title=\"$title\">$title</a></h3>\n";
			if(!empty($time) || !$time) $return .= '<time  datetime="' . date("Y-m-d", $time) . '">' . date("n.j.y", $time) . '</time>' . "\n";
			if(!empty($desc)) $return .= "<p class=\"desc\">$desc</p>\n";
			
			for($i=0;$i<2;$i++) {
				// get the item
				$item = $rss->getElementsByTagName("item")->item($i);
				
				// make the article happen
				$return .= "<article>";
				
				// get the title, link, and desc of the item this time
				$title = $item->getElementsByTagName("title")->item(0)->nodeValue;
				$link = $item->getElementsByTagName("link")->item(0)->nodeValue;
				$desc = $item->getElementsByTagName("description")->item(0)->nodeValue;
				
				// populate the article with the rss feed's contents
				$return .= "<h4><a href=\"$link\" title=\"$title\">$title</a></h4>\n<p>$desc</p>\n";
				
				// this article is done!
				$return .= "</article>\n";
			}
			
			$return .= "</section>";
		}
		
		// print out the rss feeds section
		echo $return;
	}

	// FUNCTION		ads
	// PARAMETERS	$mode (default "show") accepts either "show" or "edit". "show" displays the ads, "edit" displays all of them in a form
	// RETURNS		-
	// ECHOS		<img> of classified ads or an editable form of them
	public static function ads($mode = "show") {
		// open XML file with banner locations
		$dom = new DOMDocument();
		if($dom->load("./xml/ads.xml")) { // if it opened, do stuff, else throw error
			// get all the ads
			$ads = $dom->getElementsByTagName("ad");
			
			// storage arrays
			$urls = array();
			$ids = array();
			
			// make two parallel arrays, one with all the URLs, and another with the IDs
			foreach($ads as $ad) {
				$urls[] = $ad->getAttribute("url");
				$ids[] = $ad->getAttribute("id");
			}
						
			// if mode is to display the classified ads
			if($mode == "show") {
				for($i=0;$i<count($urls);$i++) {
					echo '<img src="' . $urls[$i] . '" width="350" class="classified_img" />' . "\n";
				}
			} elseif($mode == "edit") { // if mode is to display all ads in a form
				echo '<p class="formdesc">Tick the checkbox next to an item to remove it on save<br/>ADs are constrained to 350px wide</p>';
				
				// display every ad in a field
				for($i=0; $i<count($ids); $i++) {
					echo '<div class="classified_div">';
					echo '<input type="checkbox" name="remove[]" id="' . $ids[$i] . '" value="' . $ids[$i] . '" />';
					echo '<input type="hidden" name="url-' . $ids[$i] . '" value="' . $urls[$i] . '" />';
					echo '<label for="' . $ids[$i] . '"><img src="' . $urls[$i] . '" width="350" class="classified_img" /></label>';
					echo '</div>';
				}
				
				// display an extra field in case user wants to add a banner
				echo '<input type="url" name="new" class="banner-new" />';
				echo 'New advert to add';
			}
		} else {
			echo "[Error loading ads.xml - check that file exists in correct directory and reload the page]";
		}
	}

	// FUNCTION		save_ads
	// PARAMETERS	$ids, $urls. parallel arrays of the ads to be saved to ads.xml
	// RETURNS		true on success, false on fail
	// ECHOS		-
	public static function save_ads($ids, $urls) {
		// create a new domdocument (we're making a new xml file entirely, because the items have to be updated)
		$newdom = new DOMDocument("1.0", "UTF-8");
		$adsEl = $newdom->createElement("ads");
		
		// setup each individual ad
		for($i=0; $i<count($urls); $i++) {
			$adEl = $newdom->createElement("ad");
			$adEl->setAttribute("url", $urls[$i]);
			$adEl->setAttribute("id", $ids[$i]);
			$adEl->setIdAttribute("id", true);
			$adsEl->appendChild($adEl);
		}
		
		// finish the xml file
		$newdom->appendChild($adsEl);
		$newdom->preserveWhiteSpace = true;
		$newdom->formatOutput = true;
		
		// save the xml file
		if($newdom->save("./xml/ads.xml")) return true;
		return false;
	}

}
?>