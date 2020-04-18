<?php

namespace App\Controller;

use App\Entity\ForumDiscussion;
use App\Entity\ForumPost;
use App\Form\ForumDiscussionCombine;
use App\Form\ForumDiscussionMove;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ForumModerateController extends ForumBaseController
{
    public const ACTION_CLOSE = 'close';
    public const ACTION_OPEN = 'open';
    public const ACTION_MOVE = 'move';

    /**
     * @param Request $request
     * @param int $id
     * @param string $action
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request, int $id, string $action)
    {
        $discussion = $this->getDiscussion($id);
        if ($action === self::ACTION_CLOSE && !$discussion->isLocked()) {
            $discussion->setLocked(true);
            $this->doctrine->getManager()->flush();
        } elseif ($action === self::ACTION_OPEN && $discussion->isLocked()) {
            $discussion->setLocked(false);
            $this->doctrine->getManager()->flush();
        } elseif ($action === self::ACTION_MOVE) {
            $form = $this->formFactory->create(ForumDiscussionMove::class, $discussion);
            $form->handleRequest($request);
            if (!$form->isSubmitted() || !$form->isValid()) {
                return $this->render('forum/discussionMove.html.twig', [
                    'discussion' => $discussion,
                    'form' => $form->createView()
                ]);
            }
            $this->doctrine->getManager()->flush();
        }

        return $this->redirectToRoute(
            'forum_discussion',
            ['id' => $discussion->getId(), 'name' => urlencode($discussion->getTitle())]
        );
    }

    /**
     * @param Request $request
     * @param int $id1
     * @param int $id2
     * @return RedirectResponse|Response
     */
    public function combineAction(Request $request, int $id1, int $id2)
    {
        $discussion1 = $this->getDiscussion($id1);
        $discussion2 = $this->getDiscussion($id2);

        $newDiscussion = new ForumDiscussion();
        $newDiscussion->setForum($discussion1->getForum());

        $form = $this->formFactory->create(ForumDiscussionCombine::class, $newDiscussion);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldestPost = $this->movePostsAndGetOldest($discussion1, $discussion2, $newDiscussion);

            $newDiscussion
                ->setAuthor($oldestPost->getAuthor())
                ->setTitle($form->get('title')->getData())
                ->setViewed($discussion1->getViewed() + $discussion2->getViewed());
            $this->doctrine->getManager()->persist($newDiscussion);
            $this->doctrine->getManager()->remove($discussion1);
            $this->doctrine->getManager()->remove($discussion2);
            $this->doctrine->getManager()->flush();

            return $this->redirectToRoute(
                'forum_discussion',
                ['id' => $newDiscussion->getId(), 'name' => urlencode($newDiscussion->getTitle())]
            );
        }

        return $this->render('forum/discussionCombine.html.twig', [
            'discussion1' => $discussion1,
            'discussion2' => $discussion2,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param ForumDiscussion $discussion1
     * @param ForumDiscussion $discussion2
     * @param ForumDiscussion $newDiscussion
     * @return ForumPost
     */
    private function movePostsAndGetOldest(
        ForumDiscussion $discussion1,
        ForumDiscussion $discussion2,
        ForumDiscussion $newDiscussion
    ): ForumPost {
        /**
         * @var ForumPost $oldestPost
         */
        $oldestPost = null;
        foreach ($discussion1->getPosts() as $post) {
            if (is_null($oldestPost) || $post->getTimestamp() < $oldestPost->getTimestamp()) {
                $oldestPost = $post;
            }
            $post->setDiscussion($newDiscussion);
        }
        foreach ($discussion2->getPosts() as $post) {
            if (is_null($oldestPost) || $post->getTimestamp() < $oldestPost->getTimestamp()) {
                $oldestPost = $post;
            }
            $post->setDiscussion($newDiscussion);
        }

        // Move the favorites
        foreach ($discussion1->getFavorites() as $favorite) {
            $favorite->setDiscussion($newDiscussion);
        }
        foreach ($discussion2->getFavorites() as $favorite) {
            $favorite->setDiscussion($newDiscussion);
        }

        // Move the wikis
        foreach ($discussion1->getWikis() as $wiki) {
            $wiki->setDiscussion($newDiscussion);
        }
        foreach ($discussion2->getWikis() as $wiki) {
            $wiki->setDiscussion($newDiscussion);
        }

        return $oldestPost;
    }

    /**
     * @param int $id
     * @param string $postIds
     * @return RedirectResponse
     */
    public function splitAction(int $id, string $postIds): RedirectResponse
    {
        $discussion = $this->getDiscussion($id);

        $postIds = array_filter(explode(',', $postIds));
        $firstPost = $this->doctrine->getRepository(ForumPost::class)->find($postIds[0]);

        // Create the new discussion
        $newDiscussion = new ForumDiscussion();
        $newDiscussion
            ->setForum($discussion->getForum())
            ->setTitle('Verwijderd uit ' . $discussion->getTitle())
            ->setAuthor($firstPost->getAuthor());
        $this->doctrine->getManager()->persist($newDiscussion);

        foreach ($postIds as $postId) {
            $post = $this->doctrine->getRepository(ForumPost::class)->find($postId);
            $post->setDiscussion($newDiscussion);
        }

        $this->doctrine->getManager()->flush();

        return $this->redirectToRoute(
            'forum_discussion',
            ['id' => $newDiscussion->getId(), 'name' => urlencode($newDiscussion->getTitle())]
        );
    }

    /**
     * @param int $id
     * @return ForumDiscussion
     */
    private function getDiscussion(int $id): ForumDiscussion
    {
        $discussion = $this->doctrine->getRepository(ForumDiscussion::class)->find($id);
        if (is_null($discussion) || !$this->userIsModerator($discussion)) {
            throw new AccessDeniedHttpException();
        }
        return $discussion;
    }
}