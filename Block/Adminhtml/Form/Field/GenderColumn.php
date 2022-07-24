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

namespace Adobe\Emi\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class GenderColumn extends Select
{
    /**
    * value of Male
    */
    const MALE = 1;

    /**
    * value of Female
    */
    const FEMALE = 2;

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        return [
            ['label' => 'Male', 'value' => self::MALE],
            ['label' => 'Female', 'value' => self::FEMALE],
        ];
    }
}
