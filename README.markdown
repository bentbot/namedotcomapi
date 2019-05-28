# Name.com PHP API

## Creating a new construct
`````
$name = new NameDotComApi('username', 'api-key-api-key-api-key-api-key-api-key');
$domains = $name->getDomainList();
print_r($domains);
`````

## Apis

### Hello
HelloFunc()

### DNS Records
RecordModel()

### DNSSEC
DNSSECModel()

### Domains
DomainModel()
addPrice($tld, $price)
getDomainList()
checkDomain($keyword)

### Email Forwarding
EmailForwardingModel()

### Domain Transfers
TransferModel()

### URL Forwarding
URLForwardingModel()

### Vanity Nameservers
VanityNameserversModel( $domain, $hostname, $ips = [] )
ListVanityNameservers( $domain )
GetVanityNameserver( $domain, $hostname )
CreateVanityNameserver( $domain, $hostname, $ips )
UpdateVanityNameserver( $domain, $hostname, $ips )
DeleteVanityNameserver( $domain, $hostname )
