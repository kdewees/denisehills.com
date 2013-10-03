<? echo doctype(); ?>
<html>
<head>
<?
echo link_tag('css/reset.css');
echo link_tag('css/text.css');
echo link_tag('css/960_12_col.css');
echo link_tag('css/admin.css');
echo empty($css) ? "" : $css;
?>
	<title><?= $website_name ?> > Admin Section</title>
<?
echo empty($script) ? "" : $script;
?>
</head>
<body>
<div class='container_12'>
	<div id='header' class='grid_12'></div>
	<div class='clear'></div>
	<div id='header-nav' class='grid_12'>
<?
echo anchor('pages', "View $website_name Website");
echo anchor('admin', 'Admin Home');
?>
	</div><!-- header-nav -->
	<div class='clear'></div>
	<div id='page' class='grid_12'>
<?
$data = array(
	'nav' => $nav,
	'table' => $table,
	'message' => $message,
	'content' => $content,
    'website_name' => $website_name);
$this->load->view($inner_view, $data);
?>
	</div><!-- page -->
</div><!-- container_12 -->
<?
if (!empty($jquery))
{
    echo "<script type='text/javascript'>\n";
    echo "$(document).ready(function() {\n";
    echo $jquery . "\n";
    echo "});\n";
    echo "</script>\n";
}
?>
</body>
</html>