<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\Type;

require_once __DIR__.'/TestCase.php';

use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ChoiceTypeTest extends TestCase
{
    protected $choices = array(
        'a' => 'Bernhard',
        'b' => 'Fabien',
        'c' => 'Kris',
        'd' => 'Jon',
        'e' => 'Roman',
    );

    protected $preferredChoices = array('d', 'e');

    protected $groupedChoices = array(
        'Symfony' => array(
            'a' => 'Bernhard',
            'b' => 'Fabien',
            'c' => 'Kris',
        ),
        'Doctrine' => array(
            'd' => 'Jon',
            'e' => 'Roman',
        )
    );

    protected $numericChoices = array(
        0 => 'Bernhard',
        1 => 'Fabien',
        2 => 'Kris',
        3 => 'Jon',
        4 => 'Roman',
    );

    public function testIsChoiceSelectedDifferentiatesBetweenZeroAndEmpty_integerZero()
    {
        $field = $this->factory->create('choice', 'name', array(
            'choices' => array(
                0 => 'Foo',
                '' => 'Bar',
            )
        ));

        $field->bind(0);

        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected(0, $field->getClientData()));
        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected('0', $field->getClientData()));
        $this->assertFalse($field->getRenderer()->getVar('choice_list')->isChoiceSelected('', $field->getClientData()));

        $field->bind('0');

        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected(0, $field->getClientData()));
        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected('0', $field->getClientData()));
        $this->assertFalse($field->getRenderer()->getVar('choice_list')->isChoiceSelected('', $field->getClientData()));

        $field->bind('');

        $this->assertFalse($field->getRenderer()->getVar('choice_list')->isChoiceSelected(0, $field->getClientData()));
        $this->assertFalse($field->getRenderer()->getVar('choice_list')->isChoiceSelected('0', $field->getClientData()));
        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected('', $field->getClientData()));
    }

    public function testIsChoiceSelectedDifferentiatesBetweenZeroAndEmpty_stringZero()
    {
        $field = $this->factory->create('choice', 'name', array(
            'choices' => array(
                '0' => 'Foo',
                '' => 'Bar',
            )
        ));

        $field->bind(0);

        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected(0, $field->getClientData()));
        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected('0', $field->getClientData()));
        $this->assertFalse($field->getRenderer()->getVar('choice_list')->isChoiceSelected('', $field->getClientData()));

        $field->bind('0');

        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected(0, $field->getClientData()));
        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected('0', $field->getClientData()));
        $this->assertFalse($field->getRenderer()->getVar('choice_list')->isChoiceSelected('', $field->getClientData()));

        $field->bind('');

        $this->assertFalse($field->getRenderer()->getVar('choice_list')->isChoiceSelected(0, $field->getClientData()));
        $this->assertFalse($field->getRenderer()->getVar('choice_list')->isChoiceSelected('0', $field->getClientData()));
        $this->assertTrue($field->getRenderer()->getVar('choice_list')->isChoiceSelected('', $field->getClientData()));
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testConfigureChoicesWithNonArray()
    {
        $field = $this->factory->create('choice', 'name', array(
            'choices' => new \ArrayObject(),
        ));
    }

    public function getChoicesVariants()
    {
        $choices = $this->choices;

        return array(
            array($choices),
            array(function () use ($choices) { return $choices; }),
        );
    }

    public function getNumericChoicesVariants()
    {
        $choices = $this->numericChoices;

        return array(
            array($choices),
            array(function () use ($choices) { return $choices; }),
        );
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testClosureShouldReturnArray()
    {
        $field = $this->factory->create('choice', 'name', array(
            'choices' => function () { return 'foobar'; },
        ));

        // trigger closure
        $field->getRenderer()->getVar('choices');
    }

    /**
     * @dataProvider getChoicesVariants
     */
    public function testSubmitSingleNonExpanded($choices)
    {
        $field = $this->factory->create('choice', 'name', array(
            'multiple' => false,
            'expanded' => false,
            'choices' => $choices,
        ));

        $field->bind('b');

        $this->assertEquals('b', $field->getData());
        $this->assertEquals('b', $field->getClientData());
    }

    /**
     * @dataProvider getChoicesVariants
     */
    public function testSubmitMultipleNonExpanded($choices)
    {
        $field = $this->factory->create('choice', 'name', array(
            'multiple' => true,
            'expanded' => false,
            'choices' => $choices,
        ));

        $field->bind(array('a', 'b'));

        $this->assertEquals(array('a', 'b'), $field->getData());
        $this->assertEquals(array('a', 'b'), $field->getClientData());
    }

    /**
     * @dataProvider getChoicesVariants
     */
    public function testSubmitSingleExpanded($choices)
    {
        $field = $this->factory->create('choice', 'name', array(
            'multiple' => false,
            'expanded' => true,
            'choices' => $choices,
        ));

        $field->bind('b');

        $this->assertSame('b', $field->getData());
        $this->assertSame(false, $field['a']->getData());
        $this->assertSame(true, $field['b']->getData());
        $this->assertSame(false, $field['c']->getData());
        $this->assertSame(false, $field['d']->getData());
        $this->assertSame(false, $field['e']->getData());
        $this->assertSame('', $field['a']->getClientData());
        $this->assertSame('1', $field['b']->getClientData());
        $this->assertSame('', $field['c']->getClientData());
        $this->assertSame('', $field['d']->getClientData());
        $this->assertSame('', $field['e']->getClientData());
    }

    /**
     * @dataProvider getNumericChoicesVariants
     */
    public function testSubmitSingleExpandedNumericChoices($choices)
    {
        $field = $this->factory->create('choice', 'name', array(
            'multiple' => false,
            'expanded' => true,
            'choices' => $choices,
        ));

        $field->bind('1');

        $this->assertSame(1, $field->getData());
        $this->assertSame(false, $field[0]->getData());
        $this->assertSame(true, $field[1]->getData());
        $this->assertSame(false, $field[2]->getData());
        $this->assertSame(false, $field[3]->getData());
        $this->assertSame(false, $field[4]->getData());
        $this->assertSame('', $field[0]->getClientData());
        $this->assertSame('1', $field[1]->getClientData());
        $this->assertSame('', $field[2]->getClientData());
        $this->assertSame('', $field[3]->getClientData());
        $this->assertSame('', $field[4]->getClientData());
    }

    /**
     * @dataProvider getChoicesVariants
     */
    public function testSubmitMultipleExpanded($choices)
    {
        $field = $this->factory->create('choice', 'name', array(
            'multiple' => true,
            'expanded' => true,
            'choices' => $choices,
        ));

        $field->bind(array('a' => 'a', 'b' => 'b'));

        $this->assertSame(array('a', 'b'), $field->getData());
        $this->assertSame(true, $field['a']->getData());
        $this->assertSame(true, $field['b']->getData());
        $this->assertSame(false, $field['c']->getData());
        $this->assertSame(false, $field['d']->getData());
        $this->assertSame(false, $field['e']->getData());
        $this->assertSame('1', $field['a']->getClientData());
        $this->assertSame('1', $field['b']->getClientData());
        $this->assertSame('', $field['c']->getClientData());
        $this->assertSame('', $field['d']->getClientData());
        $this->assertSame('', $field['e']->getClientData());
    }

    /**
     * @dataProvider getNumericChoicesVariants
     */
    public function testSubmitMultipleExpandedNumericChoices($choices)
    {
        $field = $this->factory->create('choice', 'name', array(
            'multiple' => true,
            'expanded' => true,
            'choices' => $choices,
        ));

        $field->bind(array(1 => 1, 2 => 2));

        $this->assertSame(array(1, 2), $field->getData());
        $this->assertSame(false, $field[0]->getData());
        $this->assertSame(true, $field[1]->getData());
        $this->assertSame(true, $field[2]->getData());
        $this->assertSame(false, $field[3]->getData());
        $this->assertSame(false, $field[4]->getData());
        $this->assertSame('', $field[0]->getClientData());
        $this->assertSame('1', $field[1]->getClientData());
        $this->assertSame('1', $field[2]->getClientData());
        $this->assertSame('', $field[3]->getClientData());
        $this->assertSame('', $field[4]->getClientData());
    }
}