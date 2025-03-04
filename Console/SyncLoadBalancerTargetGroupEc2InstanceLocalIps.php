<?php

namespace Superb\AwsHttpCacheHosts\Console;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncLoadBalancerTargetGroupEc2InstanceLocalIps extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ObjectManager::getInstance()
            ->get(\Superb\AwsHttpCacheHosts\Cron\SyncLoadBalancerTargetGroupEc2InstanceLocalIps::class)
            ->execute();
        return 1;
    }

    /** * {@inheritdoc} */
    protected function configure()
    {
        $this->setName('superb:aws-http-cache-hosts:sync-load-balancer-target-group-ec2-instance-local-ips');
        $this->setDescription('Sync load balancer target group ec2 instance local ips');
        $this->setDefinition([]);
        parent::configure();
    }
}