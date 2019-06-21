<?php 

/**
 * Name.com PHP API Class
 * Class that handles all Name.com API Calls
 *
 * 2019
 * @author liam@hogan.re
 */

require_once(dirname(__FILE__).'/../vendor/autoload.php');

Requests::register_autoloader();

class NameDotComApi {

	private $options;
	private $username;
	private $session_token;
	public $url = 'https://api.name.com/';
	public $version = 'v4';
	public $data;
	public $header;
	public $connected;

	public function __construct($username, $api_token, $v = 0 ) {

		$this->username = $username;
		$this->session_token = $api_token;
		$this->header  = array( 'Accept' => 'application/json' );
		$this->options = array( 'auth' => array( $this->username, $this->session_token ));
		$this->url = $this->url.$this->version;

		if ( !$this->connected ) {
			$path = $this->url.'/hello/';
			$request = Requests::get($path, $this->header, $this->options);
			
			$data = json_decode($request->body, TRUE);
			
			if ( !$data['username']) {
				$this->connected = false;
				if ( $v || !$v ) echo 'Name.com API connection error. '; 
				if ( $v ) print_r( $request->body );
				return $data;
			} else {
				$this->connected = true;
				$result = $data['motd'];
				if( $v ) print_r( $data );
				$this->data = 'Name.com Connection: '.$result.".\n";
				return $result;
			}
			
			// 
			// NAME.COM API RESTful Method Connection.
			// 

		}

	}


	/**
     * Hello
     *
     * HelloFunc returns some information about the API server.
     *
     * @return array $hello
     * @author liam@hogan.re
     */
	public function HelloFunc()
	{

		$path = $this->url.'/hello/'.$hostname;
		$request = Requests::get($path, $this->header, $this->options);

		$data = json_decode($request->body, TRUE);
		return $data;
	}



	/****
	** DNS
	****/

	/**
     * Record
     *
     * Record is an individual DNS resource record.
     *
     * @return array $dnssec
     * @author liam@hogan.re
     */
	public function RecordModel( $id=0,$domain='',$host='',$fqdn='', $type='', $answer = '', $ttl = '', $priority = '')
	{
		$data = [
			"id" => $id,
			"domainName" => $domain,
			"host" => $host, // Hostname
			"fqdn" => $fqdn, // Fully Qualified Domain Name
			"type" => $type, // A/AAAA/ANAME/CNAME/MX/NS/SRV/TXT
			"answer" => $answer, // *Actual Record Data Here*
			"ttl" => $ttl,		 //  Minimum TTL of 300 / 5 min
			"priority" => $priority // Priority MX / SRV
		];
		return $data;
	}






	/****
	** DNSSEC
	****/

	/**
     * DNSSEC
     *
     * DNSSEC contains all the data required to create a 
     * DS record at the registry.
     *
     * @return array $dnssec
     * @author liam@hogan.re
     */
	public function DNSSECModel( $domain='',$keyTag='',$algorithm='',$digestType='',$digest='' )
	{
		$data = [
			"domainName" => $domain,
			"keyTag" => $keyTag,
			"algorithm" => $algorithm,
			"digestType" => $digestType,
			"digest" => $digest
		];
		return $data;
	}





	/****
	** Domains
	****/

	/**
     * Domain
     *
     * Domain lists all the data for a domain.
     *
     * @return array $domain
     * @author liam@hogan.re
     */
	public function DomainModel( $domain = '', $nameservers = [], $contacts = [], $privacyEnabled=false, $locked=false, $autorenewEnabled=false, $expireDate = '', $createDate = '', $renewalPrice = '', $registrant = '', $admin = '', $tech = '', $billing = '', $firstName = '', $lastName = '', $companyName = '', $address1 = '', $address2 = '', $city = '', $state = '', $zip = '', $country = '', $phone = '', $fax = '', $email = '' )
	{
		$data = [
			"domainName" => $domain,		// string
			"nameservers" => $nameservers,	// []string
			"contacts" => $contacts,		// *Contacts
			"privacyEnabled" => $privacyEnabled, 	 // bool
			"locked" => $locked,					 // bool
			"autorenewEnabled" => $autorenewEnabled, // bool
			"expireDate" => $expireDate,	// string
			"createDate" => $createDate,	// string
			"renewalPrice" => $renewalPrice,// float64
			"registrant" => $registrant,	// *Contact
			"admin" => $admin,				// *Contact
			"tech" => $tech,				// *Contact
			"billing" => $billing,			// *Contact
			"firstName" => $firstName,		// string
			"lastName" => $lastName,		// string
			"companyName" => $companyName,	// string
			"address1" => $address1,		// string
			"address2" => $address2,		// string
			"city" => $city,				// string
			"state" => $state,				// string
			"zip" => $zip,					// string
			"country" => $country,			// string
			"phone" => $phone,				// string
			"fax" => $fax,					// string
			"email" => $email				// string

		];
		return $data;
	}


    /**
     * Add Price
     * Append the price when we do check availibility
     *
     * @var string $tld the tld (.com,.etc)
     * @var string $price how much we add price (1.32)
     * @return bool false or true
     * @author rama@networks.co.id
     */
	public function addPrice($tld, $price)
	{
		if (!isset($this->data['price_add'][$tld])) 
		{
			$this->data['price_add'][$tld] = $price; 
			return true;
		} 
		else 
		{
			return false;
		}	
	}

    /**
     * Get Domain List
     * get all the domain in root account
     *
     * @return array $domain
     * @author rama@networks.co.id
     */
	public function getDomainList()
	{
		$request = Requests::get($this->url . '/domain/list', array('Api-Session-Token' => $this->session_token));
		$data = json_decode($request->body, TRUE);	
		
		if ( isset($data['domains']) ) return $data['domains'];
		if (!isset($data['domains']) ) {print_r($data); return false;}
	}



    /**
     * Check Domain Availibility
     * Check the domain availibility and the current price or the new appened price
     *
     * @var string $keyword
     * @return array $domains
     * @author rama@networks.co.id
     */
	public function checkDomain($keyword)
	{	
		$post = array('keyword' => $keyword, 'tlds' => array('com','org','me'), 'services' => array('availability'));
		$request = Requests::post($this->url . '/domain/check', $this->header, json_encode($post), $this->options);
		$data = json_decode($request->body, TRUE);
		return array_map(array($this, "__processPrice"), $data['domains']);
	}




	/****
	** Email Forwarding
	****/

	/**
     * Email Forwarding
     *
     * EmailForwarding contains all the information 
     * for an email forwarding entry.
     *
     * @return array $emailforwarding
     * @author liam@hogan.re
     */
	public function EmailForwardingModel( $domain, $emailBox, $emailTo )
	{
		$data = [
			"domainName" => $domain,
			"emailBox" => $emailBox,
			"emailTo" => $emailTo
		];
		return $data;
	}





	/****
	** Transfers
	****/

	/**
     * Transfer
     *
     * Transfer contains the information related to a
     * transfer of a domain name to Name.com.
     *
     * @return array $transfer
     * @author liam@hogan.re
     */
	public function TransferModel( $domain, $email, $status )
	{
		$data = [
			"domainName" => $domain,
			"email" => $email,
			"status" => $status
		];
		return $data;
	}




	/****
	** URL Forwardings
	****/

	/**
     * URL Forwarding
     *
     * URLForwarding is the model for URL forwarding entries.
     *
     * @return array $nameserver
     * @author liam@hogan.re
     */
	public function URLForwardingModel( $domain, $host, $forwardsTo, $type, $title, $meta )
	{
		$data = [
			"domainName" => $domain,
			"host" => $host,
			"forwardsTo" => $forwardsTo,
			"type" => $type,
			"title" => $title,
			"meta" => $meta
		];
		return $data;
	}





	/****
	** Vanity Nameservers
	****/

	/**
     * Vanity Nameservers
     *
     * VanityNameserver contains the hostname as well 
     * as the list of IP addresses for nameservers.
     *
     * @return array $nameserver
     * @author liam@hogan.re
     */
	public function VanityNameserversModel( $domain, $hostname, $ips = [] )
	{
		$data = [
			"domainName" => $domain,
			"hostname" => $hostname,
			"ips" => $ips
		];
		return $data;
	}


	/**
     * List Vanity Nameservers
     * get all the nameservers in root account
     *
     * @return array $nameservers
     * @author liam@hogan.re
     */
	public function ListVanityNameservers( $domain )
	{
		$path = $this->url.'/domains/'.$domain.'/vanity_nameservers';
		$request = Requests::get($path, $this->header, $this->options);
		
		$data = json_decode($request->body, TRUE);
		if ( isset($data['vanityNameservers']) ) return $data['vanityNameservers'];
		if (!isset($data['vanityNameservers']) ) {print_r($data); return false;}
	}

	/**
     * Get Vanity Nameserver
     * get nameserver IP addresses for domain.tld, ns.domain.tld
     *
     * @return array $ips
     * @author liam@hogan.re
     */
	public function GetVanityNameserver( $domain, $hostname )
	{

		$path = $this->url.'/domains/'.$domain.'/vanity_nameservers/'.$hostname;
		$request = Requests::get($path, $this->header, $this->options);

		$data = json_decode($request->body, TRUE);	
		if ( isset($data['ips']) ) return $data['ips'];
		if (!isset($data['ips']) ) {print_r($data); return false;}
	}

	/**
     * Create Vanity Nameserver
     * add a new nameserver record
     *
     * @return object $nameserver
     * @author liam@hogan.re
     */
	public function CreateVanityNameserver( $domain, $hostname, $ips )
	{
		
		$post = array(
			'hostname' => $hostname, 
			'ips' => (is_array($ips))?$ips:[$ips]
		);

		$path = $this->url . '/domains/'.$domain.'/vanity_nameservers';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		$data = json_decode($request->body, TRUE);	
		return $data;

	}

	/**
     * Update Vanity Nameserver
     * update the nameserver details
     *
     * @return object $nameserver
     * @author liam@hogan.re
     */
	public function UpdateVanityNameserver( $domain, $hostname, $ips )
	{

		$post = array( 'ips' => (is_array($ips))?$ips:[$ips] );
		$path = $this->url.'/domains/'.$domain.'/vanity_nameservers/'.$hostname;

		$request = Requests::put($path, $this->header, json_encode($post), $this->options);
		$data = json_decode($request->body, TRUE);
		return $data;

	}


	/**
     * Delete Vanity Nameserver
     * remove nameserver IPs from the domain name specified. 
     *
     * @return object null
     * @author liam@hogan.re
     */
	public function DeleteVanityNameserver( $domain, $hostname )
	{

		$path = $this->url.'/domains/'.$domain.'/vanity_nameservers/'.$hostname;
		$request = Requests::delete($path, $this->header, $this->options);
		$data = json_decode($request->body, TRUE);
		return $data;

	}



}
