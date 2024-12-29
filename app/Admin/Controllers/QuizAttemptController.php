<?php

namespace App\Admin\Controllers;

use App\Models\QuizAttempt;
use App\Models\Course;
use App\Models\Lesson;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @property mixed $input
 */

class QuizAttemptController extends AdminController
{
    // Grid method: List Quiz Attempts
    protected function grid()
    {
        $grid = new Grid(new QuizAttempt());

        $grid->disableCreateButton();
        $grid->disableActions();

        // Apply teacher-specific filtering
        if (Admin::user()->isRole('teacher')) {
            $token = Admin::user()->token;

            // Get course IDs belonging to the teacher
            $courseIds = DB::table('courses')->where('user_token', $token)->pluck('id')->toArray();

            // Get lesson IDs linked to these courses
            $lessonIds = DB::table('lessons')->whereIn('course_id', $courseIds)->pluck('id')->toArray();

            // Filter QuizAttempts by lesson IDs
            $grid->model()->whereIn('lesson_id', $lessonIds);
        }

        $grid->model()->orderBy('id', 'desc');


        $grid->column('id', __('ID'));
        $grid->column('user_name', __('User Name'));
        $grid->column('unique_id', __('Unique ID'));
        $grid->column('quiz_unique_id', __('Quiz Unique ID'));

        $grid->column('course_id', __('Course Name'))->display(function ($courseId) {
            return Course::find($courseId)->name ?? 'Unknown';
        });

        $grid->column('lesson_id', __('Lesson Name'))->display(function ($lessonId) {
            return Lesson::find($lessonId)->name ?? 'Unknown';
        });

        $grid->column('course_video_id', __('Course Video ID'))->display(function () {
            $courseVideoId = $this->getAttribute('course_video_id');
            $lesson = Lesson::find($this->getAttribute('lesson_id'));

            if ($lesson) {
                $videos = is_array($lesson->video) ? $lesson->video : json_decode($lesson->video, true);
                $video = collect($videos)->firstWhere('course_video_id', $courseVideoId);

                return $video
                    ? $courseVideoId . ' (' . ($video['name'] ?? 'Unknown Video') . ')'
                    : $courseVideoId;
            }

            return $courseVideoId ?? 'N/A';
        });

        $grid->column('score', __('Score'));
        $grid->column('completed', __('Completed'))->display(function ($completed) {
            return $completed ? 'Yes' : 'No';
        });

        $grid->column('quiz_started_at', __('Quiz Started At'));
        $grid->column('quiz_ended_at', __('Quiz Ended At'));
        $grid->filter(function ($filter) {

            $filter->disableIdFilter();


            $filter->where(function ($query) {

                $query->where('user_name', 'like', "%{$this->input}%")
                    ->orWhere('unique_id', 'like', "%{$this->input}%");
            }, 'Search User Name OR Unique ID');
        });

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });

        return $grid;
    }


    // Form method: Create/Edit Quiz Attempt
    protected function form()
    {
        $form = new Form(new QuizAttempt());

        // Display the ID field
        // Only display the fields without allowing edits
        $form->display('id', __('ID'));
        $form->display('user_name', __('User Name'));
        $form->display('unique_id', __('Unique ID'));
        $form->display('quiz_unique_id', __('Quiz Unique ID'));
        $form->display('course_id', __('Course'));
        $form->display('lesson_id', __('Lesson'));
        $form->display('course_video_id', __('Course Video ID'));
        $form->display('score', __('Score'));
        $form->display('completed', __('Completed'));
        $form->display('quiz_started_at', __('Quiz Started At'));
        $form->display('quiz_ended_at', __('Quiz Ended At'));

        return $form;
    }
}
