<?php
namespace Alma\GraphQL\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Alma\MonthlyPayments\Helpers\Logger;
use Alma\MonthlyPayments\Gateway\Config\Config;


class AlmaConfig implements ResolverInterface
{
    /**
     * @var Logger
     */
    private Logger $logger;
    /**
     * @var Config
     */
    private Config $almaConfig;

    public function __construct(
        Logger $logger,
        Config $almaConfig
    ) {
        $this->logger = $logger;
        $this->almaConfig = $almaConfig;
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
        $almaConfig = $value['model'];
        return [
            'model'=> $almaConfig,
            'is_enabled' => $this->almaConfig->getIsActive(),
            'mode' => $this->almaConfig->getActiveMode(),
            'title' => $this->almaConfig->getPaymentButtonTitle(),
            'description' => $this->almaConfig->getPaymentButtonDescription(),
            'sort_order' => $this->almaConfig->getSortOrder(),
        ];
    }
}
