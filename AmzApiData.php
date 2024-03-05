<?php
namespace ChateGPT;

class AmzApiData
{
	CONST SECRET_KEY = 'BxjuTVD5L805Kyjh6FDyUkn4kIHJH2E88CR3uAY+';
	public function eap_api_make_product_request($asin)
	{
		$method = 'GET';
		$host = 'webservices.amazon.com';// . get_option("eap-amazon-country");
		$uri = '/onca/xml';

		$params = array();
		$params['Operation'] = 'ItemLookup';
		$params['ItemId'] = $asin;
		$params['IncludeReviewsSummary'] = false;
		$params['ResponseGroup'] = 'Medium,OfferSummary';
		$params['Service'] = 'AWSECommerceService';
		$params['AssociateTag'] = 'test-1222';// get_option("eap-amazon-tag");
		$params['AWSAccessKeyId'] = self::SECRET_KEY;// get_option("eap-amazon-api-access-key");
		$params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
		$params['Version'] = '2011-08-01';

		ksort($params);

		$canonicalizedQuery = array();
		foreach ($params as $param => $value) {
			$param = str_replace('%7E', '~', rawurlencode($param));
			$value = str_replace('%7E', '~', rawurlencode($value));
			$canonicalizedQuery[] = $param . '=' . $value;
		}
		$canonicalizedQuery = implode('&', $canonicalizedQuery);

		$stringToSign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalizedQuery;
		$signature = $this->eap_api_generate_signature($stringToSign);

		$request = 'https://' . $host . $uri . '?' . $canonicalizedQuery . '&Signature=' . $signature;

		$result = $this->eap_curl_get_content($request);
		if (!$result) {
			return false;
		}

		$result = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
		$result = json_decode(json_encode((array)$result), true);
		$result = $result['Items']['Item'];
		return array(
			'item_title' => $result['ItemAttributes']['Title'],
			'item_image' => $this->eap_imageurl_to_base64($result['LargeImage']['URL']),
			'item_manufacturer' => $result['ItemAttributes']['Manufacturer'],
			'item_price' => str_replace("EUR", "&euro;", $result['ItemAttributes']['ListPrice']['FormattedPrice']),
			'item_newprice' => str_replace("EUR", "&euro;",
				$result['OfferSummary']['LowestNewPrice']['FormattedPrice']),
			'item_features' => $result['ItemAttributes']['Feature'],
			'item_description' => $result['EditorialReviews']['EditorialReview']['Content']
		);
	}

	private function eap_api_generate_signature($stringToSign) {
		$signature = base64_encode(hash_hmac("sha256", $stringToSign, self::SECRET_KEY, true));
		$signature = str_replace('%7E', '~', rawurlencode($signature));

		return $signature;
	}
	private function eap_curl_get_content($url){
		/*$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$html = curl_exec($curl);
		curl_close($curl);*/

		$response = wp_remote_get($url);
		$html = wp_remote_retrieve_body($response);

		if(empty($html))
			return false;

		return $html;
	}
	private function eap_imageurl_to_base64($url) {
		//$image = file_get_contents($url);
		$response = wp_remote_get($url);
		$image = wp_remote_retrieve_body($response);
		return "data:image/jpg;base64," . base64_encode($image);
	}
}