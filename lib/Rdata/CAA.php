<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

/**
 * Class CaaRdata.
 *
 * CAA is defined in RFC 6844
 *
 * @see https://tools.ietf.org/html/rfc6844
 *
 * @author Samuel Williams <sam@badcow.co>
 */
class CAA implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'CAA';

    const MAX_FLAG = 255;

    const TAG_ISSUE = 'issue';

    const TAG_ISSUEWILD = 'issuewild';

    const TAG_IODEF = 'iodef';

    const ALLOWED_TAGS = [self::TAG_ISSUE, self::TAG_ISSUEWILD, self::TAG_IODEF];

    /**
     * It is currently used to represent the critical flag
     *
     * @var int
     */
    private $flag;

    /**
     * An ASCII string that represents the identifier of the property represented by the record.
     * The RFC currently defines 3 available tags:
     *  - issue: explicity authorizes a single certificate authority to issue a certificate (any type) for the hostname.
     *  - issuewild: explicity authorizes a single certificate authority to issue a wildcard certificate (and only wildcard) for the hostname.
     *  - iodef: specifies a URL to which a certificate authority may report policy violations.
     *
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $value;

    /**
     * @return int
     */
    public function getFlag(): ?int
    {
        return $this->flag;
    }

    /**
     * @param int $flag
     *
     * @throws \InvalidArgumentException
     */
    public function setFlag(int $flag): void
    {
        if ($flag < 0 || $flag > static::MAX_FLAG) {
            throw new \InvalidArgumentException('Flag must be an unsigned integer on the range [0-255]');
        }

        $this->flag = $flag;
    }

    /**
     * @return string
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     *
     * @throws \InvalidArgumentException
     */
    public function setTag(string $tag): void
    {
        if (!in_array($tag, static::ALLOWED_TAGS)) {
            throw new \InvalidArgumentException('Tag can be one of this type '.implode(' ', static::ALLOWED_TAGS));
        }

        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return sprintf('%s %s "%s"',
            $this->flag,
            $this->tag,
            $this->value
        );
    }
}
