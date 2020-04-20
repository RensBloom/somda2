<?php

namespace App\Helpers;

use App\Entity\User;
use App\Entity\UserPreference;
use App\Entity\UserPreferenceValue;
use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Process\Exception\ExceptionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class UserHelper implements RuntimeExtensionInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param ManagerRegistry $doctrine
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     */
    public function __construct(ManagerRegistry $doctrine, TranslatorInterface $translator, RouterInterface $router)
    {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * @param int $id
     * @param string $username
     * @return string
     */
    public function getDisplayUser(int $id, string $username): string
    {
        if ($id < 0) {
            return $username;
        }
        $displayUser = '<a href="' . $this->router->generate('profile_view', ['id' => $id]) . '"';
        $displayUser .= ' title="' . sprintf($this->translator->trans('profile.view.title'), $username) . '">';
        $displayUser .= $username . '</a>';
        return $displayUser;
    }

    /**
     * @param string $key
     * @param User|null $user
     * @return UserPreferenceValue
     * @throws Exception
     */
    public function getPreferenceByKey(string $key, User $user = null): UserPreferenceValue
    {
        if (!is_null($user)) {
            foreach ($user->getPreferences() as $preference) {
                if ($preference->preference->key === $key) {
                    return $preference;
                }
            }
        }

        // Get and save the default value for this key
        /**
         * @var UserPreference $userPreference
         */
        $userPreference = $this->doctrine->getRepository(UserPreference::class)->findOneBy(['key' => $key]);
        if (is_null($userPreference)) {
            throw new Exception('Preference with key "' . $key . '" does not exist');
        }
        $userPreferenceValue = new UserPreferenceValue();
        $userPreferenceValue->preference = $userPreference;
        $userPreferenceValue->value = $userPreference->defaultValue;
        if (!is_null($user)) {
            $userPreferenceValue->user = $user;
            $this->doctrine->getManager()->persist($userPreferenceValue);
            $this->doctrine->getManager()->flush();
        }

        return $userPreferenceValue;
    }
}
