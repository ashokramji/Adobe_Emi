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

namespace Adobe\Emi\ViewModel;

use Magento\Catalog\Block\Product\AbstractProduct;
use Adobe\Emi\Block\Adminhtml\Form\Field\GenderColumn;

class EmiModel extends AbstractProduct implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
    * Email config path
    */
    const XML_PATH_EMI_CONFIG = 'emi/general/emi';

    /**
    * Module enable config path
    */
    const XML_PATH_EMI_MODULE_STATUS = 'emi/general/enable';

    /**
    * Simple
    */
    const PRODUCT_TYPE_SIMPLE = 'simple';

    /**
    * Configurable
    */
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->httpContext = $httpContext;
        $this->json = $json;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Returning config value
     *
     * @return array
     **/
    public function getConfigValue()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_EMI_CONFIG, $storeScope);
    }

    /**
     * Returning module enable status
     *
     * @return boolean
     **/
    public function getModuleStatus()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_EMI_MODULE_STATUS, $storeScope);
    }

    /**
     * Retrieve customer logged in
     *
     * @return boolean
     */
    public function getCustomerIsLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Show EMI block
     *
     * @return boolean
     */
    public function getIsShowEmiBlock()
    {
        if ($this->getCustomerIsLoggedIn() &&
            (($this->getProduct()->getTypeId() == self::PRODUCT_TYPE_SIMPLE) ||
                ($this->getProduct()->getTypeId() == self::PRODUCT_TYPE_CONFIGURABLE)) && $this->getModuleStatus()) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve customer gender
     *
     * @return int
     */
    public function getCustomerGender()
    {
        return $this->httpContext->getValue('customer_gender');
    }

    /**
     * Function getFormatedPrice
     *
     * @param float $price
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    /**
     * EMI calculator
     *
     * @param float $productFinalPrice
     * @param float $interestRate
     * @param int $tenure
     * @return float
     */
    public function getEmiAmount($productFinalPrice, $interestRate, $tenure)
    {
        $interestRate = $interestRate / (12 * 100);
        $emi = ($productFinalPrice * $interestRate *
            pow(1 + $interestRate, $tenure)) / (pow(1 + $interestRate, $tenure) - 1);
        return $emi;
    }

    /**
     * Form EMI array
     *
     * @return array
     */
    public function getEmiArray()
    {
        $configValue = is_array($this->getConfigValue()) ?
            $this->getConfigValue() : $this->json->unserialize($this->getConfigValue());
        $customerGender = !empty($this->getCustomerGender()) ? $this->getCustomerGender() : GenderColumn::MALE;
        $productFinalPrice = $this->getProduct()->getFinalPrice();
        $emiArray = [];
        foreach ($configValue as $value) {
            if ($value['gender'] == $customerGender) {
                $emiAmount = $this->getEmiAmount($productFinalPrice, $value['interest'], $value['tenure']);
                $totalAmount = $emiAmount * $value['tenure'];
                $totalInterest = $totalAmount - $productFinalPrice;
                $formatedEmiAmount = $this->getFormatedPrice(round($emiAmount, 2));
                $formatedTotalInterest = $this->getFormatedPrice(round($totalInterest, 2));
                $formatedTotalAmount = $this->getFormatedPrice(round($totalAmount, 2));

                $emiArray[] =
                    ['monthlyEmi' => $formatedEmiAmount,
                     'tenure' => $value['tenure'],
                     'totalInterest' => $formatedTotalInterest,
                     'totalAmount' => $formatedTotalAmount,
                     'emiAmount' => $emiAmount];
            }
        }
        return $emiArray;
    }

    /**
     * Return min EMI amount
     *
     * @return int
     */
    public function getMinEmiAmount()
    {
        if (!empty($this->getEmiArray())) {
            $minEmi = min(array_column($this->getEmiArray(), 'emiAmount'));
            return $this->getFormatedPrice(round($minEmi, 2));
        }
        return null;
    }
}
