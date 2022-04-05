<?php
namespace Alma\GraphQL\Model\Resolver;

use Alma\MonthlyPayments\Helpers\Logger;
use Alma\MonthlyPayments\Helpers\Eligibility;
use Alma\GraphQL\Helpers\QuoteHelper;

class AlmaFeePlans
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Eligibility
     */
    private $eligibility;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    public function __construct(
        Logger $logger,
        Eligibility $eligibility,
        QuoteHelper $quoteHelper
    ) {
        $this->logger = $logger;
        $this->eligibility = $eligibility;
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * @param $quote
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     */
    public function getPlans($maskedQuoteId) {

        $feePlans = $this->getFeePlans($maskedQuoteId);
        $paymentPlans = [];
        foreach ($feePlans as $key=> $plan){
            $planEligibility = $plan->getEligibility();
            if(!$planEligibility->isEligible()){
                $this->logger->info('This plan is not eligible',[$planEligibility]);
                continue;
            }

            $planConfig      = $plan->getPlanConfig()->toArray();
            $paymentPlans[$key]['key'] = $planConfig['key'];
            $paymentPlans[$key]['logo'] = $planConfig['logo'];
            $paymentPlans[$key]['allowed'] = $planConfig['allowed'];
            $paymentPlans[$key]['kind'] = $planConfig['kind'];
            $paymentPlans[$key]['installments_count'] = $planConfig['installmentsCount'];
            $paymentPlans[$key]['deferred_days'] = $planConfig['deferredDays'];
            $paymentPlans[$key]['deferred_months'] = $planConfig['deferredMonths'];
            $paymentPlans[$key]['enabled'] = $planConfig['enabled'];
            $paymentPlans[$key]['min_amount'] = $planConfig['minAmount'];
            $paymentPlans[$key]['max_amount'] = $planConfig['maxAmount'];
            $paymentPlans[$key]['customer_lending_rate'] = $planConfig['customerLendingRate'];
            $paymentPlans[$key]['deferred_type'] = $planConfig['deferredType'];
            $paymentPlans[$key]['deferred_duration'] = $planConfig['deferredDuration'];
            $paymentPlans[$key]['eligibility']['is_eligible'] = $planEligibility->isEligible;
            $paymentPlans[$key]['eligibility']['reasons'] = $planEligibility->reasons;
            $paymentPlans[$key]['eligibility']['constraints'] = $planEligibility->constraints;
            foreach ($planEligibility->paymentPlan as $installment){
                $paymentPlans[$key]['eligibility']['installments'][] =$installment;
            }
        }
        return array_values($paymentPlans);
    }

    /**
     * Get fee plans
     *
     * @param $maskedQuoteId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFeePlans($maskedQuoteId): array
    {
        $quoteId = $this->quoteHelper->getQuoteIdByMaskedQuoteId($maskedQuoteId);
        $this->logger->info('$Quote Id in Graph QL',[$quoteId]);
        $this->quoteHelper->setEligibilityQuoteId($quoteId);
        $plans = $this->eligibility->getEligiblePlans();
        $this->logger->info('$plans',[$plans]);
        return $plans;
    }
}
