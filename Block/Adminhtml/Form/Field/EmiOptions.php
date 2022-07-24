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

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Adobe\Emi\Block\Adminhtml\Form\Field\GenderColumn;

/**
 * Class EmiOptions
 * Return EMI options to system configuration
 */
class EmiOptions extends AbstractFieldArray
{
    /**
     * @var GenderColumn
     */
    private $genderRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('interest', ['label' => __('Interest Rate'), 'class' => 'required-entry']);
        $this->addColumn('tenure', ['label' => __('Tenure (Months)'), 'class' => 'required-entry']);
        $this->addColumn('gender', [
            'label' => __('Gender'),
            'renderer' => $this->getGenderRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $tax = $row->getTax();
        if ($tax !== null) {
            $options['option_' . $this->getGenderRenderer()->calcOptionHash($tax)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return GenderColumn
     * @throws LocalizedException
     */
    private function getGenderRenderer()
    {
        if (!$this->genderRenderer) {
            $this->genderRenderer = $this->getLayout()->createBlock(
                GenderColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->genderRenderer;
    }
}
