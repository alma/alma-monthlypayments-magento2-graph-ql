<?php
namespace Alma\GraphQL\Model\Resolver;

use Alma\MonthlyPayments\Helpers\ApiConfigHelper;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Alma\MonthlyPayments\Helpers\Logger;
use Alma\MonthlyPayments\Gateway\Config\Config;
use Alma\GraphQL\Model\Resolver\AlmaFeePlans;


class AlmaConfig implements ResolverInterface
{
    /**
     * @var Logger
     */
    private $logger;
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

    public function __construct(
        Logger $logger,
        Config $almaConfig,
        ApiConfigHelper $apiConfigHelper,
        AlmaFeePlans $almaFeePlans
    ) {
        $this->logger = $logger;
        $this->almaConfig = $almaConfig;
        $this->almaFeePlans = $almaFeePlans;
        $this->apiConfigHelper = $apiConfigHelper;
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
            'title' => $this->almaConfig->getPaymentButtonTitle(),
            'description' => $this->almaConfig->getPaymentButtonDescription(),
            'sort_order' => $this->almaConfig->getSortOrder(),
            'payment_plans_by_id' =>$this->almaFeePlans->getPlans($args['cart_id'])
        ];
    }
}
