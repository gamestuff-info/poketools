<?php


namespace App\Tests\Traits;


use Psr\Log\LoggerInterface;

/**
 * Preset loggers for use in testing
 */
trait LoggerTrait
{
    /**
     * Get a logger that disallows any messages with a severity in $severities.
     *
     * @param string[] $severities
     *
     * @return LoggerInterface
     */
    protected function getProhibitiveLogger(array $severities = ['emergency', 'alert', 'critical', 'error', 'warning']
    ): LoggerInterface {
        $logger = $this->createMock(LoggerInterface::class);
        foreach ($severities as $severity) {
            $logger->expects($this->never())->method($severity);
        }

        return $logger;
    }
}
