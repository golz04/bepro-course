<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseModuleContent;
use App\Models\CourseCategory;
use App\Models\CourseBenefit;
use App\Models\Enroll;
use App\Models\CourseReview;

class EmployeeCourseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Employee']);
    }

    private $param;
    public function index()
    {
        try {
            // $this->param['getCourse'] = \DB::table('courses')
            //                             ->select('courses.*', 'course_categories.category_name')
            //                             ->join('course_categories', 'courses.category_id', 'course_categories.id')
            //                             ->get();
            // $this->param['getCourse'] = \DB::table('courses')
            //                             ->select('courses.*', 'course_categories.category_name')
            //                             ->join('course_categories', 'courses.category_id', 'course_categories.id')
            //                             ->leftJoin('enrolls', 'courses.id', 'enrolls.course_id')
            //                             ->leftJoin('users', 'users.id', 'enrolls.user_id')
            //                             ->where('enrolls.id', NULL)
            //                             ->where('enrolls.user_id', '=', \Auth::user()->id)
            //                             ->get();
            $this->param['getCourse'] = \DB::table('courses')
                                            ->select('courses.*', 'course_categories.category_name')
                                            ->join('course_categories', 'courses.category_id', 'course_categories.id')
                                            ->join('enrolls', 'courses.id', 'enrolls.course_id')
                                            ->join('users', 'enrolls.user_id', 'users.id')
                                            ->where('users.id', \Auth::user()->id)
                                            ->get();
            $courseID = array_column($this->param['getCourse']->toArray(), 'id');
            $this->param['getCoursed'] = \DB::table('courses')
                                        ->select('courses.*', 'course_categories.category_name')
                                        ->join('course_categories', 'courses.category_id', 'course_categories.id')
                                        ->whereNotIn('courses.id', $courseID)
                                        ->get();
            // dd($courseID);
            // $this->param['getCoursed'] = \DB::table('courses')
            //                                 ->select('courses.*')
            //                                 ->join('enrolls', 'courses.id', 'enrolls.course_id')
            //                                 ->leftJoin('users', 'enrolls.user_id', 'users.id')
            //                                 ->where('users.id', \Auth::user()->id)
            //                                 ->where('courses.id', NULL)
            //                                 ->get();
            // $this->param['getCoursed'] = \DB::table('users')
            //                             ->select('courses.*')
            //                             ->join('enrolls', 'users.id', 'enrolls.user_id')
            //                             ->join('courses', 'enrolls.course_id', 'courses.id')
            //                             ->where('users.id', \Auth::user()->id)
            //                             ->where('enrolls.course_id', NULL)
            //                             ->where('enrolls.user_id', NULL)
            //                             ->get();

            $this->param['getcBenefit'] = CourseBenefit::all();
            $this->param['getEnroll'] = Enroll::where('user_id', \Auth::user()->id)->get();
            
            return view('employee.pages.course.list', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function myCourse()
    {
        try{
            $this->param['getMyCourse'] = \DB::table('enrolls')
                            ->select('courses.*')
                            ->join('courses', 'courses.id', 'enrolls.course_id')
                            ->where('enrolls.user_id', \Auth::user()->id)
                            ->where('enrolls.status', 'active')
                            ->get();

            return view('employee.pages.my-course.list', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function myCourseDetail($slug)
    {
        try{
            $this->param['getCourse'] = Course::where('slug', $slug)
                                                ->first(); //getCourse
            $getCourseID = $this->param['getCourse']->id; //getCourseID
            $this->param['getCourseModule'] = CourseModule::where('course_id', $getCourseID)
                                                            ->orderBy('ordinal', 'ASC')
                                                            ->get(); //getCourseModule
            $this->param['getCourseModuleContent'] = CourseModuleContent::orderBy('course_module_id', 'ASC')
                                                                        ->orderBy('ordinal', 'ASC')
                                                                        ->get(); //getCourseModuleContent
            $this->param['getBenefit'] = CourseBenefit::where('course_id', $getCourseID)
                                                        ->orderBy('id', 'ASC')
                                                        ->get(); //getBenefit

            return view('employee.pages.my-course.detail', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function rateCourse($slug)
    {
        try{
            $this->param['getCourse'] = Course::where('slug', $slug)
                                                ->first(); //getCourse
            $getCourseID = $this->param['getCourse']->id; //getCourseID
            $this->param['getCourseModule'] = CourseModule::where('course_id', $getCourseID)
                                                            ->orderBy('ordinal', 'ASC')
                                                            ->get(); //getCourseModule
            $this->param['getCourseModuleContent'] = CourseModuleContent::orderBy('course_module_id', 'ASC')
                                                                        ->orderBy('ordinal', 'ASC')
                                                                        ->get(); //getCourseModuleContent
            $this->param['getBenefit'] = CourseBenefit::where('course_id', $getCourseID)
                                                        ->orderBy('id', 'ASC')
                                                        ->get(); //getBenefit

            return view('employee.pages.my-course.rating-feedback', $this->param);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }

    public function rateCourseSend($slug, Request $request)
    {
        $this->validate($request, 
            [
                'feedback' => 'required|min:4',
                'rating' => 'required',
            ],
            [
                'required' => ':attribute harus diisi.',
                'feedback.min' => 'Minimal panjang karakter 4.',
            ],
            [
                'feedback' => 'Pesan / feedback',
                'rating' => 'Rating',
            ],
        );
        try{
            $this->param['getCourse'] = Course::where('slug', $slug)
                                                ->first(); //getCourse
            $getCourseID = $this->param['getCourse']->id; //getCourseID
            $getUserLog = \Auth::user()->id;

            $cReview = new CourseReview();
            $cReview->user_id = $getUserLog;
            $cReview->course_id = $getCourseID;
            $cReview->rating = $request->rating;
            $cReview->feedback = $request->feedback;
            $cReview->save();

            
            return redirect()->back()->withStatus('Berhasil Memberi Rating dan Feedback.');
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }
}
