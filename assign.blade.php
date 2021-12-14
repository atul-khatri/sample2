@extends('layouts.app')

@section('content')
<div class="content-wrapper">
   <div class="page-header clearfix mb-3">
      <h3 class="page-title">@lang('en.TASK_PLURAL')</h3>
</div>

  <div class="alert alert-success alert-dismissible" style="display: none;" id="mainmsg"></div>
  @if(session()->has('message'))
  <div class="alert alert-success alert-dismissible" role="alert">
   {{ session()->get('message') }}
   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
<div class="row">
  <div class="col-md-6">
  <div class="card">
  <div class="card-header">Employees</div>
<div class="card-body">
    
     <div class="form-group row">
      <label class="col-sm-5 col-form-label" for="@lang('en.SEARCH')">@lang('en.SEARCH') @lang('en.EMP_NAME') by @lang('en.NAME')</label>
	  <div class="col-sm-7">
      <input type="text" placeholder="@lang('en.SEARCH') @lang('en.EMP_NAME')" onkeyup="filter_assign(this.value,'emp');"  class="form-control">
    </div>
	</div>
	<hr/>
	<div class="card">
	<div class="card-body">
    <div id="filter_emp">
    @forelse($employees as $employee)
    <div data-empid="{{$employee->id}}" data-accept="task" data-suretitile="@lang('en.ARE_YOU_SURE')" data-ctext="@lang('en.ASSIGN_TASK_CONFIRM_TEXT')" data-taskassigned="@lang('en.TASK_ASSIGNED')" data-stayconfirm="@lang('en.STAY_COMFIRM')" data-cbutton="@lang('en.CANCEL_BTN')"  data-acceptbutton="@lang('en.ACCEPT_BTN')"  data-sorry="@lang('en.SORRY')"  data-alreadyassign="@lang('en.TASK_ALREADY_ASSIGN')"  class="draggable emp mb-2 textbreak">
    {!! readMoreHelper($employee->name,50) !!} - <small>{!! readMoreHelper($employee->userid,50) !!}</small>  <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>
	 <span class="tooltiptext">Drag and drop on a task to Assign Task</span>
   </div>
    @endforeach  
    
   
    <div class="no-items-found text-center mt-1" id="emp_notfound" style="display:none">
        <i class="icon-magnifier fa-3x text-muted"></i>
        <p class="mb-0 mt-3"><strong>@lang('en.NOT_FOUND_TITLE')</strong></p>
      </div>
   
    </div> 
	</div>
	</div>
 </div>
 </div>
 </div>
 
 <div class="col-md-6">
  <div class="card">
  <div class="card-header">Tasks</div>
<div class="card-body">
    <div class="form-group row">
      <label class="col-sm-5 col-form-label" for="@lang('en.SEARCH')">@lang('en.SEARCH') @lang('en.TASK_PLURAL') by @lang('en.NAME')</label>
	  <div class="col-sm-7">
      <input type="text" placeholder="@lang('en.SEARCH') @lang('en.TASK_PLURAL')" onkeyup="filter_assign(this.value,'task');"  class="form-control">
    </div>
	</div>
	<hr/>
   @forelse($tasks as $task)
   
   <div data-taskid="{{$task->id}}" data-accept="emp" class=" droppable task mb-2 myemp">
    <h2> {{$task->name}}</h2>
    @php
     $wd = gettasksDate($task->id)? ' , ' .gettasksDate($task->id):'';
       @endphp  
    <small>@lang('en.TASK_SINGULAR') @lang('en.DATE'):  {!! $task->working_dates.$wd !!}</small>
	<div class="card">
	<div class="card-body">
     <ul>
       @forelse($task->employees as $employee)
       <li>
         <span class="textbreak">{!! readMoreHelper($employee->employee->name,50) !!}</span> 
         @can('delete-assign-task')
          <a href="javascript:void(0)" onclick="removeasignedtask({{$employee->id}},'@lang('en.ARE_YOU_SURE')','@lang('en.ASSIGNED_TASK_REMOVE')','@lang('en.SUCCESS')','@lang('en.EMPLOYEE_REMOVE')','@lang('en.SORRY')','@lang('en.SOME_ERROR')');" class="text-danger float-right text-right"><i class="fa fa-times-circle" aria-hidden="true"></i><span class="tooltiptext">Remove Employee</span>
		  
		  </a>
          @endcan
       </li>
       @empty
       @endforelse
     </ul>
   </div>
   </div>
   </div>
   @empty
   @endforelse
   <div class="no-items-found text-center mt-1" id="task_notfound" style="display:none">
        <i class="icon-magnifier fa-3x text-muted"></i>
        <p class="mb-0 mt-3"><strong>@lang('en.NOT_FOUND_TITLE')</strong></p>
      </div>
 </div>

</div>


<div class='row mt-2'>
    <div class="col-md-12">
         <button type="button" onclick="window.location.href='/assign-task'" class="btn btn-default btn-fw">@lang('en.BTN_BACK')</button>
       </div>
     </div>
     </div>
<div style="display: none;" id="datetimecontent">
 <div class="col-md-6">
  <input type="hidden" id="token"  value="{{ csrf_token() }}">
  <div class="form-group {{ $errors->has('start_date') ? 'has-error' : '' }}">
   <label for="start_date">@lang('en.START_DATE'): <span class="text-danger">*</span></label> 
   <input type="text" name="start_date" class="form-control" id="date_timepicker_start" data-validation="required">    
   <span class="text-danger">{{ $errors->first('start_date') }}</span>
 </div> 
</div>
<div class="col-md-6">
  <div class="form-group {{ $errors->has('end_date') ? 'has-error' : '' }}">
   <label for="end_date">@lang('en.END_DATE'): <span class="text-danger">*</span></label>
   <input type="text" name="end_date" id="date_timepicker_end" class="form-control" data-validation="required">   
   <span class="text-danger">{{ $errors->first('end_date') }}</span>
 </div> 
</div>
</div>


<div id="changeemployee" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">@lang('en.CHANGE') @lang('en.MEMBER_SINGULAR')</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
       <form id="changeempform" method="post">
        @csrf
        <input type="hidden" name="taskid" id="taskidinput">
        <div class="form-group">
         <label>@lang('en.SELECT') @lang('en.MEMBER_SINGULAR')</label>
         <select class="form-control" name="employee_id">
          @forelse($allemp as $key=>$emp)
          <option value="{{$key}}">{{$emp}}</option>
          @empty
          @endforelse
        </select>
        <span class="has-error" style="color: red;" id="employee_id"></span>
      </div>
    </form>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-primary" onclick="changeemp(this,true);">@lang('en.CHANGE')</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('en.CLOSE')</button>
  </div>
</div>

</div>
</div>

@endsection
