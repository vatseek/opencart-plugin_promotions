<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <?php if ($error) { ?>
  <div class="warning"><?php echo $error; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/feed.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
          <a onclick="location = '<?php echo $slideshow; ?>'" class="button"><?php echo $button_slideshow; ?></a>
          <a onclick="location = '<?php echo $insert; ?>'" class="button"><?php echo $button_insert; ?></a>
          <a onclick="$('form').submit();" class="button"><?php echo $button_delete; ?></a></div>
    </div>
    <div class="content">
        <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
            <table class="list">
                <thead>
                <tr>
                    <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
                    <td class="center"><?php echo $column_title; ?></td>
                    <td class="center"><?php echo $column_status; ?></td>
                    <td class="center"><?php echo $column_order; ?></td>
                    <td class="center"><?php echo $column_image; ?></td>
                    <td class="center"><?php echo $column_actions; ?></td>
                </tr>
                </thead>
                <tbody>
                    <? foreach( $promotions as $promotion): ?>
                        <tr class="filter">
                            <td><input type="checkbox" name="selected[]" value="<?php echo $promotion['promotion_id']; ?>" /></td>
                            <td><?php echo $promotion['title']?></td>
                            <td class="center"><?php echo $promotion['status'] ? "<span style=\"color:green;\">{$entity_active}</span>" : "<span style=\"color:red;\">{$entity_inactive}</span>";?></td>
                            <td class="center"><?php echo $promotion['order']?></td>
                            <td class="center"><?php echo $promotion['picture']?></td>
                            <td class="center"><a href="<?php echo $promotion['action_url']?>"><?php echo $button_edit ?></a></td>
                        <tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </form>







    </div>
  </div>
</div>
<?php echo $footer; ?>