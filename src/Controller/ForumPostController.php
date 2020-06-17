<?php

namespace App\Controller;

use App\Entity\ForumDiscussion;
use App\Entity\ForumFavorite;
use App\Entity\ForumPost;
use App\Entity\ForumPostLog;
use App\Entity\ForumPostText;
use App\Form\BaseForm;
use App\Form\ForumPost as ForumPostForm;
use App\Generics\RouteGenerics;
use App\Helpers\EmailHelper;
use App\Helpers\FormHelper;
use App\Helpers\ForumAuthorizationHelper;
use App\Helpers\ForumHelper;
use App\Helpers\TemplateHelper;
use App\Helpers\UserHelper;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ForumPostController
{
    /**
     * @var UserHelper
     */
    private UserHelper $userHelper;

    /**
     * @var FormHelper
     */
    private FormHelper $formHelper;

    /**
     * @var ForumAuthorizationHelper
     */
    private ForumAuthorizationHelper $forumAuthHelper;

    /**
     * @var ForumHelper
     */
    private ForumHelper $forumHelper;

    /**
     * @var TemplateHelper
     */
    private TemplateHelper $templateHelper;

    /**
     * @var EmailHelper
     */
    private EmailHelper $emailHelper;

    /**
     * @param UserHelper $userHelper
     * @param FormHelper $formHelper
     * @param ForumAuthorizationHelper $forumAuthHelper
     * @param ForumHelper $forumHelper
     * @param TemplateHelper $templateHelper
     * @param EmailHelper $emailHelper
     */
    public function __construct(
        UserHelper $userHelper,
        FormHelper $formHelper,
        ForumAuthorizationHelper $forumAuthHelper,
        ForumHelper $forumHelper,
        TemplateHelper $templateHelper,
        EmailHelper $emailHelper
    ) {
        $this->userHelper = $userHelper;
        $this->formHelper = $formHelper;
        $this->forumAuthHelper = $forumAuthHelper;
        $this->forumHelper = $forumHelper;
        $this->templateHelper = $templateHelper;
        $this->emailHelper = $emailHelper;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param int $id
     * @param bool $quote
     * @return Response|RedirectResponse
     * @throws Exception
     */
    public function replyAction(Request $request, int $id, bool $quote = false)
    {
        /**
         * @var ForumPost $quotedPost
         */
        $quotedPost = $this->formHelper->getDoctrine()->getRepository(ForumPost::class)->find($id);
        if (!$this->forumAuthHelper->mayPost($quotedPost->discussion->forum, $this->userHelper->getUser())
            || $quotedPost->discussion->locked
        ) {
            throw new AccessDeniedHttpException();
        }

        $userIsModerator = $this->forumAuthHelper->userIsModerator(
            $quotedPost->discussion,
            $this->userHelper->getUser()
        );

        $form = $this->formHelper
            ->getFactory()
            ->create(ForumPostForm::class, null, [ForumPostForm::OPTION_QUOTED_POST => $quote ? $quotedPost : null]);
        if ($userIsModerator) {
            $form->add('postAsModerator', CheckboxType::class, [
                BaseForm::KEY_LABEL => 'Plaatsen als moderator',
                BaseForm::KEY_MAPPED => false,
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userIsModerator && $form->get('postAsModerator')->getData() ?
                $this->userHelper->getModeratorUser() : $this->userHelper->getUser();
            $this->formHelper->addPost($form, $quotedPost->discussion, $user);
            $this->handleFavoritesForAddedPost($quotedPost->discussion);

            return $this->formHelper->finishFormHandling('', RouteGenerics::ROUTE_FORUM_DISCUSSION, [
                'id' => $quotedPost->discussion->getId(),
                'name' => urlencode($quotedPost->discussion->title)
            ]);
        }

        $lastPosts = $this->formHelper->getDoctrine()->getRepository(ForumPost::class)->findBy(
            [ForumPostForm::FIELD_DISCUSSION => $quotedPost->discussion],
            [ForumPostForm::FIELD_TIMESTAMP => 'DESC'],
            10
        );

        return $this->templateHelper->render('forum/reply.html.twig', [
            TemplateHelper::PARAMETER_PAGE_TITLE => 'Forum - ' . $quotedPost->discussion->title,
            TemplateHelper::PARAMETER_FORM => $form->createView(),
            'post' => $quotedPost,
            'lastPosts' => $lastPosts,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function replyExampleAction(Request $request): JsonResponse
    {
        $text = (string)$request->request->get('text');
        $postText = new ForumPostText();
        $postText->text = str_replace("\n", '', $text);
        $post = new ForumPost();
        $post->text = $postText;
        return new JsonResponse(['data' => $this->forumHelper->getDisplayForumPost($post)]);
    }

    /**
     * @param ForumDiscussion $discussion
     */
    private function handleFavoritesForAddedPost(ForumDiscussion $discussion): void
    {
        foreach ($discussion->getFavorites() as $favorite) {
            if ($favorite->alerting === ForumFavorite::ALERTING_ON) {
                $this->emailHelper->sendEmail(
                    $favorite->user,
                    'Somda - Nieuwe forumreactie op "' . $discussion->title . '"',
                    'forum-new-reply',
                    [ForumPostForm::FIELD_DISCUSSION => $discussion]
                );
                $favorite->alerting = ForumFavorite::ALERTING_SENT;
            }
        }
    }

    /**
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param int $id
     * @return Response|RedirectResponse
     * @throws Exception
     */
    public function editAction(Request $request, int $id)
    {
        /**
         * @var ForumPost $post
         */
        $post = $this->formHelper->getDoctrine()->getRepository(ForumPost::class)->find($id);
        $userIsModerator = $this->forumAuthHelper->userIsModerator(
            $post->discussion,
            $this->userHelper->getUser()
        );
        if (!$this->forumAuthHelper->mayPost($post->discussion->forum, $this->userHelper->getUser())
            || $post->discussion->locked
            || ($post->author !== $this->userHelper->getUser() && !$userIsModerator)
        ) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->formHelper
            ->getFactory()
            ->create(ForumPostForm::class, null, [ForumPostForm::OPTION_EDITED_POST => $post]);
        if ($userIsModerator) {
            $form->add(
                ForumPostForm::FIELD_EDIT_AS_MODERATOR,
                CheckboxType::class,
                [BaseForm::KEY_LABEL => 'Bewerken als moderator']
            );
            $postNrInDiscussion = $this->formHelper->getDoctrine()
                ->getRepository('App:ForumDiscussion')
                ->getPostNumberInDiscussion($post->discussion, $post->getId());
            if ($postNrInDiscussion === 0) {
                $form->add(ForumPostForm::FIELD_TITLE, TextType::class, [
                    BaseForm::KEY_DATA => $post->discussion->title,
                    BaseForm::KEY_LABEL => 'Onderwerp van de discussie',
                    BaseForm::KEY_REQUIRED => true,
                ]);
            }
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->editPost($form, $post);

            return $this->formHelper->finishFormHandling('', RouteGenerics::ROUTE_FORUM_DISCUSSION, [
                'id' => $post->discussion->getId(),
                'name' => urlencode($post->discussion->title)
            ]);
        }

        return $this->templateHelper->render('forum/edit.html.twig', [
            TemplateHelper::PARAMETER_PAGE_TITLE => 'Forum - ' . $post->discussion->title,
            TemplateHelper::PARAMETER_FORM => $form->createView(),
            'post' => $post,
        ]);
    }

    /**
     * @param FormInterface $form
     * @param ForumPost $post
     * @throws Exception
     */
    private function editPost(FormInterface $form, ForumPost $post): void
    {
        if ($form->has(ForumPostForm::FIELD_EDIT_AS_MODERATOR)
            && $form->get(ForumPostForm::FIELD_EDIT_AS_MODERATOR)->getData()
        ) {
            $editor = $this->userHelper->getModeratorUser();
        } else {
            $editor = $this->userHelper->getUser();
        }

        if ($form->has(ForumPostForm::FIELD_TITLE)) {
            $post->discussion->title = $form->get(ForumPostForm::FIELD_TITLE)->getData();
        }

        $post->editTimestamp = new DateTime();
        $post->editor = $editor;
        $post->editReason = $form->get('editReason')->getData();
        $post->signatureOn = $form->get('signatureOn')->getData();
        $post->text->text = $form->get('text')->getData();

        $postLog = new ForumPostLog();
        $postLog->action = ForumPostLog::ACTION_POST_EDIT;
        $this->formHelper->getDoctrine()->getManager()->persist($postLog);

        $post->addLog($postLog);
    }
}
