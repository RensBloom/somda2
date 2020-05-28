<?php

namespace App\Controller;

use App\Entity\ForumDiscussion;
use App\Entity\ForumFavorite;
use App\Helpers\TemplateHelper;
use App\Helpers\UserHelper;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

class ForumPostFavoriteController
{
    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * @var UserHelper
     */
    private UserHelper $userHelper;

    /**
     * @var TemplateHelper
     */
    private TemplateHelper $templateHelper;

    /**
     * @param ManagerRegistry $doctrine
     * @param UserHelper $userHelper
     * @param TemplateHelper $templateHelper
     */
    public function __construct(ManagerRegistry $doctrine, UserHelper $userHelper, TemplateHelper $templateHelper)
    {
        $this->doctrine = $doctrine;
        $this->userHelper = $userHelper;
        $this->templateHelper = $templateHelper;
    }

    public function indexAction()
    {
        return $this->templateHelper->render('forum/favorites.html.twig', [
            TemplateHelper::PARAMETER_PAGE_TITLE => 'Forum favorieten',
            'favorites' => $this->doctrine->getRepository(ForumDiscussion::class)->findByFavorites(
                $this->userHelper->getUser()
            ),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @param int $id
     * @param int $alerting
     * @return JsonResponse
     * @throws Exception
     */
    public function toggleAction(int $id, int $alerting): JsonResponse
    {
        if (is_null($favorite = $this->getFavorite($id))) {
            return new JsonResponse();
        }

        $favorite->alerting = $alerting;
        $this->doctrine->getManager()->flush();

        return new JsonResponse();
    }

    /**
     * @IsGranted("ROLE_USER")
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function addAction(int $id): JsonResponse
    {
        $discussion = $this->doctrine->getRepository(ForumDiscussion::class)->find($id);
        if (is_null($discussion)) {
            return new JsonResponse();
        }

        $favorite = new ForumFavorite();
        $favorite->discussion = $discussion;
        $favorite->user = $this->userHelper->getUser();

        $this->doctrine->getManager()->persist($favorite);
        $this->doctrine->getManager()->flush();

        return new JsonResponse();
    }

    /**
     * @IsGranted("ROLE_USER")
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function removeAction(int $id): JsonResponse
    {
        if (is_null($favorite = $this->getFavorite($id))) {
            return new JsonResponse();
        }

        $this->doctrine->getManager()->remove($favorite);
        $this->doctrine->getManager()->flush();

        return new JsonResponse();
    }

    /**
     * @param int $id
     * @return ForumFavorite|null
     */
    private function getFavorite(int $id): ?ForumFavorite
    {
        $discussion = $this->doctrine->getRepository(ForumDiscussion::class)->find($id);
        if (is_null($discussion)) {
            return null;
        }

        /**
         * @var ForumFavorite $favorite
         */
        $favorite = $this->doctrine->getRepository(ForumFavorite::class)->findOneBy(
            ['discussion' => $discussion, 'user' => $this->userHelper->getUser()]
        );
        if (is_null($favorite)) {
            return null;
        }

        return $favorite;
    }
}