<?php
namespace Satis2020\ServicePackage\Listeners;
use Satis2020\ServicePackage\Jobs\SendMail;
use Illuminate\Events\Dispatcher;

class MailEventSubscriber
{
    /**
     * Handle user login events.
     * @param $event
     */
    public function handleSendMail($event)
    {
        SendMail::dispatch($event->mailable);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Satis2020\ServicePackage\Events\SendMail',
            'Satis2020\ServicePackage\Listeners\MailEventSubscriber@handleSendMail'
        );
    }
}