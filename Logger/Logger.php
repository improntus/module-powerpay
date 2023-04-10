<?php
namespace Improntus\PowerPay\Logger;

class Logger extends \Monolog\Logger
{
    public function setName($name)
    {
        $this->name = $name;
    }
}
