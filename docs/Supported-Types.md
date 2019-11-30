Supported Types
===============

Below are a list of the RData types supported by this library, while not every single type has been implemented, all
current in-use types have been. Other types can be implemented using the `Badcow\DNS\Rdata\PolymorphicRdata` object or
the Unknown Record Type `Badcow\DNS\Rdata\UnknownType` which complies with [RFC 3597 - Handling of Unknown DNS Resource
Record (RR) Types](https://tools.ietf.org/html/rfc3597).

| Type       | Type ID | Defining RFC          | Supported? | Usage*   |
|------------|---------|-----------------------|------------|----------|
| A          | 1       | RFC 1035              | ✔          | In Use   |
| AAAA       | 28      | RFC 3596              | ✔          | In Use   |
| AFSDB      | 18      | RFC 1183              | ✔          | In Use   |
| APL        | 42      | RFC 3123              | ✔          | In Use   |
| CAA        | 257     | RFC 6844              | ✔          | In Use   |
| CDNSKEY    | 60      | RFC 7344              | ✔          | In Use   |
| CDS        | 59      | RFC 7344              | ✔          | In Use   |
| CERT       | 37      | RFC 4398              | ✔          | In Use   |
| CNAME      | 5       | RFC 1035              | ✔          | In Use   |
| CSYNC      | 62      | RFC 7477              | ✔          | In Use   |
| DHCID      | 49      | RFC 4701              | ✔          | In Use   |
| DLV        | 32769   | RFC 4431              | ✔          | In Use   |
| DNAME      | 39      | RFC 6672              | ✔          | In Use   |
| DNSKEY     | 48      | RFC 4034              | ✔          | In Use   |
| DS         | 43      | RFC 4034              | ✔          | In Use   |
| HIP        | 55      | RFC 8005              | ✔          | In Use   |
| IPSECKEY   | 45      | RFC 4025              | ✔          | In Use   |
| KEY        | 25      | RFC 2535 & RFC 2930   | ✔          | In Use   |
| KX         | 36      | RFC 2230              | ✔          | In Use   |
| LOC        | 29      | RFC 1876              | ✔          | In Use   |
| MX         | 15      | RFC 1035 & RFC 7505   | ✔          | In Use   |
| NAPTR      | 35      | RFC 3403              | ✔          | In Use   |
| NS         | 2       | RFC 1035              | ✔          | In Use   |
| NSEC       | 47      | RFC 4034              | ✔          | In Use   |
| NSEC3      | 50      | RFC 5155              | ✔          | In Use   |
| NSEC3PARAM | 51      | RFC 5155              | ✔          | In Use   |
| OPENPGPKEY | 61      | RFC 7929              | ✖          | In Use   |
| PTR        | 12      | RFC 1035              | ✔          | In Use   |
| RP         | 17      | RFC 1183              | ✔          | In Use   |
| RRSIG      | 46      | RFC 4034              | ✔          | In Use   |
| SIG        | 24      | RFC 2535              | ✔          | In Use   |
| SMIMEA     | 53      | RFC 8162              | ✖          | In Use   |
| SOA        | 6       | RFC 1035 & RFC 2308   | ✔          | In Use   |
| SRV        | 33      | RFC 2782              | ✔          | In Use   |
| SSHFP      | 44      | RFC 4255              | ✔          | In Use   |
| TA         | 32768   | N/A                   | ✔          | In Use   |
| TKEY       | 249     | RFC 2930              | ✔          | In Use   |
| TLSA       | 52      | RFC 6698              | ✔          | In Use   |
| TSIG       | 250     | RFC 2845              | ✔          | In Use   |
| TXT        | 16      | RFC 1035              | ✔          | In Use   |
| URI        | 256     | RFC 7553              | ✔          | In Use   |
| A6         | 38      | RFC 2874              | ✖          | Obsolete |
| APL        | 42      | RFC 3123              | ✔          | Obsolete |
| ATMA       | 34      | N/A                   | ✖          | Obsolete |
| DOA        | 259     | N/A                   | ✖          | Obsolete |
| EID        | 31      | N/A                   | ✖          | Obsolete |
| EUI48      | 108     | RFC 7043              | ✖          | Obsolete |
| EUI64      | 109     |                       | ✖          | Obsolete |
| GID        | 102     | N/A                   | ✖          | Obsolete |
| GPOS       | 27      | RFC 1712              | ✖          | Obsolete |
| HINFO      | 13      | RFC 883               | ✔          | Obsolete |
| ISDN       | 20      |                       | ✖          | Obsolete |
| KEY        | 25      |                       | ✔          | Obsolete |
| L32        | 105     |                       | ✖          | Obsolete |
| L64        | 106     |                       | ✖          | Obsolete |
| LP         | 107     |                       | ✖          | Obsolete |
| MAILA      | 254     |                       | ✖          | Obsolete |
| MAILB      | 253     |                       | ✖          | Obsolete |
| MB         | 7       | RFC 883               | ✖          | Obsolete |
| MD         | 3       | RFC 883               | ✖          | Obsolete |
| MF         | 4       |                       | ✖          | Obsolete |
| MG         | 8       |                       | ✖          | Obsolete |
| MINFO      | 14      |                       | ✖          | Obsolete |
| MR         | 9       |                       | ✖          | Obsolete |
| NB         | 32      | RFC 1002              | ✖          | Obsolete |
| NBSTAT     | 33      |                       | ✖          | Obsolete |
| NID        | 104     | RFC 6742              | ✖          | Obsolete |
| NIMLOC     | 32      | N/A                   | ✖          | Obsolete |
| NINFO      | 56      | N/A                   | ✖          | Obsolete |
| NSAP       | 22      | RFC 1706              | ✖          | Obsolete |
| NSAP\-PTR  | 23      |                       | ✖          | Obsolete |
| NULL       | 10      | RFC 883               | ✖          | Obsolete |
| NXT        | 30      | RFC 2065              | ✖          | Obsolete |
| PX         | 26      | RFC 2163              | ✖          | Obsolete |
| RKEY       | 57      | N/A                   | ✖          | Obsolete |
| RP         | 17      | RFC 1183              | ✔          | Obsolete |
| RT         | 21      |                       | ✖          | Obsolete |
| SIG        | 24      |                       | ✔          | Obsolete |
| SINK       | 40      | N/A                   | ✖          | Obsolete |
| SPF        | 99      | RFC 4408              | ✔          | Obsolete |
| TALINK     | 58      | N/A                   | ✖          | Obsolete |
| UID        | 101     | N/A                   | ✖          | Obsolete |
| UINFO      | 100     | N/A                   | ✖          | Obsolete |
| UNSPEC     | 103     | N/A                   | ✖          | Obsolete |
| WKS        | 11      | RFC 883 & RFC 1035    | ✖          | Obsolete |
| X25        | 19      |                       | ✖          | Obsolete |

_* Obsolescence based on [Wikipedia: List of DNS record types](https://en.wikipedia.org/wiki/List_of_DNS_record_types)_