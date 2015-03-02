<?php

/**
 * This file is part of the Kdm package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Sonata\AdminBundle\Controller\CRUDController;

use Kdm\ConfigBundle\Model\SettingAdminInterface;

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
class SettingController extends CRUDController
{
    public function manageAction(Request $request, $_route)
    {
        $this->checkPermission();

        return $this->manageSettings($request, $_route);
    }

    /**
     * Manage multiple settings in one go
     *
     * @internal
     * @return Response
     */
    protected function manageSettings(Request $request, $_route)
    {
        // can't continue if this is not the right admin
        if (!$this->admin instanceof SettingAdminInterface) {
            throw new \DomainException(sprintf('Admin class used with this controller must implement "%s"', SettingAdminInterface::class));
        }

        $settingGroupName = $this->admin->getSettingGroupName();
        if (empty($settingGroupName)) {
            throw new \DomainException('A setting group name must be set for the current admin (implement "getSettingGroupName" and return a string).');
        }

        $form = $this->admin->getForm();

        // each direct child is the direct child setting group of this admin's
        // setting group
        foreach ($form->all() as $child) {
            $childName = $child->getName();
            if (strpos($childName, '_') === 0) {
                $childName = substr_replace($childName, '', 0, 1);
            }

            $child->setData($this->get('settings')->getByGroupName($settingGroupName . '.' . $childName));
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $settings = array();
                foreach ($form->all() as $child) {
                    $settings[$child->getName()] = $child->getData();
                }

                try {
                    $this->get('settings')->saveGroup($settingGroupName, $settings);

                    $this->addFlash('sonata_flash_success', 'Settings saved');
                    return $this->redirectToRoute($_route);
                } catch (\Exception $e) {
                    $this->addFlash('sonata_flash_error', $e->getMessage());
                }
            }
        }

        return $this->render('KdmConfigBundle:CRUD:manage_settings.html.twig', [
            'form' => $form->createView()
        ]);
    }

    protected function checkPermission()
    {
        if (false === $this->admin->isGranted('MANAGE')) {
            throw $this->createAccessDeniedException();
        }
    }
}
