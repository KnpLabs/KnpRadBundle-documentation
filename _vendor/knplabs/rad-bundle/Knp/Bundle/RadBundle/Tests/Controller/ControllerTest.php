<?php

namespace Knp\Bundle\RadBundle\Tests\Controller;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testFindEntityOr404()
    {
        $repository = $this->getEntityRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo(123))
            ->will($this->returnValue($entity = new \stdClass))
        ;
        $controller = $this->getControllerMock(array('getEntityRepository'));
        $controller
            ->expects($this->any())
            ->method('getEntityRepository')
            ->with($this->equalTo('Knp\Blog\Post'))
            ->will($this->returnValue($repository))
        ;

        $this->assertEquals($entity, $controller->findEntityOr404('Knp\Blog\Post', 123));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testFindEntityOr404WhenTheEntityIsNotFound()
    {
        $repository = $this->getEntityRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo(123))
            ->will($this->returnValue(null))
        ;
        $controller = $this->getControllerMock(array('getEntityRepository'));
        $controller
            ->expects($this->any())
            ->method('getEntityRepository')
            ->with($this->equalTo('Knp\Blog\Post'))
            ->will($this->returnValue($repository))
        ;

        $controller->findEntityOr404('Knp\Blog\Post', 123);
    }

    public function testFindDocumentOr404()
    {
        $repository = $this->getDocumentRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo('mongoid123'))
            ->will($this->returnValue($document = new \stdClass))
        ;
        $controller = $this->getControllerMock(array('getDocumentRepository'));
        $controller
            ->expects($this->any())
            ->method('getDocumentRepository')
            ->with($this->equalTo('Knp\Blog\Post'))
            ->will($this->returnValue($repository))
        ;

        $this->assertEquals($document, $controller->findDocumentOr404('Knp\Blog\Post', 'mongoid123'));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testFindDocumentOr404WhenTheEntityIsNotFound()
    {
        $repository = $this->getDocumentRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo('mongoid123'))
            ->will($this->returnValue(null))
        ;
        $controller = $this->getControllerMock(array('getDocumentRepository'));
        $controller
            ->expects($this->any())
            ->method('getDocumentRepository')
            ->with($this->equalTo('Knp\Blog\Post'))
            ->will($this->returnValue($repository))
        ;

        $controller->findDocumentOr404('Knp\Blog\Post', 'mongoid123');
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

    private function getEntityRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    private function getDocumentRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
