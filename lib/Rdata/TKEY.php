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
 * {@link https://tools.ietf.org/html/rfc2930}.
 */
class TKEY implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'TKEY';
    public const TYPE_CODE = 249;

    /**
     * The algorithm name is in the form of a domain name with the same
     * meaning as in [RFC 2845]{@link https://tools.ietf.org/html/rfc2845}.
     * The algorithm determines how the secret keying material agreed to
     * using the TKEY RR is actually used to derive the algorithm specific key.
     *
     * @var string
     */
    private $algorithm;

    /**
     * @var \DateTime
     */
    private $inception;

    /**
     * @var \DateTime
     */
    private $expiration;

    /**
     * The mode field specifies the general scheme for key agreement or the
     * purpose of the TKEY DNS message. 16-bit integer.
     *
     * The following values of the Mode octet are defined, available, or reserved:
     *
     *      Value    Description
     *      -----    -----------
     *      0        - reserved, see section 7
     *      1       server assignment
     *      2       Diffie-Hellman exchange
     *      3       GSS-API negotiation
     *      4       resolver assignment
     *      5       key deletion
     *      6-65534   - available, see section 7
     *      65535     - reserved, see section 7
     *
     * @var int
     */
    private $mode;

    /**
     * The error code field is an extended RCODE.  The following values are defined:.
     *
     *      Value   Description
     *      -----   -----------
     *      0       - no error
     *      1-15   a non-extended RCODE
     *      16     BADSIG   (TSIG)
     *      17     BADKEY   (TSIG)
     *      18     BADTIME  (TSIG)
     *      19     BADMODE
     *      20     BADNAME
     *      21     BADALG
     *
     * @var int
     */
    private $error = 0;

    /**
     * @var string
     */
    private $keyData;

    /**
     * @var string
     */
    private $otherData;

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function setAlgorithm(string $algorithm): void
    {
        if (!Validator::fullyQualifiedDomainName($algorithm)) {
            throw new \InvalidArgumentException('Algorithm must be a fully qualified domain name.');
        }
        $this->algorithm = $algorithm;
    }

    public function getInception(): \DateTime
    {
        return $this->inception;
    }

    public function setInception(\DateTime $inception): void
    {
        $this->inception = $inception;
    }

    public function getExpiration(): \DateTime
    {
        return $this->expiration;
    }

    public function setExpiration(\DateTime $expiration): void
    {
        $this->expiration = $expiration;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setMode(int $mode): void
    {
        if (!Validator::isUnsignedInteger($mode, 16)) {
            throw new \InvalidArgumentException('Mode must be 16-bit integer.');
        }
        $this->mode = $mode;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function setError(int $error): void
    {
        if (!Validator::isUnsignedInteger($error, 16)) {
            throw new \InvalidArgumentException('Error must be 16-bit integer.');
        }
        $this->error = $error;
    }

    public function getKeyData(): string
    {
        return $this->keyData;
    }

    /**
     * @param string $keyData binary stream
     */
    public function setKeyData(string $keyData): void
    {
        $this->keyData = $keyData;
    }

    public function getOtherData(): string
    {
        return $this->otherData;
    }

    /**
     * @param string $otherData binary stream
     */
    public function setOtherData(string $otherData): void
    {
        $this->otherData = $otherData;
    }

    public function toText(): string
    {
        return sprintf(
            '%s %d %d %d %d %s %s',
            $this->algorithm,
            $this->inception->format('U'),
            $this->expiration->format('U'),
            $this->mode,
            $this->error,
            base64_encode($this->keyData),
            base64_encode($this->otherData)
        );
    }

    public function toWire(): string
    {
        $wire = Message::encodeName($this->algorithm);
        $wire .= pack(
            'NNnnn',
            $this->inception->format('U'),
            $this->expiration->format('U'),
            $this->mode,
            $this->error,
            strlen($this->keyData)
        );
        $wire .= $this->keyData;
        $wire .= pack('n', strlen($this->otherData));
        $wire .= $this->otherData;

        return $wire;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setAlgorithm((string) array_shift($rdata));
        if (false === $inception = \DateTime::createFromFormat('U', (string) array_shift($rdata))) {
            throw new \UnexpectedValueException('Unable to parse inception date of TKEY Rdata.');
        }
        $this->setInception($inception);

        if (false === $expiration = \DateTime::createFromFormat('U', (string) array_shift($rdata))) {
            throw new \UnexpectedValueException('Unable to parse expiration date of TKEY Rdata.');
        }
        $this->setExpiration($expiration);

        $this->setMode((int) array_shift($rdata));
        $this->setError((int) array_shift($rdata));
        $this->setKeyData((string) base64_decode((string) array_shift($rdata)));
        $this->setOtherData((string) base64_decode((string) array_shift($rdata)));
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $algorithm = Message::decodeName($rdata, $offset);
        if (false === $integers = unpack('N<inception>/N<expiration>/n<mode>/n<error>/n<keySize>', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 14;
        $keySize = (int) $integers['<keySize>'];
        $keyData = substr($rdata, $offset, $keySize);
        $offset = (int) $offset + $keySize;
        if (false === $otherDataSize = unpack('n', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 2;
        $otherData = substr($rdata, $offset, $otherDataSize[1]);
        $offset += $otherDataSize[1];

        $this->setAlgorithm($algorithm);

        if (false === $inception = \DateTime::createFromFormat('U', (string) $integers['<inception>'])) {
            throw new \UnexpectedValueException('Unable to parse inception date of TKEY Rdata.');
        }
        $this->setInception($inception);

        if (false === $expiration = \DateTime::createFromFormat('U', (string) $integers['<expiration>'])) {
            throw new \UnexpectedValueException('Unable to parse expiration date of TKEY Rdata.');
        }
        $this->setExpiration($expiration);

        $this->setMode((int) $integers['<mode>']);
        $this->setError((int) $integers['<error>']);
        $this->setKeyData($keyData);
        $this->setOtherData($otherData);
    }
}
