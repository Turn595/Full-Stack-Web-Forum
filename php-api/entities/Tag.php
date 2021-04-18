<?php

//Include the doctrine API
use Doctrine\ORM\Mapping as ORM;

//Specifies the table name and the type of object (Entity) this must be above the class.
/**
 * @ORM\Entity
 * @ORM\Table(name="tags")
 */
class Tag
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $articleId;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $tag;

    /**
     * @return int
     */
    public function getArticleId(): int
    {
        return $this->articleId;
    }

    /**
     * @param int $articleId
     */
    public function setArticleId(int $articleId): void
    {
        $this->articleId = $articleId;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return int|null */
    public function getId(): ?int
    {
        return $this->id;
    }
}

//WE NEVER WANT the closing php tag in a CLASS declaration files

