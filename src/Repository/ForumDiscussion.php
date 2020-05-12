<?php

namespace App\Repository;

use App\Entity\ForumDiscussion as ForumDiscussionEntity;
use App\Entity\ForumForum;
use App\Entity\ForumPost;
use App\Entity\User;
use App\Form\ForumPost as ForumPostForm;
use App\Generics\DateGenerics;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;
use Exception;

class ForumDiscussion extends EntityRepository
{
    /**
     * @param int $limit
     * @param User|null $user
     * @return array
     * @throws Exception
     */
    public function findForDashboard(int $limit, User $user = null): array
    {
        $maxQuery = '
            SELECT p.discussionid AS disc_id, MAX(p.timestamp) AS max_date_time
            FROM somda_forum_posts p
            JOIN somda_forum_discussion d ON d.discussionid = p.discussionid
            WHERE p.timestamp > :minDate
            GROUP BY disc_id';
        if (is_null($user)) {
            $query = '
                SELECT `d`.`discussionid` AS `id`, `d`.`title` AS `title`, `a`.`uid` AS `author_id`,
                    `a`.`username` AS `author_username`, `d`.`locked` AS `locked`, `d`.`viewed` AS `viewed`,
                    TRUE AS `discussion_read`, `p_max`.`timestamp` AS `max_post_timestamp`, COUNT(*) AS `posts`
                FROM somda_forum_discussion d
                JOIN somda_users a ON a.uid = d.authorid
                JOIN somda_forum_posts p_max ON p_max.discussionid = d.discussionid
                JOIN somda_forum_posts p_count ON p_count.discussionid = d.discussionid
                INNER JOIN (' . $maxQuery . ') m ON m.disc_id = d.discussionid
                WHERE p_max.timestamp = m.max_date_time
                GROUP BY id, title, author_id, viewed, m.max_date_time, max_post_timestamp
                ORDER BY m.max_date_time DESC
                LIMIT 0, ' . $limit;
        } else {
            $query = '
                SELECT `d`.`discussionid` AS `id`, `d`.`title` AS `title`, `a`.`uid` AS `author_id`,
                    `a`.`username` AS `author_username`, `d`.`locked` AS `locked`, `d`.`viewed` AS `viewed`,
                    IF(`r`.`postid` IS NULL, FALSE, TRUE) AS `discussion_read`,
                    `p_max`.`timestamp` AS `max_post_timestamp`, COUNT(*) AS `posts`
                FROM somda_forum_discussion d
                JOIN somda_users a ON a.uid = d.authorid
                JOIN somda_forum_posts p_max ON p_max.discussionid = d.discussionid
                LEFT JOIN somda_forum_read_' . substr($user->getId(), -1) . ' r
                    ON r.uid = ' . $user->getId() . ' AND r.postid = p_max.postid
                JOIN somda_forum_posts p_count ON p_count.discussionid = d.discussionid
                INNER JOIN (' . $maxQuery . ') m ON m.disc_id = d.discussionid
                WHERE p_max.timestamp = m.max_date_time
                GROUP BY `id`, `title`, `author_id`, `viewed`, m.max_date_time, `discussion_read`, `max_post_timestamp`
                ORDER BY m.max_date_time DESC
                LIMIT 0, ' . $limit;
        }

        $connection = $this->getEntityManager()->getConnection();
        try {
            $statement = $connection->prepare($query);
            $statement->bindValue(
                'minDate',
                date(DateGenerics::DATE_FORMAT_DATABASE, mktime(0, 0, 0, date('m'), date('d') - 100, date('Y')))
            );
            $statement->execute();
            return $statement->fetchAll();
        } catch (DBALException $exception) {
            return [];
        }
    }

    /**
     * @param ForumForum $forum
     * @param User|null $user
     * @return array
     */
    public function findByForum(ForumForum $forum, User $user = null): array
    {
        $maxQuery = '
            SELECT p.discussionid AS disc_id, MAX(p.timestamp) AS max_date_time
            FROM somda_forum_posts p
            JOIN somda_forum_discussion d ON d.discussionid = p.discussionid
            WHERE d.forumid = :forumid
            GROUP BY disc_id';
        if (is_null($user)) {
            $query = '
                SELECT `d`.`discussionid` AS `id`, `d`.`title` AS `title`, `a`.`uid` AS `author_id`,
                    `a`.`username` AS `author_username`, `d`.`locked` AS `locked`, `d`.`viewed` AS `viewed`,
                    TRUE AS `discussion_read`, `p_max`.`timestamp` AS `max_post_timestamp`, COUNT(*) AS `posts`
                FROM somda_forum_discussion d
                JOIN somda_users a ON a.uid = d.authorid
                JOIN somda_forum_posts p_max ON p_max.discussionid = d.discussionid
                JOIN somda_forum_posts p_count ON p_count.discussionid = d.discussionid
                INNER JOIN (' . $maxQuery . ') m ON m.disc_id = d.discussionid
                WHERE d.forumid = :forumid AND p_max.timestamp = m.max_date_time
                GROUP BY id, title, author_id, viewed, m.max_date_time, max_post_timestamp
                ORDER BY m.max_date_time DESC';
        } else {
            $query = '
                SELECT `d`.`discussionid` AS `id`, `d`.`title` AS `title`, `a`.`uid` AS `author_id`,
                    `a`.`username` AS `author_username`, `d`.`locked` AS `locked`, `d`.`viewed` AS `viewed`,
                    IF(`r`.`postid` IS NULL, FALSE, TRUE) AS `discussion_read`,
                    `p_max`.`timestamp` AS `max_post_timestamp`, COUNT(*) AS `posts`
                FROM somda_forum_discussion d
                JOIN somda_users a ON a.uid = d.authorid
                JOIN somda_forum_posts p_max ON p_max.discussionid = d.discussionid
                LEFT JOIN somda_forum_read_' . substr($user->getId(), -1) . ' r
                    ON r.uid = ' . $user->getId() . ' AND r.postid = p_max.postid
                JOIN somda_forum_posts p_count ON p_count.discussionid = d.discussionid
                INNER JOIN (' . $maxQuery . ') m ON m.disc_id = d.discussionid
                WHERE d.forumid = :forumid AND p_max.timestamp = m.max_date_time
                GROUP BY `id`, `title`, `author_id`, `viewed`, m.max_date_time, `discussion_read`, `max_post_timestamp`
                ORDER BY m.max_date_time DESC';
        }
        $connection = $this->getEntityManager()->getConnection();
        try {
            $statement = $connection->prepare($query);
            $statement->bindValue('forumid', $forum->getId());
            $statement->execute();
            return $statement->fetchAll();
        } catch (DBALException $exception) {
            return [];
        }
    }

    /**
     * @param ForumDiscussionEntity $discussion
     * @return int
     */
    public function getNumberOfPosts(ForumDiscussionEntity $discussion): int
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(ForumPost::class, 'p')
            ->andWhere('p.discussion = :' . ForumPostForm::FIELD_DISCUSSION)
            ->setParameter(ForumPostForm::FIELD_DISCUSSION, $discussion)
            ->setMaxResults(1);
        try {
            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (Exception $exception) {
            return 0;
        }
    }

    /**
     * @param ForumDiscussionEntity $discussion
     * @param int $postId
     * @return int
     */
    public function getPostNumberInDiscussion(ForumDiscussionEntity $discussion, int $postId): int
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('p.id')
            ->from(ForumPost::class, 'p')
            ->andWhere('p.discussion = :' . ForumPostForm::FIELD_DISCUSSION)
            ->setParameter(ForumPostForm::FIELD_DISCUSSION, $discussion)
            ->addOrderBy('p.timestamp', 'ASC');
        $postIds = array_column($queryBuilder->getQuery()->getResult(), 'id');
        return array_search($postId, $postIds);
    }

    /**
     * @param ForumDiscussionEntity $discussion
     * @param User $user
     * @return int
     */
    public function getNumberOfReadPosts(ForumDiscussionEntity $discussion, User $user): int
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from('App\Entity\ForumRead' . substr($user->getId(), -1), 'r')
            ->join('r.post', 'p')
            ->andWhere('p.discussion = :' . ForumPostForm::FIELD_DISCUSSION)
            ->setParameter(ForumPostForm::FIELD_DISCUSSION, $discussion)
            ->andWhere('r.user = :user')
            ->setParameter('user', $user);
        try {
            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (Exception $exception) {
            return 0;
        }
    }

    /**
     * @param User $user
     * @param ForumPost[] $posts
     */
    public function markPostsAsRead(User $user, array $posts): void
    {
        $query = 'INSERT IGNORE INTO `somda_forum_read_'  . substr($user->getId(), -1) . '` (postid, uid) VALUES ';
        foreach ($posts as $post) {
            $query .= '(' . $post->getId() . ',' . $user->getId() . '),';
        }

        $connection = $this->getEntityManager()->getConnection();
        try {
            $statement = $connection->prepare(substr($query, 0, -1));
            $statement->execute();
        } catch (DBALException $exception) {
            return;
        }
    }
}
