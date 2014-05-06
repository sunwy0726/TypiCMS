<?php
namespace TypiCMS\Modules\Files\Controllers\Admin;

use View;
use Input;
use Request;
use Redirect;
use Response;
use Paginator;
use Notification;

use TypiCMS\Modules\Files\Repositories\FileInterface;
use TypiCMS\Modules\Files\Services\Form\FileForm;

// Base controller
use TypiCMS\Controllers\BaseController;

class FilesController extends BaseController
{

    public function __construct(FileInterface $file, FileForm $fileform)
    {
        parent::__construct($file, $fileform);
        $this->title['parent'] = trans_choice('files::global.files', 2);
    }

    /**
     * List models
     * GET /admin/model
     */
    public function index()
    {
        $page = Input::get('page');
        $type = Input::get('type');
        $filepicker = Input::get('filepicker');

        $itemsPerPage = 10;
        $data = $this->repository->byPageFrom($page, $itemsPerPage, null, array('translations'), true, $type);

        $models = Paginator::make($data->items, $data->totalItems, $itemsPerPage);

        if ($filepicker) {
            $this->layout->content = View::make('files.admin.filepicker')
                ->withModels($models);
        } else {
            $this->layout->content = View::make('files.admin.index')
                ->withModels($models);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $model = $this->repository->getModel();
        $this->title['child'] = trans('files::global.New');
        $this->layout->content = View::make('files.admin.create')
            ->withModel($model);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int      $id
     * @return Response
     */
    public function edit($model)
    {
        $this->title['child'] = trans('files::global.Edit');
        $this->layout->content = View::make('files.admin.edit')
            ->withModel($model);
    }

    /**
     * Show resource.
     *
     * @param  int      $id
     * @return Response
     */
    public function show($model)
    {
        return Redirect::route('admin.files.edit', array($model->id));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

        if ($model = $this->form->save(Input::all())) {

            if (Request::ajax()) {
                echo json_encode(array('id' => $model->id));
                exit();
            }

            if (Input::get('exit')) {
                return Redirect::route('admin.files.index');
            }
            return Redirect::route('admin.files.edit', array($model->id));

        }

        if (Request::ajax()) {
            return Response::json('error', 400);
        }

        return Redirect::route('admin.files.create')
            ->withInput()
            ->withErrors($this->form->errors());

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int      $id
     * @return Response
     */
    public function update($model)
    {

        Request::ajax() and exit($this->repository->update(Input::all()));

        if ($this->form->update(Input::all())) {
            return (Input::get('exit')) ? Redirect::route('admin.files.index') : Redirect::route('admin.files.edit', array($model->id)) ;
        }

        return Redirect::route('admin.files.edit', array($model->id))
            ->withInput()
            ->withErrors($this->form->errors());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int      $id
     * @return Response
     */
    public function sort()
    {
        $sort = $this->repository->sort(Input::all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int      $id
     * @return Response
     */
    public function destroy($model)
    {
        if ($this->repository->delete($model)) {
            if (! Request::ajax()) {
                Notification::success('File '.$model->filename.' deleted.');

                return Redirect::back();
            }
        } else {
            Notification::error('Error deleting file '.$model->filename.'.');
        }
    }
}
