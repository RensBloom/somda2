<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="somda_forum_read_4", indexes={@ORM\Index(name="somda_forum_read_4_idx_uid", columns={"uid"})})
 * @ORM\Entity
 */
class ForumRead4
{
    /**
     * @var ForumPost
     * @ORM\ManyToOne(targetEntity="App\Entity\ForumPost")
     * @ORM\JoinColumn(name="postid", referencedColumnName="postid")
     * @ORM\Id
     */
    private $post;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
     * @ORM\Id
     */
    private $user;

    /**
     * @return ForumPost
     */
    public function getPost(): ForumPost
    {
        return $this->post;
    }

    /**
     * @param ForumPost $post
     * @return ForumRead4
     */
    public function setPost(ForumPost $post): ForumRead4
    {
        $this->post = $post;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return ForumRead4
     */
    public function setUser(User $user): ForumRead4
    {
        $this->user = $user;
        return $this;
    }
}
