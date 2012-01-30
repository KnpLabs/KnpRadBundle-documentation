<?php

namespace Acme\Hello\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Acme\Hello\Entity\Author
 */
class Author
{
    /**
     * @var string $first_name
     */
    private $first_name;

    /**
     * @var string $last_name
     */
    private $last_name;

    /**
     * @var text $biography
     */
    private $biography;

    /**
     * @var integer $id
     */
    private $id;


    /**
     * Set first_name
     *
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set biography
     *
     * @param text $biography
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
    }

    /**
     * Get biography
     *
     * @return text 
     */
    public function getBiography()
    {
        return $this->biography;
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
}