<?php

namespace App\Controllers;

use App\Models\TasksModel as Model;

class TasksController extends AbstractController
{
    // protected $module = 'tasks';

    public function index()
    {
        $perPage = $this->request->input('perPage', 3);

        $sortBy = $this->request->input('sortBy', 'id');

        $sortOrderQry = strtolower($this->request->input('sortOrder', $sortOrder = 'descending'));

        if (substr($sortOrder, 0, 4) === 'desc') {
            $sortOrder = 'desc';
        } else {
            $sortOrder = 'asc';
        }

        $items = Model::query();

        if (!$items->getModel()->isFillable($sortBy)) {
            $sortBy = $items->getModel()->getKeyName();
        }

        $items = $items->orderBy($sortBy, $sortOrder)->paginate($perPage)->appends(
            array('sortBy' => $sortBy, 'sortOrder' => $sortOrderQry)
        );

        return $this->share(compact('items'))->viewMake(__FUNCTION__);
    }

    public function create()
    {
        $data = [];

        if ($model = Model::find($this->request->input('id'))) {
            // only logedin admin can edit
            if (!$this->ioc['user']) {
                return $this->ioc['response']->redirectTo('/');
            }

            $this->share($model->getAttributes())->share(compact('model'));
        }

        return $this->viewMake(__FUNCTION__, $data);
    }

    // save the submited form for the first time
    public function store()
    {
        $input = $this->input = $this->request->input();

        foreach ($input as $key => $value) {
            $input[$key] = strip_tags($value);
        }

        // 1. validate
        $validator = $this->ioc['validator'];
        $validator->setMessages($this->customMessages());
        $validation = $validator->validate($input, [
            'username' => 'required',
            'email' => 'required|email',
            'task_text' => 'required',
        ]);

        if ($validation->fails()) {
            // flash and redirect with input
            $this->ioc['session']->flashInput($input);

            $this->ioc['session']->flash('errors', $validation->errors()->all());

            return $this->ioc['response']->redirectTo('/tasks/create');
        }

        // 2. save to store
        $model = Model::firstOrNew(['id' => $this->request->input('id')])->fill($input);

        if ($user = $this->ioc['user']) {
            $model->updated_by = $user->username;
        } else {
            $model->updated_by = $input['username'];
            unset($input['task_status']);
        }

        $model->save();

        $this->ioc['session']->flash('success', ['record saved successfully']);

        return $this->ioc['response']->redirectTo('/' . $this->module);
    }

    // delete an existing record
    public function destroy()
    {
        $id = $this->request->input('id');

        if ($model = Model::find($id)) {
            $model->destroy($id);
        }
        // flash info model not found message
        else {
            $this->ioc['session']->flash('errors', 'Record was not found.');
        }

        return $this->ioc['response']->redirectTo('/' . $this->module);
    }

    public function lang()
    {
        $lang = $this->request->input('lang');

        if ($lang) {
            $this->ioc['translator']->setLocale($lang);

            $this->ioc['session']->set('locale', $this->ioc['translator']->getLocale());
        }

        return $this->ioc['response']->redirectTo($this->ioc['session']->previousUrl() ?: '/');
    }

    /**
     * Add a piece of shared data.
     *
     * @param  array|string  $key
     * @param  mixed|null    $value
     * @return self
     */
    public function shareVars()
    {
        parent::shareVars();

        $data = [
            '_usernameList' => Model::distinct()->get(['username'])->lists('username'),
        ];

        return $this->share($data);
    }
}
