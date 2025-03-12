<?php

namespace App\Form\Filter;

use App\Entity\Performance;
use App\Enum\Gender;
use App\Enum\Trial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PerformanceFilterType extends AbstractType
{
    private int $cacheLifetime = 5 * 60; # seconds

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string                 $projectDir,
        private readonly string                 $projectEnv,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Cache
        if (!is_dir($cacheDir = ($this->projectDir . '/var/cache/' . $this->projectEnv . '/form'))) {
            mkdir($cacheDir, 0777);
        }
        $cache = new PhpArrayAdapter($cachePath = ($cacheDir . DIRECTORY_SEPARATOR . 'performance-filters.php'), new FilesystemAdapter());
        if ((file_exists($cachePath) && $this->cacheLifetime < time() - filemtime($cachePath)) ||
            null === $cache->getItem('data')->get()) {
            $cache->warmUp([
                'data' => [
                    'years' => $this->em->getRepository(Performance::class)->getDistinctYears(),
                    'categories' => $this->em->getRepository(Performance::class)->getDistinctCategories(),
                    'locations' => $this->em->getRepository(Performance::class)->getDistinctLocations(),
                ],
            ]);
            @chmod($cachePath, 0777);
        }
        $years = $cache->getItem('data')->get()['years'] ?? [];
        $categories = $cache->getItem('data')->get()['categories'] ?? [];
        $locations = $cache->getItem('data')->get()['locations'] ?? [];
        // /Cache

        $builder
            ->add('trial', EnumType::class, [
                'label' => 'Épreuve',
                'class' => Trial::class,
                'data' => $options['trial'],
                'expanded' => false,
                'multiple' => false,
                'choice_label' => Trial::getChoiceLabel(),
            ])
            ->add('year', ChoiceType::class, [
                'label' => 'Saison',
                'placeholder' => 'Toutes',
                'choices' => array_combine($years, $years),
            ])
            ->add('gender', EnumType::class, [
                'label' => 'Sexe',
                'placeholder' => 'Tous',
                'class' => Gender::class,
                'expanded' => false,
                'multiple' => false,
                'choice_label' => Gender::getChoiceLabel(),
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'placeholder' => 'Toutes',
                'choices' => array_combine($categories, $categories),
            ])
            ->add('location', ChoiceType::class, [
                'label' => 'Lieu',
                'placeholder' => 'Tous',
                'choices' => array_combine($locations, $locations),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
            'trial' => null,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'f';
    }
}
