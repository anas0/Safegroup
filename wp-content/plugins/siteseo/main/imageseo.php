<?php
/*
* SITESEO
* https://siteseo.io
* (c) SiteSEO Team
*/

namespace SiteSEO;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class ImageSeo{

	static function init(){
		global $siteseo;
		
		if(empty($siteseo->setting_enabled['toggle-advanced'])){
			return; // toggle disable
		}
		
		if(!empty($siteseo->advanced_settings['advanced_attachments'])){
			add_action('template_redirect', '\SiteSEO\ImageSeo::redirect_attachment_to_parent');
		}

		if(!empty($siteseo->advanced_settings['advanced_clean_filename'])){
			add_filter('sanitize_file_name', '\SiteSEO\ImageSeo::clean_media_filename', 10, 1);
		}

		if(!empty($siteseo->advanced_settings['advanced_image_auto_alt_editor'])){
			add_filter('wp_generate_attachment_metadata', '\SiteSEO\ImageSeo::set_alt_text_from_filename', 10, 2);
		}

		if(!empty($siteseo->advanced_settings['advanced_image_auto_caption_editor'])){
			add_filter('wp_generate_attachment_metadata', '\SiteSEO\ImageSeo::set_caption_from_filename', 10, 2);
		}
		
		if(!empty($siteseo->advanced_settings['advanced_image_auto_desc_editor'])){
			add_filter('wp_generate_attachment_metadata', '\SiteSEO\ImageSeo::set_description_from_filename', 10, 2);
		}
	}
	
	static function set_description_from_filename($metadata, $attachment_id){

		$attachment = get_post($attachment_id);
		$file_name = pathinfo($attachment->guid, PATHINFO_FILENAME);
		$file_name_clean = sanitize_title_with_dashes($file_name);
		
		wp_update_post(array(
			'ID' => $attachment_id,
			'post_content' => $file_name_clean, // This is the description field
		));

		return $metadata;
	}
	
	static function set_caption_from_filename($metadata, $attachment_id){
		
		$attachment = get_post($attachment_id);
		$file_name = pathinfo($attachment->guid, PATHINFO_FILENAME);
		$file_name_clean = sanitize_title_with_dashes($file_name);

		wp_update_post(array(
			'ID' => $attachment_id,
			'post_excerpt' => $file_name_clean, // This is the caption field
		));

		return $metadata;
	}

	static function set_alt_text_from_filename($metadata, $attachment_id){
		$attachment = get_post($attachment_id);
		$file_name = pathinfo($attachment->guid, PATHINFO_FILENAME);
		$file_name_clean = sanitize_title_with_dashes($file_name);

		update_post_meta($attachment_id, '_wp_attachment_image_alt', $file_name_clean);

		return $metadata;
	}
	
	static function clean_media_filename($filename){
		$filename = strtolower($filename);		
		$filename = remove_accents($filename);
		$filename = preg_replace('/[^a-z0-9-.]+/', '-', $filename);
		$filename = trim($filename, '-.');

		$file_info = pathinfo($filename);
		$name = $file_info['filename'];
		$extension = isset($file_info['extension']) ? '.' . $file_info['extension'] : '';

		$filename = $name . $extension;

		return $filename;
	}

	static function redirect_attachment_to_parent(){

		if(is_attachment()){
 
			$attachment_id = get_queried_object_id();
			$parent_id = wp_get_post_parent_id($attachment_id);

			if($parent_id){
				wp_redirect(get_permalink($parent_id));

			}else{
				wp_redirect(home_url());
			}

			exit; 
		}
	}
}
