<?php
namespace Concrete\Core\Messenger;

use Concrete\Core\Command\Batch\BatchUpdater;
use Concrete\Core\Command\Batch\Command\BatchProcessMessageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

class MessengerEventSubscriber implements EventSubscriberInterface
{

    /**
     * @var BatchUpdater
     */
    protected $batchUpdater;

    public function __construct(BatchUpdater $batchUpdater)
    {
        $this->batchUpdater = $batchUpdater;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageHandledEvent::class => 'handleWorkerMessageHandledEvent',
            WorkerMessageFailedEvent::class => 'handleWorkerMessageFailedEvent',
        ];
    }

    public function handleWorkerMessageHandledEvent(WorkerMessageHandledEvent $event)
    {
        $message = $event->getEnvelope()->getMessage();
        if ($message instanceof BatchProcessMessageInterface) {
            $this->batchUpdater->updateJobs($message->getBatchProcess(), BatchUpdater::COLUMN_PENDING, -1);
        }
    }

    public function handleWorkerMessageFailedEvent(WorkerMessageFailedEvent $event)
    {
        $message = $event->getEnvelope()->getMessage();
        if ($message instanceof BatchProcessMessageInterface) {
            $this->batchUpdater->updateJobs($message->getBatchProcess(), BatchUpdater::COLUMN_PENDING, -1);
            $this->batchUpdater->updateJobs($message->getBatchProcess(), BatchUpdater::COLUMN_FAILED, 1);
        }
    }

}