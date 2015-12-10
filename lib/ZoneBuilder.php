<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS;

class ZoneBuilder implements ZoneBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ZoneInterface $zone)
    {
        $master = '$ORIGIN ' . $zone->getName() . PHP_EOL .
                    '$TTL ' . $zone->getDefaultTtl() . PHP_EOL;

        foreach ($zone->getResourceRecords() as $rr) {
            /* @var $rr ResourceRecord */
            if (null !== $rr->getRdata()) {
                $master .= preg_replace('/\s+/', ' ', trim(sprintf('%s %s %s %s %s',
                    $rr->getName(),
                    $rr->getTtl(),
                    $rr->getClass(),
                    $rr->getType(),
                    $rr->getRdata()->output()
                )));
            }

            if (null !== $rr->getComment()) {
                $master .= ResourceRecord::COMMENT_DELIMINATOR . $rr->getComment();
            }

            $master .= PHP_EOL;
        }

        return $master;
    }
}
