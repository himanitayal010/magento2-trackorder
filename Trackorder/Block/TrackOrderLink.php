<?php

namespace Magneto\Trackorder\Block;

class TrackOrderLink extends \Magento\Framework\View\Element\Html\Link
{

    /**
     * @var \Magneto\Quickorder\Helper\Data
     */
    protected $_helper;

    /**
     * QuickOrderLink constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magneto\Quickorder\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magneto\Trackorder\Helper\Data $helper,
        array $data = []
    ) {
       $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */

    public function getHref()
    {
        return $this->_urlBuilder->getUrl('trackorder/index/view');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }
        
        $enabled = $this->_helper->isEnabled();
        if (!$enabled) {
            return '';
        }

        return '<li><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a></li>';
    }
}
