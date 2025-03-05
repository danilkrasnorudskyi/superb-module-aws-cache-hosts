<?php

namespace Superb\AwsHttpCacheHosts\Cron;

use Aws\Ec2\Ec2Client;
use Psr\Log\LoggerInterface;
use Superb\AwsHttpCacheHosts\Model\Flag;
use Superb\AwsHttpCacheHosts\Helper\Data;
use Aws\ElasticLoadBalancingV2\ElasticLoadBalancingV2Client;

class SyncLoadBalancerTargetGroupEc2InstanceLocalIps
{
    protected $helper;
    protected $flag;
    protected $logger;

    public function __construct(
        Data $helper,
        Flag $flag,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->flag = $flag;
        $this->logger = $logger;
    }

    public function execute()
    {
        $key = $this->helper->getKey();
        $secret = $this->helper->getSecret();
        $region = $this->helper->getRegion();
        $targetGroupArn = $this->helper->getTargetGroupArn();
        if (empty($key) || empty($secret) || empty($region) || empty($targetGroupArn)) {
            return false;
        }

        $ec2InstanceIds = $this->getEc2InstanceIds($key, $secret, $region, $targetGroupArn);
        if ($ec2InstanceIds) {
            $ips = $this->getPrivateIps($key, $secret, $region, $ec2InstanceIds);
        } else {
            $ips = [];
        }

        $newIps = [];
        if ($this->helper->getIgnoreIps()) {
            foreach ($ips as $ip) {
                if (!in_array($ip, $this->helper->getIgnoreIps(), true)) {
                    $newIps[] = $ip;
                }
            }
        } else {
            $newIps = $ips;
        }

        $this->flag->saveAdditionalIps($newIps);
    }

    private function getEc2InstanceIds($key, $secret, $region, $targetGroupArn)
    {
        $lbv2 = new ElasticLoadBalancingV2Client([
            'credentials' => [
                'key' => $key,
                'secret' => $secret
            ],
            'region' => $region
        ]);

        $result = $lbv2->describeTargetHealth([
            'TargetGroupArn' => $targetGroupArn,
        ]);

        $ec2InstanceIds = [];
        if (is_array($result->get('TargetHealthDescriptions'))) {
            foreach ($result->get('TargetHealthDescriptions') as $target) {
                if (!empty($target['Target']['Id']) &&
                    !empty($target['TargetHealth']['State']) &&
                    strtolower($target['TargetHealth']['State']) === 'healthy'
                ) {
                    $ec2InstanceIds[] = $target['Target']['Id'];
                }
            }
        }
        return $ec2InstanceIds;
    }

    private function getPrivateIps($key, $secret, $region, $ec2InstanceIds)
    {
        $ec2 = new Ec2Client([
            'credentials' => [
                'key' => $key,
                'secret' => $secret
            ],
            'region' => $region
        ]);

        $result = $ec2->describeInstances([
            'InstanceIds' => $ec2InstanceIds,
        ]);

        $ips = [];

        if (is_array($result->get('Reservations'))) {
            foreach ($result->get('Reservations') as $reservation) {
                if (!empty($reservation['Instances'])) {
                    foreach ($reservation['Instances'] as $instance) {
                        if (!empty($instance['PrivateIpAddress']) &&
                            !empty($instance['State']['Name']) &&
                            strtolower($instance['State']['Name']) === 'running'
                        ) {
                            $ips[] = $instance['PrivateIpAddress'];
                        }
                    }
                }
            }
        }

        return $ips;
    }
}