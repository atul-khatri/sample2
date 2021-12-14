<?php

namespace App\Model;
use App\Http\Helpers\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Model\Employee;
use App\Model\User;
class Task extends Model
{
	use SoftDeletes;
    protected $guarded = ['id'];
    protected $fillable = 
    [
    	
    ];

    protected $appends =['is_complete','is_accept'];

    public function checksheet()
    {
        return $this->hasOne(CheckSheet::class,'id','check_sheet_id');
    }

    public function client()
    {
       return $this->hasOne(User::class,'id','client_id');
   }

   public function assign()
   {
    if(\Auth::guard('api')->check())
    {
        if(\Auth::guard('api')->user()->user_type==1){
           return $this->hasOne(TaskAssign::class,'task_id','id')->whereIn("is_accept",['0','1'])->where("employee_id",\Auth::guard('api')->user()->id);
       }else{
         return $this->hasOne(TaskAssign::class,'task_id','id')->whereIn("is_accept",['0','1']); 
     }
 }else{
    return $this->hasOne(TaskAssign::class,'task_id','id')->whereIn("is_accept",['0','1']);
}
}

public function decline()
   {
    $us = \Auth::guard('api')->user();
   
    if(\Auth::guard('api')->check())
    {
       
        if(\Auth::guard('api')->user()->user_type==1){
           
           return $this->hasOne(TaskAssign::class,'task_id','id')->whereIn("is_accept",['2'])->where("employee_id",\Auth::guard('api')->user()->id);
       }else{
       
         return $this->hasOne(TaskAssign::class,'task_id','id')->whereIn("is_accept",['2']); 
     }
 }else{
    return $this->hasOne(TaskAssign::class,'task_id','id')->whereIn("is_accept",['2']);
}
}


public function getFinalStatusAttribute($value='')
{
    $date =date('Y-m-d');
    $start_date=date('Y-m-d',strtotime($this->start_date));
    $end_date=date('Y-m-d',strtotime($this->end_date));
    if($this->is_close==1)
    {
        return 'complete';
    }
    elseif($start_date<=$date && $end_date>=$date){
        return 'pending';
    }
    elseif($start_date<$date && $end_date<$date){
        return 'overdue';
    }
    elseif($start_date>$date && $end_date>$date){
        return 'upcomming';
    }else{
        return '';
    }

}


}