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
        $master = sprintf(
            "\$ORIGIN %s\n\$TTL %s\n",
            $zone->getName(),
            $zone->getDefaultTtl()
        );

        foreach ($zone->getResourceRecords() as $rr) {
            /* @var $rr ResourceRecord */
            $master .= sprintf("%s %s %s %s %s",
                $rr->getName(),
                $rr->getTtl(),
                $rr->getClass(),
                $rr->getType(),
                $rr->getRdata()->output()
            );

            $master .= (null == $rr->getComment()) ? "\n" : sprintf("; %s\n", $rr->getComment());
        }

        return $master;
    }
}
