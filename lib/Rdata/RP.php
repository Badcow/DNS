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

/**
 * {@link https://tools.ietf.org/html/rfc1183}.
 */
class RP implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'RP';
    public const TYPE_CODE = 17;

    /**
     * @var string
     */
    private $mailboxDomainName;

    /**
     * @var string
     */
    private $txtDomainName;

    public function getMailboxDomainName(): string
    {
        return $this->mailboxDomainName;
    }

    public function setMailboxDomainName(string $mailboxDomainName): void
    {
        $this->mailboxDomainName = $mailboxDomainName;
    }

    public function getTxtDomainName(): string
    {
        return $this->txtDomainName;
    }

    public function setTxtDomainName(string $txtDomainName): void
    {
        $this->txtDomainName = $txtDomainName;
    }

    public function toText(): string
    {
        return sprintf('%s %s', $this->mailboxDomainName, $this->txtDomainName);
    }

    public function toWire(): string
    {
        return Message::encodeName($this->mailboxDomainName).Message::encodeName($this->txtDomainName);
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setMailboxDomainName($rdata[0]);
        $this->setTxtDomainName($rdata[1]);
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $this->setMailboxDomainName(Message::decodeName($rdata, $offset));
        $this->setTxtDomainName(Message::decodeName($rdata, $offset));
    }
}
