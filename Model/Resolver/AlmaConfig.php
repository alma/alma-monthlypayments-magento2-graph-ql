<?php
namespace Alma\GraphQL\Model\Resolver;

use Alma\MonthlyPayments\Helpers\ApiConfigHelper;
use Alma\MonthlyPayments\Helpers\CheckoutConfigHelper;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Alma\MonthlyPayments\Gateway\Config\Config;


class AlmaConfig implements ResolverInterface
{
    /**
     * @var Config
     */
    private $almaConfig;
    /**
     * @var AlmaFeePlans
     */
    private $almaFeePlans;
    /**
     * @var ApiConfigHelper
     */
    private $apiConfigHelper;
    /**
     * @var CheckoutConfigHelper
     */
    private $checkoutConfigHelper;

    public function __construct(
        Config $almaConfig,
        ApiConfigHelper $apiConfigHelper,
        CheckoutConfigHelper $checkoutConfigHelper,
        AlmaFeePlans $almaFeePlans
    ) {
        $this->almaConfig = $almaConfig;
        $this->almaFeePlans = $almaFeePlans;
        $this->apiConfigHelper = $apiConfigHelper;
        $this->checkoutConfigHelper = $checkoutConfigHelper;
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
            if (empty($args['cart_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
            }
            return [
            'is_enabled' => $this->almaConfig->getIsActive(),
            'mode' => $this->apiConfigHelper->getActiveMode(),
            'title' => __($this->checkoutConfigHelper->getMergePaymentTitle()),
            'description' => __($this->checkoutConfigHelper->getMergePaymentDesc()),
            'sort_order' => $this->almaConfig->getSortOrder(),
            'payment_plans_by_id' =>$this->almaFeePlans->getPlans($args['cart_id'])
        ];
    }
}
