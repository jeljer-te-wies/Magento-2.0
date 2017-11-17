<?php
/**
 * @category   Reload
 * @package    Reload_Seo
 * @copyright  Copyright (c) 2013-2015 AndCode (http://www.andcode.nl)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Reload_Seo_Model_Score is the model for storing the score results with.
 */
namespace Reload\Seo\Model;

use \Magento\Framework\App\Request\Http;
use \Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\DB\Transaction;
use \Magento\Framework\Model\AbstractModel;

class Score extends AbstractModel
{
    const CACHE_TAG = 'reload_seo_score';

    protected $_objectManager;

    /**
     * _construct basic magento constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\Reload\Seo\Model\ResourceModel\Score');

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        parent::_construct();
    }

    /**
     * getRulesCollection returns the rules collection which are linked to this score object.
     * 
     * @return Reload_Seo_Model_Resource_Scores_Rule_Collection
     */
    public function getRulesCollection()
    {
        return $this->_objectManager->create('\Reload\Seo\Model\Scores\Rule')
            ->getCollection()
            ->addFieldToFilter('score_id', array('eq' => $this->getId()));
    }

    /**
     * loadById loads an score object by the given reference id and the type.
     * 
     * @param  string $id
     * @param  string $type
     * @return Reload_Seo_Model_Score
     */
    public function loadById($id, $type)
    {
        //If id === null, we want to search the record with reference id 0
        if($id === null)
        {
            $id = 0;
        }

        $request = $this->_objectManager->create('\Magento\Framework\App\Request\Http');
        $storeId = (int) $request->getParam('store');

        //Search the collection for items with the type and the reference id and select the first result.
        $score = $this->getCollection()
            ->addFieldToFilter('type', array('eq' => $type))
            ->addFieldToFilter('reference_id', array('eq' => $id))
            ->addFieldToFilter('store_id', array('eq' => $storeId))
            ->getFirstItem();

        if($score === null)
        {
            //No score found, create a new one.
            $score = new Score();
        }

        $score->setDefaultKeywords($score->getKeywords());
        $score->setDefaultSynonyms($score->getSynonyms());

        if($score->getStoreId() != 0)
        {
            $defaultScore = $this->getCollection()
                ->addFieldToFilter('type', array('eq' => $type))
                ->addFieldToFilter('reference_id', array('eq' => $id))
                ->addFieldToFilter('store_id', array('eq' => 0))
                ->getFirstItem();

            if($defaultScore != null)
            {
                $score->setDefaultKeywords($defaultScore->getKeywords());
                $score->setDefaultSynonyms($defaultScore->getSynonyms());

                if($score->getKeywords() == null)
                {
                    $score->setKeywords($defaultScore->getKeywords());
                }

                if($score->getSynonyms() == null)
                {
                    $score->setSynonyms($defaultScore->getSynonyms());
                }
            }
        }

        //Set the type and reference id for the case when the score object does not exist yet.
        $score->setType($type);
        $score->setReferenceId($id);
        $score->setStoreId($storeId);

        if($id == null)
        {
            $score->setKeywords('');
            $score->setSynonyms('');
        }

        return $score;
    }

    /**
     * mergeFromResult merges an Reload API result with this score item.
     * 
     * @param  array $result
     * @return void
     */
    public function mergeFromResult($result)
    {
        //Update this object with the score and color and save this object.
        $this->setScore($result['score']);
        $this->setColor($result['color']);
        $this->save();

        foreach($this->getRulesCollection() as $rule)
        {
            //Obtain all the rules of this score object and delete it.
            $rule->delete();
        }

        $transaction = new Transaction();

        foreach($result['rules'] as $ruleResult)
        {
            //Loop over the rules in the result and bind them to this score object.
            $rule = $this->_objectManager->create('\Reload\Seo\Model\Scores\Rule');
            $rule->setScoreId($this->getId());
            $rule->setField($ruleResult['field']);
            $rule->setTitle($ruleResult['title']);
            $rule->setStatus($ruleResult['color']);

            $transaction->addObject($rule);
        }
        $transaction->save();
    }

    public function generateKeywords($name)
    {
        $this->setKeywords($name);
    }
}