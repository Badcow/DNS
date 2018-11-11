<?php

namespace Badcow\DNS\Rdata;


use Badcow\DNS\ResourceRecord;

class APL implements RdataInterface, FormattableInterface
{
    use RdataTrait;
    use FormattableTrait;

    const TYPE = 'APL';

    /**
     * @var \IPBlock[]
     */
    private $includedAddressRanges = [];

    /**
     * @var \IPBlock[]
     */
    private $excludedAddressRanges = [];

    /**
     * @param \IPBlock $ipBlock
     * @param bool $included True if the resource exists within the range, False if the resource
     * is not within the range. I.E. the negation.
     */
    public function addAddressRange(\IPBlock $ipBlock, $included = true)
    {
        if ($included) {
            $this->includedAddressRanges[] = $ipBlock;
        } else {
            $this->excludedAddressRanges[] = $ipBlock;
        }
    }

    /**
     * @return \IPBlock[]
     */
    public function getIncludedAddressRanges(): array
    {
        return $this->includedAddressRanges;
    }

    /**
     * @return \IPBlock[]
     */
    public function getExcludedAddressRanges(): array
    {
        return $this->excludedAddressRanges;
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        $string = '';
        foreach ($this->includedAddressRanges as $ipBlock) {
            $string .= (4 === $ipBlock->getVersion())? '1:' : '2:';
            $string .= (string) $ipBlock.' ';
        }

        foreach ($this->excludedAddressRanges as $ipBlock) {
            $string .= (4 === $ipBlock->getVersion())? '!1:' : '!2:';
            $string .= (string) $ipBlock.' ';
        }

        return rtrim($string, ' ');
    }

    /**
     * {@inheritdoc}
     */
    public function outputFormatted()
    {
        $string = ResourceRecord::MULTILINE_BEGIN.PHP_EOL;
        foreach (explode(' ', $this->output()) as $block) {
            $string .= $this->makeLine($block);
        }

        return $string.str_repeat(' ', $this->padding).ResourceRecord::MULTILINE_END;
    }

    /**
     * {@inheritdoc}
     */
    public function longestVarLength()
    {
        $l = 0;
        foreach (explode(' ', $this->output()) as $block) {
            $l = ($l < strlen($block)) ? strlen($block) : $l;
        }

        return $l;
    }
}