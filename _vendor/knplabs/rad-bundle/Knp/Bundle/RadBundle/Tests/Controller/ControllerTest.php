<?php

namespace Knp\Bundle\RadBundle\Tests\Controller;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testFindEntityCalls()
    {
        $controller = $this->getControllerMock(array('findEntity'));
        $controller
            ->expects($this->once())
            ->method('findEntity')
            ->with($this->equalTo('App:BurgerRecipe'), $this->equalTo(123))
            ->will($this->returnValue($burgerRecipe = new \stdClass))
        ;

        $this->assertEquals($burgerRecipe, $controller->findBurgerRecipeEntity(123));
    }

    public function testFindEntityByCalls()
    {
        $controller = $this->getControllerMock(array('findEntityBy'));
        $controller
            ->expects($this->once())
            ->method('findEntityBy')
            ->with($this->equalTo('App:BurgerRecipe'), $this->equalTo(array('slug' => 'some-slug')))
            ->will($this->returnValue($burgerRecipe = new \stdClass))
        ;

        $this->assertEquals($burgerRecipe, $controller->findBurgerRecipeEntityBySlug('some-slug'));
    }

    public function testFindEntityOr404Calls()
    {
        $controller = $this->getControllerMock(array('findEntityOr404'));
        $controller
            ->expects($this->once())
            ->method('findEntityOr404')
            ->with($this->equalTo('App:BurgerRecipe'), $this->equalTo(123))
            ->will($this->returnValue($burgerRecipe = new \stdClass))
        ;

        $this->assertEquals($burgerRecipe, $controller->findBurgerRecipeEntityOr404(123));
    }

    public function testFindEntityByOr404Calls()
    {
        $controller = $this->getControllerMock(array('findEntityByOr404'));
        $controller
            ->expects($this->once())
            ->method('findEntityByOr404')
            ->with($this->equalTo('App:BurgerRecipe'), $this->equalTo(array('slug' => 'some-slug')))
            ->will($this->returnValue($burgerRecipe = new \stdClass))
        ;

        $this->assertEquals($burgerRecipe, $controller->findBurgerRecipeEntityBySlugOr404('some-slug'));
    }

    public function testFindDocumentOr404Calls()
    {
        $controller = $this->getControllerMock(array('findDocumentOr404'));
        $controller
            ->expects($this->once())
            ->method('findDocumentOr404')
            ->with($this->equalTo('App:BurgerRecipe'), $this->equalTo('mongoid123'))
            ->will($this->returnValue($burgerRecipe = new \stdClass))
        ;

        $this->assertEquals($burgerRecipe, $controller->findBurgerRecipeDocumentOr404('mongoid123'));
    }

    private function getControllerMock($methods = array())
    {
        return $this->getMock('Knp\Bundle\RadBundle\Controller\Controller', $methods);
    }
}
