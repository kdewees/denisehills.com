<!DOCTYPE html>
<html>
<head>
    <title>
        <?
        echo isset($title) ? $title : "Elect Denise Hills for Tuscaloosa City School Board Chair";
        ?>
    </title>
    <link href="/css/reset.css" type="text/css" rel="stylesheet">
    <link href="/css/960_12_col.css" type="text/css" rel="stylesheet">
    <link href="/css/style.css" type="text/css" rel="stylesheet">
    <? echo isset($css) ? $css : ""; ?>
    <? echo isset($scripts) ? $scripts : ""; ?>
</head>
<body>
<div id="header-bg"></div>
<div class="container_12" >
    <div class="grid_12" id="header">
        <div id="header-nav"><?= $main_menu; ?></div>
    </div>
</div>
<div class="clear"></div>