<?php include('style.inc.php'); ?>

<ul id="<?php echo $id; ?>" id="<?php echo $id; ?>" data-counter="true" class="social-sharing social-sharing-style-20">
    <li class="twitter" data-lang="<?php echo $tw_lang; ?>" data-url="<?php echo $tw_url; ?>"<?php echo (!empty($tw_button_text) ? ' data-title="'.$tw_button_text.'"' : ''); ?><?php echo (!empty($tw_button_text) ? ' data-text="'.$tw_button_text.'"' : ''); ?>></li>
    <li class="facebook" data-lang="<?php echo $fb_lang; ?>" data-url="<?php echo $fb_like_url; ?>"<?php echo (!empty($fb_button_text) ? ' data-title="'.$fb_button_text.'"' : ''); ?><?php echo (!empty($fb_text) ? ' data-text="'.$fb_text.'"' : ''); ?>></li>
    <li class="googlePlus" data-lang="<?php echo $g_lang; ?>" data-url="<?php echo $g_url; ?>"<?php echo (!empty($g_button_text) ? ' data-title="'.$g_button_text.'"' : ''); ?><?php echo (!empty($g_button_text) ? ' data-text="'.$g_button_text.'"' : ''); ?>></li>
</ul>

<script>
if (typeof opjq === 'function') {
    opjq(window).trigger('op_init_sharrre');
}
</script>