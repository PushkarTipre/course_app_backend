<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends AdminController
{
    protected $title = 'Members';

    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->column('id', __('Id'));
        $grid->column('unique_id', __('Unique ID'));
        $grid->column('name', __('Name'));
        $grid->column('email', __('Email'));

        // $grid->column('access_token', __('access_token'));

        $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableFilter();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('unique_id', __('Unique ID'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new User());
        $form->textarea('name', __('Name'));
        $form->textarea('email', __('Email'));

        return $form;
    }
}
