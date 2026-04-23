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

class SocialMetas{

	static function add_social_graph(){
		global $siteseo;

		if(empty($siteseo->setting_enabled['toggle-social']) || empty($siteseo->setting_enabled['toggle-social'])){
			return;
		}

		$org_type = !empty($siteseo->social_settings['social_knowledge_type']) && $siteseo->social_settings['social_knowledge_type'] !== 'none' ? $siteseo->social_settings['social_knowledge_type'] : '';
		$org_name = !empty($siteseo->social_settings['social_knowledge_name']) ? $siteseo->social_settings['social_knowledge_name'] : ''; 
		$org_logo = !empty($siteseo->social_settings['social_knowledge_img']) ? $siteseo->social_settings['social_knowledge_img'] : '';
		$org_number = !empty($siteseo->social_settings['social_knowledge_phone']) ? $siteseo->social_settings['social_knowledge_phone'] : '';
		$org_contact_type = !empty($siteseo->social_settings['social_knowledge_contact_type']) ? $siteseo->social_settings['social_knowledge_contact_type'] : '';
		$org_contact_option = !empty($siteseo->social_settings['social_knowledge_contact_option']) ? $siteseo->social_settings['social_knowledge_contact_option'] : '';

		$fb_account = !empty($siteseo->social_settings['social_accounts_facebook']) ? $siteseo->social_settings['social_accounts_facebook'] : '';
		$twitter_account = !empty($siteseo->social_settings['social_accounts_twitter']) ? $siteseo->social_settings['social_accounts_twitter'] : '';
		$insta_account = !empty($siteseo->social_settings['social_accounts_instagram']) ? $siteseo->social_settings['social_accounts_instagram'] : '';
		$yt_account = !empty($siteseo->social_settings['social_accounts_youtube']) ? $siteseo->social_settings['social_accounts_youtube'] : '';
		$pt_account = !empty($siteseo->social_settings['social_accounts_pinterest']) ? $siteseo->social_settings['social_accounts_pinterest'] : '';

		//description
		$site_url = get_site_url();
		$site_description = get_bloginfo('name');

		//JSON-LD data
		$json_ld = [
			'@context' => 'https://schema.org',
			'@type' => $org_type ? esc_html($org_type) : 'Organization',
			'name' => esc_html($org_name),
			'url' => esc_url($site_url),
			'logo' => esc_url($org_logo),
			'description' => esc_html($site_description),
		];

		//contact point
		if(!empty($org_contact_option) && !empty($org_contact_type) && !empty($org_number)){
			$json_ld['contactPoint'] = [
				'@type' => 'ContactPoint',
				'contactType' => esc_html($org_contact_type),
				'telephone' => esc_html($org_number),
				'contactOption' => esc_html($org_contact_option),
			];
		}

		$same_as = array_filter([esc_url($fb_account), esc_url($twitter_account), esc_url($insta_account), esc_url($yt_account), esc_url($pt_account)]);
		if(!empty($same_as)){
			$json_ld['sameAs'] = $same_as;
		}

		// Output JSON-LD script
		echo '<script type="application/ld+json" class="siteseo-schema">';
		echo json_encode($json_ld, JSON_UNESCAPED_SLASHES);
		echo '</script>';
	}

	static function fb_graph(){
		global $siteseo, $post;

		if(empty($siteseo->setting_enabled['toggle-social']) || empty($siteseo->setting_enabled['toggle-social'])){
			return;
		}
		
		$fb_page_id = !empty($siteseo->social_settings['social_facebook_link_ownership_id']) ? $siteseo->social_settings['social_facebook_link_ownership_id'] : '';
		$fb_link_owership = !empty($siteseo->social_settings['social_facebook_admin_id']) ? $siteseo->social_settings['social_facebook_admin_id'] : '';
		$og_type = get_post_meta(get_the_ID(), '_og_type', true);
		$og_url =  get_home_url();
		$og_sitename = get_bloginfo('name');
		
		$fb_img = !empty($siteseo->social_settings['social_facebook_img']) ? $siteseo->social_settings['social_facebook_img'] : '';
		
		// Check
		$post_id = isset($post) && is_object($post) ? $post->ID : '';
		$og_title =	!empty(get_post_meta($post_id, '_siteseo_social_fb_title', true)) ? get_post_meta($post_id, '_siteseo_social_fb_title', true)  : get_the_title();
		
		$og_description = !empty(get_post_meta($post_id, '_siteseo_social_fb_desc', true)) ? get_post_meta($post_id, '_siteseo_social_fb_desc',true) : get_bloginfo('description');
			
		$og_img = !empty(get_post_meta($post_id,'_siteseo_social_fb_img', true)) ? get_post_meta($post_id, '_siteseo_social_fb_img', true) : 
		$fb_img;
		
		if(!empty($og_img)){
			$og_img = filter_var($og_img, FILTER_SANITIZE_URL);
			$og_img_width = 0;
			$og_img_height = 0;

			if(filter_var($og_img, FILTER_VALIDATE_URL)){	
				$image_info = @getimagesize($og_img);

				if($image_info !== false){
					$og_img_width = $image_info[0];
					$og_img_height = $image_info[1];
				}
			}
		}

		if(!$og_type){
			$og_type = 'website'; //default wb
		}
				
		if(!empty($siteseo->social_settings['social_twitter_card'])){

			if(!empty($og_url)){
				echo '<meta property="og:url" content="'.esc_html($og_url).'" />';
			}

			if(!empty($og_sitename)){
				echo '<meta property="og:site_name" content="'.esc_html($og_sitename).'" />';
			}

			if(function_exists('get_locale')){
				echo '<meta property="og:locale" content="'.esc_html(get_locale()).'" />';
			}

			if(!empty($og_type)){
				echo '<meta property="og:type" content="'.esc_attr($og_type).'" />';
			}

			if(!empty($og_title)){
				echo '<meta property="og:title" content="'.esc_html($og_title).'" />';
			}

			if(!empty($og_description)){
				echo '<meta property="og:description" content="'.esc_html($og_description).'" />';
			}

			if(!empty($og_img)){
				echo '<meta property="og:image" content="'.esc_html($og_img).'" />';
				echo '<meta property="og:secure_url" content="'.esc_html($og_img).'" />';
			}

			if(!empty($og_img_height)){
				echo '<meta property="og:image:height" content="'.esc_attr($og_img_height).'" />';
			}

			if(!empty($og_img_width)){
				echo '<meta property="og:image:width" content="'.esc_attr($og_img_width).'" />';
			}

			if(!empty($fb_page_id)){
				echo '<meta property="fb:pages" content="'.esc_html($fb_page_id) .'" />';
			}

			if(!empty($fb_link_owership)){
				echo '<meta property="fb:admins" content="'. esc_html($fb_link_owership).'" />';
			}	
		}
		
	}

	static function twitter_card(){
		global $siteseo, $post;

		if(empty($siteseo->social_settings['social_twitter_card']) || empty($siteseo->setting_enabled['toggle-social'])){
			return;
		}

		$site_type = get_post_meta(get_the_ID(), '_og_type', true);
		$site_url =  get_home_url();
		$sitename = get_bloginfo('name');

		$post_id = isset($post) && is_object($post) ? $post->ID : '';
		//check enable by metaboxes
		$site_title = !empty(get_post_meta($post_id, '_siteseo_social_twitter_title', true)) ? get_post_meta($post_id, '_siteseo_social_twitter_title', true) : get_the_title();
		
		$site_description = !empty(get_post_meta($post_id, '_siteseo_social_twitter_desc', true)) ? get_post_meta($post_id, '_siteseo_social_twitter_desc', true) : get_bloginfo('description');
		
		$twitter_img = isset($siteseo->social_settings['social_twitter_card_img']) ? $siteseo->social_settings['social_twitter_card_img'] : '';
		$twitter_img = !empty(get_post_meta($post_id,'_siteseo_social_twitter_img', true)) ? get_post_meta($post_id,'_siteseo_social_twitter_img', true) : $twitter_img;
		
		if(!$site_type){
			$site_type = 'website'; //default wb
		}
	
		echo '<meta name="twitter:card" content="summary"/>';
		
		echo '<meta name="twitter:locale" content="'.esc_html(get_locale()).'"/>';
		
		if($site_type){
			echo '<meta name="twitter:type" content="'.esc_attr($site_type).'"/>';
		}
		
		if(!empty($site_title)){
			echo '<meta name="twitter:title"  content="'.esc_html($site_title).'"/>';
		}
		
		if(!empty($site_description)){
			echo '<meta name="twitter:description" content="'.esc_html($site_description).'"/>';
		}
		
		if(!empty($site_url)){
			echo '<meta name="twitter:url" content="'.esc_html($site_url).'"/>';
		}
		
		if(!empty($sitename)){
			echo '<meta name="twitter:sitename" content="'.esc_html($sitename).'"/>';
		}
		
		if(!empty($twitter_img)){
			echo '<meta name="twitter:image" content="'.esc_html($twitter_img).'"/>';
		}
	}
}