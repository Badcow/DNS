<?php
/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function cmd_exists($cmd)
{
    $cmd = preg_replace('/[^A-Za-z0-9\-]/', '', $cmd);
    exec("command -v $cmd >& /dev/null && echo \"Found\" || echo \"Not Found\"", $output);

    return $output[0] == 'Found';
}

if (!($loader = @include __DIR__ . '/../vendor/autoload.php')) {
    die(<<<EOT
\033[41m\e[37m
You need to install the project dependencies using Composer:
$ wget http://getcomposer.org/composer.phar
OR
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar install --dev\033[0m


EOT
    );
}
/*
if (!cmd_exists('named-checkzone')) {
    echo '\033[31mWARNING: Bind\'s named-checkzone command is required to run some of these tests.\033[0m\n';
}*/

$loader->add('Badcow\DNS\Tests', __DIR__);