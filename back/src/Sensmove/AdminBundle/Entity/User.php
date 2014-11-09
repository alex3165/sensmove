<?php

namespace Sensmove\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 */
class User
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $isPro;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var boolean
     */
    private $haveDevice;


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
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
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
     * Set isPro
     *
     * @param boolean $isPro
     * @return User
     */
    public function setIsPro($isPro)
    {
        $this->isPro = $isPro;
    
        return $this;
    }

    /**
     * Get isPro
     *
     * @return boolean 
     */
    public function getIsPro()
    {
        return $this->isPro;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set haveDevice
     *
     * @param boolean $haveDevice
     * @return User
     */
    public function setHaveDevice($haveDevice)
    {
        $this->haveDevice = $haveDevice;
    
        return $this;
    }

    /**
     * Get haveDevice
     *
     * @return boolean 
     */
    public function getHaveDevice()
    {
        return $this->haveDevice;
    }
}
