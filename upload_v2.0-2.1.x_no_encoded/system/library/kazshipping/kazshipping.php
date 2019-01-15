<?php

define('ORIGINS_ALL_REGIONS_URL', 'https://api.exline.systems/public/v1/regions/origins?country=');
define('DESTINATIONS_ALL_REGIONS_URL', 'https://api.exline.systems/public/v1/regions/destinations?country=');
define('DESTINATIONS_URL', 'https://api.exline.systems/public/v1/regions/destination?title=');
define('CALCULATIONS_URL', 'https://api.exline.systems/public/v1/calculate?origin_id=');
define('DEFAULT_ISO', 'KZ');

class Exline {

	public function connect($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		$out = curl_exec($curl);
		//  $errno = curl_errno($curl);
		curl_close($curl);
		return json_decode($out, true);
	}
}

class HelperInfo {

    public $HelperType;

}

class MailInfo {

    public $Product;
    public $MailCat;
    public $SendMethod;
    public $Weight;
    public $From;
    public $To;
    public $SpecMarks;
    public $InCity = '';
    public $ExpressDlv ='';
    public $Size='';
    public $DeclaredValue='';
	public $Client = '';

}

class KazpostWebClient extends SoapClient {

    public function __construct($wsdl = 'http://rates.kazpost.kz/postratesprod/postratesws.wsdl', $options = array(
        'connection_timeout' => 60,
        'cache_wsdl' => WSDL_CACHE_MEMORY,
        'trace' => 1,
        'soap_version' => 'SOAP 1.2',
        'encoding' => 'UTF-8',
        'exceptions' => true,
        'location' => 'http://rates.kazpost.kz:80/postratesprod/endpoints')) {

        parent::__construct($wsdl, $options);
    }
}

class GetPostRateInfo {

    public $SndrCtg;
    public $Contract='1224/АК';
    public $Product;
    public $MailCat;
    public $SendMethod;
    public $Weight;
    public $Dimension='S';
    public $Value = '';
    public $From;
    public $To;
    public $ToCountry='KZ';
	public $PostMark = '';

}

class KazpostWebClient2 extends SoapClient {

    public function __construct($wsdl = 'http://rates.kazpost.kz/postratesprodv2/postratesws.wsdl', $options = array(
        'connection_timeout' => 60,
        'cache_wsdl' => WSDL_CACHE_MEMORY,
        'trace' => 1,
        'soap_version' => 'SOAP 1.2',
        'encoding' => 'UTF-8',
        'exceptions' => true,
        'location' => 'http://rates.kazpost.kz:80/postratesprodv2/endpoints')) {

        parent::__construct($wsdl, $options);
    }
}