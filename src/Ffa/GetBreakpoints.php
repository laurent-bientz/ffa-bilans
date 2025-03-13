<?php

namespace App\Ffa;

use App\Entity\Performance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;

class GetBreakpoints
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string                 $projectDir,
        private readonly string                 $projectEnv,
        private readonly int                    $cacheTtl,
    ) {}

    public function __invoke(array $filters): array
    {
        // Cache
        $cacheSuffix = implode('-', array_filter(array_map(fn ($key, $value) => $value ? $key . '-' . $value : null, array_keys($filters), array_values($filters)), fn ($item) => $item !== null));
        if (!is_dir($cacheDir = ($this->projectDir . '/var/cache/' . $this->projectEnv . '/doctrine'))) {
            mkdir($cacheDir, 0777);
        }
        $cache = new PhpArrayAdapter($cachePath = ($cacheDir . DIRECTORY_SEPARATOR . 'performance-breakpoints_' . $cacheSuffix . '.php'), new FilesystemAdapter());
        if ((file_exists($cachePath) && $this->cacheTtl < time() - filemtime($cachePath)) ||
            null === $cache->getItem('data')->get()) {
            $cache->warmUp([
                'data' => $this->em->getRepository(Performance::class)->getBreakpoints($filters, !empty($filters['group']) ? $filters['group'] : null),
            ]);
            @chmod($cachePath, 0777);
        }
        // /Cache

        return $cache->getItem('data')->get() ?? [];
    }
}