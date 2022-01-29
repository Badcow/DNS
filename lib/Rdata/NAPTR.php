<?php

declare(strict_types=1);

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

use Badcow\DNS\Message;
use Badcow\DNS\Parser\Tokens;
use Badcow\DNS\Validator;

/**
 * {@link https://tools.ietf.org/html/rfc3403}.
 */
class NAPTR implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'NAPTR';
    public const TYPE_CODE = 35;

    /**
     * A 16-bit unsigned integer specifying the order in which the NAPTR
     * records MUST be processed in order to accurately represent the
     * ordered list of Rules.  The ordering is from lowest to highest.
     * If two records have the same order value then they are considered
     * to be the same rule and should be selected based on the
     * combination of the Preference values and Services offered.
     *
     * @var int|null
     */
    private $order;

    /**
     * It is a 16-bit unsigned integer that specifies the order in which
     * NAPTR records with equal Order values SHOULD be processed, low
     * numbers being processed before high numbers.
     *
     * @var int
     */
    private $preference;

    /**
     * A <character-string> containing flags to control aspects of the
     * rewriting and interpretation of the fields in the record.  Flags
     * are single characters from the set A-Z and 0-9.  The case of the
     * alphabetic characters is not significant.  The field can be empty.
     *
     * @var string|null
     */
    private $flags;

    /**
     * A <character-string> that specifies the Service Parameters
     * applicable to this this delegation path.  It is up to the
     * Application Specification to specify the values found in this
     * field.
     *
     * @var string|null
     */
    private $services;

    /**
     * A <character-string> containing a substitution expression that is
     * applied to the original string held by the client in order to
     * construct the next domain name to lookup.
     *
     * @var string|null
     */
    private $regexp;

    /**
     * A <domain-name> which is the next domain-name to query for
     * depending on the potential values found in the flags field.  This
     * field is used when the regular expression is a simple replacement
     * operation.  Any value in this field MUST be a fully qualified
     * domain-name.
     *
     * @var string
     */
    private $replacement;

    /**
     * @return int
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setOrder(int $order): void
    {
        if ($order < 0 || $order > 65535) {
            throw new \InvalidArgumentException(sprintf('$order must be between 0 and 65535. "%d" given.', $order));
        }

        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getPreference(): ?int
    {
        return $this->preference;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setPreference(int $preference): void
    {
        if ($preference < 0 || $preference > 65535) {
            throw new \InvalidArgumentException(sprintf('$preference must be between 0 and 65535. "%d" given.', $preference));
        }

        $this->preference = $preference;
    }

    /**
     * @return string
     */
    public function getFlags(): ?string
    {
        return $this->flags;
    }

    /**
     * @param string $flags
     */
    public function setFlags(?string $flags): void
    {
        $this->flags = $flags;
    }

    /**
     * @return string
     */
    public function getServices(): ?string
    {
        return $this->services;
    }

    /**
     * @param string $services
     */
    public function setServices(?string $services): void
    {
        $this->services = $services;
    }

    /**
     * @return string
     */
    public function getRegexp(): ?string
    {
        return $this->regexp;
    }

    /**
     * @param string $regexp
     */
    public function setRegexp(?string $regexp): void
    {
        $this->regexp = $regexp;
    }

    /**
     * @return string
     */
    public function getReplacement(): ?string
    {
        return $this->replacement;
    }

    public function setReplacement(string $replacement): void
    {
        if (!Validator::resourceRecordName($replacement) && !Validator::fullyQualifiedDomainName($replacement) && '.' !== $replacement) {
            throw new \InvalidArgumentException(sprintf('Replacement must be a valid resource name. "%s" given.', $replacement));
        }

        $this->replacement = $replacement;
    }

    public function toText(): string
    {
        return sprintf(
            '%d %d "%s" "%s" "%s" %s',
            $this->order,
            $this->preference,
            $this->flags ?? '',
            $this->services ?? '',
            $this->regexp,
            $this->replacement
        );
    }

    public function toWire(): string
    {
        $encoded = pack('nn', $this->order, $this->preference);
        $encoded .= sprintf('"%s""%s""%s"', $this->flags ?? '', $this->services ?? '', $this->regexp);
        $encoded .= Message::encodeName($this->replacement);

        return $encoded;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setOrder((int) array_shift($rdata));
        $this->setPreference((int) array_shift($rdata));
        $this->setFlags(trim((string) array_shift($rdata), Tokens::DOUBLE_QUOTES));
        $this->setServices(trim((string) array_shift($rdata), Tokens::DOUBLE_QUOTES));
        $this->setRegexp(trim((string) array_shift($rdata), Tokens::DOUBLE_QUOTES));
        $this->setReplacement((string) array_shift($rdata));
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $integers = unpack('nOrder/nPreference', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 4;

        $this->setOrder($integers['Order']);
        $this->setPreference($integers['Preference']);
        $this->setFlags(self::extractText($rdata, $offset));
        $this->setServices(self::extractText($rdata, $offset));
        $this->setRegexp(self::extractText($rdata, $offset));
        $this->setReplacement(Message::decodeName($rdata, $offset));
    }

    /**
     * Extract text from within quotation marks and advance the offset.
     */
    private static function extractText(string $string, int &$offset): string
    {
        if (Tokens::DOUBLE_QUOTES !== $char = substr($string, $offset, 1)) {
            throw new \InvalidArgumentException(sprintf('The starting point of $string must be double quotes. "%s" given.', $char));
        }

        $value = '';
        ++$offset;

        while (Tokens::DOUBLE_QUOTES !== $char = substr($string, $offset, 1)) {
            $value .= $char;
            ++$offset;
        }

        ++$offset;

        return $value;
    }
}
