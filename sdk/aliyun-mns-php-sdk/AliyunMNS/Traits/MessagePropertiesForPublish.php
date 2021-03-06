<?php
namespace AliyunMNS\Traits;

use AliyunMNS\Constants;
use AliyunMNS\Model\MessageAttributes;

trait MessagePropertiesForPublish
{
    public $messageBody;
    public $messageTag;
    public $messageAttributes;

    public function getMessageBody()
    {
        return $this->messageBody;
    }

    public function setMessageBody($messageBody)
    {
        $this->messageBody = $messageBody;
    }

    /**
     * @return mixed
     */
    public function getMessageTag()
    {
        return $this->messageTag;
    }

    /**
     * @param mixed $messageTag
     */
    public function setMessageTag($messageTag): void
    {
        $this->messageTag = $messageTag;
    }

    public function getMessageAttributes()
    {
        return $this->messageAttributes;
    }

    public function setMessageAttributes($messageAttributes)
    {
        $this->messageAttributes = $messageAttributes;
    }

    public function writeMessagePropertiesForPublishXML(\XMLWriter $xmlWriter)
    {
        if ($this->messageBody != NULL) {
            $xmlWriter->writeElement(Constants::MESSAGE_BODY, $this->messageBody);
            if ($this->messageTag) {
                $xmlWriter->writeElement(Constants::MESSAGE_TAG, $this->messageTag);
            }
        }
        if ($this->messageAttributes !== NULL) {
            $this->messageAttributes->writeXML($xmlWriter);
        }
    }
}

?>
