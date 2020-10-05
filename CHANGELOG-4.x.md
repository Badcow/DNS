CHANGELOG for 4.x
=================
## 4.0
* `Badcow\DNS\Rdata\RdataInterface::fromWire` is no longer static and does not return an instance of the class. You now
must instantiate the object first, and then call `fromWire` method. All parameters are the same.
* `Badcow\DNS\Rdata\RdataInterface::fromText` is no longer static and does not return an instance of the class. You now
must instantiate the object first, and then call `fromText` method. All parameters are the same.
* Deleted `Bacow\DNS\Rdata\Algorithms`, use `Bacow\DNS\Algorithms` instead. 
* [PR #73](https://github.com/Badcow/DNS/pull/73) Resolves issue where records with integers are not parsed correctly. (Thank you, [Hossein Taleghani](https://github.com/a3dho3yn))
* [Issue #63](https://github.com/Badcow/DNS/issues/63) - Consistent handling ok keys, signatures and digests.
  * `KEY::setPublicKey($key)` now expects the raw binary form of the public key. Similarly `KEY::getPublicKey()` returns
  a raw binary public key. `base64_decode()` and `base64_encode()` should be used on the setter and getter, respectively,
  if you want to handle Base64 encoded string. These changes apply to all child classes as well (`DNSKEY` and `CDNSKEY`).
  * `DS::setDigest($digest)` now expects the raw binary form of the digest. Similarly `DS::getDigest` returns a raw binary
  digest. `hex2bin()` and `bin2hex()` should be used on the setter and getter, respectively, if you want to handle
  hexadecimal encoded strings. These changes apply to all child classes as well (`CDS`, `DLV` and `TA`).
  * `CERT::setCertificate($cert)` now expects the raw binary form of the certificate. Similarly `CERT::getCertificate()`
  returns a raw binary certificate. `base64_decode()` and `base64_encode()` should be used on the setter and getter, respectively,
  if you want to handle Base64 encoded string.
  
* New method `DS::calculateDigest(string $owner, DNSKEY $dnskey)` will calculate and set the digest using the DNSKEY rdata object. 