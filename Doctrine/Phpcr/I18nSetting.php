<?php

/**
 * This file is part of the KdmConfigBundle package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;

use Kdm\CmfBundle\Translation\LocaleAwareEntity;

/**
 * A translatable setting
 *
 * @author Khang Minh <kminh@kdmlabs.com>
 */
class I18nSetting extends Setting implements TranslatableInterface
{
    use LocaleAwareEntity;
}
