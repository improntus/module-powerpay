<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (https://www.improntus.com/)
 */

namespace Improntus\PowerPay\Logger;

class Logger extends \Monolog\Logger
{
    public function setName($name)
    {
        $this->name = $name;
    }
}
