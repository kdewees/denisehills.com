<?php
//MY_date_helper.php

function mysql_datetime_to_array($datetime)
{
	//2013-01-01 01:00:00 mysql_dt_format
	/* returned array format:
		[0] YYYY-mm-dd
		[1] hh:mm:ss
		[2] YYYY
		[3] mm
		[4] dd
		[5] hh
		[6] mm
		[7] ss
		[8] unix timestamp
	*/

	$dt = array();

	if (strpos($datetime, ' '))
	{
		$dt = explode(' ', $datetime);
	}
	else
	{
		array_push($dt, $datetime, '00:00:00');
	} //end if

	$return_dt = array_merge($dt, explode('-', $dt[0]));
	
	$return_dt = array_merge($return_dt, explode(':' , $dt[1]));
	
	$return_dt[] = mktime($return_dt[5], $return_dt[6], $return_dt[7], $return_dt[3], $return_dt[4], $return_dt[2]);

	return $return_dt;
} //end function mysql_datetime_to_array

function print_date_dropdown($field_name = 'date', $current_datetime = '', $readonly = FALSE, $class = "")
{
    if (($class !== "") && (strpos($class, "class=") !== -1))
        $class = substr($class, strpos($class, "'"));

	if ($current_datetime === '')
	{
		$current_datetime = now();
		$current_month = date('n', $current_datetime);
		$current_year = date('Y', $current_datetime);
		$current_day = date('j', $current_datetime);
	}
	else
	{
		$date_array = explode("-", $current_datetime);
		$current_year = $date_array[0];
		$current_month = $date_array[1];
		$current_day = $date_array[2];
	} //end if

    $date_items = array();
	$dropdown = "<select name='{$field_name}_month' class='date-dropdown $class'>\n";
	for($i = 1; $i <= 12; $i++)
	{
		$this_month = date('M', mktime(1, 1, 1, $i));

        if ($i === (int)$current_month)
        {
            $selected = 'selected';
            $date_items[] = $this_month;
        }
        else
        {
            $selected = '';
        }

        $dropdown .= "<option value='$i' $selected>$this_month</option>\n";
	} //end for
	$dropdown .= "</select>\n";
	
	$dropdown .= "<select name='{$field_name}_day' class='date-dropdown $class'>\n";
	for ($i = 1; $i <= 31; $i++)
	{
		if ($i === (int)$current_day)
        {
            $selected = 'selected';

            $date_array[] = $i;
        }
		else
        {
			$selected = '';
        }
			
		$dropdown .= "<option $selected>$i</option>\n";
	} //end for
	$dropdown .= "</select>\n";
	
	$dropdown .= "<select name='{$field_name}_year' class='date-dropdown $class'>\n";
	for ($i = $current_year; $i <= $current_year + 6; $i++)
	{
		if ($i === (int)$current_year)
        {
			$selected = 'selected';

            $date_array[] = $i;
        }
		else
        {
			$selected = '';
        }
			
		$dropdown .= "<option $selected>$i</option>\n";
	} //end for
	$dropdown .= "</select>\n";

    if ($readonly)
    {
        if ($class !== "")
            $class = "class='$class'";
        return form_input($field_name, implode(" ", $date_array), $class . "readonly");
    }
    else
    {
        return $dropdown;
    }
} //end function

function print_time_dropdown($field_name = 'time', $current_time = '', $readonly = FALSE, $class = "")
{
    if (($class !== "") && (strpos($class, "class=") !== -1))
        $class = substr($class, strpos($class, "'"));

	$am_selected = '';
	$pm_selected = '';

    $time_array = array();

	if (isset($current_time) && $current_time != '')
	{
		$time_parts = explode(':', $current_time);
		$current_hours = (int)$time_parts[0];
		$current_mins = (int)$time_parts[1];

		if ($current_hours >= 12 && $current_hours != 24)
		{
			$pm_selected = 'selected';
			if ($current_hours != 12)
				$current_hours -= 12;
		}
		else
		{
			$am_selected = 'selected';

			if ($current_hours == 24)
				$current_hours = 12;
		} //end if

        $time_array[] = $current_hours . ":" . $current_mins;
        $time_array[] = ($am_selected === "selected") ? "am" : "pm";
	}
	else
	{
		$current_hours = '99';
		$current_mins = '99';
		$am_selected = 'selected';
	} //end if

	//print hours dropdown
	$dropdown = "<select name='{$field_name}_hours' class='date-dropdown $class'>\n";
	$dropdown .= "<option value='0'>--</option>\n";
	for($i = 1; $i <= 12; $i++)
	{
		if ($i === (int)$current_hours)
			$selected = 'selected';
		else
			$selected = '';
			
		$dropdown .= "<option value='$i' $selected>$i</option>\n";
	} //end for
	$dropdown .= "</select>\n";

	$dropdown .= "<select name='{$field_name}_mins' class='date-dropdown $class'>\n";
	$dropdown .= "<option value='0'>--</option>\n";
	for($i = 0; $i <= 45; $i+=15)
	{
		if ($i === (int)$current_mins)
			$selected = 'selected';
		else
			$selected = '';

		$print_mins = $i < 10 ? "0$i" : $i;
			
		$dropdown .= "<option value='$i' $selected>$print_mins</option>\n";
	} //end for
	$dropdown .= "</select>\n";

	$dropdown .= "<select name='{$field_name}_am_pm' class='date-dropdown $class'><option $am_selected>am</option><option $pm_selected>pm</option></select>\n";

    if ($readonly)
    {
        if ($class !== "")
            $class = "class='$class'";
        return form_input($field_name, implode(" ", $time_array), $class . "readonly");
    }
    else
    {
	    return $dropdown;
    }
} //end function print_time_dropdown

function format_time($time)
{
	$time_parts = explode(':', $time);

	if ($time_parts[0] >= 12)
	{
		$am_pm = 'pm';

		if ($time_parts[0] > 12)
			$time_parts[0] = $time_parts[0] - 12;
	}
	else
	{
		$time_parts[0] = (int)$time_parts[0];
		$am_pm = 'am';
	} //end if

	return $time_parts[0] . ":" . $time_parts[1] . " " . $am_pm;
} //end function format_time

function format_datetime_input($field_name, $datetime = '', $readonly = FALSE, $class = "")
{
    if ($datetime == '')
        $datetime = date("Y-m-d H:i:s");

    if (($class !== "") && (strpos($class, "class=") === -1))
        $class = "class='$class' ";

    $dt_array = mysql_datetime_to_array($datetime);
    /* returned array format:
		[0] YYYY-mm-dd
		[1] hh:mm:ss
		[2] YYYY
		[3] mm
		[4] dd
		[5] hh
		[6] mm
		[7] ss
		[8] unix timestamp
	*/

    if ($readonly)
        $date_value = date("M d, Y", $dt_array[8]);

    if ($dt_array[5] == '00' && $dt_array[6] == '00' && $dt_array[7] == '00')
    {
        if ($readonly)
            return form_input($field_name, $date_value, $class . "size='30' readonly");
        else
            return print_date_dropdown($field_name, $dt_array[0]);
    }
    else
    {
        if ($readonly)
            return form_input($field_name, $date_value . " at " . format_time($dt_array[1]), $class . "size='30' readonly");
        else
            return print_date_dropdown($field_name, $dt_array[0]) . print_time_dropdown($field_name, $dt_array[1]);
    }
}
?>