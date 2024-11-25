<?php

namespace App\Admin\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Illuminate\Support\Facades\DB;
use Encore\Admin\Facades\Admin;

use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use setasign\Fpdi\Fpdi;

// class LessonController extends AdminController
// {
//     /**
//      * Title for current resource.
//      *
//      * @var string
//      */
//     protected $title = 'Lesson';

//     /**
//      * Make a grid builder.
//      *
//      * @return Grid
//      */
//     protected function grid()
//     {
//         $grid = new Grid(new Lesson());

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $ids = DB::table('courses')->where('user_token', '=', $token)->pluck('id')->toArray();

//             $grid->model()->whereIn('course_id', $ids);
//         }

//         $grid->column('id', __('Id'));
//         $grid->column('course_id', __('Course'))->display(function ($id) {
//             $item = Course::where('id', '=', $id)->value('name');
//             return $item;
//         });
//         $grid->column('name', __('Name'));
//         $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
//         $grid->column('description', __('Description'));
//         // $grid->column('video', __('Video'));
//         $grid->column('created_at', __('Created at'));
//         $grid->column('updated_at', __('Updated at'));

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
//         $show = new Show(Lesson::findOrFail($id));

//         $show->field('id', __('Id'));
//         $show->field('course_id', __('Course id'));
//         $show->field('name', __('Name'));
//         $show->field('thumbnail', __('Thumbnail'));
//         $show->field('description', __('Description'));
//         $show->field('video', __('Video'));
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
//         $form = new Form(new Lesson());


//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $id = DB::table('courses')->where('user_token', '=', $token)->pluck("names", "id");
//             $form->select('course_id', __('Courses'))->options($id);
//         } else {
//             $result = DB::table('courses')->pluck('name', 'id');
//             $form->select('course_id', __('Courses'))->options($result);
//         }

//         //$form->number('course_id', __('Course id'));

//         // $result = Course::pluck('name', 'id');
//         // $form->select('course_id', __('Courses'))->options($result);
//         $form->text('name', __('Name'));
//         $form->image('thumbnail', __('Thumbnail'))->uniqueName();
//         $form->text('description', __('Description'));

//         if ($form->isEditing()) {

//             $form->table('video', function ($form) {
//                 $form->text('name')->rules('required');
//                 $form->image('thumbnail')->uniqueName()->rules('required');
//                 $form->file('url')->rules('required');

//                 $form->hidden('old_thumbnail');
//                 $form->hidden('old_url');
//             });
//         } else {
//             $form->table('video', function ($form) {
//                 $form->text('name')->rules('required');
//                 $form->image('thumbnail')->uniqueName()->rules('required');
//                 $form->file('url')->rules('required');
//             });
//         }


//         $form->display('created_at', __('Created at'));
//         $form->display('updated_at', __('Updated at'));

//         $form->saving(function (Form $form) {

//             if ($form->isEditing()) {
//                 $video = $form->video;
//                 $result = $form->model()->video;
//                 $newVideos = [];
//                 $path = env("APP_URL") . "uploads/";
//                 foreach ($video as $k => $v) {
//                     $videoVal = [];
//                     if (empty($v["url"])) {
//                         $videoVal["old_url"] = str_replace($path, "", $result[$k]["url"]);
//                     } else {
//                         $videoVal["url"] = $v["url"];
//                     }


//                     if (empty($v["name"])) {
//                         $videoVal["name"] = str_replace($path, "", $result[$k]["name"]);
//                     } else {
//                         $videoVal["name"] = $v["name"];
//                     }

//                     array_push($newVideos, $videoVal);
//                 }

//                 $form->video = $newVideos;
//             }
//         });

//         return $form;
//     }
// }

//FINAL CODE
// class LessonController extends AdminController
// {
//     /**
//      * Title for current resource.
//      *
//      * @var string
//      */
//     protected $title = 'Lesson';

//     /**
//      * Make a grid builder.
//      *
//      * @return Grid
//      */
//     protected function grid()
//     {
//         $grid = new Grid(new Lesson());

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $ids = DB::table("courses")->where("user_token", "=", $token)->pluck("id")->toArray();

//             $grid->model()->whereIn('course_id', $ids);
//         }

//         $grid->model()->orderBy('id', 'desc');

//         $grid->column('id', __('Id'));

//         $grid->column('course_id', __('Course'))->display(function ($item) {

//             $item = DB::table("courses")->where("id", "=", $item)->value("name");
//             return $item;
//         });
//         $grid->column('name', __('Name'));
//         $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
//         // $grid->column('video', __('Video'));
//         $grid->column('description', __('Description'));

//         $grid->column('created_at', __('Created_at'));

//         $grid->disableExport();
//         $grid->disableFilter();
//         $grid->actions(function ($actions) {
//             // 去掉查看
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
//     protected function detail($id) {}

//     /**
//      * Make a form builder.
//      *
//      * @return Form
//      */
//     protected function form()
//     {
//         $form = new Form(new Lesson());

//         $form->display('id', __('ID'));
//         $form->text('name', __('Name'));


//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $res = DB::table("courses")->where("user_token", "=", $token)->pluck('name', 'id');
//             $form->select('course_id', __('Courses'))->options($res);
//         } else {

//             $res = DB::table("courses")->pluck('name', 'id');
//             $form->select('course_id', __('Courses'))->options($res);
//         }


//         $form->image('thumbnail', __('Thumbnail'))->uniqueName();
//         $form->text('description', __('Description'));
//         if ($form->isEditing()) {
//             $form->table('video', function ($table) {

//                 $table->text('name');
//                 $table->hidden('old_thumbnail');
//                 $table->hidden('old_url');
//                 $table->image('thumbnail')->uniqueName();
//                 $table->file('url');
//             });
//         } else {
//             $form->table('video', function ($table) {

//                 $table->text('name')->required();
//                 $table->image('thumbnail')->uniqueName()->required();
//                 $table->file('url')->required();
//             });
//         }

//         $form->display('created_at', __('Created At'));
//         $form->display('updated_at', __('Updated At'));
//         //during processing after submit
//         $form->saving(function (Form $form) {

//             //  dump($form->video);
//             if ($form->isEditing()) {

//                 $video = $form->video;
//                 //previous values //values in the Form container
//                 $res = $form->model()->video;
//                 $newVideo = [];
//                 $path = env("APP_URL") . "uploads/";
//                 foreach ($video as $k => $v) {
//                     $valueVideo = [];

//                     //if not editing
//                     if (empty($v["url"])) {
//                         //because of getVideoAttribue we get domain url
//                         $valueVideo["old_url"] = empty($res[$k]["url"]) ? "" : str_replace($path, "", $res[$k]["url"]);
//                     } else {
//                         $valueVideo["url"] = $v["url"];
//                     }
//                     if (empty($v["thumbnail"])) {
//                         $valueVideo["old_thumbnail"] = empty($res[$k]["thumbnail"]) ? "" : str_replace($path, "", $res[$k]["thumbnail"]);
//                     } else {
//                         $valueVideo["thumbnail"] = $v["thumbnail"];
//                     }
//                     if (empty($v["name"])) {
//                         $valueVideo["name"] = empty($res[$k]["name"]) ? "" : $res[$k]["name"];
//                     } else {
//                         $valueVideo["name"] = $v["name"];
//                     }
//                     $valueVideo["_remove_"] = $v["_remove_"];
//                     array_push($newVideo, $valueVideo);
//                 }
//                 // dump($form->video);
//                 //  dump($newVideo);
//                 $form->video = $newVideo;
//             }
//         });


//         return $form;
//     }
// }

//USE THIS CODE
// class LessonController extends AdminController
// {
//     protected $title = 'Lesson';

//     protected function grid()
//     {
//         $grid = new Grid(new Lesson());

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $ids = DB::table("courses")->where("user_token", "=", $token)->pluck("id")->toArray();

//             $grid->model()->whereIn('course_id', $ids);
//         }

//         $grid->model()->orderBy('id', 'desc');

//         $grid->column('id', __('Id'));
//         // $grid->column('lesson_id', __('Lesson ID'));
//         $grid->column('course_id', __('Course'))->display(function ($item) {
//             $item = DB::table("courses")->where("id", "=", $item)->value("name");
//             return $item;
//         });
//         $grid->column('name', __('Name'));
//         $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
//         $grid->column('description', __('Description'));
//         $grid->column('created_at', __('Created_at'));

//         $grid->disableExport();
//         $grid->disableFilter();
//         $grid->actions(function ($actions) {
//             $actions->disableView();
//         });

//         return $grid;
//     }

//     protected function detail($id) {}

//     protected function form()
//     {
//         $form = new Form(new Lesson());

//         $form->display('id', __('ID'));
//         // $form->display('lesson_id', __('Lesson ID'))->with(function ($value) {
//         //     return $value ?: 'Will be automatically generated';
//         // });
//         $form->text('name', __('Name'));

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $res = DB::table("courses")->where("user_token", "=", $token)->pluck('name', 'id');
//             $form->select('course_id', __('Courses'))->options($res);
//         } else {
//             $res = DB::table("courses")->pluck('name', 'id');
//             $form->select('course_id', __('Courses'))->options($res);
//         }

//         $form->image('thumbnail', __('Thumbnail'))->uniqueName();
//         $form->text('description', __('Description'));

//         if ($form->isEditing()) {
//             $form->table('video', function ($table) {
//                 $table->text('name');
//                 $table->hidden('old_thumbnail');
//                 $table->hidden('old_url');
//                 $table->image('thumbnail')->uniqueName();
//                 $table->file('url');
//             });
//         } else {
//             $form->table('video', function ($table) {
//                 $table->text('name')->required();
//                 $table->image('thumbnail')->uniqueName()->required();
//                 $table->file('url')->required();
//             });
//         }

//         $form->display('created_at', __('Created At'));
//         $form->display('updated_at', __('Updated At'));

//         $form->saving(function (Form $form) {
//             // if ($form->isCreating()) {
//             //     // Generate the next lesson_id
//             //     $maxLessonId = Lesson::max('lesson_id') ?? 0;
//             //     $form->lesson_id = $maxLessonId + 1;
//             // }

//             if ($form->isEditing()) {
//                 $video = $form->video;
//                 $res = $form->model()->video;
//                 $newVideo = [];
//                 $path = env("APP_URL") . "uploads/";
//                 foreach ($video as $k => $v) {
//                     $valueVideo = [];
//                     if (empty($v["url"])) {
//                         $valueVideo["old_url"] = empty($res[$k]["url"]) ? "" : str_replace($path, "", $res[$k]["url"]);
//                     } else {
//                         $valueVideo["url"] = $v["url"];
//                     }
//                     if (empty($v["thumbnail"])) {
//                         $valueVideo["old_thumbnail"] = empty($res[$k]["thumbnail"]) ? "" : str_replace($path, "", $res[$k]["thumbnail"]);
//                     } else {
//                         $valueVideo["thumbnail"] = $v["thumbnail"];
//                     }
//                     if (empty($v["name"])) {
//                         $valueVideo["name"] = empty($res[$k]["name"]) ? "" : $res[$k]["name"];
//                     } else {
//                         $valueVideo["name"] = $v["name"];
//                     }
//                     $valueVideo["_remove_"] = $v["_remove_"];
//                     array_push($newVideo, $valueVideo);
//                 }
//                 $form->video = $newVideo;
//             }
//         });

//         return $form;
//     }
// }




//FINAL CODE - 24 NOV 2024
// class LessonController extends AdminController
// {
//     protected $title = 'Lesson';

//     protected function grid()
//     {
//         $grid = new Grid(new Lesson());

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $ids = DB::table("courses")->where("user_token", "=", $token)->pluck("id")->toArray();

//             $grid->model()->whereIn('course_id', $ids);
//         }

//         $grid->model()->orderBy('id', 'desc');

//         $grid->column('id', __('Id'));
//         $grid->column('course_id', __('Course'))->display(function ($item) {
//             $item = DB::table("courses")->where("id", "=", $item)->value("name");
//             return $item;
//         });
//         $grid->column('name', __('Name'));
//         $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
//         $grid->column('description', __('Description'));
//         $grid->column('created_at', __('Created_at'));

//         $grid->disableExport();
//         $grid->disableFilter();
//         $grid->actions(function ($actions) {
//             $actions->disableView();
//         });

//         return $grid;
//     }

//     protected function detail($id) {}

//     protected function form()
//     {


//         $form = new Form(new Lesson());
//         $form->tools(function (Form\Tools $tools) {
//             // Add a cancel button that returns to the list view
//             $tools->append('<a href="' . admin_url('lessons') . '" class="btn btn-sm btn-danger mr-1" style="margin-right: 5px;"><i class="fa fa-times"></i>&nbsp;Cancel</a>');
//         });
//         $form->tools(function (Form\Tools $tools) {

//             $tools->disableView();    // Optional: if you want to disable view
//             $tools->disableList();    // Optional: if you want to disable list
//         });
//         $form->display('id', __('ID'));
//         $form->text('name', __('Name'));

//         if (Admin::user()->isRole('teacher')) {
//             $token = Admin::user()->token;
//             $res = DB::table("courses")->where("user_token", "=", $token)->pluck('name', 'id');
//             $form->select('course_id', __('Courses'))->options($res);
//         } else {
//             $res = DB::table("courses")->pluck('name', 'id');
//             $form->select('course_id', __('Courses'))->options($res);
//         }

//         $form->image('thumbnail', __('Thumbnail'))->uniqueName();
//         $form->text('description', __('Description'));

//         if ($form->isEditing()) {
//             $form->table('video', function ($table) {
//                 $table->text('name');
//                 $table->hidden('old_thumbnail');
//                 $table->hidden('old_url');
//                 $table->hidden('old_course_video_id');  // Add this line
//                 $table->image('thumbnail')->uniqueName();
//                 $table->file('quiz');
//                 $table->file('url');

//                 $table->text('course_video_id');
//             });
//         } else {
//             $form->table('video', function ($table) {
//                 $table->text('name')->required();
//                 $table->image('thumbnail')->uniqueName()->required();
//                 $table->file('url')->required();
//                 $table->file('quiz');
//                 $table->text('course_video_id')->required();
//             });
//         }

//         $form->display('created_at', __('Created At'));
//         $form->display('updated_at', __('Updated At'));

//         $form->saving(function (Form $form) {
//             if ($form->isEditing()) {
//                 $video = $form->video;
//                 $res = $form->model()->video;
//                 $newVideo = [];
//                 $path = env("APP_URL") . "uploads/";
//                 foreach ($video as $k => $v) {
//                     $valueVideo = [];
//                     if (empty($v["url"])) {
//                         $valueVideo["old_url"] = empty($res[$k]["url"]) ? "" : str_replace($path, "", $res[$k]["url"]);
//                     } else {
//                         $valueVideo["url"] = $v["url"];
//                     }
//                     if (empty($v["quiz"])) {
//                         $valueVideo["old_quiz"] = empty($res[$k]["quiz"]) ? "" : str_replace($path, "", $res[$k]["quiz"]);
//                     } else {
//                         $valueVideo["quiz"] = $v["quiz"];
//                     }
//                     if (empty($v["thumbnail"])) {
//                         $valueVideo["old_thumbnail"] = empty($res[$k]["thumbnail"]) ? "" : str_replace($path, "", $res[$k]["thumbnail"]);
//                     } else {
//                         $valueVideo["thumbnail"] = $v["thumbnail"];
//                     }
//                     if (empty($v["name"])) {
//                         $valueVideo["name"] = empty($res[$k]["name"]) ? "" : $res[$k]["name"];
//                     } else {
//                         $valueVideo["name"] = $v["name"];
//                     }
//                     if (empty($v["course_video_id"])) {
//                         $valueVideo["old_course_video_id"] = empty($res[$k]["course_video_id"]) ? "" : $res[$k]["course_video_id"];
//                     } else {
//                         $valueVideo["course_video_id"] = $v["course_video_id"];
//                     }
//                     $valueVideo["_remove_"] = $v["_remove_"];
//                     array_push($newVideo, $valueVideo);
//                 }
//                 $form->video = $newVideo;
//             }
//         });

//         return $form;
//     }
// }



class LessonController extends AdminController
{
    protected $title = 'Lesson';

    protected function grid()
    {
        $grid = new Grid(new Lesson());

        if (Admin::user()->isRole('teacher')) {
            $token = Admin::user()->token;
            $ids = DB::table("courses")->where("user_token", "=", $token)->pluck("id")->toArray();

            $grid->model()->whereIn('course_id', $ids);
        }

        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('course_id', __('Course'))->display(function ($item) {
            $item = DB::table("courses")->where("id", "=", $item)->value("name");
            return $item;
        });
        $grid->column('name', __('Name'));
        $grid->column('thumbnail', __('Thumbnail'))->image('', 50, 50);
        $grid->column('description', __('Description'));
        $grid->column('created_at', __('Created_at'));

        $grid->disableExport();
        $grid->disableFilter();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    protected function detail($id) {}

    // function extractQuizData($file)
    // {
    //     $content = $this->getTextFromFile($file);
    //     $quizData = $this->parseQuizContent($content);
    //     return $quizData;
    // }

    protected function getTextFromFile($file)
    {
        // Use PHPWord or FPDF to extract text content from the file
        if (pathinfo($file, PATHINFO_EXTENSION) === 'docx') {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->path());
            $content = $phpWord->getText();
        } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $pdf = new Fpdi();
            $pdf->setSourceFile($file->path());
            $pageCount = $pdf->setSourceFile($file->path());
            $content = '';
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pdf->AddPage();
                $tplIdx = $pdf->importPage($pageNo);
                $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);
                $parser = new Parser();
                $pdf = $parser->parseFile($file->path());
                $content .= $pdf->getText();
            }
        } else {
            // Handle other file types as needed
            $content = '';
        }
        return $content;
    }

    // protected function getPageContent($pdfPath, $pageNumber)
    // {
    //     $pdf = new Fpdi();
    //     $pdf->setSourceFile($pdfPath);
    //     $pageCount = $pdf->setSourceFile($pdfPath);

    //     if ($pageNumber > $pageCount) {
    //         return '';
    //     }

    //     $pdf->AddPage();
    //     $tplIdx = $pdf->importPage($pageNumber);
    //     $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);
    //     $content = $pdf->GetPageContent($pageNumber);

    //     return $content;
    // }

    // protected function parseQuizContent($content)
    // {
    //     // Use regular expressions or NLP techniques to identify questions, answers, and correct answers
    //     // Return the data in the desired JSON format
    //     $quizData = [
    //         'quiz_name' => 'My Quiz',
    //         'questions' => [
    //             [
    //                 'question' => 'What is the capital of France?',
    //                 'answers' => ['Paris', 'London', 'Berlin', 'Madrid'],
    //                 'correct_answer' => 0
    //             ],
    //             [
    //                 'question' => 'What is the largest planet in our solar system?',
    //                 'answers' => ['Earth', 'Mars', 'Jupiter', 'Saturn'],
    //                 'correct_answer' => 2
    //             ]
    //         ]
    //     ];
    //     return $quizData;
    // }

    protected function form()
    {


        $form = new Form(new Lesson());
        $form->tools(function (Form\Tools $tools) {
            // Add a cancel button that returns to the list view
            $tools->append('<a href="' . admin_url('lessons') . '" class="btn btn-sm btn-danger mr-1" style="margin-right: 5px;"><i class="fa fa-times"></i>&nbsp;Cancel</a>');
        });
        $form->tools(function (Form\Tools $tools) {

            $tools->disableView();    // Optional: if you want to disable view
            $tools->disableList();    // Optional: if you want to disable list
        });
        $form->display('id', __('ID'));
        $form->text('name', __('Name'));

        $form->hidden('quiz_file', __('Quiz File'));

        $form->saving(function (Form $form) {
            if ($form->quiz_file) {
                $quizContent = $this->extractQuizData($form->quiz_file);
                $form->quiz_json = json_encode($quizContent);
            }
        });

        if (Admin::user()->isRole('teacher')) {
            $token = Admin::user()->token;
            $res = DB::table("courses")->where("user_token", "=", $token)->pluck('name', 'id');
            $form->select('course_id', __('Courses'))->options($res);
        } else {
            $res = DB::table("courses")->pluck('name', 'id');
            $form->select('course_id', __('Courses'))->options($res);
        }

        $form->image('thumbnail', __('Thumbnail'))->uniqueName();
        $form->text('description', __('Description'));

        if ($form->isEditing()) {
            $form->table('video', function ($table) {
                $table->text('name');
                $table->hidden('old_thumbnail');
                $table->hidden('old_url');
                $table->hidden('old_course_video_id');  // Add this line
                $table->image('thumbnail')->uniqueName();
                $table->file('quiz');
                $table->file('url');

                $table->text('course_video_id');
            });
        } else {
            $form->table('video', function ($table) {
                $table->text('name')->required();
                $table->image('thumbnail')->uniqueName()->required();
                $table->file('url')->required();
                $table->file('quiz');
                $table->text('course_video_id')->required();
            });
        }

        $form->display('created_at', __('Created At'));
        $form->display('updated_at', __('Updated At'));

        $form->saving(function (Form $form) {
            if ($form->isEditing()) {
                $video = $form->video;
                $res = $form->model()->video;
                $newVideo = [];
                $path = env("APP_URL") . "uploads/";
                foreach ($video as $k => $v) {
                    $valueVideo = [];
                    if (empty($v["url"])) {
                        $valueVideo["old_url"] = empty($res[$k]["url"]) ? "" : str_replace($path, "", $res[$k]["url"]);
                    } else {
                        $valueVideo["url"] = $v["url"];
                    }
                    if (empty($v["quiz"])) {
                        $valueVideo["old_quiz"] = empty($res[$k]["quiz"]) ? "" : str_replace($path, "", $res[$k]["quiz"]);
                    } else {
                        $valueVideo["quiz"] = $v["quiz"];
                    }
                    if (empty($v["thumbnail"])) {
                        $valueVideo["old_thumbnail"] = empty($res[$k]["thumbnail"]) ? "" : str_replace($path, "", $res[$k]["thumbnail"]);
                    } else {
                        $valueVideo["thumbnail"] = $v["thumbnail"];
                    }
                    if (empty($v["name"])) {
                        $valueVideo["name"] = empty($res[$k]["name"]) ? "" : $res[$k]["name"];
                    } else {
                        $valueVideo["name"] = $v["name"];
                    }
                    if (empty($v["course_video_id"])) {
                        $valueVideo["old_course_video_id"] = empty($res[$k]["course_video_id"]) ? "" : $res[$k]["course_video_id"];
                    } else {
                        $valueVideo["course_video_id"] = $v["course_video_id"];
                    }
                    $valueVideo["_remove_"] = $v["_remove_"];
                    array_push($newVideo, $valueVideo);
                }
                $form->video = $newVideo;
            }
        });

        return $form;
    }
}
