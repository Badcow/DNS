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

use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Parser\Tokens;
use Badcow\DNS\Rcode;
use Badcow\DNS\Validator;

/**
 * {@link https://tools.ietf.org/html/rfc2845}.
 */
class TSIG implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'TSIG';
    const TYPE_CODE = 250;

    /**
     * Name of the algorithm in domain name syntax.
     *
     * @var string
     */
    private $algorithmName;

    /**
     * @var \DateTime
     */
    private $timeSigned;

    /**
     * Seconds of error permitted in time signed.
     *
     * @var int
     */
    private $fudge;

    /**
     * Message authentication code.
     *
     * @var string
     */
    private $mac;

    /**
     * @var int
     */
    private $originalId;

    /**
     * @var int
     */
    private $error = Rcode::NOERROR;

    /**
     * @var string
     */
    private $otherData;

    /**
     * @return string
     */
    public function getAlgorithmName(): string
    {
        return $this->algorithmName;
    }

    /**
     * @param string $algorithmName
     */
    public function setAlgorithmName(string $algorithmName): void
    {
        if (!Validator::fullyQualifiedDomainName($algorithmName)) {
            throw new \InvalidArgumentException('Algorithm name must be in the form of a fully qualified domain name.');
        }

        $this->algorithmName = $algorithmName;
    }

    /**
     * @return \DateTime
     */
    public function getTimeSigned(): \DateTime
    {
        return $this->timeSigned;
    }

    /**
     * @param \DateTime $timeSigned
     */
    public function setTimeSigned(\DateTime $timeSigned): void
    {
        $this->timeSigned = $timeSigned;
    }

    /**
     * @return int
     */
    public function getFudge(): int
    {
        return $this->fudge;
    }

    /**
     * @param int $fudge
     */
    public function setFudge(int $fudge): void
    {
        if (!Validator::isUnsignedInteger($fudge, 16)) {
            throw new \InvalidArgumentException(sprintf('Fudge must be an unsigned 16-bit integer, "%d" given.', $fudge));
        }
        $this->fudge = $fudge;
    }

    /**
     * @return string
     */
    public function getMac(): string
    {
        return $this->mac;
    }

    /**
     * @param string $mac
     */
    public function setMac(string $mac): void
    {
        $this->mac = $mac;
    }

    /**
     * @return int
     */
    public function getOriginalId(): int
    {
        return $this->originalId;
    }

    /**
     * @param int $originalId
     */
    public function setOriginalId(int $originalId): void
    {
        if (!Validator::isUnsignedInteger($originalId, 16)) {
            throw new \InvalidArgumentException(sprintf('Original ID must be an unsigned 16-bit integer, "%d" given.', $originalId));
        }
        $this->originalId = $originalId;
    }

    /**
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @param int $error
     */
    public function setError(int $error): void
    {
        if (!Validator::isUnsignedInteger($error, 16)) {
            throw new \InvalidArgumentException(sprintf('Error must be an unsigned 16-bit integer, "%d" given.', $error));
        }
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getOtherData(): string
    {
        return $this->otherData;
    }

    /**
     * @param string $otherData
     */
    public function setOtherData(string $otherData): void
    {
        $this->otherData = $otherData;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%s %d %d %s %d %d %s',
            $this->algorithmName,
            $this->timeSigned->format('U'),
            $this->fudge,
            base64_encode($this->mac),
            $this->originalId,
            $this->error,
            base64_encode($this->otherData)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        $timeSigned = (int) $this->timeSigned->format('U');
        $hex1 = (0xffff00000000 & $timeSigned) >> 32;
        $hex2 = (0x0000ffff0000 & $timeSigned) >> 16;
        $hex3 = 0x00000000ffff & $timeSigned;

        $wire = self::encodeName($this->algorithmName);
        $wire .= pack('nnnnn', $hex1, $hex2, $hex3, $this->fudge, strlen($this->mac));
        $wire .= $this->mac;
        $wire .= pack('nnn', $this->originalId, $this->error, strlen($this->otherData));
        $wire .= $this->otherData;

        return $wire;
    }

    /**
     * {@inheritdoc}
     *
     * @return TSIG
     *
     * @throws ParseException
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $tsig = new self();
        $tsig->setAlgorithmName((string) array_shift($rdata));
        if (false === $timeSigned = \DateTime::createFromFormat('U', (string) array_shift($rdata))) {
            throw new ParseException('Unable to decode TSIG time signed.');
        }
        $tsig->setTimeSigned($timeSigned);
        $tsig->setFudge((int) array_shift($rdata));

        if (false === $mac = base64_decode((string) array_shift($rdata), true)) {
            throw new ParseException('Unable to decode TSIG MAC. Malformed base64 string.');
        }
        $tsig->setMac($mac);

        $tsig->setOriginalId((int) array_shift($rdata));
        $tsig->setError((int) array_shift($rdata));

        if (false === $otherData = base64_decode((string) array_shift($rdata), true)) {
            throw new ParseException('Unable to decode TSIG other data. Malformed base64 string.');
        }
        $tsig->setOtherData($otherData);

        return $tsig;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DecodeException
     *
     * @return TSIG
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $tsig = new self();

        $tsig->setAlgorithmName(self::decodeName($rdata, $offset));

        $args = unpack('n<hex1>/n<hex2>/n<hex3>/n<fudge>/n<macLen>', $rdata, $offset);
        $offset += 10;

        $timeSigned = ($args['<hex1>'] << 32) + ($args['<hex2>'] << 16) + $args['<hex3>'];
        if (false === $objTimeSigned = \DateTime::createFromFormat('U', (string) $timeSigned)) {
            throw new DecodeException(static::TYPE, $rdata);
        }

        $macLen = (int) $args['<macLen>'];
        $tsig->setFudge($args['<fudge>']);
        $tsig->setTimeSigned($objTimeSigned);
        $tsig->setMac(substr($rdata, $offset, $macLen));
        $offset += $macLen;

        $args = unpack('n<id>/n<error>/n<otherLen>', $rdata, (int) $offset);
        $offset += 6;
        $otherLen = (int) $args['<otherLen>'];

        $tsig->setOriginalId($args['<id>']);
        $tsig->setError($args['<error>']);
        $tsig->setOtherData(substr($rdata, (int) $offset, $otherLen));
        $offset += $otherLen;

        return $tsig;
    }
}
