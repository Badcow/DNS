<?php

namespace Badcow\DNS\Rdata;

class NiceSoaRdata extends SoaRdata
{
    private $format = <<<TXT
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
        return sprintf(
            $this->format,
            $this->getMname(),
            $this->getRname(),
            $this->getSerial(),
            $this->getRefresh(),
            $this->getRetry(),
            $this->getExpire(),
            $this->getMinimum()
        );
    }
}
