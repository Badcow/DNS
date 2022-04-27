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
use Badcow\DNS\Validator;

/**
 * {@link https://tools.ietf.org/html/rfc8005}.
 */
class HIP implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'HIP';
    public const TYPE_CODE = 55;

    /**
     * @var int
     */
    private $publicKeyAlgorithm;

    /**
     * @var string
     */
    private $hostIdentityTag;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string[]
     */
    private $rendezvousServers = [];

    public function getPublicKeyAlgorithm(): int
    {
        return $this->publicKeyAlgorithm;
    }

    public function setPublicKeyAlgorithm(int $publicKeyAlgorithm): void
    {
        $this->publicKeyAlgorithm = $publicKeyAlgorithm;
    }

    public function getHostIdentityTag(): string
    {
        return $this->hostIdentityTag;
    }

    public function setHostIdentityTag(string $hostIdentityTag): void
    {
        $this->hostIdentityTag = $hostIdentityTag;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function addRendezvousServer(string $server): void
    {
        if (!Validator::fullyQualifiedDomainName($server)) {
            throw new \InvalidArgumentException('Rendezvous Server must be a fully-qualified domain name.');
        }

        $this->rendezvousServers[] = $server;
    }

    /**
     * @return string[]
     */
    public function getRendezvousServers(): array
    {
        return $this->rendezvousServers;
    }

    /**
     * Clear all rendezvous servers from the record.
     */
    public function clearRendezvousServer(): void
    {
        $this->rendezvousServers = [];
    }

    public function toText(): string
    {
        return sprintf(
            '%d %s %s %s',
            $this->publicKeyAlgorithm,
            bin2hex($this->hostIdentityTag),
            base64_encode($this->publicKey),
            implode(Tokens::SPACE, $this->rendezvousServers)
        );
    }

    public function toWire(): string
    {
        $rdata = pack(
            'CCn',
            strlen($this->hostIdentityTag),
            $this->publicKeyAlgorithm,
            strlen($this->publicKey)
        );

        $rdata .= $this->hostIdentityTag;
        $rdata .= $this->publicKey;
        foreach ($this->rendezvousServers as $server) {
            $rdata .= Message::encodeName($server);
        }

        return $rdata;
    }

    /**
     * @throws ParseException
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setPublicKeyAlgorithm((int) array_shift($rdata));

        if (false === $hostIdentityTag = @hex2bin((string) array_shift($rdata))) {
            throw new ParseException(sprintf('Unable to parse host identity tag of rdata string "%s".', $text));
        }
        $this->setHostIdentityTag($hostIdentityTag);

        if (false === $publicKey = base64_decode((string) array_shift($rdata), true)) {
            throw new ParseException(sprintf('Unable to parse public key of rdata string "%s".', $text));
        }
        $this->setPublicKey($publicKey);
        array_map([$this, 'addRendezvousServer'], $rdata);
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $end = $offset + ($rdLength ?? strlen($rdata));
        if (false === $integers = unpack('C<hitLen>/C<algorithm>/n<pkLen>', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 4;
        $hitLen = (int) $integers['<hitLen>'];
        $pkLen = (int) $integers['<pkLen>'];

        $this->setPublicKeyAlgorithm((int) $integers['<algorithm>']);

        $this->setHostIdentityTag(substr($rdata, $offset, $hitLen));
        $offset += $hitLen;

        $this->setPublicKey(substr($rdata, $offset, $pkLen));
        $offset += $pkLen;

        while ($offset < $end) {
            $this->addRendezvousServer(Message::decodeName($rdata, $offset));
        }
    }
}
