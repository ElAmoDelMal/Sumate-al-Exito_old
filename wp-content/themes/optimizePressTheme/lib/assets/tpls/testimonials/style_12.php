<?php include('style.inc.php'); ?>

<blockquote id="<?php echo $id; ?>" class="testimonial testimonial-style-3 testimonial-style-serif">
	<?php echo $content ?>
	<cite><span>- <?php echo $name ?>,
		<?php if (trim($href) != ''): ?>
			<a href="<?php echo $href ?>" target="_blank"><?php echo $company ?></a>
		<?php else: ?>
			<span class="op-testimonial-company"><?php echo $company ?></span>
		<?php endif; ?>
	</span></cite>
</blockquote>