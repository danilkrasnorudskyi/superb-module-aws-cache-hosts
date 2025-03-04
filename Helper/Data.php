<?php

namespace Superb\AwsHttpCacheHosts\Helper;

use Magento\Framework\App\DeploymentConfig;

class Data
{
    const AWS_KEY_CONFIG_PATH = 'superb/aws_http_cache_hosts/key';
    const AWS_SECRET_CONFIG_PATH = 'superb/aws_http_cache_hosts/secret';
    const AWS_REGION_CONFIG_PATH = 'superb/aws_http_cache_hosts/region';
    const AWS_TARGET_GROUP_ARN_CONFIG_PATH = 'superb/aws_http_cache_hosts/target_group_arn';
    const CACHE_PORT_CONFIG_PATH = 'superb/aws_http_cache_hosts/cache_port';
    const IGNORE_IPS_CONFIG_PATH = 'superb/aws_http_cache_hosts/ignore_ips';

    protected $deploymentConfig;

    public function __construct(
        DeploymentConfig $deploymentConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
    }

    public function getKey()
    {
        return $this->deploymentConfig->get(self::AWS_KEY_CONFIG_PATH);
    }

    public function getSecret()
    {
        return $this->deploymentConfig->get(self::AWS_SECRET_CONFIG_PATH);
    }

    public function getRegion()
    {
        return $this->deploymentConfig->get(self::AWS_REGION_CONFIG_PATH);
    }

    public function getTargetGroupArn()
    {
        return $this->deploymentConfig->get(self::AWS_TARGET_GROUP_ARN_CONFIG_PATH);
    }

    public function getCachePort()
    {
        return $this->deploymentConfig->get(self::CACHE_PORT_CONFIG_PATH, '6081');
    }

    public function getIgnoreIps()
    {
        $arr = $this->deploymentConfig->get(self::IGNORE_IPS_CONFIG_PATH, []);
        if (!is_array($arr)) {
            $arr = [];
        }
        return $arr;
    }

}