<?php

namespace App\ViewFunctions;

use App\Support\Str;
use DI\Container;
use Tightenco\Collect\Support\Collection;

class Breadcrumbs extends ViewFunction
{
    /** @var string The function name */
    protected $name = 'breadcrumbs';

    /** @var Container The application container */
    protected $container;

    /** @var string The directory separator */
    protected $directorySeparator;

    /**
     * Create a new Breadcrumbs object.
     *
     * @param \DI\Container $container
     */
    public function __construct(
        Container $container,
        string $directorySeparator = DIRECTORY_SEPARATOR
    ) {
        $this->container = $container;
        $this->directorySeparator = $directorySeparator;
    }

    /**
     * Build an array of breadcrumbs for a given path.
     *
     * @param string $path
     *
     * @return array
     */
    public function __invoke(string $path)
    {
        $breadcrumbs = Str::explode($path, $this->directorySeparator)->diff(
            explode($this->directorySeparator, $this->container->get('base_path'))
        )->filter();

        return $breadcrumbs->filter(function (string $crumb) {
            return $crumb !== '.';
        })->reduce(function (Collection $carry, string $crumb) {
            return $carry->put($crumb, ltrim(
                $carry->last() . $this->directorySeparator . urlencode($crumb), $this->directorySeparator
            ));
        }, new Collection)->map(function (string $path): string {
            return sprintf('?dir=%s', $path);
        });
    }
}
