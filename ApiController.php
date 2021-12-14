<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Request\CheckSheetRequest;
use Illuminate\Http\Request;
use App\Model\CheckSheet;
use App\Model\CheckListHeading;
use App\Model\CheckList;
use App\Model\Task;
use App\Model\TaskAssign;
use App\Model\TaskDetail;
use Lang;
use Auth;
use Storage;
use App\Model\Employee;
use App\Model\User;
use Illuminate\Support\Facades\Validator;
use App\Model\TaskProofDetail; 
use App\Model\CheckListImage;

class ApiController extends Controller
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

    public function getTaskList($filter_by=0)
    {
        //

      $user = Auth::guard("api")->user();
      $msg ='';
      if($filter_by == 5){
        $task['data'] =[];
        $ta=[];
        $t = $this->task->with(['client','client.address','checksheet','checksheet.heading','checksheet.heading.checklist','checksheet.heading.checklistImage','checksheet.heading.checklist.remarks','assign'=>function($q){
          $q->orWhere("is_accept",2);
        }])->whereHas("decline")->where(['company_id'=>$user->company_id,"status"=>"Active"])->orderBy('id','asc')->get();
        foreach ($t as $model) {
            $ta[] = $model->toArray();
        }
        $tasks['data']=$ta;
        
        foreach($tasks['data'] as $k=>$t){
          $tasks['data'][$k]['client']['address']=[];
          foreach($t['client']['address'] as $ad){ 
            if($ad['id'] == $t['sub_client_deptid']){
              $tasks['data'][$k]['client']['address']=$ad;
            }
          }
        }
        $task = $tasks;
        $msg='Declined task list';
      }else{
      if($user->user_type==2){
        $task = $this->task->with(['client','client.address','checksheet','checksheet.heading','checksheet.heading.checklist','checksheet.heading.checklistImage','checksheet.heading.checklist.remarks'])->whereHas("assign");
        $task= $this->makeTaskFilter($task,$filter_by,$user);
        $msg='Supervisor task list';
      }elseif($user->user_type==1){
       $task = $this->task->with(['client','client.address','checksheet','checksheet.heading','checksheet.heading.checklist','checksheet.heading.checklistImage','checksheet.heading.checklist.remarks'])->whereHas("assign",function($q) use($user){
        $q->where("employee_id",$user->id);
      });
       $task= $this->makeTaskFilter($task,$filter_by,$user);
       //array_walk_recursive($task,function(&$item){$item =($item==null?strval($item):$item);});
       $msg='Employee task list';
     }
   }
  
          for ($i=0; $i<count($task['data']); $i++) {
                
            $task['data'][$i]['client']['range'] =  $task['data'][$i]['client']['address']['range'];
          }


     return respondWithSuccess($task,"success", $msg);
   }

   private function makeTaskFilter($task,$filter,$user)
   {
        /*
        1= complete
        2= pending
        3= upcomming
        4= overdue
        5= Decline
        */
        $tasks=[];
        $date=date('Y-m-d');
        switch ($filter) {
          case 1: //complete
          {
            $ta=[];
            //$tasks['data'] = [["name"=>"Deepak"],["name"=>"hi"]];
            $t = $task->where(['company_id'=>$user->company_id,"status"=>"Active"])->orderBy('updated_at','desc')->get();
            foreach ($t as $model) {
              if($model->is_close==1){
                $ta[] = $model->toArray();
              }
            }
            $tasks['data']=$ta;
            break;
          }

          case 2: //pending
          {
            $ta=[];
            //modify on 09-04-2021
           // $t = $task->where(['company_id'=>$user->company_id,"status"=>"Active"])->whereRaw("DATE_FORMAT(start_date,'%Y-%m-%d')<='".$date."' AND DATE_FORMAT(end_date,'%Y-%m-%d')>='".$date."'")->orderBy('id','asc')->get();

            $t = $task->where(['company_id'=>$user->company_id,"status"=>"Active"])->whereRaw("DATE_FORMAT(start_date,'%Y-%m-%d')='".$date."'")->orderBy('id','asc')->get();
            foreach ($t as $model) {
              if($model->is_close==0){
                $ta[] = $model->toArray();
              }
            }
            $tasks['data']=$ta;
            break;
          }

          case 3: //upcomming
          {
            $ta=[];
            $t = $task->where(['company_id'=>$user->company_id,"status"=>"Active"])->whereRaw("DATE_FORMAT(start_date,'%Y-%m-%d')>'".$date."'")->orderBy('id','asc')->get();
            foreach ($t as $model) {
              if($model->is_close==0){
                $ta[] = $model->toArray();
              }
            }
            $tasks['data']=$ta;
            break;
          }

          case 4: //overdue
          {
            $ta=[];
            $t = $task->where(['company_id'=>$user->company_id,"status"=>"Active"])->whereRaw("DATE_FORMAT(start_date,'%Y-%m-%d')<'".$date."'")->orderBy('id','asc')->get();
            foreach ($t as $model) {
              if($model->is_close==0){
                $ta[] = $model->toArray();
              }
            }
            $tasks['data']=$ta;
            break;
          }

          default:{
          $tasks = $task->where(["company_id"=>$user->company_id])->orderBy('id','asc')->paginate($this->perpage);
          break;
        }
      }
      foreach($tasks['data'] as $k=>$t){
        $tasks['data'][$k]['client']['address']=[];
        foreach($t['client']['address'] as $ad){ 
          if($ad['id'] == $t['sub_client_deptid']){
            $tasks['data'][$k]['client']['address']=$ad;
          }
        }
      }
      return $tasks;
 }

}
