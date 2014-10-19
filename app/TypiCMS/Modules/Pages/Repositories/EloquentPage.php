<?php
namespace TypiCMS\Modules\Pages\Repositories;

use DB;
use Input;
use Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TypiCMS\Repositories\RepositoriesAbstract;

class EloquentPage extends RepositoriesAbstract implements PageInterface
{

    public function __construct(Model $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Get a page by its uri
     *
     * @param  string                      $uri
     * @return TypiCMS\Modules\Models\Page $model
     */
    public function getFirstByUri($uri)
    {
        $model = $this->make(['translations'])
            ->where('is_home', 0)
            ->whereHas('translations', function (Builder $query) use ($uri) {
                $query->where('uri', $uri);
                if (! Input::get('preview')) {
                    $query->where('status', 1);
                }
            })
            ->withOnlineGalleries()
            ->firstOrFail();
        return $model;
    }

    /**
     * Get submenu for a page
     *
     * @return Collection
     */
    public function getSubMenu($uri, $all = false)
    {
        $rootUriArray = explode('/', $uri);
        $uri = $rootUriArray[0];
        if (Config::get('app.locale_in_url')) {
            if (isset($rootUriArray[1])) {
                $uri .= '/' . $rootUriArray[1];
            }
        }

        $query = $this->model
            ->with('translations')
            ->select('*')
            ->addSelect('pages.id AS id')
            ->join('page_translations', 'pages.id', '=', 'page_translations.page_id')
            ->where('uri', '!=', $uri)
            ->where('uri', 'LIKE', $uri.'%');

        // All posts or only published
        if (! $all) {
            $query->where('status', 1);
        }
        $query->where('locale', Config::get('app.locale'));

        $query->order();

        return $query->get();
    }

    /**
     * Get Pages to build routes
     *
     * @return Collection
     */
    public function getForRoutes()
    {
        return DB::table('pages')
            ->select('pages.id', 'page_id', 'uri', 'locale')
            ->join('page_translations', 'pages.id', '=', 'page_translations.page_id')
            ->where('uri', '!=', '')
            ->where('is_home', '!=', 1)
            ->where('status', '=', 1)
            ->orderBy('locale')
            ->get();
    }
}
