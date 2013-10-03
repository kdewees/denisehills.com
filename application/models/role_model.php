<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Role_model extends CI_Model {

	var $id = 0;
	var $label = '';
	var $controller = '';
	var $view = '';
	var $link_text = '';

/* ===================================================== construct */
    function __construct($id = 0)
    {
        parent::__construct();
    } //end function construct

/* ===================================================== make_role */
	function make_role($id)
	{
		$this->id = $id;

		$this->load->database();
		$result = $this->db->get_where('roles', array('id' => $this->id));

		if ($result->num_rows() > 0)
		{
			$row = $result->row();
	
			$this->label = $row->label;
			$this->controller = $row->controller;
			$this->view = $row->view;
			$this->link_text = $row->link_text;

			return TRUE;
		}
		else
		{
			return FALSE;
		} //end if
	} //end function make_role

/* ===================================================== get_controller */
	function get_controller()
	{
		return $this->controller;
	} //end function get_controller

/* ===================================================== get_view */
	function get_view()
	{
		return $this->view;
	} //end function get_view

/* ===================================================== get_link_text */
	function get_link_text()
	{
		return $this->link_text;
	} //end function get_view
} //end class