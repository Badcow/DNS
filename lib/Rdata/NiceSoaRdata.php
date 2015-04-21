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

class NiceSoaRdata extends SoaRdata
{
    public static $format = <<<TXT
(
            %s ; MNAME
            %s ; RNAME
            %s ; SERIAL
            %s ; REFRESH
            %s ; RETRY
            %s ; EXPIRE
            %s ; MINIMUM
            )
TXT;


    /**
     * {@inheritdoc}
     */
    public function output()
    {
        $pad = $this->longestVarLength();

        return sprintf(
            self::$format,
            str_pad($this->getMname(), $pad),
            str_pad($this->getRname(), $pad),
            str_pad($this->getSerial(), $pad),
            str_pad($this->getRefresh(), $pad),
            str_pad($this->getRetry(), $pad),
            str_pad($this->getExpire(), $pad),
            str_pad($this->getMinimum(), $pad)
        );
    }

    /**
     * Determines the longest variable
     *
     * @return int
     */
    private function longestVarLength()
    {
        $l = 0;

        foreach (array(
                    $this->getMname(),
                    $this->getRname(),
                    $this->getSerial(),
                    $this->getRefresh(),
                    $this->getRetry(),
                    $this->getExpire(),
                    $this->getMinimum(),
                ) as $var) {
            $l = ($l < strlen($var)) ? strlen($var) : $l;
        }

        return $l;
    }
}
