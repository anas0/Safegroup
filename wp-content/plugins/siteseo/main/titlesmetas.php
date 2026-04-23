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

class TitlesMetas{
	
	static function advanced_metas($robots){
		global $siteseo, $post;
		
		$disable_noindex = !empty($siteseo->advanced_settings['appearance_adminbar_noindex']) ?? '';

		if(empty($siteseo->setting_enabled['toggle-titles']) || !empty($disable_noindex)){
			return $robots;
		}
		
		$settings = $siteseo->titles_settings;

		$options_cat = !empty($settings['titles_tax_titles']['category']) ? $settings['titles_tax_titles']['category'] : '';
		$option_tags = !empty($settings['titles_tax_titles']['post_tag']) ? $settings['titles_tax_titles']['post_tag'] : '';
		$option_post = !empty($settings['titles_single_titles']['post']) ? $settings['titles_single_titles']['post'] : '';
		
		$option_page_enable = !empty($settings['titles_single_titles']['page']['enable']) ? $settings['titles_single_titles']['page']['enable'] : '';
		$option_page_noindex = !empty($settings['titles_single_titles']['page']['noindex']) ? $settings['titles_single_titles']['page']['noindex'] : '';
		$option_page_nofollow = !empty($settings['titles_single_titles']['page']['nofollow']) ? $settings['titles_single_titles']['page']['nofollow'] : '';

		$robots_noindex = !empty($settings['titles_noindex']);
		$robots_nofollow = !empty($settings['titles_nofollow']);
		$robots_nosnippet = !empty($settings['titles_nosnippet']);
		$robots_noimageindex = !empty($settings['titles_noimageindex']);
		$robots_noarchive = !empty($settings['titles_noarchive']);
		
		
		$post_id = isset($post) && is_object($post) ? $post->ID : 0;
		
		$robots = [
			'noindex' => !empty(get_post_meta($post_id, '_siteseo_robots_index', true)) || $robots_noindex,
			'nofollow' => !empty(get_post_meta($post_id, '_siteseo_robots_follow', true)) || $robots_nofollow,
			'nosnippet' => !empty(get_post_meta($post_id, '_siteseo_robots_snippet', true)) || $robots_nosnippet,
			'noarchive' => !empty(get_post_meta($post_id, '_siteseo_robots_archive', true)) || $robots_noarchive,
			'noimageindex' => !empty(get_post_meta($post_id, '_siteseo_robots_imageindex', true)) || $robots_noimageindex
		];
		
		$index_extras = [
			'max-snippet' => '-1',
			'max-image-preview' => 'large',
			'max-video-preview' => '-1'
		];
		
		if(is_category() && !empty($options_cat['enable'])){
			if(!empty($options_cat['noindex'])){
				$robots['noindex'] = true;
			} else{
				$robots['index'] = true;
				$robots = array_merge($robots, $index_extras);

			}
			
			if(!empty($options_cat['nofollow'])){
				$robots['nofollow'] = true;
			} else{
				$robots['follow'] = true;
			}
		}
		
		if(is_tag() && !empty($option_tags['enable'])){
			if(!empty($option_tags['noindex']) && !empty($option_tags['enable'])){
				$robots['noindex'] = true;
			} else{
				$robots['index'] = true;
				$robots = array_merge($robots, $index_extras);
			}
			
			if(!empty($option_tags['nofollow'])){
				$robots['nofollow'] = true;
			} else{
				$robots['follow'] = true;
			}
		}

		if(is_author() && !empty($settings['titles_archives_author_noindex'])){
			if(!empty($settings['titles_archives_author_noindex'])){
				$robots['noindex'] = true;
			} else{
				$robots['index'] = true;
				$robots = array_merge($robots, $index_extras);
			}
		}
		
		if(is_date() && !empty($settings['titles_archives_date_noindex'])){
			if(!empty($settings['titles_archives_date_noindex'])){
				$robots['noindex'] = true;
			} else{
				$robots['index'] = true;
				$robots = array_merge($robots, $index_extras);
			}
		}
		
		if(is_search() && !empty($settings['titles_archives_search_title_noindex'])){
			if(!empty($settings['titles_archives_search_title_noindex'])){
				$robots['noindex'] = true;
			} else{
				$robots['index'] = true;
				$robots = array_merge($robots, $index_extras);
			}
		}
		
		if(is_single() && !empty($option_post['enable'])){
			if(!empty($option_post['noindex'])){
				$robots['noindex'] = true;
			} elseif(!empty($robots['noindex'])){
				$robots['noindex'] = true;
			}else{
				$robots['index'] = true;
				$robots = array_merge($robots, $index_extras);
			}
			
			if(!empty($option_post['nofollow'])){
				$robots['nofollow'] = true;
			} elseif(!empty($robots['nofollow'])){
				$robots['nofollow'] = true;
			} else{
				$robots['follow'] = true;
			}
		}

		if(is_page() && !empty($option_page_enable)){
			if(!empty($option_page_noindex)){
				$robots['noindex'] = true;
			} elseif(!empty($robots['noindex'])){
				$robots['noindex'] = true;
			} else{
				$robots['index'] = true;
				$robots = array_merge($robots, $index_extras);
			}
			
			if(!empty($option_page_nofollow)){
				$robots['nofollow'] = true;
			} elseif(!empty($robots['nofollow'])){
				$robots['nofollow'] = true;
			} else{
				$robots['follow'] = true;
			}
		}
		
		if(is_front_page()){
			if(!empty($robots_noindex)){
				$robots['noindex'] = true;
			} else{
				$robots['index'] = true;
				$robots = array_merge($robots, $index_extras);
			}
			
			if(!empty($robots_nofollow)){
				$robots['nofollow'] = true;
			} else{
				$robots['follow'] = true;
			}
		}

		return array_filter($robots);
	}
	
	static function add_nositelinkssearchbox(){
		global $siteseo;
		
		if(empty($siteseo->setting_enabled['toggle-titles'])){
			return;
		}
	
		if(!empty($siteseo->titles_settings['titles_nositelinkssearchbox'])){
			echo '<meta name="google" content="nositelinkssearchbox" >';
		}
	}
	
	static function replace_variables($content, $in_editor = false){
		global $post, $siteseo, $wp_query, $term;
		
		// Site info
		$site_title = get_bloginfo('name');
		$site_tagline = get_bloginfo('description');
		$site_sep = $siteseo->titles_settings['titles_sep'];

		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$page = get_query_var('page') ? get_query_var('page') : 1;
		
		// Date info
		$current_time = current_time('timestamp');
		$archive_date = get_the_date('d');
		$archive_month = get_the_date('M');
		$archive_month_name = get_the_date('F');
		$archive_year = get_the_date('Y');
		
		// Author
		$author_id = isset($post->post_author) ? $post->post_author : get_current_user_id();
		$author_first_name = get_the_author_meta('first_name', $author_id);
		$author_last_name = get_the_author_meta('last_name', $author_id);
		$author_website = get_the_author_meta('url', $author_id);
		$author_nickname = get_the_author_meta('nickname', $author_id);
		$author_bio = get_the_author_meta('description', $author_id);
		
		// WooCommerce
		$wc_variables = [];
		if(function_exists('wc_get_product') && is_singular('product')){
			$product = wc_get_product($post->ID);
			if($product){
				$wc_variables = array(
					'%%wc_single_cat%%' => wp_strip_all_tags(wc_get_product_category_list($post->ID)),
					'%%wc_single_tag%%' => wp_strip_all_tags(wc_get_product_tag_list($post->ID)),
					'%%wc_single_short_desc%%' => $product->get_short_description(),
					'%%wc_single_price%%' => $product->get_price(),
					'%%wc_single_price_exe_tax%%' => $product->get_price_excluding_tax(),
					'%%wc_sku%%' => $product->get_sku()
				);
			}
		}

		$replacements = array(
			'%%sep%%' => $site_sep,
			'%%sitetitle%%' => $site_title,
			'%%tagline%%' => $site_tagline,
			'%%post_title%%' => (is_singular() || $in_editor === TRUE) ? get_the_title() : '',
			'%%post_excerpt%%' => (is_singular() || $in_editor === TRUE) ? get_the_excerpt() : '',
			'%%post_content%%' => (is_singular() || $in_editor === TRUE) ? wp_strip_all_tags(get_the_content()) : '',
			'%%post_thumbnail_url%%' => get_the_post_thumbnail_url($post),
			'%%post_url%%' => get_permalink(),
			'%%post_date%%' => get_the_date(),
			'%%post_modified_date%%' => get_the_modified_date(),
			'%%post_author%%' => get_the_author(),
			'%%post_category%%' => wp_strip_all_tags(get_the_category_list(', ')),
			'%%post_tag%%' => wp_strip_all_tags(get_the_tag_list('', ', ', '')),
			'%%_category_title%%' => single_cat_title('', false),
			'%%_category_description%%' => category_description(),
			'%%tag_title%%' => single_tag_title('', false),
			'%%tag_description%%' => tag_description(),
			'%%term_title%%' => single_term_title('', false),
			'%%term_description%%' => term_description(),
			'%%search_keywords%%' => get_search_query(),
			'%%current_pagination%%' => $paged,
			'%%page%%' => $page,
			'%%cpt_plural%%' => post_type_archive_title('', false),
			'%%archive_title%%' => get_the_archive_title(),
			'%%archive_date%%' => $archive_date,
			'%%archive_date_day%%' => $archive_date,
			'%%archive_date_month%%' => $archive_month,
			'%%archive_date_month_name%%' => $archive_month_name,
			'%%archive_date_year%%' => $archive_year,
			'%%currentday%%' => date_i18n('j', $current_time),
			'%%currentmonth%%' => date_i18n('F', $current_time),
			'%%currentmonth_short%%' => date_i18n('M', $current_time),
			'%%currentmonth_num%%' => date_i18n('n', $current_time),
			'%%currentyear%%' => date_i18n('Y', $current_time),
			'%%currentdate%%' => date_i18n(get_option('date_format'), $current_time),
			'%%currenttime%%' => date_i18n(get_option('time_format'), $current_time),
			'%%author_first_name%%' => $author_first_name,
			'%%author_last_name%%' => $author_last_name,
			'%%author_website%%' => $author_website,
			'%%author_nickname%%' => $author_nickname,
			'%%author_bio%%' => $author_bio,
		);
		
		//WooCommerces
		if(!empty($wc_variables)){
			$replacements = array_merge($replacements, $wc_variables);
		}

		if(preg_match_all('/%%_cf_(.*?)%%/', $content, $matches)){
			foreach ($matches[1] as $custom_field) {
				$meta_value = get_post_meta($post->ID, $custom_field, true);
				$replacements["%%_cf_{$custom_field}%%"] = $meta_value;
			}
		}

		if(preg_match_all('/%%_ct_(.*?)%%/', $content, $matches)){
			foreach($matches[1] as $taxonomy){
				$terms = get_the_terms($post->ID, $taxonomy);
				$term_names = is_array($terms) ? wp_list_pluck($terms, 'name') : [];
				$replacements["%%_ct_{$taxonomy}%%"] = implode(', ', $term_names);
			}
		}

		if(preg_match_all('/%%_ucf_(.*?)%%/', $content, $matches)){
			foreach($matches[1] as $user_meta){
				$meta_value = get_user_meta($author_id, $user_meta, true);
				$replacements["%%_ucf_{$user_meta}%%"] = $meta_value;
			}
		}

		$target_keywords = isset($siteseo->keywords_settings['tempory_set']) ? $siteseo->keywords_settings['tempory_set'] : '';
		$replacements['%%target_keyword%%'] = $target_keywords;

		$replacements = array_map(function($value){
			if(is_array($value) || is_object($value)){
				return '';
			}

			return is_null($value) ? '' : wp_strip_all_tags($value);
		}, $replacements);

		return str_replace(
			array_keys($replacements),
			array_values($replacements),
			$content
		);
	}
	
	static function modify_site_title($title, $sep = ''){
        global $siteseo, $post;

		if(empty($siteseo->setting_enabled['toggle-titles'])){
			return;
		}

		$settings = $siteseo->titles_settings;

		$post_types = isset($settings['titles_single_titles']['post']['title']) ? $settings['titles_single_titles']['post']['title'] : '';
		$page_types = isset($settings['titles_single_titles']['page']['title']) ? $settings['titles_single_titles']['page']['title'] : '';
		$product_types = isset($settings['titles_single_titles']['product']['title']) ? $settings['titles_single_titles']['product']['title'] : '';
		$category_title = isset($settings['titles_tax_titles']['category']['title']) ? $settings['titles_tax_titles']['category']['title'] : '';
		$prod_cat_title = isset($settings['titles_tax_titles']['product_cat']['title']) ? $settings['titles_tax_titles']['product_cat']['title'] : '';
		$prod_cat_enable = isset($settings['titles_tax_titles']['product_cat']['enable']) ? $settings['titles_tax_titles']['product_cat']['enable'] : '';
		
		$category_enable = isset($settings['titles_tax_titles']['category']['enable']) ? $settings['titles_tax_titles']['category']['enable'] : '';
		$tags_title = isset($settings['titles_tax_titles']['post_tag']['title']) ? $settings['titles_tax_titles']['post_tag']['title'] : '';
		$tags_enable = isset($settings['titles_tax_titles']['post_tag']['enable']);

		// Check set by meta
		$post_id = isset($post) && is_object($post) ? $post->ID : '';
		$post_types_new_title = !empty(get_post_meta($post_id, '_siteseo_titles_title', true)) ? get_post_meta($post_id, '_siteseo_titles_title', true) : $post_types; 
		$page_types_new_title = !empty(get_post_meta($post_id, '_siteseo_titles_title',true)) ? get_post_meta($post_id, '_siteseo_titles_title', true) : $page_types;
		$product_types_new_title = !empty(get_post_meta($post_id, '_siteseo_titles_title', true)) ? get_post_meta($post_id, '_siteseo_titles_title', true) : $product_types;

		// Default
		if(is_front_page() && !empty($settings['titles_home_site_title'])){
			$new_title = esc_attr(self::replace_variables($settings['titles_home_site_title']));

			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}
		
		if(function_exists('is_product') && is_product() && !empty($product_types_new_title)){
			$new_title = esc_attr(self::replace_variables($product_types_new_title));
			
			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}
		
		if(function_exists('is_product_category') && is_product_category() && !empty($prod_cat_title) && !empty($prod_cat_enable)){
			$new_title = esc_attr(self::replace_variables($prod_cat_title));
			
			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}

		// Page types
		if(is_page() && !empty($page_types_new_title)){
			$new_title = esc_attr(self::replace_variables($page_types_new_title));

			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}

		// Post types
		if(is_single() && !empty($post_types_new_title)){
			$new_title = esc_attr(self::replace_variables($post_types_new_title));

			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}

		// Category taxonomie
		if(is_category() && !empty($category_title) && !empty($category_enable)){
			$new_title = esc_attr(self::replace_variables($category_title));
			
			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}

		// Tag taxonomies
		if(is_tag() && !empty($tags_title) && !empty($tags_enable)){

			$new_title = esc_attr(self::replace_variables($tags_title));

			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}

		// Author archive
		if(is_author() && !empty($settings['titles_archives_author_title']) && empty($settings['titles_archives_author_disable'])){

			$new_title = esc_attr(self::replace_variables($settings['titles_archives_author_title']));

			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}

		// Date archive
		if(is_date() && !empty($settings['titles_archives_date_title']) && empty($settings['titles_archives_date_disable'])){

			$new_title = esc_attr(self::replace_variables($settings['titles_archives_date_title']));

			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}
		
		// Search archive
		if(is_search() && !empty($settings['titles_archives_search_title'])){

			$new_title = esc_attr(self::replace_variables($settings['titles_archives_search_title']));

			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}
		
		// 404 archive
		if(is_404() && !empty($settings['titles_archives_404_title'])){

			$new_title = esc_attr(self::replace_variables($settings['titles_archives_404_title']));

			if(!empty($sep)){
				$new_title .= " $sep " . get_bloginfo('name');
			}

			return $new_title;
		}

        return $title;
    }
	
	static function add_meta_description(){
		global $siteseo, $post;

		if(empty($siteseo->setting_enabled['toggle-titles'])){
			return;
		}

		$settings = $siteseo->titles_settings;

		$page_types = isset($settings['titles_single_titles']['page']['description']) ? $settings['titles_single_titles']['page']['description'] : '';
		$post_types = isset($settings['titles_single_titles']['post']['description']) ? $settings['titles_single_titles']['post']['description'] : '';
		$product_types = isset($settings['titles_single_titles']['product']['description']) ? $settings['titles_single_titles']['product']['description'] : '';
		$category_desc = isset($settings['titles_tax_titles']['category']['description']) ? $settings['titles_tax_titles']['category']['description'] : '';
		$tags_desc = isset($settings['titles_tax_titles']['post_tag']['description']) ? $settings['titles_tax_titles']['post_tag']['description'] : '';
		$category_enable = isset($settings['titles_tax_titles']['category']['enable']);
		$tags_enable = isset($settings['titles_tax_titles']['post_tag']);
		
		$post_id = isset($post) && is_object($post) ? $post->ID : '';

		// Check set by meta
		$page_new_desc = !empty(get_post_meta($post_id, '_siteseo_titles_desc', true)) ? get_post_meta($post_id, '_siteseo_titles_desc', true) : $page_types;
		$post_new_desc = !empty(get_post_meta($post_id, '_siteseo_titles_desc', true)) ? get_post_meta($post_id, '_siteseo_titles_desc', true) : $post_types;
		$product_new_desc = !empty(get_post_meta($post_id, '_siteseo_titles_desc', true)) ? get_post_meta($post_id, '_siteseo_titles_desc', true) : $product_types;

		// Default
		if(is_front_page() && !empty($settings['titles_home_site_desc'])){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($settings['titles_home_site_desc'])) . '">';
		} elseif(is_front_page() && empty($settings['titles_home_site_desc'])){
			$description = get_bloginfo('description');

			if(!empty($description)){
				echo '<meta name="description" content="'.esc_attr($description) . '">';
			}
		}
		
		// Product Type
		if(function_exists('is_product') && is_product() && !empty($product_new_desc)){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($product_new_desc)).'" >';
		}

		// Page types
		if(is_page() && !empty($page_new_desc)){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($page_new_desc)).'" >';
	
		}
		// Post types
		if(is_single() && !empty($post_new_desc)){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($post_new_desc)).'" >';	
		}

		// Category archive
		if(is_category() && !empty($category_desc) && !empty($category_enable)){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($category_desc)).'" >'; 
		}

		// Tag archives
		if(is_tag() && !empty($tags_desc) && !empty($tags_enable)){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($tags_desc)).'" >';
		}

		// Author archive
		if(is_author() && !empty($settings['titles_archives_author_desc']) && empty($settings['titles_archives_author_disable'])){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($settings['titles_archives_author_desc'])).'" >';
		}
		
		// Date archive
		if(is_date() && !empty($settings['titles_archives_date_desc']) && empty($settings['titles_archives_date_disable'])){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($settings['titles_archives_date_desc'])).'" >';
		}
		
		// Search archive
		if(is_search() && !empty($settings['titles_archives_search_desc'])){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($settings['titles_archives_search_desc'])).'" >';
		}
		
		// 404 archives
		if(is_404() && !empty($settings['titles_archives_404_desc'])){
			echo '<meta name="description" content="'.esc_attr(self::replace_variables($settings['titles_archives_404_desc'])).'" >';
		}
	}
	
	static function add_rel_link_pages(){
		global $siteseo, $paged;

		if(empty($siteseo->setting_enabled['toggle-titles'])){
			return;
		}

		if(!empty($siteseo->titles_settings['titles_paged_rel'])){

			if(get_previous_posts_link()){

				echo '<link rel="prev" href="'.esc_url(get_pagenum_link($paged - 1)).'" />';
			}
			if(get_next_posts_link()){

				echo '<link rel="next" href="'.esc_url(get_pagenum_link($paged + 1)).'" />';
			}
		}
	}

	static function date_time_publish(){
		global $siteseo;
		
		if(empty($siteseo->setting_enabled['toggle-titles'])){
			return;
		}
		
		$post_types = isset($siteseo->titles_settings['titles_single_titles']['post']) ?? '';
        $page_types = isset($siteseo->titles_settings['titles_single_titles']['page']) ?? '';
		
		if(!empty($post_types['date']) && is_single()){
			$published_time = get_the_date('c');
			$modified_time = get_the_modified_date('c');  
			echo '<meta article:published_time content="'.esc_attr($published_time).'">';
			echo '<meta article:modified_time content="'.esc_attr($modified_time).'">';
		}
		
		if(!empty($page_types['date']) && is_page()){
			$published_time = get_the_date('c');
			$modified_time = get_the_modified_date('c');  
			echo '<meta article:published_time content="'.esc_attr($published_time).'">';
			echo '<meta article:modified_time content="'.esc_attr($modified_time).'">';
			
		}
		
		if(!empty($post_types['thumb_gcs']) && is_single()){
			if(get_the_post_thumbnail_url(get_the_ID())){
				echo '<meta name="thumbnail" content="'.esc_url(get_the_post_thumbnail_url(get_the_ID())).'">';
			}
		}

	}

	static function metaboxes_enable(){
		global $siteseo;
		
		$siteseo_options = $siteseo->advanced_settings;
		
		// We do not want to show any metabox if we have universal metabox enabled.
		if(!empty($siteseo->setting_enabled['toggle-advanced']) && !empty($siteseo->advanced_settings['appearance_universal_metabox'])){
			return;
		}

		$page_settings = $siteseo->titles_settings['titles_single_titles']['page']['enable'] ?? '';
		$post_settings = $siteseo->titles_settings['titles_single_titles']['post']['enable'] ?? '';
		
		$seo_metabox_roles = !empty($siteseo_options['security_metaboxe_role']) ? $siteseo_options['security_metaboxe_role'] : [];
		
		$allow_user_metabox = true; 
		
		if(is_user_logged_in()){
			$user = wp_get_current_user();
			
			if(is_super_admin()){
				$allow_user_metabox = true;
			} else{
				
				$siteseo_user_role = current($user->roles);
				
				if (array_key_exists($siteseo_user_role, $seo_metabox_roles)){
					$allow_user_metabox = false;
				}
			}
		}
		
		if(!empty($post_settings) && $allow_user_metabox){
			// enabled	
			add_action('add_meta_boxes', '\SiteSEO\TitlesMetas::post_metabox');
		}
		
		if(!empty($page_settings) && $allow_user_metabox){
			// enabled
			add_action('add_meta_boxes', '\SiteSEO\TitlesMetas::page_metabox');
		}
	}
	
	static function post_metabox(){
		add_meta_box('siteseo-post-metabox', 'SiteSEO', 'SiteSEO\metaboxes\Settings::render_metabox', 'post', 'normal', 'high');
	}
	
	static function page_metabox(){
		add_meta_box('siteseo-page-metabox', 'SiteSEO', 'SiteSEO\metaboxes\Settings::render_metabox', 'page', 'normal', 'high');
	}
}
