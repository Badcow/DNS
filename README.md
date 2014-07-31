Badcow DNS Zone Library
=======================

This library constructs DNS zone records based on [RFC1035](http://www.ietf.org/rfc/rfc1035.txt) and subsequent standards.

## Parser

This library includes a DNS parser. The parser is still very much under construction. The aim of the parser is to
interpret valid DNS Zone files and output them as a `\Badcow\DNS\ZoneInterface`.

## Example usage

    use Badcow\DNS\Zone,
        Badcow\DNS\ZoneFile,
        Badcow\DNS\ResourceRecord,
        Badcow\DNS\Rdata\SoaRdata,
        Badcow\DNS\Rdata\NsRdata,
        Badcow\DNS\Rdata\ARdata,
        Badcow\DNS\Rdata\AaaaRdata,
        Badcow\DNS\Validator;

    $soa_rdata = new SoaRdata;
    $soa_rdata->setMname('example.com.');
    $soa_rdata->setRname('postmaster.example.com.');
    $soa_rdata->setSerial(date('Ymd01'));
    $soa_rdata->setRefresh(3600);
    $soa_rdata->setExpire(14400);
    $soa_rdata->setRetry(604800);
    $soa_rdata->setMinimum(3600);

    $soa = new ResourceRecord;
    $soa->setType('SOA');
    $soa->setClass('IN');
    $soa->setName('@');
    $soa->setRdata($soa_rdata);

    $ns1r = new NsRdata;
    $ns1r->setNsdname('ns1.example.com.');

    $ns2r = new NsRdata;
    $ns2r->setNsdname('ns2.example.com.');

    $ns1 = new ResourceRecord;
    $ns1->setType('NS')
        ->setClass('IN')
        ->setName('@')
        ->setTtl(800)
        ->setRdata($ns1r);

    $ns2 = new ResourceRecord;
    $ns2->setType('NS')
        ->setClass('IN')
        ->setName('@')
        ->setRdata($ns2r);

    $a_rdata = new ARdata;
    $a_rdata->setAddress('192.168.1.0');

    $a_record = new ResourceRecord;
    $a_record->setType('A')
        ->setName('subdomain.au')
        ->setRdata($a_rdata)
        ->setComment("This is a local ip.");

    $aa_rdata = new AaaaRdata;
    $aa_rdata->setAddress('::1');

    $aa_record = new ResourceRecord;
    $aa_record->setType('AAAA')
        ->setName('ipv6domain')
        ->setRdata($aa_rdata)
        ->setComment("This is an IPv6 domain.");
    

    $zone = new Zone;
    $zone->setDefaultTtl(14400)
        ->setZoneName('example.com.')
        ->addResourceRecord($soa)
        ->addResourceRecord($ns1)
        ->addResourceRecord($ns2)
        ->addResourceRecord($a_record)
        ->addResourceRecord($aa_record);

    $zoneFile = new ZoneFile($zone);

    $zoneFile->saveToFile(__DIR__);

    header('Content-type: text/plain');
    echo $zoneFile->render();
    echo "\n\n\n";
    echo (\Badcow\DNS\Validator::validateZoneFile($zone->getZoneName(), __DIR__ . '/example.com')) ?
        'This is a valid record' : 'This is an invalid record';

## Build Status
[![Build Status](https://travis-ci.org/samuelwilliams/Badcow-DNS-Zone-Library.png)](https://travis-ci.org/samuelwilliams/Badcow-DNS-Zone-Library)