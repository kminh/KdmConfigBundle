<?php

/**
 * This file is part of the Kdm package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
abstract class BaseSettingAdmin extends Admin
{
    protected $baseRoutePattern = 'settings';

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        // Custom admin routes, can use: add(), remove(), clearExcept(), getRouterIdParameter()
        parent::configureRoutes($collection);

        $collection->add('manage');
        $collection->clearExcept([
            'manage'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // Fields to be shown on create/edit forms
        parent::configureFormFields($formMapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        // Fields to be shown on filter forms
        parent::configureDatagridFilters($datagridMapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        // Fields to be shown when listing items
        parent::configureListFields($listMapper);
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        // Fields to be shown when viewing an item
        parent::configureShowFields($showMapper);
    }
}
