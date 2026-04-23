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

class InstantIndexing{
	
	static function bing_txt_file(){
		global $wp, $siteseo;
		
		$request = home_url($wp->request);
		if(empty($request)){
			return;
		}

		$api_key = $siteseo->instant_settings['instant_indexing_bing_api_key'];
		$api_url = trailingslashit(home_url()) . $api_key . '.txt';
		
		if($request == $api_url){
			header('X-Robots-Tag: noindex');
			header('Content-Type: text/plain');
			status_header(200);

			esc_html_e($api_key);
			die();
		}
	}
	
	static function submit_urls_to_google($urls, $api_key){
		$responses = [];
		foreach($urls as $url){
			$endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish?key=' . urlencode($api_key);
			$body = wp_json_encode(['url' => $url, 'type' => 'URL_UPDATED']);

			$response = wp_remote_post($endpoint, [
				'body' => $body,
				'headers' => [
					'Content-Type' => 'application/json',
					'Accept' => 'application/json'
				],
				'method' => 'POST',
				'timeout' => 30
			]);

			$responses[] = [
				'url' => $url,
				'status_code' => wp_remote_retrieve_response_code($response),
				'body' => wp_remote_retrieve_body($response),
				'error' => is_wp_error($response) ? $response->get_error_message() : null
			];
		}

		return $responses;
	}
	
	static function get_google_auth_token(){
		global $siteseo;
		
		$endpoint = 'https://oauth2.googleapis.com/token';
		$scope = 'https://www.googleapis.com/auth/indexing';
		
		$google_api_data = isset($siteseo->instant_settings['instant_indexing_google_api_key']) ? $siteseo->instant_settings['instant_indexing_google_api_key'] : '';
		
		if(empty($google_api_data)){
			return false;
		}

		$google_api_data = json_decode($google_api_data, true);
		if(empty($google_api_data)){
			return;
		}

		$header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
		$claim_set = [
			'iss' => $google_api_data['client_email'],
			'scope' => $scope,
			'aud' => 'https://oauth2.googleapis.com/token',
			'exp' => time() + 3600,
			'iat' => time(),
		];
		$payload = base64_encode(json_encode($claim_set));

		openssl_sign("$header.$payload", $signature, $google_api_data['private_key'], 'sha256');
		$jwtAssertion = "$header.$payload." . base64_encode($signature);

		$response = wp_remote_post($endpoint, [
			'body' => [
				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion' => $jwtAssertion
			]
		]);

		if (is_wp_error($response)) return false;
		$body = json_decode(wp_remote_retrieve_body($response), true);
		return $body['access_token'] ?? false;

	}

    static function submit_urls_to_bing($urls, $api_key){
        $host = parse_url(home_url(), PHP_URL_HOST);
		$key_location = trailingslashit(home_url()) . $api_key . '.txt';

        $endpoint = 'https://api.indexnow.org/indexnow/';
        $body = wp_json_encode([
            'host' => $host, 
            'key' => $api_key,
			'keyLocation' => $key_location,
            'urlList' => $urls
        ]);

        $response = wp_remote_post($endpoint, [
            'body' => $body,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'timeout' => 30
        ]);

        return [
            'status_code' => wp_remote_retrieve_response_code($response),
            'body' => wp_remote_retrieve_body($response)
        ];
    }
	
}