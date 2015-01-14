<?php

namespace Erbolking\Bundle\GuestBookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entry
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Entry
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     * @Assert\NotBlank
     */
    private $message;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publicDate", type="datetime")
     */
    private $publicDate;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = {"image/png", "image/gif", "image/jpeg", "image/jpg"},
     *     mimeTypesMessage = "Please upload a valid image"
     * )
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="ipAddress", type="string", length=16)
     */
    private $ipAddress;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(targetEntity="entry", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var Entry
     *
     * @ORM\OneToMany(targetEntity="entry", mappedBy="parent")
     */
    private $children;

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
     * @return Entry
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
     * Set email
     *
     * @param string $email
     * @return Entry
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
     * Set message
     *
     * @param string $message
     * @return Entry
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Entry
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set publicDate
     *
     * @param \DateTime $publicDate
     * @return Entry
     */
    public function setPublicDate($publicDate)
    {
        $this->publicDate = $publicDate;

        return $this;
    }

    /**
     * Get publicDate
     *
     * @return \DateTime 
     */
    public function getPublicDate()
    {
        return $this->publicDate;
    }

    /**
     * Set image
     *
     * @param $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return UploadedFile
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     * @return Entry
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string 
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Get parentId
     *
     * @return Entry
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent
     *
     * @param Entry $parent
     * @return Entry
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get Children
     *
     * @return Entry
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Set Children
     *
     * @param string $children
     * @return Entry
     */
    public function setChildren($children) {
        $this->children = $children;

        return $this;
    }

    /**
     * Get Absolute Image Upload Directory Path
     *
     * @return string
     */
    public function getUploadRootDir()
    {
        return __DIR__. '/../../../../../web/' . $this->getUploadDir();
    }

    /**
     * Get Relative Image Upload Directory Path
     *
     * @return string
     */
    public function getUploadDir()
    {
        return 'uploads/images';
    }

    /**
     * Upload Image
     */
    public function uploadImage()
    {
        if (null === $this->getImage()) {
            return;
        }

        $this->getImage()->move(
            $this->getUploadRootDir(),
            $this->getImage()->getClientOriginalName()
        );
        $this->image = $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->getImage()->getClientOriginalName();
    }
}
