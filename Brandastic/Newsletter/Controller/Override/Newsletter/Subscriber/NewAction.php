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

namespace Brandastic\Newsletter\Controller\Override\Newsletter\Subscriber;

use Magento\Framework\App\ObjectManager;

class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $_resultJson;

    /**
     * @var \Brandastic\Newsletter\Block\Data
     */
    protected $_helper;

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        $result = [];
        $result['error'] = true;
        $result['message'] = __('');
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
                if ($subscriber->getId()
                    && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {
                    $result['message'] = __('This email address is already subscribed.');
                } else {
                    $status = $this->_subscriberFactory->create()->subscribe($email);
                    $couponCode = $this->getCouponCode();
                    if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                        $result['message'] = __('The confirmation request has been sent.');
                        $result['error'] = false;
                    } else {
                        $result['message'] = __('Thank you for your subscription. your couponcode is '.$couponCode);
                        $result['error'] = false;
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result['message'] = __('There was a problem with the subscription: %1', $e->getMessage());
            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        }
        return $this->getResultJson()->setData($result);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function getResultJson()
    {
        if ($this->_resultJson === null) {
            $this->_resultJson = ObjectManager::getInstance()->get(\Magento\Framework\Controller\Result\Json::class);
        }
        return $this->_resultJson;
    }

    /**
     * @return Brandastic\Newsletter\Block\Data coupon code if enabled
     */
    protected function getCouponCode()
    {
        if ($this->_helper === null) {
            $this->_helper = ObjectManager::getInstance()->get(\Brandastic\Newsletter\Helper\Data::class);
            return $this->_helper->getCouponCode();
        }
    }

}
