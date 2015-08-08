<?php

/**
 * This file is part of the CmfConfigBundle package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

use Kdm\ConfigBundle\Model\SettingManagerInterface;

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
class KernelEventsSubscriber implements EventSubscriberInterface
{
    protected $container;

    protected $settingManager;

    public function __construct(
        ContainerInterface $container,
        SettingManagerInterface $settingManager)
    {
        $this->container      = $container;
        $this->settingManager = $settingManager;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [ 'onKernelRequest' ]
        ];
    }

    /**
     * Get the locale from the request and pass it to the setting manager
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        /* @var $request Request */
        $request = $event->getRequest();

        $this->settingManager->setLocale($request->getLocale());
    }
}
