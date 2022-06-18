<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminReportQuizController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:Admin']);
    }
    
    private $param;
    public function index(){
        try {
            // $this->param['getDivision'] = Division::all(); 
            // return view('admin.pages.division.list', $this->param);

            return view('admin.pages.report-quiz.list-report-quiz');
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->withError('Terjadi kesalahan pada database', $e->getMessage());
        }
    }
}
