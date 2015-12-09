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

use Badcow\DNS\ResourceRecord;

trait FormattableTrait
{
    /**
     * The amount of left padding before an Rdata component.
     *
     * @var int
     */
    private $padding;

    /**
     * @param int $padding
     */
    public function setPadding($padding)
    {
        $this->padding = (int) $padding;
    }

    /**
     * Get the length of the longest variable.
     *
     * @return mixed
     */
    abstract public function longestVarLength();

    /**
     * Returns a padded line with comment.
     *
     * @param string $text
     * @param string $comment
     *
     * @return string
     */
    private function makeLine($text, $comment = null)
    {
        $pad = $this->longestVarLength();
        $output = str_repeat(' ', $this->padding).
                    str_pad($text, $pad);

        if (null !== $comment) {
            $output .= ' '.ResourceRecord::COMMENT_DELIMINATOR.$comment;
        }
        $output .= PHP_EOL;

        return $output;
    }
}
