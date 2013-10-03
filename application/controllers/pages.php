<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pages extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/pages
	 *	- or -  
	 * 		http://example.com/index.php/pages/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

/* ======================================================== _remap */
	public function _remap($method, $params = array())
	{
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $params);
		}
		else
		{
			//then the "method" is really a page id, send it to index
			return call_user_func_array(array($this, 'index'), array($method));
		}
	} //end function _remap

/* ======================================================== index */
	public function index($page_id = 1)
	{
		$this_page = new Page();                                                // create a new page object
		$this_page->make_page($page_id);                                        // initiate the page object

        if ($page_id == 1)                                                      // if this is the home page
        {
            $this_page->other["home1"] = $this_page->get_pagepart("home1");
            $this_page->other["home2"] = $this_page->get_pagepart("home2");
        }
        $this_page->footer["left"] = $this_page->get_pagepart("footer_left");
        $this_page->footer["middle"] = $this_page->get_pagepart("footer_middle");
        $this_page->footer["middle"] .= $this_page->build_form(1, FALSE);
        $this_page->footer["right"] = $this_page->get_pagepart("footer_right");

        $this_page->add_jquery("$('#footer form :input').not('.submit').each(function(index,field) {
        var fieldName = field.getAttribute('name');
        if (!$.isNumeric(fieldName))
        {
            return true;
        }
        var label = $(\"label[for='\" + fieldName + \"']\").text();
        if ($(field).is('input'))
        {
            $(field).attr('value', label);
        }
        else if ($(field).is('textarea'))
        {
            $(field).text(label);
        }

        //alert(\"Fieldname: \" + fieldName + \", label: \" + label)
    });

    $('#footer form :input').focus(function(){
        if ($(this).is('input'))
        {
            $(this).attr('value', '');
        }
        else if ($(this).is('textarea'))
        {
            $(this).text('');
        }
    });");

		$this->load->view($this_page->view, $this_page);
	} //end function index

/* ======================================================== _get_slide_data */
	private function _get_slide_data(&$this_page, $num_slides = "all", $promotion = 0, $show_captions = FALSE)
	{
        //get slides that are within the start-end date parameters
        $this->db->where("date_start <= CURDATE() AND date_end >= CURDATE()");

        //check to see if we're getting slides from a certain promotion
        if ($promotion > 0)
            $this->db->where("promotion", $promotion);

        if ($num_slides !== "all")
            $this->db->limit($num_slides);                                      // limit number of slides

        $query = $this->db->get("slides");                                      // perform query

        $slides = "";                                                           // init $slides
        $captions = "";                                                         // init captions
        $counter = 1;                                                           // init counter
        $img_data = array();                                                    // init img data array

        foreach ($query->result() as $row)                                      //loop through results
        {
            $img_data["src"] = "images/slides/" . $row->image;
            $img_data["alt"] = $row->heading;
            $img_data["data-caption"] = "#image" . $counter;

            $slides .= "<div class='slide'>" . img($img_data) . "</div>" . nl();

            if ($show_captions)
            {
                $captions .= "<span class='orbit-caption' id='image" . $counter . "'>";
                if (!empty($row->heading))
                    $captions .= "<h1>" . $row->heading . "</h1>";

                if (!empty($row->text))
                    $captions .="<p>" . $row->text . "</p>";

                $captions .= "</span>" . nl();
            }

            $counter++;
        }

        $rs = nl(2);
        $rs .= "<!-- Begin Orbit Slideshow -->" . nl();
        $rs .= $slides . nl();
        $rs .= "<!-- Captions for Orbit -->" . nl();
        $rs .= $captions . nl();
        $rs .= "<!-- End Orbit Slideshow -->" . nl(2);

        return $rs;
	} //end function _get_slide_data

/* ====================================================== email_form */
    public function email_form()
    {
        $post_array = $this->input->post();

        $query = $this->db->get_where('forms', array('forms.id' => $post_array['form_id']));
        $form_info = $query->row();
        $form_email = $form_info->email;
        $page_id = $form_info->thank_you_page_id;

        //get info about the form fields
        $query = $this->db->get_where('form_fields', array('form_id' => $post_array['form_id']));
        $form_fields = $query->result();

        $email = '';
        foreach ($form_fields as $field)
        {
            $key = $field->id;

            if (!($value = $post_array[$key]))
                continue;

            if (strpos(strtoupper($field->label), 'EMAIL') or strpos(strtoupper($field->label), 'E-MAIL'))
                $their_email = $value;
            if (strpos(strtoupper($field->label), 'NAME'))
                $their_name = $value;

            $email .= $field->label . ": " . $value . "\n";
        } //end foreach

        $email .= "\n\n\nIf you reply to this e-mail, it will be sent to the message-writer.\n";

        $this->load->library('email');

        $this->email->from($their_email, $their_name);
        $this->email->to($form_email);
        $this->email->subject($form_info->name . " - from $their_name");
        $this->email->message($email);
        $this->email->send();

        $controller = empty($form_info->controller) ? 'pages' : $form_info->controller;

        redirect($controller . "/" . $page_id);
    } //end function form
} //end class

/* End of file pages.php */
/* Location: ./application/controllers/pages.php */