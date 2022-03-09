<?php
namespace Alma\GraphQL\Helpers;

use Alma\MonthlyPayments\Helpers\Logger;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Alma\MonthlyPayments\Helpers\QuoteHelper as AlmaQuoteHelper;


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
     * @var AlmaQuoteHelper
     */
    private  $almaQuoteHelper;

    /**
     * @param Logger $logger
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        Logger $logger,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        AlmaQuoteHelper $almaQuoteHelper
    )
    {
        $this->logger = $logger;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->almaQuoteHelper = $almaQuoteHelper;

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

    /**
     * @param int $quoteId
     * @return void
     */
    public function setEligibilityQuoteId($quoteId):void
    {
        $this->almaQuoteHelper->setEligibilityQuoteId($quoteId);
    }
}
