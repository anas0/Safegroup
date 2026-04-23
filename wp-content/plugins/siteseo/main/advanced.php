<?php

namespace SiteSEO;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class Advanced{
	
	static function tags(){
		global $siteseo;

		if(empty($siteseo->setting_enabled['toggle-advanced'])){
			return; // toggle disable
		}
		// meta tags
		if(!empty($siteseo->advanced_settings['advanced_google'])){
			echo '<meta name="google-site-verification" content="'.esc_attr($siteseo->advanced_settings['advanced_google']).'" />' . "\n";
		}

		if(!empty($siteseo->advanced_settings['advanced_bing'])){
			echo '<meta name="msvalidate.01" content="'.esc_attr($siteseo->advanced_settings['advanced_bing']).'" />' . "\n";
		}

		if(!empty($siteseo->advanced_settings['advanced_pinterest'])){
			echo '<meta name="p:domain_verify" content="'.esc_attr($siteseo->advanced_settings['advanced_pinterest']).'" />';
		}

		if(!empty($siteseo->advanced_settings['advanced_yandex'])){
			echo '<meta name="yandex-verification" content="'.esc_attr($siteseo->advanced_settings['advanced_yandex']).'" />';
		}

		if(!empty($siteseo->advanced_settings['advanced_wp_rsd'])){
			remove_action('wp_head', 'rsd_link');
		}

	}
	
	static function remove_links(){
		global $siteseo;

		if(empty($siteseo->setting_enabled['toggle-advanced'])){
			return; // toggle disable
		}

		if(!empty($siteseo->advanced_settings['advanced_wp_rsd'])){
			remove_action('wp_head', 'rsd_link');
		}

		if(!empty($siteseo->advanced_settings['advanced_wp_wlw'])){
			remove_action('wp_head', 'wlwmanifest_link');
		}

		if(!empty($siteseo->advanced_settings['advanced_wp_shortlink'])){
			remove_action('wp_head', 'wp_shortlink_wp_head');
		}

		if(!empty($siteseo->advanced_settings['advanced_wp_generator'])){
			remove_action('wp_head', 'wp_generator');
		}

		if(!empty($siteseo->advanced_settings['advanced_comments_form_link'])){
			add_filter('comment_form_default_fields', '\SiteSEO\Advanced::remove_comment_url_field');
		}

		if(!empty($siteseo->advanced_settings['advanced_comments_author_url'])){
			add_filter('get_comment_author_link', '\SiteSEO\Advanced::remove_author_link_if_profile_url');
		}

		if(!empty($siteseo->advanced_settings['advanced_hentry'])){
			add_filter('post_class', '\SiteSEO\Advanced::remove_hentry_post_class');
		}

		if(!empty($siteseo->advanced_settings['advanced_noreferrer'])){
			add_filter('the_content', '\SiteSEO\Advanced::remove_noreferrer_from_post_content');
		}

		if(!empty($siteseo->advanced_settings['advanced_tax_desc_editor'])){
			add_action('edit_term', '\SiteSEO\Advanced::add_wp_editor_to_taxonomy_description', 10, 2);
		}
		
		if(!empty($siteseo->advanced_settings['advanced_category_url'])){
			add_action('init', '\SiteSEO\Advanced::remove_category_base');
		}
	}
	
	static function add_wp_editor_to_taxonomy_description($tag){

		if('edit' !== get_current_screen()->base || 'edit-tags' !== get_current_screen()->id){
			return;
		}

		if(isset($tag->description)){
			$editor_settings = array(
				'textarea_name' => 'description',
				'textarea_rows' => 10,
				'editor_class' => 'wp-editor-area',
				'media_buttons' => true,
				'tinymce' => true,
				'quicktags' => true,
			);

			wp_editor($tag->description, 'description', $editor_settings);
		}
	}

	static function remove_noreferrer_from_post_content($content){

		$content = preg_replace('/<a[^>]+rel=["\']?noreferrer["\']?[^>]*>/i', '<a', $content);
		return $content;
	}

	static function remove_hentry_post_class($classes){
		$classes = array_diff($classes, array('hentry'));
		return $classes;
	}

	static function remove_comment_url_field($fields){
		if(isset($fields['url'])){
			unset($fields['url']);
		}

		return $fields;
	}

	static function remove_author_link_if_profile_url(){
		$user_id = $comment->user_id;

		if(!empty($user_id)){
			$user_website = get_the_author_meta('user_url', $user_id);

			if($user_website){
				return get_comment_author($comment->comment_ID);
			}
		}

		return $author;
	}
	
	static function remove_category_base(){
		if(is_category() && !is_admin()){
			wp_redirect(home_url('/' . get_query_var('category_name') . '/'));
			exit;
		}

		add_rewrite_rule(
			'^([^/]+)/?$',
			'index.php?category_name=$matches[1]',
			'top'
		);
	}
}