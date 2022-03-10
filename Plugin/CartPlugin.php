<?php
namespace Alma\GraphQL\Plugin;

use Alma\MonthlyPayments\Helpers\Logger;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\Cart;
use Alma\GraphQL\Helpers\QuoteHelper;
use \Magento\Framework\GraphQl\Query\ResolverInterface;

class CartPlugin
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @param Logger $logger
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(
        Logger $logger,
        QuoteHelper $quoteHelper
    )
    {
        $this->logger = $logger;
        $this->quoteHelper = $quoteHelper;

    }

    /**
     * @param Cart $cart
     * @param $result
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        ResolverInterface $cart,
        $result,
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        $cart = $result['model'];
        $cartId = (int) $cart->getId();
        $this->quoteHelper->setEligibilityQuoteId($cartId);
        return $result;
    }
}
