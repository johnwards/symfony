<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

class Locale extends \Symfony\Component\Validator\Constraint
{
    public $message = 'This value is not a valid locale';

    /**
     * {@inheritDoc}
     */
    public function targets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}