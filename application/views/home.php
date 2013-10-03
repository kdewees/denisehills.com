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
        <div class="grid_8 alpha" id="home1">
            <div class="padding_20"><?= $other["home1"]; ?></div>
        </div>
        <div class="grid_4 omega" id="home2"><?= $other["home2"]; ?></div>
    </div>
</div>

<?
$footer_data = array(
                "footer" => $footer,
                "jquery" => $jquery);
$this->load->view("footer", $footer_data);
?>

</body>
</html>