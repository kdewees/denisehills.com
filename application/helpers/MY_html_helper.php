<?php
//MY_html_helper.php

function tab($num_tabs = 1)
{
	$text = "";
	for ($i = 1; $i <= $num_tabs; $i++)
	{
		$text .= "\t";
	} //end for
	return $text;
} //end function tab

function nl($num_nls = 1)
{
    $text = "";
    for ($i = 1; $i <= $num_nls; $i++)
    {
        $text .= "\n";
    }
    return $text;
}

function header_tab($string)
{
    GLOBAL $config;

    return $config["header_tabs"] ? "\t" . $string : $string;
}

function script($script_url = '', $script_dir = 'js', $script_type = 'text/javascript')
{
    if (strpos($script_url, "http://") === FALSE)
        $src = "/" . $script_dir . "/" . $script_url;
    else
        $src = $script_url;

    $script = header_tab("<script src='" . $src . "' type='" . $script_type . "'></script>\n");

	return $script;
} //end function script

function t_link_tag($params)
{
    return header_tab(link_tag($params));
} //end function t_link_tag
?>