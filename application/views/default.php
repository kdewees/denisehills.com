<?
$title = isset($title) ? $title : "";
$css = isset($css) ? $css : "";
$scripts = isset($scripts) ? $scripts : "";

$header_data = array(
    "title" => $title,
    "scripts" => $scripts,
    "css" => $css,
    "logo" => $logo,
    "main_menu" => $main_menu
);

$this->load->view("header", $header_data);
?>

<div class="container_12">
    <div class="grid_12" id="page">
        <div class="padding_20" id="content">
            <?= $content; ?>
        </div>
    </div>
    <div class="clear"></div>
</div>

<?
$footer_data = array(
    "footer" => $footer,
    "jquery" => $jquery);
$this->load->view("footer", $footer_data);
?>

</body>
</html>