<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

/* ========================================================= construct */
	public function __construct()
    {
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('form');
		$this->load->helper('file');
		$this->load->helper('date');
		$this->load->model('table');

		$this->config->load('admin', TRUE);
    } //end function construct

/* ======================================================== _remap */
    public function _remap($method, $params = array())
    {
        if (method_exists($this, $method))
        {
            return call_user_func_array(array($this, $method), $params);
        }
        else
        {
            array_unshift($params, $method);
            //then the "method" is really a db table, send it to index
            return call_user_func_array(array($this, "show"), $params);
        }
    } //end function _remap

/* ============================================================ index */
	public function index()
	{		
		$this->_display_admin_page();
	} //end function login
	
/* ========================================================== login */
	public function login()
	{
		$query = $this->db->get_where('users', array(
    		'username' => $this->input->post('username'),
    		'password' => md5($this->input->post('password'))
    		));
    	if ($query->num_rows() > 0)
    	{
    		$row = $query->row();

    		$login_data = array(
                   'admin_user_id'  => $row->id,
                   'admin_logged_in' => TRUE,
                   'admin_roles' => $row->roles);

			$this->session->set_userdata($login_data);
		}

		$this->index();
	} //end function login

/* ========================================================= blog */
// to edit the blog...without access to any of the other applications.
	public function blog($action = '', $record_num = 0, $nav = '', $message = '')
	{
		//if the first arg isn't an action, then it is the page data
		if ($action !== '' && !in_array($action, array('edit','delete')))
		{
			$page_data = $action;

			$num_data_items = count($page_data);
			$page_data_keys = array_keys($page_data);

			$key = '';
			for ($i = 0; $i < $num_data_items; $i++)
			{
				$key = $page_data_keys[$i];
				$$key = $page_data[$key];
			} //end foreach
		} //end if

		//check to make sure we've gotten all of our vars & are logged in
		if ($nav === '')
		{
			//then we were brought here by just entering "blog", and we need
			//to create the top_menu_nav

			$role_menu_return = $this->_get_role_menu();
			if (is_array($role_menu_return))
				$nav = $role_menu_return['nav'];
			else
				$this->_display_admin_page(); //send them where they belong
		} //end if

		$this->load->model('role_model');
		$role = new Role_model();
		$role->make_role(1);

		//get the list of blog entries to display on right of page
		$this->load->model('blog_model');
		$the_blog = new Blog_model();
		$blog_entry_list = $the_blog->get_blog('admin');

		$blog_table = new Table('blog');

		if ($record_num === 0)
		{
			$record_num = 'insert';
		} //end if

		if ($action === 'delete')
		{
			$this->delete_record('blog', $record_num);
			return;
		} //end if

		$blog_form = $blog_table->edit_table($record_num);

		$script = $this->_add_script('delete', 'blog');

		$data = array(
			'inner_view' => $role->get_view(),
			'nav' => '',
			'table' => '',
			'message' => $message,
			'content' => '',
			'script' => $script,
			'css' => 'blog.css',
			'blog_data' => array(
							'blog_form' => $blog_form,
							'blog_entry_list' => $blog_entry_list),
			'top_nav_menu' => $nav,
            'website_name' => $this->config->item('site_name', 'admin'));

		$this->load->view('admin', $data);
	} //end function blog
	
/* ============================================================== edit */
	public function edit($table)
	{
        $this_table = new Table($table);

        $uri = uri_string();
        $record_ids = explode("/", $uri);
        $record_ids = array_slice($record_ids, 3);
        $num_records = count($record_ids);

        if ($num_records >= 1)
            $record_id_string = implode("/", $record_ids);
        else
            $record_id_string = "insert";

        $content = $this_table->edit_table($record_id_string);

        if ($num_records > 1)
        {
            $message = "<span class='error'>Caution: You are editing multiple records. Any changes you make on this form will be applied to all selected records. Fields highlighted in red will not be changed.</span>";

            array_push($this_table->jquery, tab() . "
    var form_fields = $('input,textarea,select');
        $(form_fields).val(function(index,value) {
            if (value == '< multiple values >')
            {
                //alert('there is a multiple value...going to change the css.');
                $(this).css('background-color', '#f69679');
                if (!$(this).is(':visible'))
                {
                    $(this).siblings('iframe').contents().find('body').css('background-color', '#f69679');
                }
            }
            return value;
    });
    $(form_fields).change(function() {
        $(this).css('background-color', 'white');
            if (!$(this).is(':visible'))
            {
                alert('there is an invisible something.');
                $(this).siblings('iframe').contents().find('body').css('background-color', 'white');
            }
    });" . nl(2));
        }
        else
        {
            $message = "";
        }

        $jquery = empty($this_table->jquery) ? "" : implode("\n", $this_table->jquery);
        $script = empty($this_table->scripts) ? "" : implode("\n", $this_table->scripts);
        $css = empty($this_table->css) ? "" : implode("\n", $this_table->css);

        $this->_display_admin_page($content, $message, $script, $jquery, $css);
	} //end function edit
	
/* ===================================================== insert_record */
	public function insert_record()
	{
        $table = $this->input->post('table');
		$this_table = new Table($table);

        $insert_data = array();
        $product_category_array = array();

        $upload_message = $this->_prepare_post_data($_FILES, $this->input->post(), $this_table, $insert_data, $product_category_array);

        $this->db->insert($table, $insert_data);

        if (!empty($product_category_array))
        {
            $record_id = $this->db->insert_id();
            foreach ($product_category_array as $category_id)
            {
                $this->db->insert("product_category", array('product_id'=>$record_id,'category_id'=>$category_id));
            }
        }

		$message = $this->clear_server_cache('insert_record');
		$upload_message = empty($upload_message) ? "" : br() . $upload_message;
		
		$this->show($table, "", "<h2>Record Inserted!</h2>\n" . $message . $upload_message);
	} //end function insert_record
	
/* ===================================================== update_record */
	public function update_record()
	{
        $table = $this->input->post('table');
        $this_table = new Table($table);
        $record_id = $this->input->post('record_id');

        $update_data = array();
        $product_category_array = array();

        $upload_message = $this->_prepare_post_data($_FILES, $this->input->post(), $this_table, $update_data, $product_category_array, "update");

        if (strpos($record_id, "/"))
        {
            $record_id_array = explode("/", $record_id);
            $this->db->where_in("id", $record_id_array);
        }
        else
        {
            $this->db->where('id', $record_id);
            $record_id_array[0] = $record_id;
        }
		$this->db->update($table, $update_data);

        if (!empty($product_category_array))
        {
            foreach($record_id_array as $record_id)
            {
                foreach ($product_category_array as $category_id)
                {
                    $this->db->where(array('product_id'=>$record_id,'category_id'=>$category_id));
                    $this->db->from('product_category');
                    if ($this->db->count_all_results() > 0)
                        continue;
                    else
                        $this->db->insert('product_category',array('product_id'=>$record_id,'category_id'=>$category_id));
                }

                $query = $this->db->get_where('product_category', array('product_id'=>$record_id));

                foreach ($query->result() as $row)
                {
                    if (!in_array($row->category_id, $product_category_array))
                        $this->db->delete('product_category',array('product_id'=>$record_id,'category_id'=>$row->category_id));
                }
            }
        }
		
		$message = $this->clear_server_cache('update_record');
		$upload_message = empty($upload_message) ? '' : br() . $upload_message;
		
		$this->show($table, "", "Record Updated!" . br() . $message . br() . $upload_message);
	} //end function

/* ===================================================== _prepare_post_data */
    private function _prepare_post_data(&$file_array, $post_array, &$this_table, &$insert_data, &$product_category_array, $insert_update = "insert")
    {
        $upload_message = "";
        $table = $this_table->name;
        $fields = $this_table->load_field_list();

        if (!empty($file_array))
        {
            $file_keys = array_keys($file_array);

            $files = array();
            $j = 0;
            foreach($file_array as $file)
            {
                $files[$file_keys[$j]] = $file['name'];

                $j++;
            } //end foreach

            $post_array = array_merge($post_array, $files);
        } //end if

        foreach($fields as $field)
        {
            $type = $this_table->get_field_type($field["name"]);
            $key = $field["name"];

            if (
                (isset($post_array[$key]) &&
                    !is_array($post_array[$key]) &&
                    (
                        (strpos($post_array[$key], "< multiple values >") !== FALSE) or
                        (strpos($post_array[$key], "&lt; multiple values &gt;") !== FALSE)
                    )
                )
                or
                (isset($post_array[$key]["name"]) &&
                    ($post_array[$key]["name"] === "< multiple values >")
                )
                or
                (!isset($post_array[$key]))
            )
            {
                continue;
            }

            if (strpos($type, "readonly") !== FALSE)
                continue;

            switch ($type) {
                case "datetime":
                    $key_month = $key . "_month";
                    $key_day = $key . "_day";
                    $key_year = $key. "_year";

                    $key_hours = $key . "_hours";
                    $key_mins = $key . "_mins";
                    $key_am_pm = $key . "_am_pm";

                    if ($post_array[$key_am_pm] == 'pm' && $post_array[$key_hours] != 12)
                        $post_array[$key_hours] += 12;

                    if ($post_array[$key_am_pm] == 'am' && $post_array[$key_hours] == 12)
                    {
                        if ($post_array[$key_mins] == '00')
                            $post_array[$key_hours] = '24';
                        else
                            $post_array[$key_hours] = '00';
                    } //end if

                    $value = $value = $post_array[$key_year] . "-" . $post_array[$key_month] . "-" . $post_array[$key_day] . $post_array[$key_hours] . ":" . $post_array[$key_mins];
                    break;
                case "date":
                    $key_month = $key . "_month";
                    $key_day = $key . "_day";
                    $key_year = $key. "_year";

                    $value = $post_array[$key_year] . "-" . $post_array[$key_month] . "-" . $post_array[$key_day];
                    break;
                case "time":
                    $key_hours = $key . "_hours";
                    $key_mins = $key . "_mins";
                    $key_am_pm = $key . "_am_pm";

                    if ($post_array[$key_am_pm] == 'pm' && $post_array[$key_hours] != 12)
                        $post_array[$key_hours] += 12;

                    if ($post_array[$key_am_pm] == 'am' && $post_array[$key_hours] == 12)
                    {
                        if ($post_array[$key_mins] == '00')
                            $post_array[$key_hours] = '24';
                        else
                            $post_array[$key_hours] = '00';
                    } //end if

                    $value = $post_array[$key_hours] . ":" . $post_array[$key_mins];
                    break;
                case "image":
                    $value = str_replace(" ", "_", $file_array[$key]["name"]);
                    if (strpos($key, "thumbnail") !== FALSE)
                        $thumb_path = "/thumbnails";
                    else
                        $thumb_path = "";
                    $upload_path = "images/" . $table . $thumb_path;
                    $message_term = "Image";
                    $config = array (
                        'upload_path' => $upload_path,
                        'allowed_types' => 'gif|jpg|png',
                        'max_size' => '1024',
                        'max_width' => '1000',
                        'max_height' => '800');
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload($key))
                        $upload_message = "<h2>$message_term Uploaded!</h2>";
                    else
                        $upload_message = "$message_term Upload Error: " . $this->upload->display_errors("<i>", "</i>");
                    break;
                case "file":
                    $value = str_replace(" ", "_", $file_array[$key]["name"]);
                    $upload_path = "files";
                    $message_term = "File";
                    $config = array (
                        'upload_path' => $upload_path,
                        'allowed_types' => 'gif|jpg|png',
                        'max_size' => '1024',
                        'max_width' => '1000',
                        'max_height' => '800');
                    $this->load->library('upload', $config);
                    if ($this->upload->do_upload($key))
                        $upload_message = "$message_term Uploaded!";
                    else
                        $upload_message = "$message_term Upload Error: " . $this->upload->display_errors("<i>", "</i>");
                    break;
                default:
                    $value = $post_array[$key];
            }

            switch ($key) {
                case "title":
                case "name":
                    $insert_data['uri_title'] = url_title($value, 'dash', TRUE);
                    break;
                case "password":
                    $value = md5($value);
                    break;
                case "category":
                    if ($table == "products")
                    {
                        $product_category_array = $value;
                        $value = implode(',', $value);
                    }
                    break;
            }

            $value = $this->_fix_quotes($value);
            $insert_data[$key] = $value;
        } //end foreach

        if ($this_table->name === "products")
        {
            if ($insert_update == "insert")
            {
                $insert_data["added_by_username"] = $this->session->userdata("admin_user_id");
            }
            else
            {
                $insert_data["last_updated_by_username"] = $this->session->userdata("admin_user_id");
                $insert_data["touch_timestamp"] = date("Y-m-d H:i:s");
            }
        }

        return $upload_message;
    }

/* ===================================================== fix quotes */
    private function _fix_quotes($string) {
        $quotes = array(
            "\xC2\xAB"     => '"', // « (U+00AB) in UTF-8
            "\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
            "\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
            "\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
            "\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
            "\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
            "\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
            "\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
            "\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
            "\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
            "\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
            "\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
            "\xE2\x80\xA2" => "&bull;"
        );
        $string = strtr($string, $quotes);

        return $string;
    }
	
/* ==================================================== delete_record */
	public function delete_record($table)
	{
        $uri = uri_string();
        $record_ids = explode("/", $uri);
        $record_ids = array_slice($record_ids, 3);
        $record_records = count($record_ids) > 1 ? "Records" : "Record";

        foreach ($record_ids as $record)
        {
            $this->db->delete($table, array('id' => $record));
        }

		$message = $this->clear_server_cache('delete_record');

		$this->show($table, '', '', "<h2>" . $record_records . " Deleted!</h2>\n" . $message);
	} //end function

/* ================================================clear_server_cache */
	public function clear_server_cache($calling_script = '')
	{
		$content = "";
		
		$directory = dir("application/cache/");
		
		while ($filename = $directory->read())
		{
			if (($filename === '.htaccess') || ($filename === 'index.html') || ($filename === '.') || ($filename === '..'))
				continue;
			
			$success = unlink("application/cache/" . $filename);
			
			if (!$success)
				$content .= "System could not delete {$filename}.<br />\n";
		} //end foreach
		
		if ($content === "")
			$content = "<h2>Cache Cleared Successfully.</h2>";
			
		if ($calling_script === '')
		{			
			$content = "<div class='grid_5 push_2 alpha omega center'><br />" . $content . "</div>\n";
				
			$inner_view = $this->_which_view();
			
			if ($inner_view === 'admin_logged_in')
				$nav = $this->_build_nav();
			else
				$nav = '';
				
			$data = array(
				'inner_view' => $inner_view,
				'script' => '',
				'table' => '',
				'message' => '',
				'nav' => $nav,
				'content' => $content,
                'website_name' => $this->config->item('site_name', 'admin'));
			
			$this->load->view('admin', $data);
		}
		else
		{
			return($content);
		} //end if
	} //end function clear_server_cache

/* ============================================================ show */
	public function show($table, $order_by = "", $asc_desc = "asc", $message = "")
	{
		$this_table = new Table($table);
		$content = $this_table->list_table($order_by, $asc_desc, $message);

        $jquery = tab() . "$('table#table-list').columnFilters({alternateRowClassNames:['odd','even'], excludeColumns:[0]});" . nl(2);

        $jquery .= tab() . "$('#check-all').click(function(){
        $('table tr input').not('#check-all').prop('checked',false);

        if ($(this).is(':checked'))
        {
            $('table tr:visible input').prop('checked',true);
        }
    });" . nl(2);

        $jquery .= tab() . "jQuery.fn.extend({
    getCheckedIds: function () {
        var ids_to_delete = new Array(),
            the_id = '';

        $('input:checked').each(function(index) {
            the_id = $(this).parents('tr').find('td:nth-child(2) a').text();
            if (the_id == '')
                return true;
            ids_to_delete.push(the_id);
        });
        return ids_to_delete;
    }
});" . nl(2);

        $jquery .= tab() . "$('#mass_delete').click(function(){
        var ids_to_delete = $(this).getCheckedIds(),
            delete_ids_string = '';

        delete_ids_string = ids_to_delete.join('/');

        delete_confirm(delete_ids_string);
    });" . nl(2);

        $jquery .= tab() . "$('#mass_edit').click(function(){
        var ids_to_edit = $(this).getCheckedIds(),
            edit_ids_string = '';

        edit_ids_string = ids_to_edit.join('/');

        mass_edit_confirm(edit_ids_string);
    });" . nl();

        $script = "";
        $this->_add_script("jquery1.10.1", NULL, $script);
        $this->_add_script("jquery.columnfilters.js", NULL, $script);
        $this->_add_script("delete", $table, $script);
        $this->_add_script("mass_edit", $table, $script);

        if (!empty($this_table->css))
            $css = $this_table->css;
        else
            $css = "";

		$this->_display_admin_page($content, $message, $script, $jquery, $css);
	} //end function show
	
/* ============================================================== logout */
	public function logout()
	{
		$this->session->sess_destroy();
		$this->load->view('admin', array(
			'inner_view' => 'admin_login',
			'script' => '',
			'table' => '',
			'message' => '',
			'nav' => '',
			'content' => '',
            'website_name' => $this->config->item('site_name', 'admin')));
	} //end function logout
	
/* ========================================================== _which_view */
	private function _which_view()
	{
		if ($this->session->userdata('admin_logged_in') === TRUE)
			return 'admin_logged_in';
		else
			return 'admin_login';
	} //end function _which_view

/* ================================================= _display_admin_page */
	private function _display_admin_page($content = "", $message = "", $script = "", $jquery = "", $css = "")
	{
        $website_name = $this->config->item("website_name", "admin");
		if (!$this->_is_logged_in())
		{
			//then they aren't logged in...send them to login view
			$this->load->view('admin', array(
										'nav' => '',
										'table' => '',
										'message' => '',
										'content' => '',
										'script' => '',
										'inner_view' => 'admin_login',
                                        'website_name' => $website_name));
			return;
		} //end if

		$role_menu_return = $this->_get_role_menu();

		if (is_array($role_menu_return))
		{
			$nav = $role_menu_return['nav'];
			$controller = $role_menu_return['controller'];
			$view = $role_menu_return['view'];
	
			if ($controller !== '')
			{
				$page_data = array('nav' => $nav, 'message' => $message);
				$this->$controller($page_data);
				return;
			} //end if
		}
		elseif ($role_menu_return === 0)
		{
			$nav = $this->_build_nav();
			$view = $this->_which_view();

			if ($content === "")
				$content = "<div class='grid_5 push_2 alpha omega center'>
<h2>Welcome to " . $website_name . " Website Administration</h2>
<p>Click on links at left to perform tasks.</p>
</div>" . nl(2);
		} //end if

        if (!empty($message))
        {
            $message = strip_tags($message);
            $content .= nl(2) . "<div class='alert'><div class='alert-close'>X</div>$message</div>" . nl(2);
            $jquery .= tab() . "$('.alert-close').click(function() {
        $(this).parent('.alert').slideUp();
            });" . nl(2);
        }

        if (!empty($jquery))
            $this->use_jquery($script);

		$data = array(
			'inner_view' => $view,
			'script' => $script,
            'jquery' => $jquery,
			'table' => '',
			'message' => $message,
			'nav' => $nav,
			'content' => $content,
            'website_name' => $website_name,
            'css' => $css);

		$this->load->view('admin', $data);
	} //end function _display_admin_page

/* ===================================================== _get_role_menu */
	private function _get_role_menu()
	{
		$this->load->model('role_model');

		$role_list = $this->session->userdata('roles');

		if ((int)$role_list === 0)
			return 0;
		
		$roles = explode(',', $role_list);				

		$first_controller = '';
		$first_view = '';
		$nav = '';
		$role_count = 1;
		foreach($roles as $role)
		{
			$this_role = new Role_model();
			if ($this_role->make_role($role))
			{
				$controller_name = $this_role->get_controller();
				$link_text = $this_role->get_link_text();

				if ($role_count === 1)
				{
					$first_controller = $controller_name;
					$first_view = $this_role->get_view();
				} //end if

				$nav .= anchor("admin/" . $controller_name, $link_text);
			} //end if

			$role_count++;
		} //end foreach

		if (substr_count($nav, "</a>") > 1)
			$nav = anchor('admin/logout', 'Logout') . $nav;
		else
			$nav = anchor('admin/logout', 'Logout');

		return(array('nav' => $nav, 'view' => $first_view, 'controller' => $first_controller));
	} //end function _get_role_menu

/* ======================================================== is_logged_in */
	private function _is_logged_in()
	{
		if ($this->session->userdata('admin_logged_in') === TRUE)
			return TRUE;
		else
			return FALSE;
	} //end function _is_logged_in
	
/* ============================================================ build_nav */
	private function _build_nav()
	{
		$this_table = new Table();
		$tables = $this_table->load_table_list();

		$nav = "<ul><li><a href='" . site_url() . "admin/logout'>Logout</a></li></ul><ul>";
		foreach ($tables as $table) 
		{
			$plural = isset($table['itemname_plural']) ? $table['itemname_plural'] : ($table['itemname']) . 's';
			$table_name = $table['name'];
			$nav .= "<li>$plural</li>";
			$nav .= "<ul><li><a href='" . site_url() . "admin/show/$table_name'>" . 
				"List $plural</a></li><li><a " . "href='" . site_url() . "admin/edit/$table_name'>Create " . 
				$table['itemname'] . "</a></li></ul>";
		} //end foreach

		//$nav .= "<li>" . anchor('admin/fix_photos', 'Fix Photos') . "</li>";

		$nav .= "</ul>";
		return $nav;
	} //end function _build_nav
	
/* ============================================================ add_script */
	private function _add_script($which_script, $table = '', &$script = '')
	{
		switch($which_script)
		{
			case 'delete':
				$script .= "<script type='text/javascript'>
<!--
function delete_confirm(delete_id)
{
	var delete_it = confirm('Are you sure you want to delete the following records: ' + delete_id);
	if (delete_it == true) {
		window.location = '" . site_url() . "admin/delete_record/$table/' + delete_id;
	} else {
		return false;
	} //end if
} //end function
//-->
</SCRIPT>\n";
				break;
            case 'mass_edit':
                $script .= "<script type='text/javascript'>
<!--
function mass_edit_confirm(ids)
{
	var yes = confirm('You are about to edit multiple records: ' + ids);
	if (yes) {
		window.location = '" . site_url() . "admin/edit/$table/' + ids;
	} else {
		return false;
	} //end if
} //end function
//-->
</SCRIPT>\n";
                break;
			case 'jquery1.10.1':
				$script .= script("jquery-1.10.1.min.js");
				break;
            case 'jquery1.3.2':
                $script .= script("jquery-1.3.2.min.js");
                break;
            default:
                $script .= script($which_script);
		} //end switch
		
		return;
	} //end function _add_script

/* ============================================================ add_jquery */
    private function _add_jquery($jquery_text, &$jquery = '')
    {
        $jquery .= $jquery_text;
        return;
    }

/* ===================================================== fix_photos */
	public function fix_photos()
	{
		$this->db->where('image !=', '');
		$query = $this->db->get('products');

		echo getcwd() . br(2);

		echo $query->num_rows() . " rows returned." . br(2);

		$counter = 0;
		foreach($query->result() as $product)
		{
			if (!file_exists("./images/products/" . $product->image))
			{
				$new_product = array('image' => '', 'thumbnail' => '');
				$this->db->where('id', $product->id);
				$this->db->update('products', $new_product);

				echo "./images/products/" . $product->image . " isn't there." . br();

				$counter++;
			}
			else
			{
				echo "<span color='red'>FILE FOUND: ./images/products/" . $product->image . br();
			} //end if
		} //end foreach

		echo br(2) . "$counter images not there." . br();
	} //end function _fix_photos

/* ======================================================== use_jquery */
    public function use_jquery(&$scripts)
    {
        if (!empty($scripts) && (strpos($scripts, "jquery-") !== FALSE))
        {
            return;
        }

        $scripts .= script("jquery-1.10.1.min.js");

        return;
    } //end function _use_jquery

} //end class

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */