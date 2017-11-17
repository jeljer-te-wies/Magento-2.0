<?php


namespace Reload\Seo\Controller\Adminhtml\Seo;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Phrase;
use \Reload\Seo\Helper\Data;
use \Reload\Seo\Model\Score;

class saveResult extends \Magento\Backend\App\Action
{
    protected $dataHelper;

    protected $score;

    public function __construct(Context $context, Data $dataHelper, Score $score)
    {
        $this->dataHelper = $dataHelper;
        $this->score = $score;
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();
        $post = $request->getPost();
        $requestKey = $request->getParam('request_key');

        //Obtain and remove the request so it won't be handled again.
        $updateRequest = $this->dataHelper->removeScoreUpdateRequest($requestKey);

        if($updateRequest == null || !array_key_exists('score', $post))
        {
            //The request key does not exist, just ignore it.
            $result = null;
        }
        else
        {
            //Get the score from the post.
            $score = $post['score'];

            if($score === null || !array_key_exists('score', $score))
            {
                //No score was found.
                $result = new Phrase('Something went wrong while updating the ' . $updateRequest['type'] . ' SEO status.');
            }
            else
            {
                //Obtain the score for merging.
                $scoreObject = $this->_objectManager->create('\Reload\Seo\Model\Score')->loadById($updateRequest['id'], $updateRequest['type']);

//                $scoreObject = $score->loadById($updateRequest['id'], $updateRequest['type']);

                //Merge the result in the score object.
                $scoreObject->mergeFromResult($score);

                $result = null;
            }
        }

        //Set the result as response.
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json', true);
        $this->getResponse()->setBody(json_encode($result));
    }
}