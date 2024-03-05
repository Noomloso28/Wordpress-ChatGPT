<?php
namespace Amazon\Affiliate\Api;

require_once 'AmazonProductApi.php';

use Amazon\Affiliate\Api\AmazonProductApi;

class AmzProduct
{
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

		return $results;
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