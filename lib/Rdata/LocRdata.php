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

/**
 * Class LocRdata
 *
 * Mechanism to allow the DNS to carry location
 * information about hosts, networks, and subnets.
 *
 * @link http://tools.ietf.org/html/rfc1876
 *
 * @package Badcow\DNS\Rdata
 */
class LocRdata implements RdataInterface, FormattableInterface
{
    use RdataTrait, FormattableTrait;

    const TYPE = 'LOC';

    const LATITUDE = 'LATITUDE';

    const LONGITUDE = 'LONGITUDE';

    const FORMAT_DECIMAL = 'DECIMAL';

    const FORMAT_DMS = 'DMS';

    /**
     * @var double
     */
    private $latitude;

    /**
     * @var double
     */
    private $longitude;

    /**
     * @var double
     */
    private $altitude = 0.0;

    /**
     * @var double
     */
    private $size = 1.0;

    /**
     * @var double
     */
    private $horizontalPrecision = 10000.0;

    /**
     * @var double
     */
    private $verticalPrecision = 10.0;

    /**
     * @param double $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = (double) $latitude;
    }

    /**
     * @param string $format
     * @return float|string
     */
    public function getLatitude($format = self::FORMAT_DECIMAL)
    {
        if ($format === self::FORMAT_DMS) {
            return $this->toDms($this->latitude, self::LATITUDE);
        }

        return $this->latitude;
    }

    /**
     * @param double $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = (double) $longitude;
    }

    /**
     * @param string $format
     * @return float|string
     */
    public function getLongitude($format = self::FORMAT_DECIMAL)
    {
        if ($format === self::FORMAT_DMS) {
            return $this->toDms($this->longitude, self::LONGITUDE);
        }

        return $this->longitude;
    }

    /**
     * @param  double               $altitude
     * @throws \OutOfRangeException
     */
    public function setAltitude($altitude)
    {
        if ($altitude < -100000.00 || $altitude > 42849672.95) {
            throw new \OutOfRangeException('The altitude must be on [-100000.00, 42849672.95].');
        }

        $this->altitude = (double) $altitude;
    }

    /**
     * @return float
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * @param  double               $horizontalPrecision
     * @throws \OutOfRangeException
     */
    public function setHorizontalPrecision($horizontalPrecision)
    {
        if ($horizontalPrecision < 0 || $horizontalPrecision > 90000000.0) {
            throw new \OutOfRangeException('The horizontal precision must be on [0, 90000000.0].');
        }

        $this->horizontalPrecision = (double) $horizontalPrecision;
    }

    /**
     * @return double
     */
    public function getHorizontalPrecision()
    {
        return $this->horizontalPrecision;
    }

    /**
     * @param  double               $size
     * @throws \OutOfRangeException
     */
    public function setSize($size)
    {
        if ($size < 0 || $size > 90000000.0) {
            throw new \OutOfRangeException('The size must be on [0, 90000000.0].');
        }

        $this->size = (double) $size;
    }

    /**
     * @return double
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param  double               $verticalPrecision
     * @throws \OutOfRangeException
     */
    public function setVerticalPrecision($verticalPrecision)
    {
        if ($verticalPrecision < 0 || $verticalPrecision > 90000000.0) {
            throw new \OutOfRangeException('The vertical precision must be on [0, 90000000.0].');
        }

        $this->verticalPrecision = $verticalPrecision;
    }

    /**
     * @return double
     */
    public function getVerticalPrecision()
    {
        return $this->verticalPrecision;
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return sprintf(
                '%s %s %.2fm %.2fm %.2fm %.2fm',
                $this->getLatitude(self::FORMAT_DMS),
                $this->getLongitude(self::FORMAT_DMS),
                $this->altitude,
                $this->size,
                $this->horizontalPrecision,
                $this->verticalPrecision
        );
    }


    /**
     * {@inheritdoc}
     */
    public function outputFormatted()
    {
        $pad = $this->longestVarLength();
        $leftPadding = str_repeat(' ', $this->padding);

        return '(' . PHP_EOL .
            $leftPadding . str_pad($this->getLatitude(self::FORMAT_DMS), $pad)  . ' ; LATITUDE' . PHP_EOL .
            $leftPadding . str_pad($this->getLongitude(self::FORMAT_DMS), $pad) . ' ; LONGITUDE' . PHP_EOL .
            $leftPadding . str_pad(sprintf('%.2fm', $this->altitude), $pad)     . ' ; ALTITUDE' . PHP_EOL .
            $leftPadding . str_pad(sprintf('%.2fm', $this->size), $pad)         . ' ; SIZE' . PHP_EOL .
            $leftPadding . str_pad(sprintf('%.2fm', $this->horizontalPrecision), $pad) . ' ; HORIZONTAL PRECISION' . PHP_EOL .
            $leftPadding . str_pad(sprintf('%.2fm', $this->verticalPrecision), $pad)   . ' ; VERTICAL PRECISION' . PHP_EOL .
            $leftPadding . ')';
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
                     $this->getLatitude(self::FORMAT_DMS),
                     $this->getLongitude(self::FORMAT_DMS),
                     sprintf('%.2fm', $this->altitude),
                     sprintf('%.2fm', $this->size),
                     sprintf('%.2fm', $this->horizontalPrecision),
                     sprintf('%.2fm', $this->verticalPrecision)
                ) as $var) {
            $l = ($l < strlen($var)) ? strlen($var) : $l;
        }

        return $l;
    }

    /**
     * Determine the degree minute seconds value from decimal
     *
     * @param $decimal
     * @param string $axis
     * @return string
     */
    private function toDms($decimal, $axis = self::LATITUDE)
    {
        $d = (int) floor(abs($decimal));
        $m = (int) floor((abs($decimal) - $d) * 60);
        $s = ((abs($decimal) - $d) * 60 - $m) * 60;
        if ($axis === self::LATITUDE) {
            $h = ($decimal < 0) ? 'S' : 'N';
        } else {
            $h = ($decimal < 0) ? 'W' : 'E';
        }

        return sprintf('%d %d %.3f %s', $d, $m, $s, $h);
    }
}