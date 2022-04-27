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

namespace Badcow\DNS\Edns\Option;

interface OptionInterface
{
    /**
     * Get the string representation of the Option type.
     *
     * @return string Option name type, e.g. "COOKIE", "CLIENT_SUBNET", etc.
     */
    public function getName(): string;

    /**
     * Get the integer name code of the Option as defined by IANA.
     *
     * @return int IANA Option type code
     */
    public function getCode(): int;

    /**
     * Return a DNS Server response formatted representation of the Option.
     *
     * @return string packed binary form of Option
     */
    public function toWire(): string;

    /**
     * Populate Option from its wire representation.
     *
     * @param string   $optionValue  packed binary form of Option
     * @param int      $offset       the current offset or pointer, position of the start of Option relative to the whole $optionValue string
     * @param int|null $optionLength the length of the Option string, if null it is taken to be the whole $optionValue parameter string
     */
    public function fromWire(string $optionValue, int &$offset = 0, ?int $optionLength = null): void;
}
