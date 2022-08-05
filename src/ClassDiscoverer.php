<?php

namespace Spatie\LaravelAutoDiscoverer;

use Illuminate\Support\Str;
use ReflectionClass;
use Spatie\LaravelAutoDiscoverer\ValueObjects\DiscoverProfile;
use Spatie\LaravelAutoDiscoverer\ValueObjects\DiscoverProfileConfig;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Throwable;

class ClassDiscoverer
{
    public static function create(): self
    {
        return new self();
    }

    public function discover(DiscoverProfilesCollection $profiles): DiscoverProfilesCollection
    {
        $directories = $profiles->toCollection()
            ->flatMap(fn (DiscoverProfile $profile) => $profile->getDirectories())
            ->unique()
            ->values();

        if ($directories->isEmpty()) {
            return $profiles->each(fn (DiscoverProfile $profile) => $profile->markDiscovered());
        }

        $files = (new Finder())->files()->in($directories->all());

        $ignoredFiles = array_merge(
            config('auto-discoverer.ignored_files') ?? [],
            Composer::getAutoloadedFiles(base_path('composer.json'))
        );

        foreach ($files as $file) {
            if (in_array($file->getPathname(), $ignoredFiles)) {
                continue;
            }

            foreach ($profiles->toCollection() as $profile) {
                if (! $profile->isValidPathForProfile($file->getRealPath())) {
                    continue;
                }

                $class = $this->fullQualifiedClassNameFromFile($profile, $file);

                try {
                    $reflection = new ReflectionClass($class);
                } catch (Throwable $e) {
                    continue;
                }

                if (! $profile->config->conditions->satisfies($reflection)) {
                    continue;
                }

                $profile->addDiscovered($reflection);
            }
        }

        return $profiles->each(fn (DiscoverProfile $profile) => $profile->markDiscovered());
    }

    protected function fullQualifiedClassNameFromFile(
        DiscoverProfile $profile,
        SplFileInfo $file
    ): string {
        return Str::of($file->getRealPath())
            ->replaceFirst($profile->getBasePath(), '')
            ->replaceLast('.php', '')
            ->trim(DIRECTORY_SEPARATOR)
            ->ucfirst()
            ->replace(
                [DIRECTORY_SEPARATOR, 'App\\'],
                ['\\', app()->getNamespace()],
            )
            ->prepend(Str::finish($profile->getRootNamespace(), '\\'));
    }
}
