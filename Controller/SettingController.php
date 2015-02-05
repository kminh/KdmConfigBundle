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

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
class SettingController extends CRUDController
{
    /**
     * Manage multiple settings in one go
     *
     * @return Response
     */
    public function manageAction(Request $request)
    {
        // use Edit permission for now
        if (false === $this->admin->isGranted('EDIT')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->admin->getForm();

        if ($request->isMethod('POST')) {
        }

        return $this->render('KdmConfigBundle:CRUD:manage_settings.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
