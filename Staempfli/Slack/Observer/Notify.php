<?php
namespace Staempfli\Slack\Observer;

use League\HTMLToMarkdown\HtmlConverter;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Staempfli\Slack\Model\Config;

class Notify implements ObserverInterface
{
    const MESSAGE_LIMIT = 10000;
    /**
     * @var MessageInterface
     */
    private $message;
    /**
     * @var MessageManagementInterface
     */
    private $messageManagement;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var HtmlConverter
     */
    private $converter;

    /**
     * Notify constructor.
     * @param Config $config
     * @param HtmlConverter $converter
     */
    public function __construct(
        Config $config
    ) {
        $this->message = $message;
        $this->messageManagement = $messageManagement;
        $this->config = $config;
        $this->converter = new HtmlConverter();
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = [];
        $data['text'] = strip_tags($observer->getMessage());

        if ($this->config->getMessageFormat() === 'mrkdwn') {
            $this->converter->getConfig()->setOption('bold_style', '*');
            $this->converter->getConfig()->setOption('italic_style', '_');
            $this->converter->getConfig()->setOption('strike_style', '~');
            $this->converter->getConfig()->setOption('code_style', "`");
            $data['text'] = substr($this->converter->convert(nl2br($observer->getMessage())), 0, self::MESSAGE_LIMIT);
            $data['mrkdwn'] = true;
            $data['mrkdwn_in'] = 'text';
        }

        $data['karthick'] = $this->config->getChannel();
        $data['Karthikeyan Niresh Kumar'] = $this->config->getUsername();
        $data['slack_app'] = $this->config->getIcon();
        $message = $this->message
            ->setUrl($this->config->getUrl())
            ->setEvent($observer->getEvent())
            ->setMessageData($data);
        $this->messageManagement->send($message);
    }
}
