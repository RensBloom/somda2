<?php

namespace App\Controller;

use App\Entity\ForumForum;
use App\Entity\ForumPost;
use App\Entity\ForumPostAlert;
use App\Entity\ForumPostAlertNote;
use App\Form\ForumPostAlert as ForumPostAlertForm;
use App\Form\ForumPostAlertNote as ForumPostAlertNoteForm;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ForumPostAlertController extends ForumBaseController
{
    /**
     * @param Request $request
     * @param int $id
     * @return Response|RedirectResponse
     * @throws Exception
     */
    public function alertAction(Request $request, int $id)
    {
        if (!$this->userIsLoggedIn()) {
            throw new AccessDeniedHttpException();
        }

        /**
         * @var ForumPost $post
         */
        $post = $this->doctrine->getRepository(ForumPost::class)->find($id);
        $form = $this->formFactory->create(ForumPostAlertForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $forumPostAlert = new ForumPostAlert();
            $forumPostAlert
                ->setPost($post)
                ->setSender($this->getUser())
                ->setDate(new DateTime())
                ->setTime(new DateTime())
                ->setComment($form->get('comment')->getData());
            $this->doctrine->getManager()->persist($forumPostAlert);
            $post->addAlert($forumPostAlert);
            $this->doctrine->getManager()->flush();

            // Send this alert to the forum-moderators
            foreach ($post->getDiscussion()->getForum()->getModerators() as $moderator) {
                $this->sendEmail(
                    $moderator,
                    '[Somda-Forum] Een gebruiker heeft een forumbericht gemeld!',
                    'forum-new-alert',
                    ['post' => $post, 'user' => $this->getUser(), 'comment' => $form->get('comment')->getData()]
                );
            }

            return $this->redirectToRoute('forum_discussion_post', [
                'id' => $post->getDiscussion()->getId(),
                'postId' => $post->getId(),
                'name' => urlencode($post->getDiscussion()->getTitle())
            ]);
        }

        return $this->render('forum/alert.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function alertsAction(Request $request, int $id)
    {
        /**
         * @var ForumPost $post
         */
        $post = $this->doctrine->getRepository(ForumPost::class)->find($id);
        if (!$this->userIsModerator($post->getDiscussion())) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->formFactory->create(ForumPostAlertNoteForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $forumPostAlertNote = new ForumPostAlertNote();
            $forumPostAlertNote
                ->setAlert($post->getAlerts()[0])
                ->setAuthor($this->getUser())
                ->setDate(new DateTime())
                ->setTime(new DateTime())
                ->setText($form->get('text')->getData())
                ->setSentToReporter($form->get('sentToReporter')->getData());

            $this->doctrine->getManager()->persist($forumPostAlertNote);
            $post->getAlerts()[0]->addNote($forumPostAlertNote);
            $this->doctrine->getManager()->flush();

            // Send this alert-note to the forum-moderators
            foreach ($post->getDiscussion()->getForum()->getModerators() as $moderator) {
                $this->sendEmail(
                    $moderator,
                    '[Somda-Forum] Notitie geplaatst bij gemeld forumbericht!',
                    'forum-new-alert-note',
                    ['post' => $post, 'note' => $forumPostAlertNote]
                );
            }

            if ($form->get('sentToReporter')->getData()) {
                // We need to inform the reporter(s)
                foreach ($post->getAlerts() as $alert) {
                    if (!$alert->isClosed()) {
                        $template = $post->getDiscussion()->getForum()->getType() === ForumForum::TYPE_MODERATORS_ONLY ?
                            'forum-alert-follow-up-deleted' : 'forum-alert-follow-up';
                        $this->sendEmail(
                            $alert->getSender(),
                            '[Somda] Reactie op jouw melding van een forumbericht',
                            $template,
                            ['user' => $alert->getSender(), 'post' => $post, 'note' => $forumPostAlertNote]
                        );
                    }
                }
            }

            return $this->redirectToRoute('forum_discussion_post_alerts', ['id' => $post->getId()]);
        }

        return $this->render('forum/alerts.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function alertsCloseAction(int $id): RedirectResponse
    {
        /**
         * @var ForumPost $post
         */
        $post = $this->doctrine->getRepository(ForumPost::class)->find($id);
        if (!$this->userIsModerator($post->getDiscussion())) {
            throw new AccessDeniedHttpException();
        }

        foreach ($post->getAlerts() as $alert) {
            $alert->setClosed(true);
        }
        $this->doctrine->getManager()->flush();

        return $this->redirectToRoute('forum_discussion_post_alerts', ['id' => $post->getId()]);
    }
}