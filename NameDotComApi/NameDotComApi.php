<?php 

/**
 * Name.com PHP API Class
 * Class that handles all Name.com API Calls
 *
 * @author liam@hogan.re
 * @author rama@networks.co.id
 */

require_once(dirname(__FILE__).'/../vendor/autoload.php');
Requests::register_autoloader();

/***********************
* API INDEX
************************
* -> NameDotComApi Class
* -- __construct
* -- Hello
* -- DNS
* -- DNSSECs
* -- Domains
* -- EmailForwardings
* -- Transfers
* -- URLForwardings
* -- VanityNameservers
************************/

class NameDotComApi {

	private $options;
	private $username;
	private $session_token;
	public $url = 'https://api.name.com/';
	public $version = 'v4';
	public $data;
	public $header;
	public $connected;

	/* __construct */

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
		}
	}

	/* Hello */

	/* 
		- Hello
		-- HelloFunc
	*/

	/**
     * HelloFunc
     * HelloFunc returns some information about the API server.
     *
     * @return array $hello
     * @author liam@hogan.re
     */
	public function HelloFunc()
	{
		$path = $this->url.'/hello/';
		$request = Requests::get($path, $this->header, $this->options);
		$data = json_decode($request->body, TRUE);
		return $data;
	}

	/* DNS */

	/* 
		- DNS
		-- Record Model
		-- ListRecords
		-- GetRecord
		-- CreateRecord
		-- UpdateRecord
		-- DeleteRecord
	*/

	/**
     * Record
     * Record is an individual DNS resource record.
     *
     * @return array $record
     * @author liam@hogan.re
     */
	public function Record( $id=0,$domain='',$host='',$fqdn='', $type='', $answer = '', $ttl = '', $priority = '')
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

	/**
	* List Records 
	* returns all records for a zone.
	*
	* @param string $domainName		
	* DomainName is the zone the record exists in
	* @param int32 $perPage 		
	* Per Page is the number of records to return per request. Per Page defaults to 1,000.
	* @param int32 $page 			
	* Page is which page to return
	* * * 
	* @author liam@hogan.re
	*/
	public function ListRecords($domainName, $perPage, $page) {
	{
		$post = '?domainName='.$domainName.'&perPage='.$perPage.'&page='.$page;
		$request = Requests::get($this->url.'/domains/'.$domainName.'/records'.$post, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* Get Domain Record
	* get all the records for a domain
	*
	* @param string $domainName		
	* DomainName is the zone the record exists in
	* @param int32 $id 				
	* ID is the server-assigned unique identifier for this record
	* * * 
	* @author liam@hogan.re
	*/
	public function GetRecord($domainName, $id) {
	{
		$request = Requests::get($this->url.'/domains/'.$domainName.'/records/'.$id, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* Create Record
	* creates a new record in the zone.
	*
	* @param int32 	$id 			
	* Unique record id. Value is ignored on Create, and must match the URI on Update.
	* @param string $domainName		
	* DomainName is the zone that the record belongs to.
	* @param string $host 			
	* Host is the hostname relative to the zone: e.g. for a record for blog.example.org, domain would be "example.org" and host would be "blog". An apex record would be specified by either an empty host "" or "@". A SRV record would be specified by "_{service}._{protocal}.{host}": e.g. "_sip._tcp.phone" for _sip._tcp.phone.example.org.
	* @param string $fqdn 			
	* FQDN is the Fully Qualified Domain Name. It is the combination of the host and the domain name. It always ends in a ".". FQDN is ignored in CreateRecord, specify via the Host field instead.
	* @param string $type 			
	* Type is one of the following: A, AAAA, ANAME, CNAME, MX, NS, SRV, or TXT.
	* @param string $answer 		
	* Answer is either the IP address for A or AAAA records; the target for ANAME, CNAME, MX, or NS records; the text for TXT records. For SRV records, answer has the following format: "{weight} {port} {target}" e.g. "1 5061 sip.example.org".
	* @param int32 $ttl 			
	* TTL is the time this record can be cached for in seconds. Name.com allows a minimum TTL of 300, or 5 minutes.
	* @param int32 $priority 		
	* Priority is only required for MX and SRV records, it is ignored for all others.
	* * * 
	* @author liam@hogan.re
	*/
	public function CreateRecord($domainName, $id, $host, $fqdn, $type, $answer, $ttl, $priority) {
	{
		$post = array(
			'domainName' => $domainName, 
			'id' => $id,
			'host' => $host,
			'fqdn' => $fqdn,
			'type' => $type,
			'answer' => $answer,
			'$ttl' => $ttl,
			'priority' => $priority
		);
		$request = Requests::post($this->url.'/domains/'.$domainName.'/records', $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* Update Record
	* replaces the record with the new record that is passed.
	*
	* @param int32 $id 			
	* Unique record id. Value is ignored on Create, and must match the URI on Update.
	* @param string $domainName	
	* DomainName is the zone the record exists in
	* @param string $host	
	* Host is the hostname relative to the zone: e.g. for a record for blog.example.org, domain would be "example.org" and host would be "blog". An apex record would be specified by either an empty host "" or "@". A SRV record would be specified by "_{service}._{protocal}.{host}": e.g. "_sip._tcp.phone" for _sip._tcp.phone.example.org.
	* @param string $fqdn	
	* FQDN is the Fully Qualified Domain Name. It is the combination of the host and the domain name. It always ends in a ".". FQDN is ignored in CreateRecord, specify via the Host field instead.
	* @param string $type	
	* Type is one of the following: A, AAAA, ANAME, CNAME, MX, NS, SRV, or TXT.
	* @param string $answer	Answer is either the IP address for A or AAAA records; the target for ANAME, CNAME, MX, or NS records; the text for TXT records. For SRV records, answer has the following format: "{weight} {port} {target}" e.g. "1 5061 sip.example.org".
	* @param string $ttl	
	* TTL is the time this record can be cached for in seconds. Name.com allows a minimum TTL of 300, or 5 minutes.
	* @param string $priority	
	* TTL is the time this record can be cached for in seconds. Name.com allows a minimum TTL of 300, or 5 minutes.
	* * * 
	* @author liam@hogan.re
	*/
	public function UpdateRecord($id, $domainName, $host, $fqdn, $type, $answer, $ttl, $priority) {
	{
		$post = array(
			'domainName' => $domainName, 
			'id' => $id,
			'host' => $host,
			'fqdn' => $fqdn,
			'type' => $type,
			'answer' => $answer,
			'$ttl' => $ttl,
			'priority' => $priority
		);
		$request = Requests::put($this->url.'/domains/'.$domainName.'/records/'.$id, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* Delete Record
	* deletes a record from the zone.
	*
	* @param string $domainName	
	* DomainName is the zone that the record to be deleted exists in.
	* @param int32 $id 
	* ID is the server-assigned unique identifier for the Record to be deleted. If the Record with that ID does not exist in the specified Domain, an error is returned.
	* * * 
	* @author liam@hogan.re
	*/
	public function DeleteRecord($domainName, $id) {
	{
		$post = array(
			'domainName' => $domainName, 
			'id' => $id,
			'host' => $host,
			'fqdn' => $fqdn,
			'type' => $type,
			'answer' => $answer,
			'$ttl' => $ttl,
			'priority' => $priority
		);
		$request = Requests::delete($this->url.'/domains/'.$domainName.'/records/'.$id, $this->header $this->options);
		return json_decode($request->body, TRUE);
	}

	/* DNSSEC */

	/* 
		- DNSSECs
		-- Dnssec Model
		-- ListDNSSECs
		-- GetDNSSEC
		-- CreateDNSSEC
		-- DeleteDNSSEC
	*/

	/**
     * DNSSEC
     * DNSSEC contains all the data required to create a DS record at the registry.
     *
     * @return array $record
     * * * 
     * @author liam@hogan.re
     */
	public function DNSSEC($domainName='', $keyTag='', $algorithm='', $digestType='', $digest='')
	{
		$data = [
			"domainName" => $domainName,
			"keyTag" => $keyTag,
			"algorithm" => $algorithm,
			"digestType" => $digestType,
			"digest" => $digest
		];
		return $data;
	}

	/**
     * ListDNSSECs
     * ListDNSSECs lists all of the DNSSEC keys registered with the registry.
     *
	 * @param string $domainName	
	 * DomainName is the domain name to list keys for.
     * * * 
     * @author liam@hogan.re
     */
	public function ListDNSSECs( $domainName )
	{
		$path = $this->url.'/domains/'.$domainName.'/dnssec';
		$request = Requests::get($path, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
     * GetDNSSEC
     * GetDNSSEC retrieves the details for a key registered with the registry.
     *
	 * @param string $domainName	
	 * DomainName is the domain name to list keys for.
	 * @param string $digest	
	 * Digest is the digest for the DNSKEY RR to retrieve.
     * * * 
     * @author liam@hogan.re
     */
	public function GetDNSSEC( $domainName, $digest )
	{
		$path = $this->url.'/domains/'.$domainName.'/dnssec/'.$digest;
		$request = Requests::get($path, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
     * CreateDNSSEC
     * CreateDNSSEC registers a DNSSEC key with the registry.
     *
	 * @param string $domainName	
	 * DomainName is the domain name.
	 * @param int32 $keyTag	
	 * KeyTag contains the key tag value of the DNSKEY RR that validates this signature. The algorithm to generate it is here: https://tools.ietf.org/html/rfc4034#appendix-B
	 * @param int32 $algorithm
	 * Algorithm is an integer identifying the algorithm used for signing. Valid values can be found here: https://www.iana.org/assignments/dns-sec-alg-numbers/dns-sec-alg-numbers.xhtml
	 * @param int32 $digestType
	 * DigestType is an integer identifying the algorithm used to create the digest. Valid values can be found here: https://www.iana.org/assignments/ds-rr-types/ds-rr-types.xhtml
	 * @param string $digest
	 * Digest is a digest of the DNSKEY RR that is registered with the registry.
     * * * 
     * @author liam@hogan.re
     */
	public function CreateDNSSEC( $domainName, $keyTag, $algorithm, $digestType, $digest )
	{
		$path = $this->url.'/domains/'.$domainName.'/dnssec';
		$post = array(
			'domainName' => $domainName, 
			'id' => $id,
			'host' => $host,
			'fqdn' => $fqdn,
			'type' => $type,
			'answer' => $answer,
			'$ttl' => $ttl,
			'priority' => $priority
		);
		$request = Requests::post($this->url.'/domains/'.$domainName.'/dnssec', $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
     * DeleteDNSSEC
     * DeleteDNSSEC removes a DNSSEC key from the registry.
     *
	 * @param string $domainName	
	 * DomainName is the domain name the key is registered for.
	 * @param string $digest	
	 * Digest is the digest for the DNSKEY RR to remove from the registry.
     * * * 
     * @author liam@hogan.re
     */
	public function DeleteDNSSEC( $domainName, $digest )
	{
		$path = $this->url.'/domains/'.$domainName.'/dnssec';
		$post = array(
			'domainName' => $domainName, 
			'id' => $id,
			'host' => $host,
			'fqdn' => $fqdn,
			'type' => $type,
			'answer' => $answer,
			'$ttl' => $ttl,
			'priority' => $priority
		);
		$request = Requests::delete($this->url.'/domains/'.$domainName.'/dnssec/'.$digest, $this->header $this->options);
		return json_decode($request->body, TRUE);
	}

	/* Domains */

	/* 
		- Domains
		 -- Domain Model
		 -- Search Result Model
		 -- ListDomains
		 -- GetDomain
		 -- CreateDomain
		 -- EnableWhoisPrivacy
		 -- DisableWhoisPrivacy
		 -- EnableAutorenew
		 -- DisableAutorenew
		 -- RenewDomain
		 -- GetAuthCodeForDomain
		 -- PurchasePrivacy
		 -- SetNameservers
		 -- SetContacts
		 -- LockDomain
		 -- UnlockDomain
		 -- CheckAvailability
		 -- Search
		 -- SearchStream
	*/

	/**
     * Domain Model
     *
     * Domain lists all the data for a domain.
     *
     * @return array $domain
     * @author liam@hogan.re
     */
	public function Domain( $domain = '', $nameservers = [], $contacts = [], $privacyEnabled=false, $locked=false, $autorenewEnabled=false, $expireDate = '', $createDate = '', $renewalPrice = '', $registrant = '', $admin = '', $tech = '', $billing = '', $firstName = '', $lastName = '', $companyName = '', $address1 = '', $address2 = '', $city = '', $state = '', $zip = '', $country = '', $phone = '', $fax = '', $email = '' )
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
     * SearchResult Model
     *
     * SearchResult is returned by the CheckAvailability, Search, and SearchStream functions.
     *
     * @return array $result
     * @author liam@hogan.re
     */
	public function SearchResult( $domain = '', $sld = '', $tld = '', $purchasable=false, $premium=false, $purchasePrice=0, $purchaseType = '', $renewalPrice = 0 )
	{
		$result = [
			"domainName" => $domain,			// string
			"sld" => $sld,						// string
			"tld" => $tld,						// string
			"purchasable" => $purchasable, 	 	// bool
			"premium" => $premium,				// bool
			"purchasePrice" => $purchasePrice, 	// float64
			"purchaseType" => $purchaseType,	// string
			"renewalPrice" => $renewalPrice		// float64
		];
		return $result;
	}

	/**
	* ListDomains
	* ListDomains returns all domains in the account. It omits some information that can be retrieved from GetDomain.
	*
	* @param int32 $perPage 		
	* Per Page is the number of records to return per request. Per Page defaults to 1,000.
	* @param int32 $page 			
	* Page is which page to return
	* @return array $domain
	* * *
	* @author rama@networks.co.id
	* @author liam@hogan.re
	*/
	public function getDomainList() { return $this->ListDomains(); }
	public function ListDomains( $perPage=1000, $page=0 )
	{
		$query = '?perPage='$perPage.'&page='.$page;
		$request = Requests::get($this->url . '/domains'.$query, $this->header, $this->options);
		return json_decode($request->body, TRUE);	
	}

	/**
	* GetDomain
	* GetDomain returns details about a specific domain
	*
	* @param string $domainName
	* * *
	* @author liam@hogan.re
	*/
	public function GetDomain( $domainName )
	{
		$request = Requests::get($this->url . '/domains/'.$domainName, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* CreateDomain
	* purchases a new domain. Domains that are not regularly priced require the purchase_price field to be specified.
	*
	* @param string $domain 
	* Domain is the domain object to create. If privacy_enabled is set, Whois Privacy will also be purchased for an additional amount.
	* @param float64 $purchasePrice
	* PurchasePrice is the amount to pay for the domain. If privacy_enabled is set, the regular price for whois protection will be added automatically. If VAT tax applies, it will also be added automatically. PurchasePrice is required if purchase_type is not "registration" or if it is a premium domain.
	* @param string $purchaseType
	* PurchaseType defaults to "registration" but should be copied from the result of a search command otherwise.
	* @param int $years
	* Years is for how many years to register the domain for. Years defaults to 1 if not passed and cannot be more than 10. If passing purchase_price make sure to adjust it accordingly.
	* @param map[string]string $tldRequirements
	* TLDRequirements is a way to pass additional data that is required by some registries.
	* @param string $promoCode
	* PromoCode is not yet implemented.
	* * *
	* @author liam@hogan.re
	*/
	public function CreateDomain( $domain, $purchasePrice, $purchaseType, $years, $tldRequirements, $promoCode )
	{
		$post = array(
			'domain' => $domain,
			'purchasePrice' => $purchasePrice,
			'purchaseType' => $purchaseType,
			'years' => $years,
			'tldRequirements' => $tldRequirements,
			'promoCode' => $promoCode
		);
		$request = Requests::post($this->url . '/domains/'.$domainName, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* EnableWhoisPrivacy
	* enables the domain to be private
	*
	* @param string $domainName 
	* DomainName is the domain name to enable whoisprivacy for.
	* * *
	* @author liam@hogan.re
	*/
	public function EnableWhoisPrivacy( $domainName )
	{
		$path = $this->url.'/domains/'.$domainName.':enableWhoisPrivacy';
		$request = Requests::post($path, $this->header, json_encode([]), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* DisableWhoisPrivacy
	* disables domain privacy
	*
	* @param string $domainName 
	* DomainName is the domain name to disable whoisprivacy for.
	* * *
	* @author liam@hogan.re
	*/
	public function EnableWhoisPrivacy( $domainName )
	{
		$path = $this->url.'/domains/'.$domainName.':disableWhoisPrivacy';
		$request = Requests::post($path, $this->header, json_encode([]), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* EnableAutorenew
	* enables the domain to be automatically renewed when it gets close to expiring.
	*
	* @param string $domainName 
	* DomainName is the domain name to enable autorenew for.
	* * *
	* @author liam@hogan.re
	*/
	public function EnableAutorenew( $domainName )
	{
		$path = $this->url.'/domains/'.$domainName.':enableAutorenew';
		$request = Requests::post($path, $this->header, json_encode([]), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* DisableAutorenew
	* enables the domain to be automatically renewed when it gets close to expiring.
	*
	* @param string $domainName 
	* DomainName is the domain name to disable autorenew for.
	* * *
	* @author liam@hogan.re
	*/
	public function DisableAutorenew( $domainName )
	{
		$path = $this->url.'/domains/'.$domainName.':disableAutorenew';
		$request = Requests::post($path, $this->header, json_encode([]), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* DisableAutorenew
	* enables the domain to be automatically renewed when it gets close to expiring.
	*
	* @param string $domainName 
	* DomainName is the domain name to disable autorenew for.
	* * *
	* @author liam@hogan.re
	*/
	public function DisableAutorenew( $domainName )
	{
		$path = $this->url.'/domains/'.$domainName.':disableAutorenew';
		$request = Requests::post($path, $this->header, json_encode([]), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* RenewDomain
	* will renew a domain. Purchase_price is required if the renewal is not regularly priced.
	*
	* @param string $domainName 
	* DomainName is the domain to renew.
	* @param float64 $purchasePrice
	* PurchasePrice is the amount to pay for the domain renewal. If VAT tax applies, it will also be added automatically. PurchasePrice is required if this is a premium domain.
	* @param int32 $years
	* Years is for how many years to renew the domain for. Years defaults to 1 if not passed and cannot be more than 10.
	* @param string $promoCode
	* PromoCode is not yet implemented.
	* * *
	* @author liam@hogan.re
	*/
	public function RenewDomain( $domainName, $purchasePrice, $years, $promoCode )
	{
		$post = array(
			'domainName' => $domainName,
			'purchasePrice' => $purchasePrice,
			'years' => $years,
			'promoCode' => $promoCode
		);
		$path = $this->url.'/domains/'.$domainName.':renew';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* GetAuthCodeForDomain
	* returns the Transfer Authorization Code for the domain.
	*
	* @param string $domainName 
	* DomainName is the domain name to retrieve the authorization code for.
	* * *
	* @author liam@hogan.re
	*/
	public function GetAuthCodeForDomain( $domainName )
	{
		$path = $this->url.'/domains/'.$domainName.':getAuthCode';
		$request = Requests::post($path, $this->header, json_encode([]), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* PurchasePrivacy
	* will add Whois Privacy protection to a domain or will an renew existing subscription.
	*
	* @param string $domainName 
	* DomainName is the domain name to retrieve the authorization code for.
	* @param float64 $purchasePrice
	* PurchasePrice is the amount you expect to pay.
	* @param int64 $years
	* Years is the number of years you wish to purchase Whois Privacy for. Years defaults to 1 and cannot be more then the domain expiration date.
	* @param string $promoCode
	* PromoCode is not yet implemented
	* * *
	* @author liam@hogan.re
	*/
	public function PurchasePrivacy( $domainName, $purchasePrice, $years, $promoCode )
	{
		$post = array(
			'domainName' => $domainName,
			'purchasePrice' => $purchasePrice,
			'years' => $years,
			'promoCode' => $promoCode
		);
		$path = $this->url.'/domains/'.$domainName.':purchasePrivacy';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* SetNameservers 
	* will set the nameservers for the Domain.
	*
	* @param string $domainName 
	* DomainName is the domain name to set the nameservers for.
	* @param array $nameservers
	* Namesevers is a list of the nameservers to set. Nameservers should already be set up and hosting the zone properly as some registries will verify before allowing the change.
	* * *
	* @author liam@hogan.re
	*/
	public function SetNameservers( $domainName, $nameservers )
	{
		$post = array('nameservers' => $nameservers);
		$path = $this->url.'/domains/'.$domainName.':setNameservers';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* SetContacts 
	* will set the contacts for the Domain.
	*
	* @param string $domainName 
	* DomainName is the domain name to set the contacts for.
	* @param Contacts[model] $contacts
	* Contacts is the list of contacts to set.
	* * *
	* @author liam@hogan.re
	*/
	public function SetContacts( $domainName, $contacts )
	{
		$post = array('contacts' => $contacts);
		$path = $this->url.'/domains/'.$domainName.':setContacts';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* LockDomain 
	* will lock a domain so that it cannot be transferred to another registrar.
	*
	* @param string $domainName 
	* DomainName is the domain name to lock.
	* * *
	* @author liam@hogan.re
	*/
	public function LockDomain( $domainName )
	{
		$post = array();
		$path = $this->url.'/domains/'.$domainName.':lock';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* UnlockDomain 
	* will unlock a domain so that it can be transferred to another registrar.
	*
	* @param string $domainName 
	* DomainName is the domain name to unlock.
	* * *
	* @author liam@hogan.re
	*/
	public function UnlockDomain( $domainName )
	{
		$post = array();
		$path = $this->url.'/domains/'.$domainName.':unlock';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* CheckAvailability 
	* will check a list of domains to see if they are purchasable. A Maximum of 50 domains can be specified.
	*
	* @param []string $domainName 
	* DomainNames is the list of domains to check if they are available.
	* @param string
	* PromoCode is not implemented yet.
	* * *
	* @author liam@hogan.re
	*/
	public function CheckAvailability( $domainName )
	{
		$post = array();
		$path = $this->url.'/domains/'.$domainName.':checkAvailability';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* Search 
	* Search will perform a search for specified keywords.
	*
	* @param int32 $timeout 
	* Timeout is a value in milliseconds on how long to perform the search for. Valid timeouts are between 500ms to 5,000ms. If not specified, timeout defaults to 1,000ms. Since some additional processing is performed on the results, a response may take longer then the timeout.
	* @param string $keyword
	* Keyword is the search term to search for. It can be just a word, or a whole domain name.
	* @param []string $tldFilter
	* TLDFilter will limit results to only contain the specified TLDs.
	* @param string $promoCode
	* PromoCode is not implemented yet.
	* * *
	* @author liam@hogan.re
	*/
	public function Search( $timeout=1000, $keyword, $tldFilter, $promoCode )
	{
		$post = array(
			'timeout' = $timeout,
			'keyword' = $keyword,
			'tldFilter' = $tldFilter,
			'promoCode' = $promoCode
		);
		$path = $this->url.'/domains/'.$domainName.':search';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* Search 
	* Search will perform a search for specified keywords.
	*
	* @param int32 $timeout 
	* Timeout is a value in milliseconds on how long to perform the search for. Valid timeouts are between 500ms to 5,000ms. If not specified, timeout defaults to 1,000ms. Since some additional processing is performed on the results, a response may take longer then the timeout.
	* @param string $keyword
	* Keyword is the search term to search for. It can be just a word, or a whole domain name.
	* @param []string $tldFilter
	* TLDFilter will limit results to only contain the specified TLDs.
	* @param string $promoCode
	* PromoCode is not implemented yet.
	* * *
	* @author liam@hogan.re
	*/
	public function SearchStream( $timeout=1000, $keyword, $tldFilter, $promoCode )
	{
		$post = array(
			'timeout' = $timeout,
			'keyword' = $keyword,
			'tldFilter' = $tldFilter,
			'promoCode' = $promoCode
		);
		$path = $this->url.'/domains/'.$domainName.':searchStream';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/* EmailForwardings */

	/* 
		- EmailForwardings
		 -- Emailforwarding Model
		 -- ListEmailForwardings
		 -- GetEmailForwarding
		 -- CreateEmailForwarding
		 -- UpdateEmailForwarding
		 -- DeleteEmailForwarding
	*/

	/**
     * EmailForwarding
     * EmailForwarding contains all the information for an email forwarding entry.
     *
     * @return array $emailforwarding
     * * * 
     * @author liam@hogan.re
     */
	public function EmailForwarding($domainName='', $keyTag='', $algorithm='', $digestType='', $digest='')
	{
		$emailforwarding = [
			"domainName" => $domainName,
			"emailBox" => $emailBox,
			"emailTo" => $emailTo
		];
		return $emailforwarding;
	}

	/**
	* ListEmailForwardings
	* returns a paginated list of email forwarding entries for a domain.
	*
	* @param int32 $perPage 		
	* Per Page is the number of records to return per request. Per Page defaults to 1,000.
	* @param int32 $page 			
	* Page is which page to return
	* @param string $domainName
	* * *
	* @author liam@hogan.re
	*/
	public function ListEmailForwardings( $domainName, $perPage=1000, $page )
	{
		$query = '?perPage='$perPage.'&page='.$page;
		$request = Requests::get($this->url . '/domains/'.$domainName.'/email/forwarding'.$query, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* GetEmailForwarding 
	* returns an email forwarding entry.
	*
	* @param string $domainName
	* DomainName is the domain to list email forwarded box for.
	* @param string $emailBox
	* EmailBox is which email box to retrieve.
	* * *
	* @author liam@hogan.re
	*/
	public function GetEmailForwarding( $domainName, $emailBox )
	{
		$request = Requests::get($this->url . '/domains/'.$domainName.'/email/forwarding/'.$emailBox, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* CreateEmailForwarding 
	* creates an email forwarding entry. If this is the first email forwarding entry, it may modify the MX records for the domain accordingly.
	*
	* @param string $domainName
	* DomainName is the domain part of the email address to forward.
	* @param string $emailBox
	* EmailBox is the user portion of the email address to forward.
	* @param string $emailTo
	* EmailTo is the entire email address to forward email to.
	* * *
	* @author liam@hogan.re
	*/
	public function CreateEmailForwarding( $domainName, $emailBox, $emailTo )
	{
		$post = array(
			'emailBox' => $emailBox,
			'emailTo' => $emailTo
		);
		$request = Requests::post($this->url.'/domains/'.$domainName.'/email/forwarding/', $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* UpdateEmailForwarding 
	* updates which email address the email is being forwarded to.
	*
	* @param string $domainName
	* DomainName is the domain part of the email address to forward.
	* @param string $emailBox
	* EmailBox is the user portion of the email address to forward.
	* @param string $emailTo
	* EmailTo is the entire email address to forward email to.
	* * *
	* @author liam@hogan.re
	*/
	public function UpdateEmailForwarding( $domainName, $emailBox, $emailTo )
	{
		$path = $this->url.'/domains/'.$domainName.'/email/forwarding/'.$emailBox;
		$post = array('emailTo' => $emailTo);
		$request = Requests::put($path, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* DeleteEmailForwarding 
	* deletes the email forwarding entry.
	*
	* @param string $domainName
	* DomainName is the domain to delete the email forwarded box from.
	* @param string $emailBox
	* EmailBox is which email box to delete.
	* * *
	* @author liam@hogan.re
	*/
	public function DeleteEmailForwarding( $domainName, $emailBox )
	{
		$request = Requests::delete($this->url.'/domains/'.$domainName.'/email/forwarding/'.$emailBox, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/* Transfers */

	/*
		- Transfers
		 -- Transfer Model
		 -- ListTransfers
		 -- GetTransfer
		 -- CreateTransfer
		 -- CancelTransfer
	*/

	/*
     * Transfer Model
     *
     * Transfer contains the information related to a transfer of a domain name to Name.com.
     *
     * @return array $transfer
     * @author liam@hogan.re
     */
	public function Transfer( $domain, $email, $status )
	{
		$data = [
			"domainName" => $domain,
			"email" => $email,
			"status" => $status
		];
		return $data;
	}

	/**
	* ListTransfers
	* lists all pending transfer in requests. To get the information related to a non-pending transfer, you can use the GetTransfer function for that.
	*
	* @param int32 $perPage 		
	* Per Page is the number of records to return per request. Per Page defaults to 1,000.
	* @param int32 $page 			
	* Page is which page to return
	* @return array $transfers
	* * *
	* @author liam@hogan.re
	*/
	public function ListTransfers( $perPage=1000, $page=0 )
	{
		$query = '?perPage='$perPage.'&page='.$page;
		$request = Requests::get($this->url . '/domains'.$query, $this->header, $this->options);
		return json_decode($request->body, TRUE);	
	}

	/**
	* GetTransfer
	* gets details for a transfer request.
	*
	* @param string $domainName
	* * *
	* @author liam@hogan.re
	*/
	public function GetTransfer( $domainName )
	{
		$request = Requests::get($this->url . '/transfers/'.$domainName, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* CreateTransfer
	* purchases a new domain transfer request.
	*
	* @param string $domainName 
	* DomainName is the domain you want to transfer to Name.com.
	* @param string $authCode
	* AuthCode is the authorization code for the transfer. Not all TLDs require authorization codes, but most do.
	* @param bool $privacyEnabled
	* PrivacyEnabled is a flag on whether to purchase Whois Privacy with the transfer.
	* @param float64 $purchasePrice
	* PurchasePrice is the amount to pay for the transfer of the domain. If privacy_enabled is set, the regular price for Whois Privacy will be added automatically. If VAT tax applies, it will also be added automatically. PurchasePrice is required if the domain to transfer is a premium domain.
	* @param string $promoCode
	* PromoCode is not implemented yet
	* * *
	* @author liam@hogan.re
	*/
	public function CreateDomain( $domainName, $authCode, $privacyEnabled, $purchasePrice, $promoCode )
	{
		$post = array(
			'domainName' => $domainName,
			'authCode' => $authCode,
			'privacyEnabled' => $privacyEnabled,
			'purchasePrice' => $purchasePrice,
			'promoCode' => $promoCode
		);
		$request = Requests::post($this->url . '/transfers', $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
	* CancelTransfer
	* cancels a pending transfer request and refunds the amount to account credit.
	*
	* @param string $domainName 
	* DomainName is the domain to cancel the transfer for.
	* * *
	* @author liam@hogan.re
	*/
	public function CreateDomain( $domainName )
	{
		$request = Requests::post($this->url.'/transfers/'.$domainName.':cancel', $this->header, json_encode([]), $this->options);
		return json_decode($request->body, TRUE);
	}

	/* URLForwardings */

	/*
		- URLForwardings
		 -- Urlforwarding Model
		 -- ListURLForwardings
		 -- GetURLForwarding
		 -- CreateURLForwarding
		 -- UpdateURLForwarding
		 -- DeleteURLForwarding
	*/

	/**
     * Urlforwarding Model
     * URLForwarding is the model for URL forwarding entries.
     *
     * @return array $urlforward
     * * *
     * @author liam@hogan.re
     */
	public function URLForwarding( $domain, $host, $forwardsTo, $type, $title, $meta )
	{
		$urlforwarding = [
			"domainName" => $domain,
			"host" => $host,
			"forwardsTo" => $forwardsTo,
			"type" => $type,
			"title" => $title,
			"meta" => $meta
		];
		return $urlforwarding;
	}

	/**
     * ListURLForwardings
     * returns a pagenated list of URL forwarding entries for a domain.
     *
     * @param string $domainName
     * DomainName is the domain to list URL forwarding entries for.
     * @param int32 $perPage
	 * Per Page is the number of records to return per request. Per Page defaults to 1,000.
	 * @param int32 $page 			
	 * Page is which page to return
	 * * *
     * @author liam@hogan.re
     */
	public function ListURLForwardings( $domainName, $perPage = 1000, $page )
	{
		$query = '?perPage='$perPage.'&page='.$page;
		$request = Requests::get($this->url.'/domains/'.$domainName.'/url/forwarding/'.$query, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
     * GetURLForwarding 
     * returns an URL forwarding entry.
     *
     * @param string $domainName
     * DomainName is the domain to list URL forwarding entry for.
     * @param string $host
	 * Host is the part of the domain name before the domain. i.e. www is the host for www.example.org.
	 * * *
     * @author liam@hogan.re
     */
	public function ListURLForwardings( $domainName, $host )
	{
		$query = '?perPage='$perPage.'&page='.$page;
		$request = Requests::get($this->url.'/domains/'.$domainName.'/url/forwarding/'.$host, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
     * CreateURLForwarding 
     * creates an URL forwarding entry. If this is the first URL forwarding entry, it may modify the A records for the domain accordingly. 
     *
     * @param string $domainName
     * DomainName is the domain part of the hostname to forward.
     * @param string $host
	 * Host is the entirety of the hostname. i.e. www.example.org
	 * @param string $forwardsTo
	 * ForwardsTo is the URL this host will be forwarded to.
	 * @param string $type
	 * Type is the type of forwarding. Valid types are: Masked - This retains the original domain in the address bar and will not reveal or display the actual destination URL. If you are forwarding knowledgebase.ninja to Name.com, the address bar will say knowledgebase.ninja. This is sometimes called iframe forwarding. And: Redirect - This does not retain the original domain in the address bar, so the user will see it change and realize they were forwarded from the URL they originally entered. If you are forwarding knowledgebase.ninja to Name.com, the address bar will say Name.com. This is also called 301 forwarding.
	 * @param string $title
	 * Title is the title for the html page to use if the type is masked. Values are ignored for types other then "masked".
	 * @param string $meta
	 * Meta is the meta tags to add to the html page if the type is masked. ex: "meta name='keywords' content='fish, denver, platte'". Values are ignored for types other then "masked".
	 * * *
     * @author liam@hogan.re
     */
	public function ListURLForwardings( $domainName, $host, $forwardsTo, $type, $title, $meta )
	{
		$post = array(
			'host' => $host,
			'forwardsTo' => $forwardsTo,
			'type' => $type,
			'title' => $title,
			'meta' => $meta
		);
		$request = Requests::post($this->url.'/domains/'.$domainName.'/url/forwarding/', $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
     * UpdateURLForwarding
     * updates which URL the host is being forwarded to.
     *
     * @param string $domainName
     * DomainName is the domain part of the hostname to forward.
     * @param string $host
	 * Host is the entirety of the hostname. i.e. www.example.org
	 * @param string $forwardsTo
	 * ForwardsTo is the URL this host will be forwarded to.
	 * @param string $type
	 * Type is the type of forwarding. Valid types are: Masked - This retains the original domain in the address bar and will not reveal or display the actual destination URL. If you are forwarding knowledgebase.ninja to Name.com, the address bar will say knowledgebase.ninja. This is sometimes called iframe forwarding. And: Redirect - This does not retain the original domain in the address bar, so the user will see it change and realize they were forwarded from the URL they originally entered. If you are forwarding knowledgebase.ninja to Name.com, the address bar will say Name.com. This is also called 301 forwarding.
	 * @param string $title
	 * Title is the title for the html page to use if the type is masked. Values are ignored for types other then "masked".
	 * @param string $meta
	 * Meta is the meta tags to add to the html page if the type is masked. ex: "meta name='keywords' content='fish, denver, platte'". Values are ignored for types other then "masked".
	 * * *
     * @author liam@hogan.re
     */
	public function ListURLForwardings( $domainName, $host, $forwardsTo, $type, $title, $meta )
	{
		$post = array(
			'host' => $host,
			'forwardsTo' => $forwardsTo,
			'type' => $type,
			'title' => $title,
			'meta' => $meta
		);
		$request = Requests::put($this->url.'/domains/'.$domainName.'/url/forwarding/'.$host, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
     * DeleteURLForwarding 
     * deletes the URL forwarding entry.
     *
     * @param string $domainName
     * DomainName is the domain to delete the URL forwardind entry from.
     * @param string $host
	 * Host is the part of the domain name before the domain. i.e. www is the host for www.example.org.
	 * @return Empty response.
	 * * *
     * @author liam@hogan.re
     */
	public function ListURLForwardings( $domainName, $host, $forwardsTo, $type, $title, $meta )
	{
		$post = array(
			'host' => $host,
			'forwardsTo' => $forwardsTo,
			'type' => $type,
			'title' => $title,
			'meta' => $meta
		);
		$request = Requests::put($this->url.'/domains/'.$domainName.'/url/forwarding/'.$host, $this->header, json_encode($post), $this->options);
		return json_decode($request->body, TRUE);
	}

	/* VanityNameservers */

	/*
		- VanityNameservers
		 -- VanityNameserver Model
		 -- ListVanityNameservers
		 -- GetVanityNameserver
		 -- CreateVanityNameserver
		 -- UpdateVanityNameserver
		 -- DeleteVanityNameserver
	*/

	/**
     * VanityNameserver Model
     *
     * VanityNameserver contains the hostname as well 
     * as the list of IP addresses for nameservers.
     *
     * @return array $nameserver
     * @author liam@hogan.re
     */
	public function VanityNameservers( $domain, $hostname, $ips = [] ) { $this->VanityNameserver($domain,$hostname,$ips); }
	public function VanityNameserver( $domain, $hostname, $ips = [] )
	{
		$data = [
			"domainName" => $domain,
			"hostname" => $hostname,
			"ips" => $ips
		];
		return $data;
	}

	/**
     * ListVanityNameservers
     * lists all nameservers registered with the registry. It omits the IP addresses from the response. Those can be found from calling GetVanityNameserver.
     *
	 * @param int32 $perPage 		
	 * Per Page is the number of records to return per request. Per Page defaults to 1,000.
	 * @param int32 $page 			
	 * Page is which page to return
	 * @param string $domainName
     * @return array $nameservers
     * * *
     * @author liam@hogan.re
     */
	public function ListVanityNameservers( $domainName, $perPage=1000, $page )
	{
		$query = '?perPage='$perPage.'&page='.$page;
		$path = $this->url.'/domains/'.$domainName.'/vanity_nameservers'.$query;
		$request = Requests::get($path, $this->header, $this->options);
		return json_decode($request->body, TRUE);
	}

	/**
     * GetVanityNameserver
     * gets the details for a vanity nameserver registered with the registry.
     *
     * @param string $domainName DomainName is the domain to for the vanity nameserver.
     * @param string $hostname 	Hostname is the hostname for the vanity nameserver.
     * * *
     * @author liam@hogan.re
     */
	public function GetVanityNameserver( $domainName, $hostname )
	{
		$path = $this->url.'/domains/'.$domainName.'/vanity_nameservers/'.$hostname;
		$request = Requests::get($path, $this->header, $this->options);
		$data = json_decode($request->body, TRUE);	
		if ( isset($data['ips']) ) return $data['ips'];
		if (!isset($data['ips']) ) {print_r($data); return false;}
	}

	/**
     * CreateVanityNameserver
     * registers a nameserver with the registry.
     *
     * @param string $domainName 	DomainName is the domain to for the vanity nameserver.
     * @param string $hostname 		Hostname is the hostname for the vanity nameserver.
     * @param []string $ips 		IPs is a list of IP addresses that are used for glue records for this nameserver.
     * * *
     * @author liam@hogan.re
     */
	public function CreateVanityNameserver( $domainName, $hostname, $ips )
	{
		$post = array(
			'hostname' => $hostname, 
			'ips' => (is_array($ips))?$ips:[$ips]
		);
		$path = $this->url . '/domains/'.$domainName.'/vanity_nameservers';
		$request = Requests::post($path, $this->header, json_encode($post), $this->options);
		$data = json_decode($request->body, TRUE);	
		return $data;
	}

	/**
     * UpdateVanityNameserver
     * allows you to update the glue record IP addresses at the registry.
     *
     * @param string 	$domainName 	DomainName is the domain to for the vanity nameserver.
     * @param string 	$hostname 		Hostname is the hostname for the vanity nameserver.
     * @param []string 	$ips 		IPs is a list of IP addresses that are used for glue records for this nameserver.
     * * *
     * @author liam@hogan.re
     */
	public function UpdateVanityNameserver( $domainName, $hostname, $ips )
	{
		$post = array( 'ips' => (is_array($ips))?$ips:[$ips] );
		$path = $this->url.'/domains/'.$domainName.'/vanity_nameservers/'.$hostname;
		$request = Requests::put($path, $this->header, json_encode($post), $this->options);
		$data = json_decode($request->body, TRUE);
		return $data;

	}

	/**
     * DeleteVanityNameserver
     *  unregisteres the nameserver at the registry. This might fail if the registry believes the nameserver is in use.
     *
     * @param string 	$domainName 	DomainName is the domain of the vanity nameserver to delete.
     * @param string 	$hostname 		Hostname is the hostname of the vanity nameserver to delete.
     * * *
     * @author liam@hogan.re
     */
	public function DeleteVanityNameserver( $domainName, $hostname )
	{
		$path = $this->url.'/domains/'.$domainName.'/vanity_nameservers/'.$hostname;
		$request = Requests::delete($path, $this->header, $this->options);
		$data = json_decode($request->body, TRUE);
		return $data;
	}


	// Unattended Functions


   /**
     * Add Price
     * Append the price when we do check availibility
     *
     * @var string $tld the tld (.com,.etc)
     * @var string $price how much we add price (1.32)
     * @return bool false or true
     * * * 
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

}
