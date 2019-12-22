Parsing Unknown or Unsupported Types
====================================

PolymorphicRdata Class
----------------------

In DNS records, there may be rdata types that are not implemented in this library (though these should now be few). For
example:
```text
files.example.com.  IN  3600    MD     UDEL.ARPA.
```
The above entry refers to the obsolete `MD` resource data type. Whilst `MD` is a valid rdata type, it has not been implemented
in the Badcow DNS Library. When this `MD` record is parsed, it will parsed into an instance of `Badcow\DNS\Rdata\PolymorphicRdata`.

The `PolymorphicRdata` class is used for any *valid* rdata type that has not been implemented by the library and whose records
have a valid text representation. When this type is outputted to text, the data will remain unchanged, but the data cannot
be outputted in a wire format.

If, however, you are using a wholly unknown Rdata type (i.e. one which has not been specified by IANA) then the parser will
throw a `ParseException`. In this case, if the rdata type is valid (or apart of some specific functionality not otherwise
defined) it may be useful define a [custom rdata handler](Custom-Rdata-Handlers.md).

UnknownType Class
------------------
The same record from above can be represented in a text file as:
```text
files.example.com.  IN  3600    TYPE3   \# 11   04 55 44 45 4c 04 41 52 50 41 00
```
This representation conforms with _RFC 3597 - Handling of Unknown DNS Resource Record (RR) Types_. In this case, the record
will be parsed into an instance of `Badcow\DNS\Rdata\UnknownType`.

The `UnknownType` class is used to handle records that are formatted in this way. This class can output the _wire format_
of the record and the text representation above. This is the preferred way of handling unknown, unsupported, or otherwise
"made up" rdata types.

If the type is implemented, then the parser will render an instance of that type instead of the `UnknownType` class.
_Exempli Gratia_:
```text
files.example.com.  IN  3600    TYPE1   \# 4 c0 a8 01 64
```
The above record type, TYPE1, is a known and supported type - an `A` record with the four octets (bytes) of the IP address
`192.168.1.100`. This record will be parsed to an instance of `Badcow\DNS\Rdata\A` and *NOT* as an `UnknownType` class.