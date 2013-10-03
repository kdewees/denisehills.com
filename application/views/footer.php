<div class="clear"></div>
<div class="container_12">
    <div class="grid_12" id="footer">
        <div id="footer_left" class="grid_4 alpha">
            <div class="padding_10"><?= $footer["left"]; ?></div>
        </div>
        <div id="footer_middle" class="grid_4">
            <div class="padding_10"><?= $footer["middle"]; ?></div>
        </div>
        <div id="footer_right" class="grid_4 omega">
            <div class="padding_10"><?= $footer["right"]; ?></div>
        </div>
    </div>
</div>
<div class="clear"></div>
<?
if (!empty($jquery))
{
    echo "<script type='text/javascript'>
$(document).ready(function() {
    $jquery
});
</script>" . nl(2);
}
?>