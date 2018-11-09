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

/**
 * Class LocRdata.
 *
 * Mechanism to allow the DNS to carry location
 * information about hosts, networks, and subnets.
 *
 * @see http://tools.ietf.org/html/rfc1876
 */
class LOC implements RdataInterface, FormattableInterface
{
    use RdataTrait, FormattableTrait;

    const TYPE = 'LOC';

    const LATITUDE = 'LATITUDE';

    const LONGITUDE = 'LONGITUDE';

    const FORMAT_DECIMAL = 'DECIMAL';

    const FORMAT_DMS = 'DMS';

    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @var float
     */
    private $altitude = 0.0;

    /**
     * @var float
     */
    private $size = 1.0;

    /**
     * @var float
     */
    private $horizontalPrecision = 10000.0;

    /**
     * @var float
     */
    private $verticalPrecision = 10.0;

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = (float) $latitude;
    }

    /**
     * @param string $format
     *
     * @return float|string
     */
    public function getLatitude($format = self::FORMAT_DECIMAL)
    {
        if (self::FORMAT_DMS === $format) {
            return $this->toDms($this->latitude, self::LATITUDE);
        }

        return $this->latitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = (float) $longitude;
    }

    /**
     * @param string $format
     *
     * @return float|string
     */
    public function getLongitude($format = self::FORMAT_DECIMAL)
    {
        if (self::FORMAT_DMS === $format) {
            return $this->toDms($this->longitude, self::LONGITUDE);
        }

        return $this->longitude;
    }

    /**
     * @param float $altitude
     *
     * @throws \OutOfRangeException
     */
    public function setAltitude($altitude)
    {
        if ($altitude < -100000.00 || $altitude > 42849672.95) {
            throw new \OutOfRangeException('The altitude must be on [-100000.00, 42849672.95].');
        }

        $this->altitude = (float) $altitude;
    }

    /**
     * @return float
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * @param float $horizontalPrecision
     *
     * @throws \OutOfRangeException
     */
    public function setHorizontalPrecision($horizontalPrecision)
    {
        if ($horizontalPrecision < 0 || $horizontalPrecision > 90000000.0) {
            throw new \OutOfRangeException('The horizontal precision must be on [0, 90000000.0].');
        }

        $this->horizontalPrecision = (float) $horizontalPrecision;
    }

    /**
     * @return float
     */
    public function getHorizontalPrecision()
    {
        return $this->horizontalPrecision;
    }

    /**
     * @param float $size
     *
     * @throws \OutOfRangeException
     */
    public function setSize($size)
    {
        if ($size < 0 || $size > 90000000.0) {
            throw new \OutOfRangeException('The size must be on [0, 90000000.0].');
        }

        $this->size = (float) $size;
    }

    /**
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param float $verticalPrecision
     *
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
     * @return float
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
        return ResourceRecord::MULTILINE_BEGIN.PHP_EOL.
            $this->makeLine($this->getLatitude(self::FORMAT_DMS), 'LATITUDE').
            $this->makeLine($this->getLongitude(self::FORMAT_DMS), 'LONGITUDE').
            $this->makeLine(sprintf('%.2fm', $this->altitude), 'ALTITUDE').
            $this->makeLine(sprintf('%.2fm', $this->size), 'SIZE').
            $this->makeLine(sprintf('%.2fm', $this->horizontalPrecision), 'HORIZONTAL PRECISION').
            $this->makeLine(sprintf('%.2fm', $this->verticalPrecision), 'VERTICAL PRECISION').
            str_repeat(' ', $this->padding).ResourceRecord::MULTILINE_END;
    }

    /**
     * Determines the longest variable.
     *
     * @return int
     */
    public function longestVarLength()
    {
        $l = 0;

        foreach ([
                        $this->getLatitude(self::FORMAT_DMS),
                        $this->getLongitude(self::FORMAT_DMS),
                        sprintf('%.2fm', $this->altitude),
                        sprintf('%.2fm', $this->size),
                        sprintf('%.2fm', $this->horizontalPrecision),
                        sprintf('%.2fm', $this->verticalPrecision),
                ] as $var) {
            $l = ($l < strlen($var)) ? strlen($var) : $l;
        }

        return $l;
    }

    /**
     * Determine the degree minute seconds value from decimal.
     *
     * @param $decimal
     * @param string $axis
     *
     * @return string
     */
    private function toDms($decimal, $axis = self::LATITUDE)
    {
        $d = (int) floor(abs($decimal));
        $m = (int) floor((abs($decimal) - $d) * 60);
        $s = ((abs($decimal) - $d) * 60 - $m) * 60;
        if (self::LATITUDE === $axis) {
            $h = ($decimal < 0) ? 'S' : 'N';
        } else {
            $h = ($decimal < 0) ? 'W' : 'E';
        }

        return sprintf('%d %d %.3f %s', $d, $m, $s, $h);
    }
}
