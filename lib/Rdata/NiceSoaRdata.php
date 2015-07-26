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
    /**
     * @var int
     */
    private $padding = 12;

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        $pad = $this->longestVarLength();
        $leftPadding = str_repeat(' ', $this->padding);

        return '(' . PHP_EOL .
            $leftPadding . str_pad($this->getMname(), $pad)   . ' ; MNAME' . PHP_EOL .
            $leftPadding . str_pad($this->getRname(), $pad)   . ' ; RNAME' . PHP_EOL .
            $leftPadding . str_pad($this->getSerial(), $pad)  . ' ; SERIAL' . PHP_EOL .
            $leftPadding . str_pad($this->getRefresh(), $pad) . ' ; REFRESH' . PHP_EOL .
            $leftPadding . str_pad($this->getRetry(), $pad)   . ' ; RETRY' . PHP_EOL .
            $leftPadding . str_pad($this->getExpire(), $pad)  . ' ; EXPIRE' . PHP_EOL .
            $leftPadding . str_pad($this->getMinimum(), $pad) . ' ; MINIMUM' . PHP_EOL .
            $leftPadding . ')';
    }

    /**
     * @param int $leftPadding
     */
    public function setPadding($leftPadding)
    {
        $this->padding = (int) $leftPadding;
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
