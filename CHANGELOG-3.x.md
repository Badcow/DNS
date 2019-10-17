CHANGELOG for 3.x
=================
## 3.0
* Use `rlanvin/php-ip` version 2 [Issue #43](https://github.com/Badcow/DNS/issues/43).
* Namespace `Badcow\DNS\Rdata\DNSSEC` is deprecated and deleted. [Issue #42](https://github.com/Badcow/DNS/issues/42).
* Deleted deprecated constants:
  * `\Badcow\DNS\Parser\Normaliser::COMMENTS_NONE`
  * `\Badcow\DNS\Parser\Normaliser::COMMENTS_END_OF_RECORD_ENTRY`
  * `\Badcow\DNS\Parser\Normaliser::COMMENTS_WITHIN_MULTILINE`
  * `\Badcow\DNS\Parser\Normaliser::COMMENTS_WITHOUT_RECORD_ENTRY`
  * `\Badcow\DNS\Parser\Normaliser::COMMENTS_ALL`
* Upgraded to PHPUnit 8.
* Enforce strict_types [Issue #37](https://github.com/Badcow/DNS/issues/37).