<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\CourseType;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Tree;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/*

class CourseController extends AdminController
{

    protected function grid()
    {
        $grid = new Grid(new Course());

        if (Admin::user()->isRole('teacher')) {
            $token = Admin::user()->token;
            $grid->model()->where('user_token', '=', $token);
        }

        $grid->column('id', __('Id'));
        $grid->column('user_token', __('Teachers'))->display(
            function ($token) {
                //value function returns a specific field from the match
                return DB::table("admin_users")->where('token', '=', $token)->value('username');
            }
        );
        $grid->column('name', __('Name'));
        $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
        $grid->column('description', __('Description'));
        $grid->column('type_id', __('Type id'));
        $grid->column('price', __('Price'));
        $grid->column('lesson_num', __('Lesson num'));
        $grid->column('video_length', __('Video length'));

        $grid->column('created_at', __('Created at'));


        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Course::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('thumbnail', __('Thumbnail'));
        $show->field('video', __('Video'));
        $show->field('description', __('Description'));
        $show->field('price', __('Price'));
        $show->field('lesson_num', __('Lesson num'));
        $show->field('video_length', __('Video length'));
        $show->field('follow', __('Follow'));
        $show->field('score', __('Score'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }


    protected function form()
    {
        $form = new Form(new Course());
        $form->text('name', __('Name'));
        $result = CourseType::pluck('title', 'id');
        $form->select('type_id', __('Category'))->options($result);
        $form->image('thumbnail', __('Thumbnail'))->uniqueName();
        $form->file('video', __('Video'))->uniqueName();
        //$form->text('title', __('Title'));
        $form->text('description', __('Description'));
        $form->decimal('price', __('Price'));
        $form->number('lesson_num', __('Lesson number'));
        $form->number('video_length', __('Video length'));


        if (Admin::user()->isRole('teacher')) {
            $token = Admin::user()->token;
            $username = Admin::user()->username;
            $form->select('user_token', __('Teacher'))->options([$token => $username])->default($token)->readOnly();
        } else {
            $result = DB::table('admin_users')->pluck('username', 'token');
            $form->select('user_token', __('Teacher'))->options($res);
        }


        $form->display('created_at', __('Created at'));
        $form->display('updated_at', __('Updated at'));
        $form->switch('recommended', __('Recommended'))->default(0);
        return $form;
    }
}




*/


// class CourseController extends AdminController
// {

//     protected function grid()
//     {
//         $grid = new Grid(new Course());

//         $grid->column('id', __('Id'));
//         $grid->column('user_token', __('Teachers'))->display(
//             function ($token) {
//                 //value function returns a specific field from the match
//                 return User::where('token', '=', $token)->value('name');
//             }
//         );
//         $grid->column('name', __('Name'));
//         $grid->column('thumbnail', __('Thumbnail'))->image();


//         $grid->column('description', __('Description'));
//         $grid->column('type_id', __('Type id'));
//         $grid->column('price', __('Price'));
//         $grid->column('lesson_num', __('Lesson num'));
//         $grid->column('video_length', __('Video length'));

//         $grid->column('created_at', __('Created at'));


//         return $grid;
//     }

//     protected function detail($id)
//     {
//         $show = new Show(Course::findOrFail($id));

//         $show->field('id', __('Id'));
//         $show->field('name', __('Name'));
//         $show->field('thumbnail', __('Thumbnail'));
//         $show->field('video', __('Video'));
//         $show->field('description', __('Description'));
//         $show->field('price', __('Price'));
//         $show->field('lesson_num', __('Lesson num'));
//         $show->field('video_length', __('Video length'));
//         $show->field('follow', __('Follow'));
//         $show->field('score', __('Score'));
//         $show->field('created_at', __('Created at'));
//         $show->field('updated_at', __('Updated at'));

//         return $show;
//     }


//     protected function form()
//     {
//         $form = new Form(new Course());
//         $form->text('name', __('Name'));
//         $result = CourseType::pluck('title', 'id');
//         $form->select('type_id', __('Category'))->options($result);
//         $form->image('thumbnail', __('Thumbnail'))->uniqueName();
//         $form->file('video', __('Video'))->uniqueName();
//         $form->text('title', __('Title'));
//         $form->text('description', __('Description'));
//         $form->decimal('price', __('Price'));
//         $form->number('lesson_num', __('Lesson number'));
//         $form->number('video_length', __('Video length'));
//         $result = User::pluck('name', 'token');
//         $form->select('user_token', __('Teacher'))->options($result);
//         $form->display('created_at', __('Created at'));
//         $form->display('updated_at', __('Updated at'));
//         $form->switch('recommend', __('Recommend'))->default(0);
//         return $form;
//     }
// }

// USE THIS CODE 2 oct
// class CourseController extends AdminController
// {

//     protected function grid()
//     {
//         $grid = new Grid(new Course());

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $grid->model()->where('user_token', '=', $token);
//         }
//         $grid->model()->orderBy('id', 'desc');
//         $grid->column('id', __('Id'));
//         $grid->column('user_token', __('Teachers'))->display(
//             function ($token) {
//                 //value function returns a specific field from the match
//                 return DB::table("admin_users")->where('token', '=', $token)->value('username');
//             }
//         );
//         $grid->column('name', __('Name'));
//         $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
//         $grid->column('description', __('Description'));
//         $grid->column('type_id', __('Type id'));
//         $grid->column('price', __('Price'));
//         $grid->column('lesson_num', __('Lesson num'));
//         $grid->column('video_length', __('Video length'));

//         $grid->column('created_at', __('Created at'));


//         return $grid;
//     }

//     protected function detail($id)
//     {
//         $show = new Show(Course::findOrFail($id));

//         $show->field('id', __('Id'));
//         $show->field('name', __('Name'));
//         $show->field('thumbnail', __('Thumbnail'));
//         $show->field('video', __('Video'));
//         $show->field('description', __('Description'));
//         $show->field('price', __('Price'));
//         $show->field('lesson_num', __('Lesson num'));
//         $show->field('video_length', __('Video length'));
//         $show->field('follow', __('Follow'));
//         $show->field('score', __('Score'));
//         $show->field('created_at', __('Created at'));
//         $show->field('updated_at', __('Updated at'));

//         return $show;
//     }


//     protected function form()
//     {
// $form = new Form(new Course());
// $form->text('name', __('Name'));
// $result = CourseType::pluck('title', 'id');
// $form->select('type_id', __('Category'))->options($result);
// $form->image('thumbnail', __('Thumbnail'))->uniqueName();
// $form->file('video', __('Video'))->uniqueName();
// //$form->text('title', __('Title'));
// $form->text('description', __('Description'));
// $form->decimal('price', __('Price'));
// $form->number('lesson_num', __('Lesson number'));
// $form->number('video_length', __('Video length'));


// if (Admin::user()->isRole('teacher')) {
//     $token = Admin::user()->token;
//     $username = Admin::user()->username;
//     $form->select('user_token', __('Teacher'))->options([$token => $username])->default($token)->readOnly();
// } else {
//     $result = DB::table('admin_users')->pluck('username', 'token');
//     $form->select('user_token', __('Teacher'))->options($res);
// }


// $form->display('created_at', __('Created at'));
// $form->display('updated_at', __('Updated at'));
// $form->switch('recommended', __('Recommended'))->default(0);
// return $form;
//     }
// }





class CourseController extends AdminController
{

    protected function grid()
    {
        $grid = new Grid(new Course());

        if (Admin::user()->isRole('teacher')) {
            $token = Admin::user()->token;
            $grid->model()->where('user_token', '=', $token);
        }
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('user_token', __('Teachers'))->display(
            function ($token) {
                //value function returns a specific field from the match
                return DB::table("admin_users")->where('token', '=', $token)->value('username');
            }
        );
        $grid->column('name', __('Name'));
        $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
        $grid->column('description', __('Description'));
        $grid->column('type_id', __('Type id'));
        $grid->column('price', __('Price'));
        $grid->column('lesson_num', __('Lesson num'));
        $grid->column('video_length', __('Video length'));

        $grid->column('created_at', __('Created at'));


        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Course::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('thumbnail', __('Thumbnail'));
        $show->field('video', __('Video'));
        $show->field('description', __('Description'));
        $show->field('price', __('Price'));
        $show->field('lesson_num', __('Lesson num'));
        $show->field('video_length', __('Video length'));
        $show->field('follow', __('Follow'));
        $show->field('score', __('Score'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }


    protected function form()
    {
        $form = new Form(new Course());
        $form->text('name', __('Name'));
        $result = CourseType::pluck('title', 'id');
        $form->select('type_id', __('Category'))->options($result);
        $form->image('thumbnail', __('Thumbnail'))->uniqueName();
        $form->file('video', __('Video'))->uniqueName();
        //$form->text('title', __('Title'));
        $form->text('description', __('Description'));
        $form->decimal('price', __('Price'));
        $form->number('lesson_num', __('Lesson number'));
        $form->number('video_length', __('Video length'));

        if (Admin::user()->isRole('teacher')) {
            $token = Admin::user()->token;
            $username = Admin::user()->username;
            $form->select('user_token', __('Teacher'))->options([$token => $username])->default($token)->readOnly();
        } else {
            $result = DB::table('admin_users')->pluck('username', 'token');
            $form->select('user_token', __('Teacher'))->options($result);
        }

        $form->display('created_at', __('Created at'));
        $form->display('updated_at', __('Updated at'));
        $form->switch('recommended', __('Recommended'))->default(0);


        return $form;
    }
}







// class CourseController extends AdminController
// {
//     /**
//      * Title for current resource.
//      *
//      * @var string
//      */
//     protected $title = 'Course';

//     /**
//      * Make a grid builder.
//      *
//      * @return Grid
//      */
//     protected function grid()
//     {
//         $grid = new Grid(new Course());

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $grid->model()->where('user_token', '=', $token);
//         }

//         $grid->model()->orderBy('id', 'desc');

//         $grid->column('id', __('Id'));
//         $grid->column('user_token', __('Teacher'))->display(function ($item) {

//             $item = DB::table("admin_users")->where("token", "=", $item)->value("username");
//             return $item;
//         });
//         $grid->column('name', __('Name'));
//         $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
//         $grid->column('description', __('Description'));
//         $grid->column('type_id', __('Type id'));
//         $grid->column('price', __('Price'));
//         $grid->column('lesson_num', __('Lesson Number'));
//         $grid->column('video_len', __('Video Length'));
//         // $grid->column('down_num', __('Download Doc Number'));
//         $grid->column('created_at', __('Created_at'));
//         $grid->disableExport();
//         $grid->disableFilter();
//         $grid->actions(function ($actions) {

//             $actions->disableView();
//         });

//         return $grid;
//     }

//     /**
//      * Make a show builder.
//      *
//      * @param mixed $id
//      * @return Show
//      */
//     protected function detail($id)
//     {
//         $show = new Show(Course::findOrFail($id));

//         $show->field('id', __('Id'));
//         $show->field('name', __('Name'));
//         $show->field('thumbnail', __('Thumbnail'));
//         $show->field('video', __('Video'));
//         $show->field('description', __('Description'));
//         $show->field('price', __('Price'));
//         $show->field('lesson_num', __('Lesson num'));
//         $show->field('video_length', __('Video length'));
//         $show->field('follow', __('Follow'));
//         $show->field('score', __('Score'));
//         $show->field('created_at', __('Created at'));
//         $show->field('updated_at', __('Updated at'));

//         return $show;
//     }
//     /**
//      * Make a form builder.
//      *
//      * @return Form
//      */
//     protected function form()
//     {
//         $form = new Form(new Course);

//         $form->text('name', __('Name'));
//         $form->display('id', __('ID'));

//         // $res = DB::table("course_types")->pluck('title', 'id');
//         $result = CourseType::pluck('title', 'id');
//         $form->select('type_id', __('Category'))->options($result);

//         $form->image('thumbnail', __('Thumbnail'))->uniqueName();
//         $form->file('video', __('Video'))->uniqueName();
//         $form->text('description', __('Description'));
//         $form->decimal('price', __('Price'));
//         $form->number('lesson_num', __('Lesson Number'));
//         $form->number('video_len', __('Video Length'));
//         // $form->number('down_num', __('Download Doc Number'));

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $username = Admin::user()->username;
//             $form->select('user_token', __('Teacher'))->options([$token => $username])->default($token)->readonly();
//         } else {
//             $res = DB::table("admin_users")->pluck('username', 'token');
//             $form->select('user_token', __('Teacher'))->options($res);
//         }



//         $form->display('created_at', __('Created At'));
//         $form->display('updated_at', __('Updated At'));
//         $form->switch('recommended', __('Recommended'))->default(0);


//         return $form;
//     }
// }


// class CourseController extends AdminController
// {

//     protected function grid()
//     {
//         $grid = new Grid(new Course());
//         if (Admin::user()->isRole('teacher')) {
//             //own course
//             $token = Admin::user()->token;
//             $grid->model()->where('user_token', '=', $token);
//         }
//         $grid->column('id', __('Id'));
//         $grid->column('user_token', __('Teachers'))->display(
//             function ($token) {
//                 //value function returns a specific field from the match
//                 return DB::table("admin_users")->where('token', '=', $token)->value('username');
//             }
//         );
//         $grid->column('name', __('Name'));
//         $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
//         $grid->column('description', __('Description'));
//         $grid->column('type_id', __('Type id'));
//         $grid->column('price', __('Price'));
//         $grid->column('lesson_num', __('Lesson num'));
//         $grid->column('video_length', __('Video length'));

//         $grid->column('created_at', __('Created at'));


//         return $grid;
//     }

//     protected function detail($id)
//     {
//         $show = new Show(Course::findOrFail($id));

//         $show->field('id', __('Id'));
//         $show->field('name', __('Name'));
//         $show->field('thumbnail', __('Thumbnail'));
//         $show->field('video', __('Video'));
//         $show->field('description', __('Description'));
//         $show->field('price', __('Price'));
//         $show->field('lesson_num', __('Lesson num'));
//         $show->field('video_length', __('Video length'));
//         $show->field('follow', __('Follow'));
//         $show->field('score', __('Score'));
//         $show->field('created_at', __('Created at'));
//         $show->field('updated_at', __('Updated at'));

//         return $show;
//     }


//     protected function form()
//     {
//         $form = new Form(new Course());
//         $form->text('name', __('Name'));
//         $result = CourseType::pluck('title', 'id');
//         $form->select('type_id', __('Category'))->options($result);
//         $form->image('thumbnail', __('Thumbnail'))->uniqueName();
//         $form->file('video', __('Video'))->uniqueName();
//         //$form->text('title', __('Title'));
//         $form->text('description', __('Description'));
//         $form->decimal('price', __('Price'));
//         $form->number('lesson_num', __('Lesson number'));
//         $form->number('video_length', __('Video length'));



//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $username = Admin::user()->username;
//             $form->select('user_token', __('Teacher'))->options([$token => $username])->default($token)->readonly();
//         } else {
//             $result = DB::table('admin_users')->pluck('username', 'token');
//             $form->select('user_token', __('Teacher'))->options($result);
//         }

//         $form->display('created_at', __('Created at'));
//         $form->display('updated_at', __('Updated at'));
//         $form->switch('recommended', __('Recommended'))->default(0);
//         return $form;
//     }
// }
