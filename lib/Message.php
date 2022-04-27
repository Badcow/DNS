<?php

declare(strict_types=1);

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS;

use Badcow\DNS\Rdata\DecodeException;
use Badcow\DNS\Rdata\UnsupportedTypeException;

class Message
{
    /**
     * ID.
     *
     * @var int
     */
    private $id;

    /**
     * QR.
     *
     * @var bool
     */
    private $isResponse;

    /**
     * OPCODE.
     *
     * @var int
     */
    private $opcode;

    /**
     * AA.
     *
     * @var bool
     */
    private $isAuthoritative;

    /**
     * TC.
     *
     * @var bool
     */
    private $isTruncated;

    /**
     * RD.
     *
     * @var bool
     */
    private $isRecursionDesired;

    /**
     * RA.
     *
     * @var bool
     */
    private $isRecursionAvailable;

    /**
     * Bit 9 of the header flags.
     *
     * @var int
     */
    private $bit9 = 0;

    /**
     * AD.
     *
     * {@link https://tools.ietf.org/html/rfc4035#section-3.2.3}
     *
     * @var bool
     */
    private $isAuthenticData;

    /**
     * CD.
     *
     * {@link https://tools.ietf.org/html/rfc4035#section-3.2.2}
     *
     * @var bool
     */
    private $isCheckingDisabled;

    /**
     * RCODE.
     *
     * @var int
     */
    private $rcode;

    /**
     * @var Question[]
     */
    private $questions = [];

    /**
     * @var ResourceRecord[]
     */
    private $answers = [];

    /**
     * @var ResourceRecord[]
     */
    private $authoritatives = [];

    /**
     * @var ResourceRecord[]
     */
    private $additionals = [];

    /**
     * Encode a domain name as a sequence of labels.
     */
    public static function encodeName(string $name): string
    {
        if ('.' === $name) {
            return chr(0);
        }

        $name = rtrim($name, '.').'.';
        $res = '';

        foreach (explode('.', $name) as $label) {
            $res .= chr(strlen($label)).$label;
        }

        return $res;
    }

    public static function decodeName(string $string, int &$offset = 0): string
    {
        $len = ord($string[$offset]);
        ++$offset;

        $isCompressed = (bool) (0b11000000 & $len);
        $_offset = 0;

        if ($isCompressed) {
            $_offset = $offset + 1;
            $offset = (0b00111111 & $len) * 256 + ord($string[$offset]);
            $len = ord($string[$offset]);
            ++$offset;
        }

        if (0 === $len) {
            return '.';
        }

        $name = '';
        while (0 !== $len) {
            $name .= substr($string, $offset, $len).'.';
            $offset += $len;
            $len = ord($string[$offset]);
            if ($len & 0b11000000) {
                $name .= self::decodeName($string, $offset);
                break;
            }

            ++$offset;
        }

        if ($isCompressed) {
            $offset = $_offset;
        }

        return $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function isResponse(): bool
    {
        return $this->isResponse;
    }

    public function setResponse(bool $isResponse): void
    {
        $this->isResponse = $isResponse;
    }

    public function isQuery(): bool
    {
        return !$this->isResponse;
    }

    public function setQuery(bool $query): void
    {
        $this->setResponse(!$query);
    }

    public function getOpcode(): int
    {
        return $this->opcode;
    }

    public function setOpcode(int $opcode): void
    {
        $this->opcode = $opcode;
    }

    public function isAuthoritative(): bool
    {
        return $this->isAuthoritative;
    }

    public function setAuthoritative(bool $isAuthoritative): void
    {
        $this->isAuthoritative = $isAuthoritative;
    }

    public function isTruncated(): bool
    {
        return $this->isTruncated;
    }

    public function setTruncated(bool $isTruncated): void
    {
        $this->isTruncated = $isTruncated;
    }

    public function isRecursionDesired(): bool
    {
        return $this->isRecursionDesired;
    }

    public function setRecursionDesired(bool $isRecursionDesired): void
    {
        $this->isRecursionDesired = $isRecursionDesired;
    }

    public function isRecursionAvailable(): bool
    {
        return $this->isRecursionAvailable;
    }

    public function setRecursionAvailable(bool $isRecursionAvailable): void
    {
        $this->isRecursionAvailable = $isRecursionAvailable;
    }

    public function getBit9(): int
    {
        return $this->bit9;
    }

    public function setBit9(int $bit9): void
    {
        $this->bit9 = $bit9;
    }

    public function isAuthenticData(): bool
    {
        return $this->isAuthenticData;
    }

    public function setAuthenticData(bool $isAuthenticData): void
    {
        $this->isAuthenticData = $isAuthenticData;
    }

    public function isCheckingDisabled(): bool
    {
        return $this->isCheckingDisabled;
    }

    public function setCheckingDisabled(bool $isCheckingDisabled): void
    {
        $this->isCheckingDisabled = $isCheckingDisabled;
    }

    public function getRcode(): int
    {
        return $this->rcode;
    }

    public function setRcode(int $rcode): void
    {
        $this->rcode = $rcode;
    }

    /**
     * @return Question[]
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): void
    {
        $this->questions[] = $question;
    }

    /**
     * @param Question[] $questions
     */
    public function setQuestions(array $questions): void
    {
        $this->questions = [];
        foreach ($questions as $question) {
            $this->addQuestion($question);
        }
    }

    /**
     * @return ResourceRecord[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function addAnswer(ResourceRecord $answer): void
    {
        $this->answers[] = $answer;
    }

    /**
     * @param ResourceRecord[] $answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = [];
        foreach ($answers as $answer) {
            $this->addAnswer($answer);
        }
    }

    /**
     * @return ResourceRecord[]
     */
    public function getAuthoritatives(): array
    {
        return $this->authoritatives;
    }

    public function addAuthoritative(ResourceRecord $authoritative): void
    {
        $this->authoritatives[] = $authoritative;
    }

    /**
     * @param ResourceRecord[] $authoritatives
     */
    public function setAuthoritatives(array $authoritatives): void
    {
        $this->authoritatives = [];
        foreach ($authoritatives as $authoritative) {
            $this->addAuthoritative($authoritative);
        }
    }

    /**
     * @return ResourceRecord[]
     */
    public function getAdditionals(): array
    {
        return $this->additionals;
    }

    public function addAdditional(ResourceRecord $additional): void
    {
        $this->additionals[] = $additional;
    }

    /**
     * @param ResourceRecord[] $additionals
     */
    public function setAdditionals(array $additionals): void
    {
        $this->additionals = [];
        foreach ($additionals as $additional) {
            $this->addAdditional($additional);
        }
    }

    public function countQuestions(): int
    {
        return count($this->questions);
    }

    public function countAnswers(): int
    {
        return count($this->answers);
    }

    public function countAuthoritatives(): int
    {
        return count($this->authoritatives);
    }

    public function countAdditionals(): int
    {
        return count($this->additionals);
    }

    /**
     * @throws UnsetValueException
     */
    public function toWire(): string
    {
        $flags = 0x0 |
        ($this->isResponse & 0x1) << 15 |
        ($this->opcode & 0xF) << 11 |
        ($this->isAuthoritative & 0x1) << 10 |
        ($this->isTruncated & 0x1) << 9 |
        ($this->isRecursionDesired & 0x1) << 8 |
        ($this->isRecursionAvailable & 0x1) << 7 |
        ($this->bit9 & 0x1) << 6 |
        ($this->isAuthenticData & 0x1) << 5 |
        ($this->isCheckingDisabled & 0x1) << 4 |
        ($this->rcode & 0xF);

        $encoded = pack(
            'nnnnnn',
            $this->id,
            $flags,
            $this->countQuestions(),
            $this->countAnswers(),
            $this->countAuthoritatives(),
            $this->countAdditionals()
        );

        foreach (array_merge($this->questions, $this->answers, $this->authoritatives, $this->additionals) as $resource) {
            /* @var ResourceRecord|Question $resource */
            $encoded .= $resource->toWire();
        }

        return $encoded;
    }

    /**
     * @throws UnsupportedTypeException
     */
    public static function fromWire(string $encoded): Message
    {
        $message = new self();
        $offset = 0;
        if (false === $header = unpack('nid/nflags/nqdcount/nancount/nnscount/narcount', $encoded, $offset)) {
            throw new \UnexpectedValueException(sprintf('Malformed header encountered. "%s"', DecodeException::binaryToHex($encoded)));
        }
        $offset += 12;
        $flags = $header['flags'];

        $message->setId($header['id']);
        $message->setResponse((bool) ($flags >> 15 & 0x1));
        $message->setOpcode($flags >> 11 & 0xF);
        $message->setAuthoritative((bool) ($flags >> 10 & 0x1));
        $message->setTruncated((bool) ($flags >> 9 & 0x1));
        $message->setRecursionDesired((bool) ($flags >> 8 & 0x1));
        $message->setRecursionAvailable((bool) ($flags >> 7 & 0x1));
        $message->setBit9($flags >> 6 & 0x1);
        $message->setAuthenticData((bool) ($flags >> 5 & 0x1));
        $message->setCheckingDisabled((bool) ($flags >> 4 & 0x1));
        $message->setRcode($flags & 0xF);

        for ($i = 0; $i < $header['qdcount']; ++$i) {
            $message->addQuestion(Question::fromWire($encoded, $offset));
        }

        $rrs = [];
        while ($offset < strlen($encoded)) {
            $rrs[] = ResourceRecord::fromWire($encoded, $offset);
        }

        $message->setAnswers(array_splice($rrs, 0, $header['ancount']));
        $message->setAuthoritatives(array_splice($rrs, 0, $header['nscount']));
        $message->setAdditionals(array_splice($rrs, 0, $header['arcount']));

        return $message;
    }
}
