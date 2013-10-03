<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Page extends CI_Model {

	var $id = 1;
    var $title   = "";
    var $content = "";
	var $view = "default";
	var $main_menu = "";
	var $message = "";
	var $main_controller = "pages";
	var $scripts = "";
	var $css = "";
	var $sub_menu = "";
    var $other = array();
    var $section = 0;
    var $is_store = FALSE;
    var $store_menu_link_id = 0;
    var $footer = array();
    var $logo = "";
    var $jquery = "";

/* ======================================================== construct */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->helper('date');
    } //end function

/* ======================================================== make_page */
    function make_page($var = 1, $section = 0)
    {
    	if (is_numeric($var))
    	{
    		$id = $var;
    	}
    	else
    	{
    		$id = $this->_get_id_from_title($var);
    	} //end if

        if ($id === 1)
            $this->view = "home";

    	$this->id = $id;

        $this->config->load("deweesdesigns", TRUE);
        if ($this->config->item("store", "deweesdesigns") == TRUE)
        {
            $this->is_store = TRUE;
            $this->store_menu_link_id = $this->config->item("store_menu_link_id", "deweesdesigns");
            $this->load->model("store");
        }

    	$query = $this->db->get_where('pages', array('id' => $this->id));
    	if ($query->num_rows() > 0)
    	{
			$row = $query->row();
			$this->title = $row->title;
			$page_title = "<h1 class='title'>" . $row->title . "</h1>";
			$this->content = $page_title . stripslashes($row->content);
		}
		else
		{
			show_404();
		} //end if

        $this->main_menu = $this->create_navigation();

		if (isset($row->form_id) && $row->form_id > 0) //then build the appended form
		{
			$this->build_form($row->form_id, TRUE);
		} //end if

        $this->footer["left"] = $this->get_pagepart("footer_left");
        $this->footer["middle"] = $this->build_form(1, FALSE);
        $this->footer["right"] = $this->get_pagepart("footer_right");
        $this->logo = $this->get_pagepart("logo");

        //re-write image addresses
		$this->content = $this->_rewrite_image_addresses($this->content);

		return;
    } //end function make_page

/* ============================================= _rewrite_image_addresses */
	private function _rewrite_image_addresses($content)
	{
		$content = str_replace(array("src='/images", 'src="/images', "src='images", 'src="images'), array("src='" . site_url() . "images", "src=\"" . site_url() . "images"), $content);                                  //replace image src strings to include the site url

		return $content;
	} //end function

/* ======================================================== use_jquery */
	public function use_jquery()
	{
        if (!empty($this->scripts) && (strpos($this->scripts, "jquery-") !== FALSE))
            return;

        $this->scripts .= script("jquery-1.10.1.min.js") . nl();

        return;
	} //end function _use_jquery

/* ======================================================== build_form */
	public function build_form($form_id, $page_form = TRUE)
	{
		$this->load->helper('form');

		//form info
		$query = $this->db->get_where('forms', array('forms.id' => $form_id));
		$form_data = $query->row();

		$hidden_fields = form_hidden("email", $form_data->email);
		$hidden_fields .= form_hidden("page_id", $this->id);
        $hidden_fields .= form_hidden("form_id", $form_id);

		$form = form_open("pages/email_form");
    	$form .= $hidden_fields;
    	$form .= form_fieldset($form_data->name);

    	$query->free_result();

    	//field_info
    	$query = $this->db->get_where('form_fields', array('form_fields.form_id' => $form_id));
    	$fields = $query->result();

    	foreach ($fields as $field)
    	{
    		$field_name = $field->id;
    		$field_type = $field->type;

			$form .= form_label($field->label, $field_name);

			switch($field_type)
    		{
    			case 'text':
		    		$form .= form_input($field_name);
    				break;
    			case 'textarea':
    				$data = array(
    					'name' => $field_name,
    					'rows' => '10',
    					'cols' => '70');
    				$form .= form_textarea($data);
    				break;
    			case 'select':
    				if (isset($field->options))
    				{
						$select_array = explode(',', $field->options);
						foreach($select_array as $select_item)
						{
							$select_item = trim($select_item);
							$select_list[$select_item] = $select_item;
						} //end foreach
						$form .= form_dropdown($field_name, $select_list);
					}
					else
					{
						$form .= form_input($field_name);
					} //end if
    				break;
    			case 'checkbox':
    				$form .= form_checkbox($field_name);
    				break;
    		} //end switch

    		$form .= br(2);
    	} //end foreach

    	$form .= form_submit('submit', $form_data->submit_button_text, "class='submit'");
    	$form .= form_fieldset_close();
		$form .= form_close();

        if ($page_form)
        {
            $this->form = $form;
            return;
        }
        else
        {
            return $form;
        }
	} //end function build_form

/* ======================================================== get_id_from_title */
    private function _get_id_from_title($title)
    {
    	$this->db->where(array('uri_title' => $title));
    	$query = $this->db->get('pages');
        if ($query->num_rows() == 0)
            return 1;
    	$page_data = $query->row();

    	return $page_data->id;
    } //end function get_id_from_title

/* ======================================================== get_pagepart */
    public function get_pagepart($pagepart_id)
    {
        $this->db->select('content');

        if (is_numeric($pagepart_id))                                                       //check to see if pagepart_id is a number
            $query = $this->db->get_where('pageparts', array('id' => $pagepart_id));        //if it's a number, search by pagepart id
        else
            $query = $this->db->get_where('pageparts', array('uri_title' => $pagepart_id)); //if it's not, search by uri_title

    	if ($query->num_rows() > 0)                                                         //if the query returned rows...
    	{
    		$row = $query->row();                                                           //get the row from the query

			$content = $this->_rewrite_image_addresses($row->content);                      //fix the image addresses to display with the site address

    		return $content;                                                                //return content field with fixed image addresses back to calling function
    	}
    	else
    	{
    		return 'Content Currently Unavailable.';                                        //return "content unavailable" string to calling function, because content wasn't found
    	} //end if
    } //end function get_pagepart

/* ======================================================== create_navigation */
    public function create_navigation($menu_id = 1)
    {
        if ($this->is_store == TRUE)
        {
            $this_store = new Store();
        }

        $this->db->join('pages', 'pages.id = links.page_id', 'left');
    	$this->db->order_by('ordinal asc, text asc');
		$this->db->select('pages.uri_title, links.*');
    	$query = $this->db->get_where('links', array('links.menu_id' => $menu_id));

		$num_links = $query->num_rows();
		$link_counter = 1;

    	$navigation = "";
    	foreach ($query->result() as $link)
    	{
            $submenu = "";
			$class = "";
            $parent_class = "";

    		if ((isset($link->url)) && ($link->url !== ''))
    		{
    			$url = $link->url;
    			if (strpos($url, "http://"))
    				$offsite = TRUE;
    			else
    				$offsite = FALSE;
    		}
    		elseif ((isset($link->uri_title)) && ($link->uri_title !== ""))
    		{
    			$url = $this->main_controller . "/" . $link->uri_title;
    			$offsite = FALSE;
    		}
    		elseif ((isset($link->page_id)) && ($link->page_id !== ""))
    		{
    			$url = $this->main_controller . "/" . $link->page_id;
    			$offsite = FALSE;
    		}
    		else
    		{
    			$url = "/";
    			$offsite = FALSE;
    		} //end if

    		if ((isset($link->image)) && ($link->image !== ""))
    			$click = img("images/buttons/" . $link->image);
    		else
    			$click = $link->text;

			$link_attributes = array();

    		if ($offsite === TRUE)
				$link_attributes = array_merge($link_attributes, array("target" => "_blank"));

			$class = $this->_add_link_classes($link, $link_counter, $num_links, $class);

			if ($class != "")
			{
				$class = substr($class, 1);
				$link_attributes = array_merge($link_attributes, array("class" => $class));
			}

            if ($this->is_store == TRUE)
            {

                if ($link->id == $this->store_menu_link_id)
                {
                    $submenu = $this_store->create_category_dropdown();
                    $parent_class = " class=\"parent\"";
                }
            }

			if (count($link_attributes) > 0)
	    		$navigation .= "<li" . $parent_class . ">" . anchor($url, $click, $link_attributes) . $submenu . "</li>";
			else
				$navigation .= "<li" . $parent_class . ">" . anchor($url, $click) . $submenu . "</li>";

			$link_counter++;
    	} //end foreach

        if ($this->id !== 1)
            $navigation = "<li><a href='/'>Home</a></li>" . $navigation;

    	return "<ul>" . $navigation . "</ul>";
    } //end function create_navigation

/* ============================================ set_main_controller */
	public function set_main_controller($controller_name)
	{
		$this->main_controller = $controller_name;

		return;
	} //end function set_main_controller

/* ======================================================== _add_link_classes */
	private function _add_link_classes($link, $link_counter, $num_links, $class = "")
	{
		if ($link->page_id == $this->id)
			$class .= " current-link";

		if ($link_counter == $num_links)
			$class .= " last";

		return $class;
	} //end function _add_link_classes

/* ======================================================== add_css */
	public function add_css($file_name, $dir = "css")
	{
        $path = $dir . "/" . $file_name;

        if (strpos($file_name, ".css") === FALSE)
            $path .= ".css";

		$this->css .= link_tag($path) . nl();
	} //end function add_css

/* ======================================================== add_script */
    public function add_script($file_name, $dir = "js")
    {
        $path = $file_name;

        if (strpos($file_name, ".js") === FALSE)
            $path .= ".js";

        $this->scripts .= script($path, $dir) . nl();
    } //end function add_css

/* ======================================================== add_jquery */
    public function add_jquery($jquery)
    {
        $this->use_jquery();

        $this->jquery .= $jquery . nl();
    } // end function add_jquery

/* ======================================================== add_sub_menu */
	public function add_sub_menu($menu_id)
	{
		$this->sub_menu = $this->create_navigation($menu_id);
	} //end function add_css

} //end class