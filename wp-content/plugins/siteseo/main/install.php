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

class Install{
	
	static function activate(){
		self::default_settings();
		update_option('siteseo_version', SITESEO_VERSION);
	}

	static function deactivate(){
		delete_option('siteseo_version');
	}

	static function uninstall(){
		delete_option('siteseo_version');
		delete_option('siteseo_toggle');
		delete_option('siteseo_titles_option_name');
		delete_option('siteseo_social_option_name');
		delete_option('siteseo_advanced_option_name');
		delete_option('siteseo_instant_indexing_option_name');
		delete_option('siteseo_xml_sitemap_option_name');
		delete_option('siteseo_google_analytics_option_name');
		delete_option('siteseo_dismiss_intro');
	}

	static function default_settings(){
		global $siteseo;
		
		$titles_metas = get_option('siteseo_titles_option_name', []);
		
		$toggle_settings = [
			'toggle-titles' => true,
			'toggle-xml-sitemap' => true,
			'toggle-instant-indexing' => true,
			'toggle-advanced' => true,
			'toggle-social' => true,
			'toggle-google-analytics' => true
		];

		// Titles and Metas
		$titles_metas['titles_sep'] = '-';
		$titles_metas['titles_home_site_title'] = !isset($titles_metas['titles_home_site_title']) ? '%%sitetitle%%' : $titles_metas['titles_home_site_title'];
		$titles_metas['titles_home_site_desc'] = !isset($titles_metas['titles_home_site_desc']) ? '%%tagline%%' : $titles_metas['titles_home_site_desc'];
		$titles_metas['titles_single_titles']['post']['enable'] = true;
		$titles_metas['titles_single_titles']['post']['title'] = !isset($titles_metas['titles_single_titles']['post']['title']) ? '%%post_title%% ' : $titles_metas['titles_single_titles']['post']['title'];
		$titles_metas['titles_single_titles']['post']['description'] = !isset($titles_metas['titles_single_titles']['post']['description']) ? '%%post_excerpt%%' : $titles_metas['titles_single_titles']['post']['description'];
		$titles_metas['titles_single_titles']['page']['enable'] = true;
		$titles_metas['titles_single_titles']['page']['title'] = !isset($titles_metas['titles_single_titles']['page']['title']) ? '%%post_title%% ' : $titles_metas['titles_single_titles']['page']['title'];
		$titles_metas['titles_single_titles']['page']['description'] = !isset($titles_metas['titles_single_titles']['page']['description']) ? '%%post_excerpt%%' : $titles_metas['titles_single_titles']['page']['description'];
		$titles_metas['titles_archives_author_title'] = !isset($titles_metas['titles_archives_author_title']) ? '%%post_author%%' : $titles_metas['titles_archives_author_title'];
		$titles_metas['titles_archives_author_noindex'] = !isset($titles_metas['titles_archives_author_noindex']) ?? '';
		$titles_metas['titles_archives_date_title'] = !isset($titles_metas['titles_archives_date_title']) ? '%%archive_date%%' : '';
		$titles_metas['titles_archives_date_noindex'] = !isset($titles_metas['titles_archives_date_noindex']) ?? '';
		$titles_metas['titles_archives_search_title_noindex'] = !isset($titles_metas['titles_archives_search_title_noindex']) ?? '';
		$titles_metas['titles_nositelinkssearchbox'] = !isset($titles_metas['titles_nositelinkssearchbox']) ?? '';
		$titles_metas['titles_archives_search_title'] = !isset($titles_metas['titles_archives_search_title']) ? '%%search_keywords%%' : '';
		$titles_metas['titles_archives_404_title'] = !isset($titles_metas['titles_archives_404_title']) ? '404 - Page not found' : $titles_metas['titles_archives_404_title'];
		$titles_metas['titles_tax_titles']['category']['enable'] = true;
		$titles_metas['titles_tax_titles']['post_tag']['enable'] = true;

		// Social	
		$social_settings['social_twitter_card'] = true;
		$social_settings['social_facebook_og'] = true;

		// Sitemap
		$sitemap_settings['xml_sitemap_general_enable'] = true;
		$sitemap_settings['xml_sitemap_post_types_list']['post']['include'] = true;
		$sitemap_settings['xml_sitemap_post_types_list']['page']['include'] = true;
		$sitemap_settings['xml_sitemap_taxonomies_list']['category']['include'] = true;
		$sitemap_settings['xml_sitemap_taxonomies_list']['post_tag']['include'] = true;

		// Advanced
		$advanced_settings['advanced_attachments'] = true;
		$advanced_settings['appearance_universal_metabox'] = true;

		update_option('siteseo_toggle', $toggle_settings);
		update_option('siteseo_titles_option_name', $titles_metas);
		update_option('siteseo_social_option_name', $social_settings);
		update_option('siteseo_xml_sitemap_option_name', $sitemap_settings);
		update_option('siteseo_advanced_option_name', $advanced_settings);

	}

}