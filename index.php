<?php include_once("./inc/header.php"); ?>
	<!--<div id="letter-editor">
    	<?php //letter_from_the_editor(); ?>
    </div>
    <section id="news">
    	<h2>NEWS</h2>
        <?php //news_items(3); ?>
    </section>-->
    <section id="news">
    	<h2>NEWS</h2>
        <?php P2_Utils::news_items(3); ?>
    </section>
    <aside id="ext-feeds">
    	<h2>EXTERNAL RSS</h2>
    	<?php P2_Utils::ext_feeds(); ?>
    </aside>
    <div id="letter-editor">
    	<?php P2_Utils::letter_from_the_editor(); ?>
    </div>
<?php include_once("./inc/footer.php"); ?>