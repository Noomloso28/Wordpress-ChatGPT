<?php
namespace ChateGPT;

use Amazon\Affiliate\Api\AmazonProductApi;

class ApiData
{
	private $wpdb;
	CONST LIMIT = 5;

	public function __construct($wpdb)
	{
		$this->wpdb = $wpdb;
	}

	public function getLongDescription($postTitle = '', $keyword = '')
	{
		/** loop for get content by questions */
		$questions = $this->getQuestions($postTitle);
		$content = '';
		if (count($questions) > 0){
			foreach ($questions as $key => $question){
				$apiContent = $this->getOpenAIcontent($question);
				sleep(5);
				$subject = 'seo title about : '.$postTitle;
				$title = $this->slaveGetData($subject);
				sleep(5);
				$image = $this->getImageByTitle($question);
				sleep(5);

				if ($apiContent){
					$content .= "<h2>".strip_tags($title)."</h2>";
					$content .= "<div class='website-content'>";
					$content .= $apiContent;
					$content .= "<div class='image-side'>".$image."</div>";
					$content .= "</div>";
					$content .= $this->getAdvertisement();
				}
			}
		}
		return $content;
	}
	public function getAdvertisement()
	{
		return '<div>Banner ads</div>';
	}

	public function getSeoTitle()
	{
		$config = $this->getConfigs();

		$keywords = array_filter( explode (",", $config['Keywords']) );
//		$product = $keywords[array_rand($keywords)];

		/*** check index condition */
		$index = (int) $config['KeywordsIndex'] + 1;
		if($index > (int) array_key_last ( $keywords )){
			$index = 0;
		}
		/*** get product by key */
		$product = $keywords[$index];

		$prompt = 'seo title about : '.$product;
		return [
			'title' => $this->getOpenAIcontent($prompt) ?? null,
			'keyword' => $product,
			'index' => $index
		];
	}
	public function getDetailByText($title = '')
	{
		if ($title){
			$prompt = "Write a blog title on the significance of $title to society";
			$postTitle = $this->getOpenAIcontent($prompt) ?? $this->slaveGetData($prompt);
//			return [
//				'title' => $postTitle ?? null,
//				'keyword' => $title,
//				'index' => 0
//			];
			return $postTitle;
		}
		return [];
	}

	public function getDescriptions($text = '')
	{
		$prompt = "Write a blog post on the significance of $text to society";

		$apiContent = $this->getOpenAIcontent($prompt) ?? $this->slaveGetData($prompt);
		$content = null;
		if ($apiContent){
//			$image = $this->getImageByTitle($text);
			$image = $this->displayImage($text);

			$content .= "<div class='website-content'>";
			$content .= $apiContent;
			if($image){
				$content .= "<div class='image-side'>".$image."</div>";
			}
			$content .= "</div>";
			$content .= $this->getAdvertisement();
		}

		return $content;
	}
	private function displayImage($text='')
	{
		$url = $this->getImageApi($text);
		if($url){
			return "<img src=\"$url\" alt=\"$text\" class=\"img-resposive\" />";
		}
		return null;
	}

	public function getKeywords()
	{
		$config = $this->getConfigs();

		$keywords = array_filter( explode (",", $config['Keywords']) );
		$keyword = $keywords[array_rand($keywords)];

		return $keyword ?? null;
	}
	public function getQuestions($title = '') : array
	{
		$result = [];
		$config = $this->getConfigs();
		$questions = array_filter( explode (",", $config['Questions']) );

		/** replace {{var}} */
		foreach ($questions as $key => $question){
			$result[] = str_replace("{{var}}",$title, trim($question) );
		}

		return $result ?? [];
	}
	public function getImageByTitle($title = '')
	{
		$prompt = "INPUT = {focus} <img src=\"https://image.pollinations.ai/prompt/{description}\" alt=\"{description}\" />\n {focuseDetailed},%20{adjective1},%20{adjective2},%20{visualStyle1},%20{visualStyle2},%20{visualStyle3},%20{artistreference} INPUT =[$title]";
		return urldecode($this->slaveGetData($prompt));
	}

	private function getOpenAIcontent($prompt = '')
	{
			$config = $this->getConfigs();

			$maxTokens = (int) $config['MaxTokens'];
			$apiKey = $config['ApiToken'];

			$header = array(
				'Authorization: Bearer '.$apiKey,
				'Content-type: application/json; charset=utf-8',
			);
			$params = json_encode(array(
				'prompt'		=> $prompt,
				'model'			=> 'text-davinci-003',
				"temperature" => 0.5,
				"max_tokens" => $maxTokens,
				"top_p" => 1,
				"frequency_penalty" => 0,
				"presence_penalty" => 0,
			));
			$curl = curl_init('https://api.openai.com/v1/completions');
			$options = array(
				CURLOPT_POST => true,
				CURLOPT_HTTPHEADER =>$header,
				CURLOPT_POSTFIELDS => $params,
				CURLOPT_RETURNTRANSFER => true,
			);
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
			/** wait */
			sleep(8);

			if(200 == $httpcode){
				$json_array = json_decode($response, true);
				$choices = $json_array['choices'];
				return $choices[0]["text"];
			}

			return null;

	}
	private function slaveGetData($prompt = '')
	{
		$apiKey = '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';
		$config = $this->getConfigs();

		$maxTokens = (int) $config['MaxTokens'];
//		$apiKey = $config['ApiToken'];

		$header = array(
			'Authorization: Bearer '.$apiKey,
			'Content-type: application/json; charset=utf-8',
		);
		$params = json_encode(array(
			'prompt'		=> $prompt,
			'model'			=> 'text-davinci-003',
			"temperature" => 0.5,
			"max_tokens" => $maxTokens,
			"top_p" => 1,
			"frequency_penalty" => 0,
			"presence_penalty" => 0,
		));
		$curl = curl_init('https://api.openai.com/v1/completions');
		$options = array(
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER =>$header,
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_RETURNTRANSFER => true,
		);
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
		/** wait */
		sleep(8);

		if(200 == $httpcode){
			$json_array = json_decode($response, true);
			$choices = $json_array['choices'];
			return $choices[0]["text"];
		}

		return null;
	}
	public function getImageApi($prompt = '')
	{
//		$prompt = 'healthy fat loss';
		$apiKey = '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';

//        return Cache::remember(self::PREFIX . '_image', 60 * 60 * 24, function ($prompt) {
		try {
//			$open_ai_response = Http::withHeaders([
//				'Content-Type' => 'application/json',
//				'Authorization' => "Bearer " . self::API_KEY
//			])->post("https://api.openai.com/v1/images/generations", [
//				'prompt' => $prompt,
//				'model' => 'image-alpha-001',
//				'n' => 1,
//				'size' => '1024x1024',
//			])->json();
			$header = array(
				'Authorization: Bearer '.$apiKey,
				'Content-type: application/json; charset=utf-8',
			);
			$params = json_encode(array(
				'prompt'		=> $prompt,
				'model' => 'image-alpha-001',
				'n' => 1,
				'size' => '1024x1024',

			));
			$curl = curl_init('https://api.openai.com/v1/images/generations');
			$options = array(
				CURLOPT_POST => true,
				CURLOPT_HTTPHEADER =>$header,
				CURLOPT_POSTFIELDS => $params,
				CURLOPT_RETURNTRANSFER => true,
			);
			curl_setopt_array($curl, $options);
			$open_ai_response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
			if($httpcode == 200){
				return json_decode($open_ai_response)->data[0]->url;
			}

		} catch (Throwable $th) {
			return $th;
		}

		return null;
	}

	public function getConfigs() : array
	{
		$tablename = $this->wpdb->prefix.'chatgpt_content_writer';
		$sql = "SELECT * FROM $tablename";
		$results = $this->wpdb->get_results($sql);

		return [
			'ID' => $results[0]->id,
			'ApiToken' => $results[0]->api_token,
			'Temperature' => $results[0]->temperature,
			'MaxTokens' => $results[0]->max_tokens,
			'Language' => $results[0]->language,
			'Keywords' => $results[0]->keywords,
			'Questions' => $results[0]->questions,
			'Template' => $results[0]->template,
			'ProductTemplate' => $results[0]->product_template,
			'KeywordsIndex' => $results[0]->keywords_index,
		];
	}
	public function setKeywordIndex(int $index)
	{
		$config = $this->getConfigs();
		$tablename = $this->wpdb->prefix . 'chatgpt_content_writer';


		/** set index  */

		$this->wpdb->update($tablename, array(
			'keywords_index' => $index
		),
			array(
				'id' => $config['ID'],
			)
		);
	}
	########## test ###################
	public function getProduct()
	{
		$asin = sanitize_text_field( 'B00N3HSVXK' );

		$locale       = 'com'; //get_option( 'ams_amazon_country' );
		$regions      = $this->ams_get_amazon_regions();
		$marketplace  = 'www.amazon.com'; //get_option( 'ams_amazon_country' );
		$service_name = 'ProductAdvertisingAPI';
		$region       = $regions[ $locale ]['RegionCode'];
		$access_key   = 'AKIAIRCJSPGKRFHIF7LA'; //get_option( 'ams_access_key_id' );
		$secret_key   = 'AKIAIRCJSPGKRFHIF7LA'; //get_option( 'ams_secret_access_key' );

		$payload_arr = array();
		$payload_arr['ItemIds']     = array( $asin );
		$payload_arr['Resources']   = array( "Images.Primary.Small", "Images.Primary.Medium", "Images.Primary.Large", "Images.Variants.Small", "Images.Variants.Medium", "Images.Variants.Large", "ItemInfo.ByLineInfo", "ItemInfo.ContentInfo", "ItemInfo.ContentRating", "ItemInfo.Classifications", "ItemInfo.ExternalIds", "ItemInfo.Features", "ItemInfo.ManufactureInfo", "ItemInfo.ProductInfo", "ItemInfo.TechnicalInfo", "ItemInfo.Title", "ItemInfo.TradeInInfo", "Offers.Listings.Availability.MaxOrderQuantity", "Offers.Listings.Availability.Message", "Offers.Listings.Availability.MinOrderQuantity", "Offers.Listings.Availability.Type", "Offers.Listings.Condition", "Offers.Listings.Condition.ConditionNote", "Offers.Listings.Condition.SubCondition", "Offers.Listings.DeliveryInfo.IsAmazonFulfilled", "Offers.Listings.DeliveryInfo.IsFreeShippingEligible", "Offers.Listings.DeliveryInfo.IsPrimeEligible", "Offers.Listings.DeliveryInfo.ShippingCharges", "Offers.Listings.IsBuyBoxWinner", "Offers.Listings.LoyaltyPoints.Points", "Offers.Listings.MerchantInfo", "Offers.Listings.Price", "Offers.Listings.ProgramEligibility.IsPrimeExclusive", "Offers.Listings.ProgramEligibility.IsPrimePantry", "Offers.Listings.Promotions", "Offers.Listings.SavingBasis", "Offers.Summaries.HighestPrice", "Offers.Summaries.LowestPrice", "Offers.Summaries.OfferCount" );
		$payload_arr['PartnerTag']  = 'test-12314';//get_option( 'ams_associate_tag' );
		$payload_arr['PartnerType'] = 'Associates';
		$payload_arr['Marketplace'] = $marketplace;
		$payload_arr['Operation']   = 'GetItems';
		$payload                    = wp_json_encode( $payload_arr );
		$host                       = $regions[ $locale ]['Host'];
		$uri_path                   = "/paapi5/getitems";
		$api                        = new AmazonProductApi ( $access_key, $secret_key, $region, $service_name, $uri_path, $payload, $host, 'GetItems' );

		$response = $api->do_request();
		$results  = $response->ItemsResult->Items;
	}
	public function ams_get_amazon_regions() {
		$regions = array(
			'com.au' => array( 'RegionName' => 'Australia', 'Host' => 'webservices.amazon.com.au', 'RegionCode' => 'us-west-2' ),
			'com.br' => array( 'RegionName' => 'Brazil', 'Host' => 'webservices.amazon.com.br', 'RegionCode' => 'us-east-1' ),
			'ca'     => array( 'RegionName' => 'Canada', 'Host' => 'webservices.amazon.ca', 'RegionCode' => 'us-east-1' ),
			'cn'     => array( 'RegionName' => 'China', 'Host' => 'webservices.amazon.cn', 'RegionCode' => 'us-west-2' ),
			'fr'     => array( 'RegionName' => 'France', 'Host' => 'webservices.amazon.fr', 'RegionCode' => 'eu-west-1' ),
			'de'     => array( 'RegionName' => 'Germany', 'Host' => 'webservices.amazon.de', 'RegionCode' => 'eu-west-1' ),
			'in'     => array( 'RegionName' => 'India', 'Host' => 'webservices.amazon.in', 'RegionCode' => 'eu-west-1' ),
			'it'     => array( 'RegionName' => 'Italy', 'Host' => 'webservices.amazon.it', 'RegionCode' => 'eu-west-1' ),
			'jp'     => array( 'RegionName' => 'Japan', 'Host' => 'webservices.amazon.co.jp', 'RegionCode' => 'us-west-2' ),
			'mx'     => array( 'RegionName' => 'Mexico', 'Host' => 'webservices.amazon.com.mx', 'RegionCode' => 'us-east-1' ),
			'nl'     => array( 'RegionName' => 'Netherlands', 'Host' => 'webservices.amazon.nl', 'RegionCode' => 'eu-west-1' ),
			'sa'     => array( 'RegionName' => 'Saudi Arabia', 'Host' => 'webservices.amazon.sa', 'RegionCode' => 'eu-west-1' ),
			'sg'     => array( 'RegionName' => 'Singapore', 'Host' => 'webservices.amazon.sg', 'RegionCode' => 'us-west-2' ),
			'es'     => array( 'RegionName' => 'Spain', 'Host' => 'webservices.amazon.es', 'RegionCode' => 'eu-west-1' ),
			'com.tr' => array( 'RegionName' => 'Turkey', 'Host' => 'webservices.amazon.com.tr', 'RegionCode' => 'eu-west-1' ),
			'ae'     => array( 'RegionName' => 'United Arab Emirates', 'Host' => 'webservices.amazon.ae', 'RegionCode' => 'eu-west-1' ),
			'co.uk'  => array( 'RegionName' => 'United Kingdom', 'Host' => 'webservices.amazon.co.uk', 'RegionCode' => 'eu-west-1' ),
			'com'    => array( 'RegionName' => 'United States', 'Host' => 'webservices.amazon.com', 'RegionCode' => 'us-east-1' )
		);

		return  $regions;
	}

}