<?php

//Include the doctrine API
use Doctrine\ORM\Mapping as ORM;
use \Symfony\Component\Validator\Constraints as Assert;


//Specifies the table name and the type of object (Entity) this must be above the class.
/**
 * @ORM\Entity
 * @ORM\Table(name="comment")
 */
class Comment
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     *
     */
    protected $articleid;

    /**
     * @ORM\Column (type="string", length=50)
     * @Assert\Regex("/^\w*$/")
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\Length(
     *      min = 8,
     *      max = 200,
     *      minMessage = "Your comment must be at least 8 characters long.",
     *      maxMessage = "Your comment cannot be longer than 200 characters."
     * )
     */
    protected $body;

    /**
     * @ORM\Column(type="integer")
     */
    protected $date;

    /***********************
     * GETTER and SETTERS
     ***********************/

    /**
     * @return string
     */
    public function getUsername():string
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

    /**
     * @return string
     */
    public function getBody():string
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
     * @return int
     */
    public function getArticleid(): int
    {
        return $this->articleid;
    }

    /**
     * @param int $articleid
     */
    public function setArticleid(int $articleid): void
    {
        $this->articleid = $articleid;
    }

    /**
     * @return int|null */
    public function getId(): ?int
    {
        return $this->id;
    }
}

//WE NEVER WANT the closing php tag in a CLASS declaration files

