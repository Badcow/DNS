Using Custom RData Handlers
===========================
Out-of-the-box, the library will handle most RData types that are regularly encountered. Occasionally, you may encounter
an unsupported type. You can add your own RData handler method for the record type. For example, you may want to support
the non-standard `SPF` record type, and return a `TXT` instance.
```php
$spf = function (\ArrayIterator $iterator): Badcow\DNS\Rdata\TXT {
    $string = '';
    while ($iterator->valid()) {
        $string .= $iterator->current() . ' ';
        $iterator->next();
    }
    $string = trim($string, ' "'); //Remove whitespace and quotes

    $spf = new Badcow\DNS\Rdata\TXT;
    $spf->setText($string);

    return $spf;
};

$customHandlers = ['SPF' => $spf];

$record = 'example.com. 7200 IN SPF "v=spf1 a mx ip4:69.64.153.131 include:_spf.google.com ~all"';
$parser = new \Badcow\DNS\Parser\Parser($customHandlers);
$zone = $parser->makeZone('example.com.', $record);
```

You can also overwrite the default handlers if you wish, as long as your handler method returns an instance of
`Badcow\DNS\Rdata\RdataInterface`.
