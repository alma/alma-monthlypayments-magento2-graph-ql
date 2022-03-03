<?php
namespace Alma\GraphQL\Helpers;

use Alma\MonthlyPayments\Helpers\Logger;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;


class QuoteHelper
{
    /**
     * @var Logger
     */
    private  $logger;
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private  $maskedQuoteIdToQuoteId;

    /**
     * @param Logger $logger
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        Logger $logger,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    )
    {
        $this->logger = $logger;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;

    }

    /**
     * @param $maskedQuoteId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteIdByMaskedQuoteId($maskedQuoteId): int
    {
        return  $this->maskedQuoteIdToQuoteId->execute($maskedQuoteId);
    }
}
