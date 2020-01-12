<?php

namespace App\Controllers;

class AuthController extends AbstractController
{
    public function login()
    {
        if ($this->request->isMethod('GET')) {
            return $this->viewMake(__FUNCTION__);
        }

        $input = $this->input = $this->request->input();

        // 1. validate
        $validator = $this->ioc['validator'];
        $validator->setMessages($this->customMessages());
        $validation = $validator->validate($input, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validation->fails()) {
            // flash and redirect with input
            $this->ioc['session']->flashInput($input);
            $this->ioc['session']->flash('errors', $validation->errors()->all());

            return $this->ioc['response']->redirectTo('/login');
        }

        // find user
        $user = $this->ioc['db']->table('users')->where('username', $input['username'])->first();

        // attempt login
        if (!$user || !password_verify($input['password'], $user['password'])) {
            $this->ioc['session']->flash('errors', [
                $this->ioc->trans('Please enter valid username and password.'),
            ]);
            return $this->ioc['response']->redirectTo('/login');
        }

        // save user id to session
        $this->ioc['session']->set('_login', is_array($user) ? $user['id'] : $user->id);
        $this->ioc['session']->flash('success', [
            $this->ioc->trans('login was successful'),
        ]);

        return $this->ioc['response']->redirectTo('/');
    }

    public function logout()
    {
        $this->ioc['session']->remove('_login');
        $this->ioc['session']->flash('success', [
            $this->ioc->trans('logout was successful'),
        ]);

        return $this->ioc['response']->redirectTo('/');
    }
}
