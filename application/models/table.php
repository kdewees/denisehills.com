<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Table extends CI_Model {

    var $name   = '';
    var $scripts = array();
    var $css = array();
    var $jquery = array();

/*======================================================= construct */
    function __construct($table_name = 'all')
    {
        // Call the Model constructor
        parent::__construct();

        $this->name = $table_name;
        //$this->load->library('pagination');
    } //end function construct

/*======================================================= list_table */
    function list_table($order_by_string = "", $asc_desc = "asc", $message = "")
    {
        $table_name = $this->name;                                              // get table name from object
        $site_url = site_url();

        $order_by = array();
        if ($order_by_string !== "")                                            // init order_by array
            $order_by[] = array("field" => $order_by_string, "dir" => $asc_desc);

        // if this table has a parent field, add it to the order_by clause
        if ($this->db->field_exists("parent", $table_name) && !$this->_is_in_order_by("parent", $order_by))
            $order_by[] = array("field" => "{$table_name}.parent", "dir" => "asc");

        // if this table has a name field, add it to the order_by clause
        if ($this->db->field_exists("name", $table_name) && !$this->_is_in_order_by("name", $order_by))
            $order_by[] = array("field" => "{$table_name}.name", "dir" => "asc");

        // load the list of fields in each table from the flat table file
    	$fields = $this->load_field_list();

        $sort_order = $this->_is_asc_desc("id",$order_by);
        // begin to create the header row for the display table (action column & id column)
    	$th_row = "<thead>
    	<tr>
    	    <th class='actions'>
    	        Actions<br>
    	        <input type='checkbox' id='check-all'>
                <a href='javascript://' id='mass_edit'><img src='{$site_url}images/admin/edit.png' alt='edit record' width='16px' height='16px'></a>
                <a href='javascript://' id='mass_delete'><img src='{$site_url}images/admin/trash.png' alt='delete record?' width='16px' height='16px'></a>
            </th>
            <th class='id'>" .
                anchor("admin/show/" . $this->name . "/id/" . $sort_order, "ID" . nbs() . $this->_get_order_arrow($sort_order)) .
            "</th>" . nl();

        //add the id to the select clause of the query
        $this->db->select("{$table_name}.id");

        //initialize loop variables
    	$num_fields_printed = 0;                                                // count number of fields
        $join_table_name = 'a';
        $join_table_name2 = 'aa';

        //loop through the fields to be shown in the table, adding them to header row and query
    	foreach($fields as $field)
    	{
    		if (isset($field['list']) && ($field['list'] === FALSE))            // if not display field
    			continue;

            $parent_exists = FALSE;                                             // init

            // add this field name to query select clause
            $this->db->select("{$table_name}.{$field['name']}");

            // if this was a select field, then we need to do a join on another table
            if (isset($field['type']) && ($field['type'] === "select" or $field['type'] === "readonly_select"))
            {
                $this->db->join("{$field['select_table']} AS $join_table_name", "$join_table_name.id = {$table_name}.{$field['name']}", "LEFT OUTER");

                // if the select field is an array, we're using more than one field as the display
                // (like first and last name, for instance)
                if (is_array($field['select_field']))
                {
                    // we're going to concatinate these fields into a field we'll call "select_display"
                    $select_fields = "CONCAT_WS(' ',{$join_table_name}.";
                    $select_fields .= implode(",{$join_table_name}.", $field['select_field']);
                    $select_fields .= ") AS {$field['name']}_display";
                }
                else
                {
                    // just one field to be shown as the "select_display" field
                    $select_fields = "{$join_table_name}.{$field['select_field']} AS {$field['name']}_display";
                } //end if

                // add this field to our select clause
                $this->db->select($select_fields, FALSE);

                // if the field has a parent, we'll include it in the display field
                if ($this->db->field_exists('parent', $field['select_table']) && $field['name'] !== "parent")
                {
                    // add a join clause to our query for this table
                    $this->db->join("{$field['select_table']} AS $join_table_name2", "{$join_table_name2}.id = {$join_table_name}.parent", "LEFT OUTER");

                    // add the name field from the parent table to our select clause
                    $this->db->select("{$join_table_name2}.name as {$field['name']}_parent");

                    $join_table_name2++;                                        // increment table alias2

                    $parent_exists = TRUE;                                      // set flag
                }

                $order_by_key = $this->_is_in_order_by($field["name"], $order_by);
                if ($order_by_key !== FALSE) // field is in order_by
                {
                    if ($parent_exists)
                    {
                        // if field has parent field, order_by parent, display
                        $order_by[$order_by_key]["field"] = $field["name"] . "_parent, " . $field["name"] . "_display";
                    }
                    else
                    {
                        // if no parent, just order by display
                        $order_by[$order_by_key]["field"] = $field["name"] . "_display";
                    }
                }

                $join_table_name++;                                             // increment table alias1
            }

            $field_asc_desc = $this->_is_asc_desc($field["name"], $order_by);
            $asc_desc_img = $this->_get_order_arrow($field_asc_desc);
            $display_name = ucwords(str_replace("_", " ", $field['name']));
            $link_address = "admin/show/{$this->name}/{$field['name']}/{$field_asc_desc}";
            // add this field to our table header row
    		$th_row .= "<th>" . anchor($link_address, $display_name . nbs() . $asc_desc_img) . "</th>" . nl();
    		
    		$num_fields_printed++;                                              // increment counter
    	}
    	$th_row .= "</tr></thead>" . nl();                                             // finish up our heading row

        // this is the table with the "add new record" link
    	$add_new_row = "<table>
    <tr>
    	<td class='actions'>
    		<a href='{$site_url}admin/edit/$table_name'><img src='{$site_url}images/admin/add.png' alt='add new record' width='16px' height='16px' />" . nbs(3) . "Add New Record</a>
    	</td>
    </tr>
</table>" . nl(2);

        // add an order_by clause to our query (change array to comma-separated list)
        $num_order_by = count($order_by);
        for($i=0; $i<$num_order_by; $i++)
        {
            $this->db->order_by($order_by[$i]["field"], $order_by[$i]["dir"]);
        }

    	$query = $this->db->get($table_name);                                   // perform the query

        //echo $this->db->last_query();                                           // DEBUG

        // write the header for the show table page
    	$table = "<h2>Show <b>$table_name</b> Table</h2>" . nl(2);

        // add table with add new record link
        $table .= $add_new_row . nl();
        $table .= "<table id='table-list'>" . nl();
        $table .= $th_row . nl();                                               // add the header row

    	$i = 1;                                                                 // initiate row counter
    	foreach($query->result_array() as $row)                                 // iterate through result
    	{
    		if ($i % 2 === 0)                                                   // check if odd or even row
    			$even_odd = 'even';
    		else
    			$even_odd = 'odd';
    	
    		$id = $row['id'];                                                   // for ease of typing

            // the beginning of the table row for this record - has record id, and edit and delete icons
    		$table .= "<tr class='$even_odd'>
    		<td class='actions'>
    		    <input name='delete_{$id}' type='checkbox'>
    			<a href='{$site_url}admin/edit/$table_name/$id'>
    				<img src='{$site_url}images/admin/edit.png' alt='edit record' width='16px' height='16px' /></a>
    			<a href='javascript:delete_confirm($id)'>
    				<img src='{$site_url}images/admin/trash.png' alt='delete record?' width='16px' height='16px' /></a>
    		</td>
    		<td class='id'><a href='{$site_url}admin/edit/$table_name/$id'>$id</a></td>" . nl();

            // loop through fields in the table that are to be displayed
    		foreach($fields as $field)
    		{
    			if (isset($field['list']) && ($field['list'] === FALSE))        // check if should display
                    continue;

                $value = "";                                                    // blank out value variable

                if (isset($row[$field["name"] . "_parent"]))                    // if field_parent in result
                {
                    $value .= $row[$field["name"] . "_parent"] . " &gt; ";      // add parent > value
                }

                if (isset($field['type']) && ($field['type'] === "select" or $field['type'] === "readonly_select"))                                                            // if select field type
                {
                    if ($row[$field["name"] . "_display"] == NULL)
                        $value .= "<span class='error'>NONE</span>";            // set to NONE if empty val
                    else
                        $value .= $row[$field["name"] . "_display"];            // else set to display val
                }
                else
                {
                    $value .= $row[$field['name']];                             // else set to val
                }

                // add table cell with value for this field for this row
    			$table .= "<td><a href='{$site_url}admin/edit/$table_name/$id'>$value</a></td>" . nl();
    		}
    		
    		$table .= "</tr>" . nl();                                           // end table row
    		$i++;                                                               // increment counter
    	}
    	$table .= "</table>" . nl(2);                                           // end table
    		
    	return $table;                                                          // return table string
    } //end function list_table

/* ====================================================== _is_asc_desc */
    private function _is_asc_desc ($field_name, $order_by)
    {
        $num_order_by = count($order_by);

        for($i = 0; $i < $num_order_by; $i++)
        {
            if ($order_by[$i]["field"] == $field_name)
            {
                if ($order_by[$i]["dir"] == "asc")
                    return "desc";
                else
                    return "asc";
            }
        }
        return "asc";
    }

/* ====================================================== _get_order_arrow */
    private function _get_order_arrow($asc_desc)
    {
        $desc_icon = "<img src='/images/admin/down_arrow.png' alt='sort descending'>";
        $asc_icon = "<img src='/images/admin/up_arrow.png' alt='sort ascending'>";

        if ($asc_desc == "asc")
            return $asc_icon;
        else
            return $desc_icon;
    }

/* ====================================================== _is_in_order_by */
    private function _is_in_order_by($field_name, $order_by)
    {
        $num_order_by = count($order_by);

        for ($i=0; $i < $num_order_by; $i++)
        {
            if ($order_by[$i]["field"] == $field_name)
                return $i;
        }
        return FALSE;
    }

/*======================================================= edit_table */
    public function edit_table($record_id = "insert")
    {
    	$itemname = $this->_get_itemname();
        $mult = "< multiple values >";
    
    	if ($record_id === "insert")            // if this to be an insert...
    	{                                       // set variables for the form
    		$action = "insert";                 // to be used in the form action
    		$legend = "Insert New $itemname";   // to be used on the form legend
    		$hidden_fields = form_hidden("table", $this->name); // hidden fields necessary (form name)
    		$button = "Insert!";                // set the label for the form submit button
			$reset_button = "Empty Form";           // set the label for the form reset button
    	}
    	else                                    // there is a record_id present, so it's an update
    	{                                       // set variables for the form
            // check to see if there are multiple ids to edit
            if (strpos($record_id, "/")) // more than one id present
            {
                $record_id_array = explode("/", $record_id);
                $plural = "s";
                $this->db->where_in("id", $record_id_array);
            }
            else
            {
                $plural = "";
                $this->db->where("id", $record_id);
                $mult = "";
            }

    		$action = "update";                 // form action
    		$legend = "Update $itemname ID{$plural}: $record_id";    // form legend
    		$hidden_fields = form_hidden("record_id", $record_id);  // hidden fields necessary (record id)
    		$hidden_fields .= form_hidden("table", $this->name);    // hidden fields necessary (table name)
    		$button = "Update!";                // set the label for the form submit button
			$reset_button = "Reset Form";       // set the label for the form reset button

            $query = $this->db->get($this->name);
            //echo $this->db->last_query();                                         // DEBUG
            $i = 0;
            $the_row = array();
            $compare_row = array();
            foreach($query->result_array() as $row)
            {
                if ($i == 0)
                {
                    $compare_row = $row;
                    $i++;
                    continue;
                }

                while (list($key, $val) = each($row)) {
                    if ((!isset($the_row[$key]) or ($the_row[$key] !== $mult)) && ($compare_row[$key] !== $val))
                    {
                        $the_row[$key] = $mult;
                    }
                    else
                    {
                        $the_row[$key] = $compare_row[$key];
                    }
                }
                $i++;
            }

            //we have to get the fields from the db to fill in the form with current values for record
            //$query = $this->db->get_where($this->name, array('id' => $record_id));
            if ($i > 1)
                $row = $the_row;
            else
                $row = $compare_row;

            //$row = $query->row_array();         // create an associative array from the record data
    	} //end if
    	
    	$fields = $this->load_field_list();     // get the table and field info from "tables.txt" to create the form

        $multiform = FALSE;                     // initialize the multi-part form flag to false

        $form = "";                             // initialize the string variable that the form will be saved in to a blank string
    	foreach($fields as $field)              // step through the fields in the form to create the form
    	{
            $description = "";                  // initialize the description variable to a blank string

            $field_name = $field['name'];       // set the field name to the one we got from "tables.txt"
    		$print_field_name = ucwords(str_replace('_', ' ', $field_name)) . ': '; // make the field name user-readable by replacing underscores with spaces, and then uppercasing the first letters of words

            $form .= form_fieldset();           // open a fieldset (which will draw a line around the field and description to visually separate it from the other fields)
    		if (isset($field['description']))   // if there is a description present for this field...
    		{
                // then create a div with the description in it
    			$description = "<div class='form-description' style='width: 100%;'>(" . $field['description'] . ")</div>\n";
    		}

            $form .= form_label($print_field_name, $field_name);    // add a label for the form input
    		
    		$field_type = isset($field['type']) ? $field['type'] : 'text';    		
    		$value = isset($row[$field_name]) ? stripslashes($row[$field_name]) : "";

            $readonly = "";
    		switch($field_type)
    		{
                case "readonly_text":
                    $readonly = "readonly";
    			case "text":
		    		$form .= form_input($field_name, $value, $readonly);
    				break;
    			case "password":
    				$form .= form_password($field_name, $value);
    				break;
                case "textarea_editor":
                    if (!in_array(t_link_tag("wysiwyg/jquery.cleditor.css"), $this->css))
                    {
                        $this->css[] = t_link_tag("wysiwyg/jquery.cleditor.css");
                        $this->scripts[] = script("jquery-1.7.2.js");
                        $this->scripts[] = script("jquery.cleditor.min.js", "wysiwyg");
                    }

                    $this->jquery[] = tab() . "$(\"#" . $field_name . "\").cleditor({
          width:        650,
          height:       400,
          controls:     // controls to add to the toolbar
                        \"bold italic underline | style | \" +
                        \"color highlight removeformat | bullets numbering | outdent \" +
                        \"indent | alignleft center alignright justify | undo redo | \" +
                        \"rule image link unlink | cut copy paste pastetext | print source\",
          colors:       // colors in the color popup
                        \"000000 fffefa c5b497 d0c5b0 \" +
						\"e17e00 807b6c ff8f00 ff0000 \",
          sizes:        // sizes in the font size popup
                        \"1,2,3,4,5,6,7\",
          styles:       // styles in the style popup
                        [[\"Paragraph\", \"<p>\"], [\"Header 1\", \"<h1>\"], [\"Header 2\", \"<h2>\"],
                        [\"Header 3\", \"<h3>\"],  [\"Header 4\",\"<h4>\"],  [\"Header 5\",\"<h5>\"],
                        [\"Header 6\",\"<h6>\"]],
          useCSS:       true, // use CSS to style HTML when possible (not supported in ie)
          docType:      // Document type contained within the editor
                        '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">',
          docCSSFile:   // CSS file used to style the document contained within the editor
                        \"/css/new_style.css\",
          bodyStyle:    // style to assign to document body contained within the editor
                        \"margin:4px; cursor:text;\"
        });";

                    $data = array(
                        'name' => $field_name,
                        'value' => $value,
                        'id' => $field_name);
                    $form .= form_textarea($data);
                    break;
    			case "textarea":
    				$data = array(
    					'name' => $field_name,
    					'value' => $value,
    					'rows' => '10',
    					'cols' => '60');
    				$form .= form_textarea($data);
    				break;
                case "readonly_select":
                    $readonly = "readonly";
    			case "select":
					if (is_array($field['select_field']))
					{
						$select_fields = "CONCAT_WS(' '," . $field['select_table'] . ".";
						$select_fields .= implode("," . $field['select_table'] . ".", $field['select_field']);
						$select_fields .= ") AS select_display";
					}
					else
					{
						$select_fields = $field['select_table'] . "." . $field['select_field'] . " AS select_display";
					} //end if

                    if ($this->db->field_exists('parent', $field['select_table']))
                    {
                        $this->db->join($field['select_table'] . " AS j", "j.id = " . $field['select_table'] . ".parent", "LEFT OUTER");
                        $this->db->select("j.name as parent_name");
                        $this->db->select("j.id as parent_id");
                    }

					$this->db->select($select_fields, FALSE);
    				$this->db->select($field['select_table'] . ".id");
                    if (isset($field['select_where']))
                        $this->db->where($field['select_where']);

    				$query = $this->db->get($field['select_table']);
    				$select_array = $query->result_array();

					if (isset($field['multiple']) && $field['multiple'] === TRUE)
					{
						$select_list = array(0 => 'choose one or more');
					}
					else
					{
						$select_list = array(0 => 'choose one');
					} //end if

    				foreach($select_array as $select_item)
    				{
                        //echo print_r($select_item) . br();
                        if (isset($select_item["parent_id"]) && $select_item["parent_id"] > 0)
                        {
                            $select_display = $select_item["parent_name"] . " > " . $select_item["select_display"];
                        }
                        else
                        {
                            $select_display = $select_item["select_display"];
                        }

    					$select_list[$select_item["id"]] = $select_display;
    				} //end foreach

                    if ($field_type === "readonly_select")
                    {
                        if (isset($value) && $value !== 0 && $value !== "")
                            $field_prefill = $select_list[$value];
                        else
                            $field_prefill = "";
                        $form .= form_input($field_name, $field_prefill, $readonly);
                        break;
                    }

                    if ($value == $mult)
                        array_unshift($select_list, $value);

					if (isset($field['multiple']) && $field['multiple'] === TRUE)
					{
						$value = explode(',', $value);

						$form .= form_multiselect($field_name . "[]", $select_list, $value, "size=10");
					}
					else
					{
    					$form .= form_dropdown($field_name, $select_list, $value);
					} //end if

    				break;
    			case "datetime":
				case "date":
                case "time":
                    if ($value == $mult)
                    {
                        $form .= form_input($field_name, $value, "disabled");
                        $form .= form_hidden($field_name, 1, "disabled");
                        if ($field_type == "datetime")
                            $form .= format_datetime_input($field_name, "", FALSE, "hidden");
                        elseif ($field_type == "date")
                            $form .= print_date_dropdown($field_name, "", FALSE, "hidden");
                        else
                            $form .= print_time_dropdown($field_name, "", FALSE, "hidden");

                        $form .= nbs() . form_button("turn-date-on", "click to change date", "class='turn-date-on' id='$field_name'");

                        $this->jquery[] = tab() . "$('button.turn-date-on').click(function () {
		var theName = $(this).attr('id'),
		    theFieldset = $(this).closest('fieldset');

    	$(theFieldset).find('input[type=text]').hide();
		$(theFieldset).find('input[type=hidden]').removeAttr('disabled');
		$(theFieldset).find('select').removeClass('hidden').show();
		$(this).attr('disabled', 'disabled').hide();
    });" . nl(2);
                    }
                    else
                    {
                        $form .= form_hidden($field_name, 1);

                        if ($field_type == "datetime")
                            $form .= format_datetime_input($field_name, $value);
                        elseif ($field_type == "date")
                            $form .= print_date_dropdown($field_name, $value);
                        else
                            $form .= print_time_dropdown($field_name, $value);
                    }

    				break;
                case "readonly_datetime":
                case "readonly_date":
                case "readonly_time":
                    if ($value == $mult)
                    {
                        $form .= form_input($field_name, $value, "readonly");
                    }
                    else
                    {
                        if ($field_type == "readonly_datetime")
                            $form .= format_datetime_input($field_name, $value, TRUE);
                        elseif ($field_type == "readonly_date")
                            $form .= print_date_dropdown($field_name, $value, TRUE);
                        else
                            $form .= print_time_dropdown($field_name, $value, TRUE);
                    }

                    break;
    			case "enum":
    				$form .= $this->_form_enum($field_name, $value);
					break;
				case "image":
                    $multiform = TRUE;
					if (empty($value))
					{
						$form .= form_upload($field_name);
					}
					else
					{
						$form .= form_input($field_name, $value, "disabled");
						$form .= form_upload($field_name, '', "disabled style='display: none;'");
						$form .= nbs() . form_button('turn_upload_on', 'click to change file', "class='turn-upload-on' id='$field_name'");

                        if ($value != $mult)
                        {
                            if (strpos($field_name, "thumbnail") === FALSE)
                                $is_thumbnail = "";
                            else
                                $is_thumbnail = "/thumbnails";

                            $form .= br() . img(array("src"=>"images/" . $this->name . $is_thumbnail . "/" . $value, "class"=>"right", "width"=>"100")) . br();
                        }

                        $this->jquery[] = "$('button.turn-upload-on').click(function () {
		var theName = $(this).attr('id');
    	$('input[name=' + theName + '][type=text]').hide();
		$('input[name=' + theName + '][type=file]').removeAttr('disabled');
    	$('input[name=' + theName + '][type=file]').show();
		$(this).attr('disabled', 'disabled');
		$(this).hide();
    });";
					}
					break;
    		} //end switch
    		
    		$form .= "<br style='clear: both;' />" . $description;
    		$form .= form_fieldset_close();
			//$form .= br(2);
    	} //end foreach
    	
		$form .= "<div class='buttons'>";
    	$form .= form_submit('submit', $button, "class='submit'");
		$form .= form_reset('reset', $reset_button, "class='submit'");
		$form .= "</div>\n";
    	$form .= form_fieldset_close();
		$form .= form_close();

        if ($multiform)
        {
			$form_open = form_open_multipart("admin/{$action}_record");
		}
		else
		{
			$form_open = form_open("admin/{$action}_record");
		} //end if

		$form = $form_open . $hidden_fields . form_fieldset($legend) . $form;
		
		return $form;
    } // end function edit_table

/*======================================================= load_table_list */
    public function load_table_list()
	{
        $tables = "";
		$file = read_file('data/main/tables.txt');
		eval($file);
		return $tables;
	} // end function load_table_list

/*======================================================= _get_itemname */
	private function _get_itemname()
	{
		$tables = $this->load_table_list();
		
		foreach($tables as $table)
		{
			if ($table['name'] !== $this->name)
				continue;
				
			return $table['itemname'];
		}
	} //end function _get_itemname

/*======================================================= load_field_list */
    public function load_field_list()
    {
    	$tables = $this->load_table_list();
    	
    	foreach($tables as $table)
    	{
    		if ($table['name'] === $this->name)
    		{
    			return $table['fields'];
    		}
    		else
    			continue;
    	}
    } // end function load_field_list
    
/*======================================================= get_field_type */
    public function get_field_type($field_name)
    {
    	$fields = $this->load_field_list();
    	
    	foreach ($fields as $field)
    	{
    		if ($field['name'] === $field_name)
    		{
    			if (isset($field['type']))
	    			return $field['type'];
	    		else
	    			return 'text';
    		}
    		else
    		{
    			continue;
    		} //end if
    	} //end foreach
    } //end function get_field_type

/*=================================================== is_multiple_select */
	public function is_multiple_select($field_name)
	{
    	$fields = $this->load_field_list();

		foreach ($fields as $field)
    	{
    		if ($field['name'] === $field_name)
    		{
    			if (isset($field['multiple']) && $field['multiple'] === TRUE)
	    			return TRUE;
	    		else
	    			return FALSE;
    		}
    		else
    		{
    			continue;
    		} //end if
    	} //end foreach
	} //end function is_multiple_select
    
/*======================================================= _form_enum */
    private function _form_enum($field_name, $value, $class = "")
    {
    	$sql = "SHOW COLUMNS FROM " . $this->name . " LIKE ?";
    	$query = $this->db->query($sql, $field_name);
    	$row = $query->row();
    	$type = $row->Type;
    	
    	//print_r($type);
    	//enum('text','textarea','select','checkbox','radio')
    	
    	$enum_values = explode(",", substr($type, 5, -1));
    	
    	$select = "<select name='$field_name' " . $class . ">\n";
    	foreach($enum_values as $enum_value)
    	{
    		$enum_value = substr($enum_value, 1, -1);
    		if ($enum_value === $value)
    			$selected = 'selected';
			else
				$selected = '';
				
			$select .= "<option $selected>$enum_value</option>\n";
    	} //end foreach
    	$select .= "</select>\n";
    	
    	return $select;
    } //end function form_enum

/*======================================================= make_dropdown */
	public function make_dropdown($dropdown_name, $select_field, $table_name = 0, $value = "", $more_attributes = "", $class = "")
	{
		if ($table_name === 0)
			$table_name = $this->name;

		if (is_array($select_field))
		{
			$select_fields = "CONCAT_WS(' ',";
			for ($i = 0; $i < ($num_selects = count($select_field)); $i++)
			{
				$select_fields .= $select_field[$i];
				if ($i < ($num_selects - 1))
					$select_fields .= ",";
			} //end foreach
			$select_fields .= ") AS select_display";
		}
		else
		{
			$select_fields = $select_field . " AS select_display";
		} //end if

        $this->db->order_by("select_display");
		$this->db->select("id, $select_fields", FALSE);
		$query = $this->db->get($table_name);
		$select_array = $query->result_array();
		$select_list = array(0 => 'choose one');
		foreach($select_array as $select_item)
		{
			$select_list[$select_item['id']] = $select_item['select_display'];
		} //end foreach
		$dropdown = form_dropdown($dropdown_name, $select_list, $value, $class . $more_attributes);

		return $dropdown;
	} //end function make_dropdown
} //end of class