<?php
namespace Alma\GraphQL\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Alma\MonthlyPayments\Helpers\Logger;
use Alma\MonthlyPayments\Helpers\Eligibility;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Session as CheckoutSession;


class AlmaFeePlans implements ResolverInterface
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    public function __construct(
        Logger $logger,
        Eligibility $eligibility,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        QuoteFactory $quoteFactory,
        CheckoutSession $checkoutSession,
        GetCartForUser $getCartForUser

    ) {
        $this->logger = $logger;
        $this->eligibility = $eligibility;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->getCartForUser = $getCartForUser;

    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['masked_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "masked_cart_id" is missing'));
        }
        $currentUserId = $context->getUserId();
        $this->logger->info('userId',[$currentUserId]);
        $maskedCartId = $args['masked_cart_id'];
        $paymentPlans = [];
        $feePlans = $this->getFeePlans($maskedCartId);

        foreach ($feePlans as $key=> $plan){
            $planConfig      = $plan->getPlanConfig()->toArray();
            $planEligibility = $plan->getEligibility();
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
     * @return array
     */
    private function getFeePlans($maskedCartId): array
    {
        $quoteId = $this->getQuoteByMaskedQuoteId($maskedCartId);
        $this->logger->info('$quoteId',[$quoteId]);
        $quote = $this->getQuoteById($quoteId);
        $this->logger->info('$quote',[$quote]);
        $this->logger->info('$quote',[$quote->getData()]);
        $this->setQuoteInSession($quote);
        $plans = $this->eligibility->getCurrentsFeePlans();
        if (!$this->eligibility->isAlreadyLoaded()){
            $plans=$this->eligibility->getEligiblePlans();
        }
        return $plans;
    }

    private function getQuoteById($quoteId){
        return $this->quoteFactory->create()->load($quoteId);
    }

    private function getQuoteByMaskedQuoteId($maskedCartId){
        return  $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
    }

    private function setQuoteInSession($quote){

        if (!$this->checkoutSession->hasQuote() && $quote) {
            $this->checkoutSession->replaceQuote($quote);
            $this->logger->info('load quote in session',[$this->checkoutSession->getQuote()]);
        }

    }
}
