<?php

namespace Superb\AwsHttpCacheHosts\Model;

use Magento\Framework\Flag as MagentoFlagModel;

class Flag extends MagentoFlagModel
{
    const ADDITIONAL_IPS = 'additional_ips';

    protected $_flagCode = 'aws_http_cache_hosts';

    public function getAdditionalIps()
    {
        if ($this->isEmpty()) {
            $this->loadSelf();
        }
        if (null !== $this->getFlagData()) {
            return $this->getFlagData()[self::ADDITIONAL_IPS] ?? [];
        }
        return [];
    }

    public function saveAdditionalIps($ips)
    {
        if (!is_array($ips)) {
            $ips = [];
        }
        if ($this->isEmpty()) {
            $this->loadSelf();
        }
        $flagData = $this->getFlagData();
        if (null === $flagData) {
            $flagData = [];
        }
        $flagData[self::ADDITIONAL_IPS] = $ips;
        $this->setFlagData($flagData);
        $this->save();
    }
}
