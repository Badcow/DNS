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

    public const TYPE = 'TSIG';
    public const TYPE_CODE = 250;

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

    public function getAlgorithmName(): string
    {
        return $this->algorithmName;
    }

    public function setAlgorithmName(string $algorithmName): void
    {
        if (!Validator::fullyQualifiedDomainName($algorithmName)) {
            throw new \InvalidArgumentException('Algorithm name must be in the form of a fully qualified domain name.');
        }

        $this->algorithmName = $algorithmName;
    }

    public function getTimeSigned(): \DateTime
    {
        return $this->timeSigned;
    }

    public function setTimeSigned(\DateTime $timeSigned): void
    {
        $this->timeSigned = $timeSigned;
    }

    public function getFudge(): int
    {
        return $this->fudge;
    }

    public function setFudge(int $fudge): void
    {
        if (!Validator::isUnsignedInteger($fudge, 16)) {
            throw new \InvalidArgumentException(sprintf('Fudge must be an unsigned 16-bit integer, "%d" given.', $fudge));
        }
        $this->fudge = $fudge;
    }

    public function getMac(): string
    {
        return $this->mac;
    }

    public function setMac(string $mac): void
    {
        $this->mac = $mac;
    }

    public function getOriginalId(): int
    {
        return $this->originalId;
    }

    public function setOriginalId(int $originalId): void
    {
        if (!Validator::isUnsignedInteger($originalId, 16)) {
            throw new \InvalidArgumentException(sprintf('Original ID must be an unsigned 16-bit integer, "%d" given.', $originalId));
        }
        $this->originalId = $originalId;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function setError(int $error): void
    {
        if (!Validator::isUnsignedInteger($error, 16)) {
            throw new \InvalidArgumentException(sprintf('Error must be an unsigned 16-bit integer, "%d" given.', $error));
        }
        $this->error = $error;
    }

    public function getOtherData(): string
    {
        return $this->otherData;
    }

    public function setOtherData(string $otherData): void
    {
        $this->otherData = $otherData;
    }

    public function toText(): string
    {
        return sprintf(
            '%s %d %d %s %d %d %s',
            $this->algorithmName,
            $this->timeSigned->format('U'),
            $this->fudge,
            base64_encode($this->mac),
            $this->originalId,
            $this->error,
            base64_encode($this->otherData)
        );
    }

    public function toWire(): string
    {
        $timeSigned = (int) $this->timeSigned->format('U');
        $hex1 = (0xFFFF00000000 & $timeSigned) >> 32;
        $hex2 = (0x0000FFFF0000 & $timeSigned) >> 16;
        $hex3 = 0x00000000FFFF & $timeSigned;

        $wire = Message::encodeName($this->algorithmName);
        $wire .= pack('nnnnn', $hex1, $hex2, $hex3, $this->fudge, strlen($this->mac));
        $wire .= $this->mac;
        $wire .= pack('nnn', $this->originalId, $this->error, strlen($this->otherData));
        $wire .= $this->otherData;

        return $wire;
    }

    /**
     * @throws ParseException
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setAlgorithmName((string) array_shift($rdata));
        if (false === $timeSigned = \DateTime::createFromFormat('U', (string) array_shift($rdata))) {
            throw new ParseException('Unable to decode TSIG time signed.');
        }
        $this->setTimeSigned($timeSigned);
        $this->setFudge((int) array_shift($rdata));

        if (false === $mac = base64_decode((string) array_shift($rdata), true)) {
            throw new ParseException('Unable to decode TSIG MAC. Malformed base64 string.');
        }
        $this->setMac($mac);

        $this->setOriginalId((int) array_shift($rdata));
        $this->setError((int) array_shift($rdata));

        if (false === $otherData = base64_decode((string) array_shift($rdata), true)) {
            throw new ParseException('Unable to decode TSIG other data. Malformed base64 string.');
        }
        $this->setOtherData($otherData);
    }

    /**
     * @throws DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $this->setAlgorithmName(Message::decodeName($rdata, $offset));

        if (false === $args = unpack('n<hex1>/n<hex2>/n<hex3>/n<fudge>/n<macLen>', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 10;

        $timeSigned = ($args['<hex1>'] << 32) + ($args['<hex2>'] << 16) + $args['<hex3>'];
        if (false === $objTimeSigned = \DateTime::createFromFormat('U', (string) $timeSigned)) {
            throw new DecodeException(static::TYPE, $rdata);
        }

        $macLen = (int) $args['<macLen>'];
        $this->setFudge($args['<fudge>']);
        $this->setTimeSigned($objTimeSigned);
        $this->setMac(substr($rdata, $offset, $macLen));
        $offset += $macLen;

        if (false === $args = unpack('n<id>/n<error>/n<otherLen>', $rdata, (int) $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 6;
        $otherLen = (int) $args['<otherLen>'];

        $this->setOriginalId($args['<id>']);
        $this->setError($args['<error>']);
        $this->setOtherData(substr($rdata, (int) $offset, $otherLen));
        $offset += $otherLen;
    }
}
