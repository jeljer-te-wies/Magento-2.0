<?php
namespace Reload\Seo\Ui\Component\Listing\Columns;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Reload\Seo\Model\Score;

class SeoScore extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'column.seo_score';

    protected $_score;

    protected $_request;

    public function __construct(ContextInterface $context,
                                UiComponentFactory $componentFactory,
                                Score $score,
                                RequestInterface $request,
                                array $components = [],
                                array $data = [])
    {
        parent::__construct($context, $componentFactory, $components, $data);

        $this->_request = $request;
        $this->_score = $score;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as & $item) {
                $score = $this->_score->getResourceCollection()->getItemByColumnValue('reference_id', $item['entity_id']);
                $updateKey = $item['entity_id'] . '-product-' . (int) $this->_request->getParam('store');

                if ($score->getScore() != null && $score->getScore() !== "") {
                    $item[$fieldName] = '<div class="seo-score-grid ' . $updateKey . '"><div style="width: 18px; height: 18px; margin-right: 5px; float: left; border-radius: 100px; background-color: ' . $score->getColor() . ';"></div>' . $score->getScore() . '</div>';
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}