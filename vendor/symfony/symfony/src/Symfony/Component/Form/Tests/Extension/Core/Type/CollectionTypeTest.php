<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\Type;

class CollectionTypeTest extends \Symfony\Component\Form\Test\TypeTestCase
{
    public function testContainsNoChildByDefault()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'text',
        ));

        $this->assertCount(0, $form);
    }

    public function testSetDataAdjustsSize()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'text',
            'options' => array(
                'max_length' => 20,
            ),
        ));
        $form->setData(array('foo@foo.com', 'foo@bar.com'));

        $this->assertInstanceOf('Symfony\Component\Form\Form', $form[0]);
        $this->assertInstanceOf('Symfony\Component\Form\Form', $form[1]);
        $this->assertCount(2, $form);
        $this->assertEquals('foo@foo.com', $form[0]->getData());
        $this->assertEquals('foo@bar.com', $form[1]->getData());
        $this->assertEquals(20, $form[0]->getConfig()->getOption('max_length'));
        $this->assertEquals(20, $form[1]->getConfig()->getOption('max_length'));

        $form->setData(array('foo@baz.com'));
        $this->assertInstanceOf('Symfony\Component\Form\Form', $form[0]);
        $this->assertFalse(isset($form[1]));
        $this->assertCount(1, $form);
        $this->assertEquals('foo@baz.com', $form[0]->getData());
        $this->assertEquals(20, $form[0]->getConfig()->getOption('max_length'));
    }

    public function testThrowsExceptionIfObjectIsNotTraversable()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'text',
        ));
        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');
        $form->setData(new \stdClass());
    }

    public function testNotResizedIfSubmittedWithMissingData()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'text',
        ));
        $form->setData(array('foo@foo.com', 'bar@bar.com'));
        $form->submit(array('foo@bar.com'));

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertEquals('foo@bar.com', $form[0]->getData());
        $this->assertEquals('', $form[1]->getData());
    }

    public function testResizedDownIfSubmittedWithMissingDataAndAllowDelete()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'text',
            'allow_delete' => true,
        ));
        $form->setData(array('foo@foo.com', 'bar@bar.com'));
        $form->submit(array('foo@foo.com'));

        $this->assertTrue($form->has('0'));
        $this->assertFalse($form->has('1'));
        $this->assertEquals('foo@foo.com', $form[0]->getData());
        $this->assertEquals(array('foo@foo.com'), $form->getData());
    }

    public function testNotResizedIfSubmittedWithExtraData()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'text',
        ));
        $form->setData(array('foo@bar.com'));
        $form->submit(array('foo@foo.com', 'bar@bar.com'));

        $this->assertTrue($form->has('0'));
        $this->assertFalse($form->has('1'));
        $this->assertEquals('foo@foo.com', $form[0]->getData());
    }

    public function testResizedUpIfSubmittedWithExtraDataAndAllowAdd()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'text',
            'allow_add' => true,
        ));
        $form->setData(array('foo@bar.com'));
        $form->submit(array('foo@bar.com', 'bar@bar.com'));

        $this->assertTrue($form->has('0'));
        $this->assertTrue($form->has('1'));
        $this->assertEquals('foo@bar.com', $form[0]->getData());
        $this->assertEquals('bar@bar.com', $form[1]->getData());
        $this->assertEquals(array('foo@bar.com', 'bar@bar.com'), $form->getData());
    }

    public function testAllowAddButNoPrototype()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'form',
            'allow_add' => true,
            'prototype' => false,
        ));

        $this->assertFalse($form->has('__name__'));
    }

    public function testPrototypeMultipartPropagation()
    {
        $form = $this->factory
            ->create('collection', null, array(
                'type' => 'file',
                'allow_add' => true,
                'prototype' => true,
            ))
        ;

        $this->assertTrue($form->createView()->vars['multipart']);
    }

    public function testGetDataDoesNotContainsPrototypeNameBeforeDataAreSet()
    {
        $form = $this->factory->create('collection', array(), array(
            'type' => 'file',
            'prototype' => true,
            'allow_add' => true,
        ));

        $data = $form->getData();
        $this->assertFalse(isset($data['__name__']));
    }

    public function testGetDataDoesNotContainsPrototypeNameAfterDataAreSet()
    {
        $form = $this->factory->create('collection', array(), array(
            'type' => 'file',
            'allow_add' => true,
            'prototype' => true,
        ));

        $form->setData(array('foobar.png'));
        $data = $form->getData();
        $this->assertFalse(isset($data['__name__']));
    }

    public function testPrototypeNameOption()
    {
        $form = $this->factory->create('collection', null, array(
            'type' => 'form',
            'prototype' => true,
            'allow_add' => true,
        ));

        $this->assertSame('__name__', $form->getConfig()->getAttribute('prototype')->getName(), '__name__ is the default');

        $form = $this->factory->create('collection', null, array(
            'type' => 'form',
            'prototype' => true,
            'allow_add' => true,
            'prototype_name' => '__test__',
        ));

        $this->assertSame('__test__', $form->getConfig()->getAttribute('prototype')->getName());
    }

    public function testPrototypeDefaultLabel()
    {
        $form = $this->factory->create('collection', array(), array(
            'type' => 'file',
            'allow_add' => true,
            'prototype' => true,
            'prototype_name' => '__test__',
        ));

        $this->assertSame('__test__label__', $form->createView()->vars['prototype']->vars['label']);
    }

    public function testPrototypeDefaultRequired()
    {
        $form = $this->factory->create('collection', array(), array(
            'type' => 'file',
            'allow_add' => true,
            'prototype' => true,
            'prototype_name' => '__test__',
        ));

        $this->assertTrue($form->createView()->vars['prototype']->vars['required']);
    }

    public function testPrototypeSetNotRequired()
    {
        $form = $this->factory->create('collection', array(), array(
            'type' => 'file',
            'allow_add' => true,
            'prototype' => true,
            'prototype_name' => '__test__',
            'required' => false,
        ));

        $this->assertFalse($form->createView()->vars['required'], 'collection is not required');
        $this->assertFalse($form->createView()->vars['prototype']->vars['required'], '"prototype" should not be required');
    }

    public function testPrototypeSetNotRequiredIfParentNotRequired()
    {
        $child = $this->factory->create('collection', array(), array(
            'type' => 'file',
            'allow_add' => true,
            'prototype' => true,
            'prototype_name' => '__test__',
        ));

        $parent = $this->factory->create('form', array(), array(
            'required' => false,
        ));

        $child->setParent($parent);
        $this->assertFalse($parent->createView()->vars['required'], 'Parent is not required');
        $this->assertFalse($child->createView()->vars['required'], 'Child is not required');
        $this->assertFalse($child->createView()->vars['prototype']->vars['required'], '"Prototype" should not be required');
    }

    public function testPrototypeNotOverrideRequiredByEntryOptionsInFavorOfParent()
    {
        $child = $this->factory->create('collection', array(), array(
            'type' => 'file',
            'allow_add' => true,
            'prototype' => true,
            'prototype_name' => '__test__',
            'options' => array(
                'required' => true,
            ),
        ));

        $parent = $this->factory->create('form', array(), array(
            'required' => false,
        ));

        $child->setParent($parent);

        $this->assertFalse($parent->createView()->vars['required'], 'Parent is not required');
        $this->assertFalse($child->createView()->vars['required'], 'Child is not required');
        $this->assertFalse($child->createView()->vars['prototype']->vars['required'], '"Prototype" should not be required');
    }
}
