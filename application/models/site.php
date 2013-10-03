<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Site extends CI_Model {

	var $used_brands = '';
	var $category_nav = '';
	var $specials = '';

/* ======================================================== construct */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    } //end function

/* ===================================================== get_used_brands */
	public function get_used_brands()
	{
		if (!empty($this->used_brands))
			return $this->used_brands;

		$this->db->select('products.brand, brands.name, brands.image, brands.uri_title');
		$this->db->distinct('products.brand');
		$this->db->order_by('brands.name');
		$this->db->join('brands','brands.id=products.brand');
		$query = $this->db->get('products');

		$brands = "";
		$column_counter = 1;
		$class = '';
		foreach ($query->result() as $brand)
		{
			switch($column_counter)
			{
				case 1:
					$class = ' alpha';
					$column_counter++;
					break;
				case 2:
					$class = '';
					$column_counter++;
					break;
				case 3:
					$column_counter = 1;
					$class = ' omega';
					break;
			} //end switch

			$brands .= "<div class='grid_2 cat{$class}'><a href='/products/brands/" . $brand->uri_title . "'>" . img("images/brands/" . $brand->image) . "</a></div>\n";
		} //end foreach

/*
		switch($column_counter)
		{
			case 1:
				break;
			case 2:
				//$brands .= "<div class='grid_2 cat omega'>" . nbs() . "</div><div class='grid_2 cat omega'>" . nbs() . "</div>";
				break;
			case 3:
				//$brands .= "<div class='grid_2 cat omega'>" . nbs() . "</div>";
				break;
		} //end switch
*/

		$this->used_brands = $brands;

		return $brands;
	} //end function get_used_brands

/* ===================================================== get_specials */
	public function get_specials()
	{
		if (!empty($this->specials))
			return $this->specials;

		//$this->db->limit(2);
		$this->db->order_by('id', 'random');
		$query = $this->db->get('products');

		$num_specials = 0;
		$specials = "";
		foreach ($query->result() as $special)
		{
			//check to make sure the image is valid
			//if not, skip this special
			if (!is_file("images/products/" . $special->image))
				continue;

			$specials .= "<div class='grid_6 special'>
	<div class='sash'><span>special!</span></div>
	<div>" . img("images/products/" . $special->image) . "</div>
	<div class='description'>" . $special->name . " - $";

			$specials .= $special->sale_price == 0 ? $special->price : $special->sale_price;

			$specials .= "</div>
</div><!-- special -->";

			$num_specials++;

			if ($num_specials === 2)
				break;
		} //end foreach

		$this->specials = $specials;

		return $specials;
	} //end function get_specials()

/* ===================================================== nav_jquery */
	public function nav_jquery()
	{
		$jquery = "<script type='text/javascript'>
jQuery(document).ready(function(){
	$('a.parent').toggle(
		function(event){
			$(this).next('div.child:first').slideDown();
			$(this).css('background-image', 'url(/images/left_button_bg_up.png)');
		},
		function(){
			$(this).next('div.child:first').slideUp();
			$(this).css('background-image', 'url(/images/left_button_bg_down.png)');
		}
	); //end area hover function
});
</script>
</script>\n";

		return $jquery;
	} //end function nav_jquery

/* ===================================================== create_category_nav */
	public function get_category_nav()
	{
		if (!empty($this->category_nav))
			return $this->category_nav;

		$this->db->order_by('parent,name');
		$this->db->where("id != 7");
		$query = $this->db->get('categories');

		$parent_categories = array();
		foreach($query->result() as $category)
		{
			if ($category->parent == 0)
			{
				$parent_categories[$category->id] = array(
								'name' => $category->name,
								'link' => $category->uri_title,
								'sub_categories' => array());
			} else {
				$parent_categories[$category->parent]['sub_categories'][] = array(
						'name' => $category->name,
						'link' => $category->uri_title,
						'id' => $category->id);
			} //end if
		} //end foreach

		$links = "";

		foreach ($parent_categories as $parent_category)
		{	
			$links .= anchor("products/" . $parent_category['link'], $parent_category['name'], array('class' => 'parent')) . "\n";

			if (!empty($parent_category['sub_categories']))
			{
				$links .= "<div class='child'>\n";
				foreach($parent_category['sub_categories'] as $sub_category)
				{
					$links .= anchor("products/categories/" . $sub_category['link'], $sub_category['name'], array('class' => 'child')) . "\n";
				} //end foreach
				$links .= "</div><!-- child -->\n";
			} //end if
		} //end foreach

		$this->category_nav = $links;

		return $links;
	} //end function create_category_nav

} //end class