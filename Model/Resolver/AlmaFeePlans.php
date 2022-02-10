<?php
namespace Alma\GraphQL\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Alma\MonthlyPayments\Helpers\Logger;
use Alma\MonthlyPayments\Helpers\Eligibility;

class AlmaFeePlans implements ResolverInterface
{
    public function __construct(
        Logger $logger,
        Eligibility $eligibility
    ) {
        $this->logger = $logger;
        $this->eligibility = $eligibility;
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
        $almaEligibility = $value['model'];
        $paymentPlans = [];
        $feePlans = $this->getFeePlans();
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
    private function getFeePlans(): array
    {
        $this->logger->info('get Alma Fee plans in Graph QL resolver',[]);
        $plans = $this->eligibility->getCurrentsFeePlans();
        if (!$this->eligibility->isAlreadyLoaded()){
            $plans=$this->eligibility->getEligiblePlans();
        }
        return $plans;
    }
}
