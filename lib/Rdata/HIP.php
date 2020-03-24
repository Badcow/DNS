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
use Badcow\DNS\Validator;

/**
 * {@link https://tools.ietf.org/html/rfc8005}.
 */
class HIP implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'HIP';
    const TYPE_CODE = 55;

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

    /**
     * @return int
     */
    public function getPublicKeyAlgorithm(): int
    {
        return $this->publicKeyAlgorithm;
    }

    /**
     * @param int $publicKeyAlgorithm
     */
    public function setPublicKeyAlgorithm(int $publicKeyAlgorithm): void
    {
        $this->publicKeyAlgorithm = $publicKeyAlgorithm;
    }

    /**
     * @return string
     */
    public function getHostIdentityTag(): string
    {
        return $this->hostIdentityTag;
    }

    /**
     * @param string $hostIdentityTag
     */
    public function setHostIdentityTag(string $hostIdentityTag): void
    {
        $this->hostIdentityTag = $hostIdentityTag;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @param string $server
     */
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

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%d %s %s %s',
            $this->publicKeyAlgorithm,
            bin2hex($this->hostIdentityTag),
            base64_encode($this->publicKey),
            implode(Tokens::SPACE, $this->rendezvousServers)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        $rdata = pack('CCn',
            strlen($this->hostIdentityTag),
            $this->publicKeyAlgorithm,
            strlen($this->publicKey)
        );

        $rdata .= $this->hostIdentityTag;
        $rdata .= $this->publicKey;
        foreach ($this->rendezvousServers as $server) {
            $rdata .= self::encodeName($server);
        }

        return $rdata;
    }

    /**
     * {@inheritdoc}
     *
     * @return HIP
     *
     * @throws ParseException
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $hip = new self();
        $hip->setPublicKeyAlgorithm((int) array_shift($rdata));

        if (false === $hostIdentityTag = @hex2bin((string) array_shift($rdata))) {
            throw new ParseException(sprintf('Unable to parse host identity tag of rdata string "%s".', $text));
        }
        $hip->setHostIdentityTag($hostIdentityTag);

        if (false === $publicKey = base64_decode((string) array_shift($rdata), true)) {
            throw new ParseException(sprintf('Unable to parse public key of rdata string "%s".', $text));
        }
        $hip->setPublicKey($publicKey);
        array_map([$hip, 'addRendezvousServer'], $rdata);

        return $hip;
    }

    /**
     * {@inheritdoc}
     *
     * @return HIP
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $hip = new self();

        $end = $offset + ($rdLength ?? strlen($rdata));
        $integers = unpack('C<hitLen>/C<algorithm>/n<pkLen>', $rdata, $offset);
        $offset += 4;
        $hitLen = (int) $integers['<hitLen>'];
        $pkLen = (int) $integers['<pkLen>'];

        $hip->setPublicKeyAlgorithm((int) $integers['<algorithm>']);

        $hip->setHostIdentityTag(substr($rdata, $offset, $hitLen));
        $offset += $hitLen;

        $hip->setPublicKey(substr($rdata, $offset, $pkLen));
        $offset += $pkLen;

        while ($offset < $end) {
            $hip->addRendezvousServer(self::decodeName($rdata, $offset));
        }

        return $hip;
    }
}
