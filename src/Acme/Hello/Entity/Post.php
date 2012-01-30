<?php

namespace Acme\Hello\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Acme\Hello\Entity\Post
 */
class Post
{
    /**
     * @var string $title
     */
    private $title;

    /**
     * @var text $body
     */
    private $body;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Acme\Hello\Entity\Author
     */
    private $author;

    /**
     * @var Acme\Hello\Entity\Tag
     */
    private $tags;

    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param text $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get body
     *
     * @return text 
     */
    public function getBody()
    {
        return $this->body;
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
     * Set author
     *
     * @param Acme\Hello\Entity\Author $author
     */
    public function setAuthor(\Acme\Hello\Entity\Author $author)
    {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return Acme\Hello\Entity\Author 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Add tags
     *
     * @param Acme\Hello\Entity\Tag $tags
     */
    public function addTag(\Acme\Hello\Entity\Tag $tags)
    {
        $this->tags[] = $tags;
    }

    /**
     * Get tags
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }
}