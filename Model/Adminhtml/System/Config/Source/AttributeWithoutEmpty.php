<?php
namespace Reload\Seo\Model\Adminhtml\System\Config\Source;

class AttributeWithoutEmpty extends Attribute
{
    /**
     * toOptionArray searches all product attributes and returns them in an array without an empty option.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        if(array_key_exists(0, $options))
        {
            unset($options[0]);
        }
        return $options;
    }
}