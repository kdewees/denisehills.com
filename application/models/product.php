<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Product extends CI_Model {

	var $id = 0;
	var $name = "";
	var $description = "";
	var $image = "";
	//var $category = "";
    //var $parent_category = "";
	var $brand = "";
	var $price = 0.0;
	var $weight = 0;
	var $catalog_number = 0;
	var $sale_price = 0.0;
	var $thumbnail = "";
	var $sale = FALSE;
    //var $category_uri = "";
    //var $parent_category_uri = "";
    var $dimensions = "";
    var $wood_type = "";
    var $brand_info = array();
    var $product_finishes = "";

/* ======================================================== construct */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    } //end function

/* ======================================================== make_product */
	public function make_product($id)
	{
		if (empty($id))
			return FALSE;

        $this->db->select('products.*, brands.name as brand_name, brands.image as brand_image, brands.website as brand_website, brands.finishes, brands.shipping_time');
        $this->db->join('brands', 'brands.id=products.brand');
        $query = $this->db->get_where('products', array('products.id' => $id));

        if ($query->num_rows() > 0)
		{
			$row = $query->row();

            $this->id = $row->id;
            $this->name = $row->name;
            $this->description = $row->description;
            $this->image = $row->image;
            //$this->category = $row->category_name;
            //$this->parent_category = $row->parent_category_name;
            $this->brand = $row->brand_name;
            $this->price = $row->price;
            $this->weight = $row->weight;
            $this->catalog_number = $row->catalog_number;
            $this->sale_price = $row->sale_price;
            $this->sale = $row->sale;
            //$this->category_uri = $row->category_uri;
            //$this->parent_category_uri = $row->parent_category_uri;
            $this->dimensions = $row->dimensions;
            $this->wood_type = $row->wood_type;
            $this->brand_info = array(
                "brand_website" => $row->brand_website,
                "brand_image" => $row->brand_image,
                "finishes" => $row->finishes,
                "shipping_time" => $row->shipping_time
            );
            $this->product_finishes = $row->product_finishes;

			return $this;
		}
		else
		{
			return FALSE;
		} //end if
	} //end function make_product

/* ======================================================== show_product */
	public function show_product($id, $parent_category, $child_category)
	{
		if (!$this->id)
			$this->make_product($id);

        $this->db->select("id,name");
        $query = $this->db->get_where("categories", array("uri_title"=>$parent_category));
        $row = $query->row();
        $parent_category_name = $row->name;
        $parent_category_id = $row->id;

        $query->free_result();

        $this->db->select("name");
        $query = $this->db->get_where("categories", array("uri_title"=>$child_category, "parent"=>$parent_category_id));
        $row = $query->row();
        $child_category_name = $row->name;

        $query->free_result();

        if (empty($this->image))
            $image = "<div class='no-image'>no<br/>image</div>";
        else
            $image = img("images/products/" . str_replace(" ", "_", $this->image));

        $product = "";

        $product .= "<div class='grid_12'>";
        $product .= "<h1 class='headline'>";
        $product .= "<a href='/products/" . $parent_category . "'>";
        $product .= ucwords($parent_category_name);
        $product .= "</a>";
        $product .= " &gt; ";
        $product .= "<a href='/products/" . $parent_category . "/" . $child_category . "'>";
        $product .= ucwords($child_category_name);
        $product .= "</a>";
        $product .= " &gt; ";
        $product .= "<span class='black'>" . $this->name . "</span>";
        $product .= "</h1>";
        $product .= "</div>";

        $product .= "<div class='clear'></div>";

        $product .= "<div class='product standalone container_12'>";

        $product .= "<div class='grid_5 push_1'>";
        //$product .= "<div class='price'>" . $this->price . "</div>";
        $product .= "<div class='name'><h2>" . $this->name . "</h2></div>";
        $product .= "<div class='brand'>";
        $product .= "<h3>Manufactured by " . $this->brand ."</h3>";

        $end_link = FALSE;
        if ($this->brand_info["brand_website"])
        {
            $product .= "<a href='" . $this->brand_info["brand_website"] . "' target='_blank'>";
            $end_link = TRUE;
        }
        if ($this->brand_info["brand_image"])
        {
            $product .= "<img class='brand-image' src='/images/brands/" . $this->brand_info["brand_image"] . "'>";
        }
        if ($end_link)
            $product .= "</a>";

        $product .= "</div>";
        $product .= "<div class='wood-type'><h4>Wood Type: " . $this->wood_type . "</h4></div>";
        $product .= "<div class='description'>" . $this->description . "</div>";

        if (isset($this->dimensions) && $this->dimensions !== "")
            $product .= "<div class='dimensions'><b>Dimensions:</b> " . $this->dimensions . "</div>";

        if (isset($this->brand_info["shipping_time"]) && $this->brand_info["shipping_time"] !== "")
            $product .= "<div class='shipping'><b>Shipping Time:</b> " . $this->brand_info["shipping_time"] . "</div>";

        $product .= "</div>";  //end product info grid_8

        $product .= "<div class='product grid_5 push_1 product_image'><span class='helper'></span>";
        $product .= $image;
        $product .= "</div>";

        if (isset($this->product_finishes) && $this->product_finishes !== "")
            $product .= "<div class='product_finishes'><b>Available in these finishes:</b>" . br() . $this->product_finishes . "</div>";

        $product .= "</div>" . br(2); //end standalone product container

        /* display finishes -- in house first */
        $product .= "<div class='clear'></div>";
        $product .= "<div class='grid_12'>";
        $product .= $this->get_pagepart(13);

        /* if there is a special manufacturer's finishes in the db, display that */
        if (isset($this->brand_info["finishes"]) && $this->brand_info["finishes"] !== "")
        {
            $product .= "<h3 class='center_text'>Manufacturer Finishes Available from " . $this->brand . "</h3>" . br();
            $product .= $this->_rewrite_image_addresses($this->brand_info["finishes"]);
        }

        /* end the finishes div */
        $product .= "</div>";
        $product .= br(2);

        return $product;
	} //end function show_product

/* ============================================= _rewrite_image_addresses */
    private function _rewrite_image_addresses($content)
    {
        $content = str_replace(array("src='/images", 'src="/images', "src='images", 'src="images'), array("src='" . site_url() . "images", "src=\"" . site_url() . "images","src='" . site_url() . "images", "src=\"" . site_url() . "images"), $content);                                  //replace image src strings to include the site url

        return $content;
    } //end function

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
} //end class