<?php

namespace Alma\GraphQL\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Alma\MonthlyPayments\Helpers\Logger;

/**
 * Format Alma input into value expected when setting payment method
 */
class AlmaPlanDataProvider implements AdditionalDataProviderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }
    private const PATH_ADDITIONAL_DATA = 'alma_additional_data';

    /**
     * Format Alma input into value expected when setting payment method
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $args): array
    {
        $this->logger->info('AdditionalDataProviderInterface args',[$args]);
        if (!isset($args[self::PATH_ADDITIONAL_DATA])) {
            throw new GraphQlInputException(
                __('Required parameter "alma_additional_data" for "payment_method" is missing.')
            );
        }
        return $args[self::PATH_ADDITIONAL_DATA];
    }
}
