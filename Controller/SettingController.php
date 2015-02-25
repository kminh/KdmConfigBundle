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

use Kdm\ConfigBundle\Model\SettingGroup;

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
abstract class SettingController extends CRUDController
{
    abstract public function manageAction(Request $request);

    /**
     * Manage multiple settings in one go
     *
     * @internal
     * @return Response
     */
    protected function manageSettings(Request $request)
    {
        $form = $this->admin->getForm();

        if ($request->isMethod('POST')) {
            var_dump($request->request); exit;
            var_dump($form->get('title')->getData()); exit;
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
