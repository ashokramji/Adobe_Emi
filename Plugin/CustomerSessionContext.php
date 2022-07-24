<?php
/**
 * Adobe_Emi
 *
 * @category  PHP
 * @package   Adobe\Emi
 * @copyright Copyright © 2022 Adobe. All rights reserved.
 * @link      https://www.adobe.com/
 **/
declare(strict_types=1);

namespace Adobe\Emi\Plugin;

class CustomerSessionContext
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->httpContext->setValue(
            'customer_id',
            $this->customerSession->getCustomerId(),
            false
        );

        $this->httpContext->setValue(
            'customer_gender',
            $this->customerSession->getCustomer()->getGender(),
            false
        );

        return $proceed($request);
    }
}
