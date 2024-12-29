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
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use setasign\Fpdi\Fpdi;




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



    protected function form()
    {


        $form = new Form(new Lesson());
        $form->tools(function (Form\Tools $tools) {
            // Add a cancel button that returns to the list view
            $tools->append('<a href="' . admin_url('lessons') . '" class="btn btn-sm btn-danger mr-1" style="margin-right: 5px;"><i class="fa fa-times"></i>&nbsp;Cancel</a>');
        });
        if ($form->isEditing()) {
            $form->html('
            <div style="text-align: center; font-size: 18px; font-weight: bold; color: #d9534f; margin-bottom: 15px; background-color: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb;">
                <i class="fa fa-exclamation-triangle" style="margin-right: 10px;"></i> 
                <span>Warning: Important Notice</span>
            </div>
            <div style="text-align: center; font-size: 16px; color: #721c24; margin-bottom: 20px;">
                <strong>If you make any minor changes to the following data, you need to REUPLOAD:</strong>
            </div>
            <div style="text-align: center; font-size: 16px; color: #383d41; margin-bottom: 20px;">
                <ul style="list-style-type: none; padding-left: 0;">
                    <li><strong>1) Video</strong> - Please upload the same or updated video again.</li>
                    <li><strong>2) Video Thumbnail</strong> - Please upload the same or updated video thumbnail again.</li>
                    <li><strong>3) Quiz</strong> - Please upload quiz file. Leave it empty if there is no quiz for a particular video.</li>
                </ul>
            </div>
            <div style="text-align: center; font-size: 14px; color: #c82333; font-weight: bold;">
                <span>If you save you will lose all the videos, thumbnails and quizes. Before saving please validate the changes.</span>
            <div style="text-align: center; font-size: 14px; color: #c82333; font-weight: bold;">
                <span>If you want to cancel these changes, click the Cancel button below to prevent any loss of data.</span>
            </div>
        ');
        }


        $form->tools(function (Form\Tools $tools) {

            $tools->disableView();    // Optional: if you want to disable view
            $tools->disableList();    // Optional: if you want to disable list
        });
        $form->display('id', __('ID'));
        $form->text('name', __('Name'));

        // $form->hidden('quiz', __('Quiz File'));

        $form->saving(function (Form $form) {
            Log::info('Form saving started', [
                'is_editing' => $form->isEditing(),
                'form_id' => $form->model()->id ?? 'new'
            ]);
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
                // Log::info($video);
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
            } else {
                if (isset($form->video)) {
                    Log::info('Processing new lesson videos', [
                        'total_videos' => count($form->video)
                    ]);
                    Log::info('PHP Upload Settings', [
                        'upload_max_filesize' => ini_get('upload_max_filesize'),
                        'post_max_size' => ini_get('post_max_size'),
                        'max_file_uploads' => ini_get('max_file_uploads'),
                        'memory_limit' => ini_get('memory_limit'),
                    ]);
                    foreach ($form->video as $k => $v) {
                        Log::info("Processing new video #{$k}", [
                            'has_required_fields' => [
                                'name' => !empty($v['name']),
                                'thumbnail' => !empty($v['thumbnail']),
                                'url' => !empty($v['url']),
                                'course_video_id' => !empty($v['course_video_id'])
                            ]
                        ]);
                    }
                    if (count($form->video) > ini_get('max_file_uploads')) {
                        Log::warning('Exceeding max_file_uploads limit', [
                            'attempted' => count($form->video),
                            'max_allowed' => ini_get('max_file_uploads')
                        ]);
                    }
                }
            }
        });

        Admin::script(<<<JS
            $(document).ready(function() {
               
                $('.btn-primary[type="submit"]').click(function(e) {
                    if ($('input[name$="[thumbnail]"]').val() === '' && 
                        $('input[name$="[quiz]"]').val() === '' && 
                        $('input[name$="[url]"]').val() === '') {
                        
                        e.preventDefault();
                        
                        Swal.fire({
                            title: 'Warning!',
                            text: 'You need to re-upload thumbnail, quiz, and video files before saving. Would you like to continue?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, save anyway',
                            cancelButtonText: 'No, let me upload files'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                
                                $(e.target).closest('form').submit();
                            }
                        });
                        
                        return false;
                    }
                });
            });
        JS);



        return $form;
    }
}
