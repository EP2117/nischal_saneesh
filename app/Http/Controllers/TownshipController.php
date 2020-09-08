<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Township;
use App\Imports\TownshipImport;
use Maatwebsite\Excel\Facades\Excel;

class TownshipController extends Controller
{
    public function index()
    {
        $data = Township::orderBy('township_name', 'ASC')->get();
        return response(compact('data'), 200);
    }

	public function townshipByState($id)
    {
        $data = Township::orderBy('township_name', 'DESC')
                ->where('state_id',$id)
                ->get();
        return response(compact('data'), 200);
    }

    public function import() 
    {
        $path1 = request()->file('file')->store('temp'); 
        $path=storage_path('app').'/'.$path1; 

        $import = new TownshipImport();
        Excel::import($import,$path);
        return ["message" => "success"];
    }
}
