<?php

namespace Badcow\DNS\Rdata;

class NiceSoaRdata extends SoaRdata
{
    static $format = <<<TXT
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
        $vars = array(
            $this->getMname(),
            $this->getRname(),
            $this->getSerial(),
            $this->getRefresh(),
            $this->getRetry(),
            $this->getExpire(),
            $this->getMinimum(),
        );

        $l = 0;

        foreach ($vars as $var) {
            $l = ($l < strlen($var)) ? strlen($var) : $l;
        }

        return $l;
    }
}
