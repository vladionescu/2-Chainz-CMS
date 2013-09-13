<?php include_once("./inc/header.php"); ?>
	<?php if(isset($_GET['id']) && !empty($_GET['id'])) : $id = $_GET['id'];?>
    	<section id="news">
        	<?php P2_Utils::news_item($id); ?>
        </section>
    <?php else: ?>
		<?php 
		$page = 1; if(isset($_GET['page']) && !empty($_GET['page'])) $page = $_GET['page'];
        pagination($page);
		?>
        <section id="news">
            <?php global $nr_show; P2_Utils::news_items($nr_show, $page); ?>
        </section>
    <?php endif; ?>
<?php include_once("./inc/footer.php"); ?>