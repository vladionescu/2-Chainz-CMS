<?php
// RSSFeed.class.php
// Generate an RSS feed file in the current dir called project2.rss
// Vlad Ionescu @ RIT
// 539 - Project 2 (2012-3)

class RSSFeed{
	// upon making an instance of this class the constructor will parse
	// news.xml and generate an RSS 2.0 feed from it, placed in /project2/project2.rss
	public function __construct() {
		////////// news.xml
		// open the file
		$xml = new DOMDocument();
		if(!$xml->load("./xml/news.xml")) return 1;
		
		// get the news items
		$news = $xml->getElementsByTagName("item");
		
		////////// RSS 2.0
		// create a new dom
		$dom = new DOMDocument('1.0', 'UTF-8');
		// make the rss element with a version="2.0" attr
		$rssElement = $dom->createElement('rss');
		$rssElement->setAttribute('version', '2.0');
		// make the channel element where all the rss things will live
		$channel = $dom->createElement('channel');
		// fill out the mandatory rss info (title, link, description)
		$channel->appendChild( $dom->createElement('title', 'Vlad Ionescu\'s Project 2') );
		$channel->appendChild( $dom->createElement('link', 'http://people.rit.edu/~vxi6514/539/project2/project2.rss') );
		$channel->appendChild( $dom->createElement('description', 'Project 2\'s RSS 2.0 Feed') );

		// now lets add the news item
		foreach($news as $article) {
			// make the item container
			$item = $dom->createElement('item');
			
			// prepare the title
			$title = $article->getElementsByTagName("title")->item(0)->nodeValue;
			$title = str_replace("<![CDATA[", "", $title);
			$title = str_replace("]]>", "", $title);
			// append the title
			$item->appendChild( $dom->createElement('title', $title) );
			
			// append the link
			$item->appendChild( $dom->createElement('link', 'http://people.rit.edu/~vxi6514/539/project2/news.php?id=' . $article->getAttribute("id")) );
			
			// prepare the contents (description)
			$content = $article->getElementsByTagName("content")->item(0)->nodeValue;
			$content = str_replace("<![CDATA[", "", $content);
			$content = str_replace("]]>", "", $content);
			// append the content (description)
			$item->appendChild( $dom->createElement('description', $content) );
			
			// append the pubDate
			$item->appendChild( $dom->createElement('pubDate', date("D F j, Y H:i:s O", $article->getAttribute("date")) ) );
			
			// append the guid
			$item->appendChild( $dom->createElement('guid', 'http://people.rit.edu/~vxi6514/539/project2/news.php?id=' . $article->getAttribute("id")) );
			$channel->appendChild($item);
		}
		
		// put it all together and save the RSS
		$rssElement->appendChild($channel);
		$dom->appendChild($rssElement);
		$dom->preserveWhiteSpace = true;
		$dom->formatOutput = true;
		if(!$dom->save("./project2.rss")) echo '[Error saving project2.rss]';
	}
}
?>