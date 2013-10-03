<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Products extends CI_Controller {

/* ======================================================== construct */
	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
		$this->load->library("pagination");
        $this->config->load("deweesdesigns", TRUE);
    } //end function

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
			//then the "method" is really a category id, send it to index
			return call_user_func_array(array($this, "index"), $params);
		}
	} //end function _remap

/* ======================================================== index */
	public function index($parent_category, $child_category = "", $offset = 0, $search_by = "categories")          //params are variable
	{
		$products_per_page = $this->config->item("pagination_max_entries", "deweesdesigns");

		//create page
			$this_page = new Page();
			$this_page->make_page(2);

		//get products from db
			$product_array = $this->_get_products($search_by, $parent_category, $child_category, $offset, $products_per_page);

        //add products to page content
            $this_page->content = $product_array["products"];

		//create pagination links
        $pagination = "";
        if ($child_category)
        {
            $uri = $parent_category . "/" . $child_category;

            $config["base_url"] = base_url() . "products/" . $uri;
            $config["per_page"] = $products_per_page;
            $config["total_rows"] = $product_array["num_products"];
            $this->pagination->initialize($config);
            $pagination = $this->pagination->create_links();
        }

		$this_page->content = $product_array["headline"] . $pagination . $this_page->content . br() . $pagination;
        $this_page->title = strip_tags($product_array["headline"]);

		$this->load->view('default', $this_page); //display page
	} //end function index

/* ======================================================== _get_products */
	private function _get_products($search_by, $parent_category, $child_category, $offset, $products_per_page)
	{
        $total_num_products = 0;
        $parent_name = "";
        if ($search_by == "categories" && (!isset($child_category) or $child_category == ""))   //then this is a parent category...list sub_categories
        {
            $query = $this->db->get_where("categories", array("uri_title" => $parent_category));
            $row = $query->row();
            $parent_id = $row->id;
            $parent_name = ucwords($row->name);
            $query->free_result();

            $this->db->select("categories.*, products.image");
            $this->db->join("product_category", "product_category.category_id = categories.id");
            $this->db->join("products", "products.id = product_category.product_id");
            $this->db->where("categories.parent", $parent_id);
            $this->db->order_by("categories.name");
            $this->db->group_by("categories.id");
            $query = $this->db->get("categories");

            $top_div_class = "sub-category-list";
            $grid_class = "grid_4";
            $grid_height = "270px";
            $listing = "sub-category";
            $next_line_after = 3;
        }
        else                                                                    // this is a child category - list products
        {
            $this->db->select("id, name");
            $query = $this->db->get_where("categories", array('uri_title'=>$parent_category));
            $row = $query->row();
            $parent_category_id = $row->id;
            $parent_name = $row->name;
            $query->free_result();

            $this->db->select("id, name");
            $query = $this->db->get_where("categories",array('uri_title'=>$child_category, 'parent'=>$parent_category_id));
            $row = $query->row();
            $child_category_id = $row->id;
            $category_name = $row->name;
            $query->free_result();

            $this->db->start_cache();
            $this->db->select("products.*, brands.name as brand_name");
            $this->db->where("product_category.category_id", $child_category_id);
            $this->db->join("products", "products.id = product_category.product_id");
            $this->db->join("brands", "brands.id = products.brand");
            $this->db->order_by("products.name");
            $this->db->stop_cache();

            $total_num_products = $this->db->count_all_results("product_category");

            $query = $this->db->get("product_category", $products_per_page, $offset);

            $this->db->flush_cache();

            //echo $this->db->last_query();

            if ($query->num_rows() === 0)
            {
                return array("num_products" => 0, "products" => "<br /><h2 class='center'>No Products Found</h2>\n", 'headline' => "");
            }

            $top_div_class = "product-list";
            $grid_class = "grid_3";
            $grid_height = "198px";
            $listing = "product";
            $next_line_after = 4;
        }

        $headline = "<h1 class='headline'>$parent_name</h1>";
        $products = "<div class='$top_div_class'>";
        $counter = 1;

		foreach($query->result() as $product)
		{
            if ($counter === 1)
            {
                $headline = "<h1 class='headline'>";
                if ($listing == "sub-category")
                    $headline .= $parent_name;
                elseif ($listing == "product")
                    $headline .= "<a href='/products/" . $parent_category . "'>" . $parent_name . "</a> &gt; " . ucwords($category_name);
                $headline .= "</h1>";
            }
            if (!empty($product->image))
				$image = img(array("src" => "images/products/" . $product->image, "alt" => $product->name));
			else
				$image = "<div class='no-image'>no<br />image</div>";

            /*
            if (isset($product->thumbnail) && !empty($product->thumbnail))
                $thumbnail = img("images/products/thumbnails/" . $product->thumbnail);
            else
                $thumbnail = "<div class='no-image'>no<br />image</div>";
            */

			$products .= "<div class='$grid_class $listing'>";                  //beginning listing div
            $products .= "<a href='/products/";                                 //beginning link
            if ($listing == "sub-category")                                     //if this is a sub-cat listing...
            {
                $products .= $parent_category . "/" . $product->uri_title;          //...then link to sub-cat product listing
            }
            elseif ($listing == "product")                                      //if this is a product lising...
            {
                $products .= "view/" . $product->id . "/" . $parent_category . "/" . $child_category;                            //then link to the product
            }
            $products .= "'>";                                                  //end link
            $products .= "<div class='product_image'><span class='helper'></span>" . $image . "</div>";                                                //display product image
            //$products .= br();
            //$products .= $thumbnail;                                            //display thumbnail image
            //$products .= "<div class='price'>" . $product->price . "</div>";    //display product price
            $products .= "<div class='name'>" . $product->name . "</div>";
            //$products .= "<div class='description'>" . $product->description . "</div>";
            $products .= "</a>";                                                //end link to product
            $products .= "</div>";                                              //end grid div

            $counter++;

            if ($counter % $next_line_after == 1)
                $products .= "<div class='clear'></div>";
		}

		$products .= "</div><!-- end product list -->\n";

		return array("num_products" => $total_num_products, "products" => $products, "headline" => $headline);
	} //end function

/* ======================================================== view_product */
	public function view($product_id, $parent_category, $child_category)
	{
		if (empty($product_id))
			$this->index($product_id);

		$this->load->model('product');
		$the_product = New Product();
		$the_product->make_product($product_id);

		$this_page = new Page();
		$this_page->make_page(2);

        $this_page->content = $the_product->show_product($product_id, $parent_category, $child_category);
        $this_page->title = $the_product->name;

		$this->load->view('default', $this_page);
	} //end function view_product

} //end class

/* End of file products.php */
/* Location: ./application/controllers/products.php */