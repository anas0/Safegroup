<?php
/*
Plugin Name: SiteSEO - SEO Simplified
Plugin URI: http://wordpress.org/plugins/siteseo/
Description: This plugin handles On Page SEO, Content Analysis, Social Previews, Google Preview, Hyperlink Analysis, Image Analysis, Home Page Monitor, Schemas for various type of posts.
Author: Softaculous
Version: 1.1.6
Author URI: https://siteseo.io/
License: GPLv2
Text Domain: siteseo
Domain Path: /languages
*/

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

if(defined('SITESEO_VERSION')){
	return;
}

define('SITESEO_VERSION', '1.1.6');
define('SITESEO_FILE', __FILE__);
define('SITESEO_DOCS', 'https://siteseo.io/docs/');
define('SITESEO_DIR_PATH', plugin_dir_path(SITESEO_FILE));
define('SITESEO_DIR_URL', plugin_dir_url(SITESEO_FILE));
define('SITESEO_ASSETS_PATH', SITESEO_DIR_PATH . 'assets');
define('SITESEO_ASSETS_URL', SITESEO_DIR_URL . 'assets');
define('SITESEO_DEV', file_exists(SITESEO_DIR_PATH.'DEV.php'));

include_once(SITESEO_DIR_PATH . 'functions.php');

function siteseo_autoloader($class){
	if(!preg_match('/^SiteSEO\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	// Include file
	if(file_exists(SITESEO_DIR_PATH . 'main/'.strtolower($m[1]).'.php')){
		include_once(SITESEO_DIR_PATH.'main/'.strtolower($m[1]).'.php');
	}
}

spl_autoload_register('siteseo_autoloader');

register_activation_hook(SITESEO_FILE, '\SiteSEO\Install::activate');
register_deactivation_hook(SITESEO_FILE, '\SiteSEO\Install::deactivate');
register_uninstall_hook(SITESEO_FILE, '\SiteSEO\Install::uninstall');
add_action('plugins_loaded', 'siteseo_load_plugin');

function siteseo_load_plugin(){
	global $siteseo;

	if(empty($siteseo)){
		$siteseo = new StdClass();
	}
	
	// Loading all the options.
	$siteseo->setting_enabled = get_option('siteseo_toggle', []);
	$siteseo->titles_settings = get_option('siteseo_titles_option_name', []);
	$siteseo->social_settings = get_option('siteseo_social_option_name', []);
	$siteseo->advanced_settings = get_option('siteseo_advanced_option_name', []);
	$siteseo->instant_settings = get_option('siteseo_instant_indexing_option_name', []);
	$siteseo->sitemap_settings = get_option('siteseo_xml_sitemap_option_name', []);
	$siteseo->analaytics_settings = get_option('siteseo_google_analytics_option_name', []);
	
	siteseo_check_update();

	if(wp_doing_ajax()){
		\SiteSEO\Ajax::hooks();
		return;
	}
	
	if(defined('SITESEO_PRO_VERSION') && version_compare(SITESEO_PRO_VERSION, '1.1.5', '<')){ 
		if(!function_exists('is_plugin_active')){ 
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; 
		} 
	}
	
	// Image & Sitemap block
	add_action('init', '\SiteSEO\admin::register_sitmap_block');
	add_action('init','\SiteSEO\ImageSeo::init');
	
	add_action('init', '\SiteSEO\TitlesMetas::metaboxes_enable');
	// TOC 
	add_shortcode('siteseo_toc', '\SiteSEO\TableofContent::render_toc');
	add_action('init', '\SiteSEO\TableofContent::enable_toc');
	
	add_action('init', '\SiteSEO\GenerateSitemap::add_rewrite_rules', 20);
	add_action('template_redirect', '\SiteSEO\GenerateSitemap::handle_sitemap_requests', 0);
	
	// redirect
	add_action('template_redirect', '\SiteSEO\GoogleAnalytics::handle_custom_redirect');
	
	//shortcode
	add_shortcode('siteseo_html_sitemap', '\SiteSEO\GenerateSitemap::html_sitemap');

	if(!empty($siteseo->instant_settings['instant_indexing_bing_api_key'])){
		add_action('template_redirect', '\SiteSEO\InstantIndexing::bing_txt_file', 0);
	}
	
	\SiteSEO\Admin::permission();

	if(empty($siteseo->advanced_settings['appearance_universal_metabox_disable'])){
		add_action('wp_enqueue_scripts', 'siteseo_universal_assets');
		add_action('enqueue_block_editor_assets', 'siteseo_universal_assets');
	}
	
	if(!is_admin()){
		// Code that will be used in the frontend will go here.
		
		// Cookies enqueue
		add_action('wp_enqueue_scripts', '\SiteSEO\Admin::cookies_bar');

		// Titles and Metas
		add_action('wp_head', '\SiteSEO\TitlesMetas::add_nositelinkssearchbox', 1);
		add_filter('wp_title', '\SiteSEO\TitlesMetas::modify_site_title', 10, 2);
		add_filter('pre_get_document_title', '\SiteSEO\TitlesMetas::modify_site_title', 10);
		add_action('wp_head' , '\SiteSEO\TitlesMetas::add_meta_description', 1);
		add_filter('wp_robots', '\SiteSEO\TitlesMetas::advanced_metas', 999);
		add_action('wp_head', '\SiteSEO\TitlesMetas::add_rel_link_pages', 9);
		add_action('wp_head' ,'\SiteSEO\TitlesMetas::date_time_publish', 3);

		// Social
		add_action('wp_head', '\SiteSEO\SocialMetas::add_social_graph', 1);
		add_action('wp_head','\SiteSEO\SocialMetas::fb_graph', 1);
		add_action('wp_head','\SiteSEO\SocialMetas::twitter_card', 1);

		// Sitemaps
		add_action('init','\SiteSEO\GenerateSitemap::settings');

		// Image & Advanced
		add_action('wp_head','\SiteSEO\Advanced::tags');
		add_action('init','\SiteSEO\Advanced::remove_links');
		

		// Analaytics
		add_action('init','\SiteSEO\GoogleAnalytics::ga_render');

		return;
	}

	if(is_admin() && current_user_can('siteseo_manage')){
		\SiteSEO\Admin::init();
	}
}

function siteseo_check_update(){
	global $siteseo;

	$current_version = get_option('siteseo_version');	
	$version = (int) str_replace('.', '', $current_version);

	// No update required
	if($current_version == SITESEO_VERSION){
		return true;
	}
	
	if($version < 115){
		$options = $siteseo->titles_settings;
		$post_types = siteseo_post_types();

		if(!empty($post_types) && is_array($post_types)){
			$post_types = array_keys($post_types);
			foreach($post_types as $post_type){
				if(!empty($options['titles_single_titles'][$post_type])){
					$options['titles_single_titles'][$post_type]['enable'] = empty($options['titles_single_titles'][$post_type]['enable']) ? true : false;
				}
			}
		}

		$taxonomies = get_taxonomies(['show_ui' => true, 'public'  => true], 'objects', 'and');
		if(!empty($taxonomies) && is_array($taxonomies)){
			$taxonomies = array_keys($taxonomies);
			foreach($taxonomies as $taxonomy){
				$options['titles_tax_titles'][$taxonomy]['enable'] = empty($options['titles_tax_titles']['post']['enable']) ? true : false;
			}
		}

		update_option('siteseo_titles_option_name', $options);
	}


	// Is it first run ?
	if(empty($current_version)){
		\SiteSEO\Install::activate();
		return;
	}

	update_option('siteseo_version', SITESEO_VERSION);
}

