<?php

class AttendanceController extends Zend_Controller_Action {

    protected $arrSettings, $controller;
    private  $bioEmpId;

    private function isWeekend($date) {
        if (date("l", mktime(0, 0, 0, date('m', strtotime($date)), date('d', strtotime($date)), date('Y', strtotime($date)))) == 'Saturday'
                || date("l", mktime(0, 0, 0, date('m', strtotime($date)), date('d', strtotime($date)), date('Y', strtotime($date)))) == 'Sunday') {
            return true;
        } else {
            return false;
        }
    }

    protected function isAttendanceMarked($date, $employees) {
        $attendanceModel = new Application_Model_Attendance();
        foreach ($employees as $emp) {
            $attendanceRow = $attendanceModel->fetchRow("time_in like '%" . $date . "%' and employee_id='" . $emp . "'");
            if ($attendanceRow)
                return true;
        }
        return false;
    }
    protected function getTimeDiff($dtime,$atime)
    {
        $nextDay=$dtime>$atime?1:0;
        $dep=explode(':',$dtime);
        $arr=explode(':',$atime);


        $diff=abs(mktime($dep[0],$dep[1],0,date('n'),date('j'),date('y'))-mktime($arr[0],$arr[1],0,date('n'),date('j')+$nextDay,date('y')));

        //Hour

        $hours=floor($diff/(60*60));

        //Minute 

        $mins=floor(($diff-($hours*60*60))/(60));

        //Second

        $secs=floor(($diff-(($hours*60*60)+($mins*60))));

        if(strlen($hours)<2)
        {
            $hours="0".$hours;
        }

        if(strlen($mins)<2)
        {
            $mins="0".$mins;
        }

        if(strlen($secs)<2)
        {
            $secs="0".$secs;
        }

        return $hours.':'.$mins.':'.$secs;

    }

    protected function markAttendance($empId, $formData) { 

        $reasonModel = new Application_Model_LeaveReason();
        $reasonOptionGroup = $reasonModel->getOptionGroup();

        $attendanceModel = new Application_Model_Attendance();
        $attendanceRow = $attendanceModel->fetchRow("employee_id='" . $empId . "' and
                    time_in like '%" . $formData['date'] . "%'");
        
        $storage = new Zend_Auth_Storage_Session();
        $loginUserId = $storage->read()->id;

        if(empty($loginUserId) || is_null($loginUserId))
            $loginUserId = 0;

        if ($formData['time_in'] != "") {
            if (!$attendanceRow){ 
                $todaydate = date('Y-m-d h:i:s',  time());
                $attendanceRow = $attendanceModel->createRow();                
                $attendanceRow->created_by = $loginUserId;
                $attendanceRow->created_date= $todaydate;
            }else{ //echo "<pre>";
                //print_r($attendanceRow);die;
                $todaydate = date('Y-m-d h:i:s',  time());//echo $todaydate;die;
                $attendanceRow->modified_by = $loginUserId;
                $attendanceRow->modified_date = $todaydate;
                $previousReason = $attendanceRow->reason_id;                
            }

            $attendanceRow->employee_id = $empId;
            // $attendanceRow->punch_type = $formData['punch_type'];
            //$attendanceRow->date = date('Y-m-d', strtotime($formData['date']));
            $attendanceRow->time_in = date('Y-m-d', strtotime($formData['date'])) . ' ' . date('H:i', strtotime($formData['time_in']));
             
            if (isset($formData['time_out']) && $formData['time_out'] != '')
                $attendanceRow->time_out = date('Y-m-d', strtotime($formData['newdate'])) . ' ' . date('H:i', strtotime($formData['time_out']));
            else
               $attendanceRow->time_out = null;
            
            $attendanceRow->remarks = $formData['remarks'];
            $attendanceRow->reason_id = $formData['reason'];
            if (isset($formData['hrs']) && $formData['hrs'] != '')
                $attendanceRow->hrs = $formData['hrs'];
            else
                $attendanceRow->hrs = null;
            
            if (isset($formData['office_hrs']) && $formData['office_hrs'] != '')
                $attendanceRow->office_hrs = $formData['office_hrs'];
            else
                $attendanceRow->office_hrs = "00:00:00";
            
            
            /*if (($formData['reason'] != 0 && $formData['reason'] != NULL)
                    || (isset($attendanceRow->reason_id) && @$reasonOptionGroup[$previousReason] == "Other")) {
                $attendanceRow->attendance_late = 'yes';
            } else {
                $attendanceRow->attendance_late = 'no';
            }*/
            
            $hours = strtotime($formData['time_out']) - strtotime($formData['time_in']);
            $hrs = $hours / 3600;
            $sec = $hours % 3600;
            $min = intval($sec / 60);

            //echo "<pre>";                        print_r($attendanceRow);die;
            $attendanceRow->save();
           
            if (isset($tokenRow))
                $tokenRow->save();
             
        }
    }

   private function getHolidayFlag($date) {
        $holidayModel = new Application_Model_Holidays();
        $holidayFlag = false;
        $holidayRow = $holidayModel->fetchRow("(date <= '" . $date . "' and to_date >= '" . $date . "')");
        if ($holidayRow) {
            return $holidayFlag = true;
        } else {
            return $holidayFlag;
        }
    }

    private function getEmployeeLeaveFlag($employee_id, $date) {
        $leaveFlag = false;
        $leaveModel = new Application_Model_Leave();
        $leaveRow = $leaveModel->fetchRow("employee_id='" . $employee_id . "' and (from_date <= '" . $date . "' and to_date >= '" . $date . "')");
        return $leaveRow;
        /*if ($leaveRow) {
            return $leaveFlag = true;
        } else {
            return $leaveFlag;
        }*/
    }


    /**
     * ROLE
     * @spreadsheet Spreadsheet View
     */
    public function attendanceMachineLogAction() {
        $this->view->inc_suggest = true;
        $this->view->inc_autoSuggest = "employeeNumberSelect";

        $empModel = new Application_Model_Employees();
        $empArray = $empModel->getTheEmployees('number');
        $this->view->employeeList = $empArray;


        if ($this->_request->isPost()) {

            $formData = $this->_request->getPost();
            if (isset($formData['bsubmit']) && $formData['bsubmit'] == "Search") {
                $fromdate = $formData['fromdate'];
                $todate = $formData['todate'];

                $empNum = $formData["employeeNumberSelect"];

            }


            /* Button for reports is pressed */


            $this->view->fromdate = $fromdate;
            $this->view->todate =$todate;
            $this->view->empNum = $empNum;

            $modelObjAttendanceMachlog = new Application_Model_AttendanceMachineLog();
            $rowAttendaceMac1 = $modelObjAttendanceMachlog->attendanceMachineLog($empNum, $fromdate, $todate);

            $calContent = "";
            $sr = 1;
            foreach ($rowAttendaceMac1 as $er) {
//                if (isset($er["d.file_name"])) {
//                    $filePath = "/uploads/Picture/thumb__" . $er["d.file_name"];
//                } else {
//                    $filePath = "/images/emp-picture.jpg";
//                }
                $calContent .='<tr>
                        <td>' . $sr . '</td>
                        <td>' . $er['employee_id'] . '</td>
                        <td>' . $er['e.name'] . '</td>';
                $calContent .= '<td>' . $er['date_time'] . '</td>';
                if($er['status']==0)
					$status = "Check In";
                else if($er['status']==1)
                    $status = "Check Out";
                else if($er['status']==2)
                    $status = "Time Out";
                else if($er['status']==3)
                    $status = "Time In";        
                $calContent .= '<td>' . $status . '</td></tr>';
                $sr++;
            }
            $this->view->calContent = $calContent;
        }
    }
    /**
     * ROLE
     * @spreadsheet Spreadsheet View
     */
    public function spreadsheetAction() {

//Employee List
        $this->view->inc_suggest = true;
        $this->view->inc_autoSuggest = "selectEmployee,selectEmployee1";
        $employeeModel = new Application_Model_Employees();
        $acl2Plugin = new Application_Controller_Plugin_Acl2();
        $employeeList = $acl2Plugin->getEmployeeList();
        if ($employeeList == '') { // admin, administrator, hr and Can view All Employees
            $employeeIn = '';
        } else {
            $employeeIn = 'id IN (' . $employeeList . ') and';
        }
        $employeeSelect = $employeeModel->select()
                ->order("name asc");
				
				/*
					                ->where($employeeIn . " current_job_status NOT IN (
                                            'Resigned', 'Terminated'
                                         )")
					///Token approval for resigned / terminated
				*/
        $employeeRows = $employeeModel->fetchAll($employeeSelect);
        $this->view->employees = $employeeRows;
        
        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');

        $employeeList = $Plugin->getEmployeeList();
        if ($employeeList == '') { // admin, administrator, hr and Can view All Employees
            $employeeIn = '';
        } else {
            $employeeIn = 'e.id IN (' . $employeeList . ') and';
            $this->view->hideForm = 'true';
        }

        
        /* enabling css for filter selection objects */
        
        /* Setting values for filter object by default */
        $this->view->date = date('Y-m-d');
        $this->view->empId = 'all';
        
        $leaveReasonModel = new Application_Model_LeaveReason();
        $leaveReasonRows = $leaveReasonModel->fetchAll('status = "Active"');
        $optionGroups = array('Leave Status' => array(),
            'Other' => array());
        foreach ($leaveReasonRows as $reasonRow) {
            $optionGroups[$reasonRow['option_group']][$reasonRow->id] = $reasonRow->reason_name;
        }

        /* Fetching all employees list for employee select object */
        $employeeModel = new Application_Model_Employees();


        $employeeSelect = $employeeModel->jobStatusSelect('current', $employeeIn);
        $employeeRows = $employeeModel->fetchAll($employeeSelect);
        $this->view->employeeRows = $employeeRows;

        /* Fetching all locations list for employee select object */
        $locationsModel = new Application_Model_Location();
        $locSelect = $locationsModel->select()->order("name asc");
        $locationRecords = $locationsModel->fetchAll($locSelect);
        $locationsList = $locationsModel->getTheLocations('id');

        $this->view->locationRecords = $locationRecords;

        /* Fetching all designations list for employee select object */
        $designationModel = new Application_Model_Designation();
        $designationRecords = $designationModel->getTheDesignations('id');
        $designationRecords[''] = 'All Designations';

        $this->view->designationRecords = $designationRecords;

        /* By default show all locations selected in filter */
        $locVal = '';
        $locVal .= "<li class='as-selection-item'>All Locations<a class='as-close'>&times;</a></li>";
        $this->view->locVal = $locVal;

        /* By default show all designations selected in filter */
        $desVal = '';
        $desVal .= "<li class='as-selection-item'>All Designations<a class='as-close'>&times;</a></li>";
        $this->view->desVal = $desVal;

        $calContent = '';
        $colspan = 12;

        $this->view->colspan = $colspan;

        /* --------------------------------POST-------------------------------------------------------------- */

        /* Functionality after submitting attendance or search filter */
        if ($this->_request->isPost()) { 

            $formData = $this->_request->getPost();
            $newdate=$formData['date'];
            if (isset($formData['bsubmit']) && $formData['bsubmit'] == "Search") {
                $date = $formData["date"];
                $empId = $formData["employee"];
//                if ($empId == 'all') {
//                    $empId = $employeeIn;
//                }
                $loc = $formData["selectedLocationVal"];
                $des = $formData["selectedDesignationVal"];
                $empStatus = $formData["employmentStatus"];
                @$mgrId = $formData["selectedManagerVal"];
                
            } else {
                $date = $formData['attendanceDate'];
                $newdate=$date;
                $empId = $formData['employee'];
//                if ($empId == 'all') {
//                    $empId = $employeeIn;
//                }
                $loc = $formData['attendanceLocations'];
                $des = $formData['attendanceDesignations'];
                $empStatus = $formData["employmentStatus"];
                @$mgrId = $formData["selectedManagerVal"];
            }
//            if ($empId != '') {
//                $empId = str_replace('e.id IN (', '', $empId);
//                $empId = str_replace(') and', '', $empId);
//            }

            /* Button for reports is pressed */


            $this->view->date = $date;
            $this->view->newdate =$newdate;
            $this->view->empId = $empId;
            $this->view->loc = $loc;
            $this->view->des = $des;
            $this->view->empStatus = $empStatus;
            $this->view->mgrId = $mgrId;
            $options = array();
            $options['exFlag'] = $empStatus;
            Zend_Controller_Action_HelperBroker::addHelper(new Application_Action_Helper_Filter());

            if (isset($formData['attendanceDate']) && $formData['bsubmit'] != "Update") {
//                if ($empId == '``') {
//                    $empId = '';
//                }
                $employeeRows = $this->getHelper('Filter')->getEmployeesFromFilter($empId, $loc, $des, '', '', $options);
                $employeeRows = explode(',', $employeeRows);

                foreach ($employeeRows as $emRow) {
                    /*$employeeTimingsModel = new Application_Model_EmployeeTimings();
                    
                    $employeeTimingsModelData = $employeeTimingsModel->fetchRow(" employee_id ='" . $emRow."' and recent_record='1'");
                   
                    if($employeeTimingsModelData && $employeeTimingsModelData['time_in']!="NULL")
                    {
                        $end=$employeeTimingsModelData['time_out'];
                               
                        $start=$employeeTimingsModelData['time_in'];
                         $office_hrs=explode(":",$this->getTimeDiff($start,$end));
                         $hrs=$office_hrs[0];
                         $min=$office_hrs[1];
                         
                        //echo $end=strtotime($employeeTimingsModelData['time_out']) ."<br>";
                       // echo $start=strtotime($employeeTimingsModelData['time_in']);
                       //$hours = strtotime($employeeTimingsModelData['time_out']) - strtotime($employeeTimingsModelData['time_in']);
                    }
                    else
                    {
                       $settingsModel = new Application_Model_Settings();
                       $value1=$settingsModel->fetchRow("param ='office_timein' ");
                       $value2=$settingsModel->fetchRow("param ='office_timeout' ");
                       
                       $time1=$value1['value'];
                       $time2=$value2['value'];
                       $time1=strtotime($time1);
                       $time2=strtotime($time2);
                 //      echo $time1.' '.$time2.'<br>';
                      $hours= ($time2-$time1);
                       
                       // $hours="32400";
                    
                        $hrs = intval($hours / 3600);

                        $sec = $hours % 3600;
                        $min = intval($sec / 60);
                        $sec=$sec%60;

                    }*/
                    //$total_working_hours= date('H:i', strtotime($total_working_hours));
                    $data = array();
                    $data['date'] = $date;
                    $data['time_in'] = (empty($formData[$emRow]['in'])?"00:00":$formData[$emRow]['in']);
                    $data['time_out'] = (strlen($formData[$emRow]['out'])<=3?$formData['newdate'.$emRow]." 00:00":$formData[$emRow]['out']);
                    $data['remarks'] = $formData[$emRow]['rmks'];
                    $data['reason'] = $formData[$emRow]['slc'];
                    $data['hrs'] = ((substr(strval($formData[$emRow]['hrs']), 0, 1) == "-" || empty($formData[$emRow]['hrs']))?"00:00:00":$formData[$emRow]['hrs']);
                    $data['newdate']=$formData['newdate'.$emRow];
                   // $data['created_date'] = $date;
                   // $data['created_by'] = $this->id;
                    
                    /*if($data['reason']==13){
                            $hrs-=4;
                            $total_working_hours=$hrs.":".$min;
                    }
                    else if($data['reason']==14){
                        $hrs-=2;
                        $total_working_hours=$hrs.":".$min;
                    }
                    else if($data['reason']==4 || $data['reason']==5){
                        if($formData[$emRow]['hrs']=="00:00:00")
                            $total_working_hours="00:00:00";
                        else
                            $total_working_hours=$hrs.":".$min;
                    }
                    else if($data['reason']!="0")
                        $total_working_hours="00:00:00";
                    else
                        $total_working_hours=$hrs.":".$min;*/
                    
                    if($data['time_in']=="00:00"  && $data['time_out']==$formData['newdate'.$emRow]." 00:00" && $data['reason']==0 && empty($data['remarks']))
                        $data['office_hrs'] = "00:00";
                    else
                        $data['office_hrs'] = $this->getHelper('CommonFunctions')->calculateOfficeHours(array('empId' => $emRow, 'dateTime' => $date, 'reason' => $data['reason']));
                    
                   // $data['office_hrs']=$total_working_hours;

                    $this->markAttendance($emRow, $data);
                }
                $attendanceFlag = true;
            };

            if (isset($attendanceFlag) && $attendanceFlag) {
                $messages = array();
                $messages[] = array('success', 'Attendance Saved Successfully');
                $this->view->messages = $messages;
            }

//            if ($empId == '``') {
//                $empId = 'all';
//            }
               
            $employeeRows = $this->getHelper('Filter')->getEmployeesFromFilter($empId, $loc, $des, '', '', $options);
            if ($employeeRows != '') {
                $employeeRows = explode(',', $employeeRows);
                // sort($employeeRows);
                if ($this->isAttendanceMarked($date, $employeeRows) && $formData['bsubmit'] != "Update") {
                    $attendanceMarked = true;
                } else {
                    $attendanceMarked = false;
                }
                
                $sr = 1;
                $tabindex = 9;
                if (count($employeeRows) > 1) {
                    $employeeRows = array_slice($employeeRows, 0, -1);
                }//print_r($employeeRows);die;
                foreach ($employeeRows as $er) { 
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $pic = $db->fetchAll("select file_name from documents where status='Active' and employee_id='" . $er .
                                    "' and type='Picture' and file_type='Image'");
                    $secondaryFields = $db->fetchAll("select number,name from employee where id='" . $er . "'");
                    if (isset($pic[0]["file_name"])) {
                        $filePath = "/uploads/Picture/thumb__" . $pic[0]["file_name"];
                    } else {
                        $filePath = "/images/emp-picture.jpg";
                    }
                    if(!$attendanceMarked){
                        $calContent .='<tr>
                        <td>' . $sr . '</td>
                        <td><img src="' . $filePath . '" width="50" height="50"/></td>
                        <td>' . $secondaryFields[0]['number'] . '</td>
                        <td width="10">' . $secondaryFields[0]['name'] . '</td>';
                    }else{
                        $this->view->update_flag = True;
                        $calContent .='<tr>
                        <td>' . $sr . '</td>
                        <td><img src="' . $filePath . '" width="50" height="50" class="img-thumbnail"/></td>
                        <td width="10">'. $secondaryFields[0]['number'].' - '. $secondaryFields[0]['name'] . '</td>';
                    }
                    $sr++;
                    $holidayFlag = $this->getHolidayFlag($date);
                    $leaveRow = $this->getEmployeeLeaveFlag($er, $date);
                    $attendanceModel = new Application_Model_Attendance();
                    $attendanceR = $attendanceModel->fetchRow("employee_id='" . $er . "' and time_in like '%" . $date . "%'");
                    $backgroundClass = '';
                    $leaveReasonId = '';
                    $leaveRemarks = '';
                    if ($holidayFlag) {
                        $backgroundClass = 'holidays';
                    } elseif ($leaveRow) {
                        $leaveReasonId = $leaveRow->reason_id;
                        $leaveRemarks = $leaveRow->remarks;
                        $backgroundClass = 'leave';
                    } else {
                        //natural background
                    }
                    
                    $calContent .= '<td align="center" width="100" class="' . $backgroundClass . '"><input tabindex="' . $tabindex++ . '" id="in" class="form-control timebox arrow" type="text" ' . (($attendanceMarked) ? 'readonly = "readonly"' : '') . ' name="' . $er . '[in]" value="' . (isset($attendanceR) ? date("H:i", strtotime($attendanceR['time_in'])) : '') . '" style="width:60px;"/></td>
                                    <td align="center" class="' . $backgroundClass . '">

                                    <input style="width:105px; margin-right: -20px;"  id="newdate'.$er.'" class="form-control newdate" type="text" readonly="readonly" name="newdate'.$er.'" tabindex="2"  value="' . ((isset($attendanceR) && $attendanceR['time_out']) != null ? substr($attendanceR['time_out'],0,10) : $newdate. '') . '" />
                                    </td>
                                    <td><input style="width:60px;" tabindex="' . $tabindex++ . '" type="text" id="out" class="form-control out timebox arrow" ' . (($attendanceMarked) ? 'readonly = "readonly"' : '') . ' name="' . $er . '[out]" value="' . ((isset($attendanceR) && $attendanceR['time_out']) != null ? date("H:i", strtotime($attendanceR['time_out'])) : '') . '"/></td>
                                    <td style="width:10;" align="center" colspan="2"><input  type="hidden" name="' . $er . '[hrs]" value="' . ((isset($attendanceR) && $attendanceR['hrs'] != null && $attendanceR['hrs'] != '') ? substr($attendanceR['hrs'],0,5) : '') . '" /><div name="' . $er . '[show]" class="' . $er . 'timebox" style="width:40px;">'
                            . ((isset($attendanceR) && $attendanceR['hrs'] != null && $attendanceR['hrs'] != '') ? substr($attendanceR['hrs'],0,5) : '') . '</div></td>';
                    $calContent .= '<td class="' . $backgroundClass . '" ><select class="form-control selectpicker" tabindex="' . $tabindex++ . '" class="slcts" name="' . $er . '[slc]" value="' . (isset($attendanceR) ? $attendanceR['reason_id'] : '') . '">';
                    $calContent .= '<option value="0" SELECTED>Select Reason</option>';
                    foreach ($optionGroups as $optionGroup => $optionGroupArray) {
                        $calContent .= '<optgroup label="' . $optionGroup . '">';
                        foreach ($optionGroupArray as $key => $value) {
                            $calContent .= '<option style="margin-left:-12px;" value="' . $key . '"' . ($key == @$attendanceR['reason_id'] ? ' SELECTED' : ($key == $leaveReasonId ? ' SELECTED' :'')) . '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $value . '</option>';
                        }
                        $calContent .= '</optgroup>';
                    }//echo "<pre>";print_r($attendanceR);die;
                    $calContent .= '</select></td>';
                    $calContent .= '<td class="' . $backgroundClass . '">
                        <input tabindex="' . $tabindex++ . '" class="form-control remarks arrow" ' . (($attendanceMarked) ? 'readonly = "readonly"' : '') . ' type="text" name="' . $er . '[rmks]" value="' . (isset($attendanceR) ? $attendanceR['remarks'] : (isset($leaveRemarks)?$leaveRemarks:"")) . '"/></td>';
                    if($attendanceMarked){
                        $cby_id = $attendanceR['created_by'];
                        $emp_name_create = $db->fetchAll("select number,name from employee where id = '". $cby_id ."'");
                        $created_date = $attendanceR['created_date'];
                        $created_date = date(Zend_Registry::getInstance()->get('DATE'), strtotime($created_date));
                        $calContent .= '<td class="text-element ' . $backgroundClass . '">'.$emp_name_create[0]['name'].'<br>ON<br>'.$created_date.'</td>';
                        $mby_id = $attendanceR['modified_by'];
                        if($mby_id > 0){
                            $emp_name_modified = $db->fetchAll("select number,name from employee where id = '". $mby_id ."'");
                            $modified_date = $attendanceR['created_date'];
                            $modified_date = date(Zend_Registry::getInstance()->get('DATE'), strtotime($modified_date));
                            $calContent .= '<td class="text-element ' . $backgroundClass . '">'.$emp_name_modified[0]['name'].'<br>ON<br>'.$modified_date.'</td>';
                        }else{
                            $calContent .= '<td class="text-element ' . $backgroundClass . '">Not Modified</td>';
                        }
                    }
                }
                   

                $calContent .='       </tr>
                            <tr style="text-align:center;">
                              <td colspan="' . $colspan . '" style="border:1;padding-top:10px;padding-bottom:10px;">';
                if(count($employeeRows)==1) {
                    if ($attendanceMarked)
                        $calContent .= '<span><input tabindex="' . $tabindex++ . '" type="submit" value="Update" class="btn btn-primary" name="bsubmit" title="Update" style="margin-right:3px;"/></span>';
                    else
                        $calContent .= '<span><input tabindex="' . $tabindex++ . '" type="submit" value="Save Attendance" class="btn btn-primary" name="bsubmit"  title="Save Attendance" style="margin-right:3px;"/></span>';
                }
                //style="text-decoration:none;"
                $calContent .='<span><a tabindex="' . $tabindex++ . '" style="text-decoration:none;" title="Empty Attendance Sheet" 
                                     href="/attendance/generate-report/type/empty/date/' . $date . '/empId/' . $empId . '/des/' . $des . '/loc/' . $loc . '/opt/' . $options['exFlag'] . '" target="_blank"/><input tabindex="' . $tabindex++ . '" type="button" value="Print Empty Sheet" class="btn btn-primary" name="bsubmit" title="Print Empty Sheet"/></a></span>
                              <span><a tabindex="' . $tabindex++ . '" style="text-decoration:none;" title="Today\'s Update"
                                     href="/attendance/generate-report/ex/pdf/type/update/date/' . $date . '/empId/' . $empId . '" target="_blank"/><input tabindex="' . $tabindex++ . '" type="button" value="Print Today\'s Update(Pdf)" class="btn btn-primary" name="bsubmit"  title="Print Today\'s Update(Pdf)"/></a></span>
                                         <span><a tabindex="' . $tabindex++ . '" style="text-decoration:none;" title="Today\'s Update" 
                                     href="/attendance/generate-report/ex/excel/type/update/date/' . $date . '/empId/' . $empId . '" target="_blank"/><input tabindex="' . $tabindex++ . '" type="button" value="Print Today\'s Update(Excel)" class="btn btn-primary" name="bsubmit" title="Print Today\'s Update(Excel)"/></a></span>
                              <span><a  tabindex="' . $tabindex++ . '"  style="text-decoration:none;"  title="Weekly  Sheet" 
34 	 	                     href="/attendance/generate-report/type/weekly/date/' . $date . '/empId/' . $empId . '/des/' . $des . '/loc/' . $loc
                        . '"  target="_blank"/><input tabindex="' . $tabindex++ . '" type="button" value="Print  Weekly  Sheet" class="btn btn-primary" name="bsubmit" title="Print  Weekly  Sheet"/></a></span>
                              </td>
                            </tr>';
                $this->view->calContent = $calContent;
            }
        }
        
    }

    /**
     * ROLE
     * @generatereport Empty Attendance Sheet and Today's Update Reports
     */
    public function generateReportAction() { 

        $date = $this->_request->date;
        $format = $this->_request->ex;
        $empId = $this->_request->empId;
        
        $checkSingleEmpId = split (",", $empId); 
        $checkSingleEmpId = count($checkSingleEmpId);
        if($checkSingleEmpId == 1){
            $empId = $this->_request->empId;
        }else{
            $empId = substr($empId, 0, -1);
        }
        $des = $this->_request->des;
        $loc = $this->_request->loc;
        $options = array();
        $options['exFlag'] = $this->_request->opt;

        /* generate pdf report */
        require_once('../library/Application/tcpdf/mypdf.php');
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $storage = new Zend_Auth_Storage_Session();
        if ($storage->read()->employee_id == 0 || $storage->read()->name == 'Admin') {
            $empName = $storage->read()->name;
            $employeeId = $storage->read()->employee_id;
            $empNumber = $storage->read()->employee_id;
        } else {
            $employeeModel = new Application_Model_Employees();
            $employeeId = $storage->read()->employee_id;
            $employeeRow = $employeeModel->fetchRow("id='" . $employeeId . "'");
            $empName = $employeeRow->name;
            $employeeId = $storage->read()->employee_id;
            $empNumber = $employeeRow['number'];
        }
        $htmlContent = '';
        if ($this->_request->type == "empty") {
            $htmlContent = $this->attendanceSheetPrint($date, $empId, $des, $loc, $options, false);
        } elseif ($this->_request->type == "filled") {
            $htmlContent = $this->attendanceSheetPrint($date, $empId, $des, $loc, true);
        } elseif ($this->_request->type == "update") {
            $htmlContent = $this->generateTodaysUpdate($date, $empId);
        } elseif ($this->_request->type == "weekly") {
            $pdf = new MYPDF('l', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $htmlContent = '<table align="right" style="transform:rotate(90deg);"><tr><td style="background-color:#f29fbb;">Leave</td><td style="background-color:#ffbb89;">Time Short</td><td style="background-color:#c5b9e6;">Short Leave</td><td style="background-color:#9adcf4;">Half Leave</td></tr></table>';
            $htmlContent = $this->generateWeeklyReport($date, $empId, $loc, $des);
        }
        $pdf->SetFooterMargin(20);
        // add default footer
        $pdf->setPrintFooter(true);
        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 10);
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFontSize(7);
        // add a page
        $pdf->AddPage('L');
        $pdf->writeHTML($htmlContent, true, false, false, false, '');
        $html = '<p>This document was printed on: <u><b>' . date(Zend_Registry::getInstance()->get('DATE') . ' H:i:s') . '</b></u>
                         ,printed by: <u><b>' . $empName . '</b></u>(<u><b>' . $empNumber . '</b></u>)</p>';
        $pdf->lastPage();
        $pdf->SetFontSize(7);
        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        if($format=="excel"){
            header('Content-type: application/ms-excel');
            header('Content-Disposition: attachment; filename=NextHRM_report.xls');
            echo $htmlContent.$html;
            die;
        }else{
            $pdf->Output("NextHRM_report.pdf", 'I');
        }
    }

    private function getLocationString($loc) {
        $locationsModel = new Application_Model_Location();
        $locationList = $locationsModel->getTheLocations('id');
        $location = '';
        if ($loc == "")
            $location = 'All Centers';
        else {
            $locArray = explode(',', $loc);
            $tempLocArray = array();
            foreach ($locArray as $aLocation) {
                if ($aLocation != "")
                    $tempLocArray[] = $locationList[$aLocation];
            }
            $location = implode(',', $tempLocArray);
        }
        return $location;
    }

    private function getDesignationString($des) {
        $designationsModel = new Application_Model_Designation();
        $designationList = $designationsModel->getTheDesignations('id');
        $designation = '';
        if ($des == "")
            $designation = 'All Designations';
        else {
            $desArray = explode(',', $des);
            $tempDesArray = array();
            foreach ($desArray as $aDesignation) {
                if ($aDesignation != "")
                    $tempDesArray[] = $designationList[$aDesignation];
            }
            $designation = implode(',', $tempDesArray);
        }
        return $designation;
    }

    private function attendanceSheetPrint($date, $empId, $des, $loc, $options, $filled=true) {

        $employeeModel = new Application_Model_Employees();
        Zend_Controller_Action_HelperBroker::addHelper(new Application_Action_Helper_Filter());
        $employeeRows = $this->getHelper('Filter')->getEmployeesFromFilter($empId, $loc, $des, '', '', $options);
        $employeeRows = explode(',', $employeeRows);

        $location = '';
        $designation = '';

        $location = $this->getLocationString($loc);
        $designation = $this->getDesignationString($des);

        $htmlContent = '';
        $htmlContent .= '<style type="text/css">
                            table {  
                                    padding: 4px;
                            }
                            table tr    { 
                                           page-break-inside:avoid; 
                                           page-break-after:auto; 
                            }
                            td {
                                text-align: center;
                                height: 29.3px;
                                font-family: arial;
                            }
                            thead tr th { border-bottom: 1px solid #000000;}
                            tfoot { display:table-footer-group; }
                            .head{ 
                                    border-left: 1px solid #000000;  
                                    border-bottom: 1px solid #000000;
                                    font-weight: bold;
                                    text-align: center;
                             }
                            .row{ 
                                    border-left: 1px solid #000000; 
                                    border-bottom: 1px solid #000000;
                                    height: 20px;
                                    text-align: center;
                             }
                            .lastcol{ 
                                        border-left: 1px solid #000000; 
                                        border-bottom: 1px solid #000000; 
                                        border-right: 1px solid #000000; 
                                        font-weight: bold;
                              }
                              .textsett{
                                        display: table-cell;
                                        vertical-align: middle;
                              }
                        </style>
                <table width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <td colspan="2" style="text-align:left;"><b>Day:</b><u>' . date(' l', strtotime($date)) . '</u></td>
                            <td height="20px" colspan="3" style="text-align:center;"><b>NEXTBRIDGE (PVT) LTD</b></td>
                            <td colspan="2" style="text-align:right;"><b>Date:</b><u>' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($date)) . '</u></td>
                        </tr>
                        <tr>
                            <td colspan="7" style="background-color: #CCC; font-size:10;text-align: center;border-top: 1px solid #000000;" class="head lastcol">
                                   <span class="textsett"> DAILY ATTENDANCE SHEET FOR ' . strtoupper($designation . ' in ' . $location) . '</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="head" width="34px">Sr#.</td>
                            <td class="head" width="50px">Emp ID</td>';
        if ($filled) {
            $htmlContent .= '<td class="head" width="204.5px">Employee Name</td>
                                                <td class="head" >Time In</td>
                                                <td class="head" >Time Out</td>
                                                <td class="head lastcol" colspan="2">Total Hours</td>';
        } else {
            $htmlContent .= '<td class="head" width="197px">Employee Name</td>
                                                <td class="head">Time In</td>
                                                <td class="head" width="100px">Signature</td>
                                                <td class="head">Time Out</td>
                                                <td class="head lastcol" width="100px">Signature</td>';
        }
        $htmlContent .= '</tr>
                                          </thead><tbody>';

        $sr = 1;
        foreach ($employeeRows as $eRow) {
            $db = Zend_Db_Table::getDefaultAdapter();
            $secondaryFields = $db->fetchAll("select number,name from employee where id='" . $eRow . "'");
            $htmlContent .= '<tr>
                            <td class="row" width="34px">' . $sr . '</td>
                            <td class="row" width="50px">' . $secondaryFields[0]['number'] . '</td>';
            if ($filled) {
                $attendanceRecord = $db->fetchAll("select time_in,time_out,hrs from attendance where employee_id='" . $eRow . "' and time_in like '%" . $date . "%'");
                $htmlContent .= '<td class="row" width="204.5px" style="text-align:left;"><span class="textsett" style="margin-top:15px;">' . $secondaryFields[0]['name'] . '</span></td>';
                if ($attendanceRecord) {
                    $htmlContent .= '<td class="row">' . date('H:i', strtotime($attendanceRecord[0]['time_in'])) . '</td>
                                 <td class="row" >' . date('H:i', strtotime($attendanceRecord[0]['time_out'])) . '</td>
                                 <td class="row lastcol" colspan="2">' . date('H:i', strtotime($attendanceRecord[0]['hrs'])) . '</td>
                                 </tr>';
                } else {
                    $htmlContent .= '<td class="row"></td>
                                 <td class="row"></td>
                                 <td class="row lastcol" colspan="2"></td>
                                 </tr>';
                }
            } else {
                $htmlContent .= '<td class="row" width="197px" style="text-align:left;"><span class="textsett" style="margin-top:15px;">' . $secondaryFields[0]['name'] . '</span></td>';
                $htmlContent .= '<td class="row"></td>
                                <td class="row" width="100px"></td>
                                <td class="row"></td>
                                <td class="lastcol" width="100px"></td>
                                </tr>';
            }
            $sr = $sr + 1;
        }
        while ($sr % 30 != 0 && $filled == false) {
            $htmlContent .= '<tr>
                <td class="row" width="33.5px">' . $sr . '</td>
                <td class="row" width="50px"></td>
                <td class="row" width="197.5px" style="text-align:left;padding-left:5px;padding-top:5px;"></td>
                <td class="row"></td>
                <td class="row" width="100px"></td>
                <td class="row"></td>
                <td class="lastcol" width="100px"></td>
                </tr>';
            $sr = $sr + 1;
        }
        $htmlContent .= '</tbody>
        </table>';
        return $htmlContent;
    }

    private function generateTodaysUpdate($date, $empId='') {
        $dateHeading = "Today's Update " . date(Zend_Registry::getInstance()->get('DATE'), strtotime($date));

        $attendanceModel = new Application_Model_Attendance();
        $locationModel = new Application_Model_Location();
        $locationList = $locationModel->getTheLocations('id');
        $reasonModel = new Application_Model_LeaveReason();
        $reasonOptionGroup = $reasonModel->getOptionGroup();
        unset($locationList['']);
        $employeeModel = new Application_Model_Employees();
        // html for the report
        $htmlcontent = '<style type="text/css">
                        table {  
                                    padding: 4px;
                            }
                            table tr    { 
                                           page-break-inside:avoid; 
                                           page-break-after:auto; 
                            }
                            td {
                                font-family: arial;
                            }
                            th {
                                font-family: arial;
                                vertical-align: text-top;
                            }
                        </style>
                        <table align="right;" width="40%"><tr><td style="background-color:#c5b9e6;">Late Comers</td><td style="background-color:#ffbb89;">Did not inform</td><td style="background-color:#f29fbb;">On Leave</td></tr></table><br/>
            <table cellspacing="0" border="1" width="100%">
            <thead>
            <tr  style="text-align: center;">
                <td colspan="5" height="30px" style="padding-top: 10px;">' . $dateHeading . '</td>
            </tr>
            <tr style="background-color: #CCC; text-align: center;">
                    <th width="38px">
                        Sr.#
                    </th>
                    <th width="200px">
                        Name
                    </th>
                    <th width="80px">
                        Time-in
                    </th>
                    <th width="130px">
                        Reason
                    </th>
                    <th width="225px">
                        Remarks
                    </th>
        </tr></thead>';
        $count = 1;
        foreach ($locationList as $key => $value) {
            if(!empty($empId)){ 
                $attendanceRows = $attendanceModel->getTodaysUpdate($date, $key, $empId);
            }else{
                $attendanceRows = $attendanceModel->getTodaysUpdate($date, $key);
            }
            if ($attendanceRows->count() > 0) {
                $htmlcontent .= '<tr style="text-align:center; background-color: #CCC;padding-top: 10px;"><td colspan="5"><b>' .
                        $value . '</b></td></tr>';
                foreach ($attendanceRows as $attendance) {
                    $tdColor = "";
                    if(!in_array($attendance['a.reason_id'],array(4,5,0)))
                        $tdColor = "#f29fbb";
                    elseif($attendance['a.reason_id']==5)
                        $tdColor = "#ffbb89";
                    else
                        $tdColor = "#c5b9e6";
                    $htmlcontent .= '<tr style="text-align: left;">
        <td width="38px" style="text-align:center;">' . $count . '</td>
        <td width="200px" style="background-color:'.$tdColor.';">' . $attendance['e.name'] . '</td>';
                    if ($attendance['a.reason_id'] != '' && $attendance['a.reason_id'] != 0) {
                        //if ($reasonOptionGroup[$attendance['a.reason_id']] == 'Other' && date('H:i', strtotime($attendance['time_in'])) != '00:00') {
                            $htmlcontent .= '<td width="80px">' . date('H:i', strtotime($attendance['time_in'])) . '</td>';
                        //} else {
                            $htmlcontent .= '<td width="130px">' . $attendance['r.reason_name'] . '</td>';
                        //}
                    } else {
                        $htmlcontent .= '<td width="80px">' . date('H:i', strtotime($attendance['time_in'])) . '</td>';
                        $htmlcontent .= '<td width="130px">&nbsp;</td>';
                    }
                    $htmlcontent .= '<td width="225px">' . (empty($attendance['remarks'])?"&nbsp;":$attendance['remarks']) . '</td>';
                    $htmlcontent .= '</tr>';
                    $count = $count + 1;
                }
            } 
        }
        $htmlcontent .= '</table>';
        return $htmlcontent;
    }

    private function generateWeeklyReport($date, $empId, $location, $designation, $cron=false) {
        $week_number = date('W', strtotime($date));
        $year = date('Y', strtotime($date));
        $datesInWeek = array();
        $attDays = 5;
        if($cron)
            $attDays = 6;
        
        for ($day = 1; $day <= $attDays; $day++) {
            $datesInWeek[$day] = date('Y-m-d', strtotime($year . "W" . $week_number . $day));
        }
        $saturdayOnn = false;
        $db = Zend_Db_Table::getDefaultAdapter();
        if($cron){
            $saturdayAttRecord = $db->fetchRow("select time_in,time_out,hrs,reason_id,office_hrs,break_time from attendance where employee_id IN (" . $empId . ") and time_in like '%" . $datesInWeek[6] . "%'");
            if($saturdayAttRecord){
                $saturdayOnn = true;
            }else{
                unset($datesInWeek[6]);
            }
        }
        Zend_Controller_Action_HelperBroker::addHelper(new Application_Action_Helper_Filter());
        $employees = $this->getHelper('Filter')->getEmployeesFromFilter($empId, $location, $designation, '', '', null);
        $employees = explode(',', $employees);

        $locationNames = $this->getLocationString($location);
        $designationNames = $this->getDesignationString($designation);

        $leaveReasonModel = new Application_Model_LeaveReason();
        $optionGroupList = $leaveReasonModel->getOptionGroup();

        $htmlContent = '';
        $htmlContent .= '<style>
                            table{
                                padding: 4px;
                            }
                             table tr    { 
                                           page-break-inside:avoid; 
                                           page-break-after:auto; 
                            }
                            td{
                                text-align: center;
                                
                            }
                            .hd{
                                background-color:#CCC;
                            }
                            .days{
                                background-color:#E4FAFC;
                            }
                            .time{
                                background-color:#D9FCDD;
                            }
                            .total{
                                background-color:#F5F97E;
                            }
                        </style>
                        <table align="right" width="40%"><tr><td style="background-color:#f29fbb;">Leave</td><td style="background-color:#ffbb89;">Time Short</td><td style="background-color:#c5b9e6;">Short Leave</td><td style="background-color:#9adcf4;">Half Leave</td></tr></table><br/>
                        <table border="1" cellspacing="0" width="100%">
                        <thead>';
                        if(!$cron){
                            $htmlContent .= '<tr>
                                <td colspan="30" width="1005px" class="hd"><b>Weekly Time Sheet of ' . $designationNames . ' in ' . $locationNames . '</b></td>
                            </tr>';
                        }
                        $htmlContent .= '<tr>
                            <td colspan="30" width="1005px" class="hd"><b>During The Period From  ' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($datesInWeek[1])) .
                ' to ' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($datesInWeek[5])) . '</b></td>
                        </tr>
                        <tr>
                           <td rowspan="2" width="20px">Sr.</td>
                           <td rowspan="2" width="35px">Emp ID</td>
                           <td rowspan="2" width="80px">Employee Name</td>
                           <td colspan="4" width="154px" class="days">Monday
                               (' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($datesInWeek[1])) . ')</td>
                           <td colspan="4" width="154px" class="days">Tuesday
                               (' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($datesInWeek[2])) . ')</td>
                           <td colspan="4" width="154px" class="days">Wednesday
                               (' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($datesInWeek[3])) . ')</td>
                           <td colspan="4" width="154px" class="days">Thursday
                               (' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($datesInWeek[4])) . ')</td>
                           <td colspan="4" width="154px" class="days">Friday
                               (' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($datesInWeek[5])) . ')</td>';
                            if($saturdayOnn){
                                   $htmlContent .= '<td colspan="4" width="154px" class="days">Saturday
                               (' . date(Zend_Registry::getInstance()->get('DATE'), strtotime($datesInWeek[6])) . ')</td>';
                            }
                                   
                           $htmlContent .= '<td rowspan="2" class="hd" width="50px">Worked Hours</td>
                           <td rowspan="2" class="hd" width="50px">Office Hours</td>
                        </tr>
                        <tr>';
                           if(!$saturdayOnn)
                               $attDays = 5;

        $i = 0;
        for ($i = 1; $i <= $attDays; $i++) {
            $htmlContent .= '<td class="time">Check IN</td>
                                        <td class="time">Check Out</td>
                                        <td class="time">Break Time</td>
                                        <td class="time">Total Hrs</td>';
        }

        $htmlContent .= '</tr>
                                        </thead>
                                        <tbody>';
        $count = 1;
        foreach ($employees as $emp) {
            $db = Zend_Db_Table::getDefaultAdapter();
            $employeeRecord = $db->fetchAll("select number,name from employee where id='" . $emp . "'");
            $htmlContent .= '<tr>';
            $htmlContent .= '<td width="20px">' . $count . '</td>';
            $htmlContent .= '<td width="35px">' . $employeeRecord[0]['number'] . '</td>';
            $htmlContent .= '<td width="80px" style="text-align:left;">' . $employeeRecord[0]['name'] . '</td>';
            $totalTime = array();
            $totalOfficeTime = array();
            foreach ($datesInWeek as $dtw) {
                $leaveColor = "";
                ob_clean();
                $attendanceRecord = $db->fetchRow("select time_in,time_out,hrs,reason_id,office_hrs,break_time from attendance where employee_id='" . $emp . "' and time_in like '%" . $dtw . "%'");
                $modelLeave = new Application_Model_Leave();
                $curdateLeaves = $modelLeave->fetchRow(" from_date <= '" . $dtw . "' and to_date >= '" . $dtw . "' and employee_id=" . $emp);
                    if($curdateLeaves['leave_type']=="half_leave_availed"){
                        $leaveColor = 'background-color:#9adcf4;';
                    }elseif($curdateLeaves['leave_type']=="short_leave_availed"){
                        $leaveColor = 'background-color:#c5b9e6;';
                    }
                    $timein = date('H:i',  strtotime($attendanceRecord['time_in']));
                    $timeout = date('H:i',  strtotime($attendanceRecord['time_out']));
                    $breakTime = "00:00";                    
                    if($attendanceRecord['break_time']!="00:00:00")
                        $breakTime = date('H:i',  strtotime($attendanceRecord['break_time']));
                if ($attendanceRecord) {
                    if($attendanceRecord['reason_id']==13){
                        $leaveColor = 'background-color:#9adcf4;';
                    }elseif($attendanceRecord['reason_id']==14){
                        $leaveColor = 'background-color:#c5b9e6;';
                    }
                    if ($attendanceRecord['reason_id'] == 0 || $attendanceRecord['reason_id'] == NULL) {
                        $htmlContent .= '<td width="38.5">' . date('H:i', strtotime($attendanceRecord['time_in'])) . '</td>';
                        $htmlContent .= '<td width="38.5">' . date('H:i', strtotime($attendanceRecord['time_out'])) . '</td>';
                        $htmlContent .= '<td width="38.5">' . $breakTime . '</td>';
                        $htmlContent .= '<td width="38.5" style="'.($attendanceRecord['hrs']<$attendanceRecord['office_hrs']?"background-color: #ffbb89;":"").'">' . date('H:i', strtotime($attendanceRecord['hrs'])) . '</td>';
                    } else {
                        if ($timein!="00:00" && $timeout!="00:00" && $attendanceRecord['reason_id'] != 0) {
                            $htmlContent .= '<td width="38.5">' . date('H:i', strtotime($attendanceRecord['time_in'])) . '</td>';
                            $htmlContent .= '<td width="38.5">' . date('H:i', strtotime($attendanceRecord['time_out'])) . '</td>';
                            $htmlContent .= '<td width="38.5">' . $breakTime . '</td>';
                            $htmlContent .= '<td width="38.5" style="'.$leaveColor.'">' . date('H:i', strtotime($attendanceRecord['hrs'])).'</td>';
                        } else {
                            $htmlContent .= '<td colspan="4" width="154px" style="background-color:#f29fbb;">Leave</td>';
                        }
                    }
                    $totalTime[] = $attendanceRecord['hrs'];
                    $totalOfficeTime[] = $attendanceRecord['office_hrs'];
                } elseif ($this->getHolidayFlag($dtw)) {
                    $htmlContent .= '<td colspan="4" width="154px">Holiday</td>';
                } elseif ($this->getEmployeeLeaveFlag($emp, $dtw)) {
                    $htmlContent .= '<td colspan="4" width="154px" style="background-color:#f29fbb;">Leave</td>';
                } else {
                    $htmlContent .= '<td colspan="4" width="154px">Not Marked</td>';
                }
            }
            $workinHrs = $this->calculateTotalHours($totalTime);
            $officeHrs = $this->calculateTotalHours($totalOfficeTime);
            if ($totalTime != 0)
                $htmlContent .= '<td width="50px" style="'.($workinHrs<$officeHrs?"background-color: #ffbb89;":"").'">' . $workinHrs . '</td>';
            else
                $htmlContent .= '<td width="50px">00:00</td>';
            
            $htmlContent .= '<td width="50px">' . $officeHrs . '</td>';
            
            $htmlContent .= '</tr>';
            $count = $count + 1;
        }
        $htmlContent .= '</tbody>
                                    </table>';
        return $htmlContent;
    }

    private function calculateTotalHours($timeArray) {
        $hrs = array();
        $min = array();
        $fullHours = 0;
        $fullMin = 0;
        foreach ($timeArray as $time) {
            $time = explode(':', $time);
            $hrs[] = $time[0];
            $min[] = $time[1];
        }
        foreach ($hrs as $hr) {
            $fullHours = $fullHours + $hr;
        }
        foreach ($min as $m) {
            $fullMin = $fullMin + $m;
        }
        if ($fullMin > 60) {
            $carryMin = intval($fullMin / 60);
            $fullHours = $fullHours + $carryMin;
            $fullMin = intval($fullMin % 60);
        }
        if ($fullHours < 10 || $fullHours == 0)
            $fullHours = '0' . $fullHours;
        if ($fullMin < 10 || $fullMin == 0)
            $fullMin = '0' . $fullMin;

        return $fullHours . ':' . $fullMin;
    }
	//http://stackoverflow.com/questions/14938339/php-datetime-difference-between-2-datetime-with-2-variables
	private function timeDateDifference($timeIn,$timeOut){
            //Salman updated - Seconds are also deducted so i have removed seconds from params
		$timeIn = date('Y-m-d H:i',  strtotime($timeIn));
                $timeOut = date('Y-m-d H:i',  strtotime($timeOut));
		$start_date = new DateTime($timeIn);
    $end_date = new DateTime($timeOut);
    $interval = $start_date->diff($end_date);
		//echo "Result " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days ";
		//echo "Result " . $interval->h . "   " . $interval->i."  ".$interval->s."  ";
		
		 if($interval->d >0)
		 	$resultTime=($interval->d *24)+ $interval->h;
		 else
			$resultTime=$interval->h;
		
		$resultTime .=":".$interval->i.":".$interval->s;
		return $resultTime;  
	}
  public function attendanceBiometricDeviceAction(){
    //$this->_helper->layout->disableLayout();
    $modelObjAttendanceMach1 = new Application_Model_AttendanceMachine();
    $modelObjAttendance = new Application_Model_Attendance();
    $modelObjAttendanceMach1->updateTableEmployees();// update all employee code with id number
    $rowAttendaceMac1 = $modelObjAttendanceMach1->getAll('0');
    foreach($rowAttendaceMac1 as $attendanceRow){    
      $inputDate = split(" ", $attendanceRow['date_time']);
      $this->bioEmpId = $attendanceRow['employee_id_db'];
      
      $checkAttendance = "employee_id=".$this->bioEmpId." and time_in like '".$inputDate[0]."%'";
      $checkAttendanceUpdate = array('date_time'=>$attendanceRow['date_time']);
	  
      $rowAttendance = $modelObjAttendance->fetchRow($checkAttendance);      
      if(count($rowAttendance) > 0){
			$timeInArray = split(" ", $rowAttendance['time_in']); 
                        if($rowAttendance['time_out'] == '' || $rowAttendance['time_out'] == NULL || $rowAttendance['time_out'] == 'NULL' || $rowAttendance['time_out'] == 'null' || $rowAttendance['time_out'] == null){
                            $timeOutArray = array($timeInArray[0],'00:00:00');
                        }else{
                            $timeOutArray = split(" ", $rowAttendance['time_out']);
                        }
	  }
      if($attendanceRow['status'] == 0){                   
               
        if(count($rowAttendance)>0){ 
          if($timeInArray[1] != '00:00:00'){
            if($timeOutArray[1] != '00:00:00'){
              $this->updateAttendanceMachine(2, $attendanceRow['date_time']);
            }else if($timeOutArray[1] == '00:00:00'){
              $remarks = 'You checked-in twice(2nd check-in time:'.$attendanceRow['date_time'].')';
              $this->errorHandler($remarks, $checkAttendanceUpdate);
            }
          }else if($timeInArray[1] == '00:00:00'){
            if($timeOutArray[1] != '00:00:00'){
              //$remarks = 'Wrong Entry '.$attendanceRow['date_time'].' Time-In after Time-Out(with no Time-In).';
                $remarks = 'You already checked-out(without Check-In) and trying to check-in at '.$attendanceRow['date_time'];
              $this->errorHandler($remarks, $checkAttendanceUpdate);
            }else if($timeOutArray[1] == '00:00:00'){
              $reasonId = $rowAttendance['reason_id'];
              if($rowAttendance['reason_id'] == 5){
                $reasonId = '';
              }
              $this->updateAttendance(array('time_in'=>$attendanceRow['date_time'], 'reason_id' => $reasonId), $checkAttendance);
            }            
          }          
        }else{ 
          $timeOut = $inputDate[0].' 00:00:00';
          $this->insertAttendance(array('employee_id'=>$attendanceRow['employee_id_db'],'time_in'=>$attendanceRow['date_time'], 'time_out'=>$timeOut),1,$attendanceRow['date_time']);
        }
      }else if($attendanceRow['status'] == 1){ 
        if(count($rowAttendance) > 0){
          if($timeInArray[1] != '00:00:00'){
            if($timeOutArray[1] != '00:00:00'){              
              $remarks = 'You already check out and try to check out again at '.$attendanceRow['date_time'];
              $updateData = array('remarks' => $remarks); 
              $this->errorHandler($remarks, $checkAttendanceUpdate);
              // $this->updateAttendance($updateData, $checkAttendance); 
              //$this->updateAttendanceMachine(3, $attendanceRow['date_time']);
            }else if($timeOutArray[1] == '00:00:00'){
              //New Code              
              $whereTimeout = "employee_id_db=" . $this->bioEmpId . " and date_time like '" . $inputDate[0] . "%' and (status='2') and process = '2'";
              $secondMachineAttendanceRow = $modelObjAttendanceMach1->fetchRow($whereTimeout);
              if(count($secondMachineAttendanceRow)>0){                
                $remarks = 'Break start time ('.$secondMachineAttendanceRow['date_time'].') marked but no break end time marked and trying to Check-out '.$secondMachineAttendanceRow['date_time'];
                $this->errorHandler($remarks, $checkAttendanceUpdate);
              }else{
                $this->updateAttendance(array('time_out'=>$attendanceRow['date_time'], 'hrs'=>$this->timeDateDifference($rowAttendance['time_in'],$attendanceRow['date_time'])), $checkAttendance);
              }
            }
          }else if($timeInArray[1] == '00:00:00'){
            if($timeOutArray[1] != '00:00:00'){
              //$remarks = 'Wrong Entry Duplicate Time-Out without Time-In.';
              $remarks = 'You already checked-out(without Check-In) and trying to check-out at '.$attendanceRow['date_time'];
              $this->errorHandler($remarks, $checkAttendanceUpdate);
            }else if($timeOutArray[1] == '00:00:00'){
              $this->handleNightShiftCase($attendanceRow['date_time']);
            }
          }
        }else if(count($rowAttendance) == 0){
          $this->handleNightShiftCase($attendanceRow['date_time']);
        }              
      }else if ($attendanceRow['status'] == 2) { 
        if(count($rowAttendance) > 0){
          $this->updateAttendanceMachine(2, $attendanceRow['date_time']);
        }else{
          
          $prevDateStr = new DateTime($inputDate[0]);
          $prevDateStr->sub(DateInterval::createFromDateString('1 day'));
          $prevDate = $prevDateStr->format('Y-m-d');

          $prevDayStartBreak = "employee_id_db=" . $this->bioEmpId . " and date_time like '" . $prevDate . "%' and (status='2') and process = '2'";
          $prevDayAttendanceMachineRow = $modelObjAttendanceMach1->fetchRow($prevDayStartBreak);

          $checkAttendancePrevDay = "employee_id=".$this->bioEmpId." and time_in like '".$prevDate."%'";        
          $rowAttendancePrevDay = $modelObjAttendance->fetchRow($checkAttendancePrevDay);
          $prevDayTimeInArray = split(' ', $rowAttendancePrevDay['time_in']);
          $prevDayTimeOutArray = split(' ', $rowAttendancePrevDay['time_out']);
          
          if(count($rowAttendancePrevDay) > 0){ 
            $timeIn = $inputDate[0].' 00:00:00';
            $timeOut = $inputDate[0].' 00:00:00';
            if($prevDayTimeInArray[1] != '00:00:00' && $prevDayTimeOutArray[1] == '00:00:00'){ 
              if(count($prevDayAttendanceMachineRow) == 0){
                $this->updateAttendanceMachine(2, $attendanceRow['date_time']);
              }              
                        } else {
                            $remarks = 'No Check-In exist and trying to mark break at ' . $attendanceRow['date_time'];
                            $this->insertAttendance(array('time_in' => $timeIn, 'time_out'=>$timeOut, 'remarks' => $remarks), 3, $attendanceRow['date_time']);
            }
                    } else {
                        $remarks = 'No Check-In exist and trying to mark break at ' . $attendanceRow['date_time'];
                        $this->insertAttendance(array('time_in' => $timeIn, 'time_out'=>$inputDate[0].' 00:00:00', 'remarks' => $remarks), 3, $attendanceRow['date_time']);
          }        
        }
      }else if ($attendanceRow['status'] == 3) {
        $this->handleBreakCase($inputDate[0], $attendanceRow['date_time'], $checkAttendanceUpdate);
      } 
    }   
    
    die(''); 
	}
	private function handleNightShiftCase($attendanceRowDatTime){
	  $modelObjAttendance = new Application_Model_Attendance();
	  $inputDate = split(" ", $attendanceRowDatTime);
	  
	  $prev_date_temp = new DateTime($inputDate[0]);
	  $prev_date_temp->sub(DateInterval::createFromDateString('1 day'));
	  $prev_date = $prev_date_temp->format('Y-m-d');
	
	  
	  $checkAttendanceLastDay = "employee_id=".$this->bioEmpId." and time_in like '".$prev_date."%'";
	  $rowAttendanceLastDay = $modelObjAttendance->fetchRow($checkAttendanceLastDay);
    if(count($rowAttendanceLastDay) > 0){
      $timeInArrayLastDay = split(" ", $rowAttendanceLastDay['time_in']);
      
      
      
        if($rowAttendanceLastDay['time_out'] == '' || $rowAttendanceLastDay['time_out'] == NULL || $rowAttendanceLastDay['time_out'] == 'NULL' || $rowAttendanceLastDay['time_out'] == 'null' || $rowAttendanceLastDay['time_out'] == null){
            $timeOutArrayLastDay = array($timeInArrayLastDay[0],'00:00:00');
        }else{
            $timeOutArrayLastDay = split(" ", $rowAttendanceLastDay['time_out']);
        }
      
      
      
      /*if($timeInArrayLastDay[1] != '00:00:00'&& $timeOutArrayLastDay[1] == '00:00:00' || $timeOutArray == NULL || $timeOutArray == 'NULL' || $timeOutArray == 'null'){
        
        $this->updateAttendance(array('time_out'=>$attendanceRowDatTime, 'hrs'=>$this->timeDateDifference($rowAttendanceLastDay['time_in'],$attendanceRowDatTime)), $checkAttendanceLastDay);              
      }else if($timeInArrayLastDay[1] != '00:00:00'&& $timeOutArrayLastDay[1] != '00:00:00'){             
       */
      if($timeInArrayLastDay[1] != '00:00:00'&& $timeOutArrayLastDay[1] == '00:00:00'){
        
        $this->updateAttendance(array('time_out'=>$attendanceRowDatTime, 'hrs'=>$this->timeDateDifference($rowAttendanceLastDay['time_in'],$attendanceRowDatTime)), $checkAttendanceLastDay);              
      }else if($timeInArrayLastDay[1] != '00:00:00'&& $timeOutArrayLastDay[1] != '00:00:00'){          
        /*$this->insertAttendance(array('employee_id'=>$employee_id,'time_out'=>$rowAttendanceLastDay['time_out'] ,'remarks'=>'Time out without time in please inform the employ for correction.'),3,$attendanceRowDatTime);*/
        //$this->updateAttendanceMachine(3, $attendanceRowDatTime);
        //casecs#1 no last day row exist.... 
        $remarks ='1:No Check-In exists and Check-out at '.$attendanceRowDatTime;        
        $this->errorHandler($remarks, array('date_time'=>$attendanceRowDatTime));
        
      }else{
           /*casecs#1 no last day row exist.... 
$remarks = 'You already checked-out(without Check-In) and trying to check-out at '.$attendanceRow['date_time'];
              $this->errorHandler($remarks, $checkAttendanceUpdate);
         * 
         *          */
        $remarks ='2:No Check-In exists and Check-out at '.$attendanceRowDatTime;        
        $this->errorHandler($remarks, array('date_time'=>$attendanceRowDatTime));
      }
	  }else{
              
              
              /*casecs#1 no last day row exist.... 
               * 
               * 
               * 
$remarks = 'You already checked-out(without Check-In) and trying to check-out at '.$attendanceRow['date_time'];
              $this->errorHandler($remarks, $checkAttendanceUpdate);
         * 
         *          */
            $remarks ='3:No Check-In exists and Check-out at '.$attendanceRowDatTime;        
            $this->errorHandler($remarks, array('date_time'=>$attendanceRowDatTime));
	  }
  }
	private function handleBreakCase($inputDate, $inputDateTime, $checkAttendanceUpdate) {//die("handle breake case");
    $modelObjAttendanceMach1 = new Application_Model_AttendanceMachine();
    $modelObjAttendance = new Application_Model_Attendance();
    
    $dateTimeArray = split(" ", $inputDateTime);
    // change here
    //$whereTimeout = "employee_id_db=" . $this->bioEmpId . " and date_time like '" . $inputDate . "%' and (status='0) and process = '2'";
    $whereTimeout = "employee_id_db=" . $this->bioEmpId . " and date_time like '" . $inputDate . "%' and (status='2') and process = '2'";
    $attendanceMachineRow = $modelObjAttendanceMach1->fetchRow($whereTimeout);
    
    $attendanceRowWhere = "employee_id=" . $this->bioEmpId . " and time_in like '" . $inputDate . "%'";
    $attendanceRow = $modelObjAttendance->fetchRow($attendanceRowWhere);

    if (count($attendanceMachineRow)>0) {      
      $this->updateAttendanceMachine(1, $attendanceMachineRow['date_time']);
      $this->updateAttendanceMachine(1, $inputDateTime);
      if(count($attendanceRow) == 0){
        $prevDateStr = new DateTime($inputDate);
        $prevDateStr->sub(DateInterval::createFromDateString('1 day'));
        $prevDate = $prevDateStr->format('Y-m-d');       

        $checkAttendancePrevDay = "employee_id=".$this->bioEmpId." and time_in like '".$prevDate."%'";        
        $rowAttendancePrevDay = $modelObjAttendance->fetchRow($checkAttendancePrevDay);
        
        $this->countBreakTime($rowAttendancePrevDay, $attendanceMachineRow, $inputDateTime);
      }else{
        $this->countBreakTime($attendanceRow, $attendanceMachineRow, $inputDateTime);
      }
      
    }else{    
      
      $prevDateStr = new DateTime($inputDate);
      $prevDateStr->sub(DateInterval::createFromDateString('1 day'));
      $prevDate = $prevDateStr->format('Y-m-d');
      
      $prevDayStartBreak = "employee_id_db=" . $this->bioEmpId . " and date_time like '" . $prevDate . "%' and (status='2') and process = '2'";
      $prevDayAttendanceMachineRow = $modelObjAttendanceMach1->fetchRow($prevDayStartBreak);
      
      $checkAttendancePrevDay = "employee_id=".$this->bioEmpId." and time_in like '".$prevDate."%'";        
      $rowAttendancePrevDay = $modelObjAttendance->fetchRow($checkAttendancePrevDay);
      
      if(count($prevDayAttendanceMachineRow)>0){        
        
        $this->updateAttendanceMachine(1, $prevDayAttendanceMachineRow['date_time']);
        $this->updateAttendanceMachine(1, $inputDateTime);
        $attendanceRowWherePrevDay = "employee_id=" . $this->bioEmpId . " and time_in like '" . $prevDate . "%'";
        $attendanceRowPrevDay = $modelObjAttendance->fetchRow($attendanceRowWherePrevDay);      
        $this->countBreakTime($rowAttendancePrevDay,$prevDayAttendanceMachineRow, $inputDateTime);
        
      }else{ 
        
        if(count($attendanceRow)>0){
        
          $remarks = 'No break start time exist and trying to end break at '.$inputDateTime;
          $updateData = array('remarks' => $remarks); 
          $this->errorHandler($remarks, array('date_time'=>$inputDateTime));
          //$this->updateAttendance($updateData, $attendanceRowWhere);        
          
        }else{
          
          $remarks = 'No Check In exist and trying to end Break time '.$inputDateTime;
          $insertData = array('time_in'=>$dateTimeArray[0].' 00:00:00', 'time_out'=>$dateTimeArray[0].' 00:00:00','remarks' => $remarks); 
          $this->insertAttendance($insertData,3, $inputDateTime);
          
        }        
      }
    }
  } 
  private function countBreakTime($attendanceRow, $attendanceMachineRow, $inputDateTime){ 
    $timeInArray = split(' ',$attendanceRow['time_in']);
    $startBreak = $attendanceMachineRow['date_time'];
    $endBreak = $inputDateTime;
    

    $breakTime = $this->timeDateDifference($startBreak, $endBreak); //die($breakTime);

    $breakTimeArray = split(':', $breakTime);
    
    $attendanceRowWhere = "employee_id=" . $this->bioEmpId . " and time_in like '" . $timeInArray[0] . "%'";
    $updateAttendanceData = array('break_in'=>$startBreak,'break_out'=>$endBreak,'break_time' => $breakTime , 'remarks' => 'Taken break of ' . $breakTimeArray[0] . ' Hour and '.$breakTimeArray[1].' Minutes'); 

    $this->updateAttendance($updateAttendanceData, $attendanceRowWhere); 
  }
  private function updateAttendance($data, $where){
            $modelObjAttendance = new Application_Model_Attendance();
            $rowAttendance = $modelObjAttendance->fetchRow($where);
            $fromDate = explode(' ',$rowAttendance['time_in']);
            $office_hours = $this->getHelper('CommonFunctions')->calculateOfficeHours(array('empId'=>$this->bioEmpId,'dateTime'=>$fromDate[0]));
            //$office_hours = $this->calculateOfficeHours($rowAttendance['time_in']);
            
              /*$settingsModel = new Application_Model_Settings();
              $value1 = $settingsModel->fetchRow("param ='office_timein' ");
              $value2 = $settingsModel->fetchRow("param ='office_timeout' ");

              $time1 = $value1['value'];
              $time2 = $value2['value'];

              $office_hours = $this->timeDateDifference($time1, $time2);*/
              $defaultData['office_hrs'] = $office_hours;
            //echo 'helooo<pre>';print_r($rowAttendance['remarks']);die("king");
            $defaultData['employee_id'] = $this->bioEmpId;
            if($data['time_in']!=''){
                    $dateTime = $defaultData['time_in'] = $data['time_in'];			
            }
            if($data['time_out']!=''){
                    $dateTime = $defaultData['time_out'] = $data['time_out'];
                    if($rowAttendance['break_time'] != '00:00:00'){
                      $totalHours = $this->timeDateDifference($data['hrs'], $rowAttendance['break_time']); //die("total hrs = ".$totalHours);
                    }else{
                      $totalHours = $data['hrs'];
                    }
                    $defaultData['hrs'] = $totalHours;//$data['hrs'];
                    //$defaultData['hrs'] = $data['hrs'];
            }
            if($data['remarks']!=''){
				if(trim($rowAttendance['remarks'])=='')
					$defaultData['remarks'] = $data['remarks'];
				else
					$defaultData['remarks'] = $rowAttendance['remarks'].'. '.$data['remarks'];
            } 
            if(isset($data['reason_id'])){
              $defaultData['reason_id'] = $data['reason_id'];
            }
            if($data['break_time'] != ''){
              if($rowAttendance['break_time']!= '00:00:00'){
                $timeArray = split(':', $data['break_time']);
                $timeVar = 'PT'.$timeArray[0].'H'.$timeArray[1].'M'.$timeArray[2].'S';
                $dateTimeObj = new DateTime($rowAttendance['break_time']);
                $dateTimeObj->add(new DateInterval($timeVar));//Standard parameter "PT3H30M30S" 
                $totalBreak = $dateTimeObj->format('H:i:s');
                $defaultData['break_time'] = $totalBreak;
                $defaultData['break_in'] = $rowAttendance['break_in'].",".$data['break_in'];
                $defaultData['break_out'] = $rowAttendance['break_out'].",".$data['break_out'];
              }else{
                $defaultData['break_time'] = $data['break_time'];
                $defaultData['break_in'] = $data['break_in'];
                $defaultData['break_out'] = $data['break_out'];
              }
            }
            $modelObjAttendance = new Application_Model_Attendance();
            $modelObjAttendance->update($defaultData, $where);

            $this->updateAttendanceMachine(1, $dateTime);
        }
  private function insertAttendance($data,$processState,$attendanceRowDateTime){
      $fromDate = explode(' ',$data['time_in']);
      $officeHours = $this->getHelper('CommonFunctions')->calculateOfficeHours(array('empId'=>$this->bioEmpId,'dateTime'=>$fromDate[0]));
  //$officeHours = $this->calculateOfficeHours($attendanceRowDateTime);
	$defaultData=array( 
                    'employee_id'=>$this->bioEmpId,
                    'time_in'=>($data['time_in']!='')?$data['time_in']:'',
                    'time_out'=>($data['time_out']!='')?$data['time_out']:'',
                    'remarks'=>($data['remarks']!='')?$data['remarks']:'',
                    'reason_id'=>0,
                    'hrs'=>'00:00:00',
                    'office_hrs'=>$officeHours,
                    'created_date'=>date('Y-m-d H:i:s')
                );
    $modelObjAttendance = new Application_Model_Attendance();
    $modelObjAttendance->insert($defaultData);
	
    $this->updateAttendanceMachine($processState, $attendanceRowDateTime);
  }
  private function updateAttendanceMachine($process, $date_time){// echo "==>".$process."==>".$employee_id."==>".$date_time;die;
    $modelObjAttendanceMach1 = new Application_Model_AttendanceMachine();
    $modelObjAttendanceMach1->update(array('process'=>$process),"employee_id_db=".$this->bioEmpId." and date_time='".$date_time."'");
  }
  
  private function errorHandler($remarks, $checkAttendanceUpdate){
    $updateAttendanceMachineParm = $inputDate = split(" ", $checkAttendanceUpdate['date_time']);
    $where = "employee_id=".$this->bioEmpId." and time_in like '".$inputDate[0]."%'";   
    
    $modelObjAttendance = new Application_Model_Attendance();
    $rowAttendance = $modelObjAttendance->fetchRow($where);
    
    if(count($rowAttendance) > 0){    
        $this->updateAttendance(array('remarks'=>$remarks), $where);
    }else{
        $time = $inputDate[0].' 00:00:00';
        $this->insertAttendance(array('time_in'=>$time,'time_out'=>$time,'remarks'=>$remarks),3,$checkAttendanceUpdate['date_time']);
    }    
    
    $this->updateAttendanceMachine(3,$checkAttendanceUpdate['date_time']);
	
  }
  private function calculateOfficeHours($dateTime){    
    $modelObjAttendance = new Application_Model_Attendance();
    $employeeTimingsModel = new Application_Model_EmployeeTimings();
    
    $inputDate = split(" ", $dateTime);
    $checkAttendance = "employee_id=".$this->bioEmpId." and time_in like '".$inputDate[0]."%'";

    $rowAttendance = $modelObjAttendance->fetchRow($checkAttendance); 
                    
    $employeeTimingsModelData = $employeeTimingsModel->fetchRow(" employee_id ='" . $this->bioEmpId."' and recent_record='1'");

    if(count($employeeTimingsModelData) > 0){
      if($employeeTimingsModelData['time_in']!="NULL" && $employeeTimingsModelData['time_out']!="NULL"){
        $end = $employeeTimingsModelData['time_out'];
        $start = $employeeTimingsModelData['time_in'];
        $office_hrs=explode(":",$this->getTimeDiff($start,$end));
        $hrs = $office_hrs[0];
        $min = $office_hrs[1];       
      }
    } else {    
      $settingsModel = new Application_Model_Settings();
      $value1 = $settingsModel->fetchRow("param ='office_timein' ");
      $value2 = $settingsModel->fetchRow("param ='office_timeout' ");

      $time1 = $value1['value'];
      $time2 = $value2['value'];
      
      $hours = $this->timeDateDifference($time1, $time2);//echo strtotime($hours);die;
      $hoursArray = split(':', $hours);

       $hrs = $hoursArray[0];
       $min = $hoursArray[1];
       $sec = $hoursArray[2];       
       //$sec=$sec%60;
    }
    //$total_working_hours=$hrs.":".$min;
    if($rowAttendance > 0 && $rowAttendance['reason_id']){
      if($rowAttendance['reason_id']==13){
        $hrs-=4;
        $total_working_hours = array($hrs,$min);
      } else if($rowAttendance['reason_id']==14){
        $hrs-=2;
        $total_working_hours = array($hrs,$min);
      } else if($rowAttendance['reason_id']==4 || $rowAttendance['reason_id']==5){
        /*if($formData[$emRow]['hrs']=="00:00:00")
          $total_working_hours="00:00:00";
        else*/
          $total_working_hours = array($hrs,$min);
      } else if($data['reason']!="0")
        $total_working_hours = array('00','00');
      else
        $total_working_hours = array($hrs,$min);
    }else
      $total_working_hours = array($hrs,$min);
      $totalWorkingHours = $this->calculateTotalHours($total_working_hours);
   
	  return $totalWorkingHours;
  }
  
  /*

 * This below funciton is useless. Now this is done through cron 

  */
  private function emptyAttendanceMachineTableAction(){
    $this->_helper->layout->disableLayout();
    $modelObjAttendanceMach1 = new Application_Model_AttendanceMachine();
    $modelObjAttendance = new Application_Model_Attendance();
    $rowAttendaceMac1 = $modelObjAttendanceMach1->getAll(2);
    
    //$whereDeleteAttendanceMach = array('process!=2');
    //$modelObjAttendanceMach1->delete($whereDeleteAttendanceMach);
    $rowAttendaceMac1 = $modelObjAttendanceMach1->fetchAll();    
    foreach($rowAttendaceMac1 as $attendanceRow){
      $inputDate = split(" ", $attendanceRow['date_time']);
      $prev_date = date('Y-m-d', strtotime($inputDate[0]) - 86400);
      //echo $prev_date;die;
      $checkAttendance = "employee_id=".$attendanceRow['employee_id']." and time_in like '".$inputDate[0]."%'";
      $rowAttendance = $modelObjAttendance->fetchRow($checkAttendance);
      if($attendanceRow['status'] == 0 && $attendanceRow['process'] == '2'){
        $whereTimeout = array("employee_id=".$attendanceRow['employee_id']." and date_time like '".$inputDate[0]."%' and status='1' and process = '2'");
        $secondAttendanceRow = $modelObjAttendanceMach1->fetchRow($whereTimeout);
        $firstTimein = strtotime($rowAttendance['time_in']);
        $firstTimeout = strtotime($rowAttendance['time_out']);  
        $secondTimein = strtotime($attendanceRow['date_time']);
        $breakeTime = $secondTimein - $firstTimeout;
        $break = date('H:i:s', $breakeTime);
        $secondTimeout = strtotime($secondAttendanceRow['date_time']);
        $totalTime1 = $secondTimeout-$firstTimein;//echo ($secondTimeout-$firstTimein-$breakeTime)."=========";
        $totalTime = $totalTime1-$breakeTime;//echo $totalTime;
        $hrs = date('H:i:s', $totalTime);//echo $attendanceRow['employee_id']."firsttimein = ".$firstTimein." firsttimeout = ".$firstTimeout.'=>'.$rowAttendance['time_out'].' secondtimein = '.$secondTimein.'=>'.$attendanceRow['date_time'].' secondtimeout = '.$secondTimeout.'=>'.$secondAttendanceRow['date_time'].' break time = '.$breakeTime.' break= '.$break.' Total time= '.$totalTime1.' Total hrs= '.$hrs; die("==== here");
        $updateTimeout = array('time_out'=>$secondAttendanceRow['date_time'], 'remarks'=>'Taken Break of '.$break.'hours', 'hrs'=>$hrs);
        $modelObjAttendance->update($updateTimeout, $checkAttendance);
        $updateProcessWhere = "employee_id=".$attendanceRow['employee_id']." and date_time='".$attendanceRow['date_time']."'";
        $update = array('process'=>1);
        //$modelObjAttendanceMach1->update($update, $updateProcessWhere);
        $modelObjAttendanceMach1->update($update, $updateProcessWhere);die("here");
      }

    }
  }
  public function cronTodaysDidnotInformCasesAction(){
        //$testDate = date("Y-m-d H:i:s");
        //$emailStatus = Application_Action_Helper_Mail::testEmailSohail($testDate." Cron Today Attendance","This is testing email send to Soahil and Salman at ".$testDate);
        
        $settingsModel = new Application_Model_Settings();
        $fieldOneRow = $settingsModel->fetchRow("param = 'exclude-employee-from-report'");

        $excludeEmployeeNightShift = explode(',', $fieldOneRow->value);

        $attendanceModel = new Application_Model_Attendance();
        $employeeRows = $attendanceModel->getEmployeesAttendanceNotMarked();

        $date =  date("Y-m-d");
        if(!$this->getHolidayFlag($date)){
            foreach ($employeeRows as $emRow) {
                $data = array();
                $data['date'] = $date;
                $data['time_in'] = "00:00";
                $data['time_out'] = $date." 00:00";
                $data['remarks'] = "";
                if(in_array($emRow["e.id"], $excludeEmployeeNightShift))
                    $data['reason'] = 0;
                else
                    $data['reason'] = 5;
                $data['hrs'] = "00:00:00";
                $data['newdate'] = $date;
                $data['created_date'] = $date;

                $data['office_hrs'] = "00:00:00";
                $this->markAttendance($emRow["e.id"], $data);
            }
        }
        //$emailStatus = Application_Action_Helper_Mail::testEmailSohail($testDate." Cron Today Attendance2","This is testing email send to Soahil and Salman at ".$testDate);
        die('');
  }
    public function cronTodaysAttendanceReportAction(){
        //$this->_helper->layout->disableLayout();
        $date =  date("Y-m-d");
        if(!$this->getHolidayFlag($date)){
            $htmlContent = $this->generateTodaysUpdate($date);


            if(date("g")<3)
                $subject = "Today's Update Report ".$date;
            else
                $subject = "Final Today's Update Report ".$date;

            $emailStatus = Application_Action_Helper_Mail::sendTodaysUpdateEmail($subject,$htmlContent);
        }
        //var_dum($emailStatus);
        die();
    }
    /*
     *Old handle breake case 
     *      */
   /* private function handleBreakCase($inputDate,$inputDateTime,$checkAttendanceUpdate){	
	  $modelObjAttendanceMach1 = new Application_Model_AttendanceMachine();
    $modelObjAttendance = new Application_Model_Attendance();
	  $whereTimeout = "employee_id_db=".$this->bioEmpId." and date_time like '".$inputDate."%' and status='0' and process = '2'";
	  $secondMachineAttendanceRow = $modelObjAttendanceMach1->fetchRow($whereTimeout);
	  if(count($secondMachineAttendanceRow)){
      
      $this->updateAttendanceMachine(1, $secondMachineAttendanceRow['date_time']); 
      $attendanceRowWhere = "employee_id=".$this->bioEmpId." and time_in like '".$inputDate."%'";
      $attendanceRow = $modelObjAttendance->fetchRow($attendanceRowWhere);
      
      $firstTimein = $attendanceRow['time_in'];
      $firstTimeout = $attendanceRow['time_out'];  
      $secondTimein = $secondMachineAttendanceRow['date_time'];
      $secondTimeout = $inputDateTime; 
      
      $breakeTime = $this->timeDateDifference($firstTimeout,$secondTimein);
      
	  
	  
	  $temporarayDateSplit = split(" ", $firstTimeout);
	  
      $actualTimeOut = $this->timeDateDifference($temporarayDateSplit[0].' '.$breakeTime,$secondTimeout);
      $totalTimeSpend = $this->timeDateDifference($firstTimein,$temporarayDateSplit[0].' '.$actualTimeOut);
	  
      
      $updateData = array('time_out'=>date('Y-m-d',strtotime($secondTimeout)).' '.$actualTimeOut,'hrs'=>$totalTimeSpend,'remarks'=>'Take break of time '.$breakeTime);//echo $attendanceRowWhere.">>>>>";  print_r($updateData);die(" here");   
      $this->updateAttendance($updateData, $attendanceRowWhere);    
	  }else{
      	//$remarks = 'Wrong Entry '.$inputDateTime.' Time-In after Time-Out(with no Time-In).';
        $remarks = 'You already checked-out and trying to check-out at '.$inputDateTime;
      	$this->errorHandler($remarks, $checkAttendanceUpdate);
	  }
	}*/
    
    
    public function cronSendWeeklyAttendaceEmployeeAction(){
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        // Only run if today is Monday otherwise do not execute cron
//        if(date('D') != 'Mon'){
//            echo 'Today is not Monday';
//            exit;
//        }
        $employeeModel = new Application_Model_Employees();
        $employeeSelect = $employeeModel->select()
                          ->where("status='Active'")
                          ->where("current_job_status NOT IN ('Resigned','Terminated')")
                          ->order("name asc");
        $employees = $employeeModel->fetchAll($employeeSelect);
        $date = date('Y-m-d', strtotime('-7 days'));
        $week_number = date('W', strtotime($date));
        $year = date('Y', strtotime($date));
        $datesInWeek = array();
        for ($day = 1; $day <= 6; $day++) {
            $datesInWeek[$day] = date('Y-m-d', strtotime($year . "W" . $week_number . $day));
        }
//        Zend_Controller_Action_HelperBroker::addHelper(new Application_Action_Helper_Filter());
//        $locationNames = $this->getLocationString($location);
//        $designationNames = $this->getDesignationString($designation);
        
        foreach($employees as $emp){
            $empId = $emp['id'];
            
            $db = Zend_Db_Table::getDefaultAdapter();
            $saturdayAttRecord = $db->fetchRow("select time_in,time_out,hrs,reason_id,office_hrs,break_time from attendance where employee_id='" . $empId . "' and time_in like '%" . $datesInWeek[6] . "%'");
            $saturdayOnn = false;
            if($saturdayAttRecord)
                $saturdayOnn = true;
            $htmlContent = 'Hi '.$emp['name'].",<br/><br/>Following is your Weekly Attendance Report";
            $htmlContent .= $report= $this->generateWeeklyReport($date,$empId, $emp['current_location_id'], $emp['current_designation_id'],true);
            
            $htmlContent .= '<br/><br/>This mail is auto generated by NextHRM system.If you have any concerns please Contact HR';
            $subject = 'Weekly Attendance Report for '.$emp['name'].' ['.$date.' - '.($saturdayOnn?$datesInWeek[6]:$datesInWeek[5]).']';
            Application_Action_Helper_Mail::sendCustomEmail($empId,$subject,$htmlContent,false);
        }
        
        exit;
    }
    
    public function cronSendWeeklyAttendaceReporttoAction(){
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        // Only run if today is Monday otherwise do not execute cron
//        if(date('D') != 'Mon'){
//            echo 'Today is not Monday';
//            exit;
//        }
        $employeeModel = new Application_Model_Employees();
        $employeeSelect = $employeeModel->select()
                          ->where("status='Active'")
                          ->where("current_job_status NOT IN ('Resigned','Terminated')")
                          ->order("name asc");
        $employees = $employeeModel->fetchAll($employeeSelect);
        $date = date('Y-m-d', strtotime('-7 days'));
        $week_number = date('W', strtotime($date));
        $year = date('Y', strtotime($date));
        $datesInWeek = array();
        for ($day = 1; $day <= 6; $day++) {
            $datesInWeek[$day] = date('Y-m-d', strtotime($year . "W" . $week_number . $day));
        }
        $lm1_lm2_lm3=array();
        
        foreach($employees as $emp){
            $empId = $emp['id'];
            
            $db = Zend_Db_Table::getDefaultAdapter();
            $saturdayAttRecord = $db->fetchRow("select time_in,time_out,hrs,reason_id,office_hrs,break_time from attendance where employee_id='" . $empId . "' and time_in like '%" . $datesInWeek[6] . "%'");
            $saturdayOnn = false;
            if($saturdayAttRecord)
                $saturdayOnn = true;
            
            $lm1_lm2_lm3[$emp['current_supervisor_id']][]= $empId;
            if($saturdayOnn)
                $lm1_lm2_lm3[$emp['current_supervisor_id']]['saturday']= true;
            
            $lm1_lm2_lm3[$emp['current_teamlead_id']][]= $empId;
            if($saturdayOnn)
                $lm1_lm2_lm3[$emp['current_teamlead_id']]['saturday']= true;
            
            $lm1_lm2_lm3[$emp['current_supervisor_id_2']][]= $empId;
            if($saturdayOnn)
                $lm1_lm2_lm3[$emp['current_supervisor_id_2']]['saturday']= true;
        }
        ksort($lm1_lm2_lm3);
        foreach($lm1_lm2_lm3 as $key=>$data){
            if($key!=''){
                $htmlContent ='';
                $saturdayOnn = false;
                if($lm1_lm2_lm3[$key]['saturday']){
                    $saturdayOnn = true;
                    unset($lm1_lm2_lm3[$key]['saturday']);
                }
                $subject = 'Weekly Attendance Report of Employees ['.$date.' - '.($saturdayOnn?$datesInWeek[6]:$datesInWeek[5]).']';
                $htmlContent .= 'Hi,<br/><br/>Following is Weekly Attendance Report for your employees';
                
                $empIds = implode(",",$lm1_lm2_lm3[$key]);
                
                $htmlContent .= $this->generateWeeklyReport($date,$empIds, '', '',true);
                
                $htmlContent .= '<br/><br/>This mail is auto generated by NextHRM system.If you have any concerns please Contact HR';
                Application_Action_Helper_Mail::sendCustomEmailReportTo($key,$subject,$htmlContent);
            }
        }
        
        exit;
    }
    
}