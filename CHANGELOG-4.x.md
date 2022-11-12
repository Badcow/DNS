CHANGELOG for 4.x
=================
## 4.2.1
* [Pull #113](https://github.com/Badcow/DNS/pull/113/) Fix to prevent double stripping of backslashes. (Thank you, [SloEddy](https://github.com/SloEddy))
## 4.2.0
* Deprecate support for PHP 7
* [Issue #104](https://github.com/Badcow/DNS/issues/96) Support EDNS (Thank you, [yeganemehr](https://github.com/yeganemehr))
* [Pull #110](https://github.com/Badcow/DNS/pull/110/) Bumped PHP-IP to version 3 (Thank you, [necrogami](https://github.com/necrogami))
## 4.1.1
* [Issue #96](https://github.com/Badcow/DNS/issues/96) - Use the correct version of [rlanvin/php-ip](https://github.com/rlanvin/php-ip)
for PHP 8.0. (Thank you, [AdnanHussainTurki](https://github.com/AdnanHussainTurki))
* Switch to Github Workflows from Travis and Scrutinizer.
## 4.1
* [Issue #88](https://github.com/Badcow/DNS/issues/88) - TXT Rdata now supports unquoted contiguous strings. (Thank you,
[Lorenz Bausch](https://github.com/lorenzbausch) - for bringing this to issue to light).
* [Issue #89](https://github.com/Badcow/DNS/issues/89) - Fixes issue where TXT record with number as value throws exception
(Thank you, [Lorenz Bausch](https://github.com/lorenzbausch) for bringing this to issue to light).
* [PR #90](https://github.com/Badcow/DNS/pull/90) - Do not add double dot when `$ORIGIN` is the root domain ("."). (Thank you,
[Cedric Dubois](https://github.com/cedricdubois))
* [Issue #91](https://github.com/Badcow/DNS/issues/91) - Use `christian-riesen/base32` instead of `ademarre/binary-to-text-php`.
* Explicit support for PHP 8.
* Throw exception if unpack() returns false. This fixes a litany of new PHPStan errors that are being seen in development.
## 4.0
* `Badcow\DNS\Rdata\RdataInterface::fromWire` is no longer static and does not return an instance of the class. You now
must instantiate the object first, and then call `fromWire` method. All parameters are the same.
* `Badcow\DNS\Rdata\RdataInterface::fromText` is no longer static and does not return an instance of the class. You now
must instantiate the object first, and then call `fromText` method. All parameters are the same.
* Deleted `Bacow\DNS\Rdata\Algorithms`, use `Bacow\DNS\Algorithms` instead. 
* [PR #73](https://github.com/Badcow/DNS/pull/73) Resolves issue where records with integers are not parsed correctly.
(Thank you, [Hossein Taleghani](https://github.com/a3dho3yn))
* [Issue #63](https://github.com/Badcow/DNS/issues/63) - Consistent handling ok keys, signatures and digests.
  * `KEY::setPublicKey($key)` now expects the raw binary form of the public key. Similarly `KEY::getPublicKey()` returns
  a raw binary public key. `base64_decode()` and `base64_encode()` should be used on the setter and getter, respectively,
  if you want to handle Base64 encoded string. These changes apply to all child classes as well (`DNSKEY` and `CDNSKEY`).
  * `DS::setDigest($digest)` now expects the raw binary form of the digest. Similarly `DS::getDigest` returns a raw binary
  digest. `hex2bin()` and `bin2hex()` should be used on the setter and getter, respectively, if you want to handle
  hexadecimal encoded strings. These changes apply to all child classes as well (`CDS`, `DLV` and `TA`).
  * `CERT::setCertificate($cert)` now expects the raw binary form of the certificate. Similarly `CERT::getCertificate()`
  returns a raw binary certificate. `base64_decode()` and `base64_encode()` should be used on the setter and getter, respectively,
  if you want to handle Base64 encoded strings.
  * `DHCID::setDigest($digest)` now expects the raw binary form of the digest. Similarly `DHCID::getDigest` returns a raw
  binary digest. `hex2bin()` and `bin2hex()` should be used on the setter and getter, respectively, if you want to handle
  hexadecimal encoded strings.
  * `IPSECKEY::setPublicKey($key)` now expects the raw binary form of the public key. Similarly `IPSECKEY::getPublicKey()`
  returns a raw binary public key. `base64_decode()` and `base64_encode()` should be used on the setter and getter, respectively,
  if you want to handle Base64 encoded strings.
  * `NSEC3::setNextHashedOwner($key)` now expects the raw binary form of the hash. Similarly `NSEC3::getNextHashedOwner()`
  returns a raw binary hash. `NSEC3::base32decode()` and `NSEC3::base32encode()` should be used on the setter and getter,
  respectively, if you want to handle Base32 encoded strings.
  * `RRSIG::setSignature($signature)` now expects the raw binary form of the signature. Similarly `RRSIG::getSignature()` returns
  a raw binary signature. `base64_decode()` and `base64_encode()` should be used on the setter and getter, respectively,
  if you want to handle Base64 encoded string. These changes apply to the child class `SIG` as well.
  * `SSHFP::setFingerprint($fingerprint)` now expects the raw binary form of the fingerprint. Similarly `SSHFP::getFingerprint`
  returns a raw binary fingerprint. `hex2bin()` and `bin2hex()` should be used on the setter and getter, respectively,
  if you want to handle hexadecimal encoded strings.
* New method `DS::calculateDigest(string $owner, DNSKEY $dnskey)` will calculate and set the digest using the DNSKEY rdata object. 
* [Issue #75](https://github.com/Badcow/DNS/issues/75) - Resolves issue where RRSIG records are not process correctly.
(Thank you, [emkookmer](https://github.com/emkookmer))
* NSEC3 has new parameter `$nextOwnerName` with respective setter and getters `NSEC3::setNextOwnerName($nextOwnerName)`
  and `NSEC3::getNextOwnerName()`. This new parameter will not be rendered in the rdata text or wire formats, but can be
  used to calculate the `nextOwnerHashedName`.
* NSEC3 has new method `NSEC3::calculateNextOwnerHash()` to calculate and set `NSEC3::nextOwnerHash`. Requires
  `NSEC3::salt`, `NSEC3::nextOwnerName`, and `NSEC3::iterations` to be set before calling method.
* `Factory::NSEC3()` has been changed to take only the following parameters:
  * `[bool]$unsignedDelegationsCovered`
  * `[int]$iterations`
  * `[string]$salt`
  * `[string]$nextOwnerName`
  * `[array] $types`
* [Issue #70](https://github.com/Badcow/DNS/issues/70) - Multiple $ORIGIN declarations are now supported.
(Thank you, [MikeAT](https://github.com/MikeAT))
* [Issue #80](https://github.com/Badcow/DNS/issues/80) - Parser now supports the $INCLUDE directive to import and parse
child or subdomain zone files.
* [PR #82](https://github.com/Badcow/DNS/pull/82) - Fix character escaping in TXT records. (Thank you, [@fbett](https://github.com/fbett))
* [Issue #84](https://github.com/Badcow/DNS/issues/84) - `TXT::toText()` now splits string into 255-byte chunks. (Thank you, [@fbett](https://github.com/fbett))
* [Issue #85](https://github.com/Badcow/DNS/issues/85) - `Badow\DNS\AlignedBuilder` now has finer controls. You can now
  define the order of rendering Resource Records and add or change Rdata output formatters (see `Docs/AlignedZoneBuilder`).
* `Badow\DNS\AlignedBuilder` cannot be called statically anymore. It must be instantiated.
