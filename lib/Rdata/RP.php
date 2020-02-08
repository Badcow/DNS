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

use Badcow\DNS\Parser\Tokens;

/**
 * {@link https://tools.ietf.org/html/rfc1183}.
 */
class RP implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'RP';
    const TYPE_CODE = 17;

    /**
     * @var string
     */
    private $mailboxDomainName;

    /**
     * @var string
     */
    private $txtDomainName;

    /**
     * @return string
     */
    public function getMailboxDomainName(): string
    {
        return $this->mailboxDomainName;
    }

    /**
     * @param string $mailboxDomainName
     */
    public function setMailboxDomainName(string $mailboxDomainName): void
    {
        $this->mailboxDomainName = $mailboxDomainName;
    }

    /**
     * @return string
     */
    public function getTxtDomainName(): string
    {
        return $this->txtDomainName;
    }

    /**
     * @param string $txtDomainName
     */
    public function setTxtDomainName(string $txtDomainName): void
    {
        $this->txtDomainName = $txtDomainName;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%s %s', $this->mailboxDomainName, $this->txtDomainName);
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return self::encodeName($this->mailboxDomainName).self::encodeName($this->txtDomainName);
    }

    /**
     * {@inheritdoc}
     *
     * @return RP
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $rp = new self();
        $rp->setMailboxDomainName($rdata[0]);
        $rp->setTxtDomainName($rdata[1]);

        return $rp;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $rp = new self();
        $rp->setMailboxDomainName(self::decodeName($rdata, $offset));
        $rp->setTxtDomainName(self::decodeName($rdata, $offset));

        return $rp;
    }
}
