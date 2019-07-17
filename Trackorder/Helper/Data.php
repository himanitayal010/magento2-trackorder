<?php
namespace Magneto\Trackorder\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ENABLED = 'trackorder_config/general/enable';

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
    public function isEnabled()
    {
    return $this->scopeConfig->getValue(self::XML_PATH_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
