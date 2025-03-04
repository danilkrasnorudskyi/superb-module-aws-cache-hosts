<?php

namespace Superb\AwsHttpCacheHosts\Plugin\Model\Cache;

use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;
use Psr\Log\LoggerInterface;
use Superb\AwsHttpCacheHosts\Model\Flag;
use Superb\AwsHttpCacheHosts\Helper\Data;
use Magento\PageCache\Model\Cache\Server as Subject;

class Server
{
    protected $flag;
    protected $helper;
    protected $logger;

    public function __construct(
        Flag $flag,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->flag = $flag;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function afterGetUris(
        Subject $server,
        $result
    ) {
        if (is_array($result)) {
            try {
                $additionalIps = $this->flag->getAdditionalIps();
                if (is_array($additionalIps) && $additionalIps) {
                    foreach ($additionalIps as $ip) {
                        try {
                            if (!$this->isHostAdded($result, $ip)) {
                                $uri = UriFactory::factory('')
                                    ->setHost($ip)
                                    ->setPort($this->helper->getCachePort())
                                    ->setScheme('http')
                                    ->setPath('/')
                                    ->setQuery(null);
                                $result[] = $uri;
                            }
                        } catch (\Exception $e) {
                            $this->logger->info(__CLASS__ . ': Unable to create uri using ip: ' . $ip . '  message: ' . $e->getMessage());
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->info(__CLASS__ . ': Unable to retrieve additional ips message: ' . $e->getMessage());
            }
        }
        return $result;
    }

    private function isHostAdded($result, $host)
    {
        foreach ($result as $uri) {
            if ($uri instanceof Uri && $uri->getHost() == strtolower($host) && $uri->getPort() == $this->helper->getCachePort()) {
                return true;
            }
        }
        return false;
    }
}