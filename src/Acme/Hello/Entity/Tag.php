<?php

namespace Acme\Hello\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Acme\Hello\Entity\Tag
 */
class Tag
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Acme\Hello\Entity\Post
     */
    private $posts;


    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set posts
     *
     * @param Acme\Hello\Entity\Post $posts
     */
    public function setPosts(\Acme\Hello\Entity\Post $posts)
    {
        $this->posts = $posts;
    }

    /**
     * Get posts
     *
     * @return Acme\Hello\Entity\Post 
     */
    public function getPosts()
    {
        return $this->posts;
    }
}