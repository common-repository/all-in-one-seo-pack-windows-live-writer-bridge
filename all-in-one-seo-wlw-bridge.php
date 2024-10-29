<?php
/*
Plugin Name: All In One SEO Pack Windows Live Writer Bridge
Plugin URI: http://techie-buzz.com/wordpress-plugins/all-in-one-seo-wlw-bridge-plugin.html
Description: All In One SEO Pack to Windows Live Writer Bridge allows bloggers using Windows Live Writer to control the SEO parameters from their desktop without having to visit the website to add All-in-one-seo parameters
Version: 1.1
Author: Keith Dsouza
Author URI: http://techie-buzz.com/

If Users have added All In One SEO Tags Using the Windows Live Writer Plugin, the parameters will be as follows

<!--aiospwlwbstart
aiosp_title=SEO Title
aiosp_keywords=SEO,KEYWORDS
aiosp_description=SEO Description
aiospwlwbsend-->

*/

/**
* Adds a Bridge between WLW and All in One SEO Pack, allowing users to
* add keywords, SEO title and description while blogging using WLW
* hooks into the xmlrpc_publish_post action 
*
*
* v1.0 - Initial Release
* v1.1 - Changed the var names to match those used by AIOSEO
*
*/

function all_in_one_seo_wlw_bridge($id) {
  $aioswb_store_array = array();
  //argh too bad I have to do this, alas the xmlrpc for wordpress does not have appropriate hooks
  $post = get_post($id);  
  $content = $post->post_content;
  //All in ONE Seo WLW Bridge Params spotted in the wild, work on them and add them to the database
  if (preg_match("|<!--aiospwlwbstart|", $content) && preg_match("|aiospwlwbsend-->|", $content)) {
    preg_match ("/(.*?)<!--aiospwlwbstart(.*?)aiospwlwbsend-->(.*?)/isU", $content, $matcher);
    
    //NADA will be using this when we need to update the post and remove our content from it
    //replace everything from the content
    //$aioswb_content_content = preg_replace("/".$matcher[2]."/isU", "", $aioswb_content_content);
    //$aioswb_content_content = preg_replace("/<!--aiospwlwbstart-->/isU", "", $aioswb_content_content);
    //$aioswb_content_content = preg_replace("/<!--aiospwlwbsend-->/isU", "", $aioswb_content_content);
    
    if($matcher) {
      $aios_tags_temp = $matcher[2];
      //remove extra html comments from the content
      $aios_tags_temp = preg_replace("/<!--/isU", "", $aios_tags_temp);
      $aios_tags_temp = preg_replace("/-->/isU", "", $aios_tags_temp);
      
      //break our content into a array
      $aios_tags_array = split("\n", $aios_tags_temp);
      //cleanup empty elements from this array
      $aios_tags_array = array_filter($aios_tags_array);
      //go go go do our stuff here
      foreach($aios_tags_array as $aios_tags) {
        $aios_tags_nv_pair = split("=", $aios_tags);
        $tag_name = trim($aios_tags_nv_pair[0]);
        $tag_value = trim($aios_tags_nv_pair[1]);
        $aioswb_store_array[$tag_name] = $tag_value;
        //not empty then add it to our consuming array
        if(!empty($tag_name) && ! empty($tag_value)) {
          $aioswb_store_array[$tag_name] = $tag_value;
        }
      }
      //remove empty stuff from our consuming array
      $aioswb_store_array = array_filter($aioswb_store_array);
      
      $keywords_text = "keywords";
      $description_text = "description";
      $title_text = "title";
      
      /*
      * Later versions of AIOSEO plugin uses different store for keywords etc
      */
      if(function_exists("aioseop_get_version")) {
        $keywords_text = "_aioseop_keywords";
        $description_text = "_aioseop_description";
        $title_text = "_aioseop_title";
      }
      
      //we need to make sure its and array
      if(is_array($aioswb_store_array)) {
        $keywords = $aioswb_store_array["aiosp_keywords"];
		    $description = $aioswb_store_array["aiosp_description"];
		    $title = $aioswb_store_array["aiosp_title"];
      
		    if (isset($keywords) && !empty($keywords)) {
			    delete_post_meta($id, $keywords_text);
          add_post_meta($id, $keywords_text, $keywords);
		    }
		    if (isset($description) && !empty($description)) {
			    delete_post_meta($id, $_aioseop_description);
          add_post_meta($id, $_aioseop_description, $description);
		    }
		    if (isset($title) && !empty($title)) {
			    delete_post_meta($id, $_aioseop_title);
          add_post_meta($id, $_aioseop_title, $title);
		    }
      }
      //done done done, nothing more to do, happy bloggers live longer
      //so do I :-) ;-)
    }
  }
}

//we only hook into one action right now, the one where the post is coming from a external client
add_action('xmlrpc_publish_post','all_in_one_seo_wlw_bridge');


?>