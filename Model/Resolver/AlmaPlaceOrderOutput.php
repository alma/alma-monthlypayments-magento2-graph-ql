<?php
namespace Alma\GraphQL\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Alma\MonthlyPayments\Helpers\Logger;
use Alma\MonthlyPayments\Helpers\Eligibility;
use Alma\MonthlyPayments\Model\Api\Payment;

use Magento\Sales\Model\OrderFactory;

class AlmaPlaceOrderOutput implements ResolverInterface
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
     * @var Payment
     */
    private $almaPayment;
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    public function __construct(
        Logger $logger,
        Eligibility $eligibility,
        OrderFactory $orderFactory,
        Payment $almaPayment
    ) {
        $this->logger = $logger;
        $this->eligibility = $eligibility;
        $this->almaPayment = $almaPayment;
        $this->orderFactory = $orderFactory;
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

        $orderModel = $this->orderFactory->create();
        $order = $orderModel->loadByIncrementId($value['order_id']);
        $orderId = $order->getId();

        $url = $this->almaPayment->getPaymentUrl($orderId);
        $this->logger->info('$url',[$url]);
        return $url;
    }
}
