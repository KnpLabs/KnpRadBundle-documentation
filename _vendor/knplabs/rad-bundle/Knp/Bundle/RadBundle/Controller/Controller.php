<?php

namespace Knp\Bundle\RadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller.
 *
 * Provides missing methods for the base controller.
 */
class Controller extends BaseController
{
    /**
     * Shortcut to return Validator service.
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->get('validator');
    }

    /**
     * Shortcut to validator validate method.
     *
     * @param object     $object The object to validate
     * @param array|null $groups The validator groups to use for validating
     *
     * @return ConstraintViolationList
     */
    public function validate($object, $groups = null)
    {
        return $this->getValidator()->validate($object, $groups);
    }

    /**
     * Shortcut to return Session instance.
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->getRequest()->getSession();
    }

    /**
     * Shortcut to return the Security Context service.
     *
     * @return SecurityContext
     *
     * @throws \LogicException If SecurityBundle is not available
     */
    public function getSecurityContext()
    {
        if (!$this->container->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        return $this->container->get('security.context');
    }

    /**
     * Shortcut to check current user rights with Security Context.
     *
     * @param array $attributes
     * @param mixed $object
     *
     * @return Boolean
     */
    public function isGranted($attributes, $object = null)
    {
        return $this->getSecurityContext()->isGranted($attributes, $object);
    }

    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *     throw $this->createAccessDeniedException('You have no rights');
     *
     * @return AccessDeniedException
     */
    public function createAccessDeniedException($message = 'Access Denied', \Exception $previous = null)
    {
        return new AccessDeniedException($message, $previous);
    }

    /**
     * Shortcut to return Doctrine EntityManager service.
     *
     * @param string $name The entity manager name (null for the default one)
     *
     * @return EntityManager
     */
    public function getEntityManager($name = null)
    {
        return $this->getDoctrine()->getEntityManager($name);
    }

    /**
     * Shortcut to return Doctrine Entity Repository.
     *
     * @param string $repositoryName The repository name
     * @param string $managerName    The entity manager name (null for default one)
     *
     * @return EntityRepository
     */
    public function getEntityRepository($repositoryName, $managerName = null)
    {
        return $this->getEntityManager($managerName)->getRepository($repositoryName);
    }

    /**
     * Shortcut to return Doctrine ManagerRegistry service.
     *
     * @param string $name The document manager name (null for the default one)
     *
     * @return ManagerRegistry
     */
    public function getDocumentManager($name = null)
    {
        return $this->getDoctrine()->getManager($name);
    }

    /**
     * Shortcut to return Doctrine Document Repository.
     *
     * @param string $repositoryName The repository name
     * @param string $managerName    The document manager name (null for default one)
     *
     * @return DocumentRepository
     */
    public function getDocumentRepository($repositoryName, $managerName = null)
    {
        return $this->getDocumentManager($managerName)->getRepository($repositoryName);
    }

    /**
     * Renders a hash into JSON.
     *
     * @param array    $hash     The hash
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function renderJson(array $hash, Response $response = null)
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent(json_encode($hash));
        $response->headers->set('Content-type', 'application/json');

        return $response;
    }

    /**
     * Finds the entity matching the specified id or throws a NotFoundHttpException
     *
     * @param  string $class       The entity class name
     * @param  mixed  $id          The entity identifier
     * @param  string $managerName The entity manager to use
     *
     * @return object The found entity
     *
     * @throws NotFoundHttpException if the entity was not found
     */
    public function findEntityOr404($class, $id, $managerName = null)
    {
        $entity = $this->getEntityRepository($class, $managerName)->find($id);

        if (null === $entity) {
            throw $this->createNotFoundException(sprintf(
                'The %s entity with id "%s" was not found.',
                $class,
                is_array($id) ? implode(' ', $id) : $id
            ));
        }

        return $entity;
    }

    /**
     * Finds the document matching the specified id or throws a NotFoundHttpException
     *
     * @param  string $class       The document class name
     * @param  string $id          The document class name
     * @param  string $managerName The document manager to use
     *
     * @return object The found document
     *
     * @throws NotFoundHttpException if the document was not found
     */
    public function findDocumentOr404($class, $id, $managerName = null)
    {
        $document = $this->getDocumentRepository($class, $managerName)->find($id);

        if (null === $document) {
            throw $this->createNotFoundException(sprintf(
                'The %s document with id "%s" was not found.',
                $class,
                $id
            ));
        }

        return $document;
    }

    /**
     * Adds some dynamic shortcuts
     *
     * @method find{EntityName}EntityOr404($entityId, $managerName = null)
     */
    public function __call($method, array $arguments)
    {
        if (preg_match('/^find(\w+)(Entity|Document)Or404$/', $method, $matches)) {
            if (0 === count($arguments)) {
                throw new \BadMethodCallException(
                    'You must pass an id as first argument.'
                );
            }

            $name    = sprintf('App:%s', $matches[1]);
            $id      = $arguments[0];
            $manager = isset($arguments[1]) ? $arguments[1] : null;

            switch ($matches[2]) {
                case 'Entity':
                    return $this->findEntityOr404($name, $id, $manager);
                case 'Document':
                    return $this->findDocumentOr404($name, $id, $manager);
            }
        }

        throw new \BadMethodCallException(sprintf(
            'The method %s does not exist.',
            $method
        ));
    }
}
