Message
=======
Badcow DNS can parse a DNS message and create PHP objects representing the message.

## Example
### Parsing a message
Say we have a message as a binary encoded string. The message is represented in hexadecimal below:
```
000a 8580 0001 0003 0000 0003 0376 6978 0363 6f6d 0000 0200 01c0 0c00 0200 0100 000e 1000 0b05 6973 7276 3102 7061 c00c
c00c 0002 0001 0000 0e10 0009 066e 732d 6578 74c0 0cc0 0c00 0200 0100 000e 1000 0e03 6e73 3104 676e 6163 0363 6f6d 00c0
2500 0100 0100 000e 1000 04cc 98b8 86c0 3c00 0100 0100 000e 1000 04cc 98b8 40c0 5100 0100 0100 02a1 4a00 04c6 97f8 f6
```
_Note: It is not necessary for you to be familiar with the encoding of a DNS message packet, but an understanding of the key
components of a DNS message is of great assistance. [Please see RFC1035 section 4](https://tools.ietf.org/html/rfc1035#section-4)
for details of the message format._

The above message can be parsed into a PHP object...
```php
$binaryMessage = '...'; // Some binary string
$message = Badcow\DNS\Message::fromWire($binaryMessage); //This will return a Badcow\DNS\Message object.
echo $message->getId(); //The message ID, in this case: 10.
echo $message->isResponse() ? 'Response' : 'Query'; //Whether the message is a response or query.

//Iterate over the QUESTION section of the message. These are a collection of Badcow\DNS\Question objects.
foreach ($message->getQuestions() as $question) {
    echo $question->getName();
    echo $question->getType();
}

//Iterate of the ANSWER section. These are \Badcow\DNS\ResourceRecord objects.
foreach ($message->getAnswers() as $answer) {
    echo $answer->getName();
    echo $answer->getType();
    echo $answer->getRdata()->toText(); //The rdata
}
```

### Encoding a message
```php
$question = new \Badcow\DNS\Question();
$question->setName('example.com.');
$question->setClass('IN');
$question->setType('A');

$message = new \Badcow\DNS\Message();
$message->setId(123);
$message->setQuery(true);

$message->setRecursionDesired(true);
$message->addQuestion($question);

$binaryMessage = $message->toWire();

```