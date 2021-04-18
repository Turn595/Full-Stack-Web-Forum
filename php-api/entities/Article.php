<?php

//Include the doctrine API
use Doctrine\ORM\Mapping as ORM;
use \Symfony\Component\Validator\Constraints as Assert;


//Specifies the table name and the type of object (Entity) this must be above the class.
/**
 * @ORM\Entity
 * @ORM\Table(name="article")
 */
class Article
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(
     *      min = 5,
     *      max = 50,
     *      minMessage = "Title must be 5 at least characters long.",
     *      maxMessage = "Title must be less than 50 characters."
     * )
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(
     *      min = 5,
     *      max = 50,
     *      minMessage = "Secondary Title must be at least 5 characters long.",
     *      maxMessage = "Secondary Title must be less than 50 characters."
     * )
     */
    protected $subtitle;

    /**
     * @ORM\Column(type="string", length=5000)
     * @Assert\Length(
     *      min = 8,
     *      max = 50,
     *      minMessage = "The Body must be at least  characters long.",
     *      maxMessage = "The Body must be less than 5000 characters."
     * )
     */
    protected $body;

    /**
     * @ORM\Column(type="string", length=2000)
     * @Assert\Length(
     *      min = 0,
     *      max = 50,
     *      minMessage = "There is something wrong with your URL.",
     *      maxMessage = "The URL must be less than 2000 characters long."
     * )
     */
    protected $bannerURL;

    /**
     * @ORM\Column(type="integer")
     */
    protected $date;

    /**
     * @ORM\Column (type="string", length=50)
     * @Assert\Regex("/^\w*$/")
     */
    protected $username;


    /***********************
     * GETTER and SETTERS
     ***********************/

    /**
     * @return int|null */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle($subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBannerURL(): string
    {
        return $this->bannerURL;
    }

    /**
     * @param string $bannerURL
     */
    public function setBannerURL(string $bannerURL): void
    {
        $this->bannerURL = $bannerURL;
    }

    /**
     * @return int
     */
    public function getDate(): int
    {
        return $this->date;
    }

    /**
     * @param int $date
     */
    public function setDate(int $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }


}

//WE NEVER WANT the closing php tag in a CLASS declaration files

