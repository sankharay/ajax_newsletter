<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brandastic.com
 *
 * @category    Brandastic
 * @package     Brandastic_Newsletter
 * @copyright   Copyright (c) 2019-2020 brandastic. All rights reserved. (http://www.brandastic.com/)
 * @license     https://www.brandastic.com/LICENSE.txt
 */
 
namespace Brandastic\Newsletter\Observer;

use Magento\Checkout\Helper\Cart;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface as Session;

class onCartLoads implements ObserverInterface
{
    /*
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;
    /*
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    public function __construct(
        Cart $_cartHelper,
        Session $session
    ) {
        $this->_session = $session;
        $this->_cartHelper = $_cartHelper;
    }

    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_session->start();
        $couponCode = $this->_session->getBrandasticCoupon();
        if ($couponCode) {
            $quote = $this->_cartHelper->getQuote();
            $quote->setCouponCode($couponCode)->collectTotals()->save();
        }
    }
}
