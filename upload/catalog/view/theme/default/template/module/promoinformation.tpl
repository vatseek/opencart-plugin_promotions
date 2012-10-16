<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <span><image src="<?php echo $image_url?>" alt="<?php echo $image?>"></span>
  <?php echo $description; ?>
  <!--div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div-->
  <div class="product-grid">
      <?php foreach($products as $product): ?>
      <div>
          <?php if ($product['thumb']) { ?>
          <div class="image">
              <a href="<?php echo $product['href']; ?>">
              <img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a>
              <span class="badge-small"></span>
          </div>
          <?php } ?>
          <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
          <div class="description"><?php echo $product['description']; ?></div>
          <?php if ($product['price']) { ?>
          <div class="price">
              <?php if (!$product['special']) { ?>
              <?php echo $product['price']; ?>
              <?php } else { ?>
              <span class="price-old"><?php echo $product['price']; ?></span>
              <br>
              <span class="price-new"><?php echo $product['special']; ?></span>
              <?php } ?>
          </div>
          <?php } ?>
          <div class="cart">
              <input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
          </div>
          <div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $button_wishlist; ?></a></div>
          <div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $button_compare; ?></a></div>
      </div>
      <?php endforeach; ?>
  </div>

  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>