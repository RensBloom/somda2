<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Helpers\UserHelper;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractFOSRestController
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
     * @param ManagerRegistry $doctrine
     * @param UserHelper $userHelper
     */
    public function __construct(ManagerRegistry $doctrine, UserHelper $userHelper)
    {
        $this->doctrine = $doctrine;
        $this->userHelper = $userHelper;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request): Response
    {
        // If we reach this point, the user was successfully logged in, so we look the user up and return it
        $userInformation = json_decode($request->getContent(), true);
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => $userInformation['username']]);

        return $this->handleView($this->view(['data' => $user], 200));
    }

    /**
     * @IsGranted("ROLE_API_USER")
     * @param int $id
     * @param string $token
     * @return Response
     */
    public function verifyAction(int $id, string $token): Response
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(
            ['id' => $id, 'active' => true, 'apiToken' => $token]
        );
        if (!is_null($user)) {
            if ($user->apiTokenExpiryTimestamp > new DateTime()) {
                $user->apiTokenExpiryTimestamp = new DateTime(User::API_TOKEN_VALIDITY);
                $this->doctrine->getManager()->flush();
            } else {
                $user = null;
            }
        }

        return $this->handleView($this->view(['data' => $user], 200));
    }
}
