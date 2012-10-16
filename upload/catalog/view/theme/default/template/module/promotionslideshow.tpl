<?php if (isset($promotions) && (sizeof($promotions) > 0)): ?>
<div class="slideshow promo">
  <div id="promotionslideshow<?php echo $module; ?>" class="nivoSlider" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;">
    <?php foreach ($promotions as $promotion): ?>
        <a href="<?php echo $promotion['item_link']; ?>"><img src="<?php echo $promotion['image_link']; ?>" alt="" /></a>
    <?php endforeach; ?>
  </div>
</div>
<script type="text/javascript"><!--
$(document).ready(function() {
	$('#promotionslideshow<?php echo $module; ?>').nivoSlider();
});
--></script>
<?php endif;?>