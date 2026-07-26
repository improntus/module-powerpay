<?php
/**
 * @author Improntus Dev Team
 * @copyright Copyright (c) 2026 Improntus (https://www.improntus.com/)
 */

namespace Improntus\PowerPay\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

class CustomHandler extends BaseHandler
{
    protected $loggerType = MonologLogger::INFO;

    protected $fileName = 'var/log/powerpay/info.log';
}
