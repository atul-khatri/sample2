<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Request\CheckSheetRequest;
use App\Request\TaskRequest;
use Illuminate\Http\Request;
use App\Model\CheckSheet;
use App\Model\CheckListHeading;
use App\Model\CheckList;
use App\Model\Task;
use App\Model\TaskAssign;
use Lang;
use Auth;
use Addjscss;
use DB;
use App\Model\Employee;
use App\Model\User;
use App\Model\SubClient;
use App\Model\SubClientDepartment;
use Illuminate\Support\Facades\Validator;
use DateTime;
use App\Model\CheckListImage;
class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $perpage=10;
    private $checksheet;
    private $task;
    private $assign;
    public function __construct(CheckSheet $checksheet, Task $task, TaskAssign $assign)
    {
        $this->checksheet = $checksheet;
        $this->task = $task;
        $this->assign = $assign;
    }

    public function index()
    {
       
        $tasks=$this->task->whereHas('client')->orderBy("id","desc")->where(["company_id"=>Auth::user()->company_id,"is_quick"=>0,"parent"=>0])->paginate($this->perpage);
        return view("task.index",compact("tasks"));
    }

   

    public function create()
    {
        return view("task.create");
    }

    public function createFromDepartment(Request $request)
    {
        $dept_id=$request->dept_id;
        $result = DB::select('select id,client_id,sub_client_id from sub_client_departments where id='.$dept_id);

        $subclientids=SubClient::where(["client_id"=>$result[0]->client_id])->get(["id","name"]);
        $subclientDeptid=SubClientDepartment::where(["sub_client_id"=>$result[0]->sub_client_id,"status"=>"Active"])->get(["id","name"]);


        $employees =  Employee::where(["company_id"=>Auth::user()->company_id,"status"=>"Active"])->where("user_type",1)->orderBy("name","asc")->get(["id","name"]);

        $clientId=$result[0]->client_id;
        $checksheet = $this->checksheet->where(["client_id"=>$clientId,"checksheet_type"=>"2","status"=>"Active"])->whereDoesntHave('task')->get();
       
        
        return view("task.createfromdept",compact("employees","result","subclientids","subclientDeptid","checksheet"));
    }

   
    public function store(Request $request)
    {
        $indata = $request->input();
       
        if($request->frequency_data)
        {
            $indata['frequency_data'] = json_encode($request->frequency_data);
           
        }
       // echo $request->frequency;
       $workingdates= $this->workingDays($request->start_date, $request->end_date,$request->frequency_data,$request->frequency,$request->selected_days);

       $workingdates = explode(',',$workingdates);

      
       if (count(array_filter($workingdates)) != 0) {
     
           sort($workingdates);
           $indata['working_dates'] = $workingdates[0];

         $indata['start_date'] = $workingdates[0].' '.date('H:i', strtotime($request->start_date)); 
           if ($request->frequency=='monthly') {
               $indata['start_date'] = $workingdates[0];
               $indata['end_date'] = $workingdates[0];
               $indata['select_days'] = json_encode($request->selected_days);
           }
       } 
        else {
          
       $indata['start_date'] = $request->start_date;
        $indata['end_date'] = $request->end_date;

        if ($request->frequency=='monthly') {
            $indata['select_days'] = json_encode($request->selected_days);
        }

       }
        
    //    $indata['end_date'] = $workingdates[0].' '.date('h:i',strtotime($request->end_date));
     
       $ttasks = $this->task->fill($indata)->save();
       array_shift($workingdates);
       
       if (!empty($workingdates)) {
        
           foreach ($workingdates as $workd) {

               $indata['parent'] =  $this->task->id;
               $indata['working_dates'] = $workd;
               $indata['start_date'] = $workd.' '.date('H:i',strtotime($request->start_date));
               $indata['end_date'] = $workd.' '.date('H:i',strtotime($request->end_date));
               if($request->frequency=='monthly'){
                $indata['start_date'] = $workd;
                $indata['end_date'] = $workd;
                $indata['select_days'] = json_encode($request->selected_days);
               }
              
               $csData =CheckSheet::where("id",$indata['check_sheet_id'])->first();
                $newcsData['name'] = $csData->name;
                $newcsData['checklist_no'] = $csData->checklist_no;
                $newcsData['comment'] = $csData->comment;
                $newcsData['company_id'] = $csData->company_id;
                $newcsData['industry_id'] = $csData->industry_id;
                $newcsData['checksheet_type'] = 4;
                $newcsData['client_id'] = $csData->client_id;
                $newcsData['client_frequency'] = $csData->client_frequency;
                $newcsData['days'] = $csData->days;
                $newcsData['start_date'] = $csData->start_date;
                $newcsData['end_date'] = $csData->end_date;
                $newcsData['status'] = $csData->status;
               
              
                $newCs = New CheckSheet();
                $newCs->fill($newcsData);
                $newCs->save();

                $cshData =CheckListHeading::where("check_sheet_id",$indata['check_sheet_id'])->get();
                
                foreach($cshData as $head){

                    $newHeadData['name'] = $head->name;
                    $newHeadData['tool'] = $head->tool;
                    $newHeadData['check_sheet_id'] = $newCs->id;
                    $newHeadData['status'] = $head->status;
                    $heading = New CheckListHeading();
                    $heading->fill($newHeadData);
                    $heading->save();

                    $clData =CheckList::where("check_list_heading_id",$head['id'])->get();
                   if ($clData) {
                       foreach ($clData as $cl) {
                           $newclData['name'] = $cl->name;
                           $newclData['frequency'] = $cl->frequency;
                           $newclData['check_list_heading_id'] = $heading->id;
                           $newclData['check_sheet_id'] = $newCs->id;
                           $newclData['proof'] = $cl->proof;
                           $newclData['status'] = $cl->status;
                           $cld = new CheckList();
                           $cld->fill($newclData);
                           $cld->save();
                       }
                   }

                   $imgData =CheckListImage::where("check_list_heading_id",$head['id'])->get();
                   if ($imgData) {
                       foreach ($imgData as $img) {
                           $newimgData['image'] = $img->image;
                           $newimgData['check_list_heading_id'] = $heading->id;
                           $newimgData['check_sheet_id'] = $newCs->id;
                           $newimgData['status'] = $img->status;
                           $imdata = new CheckListImage();
                           $imdata->fill($newimgData);
                           $imdata->save();
                       }
                   }

                }
                $indata['check_sheet_id'] = $newCs->id;
               $tasks = New Task();
               $tasks->fill($indata);
               $tasks->save();
           }
       }
       

       // $this->task->fill($indata)->save();
        return redirect("/tasks")->with("message",__('en.CREATE_MSG'));
    }
    
    
    public function assignTaskList()
    {
       $employees =  Employee::where(["company_id"=>Auth::user()->company_id,"status"=>"Active"])->orderBy("name","asc")->paginate($this->perpage);
       return view("task.assign-list",compact("employees"));
   }

   public function assignTask()
   {
    $date = date("Y-m-d");
       $employees =  Employee::where(["company_id"=>Auth::user()->company_id,"status"=>"Active"])->where("user_type",1)->orderBy("id","desc")->get(["id","name","userid"]);

       //comment on 31-03-2021
      // $tasks=$this->task->orderBy("id","desc")->where(["company_id"=>Auth::user()->company_id,"is_close"=>"0","parent"=>0])->whereRaw("DATE_FORMAT(end_date,'%Y-%m-%d')>='".$date."'")->where("status","Active")->get(["id","name","working_dates"]);

       $tasks=$this->task->orderBy("id","desc")->where(["company_id"=>Auth::user()->company_id,"is_close"=>"0","parent"=>0])->where("status","Active")->get(["id","name","working_dates"]);
       $allemp =   Employee::where(["status"=>"Active"])->get()->pluck("name","id","userid")->all();   
       return view("task.assign",compact("employees","tasks","allemp"));
   }

   public function postAssignTask(Request $request)
   {
       //dd($request);
    $chk = $this->assign->where(["employee_id"=>$request->employee_id, "task_id"=>$request->task_id])->whereIn("is_accept",['0','1'])->count();
    if($chk==0){
        $assindata = Task::where(["parent"=>$request->task_id])->get();
        if (!empty($assindata)) {
            foreach ($assindata as $adata) {

                $indata['employee_id'] = $request->employee_id;
                $indata['task_id'] = $adata['id'];
                $tassign = new TaskAssign();
                $tassign->fill($indata);
                $tassign->save();
            }
        }
        $task = Task::find($request->task_id);
        $accept = $task->is_quick==1?1:0;
        $request['is_accept'] = $accept;
        $this->assign->fill($request->input())->save();
        
        return 1;
    }else{
        return 0;
    }
}


}
