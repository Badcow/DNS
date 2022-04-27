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

trait OptionTrait
{
    public function getName(): string
    {
        /* @const NAME */
        return static::NAME;
    }

    public function getCode(): int
    {
        /* @const TYPE_CODE */
        return static::CODE;
    }
}
