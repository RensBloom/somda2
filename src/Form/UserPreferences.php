<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\User as UserEntity;
use App\Entity\UserPreference;
use App\Helpers\UserHelper;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPreferences extends AbstractType
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var UserPreference[] $allSettings
         * @var UserHelper $userHelper
         */
        $allSettings = $options['allSettings'];
        $userHelper = $options['userHelper'];

        foreach ($allSettings as $setting) {
            if ($setting->getOrder() > 0) {
                $value = $userHelper->getPreferenceByKey($setting->getKey(), $builder->getData())->getValue();
                $typePart = explode('|', $setting->getType());
                switch($typePart[0]) {
                    case 'number':
                        $builder->add($setting->getKey(), ChoiceType::class, [
                            'choices' => array_combine(range(1, (int)$typePart[1]), range(1, (int)$typePart[1])),
                            'data' => (int)$value,
                            'label' => $setting->getDescription(),
                            'mapped' => false,
                            'required' => true,
                        ]);
                        break;
                    case 'text':
                        $builder->add($setting->getKey(), TextType::class, [
                            'data' => $value,
                            'label' => $setting->getDescription(),
                            'mapped' => false,
                            'required' => true,
                        ]);
                        break;
                    case 'boolean':
                        $builder->add($setting->getKey(), CheckboxType::class, [
                            'data' => (int)$value === 1,
                            'label' => $setting->getDescription(),
                            'mapped' => false,
                            'required' => true,
                        ]);
                        break;
                    case 'table':
                        if ($typePart[1] !== 'location') {
                            throw new Exception(
                                'Unknown setting table ' . $typePart[1] . ' for key ' . $setting->getKey()
                            );
                        }

                        $builder->add($setting->getKey(), EntityType::class, [
                            'choice_label' => function (Location $location) {
                                return $location->getName() . ' - ' . $location->getDescription();
                            },
                            'choice_value' => $typePart[2],
                            'class' => Location::class,
                            'data' => $this->doctrine->getRepository(Location::class)->findOneBy(['name' => $value]),
                            'label' => $setting->getDescription(),
                            'mapped' => false,
                            'required' => true,
                        ]);
                        break;
                    default:
                        throw new Exception('Unknown setting type ' . $typePart[0] . ' for key ' . $setting->getKey());
                }
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserEntity::class,
            'allSettings' => [],
            'userHelper' => null,
        ]);
    }
}