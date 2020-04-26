<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserInfo;
use App\Form\UserMail;
use App\Helpers\EmailHelper;
use App\Helpers\FlashHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\TemplateHelper;
use App\Helpers\UserHelper;
use Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProfileController
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * @var TemplateHelper
     */
    private $templateHelper;

    /**
     * @var FlashHelper
     */
    private $flashHelper;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RedirectHelper
     */
    private $redirectHelper;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @param ManagerRegistry $doctrine
     * @param UserHelper $userHelper
     * @param TemplateHelper $templateHelper
     * @param FlashHelper $flashHelper
     * @param FormFactoryInterface $formFactory
     * @param RedirectHelper $redirectHelper
     * @param EmailHelper $emailHelper
     */
    public function __construct(
        ManagerRegistry $doctrine,
        UserHelper $userHelper,
        TemplateHelper $templateHelper,
        FlashHelper $flashHelper,
        FormFactoryInterface $formFactory,
        RedirectHelper $redirectHelper,
        EmailHelper $emailHelper
    ) {
        $this->doctrine = $doctrine;
        $this->userHelper = $userHelper;
        $this->templateHelper = $templateHelper;
        $this->flashHelper = $flashHelper;
        $this->formFactory = $formFactory;
        $this->redirectHelper = $redirectHelper;
        $this->emailHelper = $emailHelper;
    }

    /**
     * @param Request $request
     * @param int|null $id
     * @return Response|RedirectResponse
     * @throws Exception
     */
    public function indexAction(Request $request, int $id = null)
    {
        if (is_null($id)) {
            if (!$this->userHelper->userIsLoggedIn()) {
                throw new AccessDeniedHttpException();
            }
            $user = $this->userHelper->getUser();
        } else {
            $user = $this->doctrine->getRepository(User::class)->find($id);
        }

        $form = null;
        if ($user === $this->userHelper->getUser()) {
            $form = $this->formFactory->create(UserInfo::class, $this->userHelper->getUser()->info);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->doctrine->getManager()->flush();

                $this->flashHelper->add(FlashHelper::FLASH_TYPE_INFORMATION, 'Je profiel is aangepast');

                return $this->redirectHelper->redirectToRoute('profile');
            }
        }

        return $this->templateHelper->render('somda/profile.html.twig', [
            TemplateHelper::PARAMETER_PAGE_TITLE => 'Profiel van ' . $user->username,
            'user' => $user,
            'form' => $form ? $form->createView() : null
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response|RedirectResponse
     */
    public function mailAction(Request $request, int $id)
    {
        /**
         * @var User $user
         * @var User $moderator
         */
        $user = $this->doctrine->getRepository(User::class)->find($id);
        if (is_null($user)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->formFactory->create(
            UserMail::class,
            null,
            ['isModerator' => $this->userHelper->getUser()->hasRole('ROLE_ADMIN')]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('senderOption')->getData() === 'moderator') {
                if (!$this->userHelper->getUser()->hasRole('ROLE_ADMIN')) {
                    throw new AccessDeniedHttpException();
                }
                $from = ['mods@somda.nl', 'Somda moderator'];
                $template = 'user-mail-moderator';

                // Send a copy of the email to the moderator user
                $this->emailHelper->sendEmail(
                    $this->userHelper->getModeratorUser(),
                    'Somda - Door moderator verstuurde e-mail',
                    'user-mail-moderator-copy',
                    ['user' => $user, 'text' => $form->get('text')->getData()]
                );
            } elseif ($form->get('senderOption')->getData() === 'direct') {
                $from = [$this->userHelper->getUser()->email, $this->userHelper->getUser()->username];
                $template = 'user-mail-direct';
            } else {
                $from = ['noreply@somda.nl', $this->userHelper->getUser()->username];
                $template = 'user-mail-anonymous';
            }
            $this->emailHelper->sendEmail(
                $user,
                $form->get('subject')->getData(),
                $template,
                ['sender' => $this->userHelper->getUser(), 'from' => $from, 'text' => $form->get('text')->getData()]);

            $this->flashHelper->add(FlashHelper::FLASH_TYPE_INFORMATION, 'Je bericht is verzonden');

            return $this->redirectHelper->redirectToRoute('profile_view', ['id' => $user->getId()]);
        }

        return $this->templateHelper->render('somda/mail.html.twig', [
            TemplateHelper::PARAMETER_PAGE_TITLE => 'Mail sturen naar ' . $user->username,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
