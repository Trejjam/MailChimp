<?php
declare(strict_types=1);

namespace Trejjam\MailChimp\DI;

use Composer\CaBundle\CaBundle;
use Nette\DI\Statement;

final class HttpClientConfiguration
{
    /**
     * @var string|array|Statement|null
     */
    public $clientFactory = null;
    /**
     * @var array
     */
    public $client = [];

    public function __construct()
    {
        $this->client['verify'] = CaBundle::getSystemCaRootBundlePath();
    }
}
