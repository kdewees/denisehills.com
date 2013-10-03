<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Store extends CI_Model {

    var $product_categories_list = array();

    /* ========================================================================= construct */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    } //end function

    /* ========================================================================= create_product_category_list */
    public function create_product_category_list($product_category_table="categories")
    {
        if ($this->db->field_exists("ordinal", $product_category_table) == TRUE)
            $order_by_ordinal = $product_category_table . ".ordinal, ";
        else
            $order_by_ordinal = "";

        $this->db->select($product_category_table . ".id, " . $product_category_table . ".name, " . $product_category_table . ".parent AS parent_id, " . $product_category_table . ".uri_title");
        $this->db->order_by($product_category_table . ".parent, " . $order_by_ordinal . $product_category_table . ".name");
        $query = $this->db->get($product_category_table);

        $categories = array();

        foreach($query->result() as $category)
        {
            if ($category->parent_id == 0)
            {
                $categories[$category->id]["name"] = $category->name;
                $categories[$category->id]["uri_title"] = $category->uri_title;
            }
            else
            {
                $categories[$category->parent_id]["children"][$category->id]["name"] = $category->name;
                $categories[$category->parent_id]["children"][$category->id]["uri_title"] = $category->uri_title;
            }
        }

        $this->product_categories_list = $categories;
        return $categories;
    } //end function

    /*========================================================================== create_category_dropdown */
    public function create_category_dropdown()
    {
        //$this->config->load("deweesdesigns", TRUE);
        $store_url = $this->config->item("store_controller", "deweesdesigns");
        $categories = $this->create_product_category_list();

        $dropdown = "<ul>";

        foreach($categories as $category)
        {
            if (isset($category["children"]) and is_array($category["children"]))
            {
                $dropdown .= "<li class=\"parent\"><a href=\"/" . $store_url . "/" . $category["uri_title"] . "\">" . $category["name"] . "</a>";
                $dropdown .= "<ul>";

                foreach ($category["children"] as $child)
                {
                    $dropdown .= "<li><a href=\"/" . $store_url . "/" . $category["uri_title"] . "/" . $child["uri_title"] . "\">" . $child["name"] . "</a></li>";
                }

                $dropdown .= "</ul></li>";
            }
            else
            {
                $dropdown .= "<li><a href=\"/" . $store_url . "/" . $category["uri_title"] . "\">" . $category["name"] . "</a></li>";
            }
        }

        $dropdown .= "</ul>";

        return $dropdown;
    } //end function
}