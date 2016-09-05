<?php

class AdministrationController extends Zend_Controller_Action {

    protected $arrSettings, $controller;

    /**
     * ROLE
     * @index General Tab
     */
    public function indexAction() {
        $general = array(
            "administration/bank" => "Adds new banks to the application for employee salary accounts info.",
            "administration/bank-branch" => "Adds new bank branches to the application for employee bank account management.",
            "city" => "It have a city list.",
            "administration/company-docs" => "It have a company documents list.",
            "department" => "All the departments list",
            "administration/degree" => "Adds new degree to the application for employee education info.",
            "designation" => "It has all the designations list",
            "division" => "All the divisions list",
            "education-type" => "This table cantains the record about the education like F.A, BCS, MCS and etc.",
            "administration/employer" => "Adds new employers to the application.",
            "administration/institute" => "Adds new Institutes to the application.",
            "administration/job-title" => "Adds new Job Titles to the application.",
            "language-type" => "In which the language will be stored which is convinent for user.",
            "administration/leave-reason" => "Adds new leave reason to the reason control in leave form.",
            "location" => "In which user can store his/her house location information.",
            "news" => 'It add news to the news segment.',
            "relationship" => 'All relationship will be shown in this.',
            "skill-type" => "This table have record about the skills, which a person have very good command.",
            "state" => "All the states list",
            "workspace" => "All the workspaces list",
            //"administration/find-attendance-and-leave-problems" => "Find problems in Attendance / Leaves",
            "administration/find-attendance-leave-problems" => "Find problems in Attendance and leave"
//            "administration/find-leave-problems" => "Find problems in Leaves",
//            // "administration/fix-attendance" => "Find and Fix problems in Attendance / Leaves",
//            "administration/fix-attendance-problems" => "Find and Fix problems in Attendance"
                //"administration/fix-attendance" => "Fix The Attendance Problems to stable the system"
        );

        $this->view->list = $general;
    }

    /**
     * ROLE
     * @management User Management Tab
     */
    public function managementAction() {
        $employee = array(
            "acl-user-role/roles" => "It store the different user roles.",
            "user" => "Manage users",
            "section" => "Manage sections",
            "administration/update-resources" => "Update Resources"
        );
        $this->view->list = $employee;
    }

    public function attendanceNotificationTimeAction() {

        $messages = array();


        if ($this->_request->update == 's') {
            $messages[] = array('success', 'Attendance Notification Time Updated Successfully');
        }

        $this->view->messages = $messages;

        $settingsModel = new Application_Model_Settings();
        $fieldOneRow = $settingsModel->fetchRow("param = 'attendance_notification_time'");

        $attendanceNotificationFrom = new Application_Form_AttendanceNotificationTime();
        //$userModel = new Application_Model_Dateformat();
        $attendanceNotificationFrom->setMethod('post');
        $attendanceNotificationFrom->setName('attendaceNotificationTime');
        $attendanceNotificationFrom->setAction('administration/attendance-notification-time');

        $attendanceNotificationFrom->getElement('attendaceNotificationTimefield')->setValue($fieldOneRow->value);

        $attendanceNotificationFrom->setAttrib('class', 'form-horizontal clearfix');
        $this->view->form = $attendanceNotificationFrom;

        $this->view->controller = $this->getRequest()->getControllerName();

        if ($this->_request->isPost()) {
            $notificationtime = $this->_request->getPost('attendaceNotificationTimefield');
            $data = array("value" => $notificationtime);
            $settingsModel->update($data, "param='attendance_notification_time'");

            $this->_redirect('administration/attendance-notification-time/update/s');
        }
    }

    public function excludeEmployeeFromReportAction() {
        $this->view->inc_autoSuggest = 'selectEmployeeAll';
        $this->view->inc_tiny_mce = true;
        $this->view->inc_suggest = true;
        $messages = array();

        $storage = new Zend_Auth_Storage_Session();
        $user_id = $storage->read()->id;

        if ($this->_request->update == 's') {
            $messages[] = array('success', 'Updated Successfully');
        } elseif ($this->_request->error == 'e') {
            $messages[] = array('error', 'Select some Employee');
        }

        $this->view->messages = $messages;

        $settingsModel = new Application_Model_Settings();
        $fieldOneRow = $settingsModel->fetchRow("param = 'exclude-employee-from-report'");
        $this->view->empId = (empty($fieldOneRow->value) ? 'none' : $fieldOneRow->value);

        if ($this->_request->isPost()) {
            $employeeExclude = $this->_request->getPost('selectedEmployeeValAll');
            if ($employeeExclude == 'none' || $employeeExclude == "all") {
                $this->_redirect('administration/exclude-employee-from-report/error/e');
            } else {
                //$employeeExclude = substr($employeeExclude,0,strlen($employeeExclude)-1);
                $data = array("value" => $employeeExclude);
                $settingsModel->update($data, "param='exclude-employee-from-report'");
                $this->_redirect('administration/exclude-employee-from-report/update/s');
            }
        }
    }

    public function todaysAttendanceReportRecipientsAction() {
        $this->view->inc_autoSuggest = 'selectEmployeeto,selectEmployeecc';
        $this->view->inc_tiny_mce = true;
        $this->view->inc_suggest = true;
        $messages = array();

        $storage = new Zend_Auth_Storage_Session();
        $user_id = $storage->read()->id;

        if ($this->_request->update == 's') {
            $messages[] = array('success', 'Updated Successfully');
        } elseif ($this->_request->error == 'e') {
            $messages[] = array('error', 'Select To Recipients');
        }

        $this->view->messages = $messages;

        $settingsModel = new Application_Model_Settings();
        $fieldOneRow = $settingsModel->fetchRow("param = 'todays-attendance-report-recipients-to'");
        $this->view->empIdTo = (empty($fieldOneRow->value) ? 'none' : $fieldOneRow->value);

        $fieldOneRow = $settingsModel->fetchRow("param = 'todays-attendance-report-recipients-cc'");
        $this->view->empIdCC = (empty($fieldOneRow->value) ? 'none' : $fieldOneRow->value);

        $fieldOneRow = $settingsModel->fetchRow("param = 'todays-attendance-report-extra-recipients'");
        $this->view->extraRecipients = (empty($fieldOneRow->value) ? '' : $fieldOneRow->value);


        if ($this->_request->isPost()) {
            $recipientTo = $this->_request->getPost('selectedEmployeeValto');
            $recipientCC = $this->_request->getPost('selectedEmployeeValcc');
            $extraRecipientTo = $this->_request->getPost('extraRecipients');
            if ($recipientTo == 'none' || $recipientTo == "all") {
                $this->_redirect('administration/todays-attendance-report-recipients/error/e');
            } else {
                //$employeeExclude = substr($employeeExclude,0,strlen($employeeExclude)-1);
                $data = array("value" => $recipientTo);
                $settingsModel->update($data, "param='todays-attendance-report-recipients-to'");

                $data = array("value" => $recipientCC);
                $settingsModel->update($data, "param='todays-attendance-report-recipients-cc'");

                $data = array("value" => $extraRecipientTo);
                $settingsModel->update($data, "param='todays-attendance-report-extra-recipients'");

                $this->_redirect('administration/todays-attendance-report-recipients/update/s');
            }
        }
    }

    /**
     * ROLE
     * @profile Settings Tab
     */
    public function officeTimingsAction() {

        $messages = array();


        if ($this->_request->update == 's') {
            $messages[] = array('success', 'Office Timing Updated Successfully');
        }

        $this->view->messages = $messages;

        // $storage = new Zend_Auth_Storage_Session();
        //$modified_by = $storage->read()->id;

        $settingsModel = new Application_Model_Settings();
        $fieldOneRow = $settingsModel->fetchRow("param = 'office_timein'");
        $fieldTwoRow = $settingsModel->fetchRow("param = 'office_timeout'");

        $OfficeTimingsFrom = new Application_Form_OfficeTimings();
        //$userModel = new Application_Model_Dateformat();
        $OfficeTimingsFrom->setMethod('post');
        $OfficeTimingsFrom->setName('officetimings');
        $OfficeTimingsFrom->setAction('administration/office-timings');

        $OfficeTimingsFrom->getElement('starttime')->setValue($fieldOneRow->value);
        $OfficeTimingsFrom->getElement('endtime')->setValue($fieldTwoRow->value);

        $OfficeTimingsFrom->setAttrib('class', 'form-horizontal clearfix');
        $this->view->form = $OfficeTimingsFrom;

        $this->view->controller = $this->getRequest()->getControllerName();

        if ($this->_request->isPost()) {
            $starttime = $this->_request->getPost('starttime');
            $endtime = $this->_request->getPost('endtime');
            $data = array("value" => $starttime);
            $settingsModel->update($data, "param='office_timein'");
            $data = array("value" => $endtime);
            $settingsModel->update($data, "param='office_timeout'");

            $this->_redirect('administration/office-timings/update/s');
        }
    }

    public function markLeaveDateAction() {
//        echo 'hi mark leave';exit;
        $messages = array();


        if ($this->_request->update == 's') {
            $messages[] = array('success', 'Mark Leave Date Updated Successfully');
        }

        $this->view->messages = $messages;

        $settingsModel = new Application_Model_Settings();
        $fieldOneRow = $settingsModel->fetchRow("param = 'date_limit'");



        $MarkLeaveDate = new Application_Form_MarkLeaveDate();
        $MarkLeaveDate->setMethod('post');
        $MarkLeaveDate->setName('markleavedate');
        $MarkLeaveDate->getElement("dates")->setValue($fieldOneRow['value']);

        $MarkLeaveDate->setAttrib('class', 'form-horizontal clearfix');
        $this->view->form = $MarkLeaveDate;

        $this->view->controller = $this->getRequest()->getControllerName();

        if ($this->_request->isPost()) {

            $date = $this->_request->getPost('dates');

            $data = array("value" => $date);
            $settingsModel->update($data, "param='date_limit'");

            $this->_redirect('administration/mark-leave-date/update/s');
        }
    }

    /* 	public function fixAttendanceAction(){
      error_reporting(E_ALL);
      if($this->_request->fromdate){
      try{
      $modelEmployee= new Application_Model_Employees();
      //$allEmployees=$modelEmployee->fetchAll(" job_status not in ('Terminated','Resigned') and current_job_status not in ('Terminated','Resigned')")->toArray();
      $allEmployee=$modelEmployee->fetchAll("number = 01593")->toArray();
      $from_date=strtotime($this->_request->fromdate);//date("Y-m-d",(time() -  (86400 * 120)) );
      $to_date=strtotime($this->_request->todate);//date("Y-m-d",time() );
      $this->view->fromdate=date("Y-m-d",$from_date);
      $this->view->todate=date("Y-m-d",$to_date);
      $check_date=$from_date;
      $str = array();$i = 1;
      $reportName = "Attendance Fixation Report";
      while($check_date <= $to_date){
      $date = date("Y-m-d",  $check_date);
      // foreach($allEmployees as $emp){
      $emp = $allEmployee[0];
      $f_flag = 0;
      $modelLeave= new Application_Model_Leave();
      $curdateLeaves=$modelLeave->fetchAll(" from_date like '%".$date."%' and employee_id=".$emp['id'])->toArray();
      if(count($curdateLeaves) > 0){
      $curdateLeaves=$curdateLeaves[0];
      }
      $modelAttendance= new Application_Model_Attendance();
      $curdateAttendance=$modelAttendance->fetchAll(" time_in like '%".$date."%' and employee_id=".$emp['id'])->toArray();
      if(count($curdateAttendance) > 0){
      $curdateAttendance=$curdateAttendance[0];
      }
      if($curdateLeaves && $curdateLeaves['leave_type']!='short_leave_availed' && $curdateLeaves['leave_type']!='half_leave_availed'){
      $f_flag = 1;
      if($curdateAttendance){
      $d=explode(" ",$curdateAttendance['time_in']);
      if($curdateAttendance && $d[1]!="00:00:00"){

      $reason = "Attendance is Marked";
      $date1 = strtotime($date);
      $date1 = date("d-M-Y",$date1);
      if($curdateLeaves['duration'] == 1){
      $str[$i] = $emp['number'].",".$emp['name'].",Attendance Should Not be Marked in the case of <b>".$curdateLeaves['leave_type']." </b>by <b>".$emp['name']." </b>on date ".$date1.".,".$reason.",".$curdateAttendance['time_in'].",".$curdateAttendance['time_out'].",".$curdateLeaves['reason_id'].",".$curdateAttendance['id'].",".$f_flag.",".$curdateLeaves['duration'];
      // $str[$i] = $emp['number'].",".$emp['name'].",Attendance Should Not be Marked in the case of <b>".$curdateLeaves['leave_type']." </b>by <b>".$emp['name']." </b>on date ".$date1.".,".$reason;
      $i++;
      }else{
      $date1 = strtotime($date);
      $date = date("d-M-Y",$date1);
      $date1 = $date1 + $curdateLeaves['duration']*84200;
      $date1 = date("d-M-Y",$date1);
      $str[$i] = $emp['number'].",".$emp['name'].",Attendance Should Not be Marked in the case of <b>".$curdateLeaves['leave_type']."</b> by <b>".$emp['name']." </b>From date ".$date." To date ".$date1." for ".$curdateLeaves['duration']." days.,".$reason.",".$curdateAttendance['time_in'].",".$curdateAttendance['time_out'].",".$curdateLeaves['reason_id'].",".$curdateAttendance['id'].",".$f_flag.",".$curdateLeaves['duration'];
      //$str[$i] = $emp['number'].",".$emp['name'].",Attendance Should Not be Marked in the case of <b>".$curdateLeaves['leave_type']."</b> by <b>".$emp['name']." </b>From date ".$date." To date ".$date1." for ".$curdateLeaves['duration']." days.,".$reason;
      $i++;

      }
      }
      }
      }
      else if($curdateLeaves && ($curdateLeaves['leave_type']=='short_leave_availed' || $curdateLeaves['leave_type']=='half_leave_availed'  )){
      $d=explode(" ",$curdateAttendance['time_in']);
      $date1 = strtotime($date);
      $date1 = date("d-M-Y",$date1);
      if((!$curdateAttendance || $d[1]== "00:00:00")&&$curdateAttendance['reason_id']==0){
      $f_flag = 0;
      $reason = "Attendance is not Marked and reason is not set";
      $str[$i] = $emp['number'].",".$emp['name'].",Attendance should be Marked in the case of<b> ".$curdateLeaves['leave_type']." </b>by <b>".$emp['name']." </b>on date ".$date1.'.,'.$reason.",".$curdateAttendance['time_in'].",".$curdateAttendance['time_out'].",".$curdateLeaves['reason_id'].",".$curdateAttendance['id'].",".$f_flag.",".$curdateLeaves['duration'];
      //$str[$i] = $emp['number'].",".$emp['name'].",Attendance should be Marked in the case of<b> ".$curdateLeaves['leave_type']." </b>by <b>".$emp['name']." </b>on date ".$date1.'.,'.$reason;
      $i++;
      }
      else if($curdateAttendance && $curdateAttendance['reason_id']==0){
      $f_flag =2;
      $reason = "Leave Reason is not given in Attendance";
      $str[$i] = $emp['number'].",".$emp['name'].",Leave reason is not set in <b>Attendance section</b> for the employee<b> ".$emp['name']." </b>on date ".$date1.".It should be ".$curdateLeaves['leave_type'].",".$reason.",".$curdateAttendance['time_in'].",".$curdateAttendance['time_out'].",".$curdateLeaves['reason_id'].",".$curdateAttendance['id'].",".$f_flag.",".$curdateLeaves['duration'];
      //$str[$i] = $emp['number'].",".$emp['name'].",Leave reason is not set in <b>Attendance section</b> for the employee<b> ".$emp['name']." </b>on date ".$date1.".It should be ".$curdateLeaves['leave_type'].",".$reason;
      $i++;
      }
      }
      else if($curdateAttendance && $curdateAttendance['reason_id'] != 0 && !$curdateLeaves){
      $f_flag = 0;
      $reason = "Leave is not Marked";
      $date1 = strtotime($date);
      $date1 = date("d-M-Y",$date1);
      //$leavetype= $curdateAttendance['reason_id']==14 ? "Short Leave" : "Half Leave";
      if($time_in == '00:00:00'){
      $str[$i] = $emp['number'].",".$emp['name'].",<b>Leave </b>Not marked by<b> ".$emp['name']." </b>on date ".$date1.' To Fix Employee should mark Leave.,' .$reason.",".$curdateAttendance['time_in'].",".$curdateAttendance['time_out'].",".$curdateLeaves['reason_id'].",".$curdateAttendance['id'].",".$f_flag.",".$curdateLeaves['duration'];
      $i++;
      }else{
      $str[$i] = $emp['number'].",".$emp['name'].",<b>Shot/Half Leave </b>Not marked by<b> ".$emp['name']." </b>on date ".$date1.' To Fix Employee should mark Leave.,' .$reason.",".$curdateAttendance['time_in'].",".$curdateAttendance['time_out'].",".$curdateLeaves['reason_id'].",".$curdateAttendance['id'].",".$f_flag.",".$curdateLeaves['duration'];
      $i++;
      }
      }
      // }
      $check_date += 86400;
      }//echo "<pre>";                                        print_r($str);die;
      $this->view->reportname = $reportName;
      $this->view->str = $str;
      }
      catch(Zend_Exception $e){
      print_r($e);die;
      }
      }
      else{
      $from_date=time();
      $to_date=time();
      $this->view->fromdate=date("Y-m-d",$from_date);
      $this->view->todate=date("Y-m-d",$to_date);
      }
      } */

    public function ajaxFixFunctionsAction() {
        //return FALSE;
        $this->_helper->layout->disableLayout();
        $storage = new Zend_Auth_Storage_Session();
        $modelAttendance = new Application_Model_Attendance();
        $output = 0;
        if ($_REQUEST['p_type'] == 2) {
            //$attendance_id = $_REQUEST['attendance_id'];
            $attendance_id = $this->getRequest()->getParam('attendance_id');
            //$reason_id = $_REQUEST['reason'];               
            $reason_id = $this->getRequest()->getParam('reason');
            $date = $_REQUEST['dateint'];
            $date = date("Y-m-d", $date);
            $t = '00:00:00';
            $t_in = array($date, $t);
            $t_in = implode(" ", $t_in);
            $t_out = $t_in;
            $data = array(
                'time_in' => $t_in,
                'time_out' => $t_out,
                'reason_id' => $reason_id,
                'office_hrs' => '00:00:00'
            );
            $where = " id = '" . $attendance_id . "'";
            $res = $modelAttendance->update($data, $where);
            if ($res) {
                $output = 1;
            }
        } else if ($_REQUEST['p_type'] == 1) {
            //$reason_id = $_REQUEST['reason'];
            $reason_id = $this->getRequest()->getParam('reason');
            //$attendance_id = $_REQUEST['attendance_id'];
            $attendance_id = $this->getRequest()->getParam('attendance_id');
            $data = array("reason_id" => $reason_id);
            $res = $modelAttendance->update($data, "id = " . $attendance_id);
            if ($res) {
                $output = 1;
            }
        } else if ($_REQUEST['p_type'] == 3) {
            $reason_id = 13;
            //$attendance_id = $_REQUEST['attendance_id'];
            $attendance_id = $this->getRequest()->getParam('attendance_id');
            $data = array("reason_id" => $reason_id);
            $res = $modelAttendance->update($data, "id = " . $attendance_id);
            //echo '<td id ='.$this->getRequest()->getParam('p_type').'>fixed</td>';
            if ($res) {
                $output = 1;
            }
        }
        echo json_encode($output);
        exit;
    }

    public function fixAttendanceProblemsAction() {
        // error_reporting(E_ALL);
        if ($this->_request->fromdate) {
            try {
                $modelEmployee = new Application_Model_Employees();
                $allEmployees = $modelEmployee->fetchAll(" job_status not in ('Terminated','Resigned') and current_job_status not in ('Terminated','Resigned')")->toArray();
                //$allEmployee=$modelEmployee->fetchAll("number = 01159")->toArray();
                $from_date = strtotime($this->_request->fromdate); //date("Y-m-d",(time() -  (86400 * 120)) );
                $to_date = strtotime($this->_request->todate); //date("Y-m-d",time() );
                $this->view->fromdate = date("Y-m-d", $from_date);
                $this->view->todate = date("Y-m-d", $to_date);
                $check_date = $from_date;
                $str = array();
                $i = 1;
                $reportName = "Find Attendance Problem Report";
                while ($check_date <= $to_date) {
                    $date = date("Y-m-d", $check_date);
                    foreach ($allEmployees as $emp) {
                        //$emp = $allEmployee[0];
                        $f_flag = 0;
                        $modelLeave = new Application_Model_Leave();
                        $curdateLeaves = $modelLeave->fetchAll(" from_date <= '" . $date . "' and to_date >= '" . $date . "' and employee_id=" . $emp['id'])->toArray();

                        if (count($curdateLeaves) > 0) {
                            $curdateLeaves = $curdateLeaves[0];
                        }
                        // echo " time_in LIKE '%".$date."%' and employee_id=".$emp['id'];die;
                        $modelAttendance = new Application_Model_Attendance();
                        $curdateAttendance = $modelAttendance->fetchAll(" time_in LIKE '%" . $date . "%' and employee_id=" . $emp['id'])->toArray();
                        if (count($curdateAttendance) > 0) {
                            $curdateAttendance = $curdateAttendance[0];
                        }
                        /* echo "<pre>";
                          print_r($curdateAttendance);die; */
                        if ($curdateLeaves && $curdateLeaves['leave_type'] != 'short_leave_availed' && $curdateLeaves['leave_type'] != 'half_leave_availed') {
                            $reasonModel = new Application_Model_LeaveReason();
                            $reasonList = $reasonModel->fetchAll(" id =" . $curdateLeaves['reason_id']);
                            $type = "Attendance";
                            if (count($reasonList) > 0) {
                                $reasonList = $reasonList[0];
                            }
                            $date2 = strtotime($date);
                            $day = date("D", $date2);
                            $date1 = date("d-M-Y", $date2);
                            $t = $curdateAttendance['time_in'];
                            $in_time = explode(" ", $t);
                            $time_in = $in_time[1];
                            if ($time_in == NULL) {
                                $time_in = '00:00:00';
                            }
                            //echo $curdateAttendance['time_in']."++++".$curdateAttendance['time_out'];die;
                            if ($day != 'Sat' && $day != 'Sun') {
                                if ($curdateLeaves['duration'] == 1) {
                                    if ($time_in == '00:00:00' && $curdateAttendance['reason_id'] == 0) {
                                        if ($curdateAttendance[id]) {
                                            $f_flag = 1;
                                        } else {
                                            $f_flag = 0;
                                        }
                                        $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Proper Reason must set in Attendance section</b> in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date " . $date1 . " and reason for leave is " . $reasonList['reason_name'] . ".," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                        $i++;
                                    } else if ($time_in != '00:00:00' && $curdateAttendance['reason_id'] != 0) {
                                        $f_flag = 2;
                                        $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Attendance Should Not be Marked</b> in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date " . $date1 . " and reason for leave is " . $reasonList['reason_name'] . ".," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                        $i++;
                                    } else if ($time_in != '00:00:00' && $curdateAttendance['reason_id'] == 0) {
                                        $f_flag = 2;
                                        $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Attendance Should Not be Marked</b> in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date " . $date1 . " and reason is not set it should be " . $reasonList['reason_name'] . ".," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                        $i++;
                                    }
                                } else {
                                    $date2 = strtotime($date);
                                    $date1 = date("d-M-Y", $date2);
                                    $f_date = $curdateLeaves['from_date'];
                                    $f_date = explode(" ", $f_date);
                                    $f_date = $f_date[0];
                                    $f_date = strtotime($f_date);
                                    $f_date = date("d-M-Y", $f_date);
                                    $t_date = $curdateLeaves['to_date'];
                                    $t_date = explode(" ", $t_date);
                                    $t_date = $t_date[0];
                                    $t_date = strtotime($t_date);
                                    $t_date = date("d-M-Y", $t_date);
                                    $duration = intval($curdateLeaves['duration']); //echo $time_in;
                                    if ($time_in == '00:00:00' && $curdateAttendance['reason_id'] == 0) {
                                        if ($curdateAttendance[id]) {
                                            $f_flag = 1;
                                        } else {
                                            $f_flag = 0;
                                        }
                                        $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Proper Reason must set in Attendance section</b> in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date<b> " . $date1 . " </b>availed " . $duration . " leaves From " . $f_date . " To " . $t_date . " and reason for leave is " . $reasonList['reason_name'] . ".," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                        $i++;
                                    } else if ($time_in != '00:00:00' && $curdateAttendance['reason_id'] != 0) {
                                        $f_flag = 2;
                                        $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Attendance Should Not be Marked</b> in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date<b> " . $date1 . " </b>availed " . $duration . " leaves From " . $f_date . " To " . $t_date . " and reason for leave is " . $reasonList['reason_name'] . ".," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                        $i++;
                                    } else if ($time_in != '00:00:00' && $curdateAttendance['reason_id'] == 0) {
                                        $f_flag = 2;
                                        $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Attendance Should Not be Marked</b> in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date<b> " . $date1 . " </b>availed " . $duration . " leaves From " . $f_date . " To " . $t_date . " and reason is not set it should be " . $reasonList['reason_name'] . ".," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                        $i++;
                                    }
                                }
                            }
                        } else if ($curdateLeaves && ($curdateLeaves['leave_type'] == 'short_leave_availed' || $curdateLeaves['leave_type'] == 'half_leave_availed' )) {
                            $date1 = strtotime($date);
                            $day = date("D", $date1);
                            $date1 = date("d-M-Y", $date1);
                            $t = $curdateAttendance['time_in'];
                            $time_in = explode(" ", $t);
                            $time_in = $time_in[1];
                            $type = "Attendance";
                            if ($time_in == NULL) {
                                $time_in = '00:00:00';
                            }
                            if ($day != 'Sat' && $day != 'Sun') {
                                if ($time_in == '00:00:00' && $curdateAttendance['reason_id'] == 0) {
                                    $f_flag = 0;
                                    $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Attendance should be marked</b> and Proper set <b>Reason</b> in Attendance section in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date " . $date1 . " and reason for leave is Half / Short Leave.," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                    $i++;
                                } else if ($time_in == '00:00:00' && $curdateAttendance['reason_id'] != 0) {
                                    $f_flag = 0;
                                    $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Attendance should be marked</b> in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date " . $date1 . " and reason for leave is Half / Short Leave.," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                    $i++;
                                } else if ($time_in != '00:00:00' && $curdateAttendance['reason_id'] == 0) {
                                    $f_flag = 3;
                                    $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Proper Reason must set in Attendance section</b> in the case of <b>" . ucwords(str_replace("_", " ", $curdateLeaves['leave_type'])) . " </b>by <b> " . $emp['name'] . " </b>on date " . $date1 . " and reason is not set it should be Half / Short Leave.," . $type . "," . $date . "," . $curdateLeaves['reason_id'] . "," . $curdateAttendance['id'] . "," . $f_flag . "," . $curdateLeaves['duration'];
                                    $i++;
                                }
                            }
                        }//echo "<pre>";                            print_r($str);die;
                    }
                    $check_date += 86400;
                }//echo "<pre>";                                        print_r($str);die;
                $this->view->reportname = $reportName;
                $this->view->str = $str;
            } catch (Zend_Exception $e) {
                print_r($e);
                die;
            }
        } else {
            $from_date = time();
            $to_date = time();
            $this->view->fromdate = date("Y-m-d", $from_date);
            $this->view->todate = date("Y-m-d", $to_date);
        }
    }

    private function findAttendanceProblems($fromDate, $toDate, $sendEmail = false) {
        try {
            $modelEmployee = new Application_Model_Employees();
            $allEmployees = $modelEmployee->fetchAll("job_status not in ('Terminated','Resigned') AND current_job_status not in ('Terminated','Resigned')");
            $str = array();
            foreach ($allEmployees as $emp) {
                $attLeaveProblems = "";
                $counter = 1;
                $from_date = $fromDate;
                $to_date = $toDate;
                while ($from_date <= $to_date) {
                    $day = date("D", strtotime($from_date));
                    $isHoliday = $this->getHolidayFlag($from_date);
                    if ($day != 'Sat' && $day != 'Sun' && !$isHoliday) {                        
                        $modelLeave = new Application_Model_Leave();
                        $curdateLeaves = $modelLeave->fetchRow(" from_date <= '" . $from_date . "' and to_date >= '" . $from_date . "' and employee_id=" . $emp['id']);

                        $modelAttendance = new Application_Model_Attendance();
                        $curdateAttendance = $modelAttendance->fetchRow(" time_in like '%" . $from_date . "%' and employee_id=" . $emp['id']);
                        if (empty($curdateAttendance)) {
                            $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> No Record found in attendance for date " . $from_date;
                            $counter++;
                        } else {
                            $office_hrs = $curdateAttendance['office_hrs'];
                            $working_hrs = $curdateAttendance['hrs'];
                            $reason_id = $curdateAttendance['reason_id'];
                            $remarks = $curdateAttendance['remarks'];
                            $timein = date("H:i", strtotime($curdateAttendance['time_in']));
                            $timeout = date("H:i", strtotime($curdateAttendance['time_out']));
                            $reasonModel = new Application_Model_LeaveReason();
                            $reasonList = array();
                            if (!empty($reason_id))
                                $reasonList = $reasonModel->fetchRow(" id =" . $reason_id);

                            $timeDiff = $this->getHelper('CommonFunctions')->getTimeDiff($office_hrs, $working_hrs);
                            if ($office_hrs > $working_hrs) {
                                if ($timeDiff >= '02:00:00' && $timeDiff <= '03:29:00' && $curdateLeaves) {
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Short Leave is marked but still time is short for date " . $from_date;
                                    $counter++;
                                } elseif ($timeDiff >= '02:00:00' && $timeDiff <= '03:29:00' && !$curdateLeaves) {                                    
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Short Leave is required for date " . $from_date;
                                    $counter++;
                                } elseif ($timeDiff >= '03:30:00' && $timeDiff <= '04:59:00' && $curdateLeaves) {
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Half Leave is marked but still time is short for date " . $from_date;
                                    $counter++;
                                } elseif ($timeDiff >= '03:30:00' && $timeDiff <= '04:59:00' && !$curdateLeaves) {
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Half Leave is required for date " . $from_date;
                                    $counter++;
                                } elseif ($timeDiff >= '05:00:00' && $reason_id == 0 && !$curdateLeaves) {
                                    if ($timein != "00:00" && $timeout == "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Timeout is missing for date " . $from_date;
                                        $counter++;
                                    } elseif ($timein == "00:00" && $timeout != "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Timein is missing for date " . $from_date;
                                        $counter++;
                                    } else {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Full day leave is required for date " . $from_date;
                                        $counter++;
                                    }
                                } elseif ($timeDiff >= '05:00:00' && $reason_id != 0 && !$curdateLeaves) {
                                    if ($timein != "00:00" && $timeout == "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Timeout is missing for date " . $from_date;
                                        $counter++;
                                    } elseif ($timein == "00:00" && $timeout != "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Timein is missing for date " . $from_date;
                                        $counter++;
                                    } else {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Full day leave is required for date " . $from_date;
                                        $counter++;
                                    }
                                }
                            } elseif ($office_hrs == "00:00:00" && $working_hrs == "00:00:00") {
                                $leaveStatus = $curdateLeaves['status'];
                                if ($curdateLeaves && $reason_id != 0) {
                                    if ($timein != "00:00" && $timeout == "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Timeout is missing and leave is marked for date " . $from_date;
                                        $counter++;
                                    } elseif ($timein == "00:00" && $timeout != "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Checkin is missing and leave is marked for date " . $from_date;
                                        $counter++;
                                    } elseif ($timein != "00:00" && $timeout != "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . "Both Attendance & Leave Exist for date " . $from_date;
                                        $counter++;
                                    }/* elseif($leaveStatus!='Approved'){
                                      $str[] = $emp['number'] . "," . $emp['name'] . ",<b>".$counter.". Proper Reason</b> Time in = ".$timein." Time out= ".$timeout." Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Leave status is ".$leaveStatus." for date " . $from_date;
                                      $attLeaveProblems = "<b>".$counter.". Proper Reason</b> Time in = ".$timein." Time out= ".$timeout." Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Leave status is ".$leaveStatus." for date " . $from_date;
                                      } */
                                } elseif (!$curdateLeaves && $reason_id != 0) {
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Reason In Attendance = " . $reasonList['reason_name'] . " and no leave marked for date " . $from_date;
                                    $counter++;
                                } elseif ($reason_id == 0) {
                                    if ($timein == "00:00" && $timeout == "00:00" && !$curdateLeaves) {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Checkin and Checkout are missing and no leave marked for date " . $from_date;
                                        $counter++;
                                    }
                                }
                            } elseif ($office_hrs == "00:00:00" && $working_hrs != "00:00:00") {

                                if (!$curdateLeaves && $reason_id != 0) {
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Attendance Reason = " . $reasonList['reason_name'] . " for date " . $from_date;
                                    $counter++;
                                } elseif ($curdateLeaves && $reason_id != 0) {
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Attendance Reason = " . $reasonList['reason_name'] . " Leave is marked as well for date " . $from_date;
                                    $counter++;
                                } elseif (!$curdateLeaves && $reason_id == 0) {
                                    if ($timein != "00:00" && $timeout == "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Timeout is missing for date " . $from_date;
                                        $counter++;
                                    } elseif ($timein == "00:00" && $timeout != "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Timein is missing for date " . $from_date;
                                        $counter++;
                                    } elseif ($timein != "00:00" && $timeout != "00:00") {
                                        $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Both Timein and Timeout are missing for date " . $from_date;
                                        $counter++;
                                    }
                                } elseif ($curdateLeaves && $reason_id == 0) {
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " Leave is marked For date " . $from_date;
                                    $counter++;
                                } else {
                                    $attLeaveProblems .= (!empty($attLeaveProblems)?"<br/>":"")."<b>".$counter.". Proper Reason</b> Check in = " . $timein . " Check out= " . $timeout . " Office Hours = " . $office_hrs . " Working Hours = " . $working_hrs . " for date " . $from_date;
                                    $counter++;
                                }
                            }
                        }
                    }
                    $from_date = date('Y-m-d', strtotime($from_date . "+1 days"));
                }
                if ($sendEmail && !empty($attLeaveProblems)) {
                    $subject = "Attendance - Leave Problems for ".$emp['name']." [".$fromDate." - ".$toDate."]";
                    Application_Action_Helper_Mail::sendCustomEmail($emp['id'], $subject, "Hi ".$emp['name'].",<br/><br/>Nexthrm Found following Problem in your Attendance<br/><br/>" . $attLeaveProblems . "<br/><br/>This mail is auto generated by NextHRM system.If you have any concerns please Contact HR/FDO");
                }
                if(!$sendEmail && !empty($attLeaveProblems)){
                    $temp = array();
                    $temp['number'] = $emp['number'];
                    $temp['name'] = $emp['name'];
                    $temp['problems'] = $attLeaveProblems;
                    $str[] = $temp;
                }
            }
            return $str;
        } catch (Zend_Exception $e) {
            echo '<pre>';
            print_r($e);
            die;
        }
    }

    public function cronFindAttendanceProblemsAction() {
        $this->_helper->layout->disableLayout();
        //$from_date = date('m-01-Y',strtotime('this month'));
        //$to_date = date('m-t-Y',strtotime('this month'));

        $date = date('Y-m-d');
        $ts = strtotime($date);
        $start = (date('w', $ts) == 0) ? $ts : strtotime('last monday', $ts);
        $from_date = date('Y-m-d', $start);
        $to_date = date('Y-m-d', strtotime('next friday', $start));

        $problemArr = $this->findAttendanceProblems($from_date, $to_date, true);
        die();
    }

    public function findAttendanceLeaveProblemsAction() {
        if ($this->_request->fromdate) {
            $from_date = $this->_request->fromdate;
            $to_date = $this->_request->todate;
            $reportName = "Find Attendance Problem Report";
            $problemArr = $this->findAttendanceProblems($from_date, $to_date);
            $this->view->str = $problemArr;
            $this->view->reportname = $reportName;
        }
    }

    public function findLeaveProblemsAction() {
        // error_reporting(E_ALL);
        if ($this->_request->fromdate) {
            try {
                $modelEmployee = new Application_Model_Employees();
                $allEmployees = $modelEmployee->fetchAll(" job_status not in ('Terminated','Resigned') and current_job_status not in ('Terminated','Resigned')")->toArray();
                //$allEmployee=$modelEmployee->fetchAll("number = 01593")->toArray();
                $from_date = strtotime($this->_request->fromdate); //date("Y-m-d",(time() -  (86400 * 120)) );
                $to_date = strtotime($this->_request->todate); //date("Y-m-d",time() );
                $this->view->fromdate = date("Y-m-d", $from_date);
                $this->view->todate = date("Y-m-d", $to_date);
                $check_date = $from_date;
                $str = array();
                $i = 1;
                $reportName = "Find Attendance Problem Report";
                while ($check_date <= $to_date) {
                    $date = date("Y-m-d", $check_date);
                    foreach ($allEmployees as $emp) {
                        // $emp = $allEmployee[0];
                        $modelLeave = new Application_Model_Leave();
                        $curdateLeaves = $modelLeave->fetchAll(" from_date <= '" . $date . "' and to_date >= '" . $date . "' and employee_id=" . $emp['id'])->toArray();
                        if (count($curdateLeaves) > 0) {
                            $curdateLeaves = $curdateLeaves[0];
                        }
                        $modelAttendance = new Application_Model_Attendance();
                        $curdateAttendance = $modelAttendance->fetchAll(" time_in like '%" . $date . "%' and employee_id=" . $emp['id'])->toArray();
                        if (count($curdateAttendance) > 0) {
                            $curdateAttendance = $curdateAttendance[0];
                        }
                        $date2 = strtotime($date);
                        $day = date("D", $date2);
                        $date1 = date("d-M-Y", $date2);
                        $t = $curdateAttendance['time_in'];
                        $in_time = explode(" ", $t);
                        $time_in = $in_time[1]; // echo $time_in;die;
                        $type = "Leave";
                        $today = date('Y-m-d', time());
                        $today = strtotime($today);
                        $c_date = strtotime($date);
                        if ($day != 'Sat' && $day != 'Sun') {
                            if ($time_in != '00:00:00' && $curdateAttendance['reason_id'] != 0 && !$curdateLeaves) {
                                if ($curdateAttendance['reason_id'] != 4 && $curdateAttendance['reason_id'] != 5) {
                                    $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Shot/Half Leave </b>Not marked by <b> " . $emp['name'] . " </b>on date " . $date1 . ' To Fix Employee should mark Half/Short Leave.,' . $date1;
                                    $i++;
                                }
                            } else if ($time_in == '00:00:00' && $curdateAttendance['reason_id'] != 0 && !$curdateLeaves) {
                                if ($curdateAttendance['reason_id'] != 4 && $curdateAttendance['reason_id'] != 5 && $curdateAttendance['reason_id'] != 13 && $curdateAttendance['reason_id'] != 14) {
                                    $str[$i] = $emp['number'] . "," . $emp['name'] . ", <b>Leave not marked</b> by<b> " . $emp['name'] . " </b>on date " . $date1 . ' To Fix Employee should mark Leave.,' . $date1;
                                    $i++;
                                } else if ($curdateAttendance['reason_id'] == 13 && $curdateAttendance['reason_id'] == 14) {
                                    $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Shot/Half Leave not marked</b> by<b> " . $emp['name'] . " </b>on date " . $date1 . ' To Fix Employee should mark Half/Short Leave.,' . $data1;
                                    $i++;
                                } else {
                                    $str[$i] = $emp['number'] . "," . $emp['name'] . ",<b>Attendance is not marked</b> by<b> " . $emp['name'] . " </b>on date " . $date1 . ' To Fix Employee should mark Leave.,' . $date1;
                                    $i++;
                                }
                            } else if (!$curdateAttendance && !$curdateLeaves && ($c_date < $today)) {
                                $str[$i] = $emp['number'] . "," . $emp['name'] . ", <b>Leave not marked</b> by<b> " . $emp['name'] . " </b>on date " . $date1 . ' To Fix Employee should mark Leave and also set Reason for Leave in Attendance section.,' . $date1;
                                $i++;
                            }
                        }
                    }
                    $check_date += 86400;
                }//echo "<pre>";                                        print_r($str);die;
                $this->view->reportname = $reportName;
                $this->view->str = $str;
            } catch (Zend_Exception $e) {
                print_r($e);
                die;
            }
        } else {
            $from_date = time();
            $to_date = time();
            $this->view->fromdate = date("Y-m-d", $from_date);
            $this->view->todate = date("Y-m-d", $to_date);
        }
    }

    /*       public function findAttendanceAndLeaveProblemsAction(){
      error_reporting(E_ALL);
      if($this->_request->fromdate){
      try{
      $modelEmployee= new Application_Model_Employees();
      $allEmployees=$modelEmployee->fetchAll(" job_status not in ('Terminated','Resigned') and current_job_status not in ('Terminated','Resigned')")->toArray();
      $from_date=strtotime($this->_request->fromdate);//date("Y-m-d",(time() -  (86400 * 120)) );
      $to_date=strtotime($this->_request->todate);//date("Y-m-d",time() );
      $this->view->fromdate=date("Y-m-d",$from_date);
      $this->view->todate=date("Y-m-d",$to_date);
      $check_date=$from_date;
      $str = array();$i = 1;
      $reportName = "Attendance Fixation Report";
      while($check_date <= $to_date){
      $date = date("Y-m-d",  $check_date);
      foreach($allEmployees as $emp){
      $modelLeave= new Application_Model_Leave();
      $curdateLeaves=$modelLeave->fetchAll(" from_date <= '" . $date . "' and to_date >= '" . $date . "' and employee_id=".$emp['id'])->toArray();
      if(count($curdateLeaves) > 0){
      $curdateLeaves=$curdateLeaves[0];
      }
      $modelAttendance= new Application_Model_Attendance();
      $curdateAttendance=$modelAttendance->fetchAll(" time_in like '%".$date."%' and employee_id=".$emp['id'])->toArray();
      if(count($curdateAttendance) > 0){
      $curdateAttendance=$curdateAttendance[0];
      }
      if($curdateLeaves && $curdateLeaves['leave_type']!='short_leave_availed' && $curdateLeaves['leave_type']!='half_leave_availed'){
      if($curdateAttendance){
      $d=explode(" ",$curdateAttendance['time_in']);
      if($curdateAttendance && $d[1]!="00:00:00"){
      $reason = "Attendance";
      $date1 = strtotime($date);
      $date1 = date("d-M-Y",$date1);
      if($curdateLeaves['duration'] == 1){
      $str[$i] = $emp['number'].",".$emp['name'].",<b>Attendance</b> Should Not be Marked in the case of <b>".ucwords(str_replace("_", " ",$curdateLeaves['leave_type'] ))." </b>by <b>".$emp['name']." </b>on date ".$date1.".,".$reason;
      $i++;
      }else{
      $date1 = strtotime($date);
      $date = date("d-M-Y",$date1);
      $date1 = $date1 + $curdateLeaves['duration']*84200;
      $date1 = date("d-M-Y",$date1);
      $str[$i] = $emp['number'].",".$emp['name'].",<b>Attendance</b> Should Not be Marked in the case of <b>".ucwords(str_replace("_", " ",$curdateLeaves['leave_type'] ))."</b> by <b>".$emp['name']." </b>From date ".$date." To date ".$date1." for ".$curdateLeaves['duration']." days.,".$reason;
      $i++;

      }
      }
      }
      }
      else if($curdateLeaves && ($curdateLeaves['leave_type']=='short_leave_availed' || $curdateLeaves['leave_type']=='half_leave_availed'  )){
      $d=explode(" ",$curdateAttendance['time_in']);
      $reason = "Attendance";
      $date1 = strtotime($date);
      $date1 = date("d-M-Y",$date1);
      if(!$curdateAttendance || $d[1]== "00:00:00"){
      //  $reason = "Attendance is not Marked";
      $str[$i] = $emp['number'].",".$emp['name'].",<b>Attendance</b> should be Marked in the case of<b> ".ucwords(str_replace("_", " ",$curdateLeaves['leave_type'] ))." </b>by <b>".$emp['name']." </b>on date ".$date1.'.,'.$reason;
      $i++;
      }
      else if($curdateAttendance && $curdateAttendance['reason_id']==0){
      // $reason = "Leave Reason is not given in Attendance";
      $str[$i] = $emp['number'].",".$emp['name'].",<b>Leave reason</b> is not set in <b>Attendance section</b> for the employee<b> ".$emp['name']." </b>on date ".$date1.".It should be <b>".ucwords(str_replace("_", " ",$curdateLeaves['leave_type'] ))."</b>.,".$reason;
      $i++;
      }
      }
      else if($curdateAttendance && $curdateAttendance['reason_id'] != 0 && !$curdateLeaves){
      $reason = "Leave";
      $date1 = strtotime($date);
      $date1 = date("d-M-Y",$date1);
      $time_in = explode(" ", $curdateAttendance['time_in']);
      $time_in = $time_in[1];
      if($time_in == '00:00:00'){
      $str[$i] = $emp['number'].",".$emp['name'].", <b>Leave </b>Not marked by<b> ".$emp['name']." </b>on date ".$date1.' To Fix Employee should mark Leave.,'.$reason;
      $i++;
      }else{
      $str[$i] = $emp['number'].",".$emp['name'].",<b>Shot/Half Leave </b>Not marked by<b> ".$emp['name']." </b>on date ".$date1.' To Fix Employee should mark Leave.,'.$reason;
      $i++;
      }
      }
      }
      $check_date += 86400;
      }//echo "<pre>";                                        print_r($str);die;
      $this->view->reportname = $reportName;
      $this->view->str = $str;
      }
      catch(Zend_Exception $e){
      print_r($e);die;
      }
      }
      else{
      $from_date=time();
      $to_date=time();
      $this->view->fromdate=date("Y-m-d",$from_date);
      $this->view->todate=date("Y-m-d",$to_date);
      }
      } */

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

    public function profileAction() {

        $storage = new Zend_Auth_Storage_Session();
        $user_id = $storage->read()->id;


        $general = array(
            "user/change-password/id/" . $user_id . "" => 'Here user can change the password.',
            "administration/company-details" => 'Here user can set company\'s name and Contact Details.',
            "administration/date-format" => 'Here user can change the date format.',
            "settings/department-fields" => 'Here user can change labels for department specific optional fields.',
            "settings/leave-quota-settings" => 'Here user can change general leave quota for all employees.',
            //"user/profile" => "User can edit his info here.",
            "version-control" => 'It will show the latest vesrion.',
            //"settings/review-report-date" => "Here user can set Dates to view Review Report on Dashboard.",
            //"settings/performance-review-email-setting" => "Here user can set Subject Tags, From Email ID and Email Signature",
            //"review-notification-settings" => "Here user can set which specific VTO and GM notify about review",
            "settings/insurance-policy" => "Here user can set Insurance Policy Number.",
            "settings/insurance-outpatient-revised-date-policy" => "Here user can set Outpatient From and To Date.",
            "settings/insurance-inpatient-revised-date-policy" => "Here user can set Inpatient From and To Date.",
            //"settings/insurance-policy" => "Here user can set Outpatient Valid From and Valid To Date.",
            "administration/attendance-notification-time" => "Attendance Notification Time",
            "administration/exclude-employee-from-report" => "Exclude Employee From Report",
            "administration/todays-attendance-report-recipients" => "Set Todays Attendance Report Recipients",
            "administration/office-timings" => "Here user can set Office Timmings for staff",
            "email-templates/" => "Email Settings For Leave Steps",
            "email-templates/email-sending-options" => "Email Sending Options Settings For Leave Steps",
            "administration/mark-leave-date" => "Mark leave date"
        );

        $this->view->list = $general;
    }

    /**
     * ROLE
     * @logs Logs Tab
     */
    public function logsAction() {
        $general = array(
            "logs" => 'It will maintain the system logs generated against events.',
            "settings/login-activity" => 'Activity about the login user can be manage here.',
            "web-service-logs" => 'It contains the webservices logs information.'
        );

        $this->view->list = $general;
    }

    /**
     * ROLE
     * @companyname Set Company Name
     */
    public function companyDetailsAction() {
        $messages = array();
        if ($this->_request->update == 's') {
            $messages[] = array('success', 'Company Details Updated Successfully');
        }

        $this->view->messages = $messages;

        $storage = new Zend_Auth_Storage_Session();
        $modified_by = $storage->read()->id;

        $form = new Application_Form_NameConfiguration();
        $settingsModel = new Application_Model_Settings();
        $fieldOneRow = $settingsModel->fetchRow("param = 'company-name'");
        $fieldTwoRow = $settingsModel->fetchRow("param = 'company-tell'");
        $fieldThreeRow = $settingsModel->fetchRow("param = 'company-fax'");
        $fieldFourRow = $settingsModel->fetchRow("param = 'company-address'");

        $form->getElement('value_one')->setValue($fieldOneRow->value);
        $form->getElement('value_two')->setValue($fieldTwoRow->value);
        $form->getElement('value_three')->setValue($fieldThreeRow->value);
        $form->getElement('value_four')->setValue($fieldFourRow->value);

        $form->setAttrib('class', 'form-horizontal clearfix');
        $this->view->form = $form;

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $fieldOneRow->value = $formData['value_one'];
            $fieldTwoRow->value = $formData['value_two'];
            $fieldThreeRow->value = $formData['value_three'];
            $fieldFourRow->value = $formData['value_four'];
            $fieldOneRow->save();
            $fieldTwoRow->save();
            $fieldThreeRow->save();
            $fieldFourRow->save();
            $this->_redirect('administration/company-details/update/s');
        }
    }

    /**
     * ROLE
     * @institute Institute Management
     */
    public function instituteAction() {


        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Institute Information Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Institute Information Updated Successfully');
        }
        $this->view->messages = $messages;

        $statusArray = array("Active" => "Active", "Inactive" => "Inactive");
        $fields = array(
            'i.name' => array(
                'title' => 'Institute Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'i.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'i.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'i.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => $statusArray,
                'default' => 'no'
            )
        );

        $this->view->defaultFields = $fields;
        $instituteModel = new Application_Model_Institute();

        $sql_formatted_values = '';

        $this->view->actionType = 'listing';



        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $instituteModel->selectAllInstituteDetails($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $instituteModel->selectAllInstituteDetails('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {
// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from institute where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @institute Institute Add
     */
    public function addInstituteAction() {
        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Institute Information Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Institute Information Updated Successfully');
        }
        $this->view->messages = $messages;
        $fields = array(
            'i.name' => array(
                'title' => 'Institute Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'i.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'i.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'i.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            )
        );

        $this->view->defaultFields = $fields;
        $instituteModel = new Application_Model_Institute();

        $sql_formatted_values = '';

        $this->view->actionType = 'add';

        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_Institute(
                    $this->getRequest()->getParam('id'), $this->getRequest()->getParam('emplId'), $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Institute";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $instituteModel->createRow();
                    $row->created_date = date('Y-m-d');
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;

                    $row->name = $form->getValue('name');
                    $row->status = $form->getValue('status');
                    $recordExist = $instituteModel->checkOnEditIfRecordExists($id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('administration/institute/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $instituteModelData = $instituteModel->fetchRow('id=' . $id);
                    $form->populate($instituteModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $instituteModel->selectAllInstituteDetails($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $instituteModel->selectAllInstituteDetails('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from institute where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @institute Institute Edit
     */
    public function editInstituteAction() {
// action body
        $instituteModel = new Application_Model_Institute();
        $id = (int) $this->getRequest()->getParam('id');
        $instituteModelRecord = $instituteModel->fetchRow('id = ' . $id);
        if ($instituteModelRecord['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");


        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Institute Information Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Institute Information Updated Successfully');
        }
        $this->view->messages = $messages;



        $fields = array(
            'i.name' => array(
                'title' => 'Institute Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'i.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'i.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'i.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            )
        );

        $this->view->defaultFields = $fields;
        $instituteModel = new Application_Model_Institute();

        $sql_formatted_values = '';

        $this->view->actionType = 'update';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_Institute(
                    $this->getRequest()->getParam('id'), $this->getRequest()->getParam('emplId'), $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Institute";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $id = (int) $this->getRequest()->getParam('id');
                    $row = $instituteModel->fetchRow('id=' . $id);
                    //print_r($row->employee_id);die;
                    $storage = new Zend_Auth_Storage_Session();
                    $row->modified_by = $storage->read()->id;
                    //$row->modified_date = date('Y-m-d');

                    $row->name = $form->getValue('name');
                    $row->status = $form->getValue('status');
                    $recordExist = $instituteModel->checkOnEditIfRecordExists($id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('administration/institute/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $instituteModelData = $instituteModel->fetchRow('id=' . $id);
                    $form->populate($instituteModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $instituteModel->selectAllInstituteDetails($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $instituteModel->selectAllInstituteDetails('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from institute where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @jobtitle Job Title Management
     */
    public function jobTitleAction() {
        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Job Title Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Job Title Updated Successfully');
        }
        $this->view->messages = $messages;
        $jTitleModel = new Application_Model_JobTitle();

        $jtArray = $jTitleModel->getTheTitles('job_title');

        $fields = array(
            'jt.job_title' => array(
                'title' => 'Job Title',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'jt.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'jt.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'jt.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => array(
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ),
                'default' => 'no'
            )
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';

        $this->view->actionType = 'listing';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $jTitleModel->selectAllJoBTitles($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $jTitleModel->selectAllJoBTitles('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from job_title where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @jobtitle Job Title Add
     */
    public function addJobTitleAction() {
// action body
        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Job Title Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Job Title Updated Successfully');
        }
        $this->view->messages = $messages;
        $jTitleModel = new Application_Model_JobTitle();

        $jtArray = $jTitleModel->getTheTitles('job_title');

        $fields = array(
            'jt.job_title' => array(
                'title' => 'Job Title',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'jt.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'jt.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'jt.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => array(
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ),
                'default' => 'no'
            )
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'add';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_JobTitle(
                    $this->getRequest()->getParam('id'), $this->getRequest()->getParam('emplId'), $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Job Title";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $jTitleModel->createRow();
                    $row->created_date = date('Y-m-d');
                    ;
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;

                    $row->job_title = $form->getValue('job_title');
                    $row->status = $form->getValue('status');
                    $recordExist = $jTitleModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('job_title'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('administration/job-title/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $jTitleModelData = $jTitleModel->fetchRow('id=' . $id);
                    $form->populate($jTitleModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $jTitleModel->selectAllJoBTitles($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $jTitleModel->selectAllJoBTitles('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from job_title where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @jobtitle Job Title Edit
     */
    public function editJobTitleAction() {
// action body
        $jTitleModel = new Application_Model_JobTitle();
        $id = (int) $this->getRequest()->getParam('id');
        $jTitleModelRecord = $jTitleModel->fetchRow('id = ' . $id);
        if ($jTitleModelRecord['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");


        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Job Title Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Job Title Updated Successfully');
        }
        $this->view->messages = $messages;
        $jTitleModel = new Application_Model_JobTitle();

        $jtArray = $jTitleModel->getTheTitles('job_title');

        $fields = array(
            'jt.job_title' => array(
                'title' => 'Job Title',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'jt.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'jt.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'jt.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => array(
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ),
                'default' => 'no'
            )
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'update';

        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_JobTitle(
                    $this->getRequest()->getParam('id'), $this->getRequest()->getParam('emplId'), $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Job Title";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $id = (int) $this->getRequest()->getParam('id');
                    $row = $jTitleModel->fetchRow('id=' . $id);
                    //print_r($row->employee_id);die;
                    $storage = new Zend_Auth_Storage_Session();
                    $row->modified_by = $storage->read()->id;

                    $row->job_title = $form->getValue('job_title');
                    $row->status = $form->getValue('status');

                    $recordExist = $jTitleModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('job_title'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('administration/job-title/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $jTitleModelData = $jTitleModel->fetchRow('id=' . $id);
                    $form->populate($jTitleModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $jTitleModel->selectAllJoBTitles($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $jTitleModel->selectAllJoBTitles('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from job_title where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @employer Employer Management
     */
    public function employerAction() {
        $this->view->id = $this->getRequest()->getParam('emplId');
        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employer Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Employer Updated Successfully');
        }
        $this->view->messages = $messages;

        $employerModel = new Application_Model_Employer();

        $this->view->defaultFields = array(
            'empl.name' => array(
                'title' => 'Employer Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'address' => array(
                'title' => 'Employer Address',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'contact_number' => array(
                'title' => 'Contact Number',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'contact_person' => array(
                'title' => 'Contact Person',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'no_of_employees' => array(
                'title' => 'no_of_employees',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'state_id' => array(
                'title' => 'State',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'city_id' => array(
                'title' => 'City',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            )
        );
        $sql_formatted_values = '';
        $this->view->actionType = 'listing';



        $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $this->view->defaultFields;
        $modelNationality = new Application_Model_Country();
        $nationalityList = $modelNationality->allCountry('country');
        // Fetching country_id against country name ///
        if (strpos($sql_formatted_values, 'country_id like') !== false) {
            $countryId = $this->getCountryNameByCountryId($sql_formatted_values, $nationalityList);
            $sql_formatted_values = "AND country_id like '%" . $countryId . "%' ";
        }
        ///////////
        // Fetching state_id against state name ///
        //////////// 
        if (strpos($sql_formatted_values, 'state_id like') !== false) {
            $stateId = $this->getStateNameByStateId($sql_formatted_values);
            $sql_formatted_values = "AND empl.state_id like '%" . $stateId . "%' ";
        }
        ///////////
        if (isset($this->arrSettings["sort"]))
            $records = $employerModel->selectAllEmployers($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $employerModel->selectAllEmployers('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        $nationalityList = $modelNationality->allCountry('country');
        $nationalityList[''] = '';
        $employerList = array();
        $employerList = $employerModel->getTheEmployers('id');
//fetching records from the paginator selecting ids of the countries and replacing them
// with their respective country name
        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from employer where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }

            if ($page->country_id)
                $page['country_id'] = $nationalityList[$page->country_id];
            else
                $page['country_id'] = '.';

            if ($page->state_id) {
                $stateModel = new Application_Model_State();
                $stateRow = $stateModel->fetchRow("id='" . $page->state_id . "'");
                $page['state_id'] = $stateRow->name;
            } else
                $page['state_id'] = '.';

            if ($page->city_id) {
                $cityModel = new Application_Model_City();
                $cityRow = $cityModel->fetchRow("id='" . $page->city_id . "'");
                $page['city_id'] = $cityRow->name;
            } else
                $page['city_id'] = '.';
        }
    }

    /**
     * ROLE
     * @employer Employer Add
     */
    public function addEmployeerAction() {
// action body
        $this->view->id = $this->getRequest()->getParam('emplId');
        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employer Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Employer Updated Successfully');
        }
        $this->view->messages = $messages;

        $employerModel = new Application_Model_Employer();

        $this->view->defaultFields = array(
            'empl.name' => array(
                'title' => 'Employer Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'address' => array(
                'title' => 'Employer Address',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'contact_number' => array(
                'title' => 'Contact Number',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'contact_person' => array(
                'title' => 'Contact Person',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'no_of_employees' => array(
                'title' => 'no_of_employees',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'state_id' => array(
                'title' => 'State',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'city_id' => array(
                'title' => 'City',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'empl.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            )
        );
        $sql_formatted_values = '';

        $this->view->actionType = 'add';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;


            $form = new Application_Form_Employer(
                    '', '', '', $this->getRequest()->getParam('id'), $this->view->actionType);
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Employer";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $employerModel->createRow();
                    $row->created_date = date('Y-m-d');
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;

                    $row->country_id = $form->getValue('country_id');
                    $row->state_id = $form->getValue('state_id');
                    $row->city_id = $form->getValue('city_id');
                    $row->name = ucwords(strtolower($form->getValue('name')));
                    $row->address = $form->getValue('address');
                    $row->contact_number = $form->getValue('contact_number');
                    $row->contact_person = $form->getValue('contact_person');
                    $row->no_of_employees = $form->getValue('no_of_employees');
                    $row->status = $form->getValue('status');
                    $row->save();


                    $this->_redirect('administration/employer/' . $this->view->actionType . '/s');
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $form->getValue('id');
                if ($id > 0) {
                    $form->populate($employerModelArray);
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $this->view->defaultFields;

        if (isset($this->arrSettings["sort"]))
            $records = $employerModel->selectAllEmployers($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $employerModel->selectAllEmployers('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        $modelNationality = new Application_Model_Country();
        $nationalityList = $modelNationality->allCountry('country');
        $nationalityList[''] = '';
        $employerList = array();
        $employerList = $employerModel->getTheEmployers('id');
//fetching records from the paginator selecting ids of the countries and replacing them
// with their respective country name
        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from employer where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }


            if ($page->country_id)
                $page['country_id'] = $nationalityList[$page->country_id];
            else
                $page['country_id'] = '.';

            if ($page->state_id) {
                $stateModel = new Application_Model_State();
                $stateRow = $stateModel->fetchRow("id='" . $page->state_id . "'");
                $page['state_id'] = $stateRow->name;
            } else
                $page['state_id'] = '.';

            if ($page->city_id) {
                $cityModel = new Application_Model_City();
                $cityRow = $cityModel->fetchRow("id='" . $page->city_id . "'");
                $page['city_id'] = $cityRow->name;
            } else
                $page['city_id'] = '.';
        }
    }

    /**
     * ROLE
     * @employer Employer Edit
     */
    public function editEmployeerAction() {
// action body
        $employerModel = new Application_Model_Employer();
        $id = (int) $this->getRequest()->getParam('id');
        $employerModelRecord = $employerModel->fetchRow('id = ' . $id);
        if ($employerModelRecord['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");


        $this->view->id = $this->getRequest()->getParam('emplId');
        $messages = array();

//        $emplId = $this->getRequest()->getParam('emplId');
//        $front = Zend_Controller_Front::getInstance();
//        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
//        $Plugin->verifyAccess($emplId);

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employer Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Employer Updated Successfully');
        }
        $this->view->messages = $messages;

        $employerModel = new Application_Model_Employer();

        $this->view->defaultFields = array(
            'empl.name' => array(
                'title' => 'Employer Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'address' => array(
                'title' => 'Employer Address',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'contact_number' => array(
                'title' => 'Contact Number',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'contact_person' => array(
                'title' => 'Contact Person',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'no_of_employees' => array(
                'title' => 'no_of_employees',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'state_id' => array(
                'title' => 'State',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'city_id' => array(
                'title' => 'City',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'empl.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            )
        );
        $sql_formatted_values = '';

        $this->view->actionType = 'update';

        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;


            $id = (int) $this->_request->getParam('id', 0);
            $employerModelData = $employerModel->fetchRow('id=' . $id);
            $employerModelArray = $employerModelData->toArray();

            $form = new Application_Form_Employer(
                    $employerModelArray['country_id'], $employerModelArray['state_id'], $employerModelArray['city_id'], $this->getRequest()->getParam('id'), $this->view->actionType);
            $form->populate($employerModelArray);


            $hdnCountryId = new Zend_Form_Element_Hidden('hdn_country_id');
            $hdnCountryId->setValue($employerModelArray['country_id']);
            $form->addElement($hdnCountryId);

            $hdnStateId = new Zend_Form_Element_Hidden('hdn_state_id');
            $hdnStateId->setValue($employerModelArray['state_id']);
            $form->addElement($hdnStateId);

            $hdnCityId = new Zend_Form_Element_Hidden('hdn_city_id');
            $hdnCityId->setValue($employerModelArray['city_id']);
            $form->addElement($hdnCityId);

            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Employer";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $id = (int) $form->getValue('id');
                    $row = $employerModel->fetchRow('id=' . $id);
                    $storage = new Zend_Auth_Storage_Session();
                    $row->modified_by = $storage->read()->id;

                    $row->country_id = $form->getValue('country_id');
                    $row->state_id = $form->getValue('state_id');
                    $row->city_id = $form->getValue('city_id');
                    $row->name = ucwords(strtolower($form->getValue('name')));
                    $row->address = $form->getValue('address');
                    $row->contact_number = $form->getValue('contact_number');
                    $row->contact_person = $form->getValue('contact_person');
                    $row->no_of_employees = $form->getValue('no_of_employees');
                    $row->status = $form->getValue('status');
                    $row->save();


                    $this->_redirect('administration/employer/' . $this->view->actionType . '/s');
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $form->getValue('id');
                if ($id > 0) {
                    $form->populate($employerModelArray);
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $this->view->defaultFields;

        if (isset($this->arrSettings["sort"]))
            $records = $employerModel->selectAllEmployers($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $employerModel->selectAllEmployers('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        $modelNationality = new Application_Model_Country();
        $nationalityList = $modelNationality->allCountry('country');
        $nationalityList[''] = '';
        $employerList = array();
        $employerList = $employerModel->getTheEmployers('id');
//fetching records from the paginator selecting ids of the countries and replacing them
// with their respective country name
        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from employer where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }


            if ($page->country_id)
                $page['country_id'] = $nationalityList[$page->country_id];
            else
                $page['country_id'] = '.';

            if ($page->state_id) {
                $stateModel = new Application_Model_State();
                $stateRow = $stateModel->fetchRow("id='" . $page->state_id . "'");
                $page['state_id'] = $stateRow->name;
            } else
                $page['state_id'] = '.';

            if ($page->city_id) {
                $cityModel = new Application_Model_City();
                $cityRow = $cityModel->fetchRow("id='" . $page->city_id . "'");
                $page['city_id'] = $cityRow->name;
            } else
                $page['city_id'] = '.';
        }
    }

    /**
     * ROLE
     * @bank Bank Management
     */
    public function bankAction() {
        $bankModel = new Application_Model_Bank();

        $bankArray = $bankModel->getTheBanks('name');

        $fields = array(
            'b.name' => array(
                'title' => 'Bank Name',
                'type' => 'textbox',
                'value' => $bankArray,
                'default' => 'always'
            ),
            'b.ban' => array(
                'title' => 'BAN',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.swift_code' => array(
                'title' => 'Swift Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'b.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $bankModel->selectAllBankDetails($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $bankModel->selectAllBankDetails('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @bank Bank Add
     */
    public function addBankAction() {
// action body
        $bankModel = new Application_Model_Bank();

        $bankArray = $bankModel->getTheBanks('name');

        $fields = array(
            'b.name' => array(
                'title' => 'Bank Name',
                'type' => 'dropdown',
                'value' => $bankArray,
                'default' => 'always'
            ),
            'b.ban' => array(
                'title' => 'BAN',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.swift_code' => array(
                'title' => 'Swift Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'b.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'add';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_Bank(
                    $this->getRequest()->getParam('id'), $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Bank";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $bankModel->createRow();
                    $row->created_date = date('Y-m-d');
                    $row->modified_date = date('Y-m-d');
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;

                    $row->name = $form->getValue('name');
                    $row->ban = $form->getValue('ban');
                    $row->swift_code = $form->getValue('swift_code');
                    $row->status = $form->getValue('status');
                    $recordExist = $bankModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('administration/bank/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $bankModelData = $bankModel->fetchRow('id=' . $id);
                    $form->populate($bankModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $bankModel->selectAllBankDetails($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $bankModel->selectAllBankDetails('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @bank Bank Edit
     */
    public function editBankAction() {
// action body
        $bankModel = new Application_Model_Bank();
        $id = (int) $this->getRequest()->getParam('id');
        $row = $bankModel->fetchRow('id=' . $id);
        if ($row['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");

        $bankArray = $bankModel->getTheBanks('name');

        $fields = array(
            'b.name' => array(
                'title' => 'Bank Name',
                'type' => 'dropdown',
                'value' => $bankArray,
                'default' => 'always'
            ),
            'b.ban' => array(
                'title' => 'BAN',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.swift_code' => array(
                'title' => 'Swift Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'b.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'b.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'update';

        if ($id != 0) {
            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                $form = new Application_Form_Bank(
                        $this->getRequest()->getParam('id'), $this->view->actionType
                );
                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Bank";

                if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {

                        $id = (int) $this->getRequest()->getParam('id');
                        $row = $bankModel->fetchRow('id=' . $id);

                        //print_r($row->employee_id);die;
                        $storage = new Zend_Auth_Storage_Session();
                        $row->modified_by = $storage->read()->id;
                        $row->modified_date = date('Y-m-d');

                        $row->name = $form->getValue('name');
                        $row->ban = $form->getValue('ban');
                        $row->swift_code = $form->getValue('swift_code');
                        $row->status = $form->getValue('status');
                        $recordExist = $bankModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
                        if ($recordExist) {
                            $this->view->errorMessages = array(
                                array('ErrorMessage' => 'The record already exists')
                            );
                        } else {
                            $row->save();
                            $this->_redirect('administration/bank/' . $this->view->actionType . '/s');
                        }
                    } else {
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    $id = (int) $this->_request->getParam('id', 0);
                    if ($id > 0) {
                        $bankModelData = $bankModel->fetchRow('id=' . $id);
                        $form->populate($bankModelData->toArray());
                    }
                }
            } else
                $this->view->actionType = 'general';

            $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
            $this->view->arrSettings = $this->arrSettings;
            $search_paramter = $this->arrSettings["filters"];
            $posted_fields_string = $search_paramter['posted_values'];
            $sql_formatted_values = $search_paramter['sql_formatted_values'];
            $this->view->posted_fields_string = $posted_fields_string;
            $this->view->fields_array = $fields;

            if (isset($this->arrSettings["sort"]))
                $records = $bankModel->selectAllBankDetails($this->arrSettings["sort"], $sql_formatted_values);
            else
                $records = $bankModel->selectAllBankDetails('', $sql_formatted_values);
            $paginator = new Zend_Paginator($records);
            $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
            $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
            $this->view->paginator = $paginator;
        }else {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
    }

    /**
     * ROLE
     * @educationlevel Education Level Management
     */
    public function educationLevelAction() {

        $levelModel = new Application_Model_EducationType();

        $levelArray = $levelModel->getTheEduType('name');

        $fields = array(
            'name' => array(
                'title' => 'Education Level',
                'type' => 'dropdown',
                'value' => $levelArray,
                'default' => 'always'
            ),
            'created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'us.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';

        if ($this->_request->add == 'true')
            $this->view->actionType = 'add';
        else if ($this->_request->edit != '')
            $this->view->actionType = 'update';
        else
            $this->view->actionType = 'listing';

        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_EducationLevel(
                    $this->getRequest()->getParam('id'), $this->view->actionType
            );
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Education Level";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {
                    if ($this->view->actionType == 'add') {
                        $row = $levelModel->createRow();
                        $row->created_date = date('Y-m-d');
                        $storage = new Zend_Auth_Storage_Session();
                        $row->created_by = $storage->read()->id;
                    } else {
                        $id = (int) $this->getRequest()->getParam('id');
                        $row = $levelModel->fetchRow('id=' . $id);
                        //print_r($row->employee_id);die;
                        $storage = new Zend_Auth_Storage_Session();
                        $row->modified_by = $storage->read()->id;
                    }
                    $row->name = $form->getValue('name');
                    $recordExist = $levelModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('administration/education-level/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $levelModelData = $levelModel->fetchRow('id=' . $id);
                    $form->populate($levelModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $levelModel->selectAllLevels($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $levelModel->selectAllLevels('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @degree Degree Management
     */
    public function degreeAction() {
        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Degree Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Degree Updated Successfully');
        }
        $this->view->messages = $messages;

        $degreeModel = new Application_Model_Degree();
        $levelModel = new Application_Model_EducationType();
        $levelArray = $levelModel->getTheEduType('type');

        $degreeArray = $degreeModel->getTheDegrees('name');

        $fields = array(
            'd.name' => array(
                'title' => 'Degree',
                'type' => 'dropdown',
                'value' => $degreeArray,
                'default' => 'always'
            ),
            'e.type' => array(
                'title' => 'Education Type',
                'type' => 'dropdown',
                'value' => $levelArray,
                'default' => 'always'
            ),
            'd.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'd.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'd.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';

        $this->view->actionType = 'listing';


        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $degreeModel->selectAllDegrees($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $degreeModel->selectAllDegrees('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from degree where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @degree Degree Add
     */
    public function addDegreeAction() {
// action body

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Degree Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Degree Updated Successfully');
        }
        $this->view->messages = $messages;


        $degreeModel = new Application_Model_Degree();
        $levelModel = new Application_Model_EducationType();
        $levelArray = $levelModel->getTheEduType('type');

        $degreeArray = $degreeModel->getTheDegrees('name');

        $fields = array(
            'd.name' => array(
                'title' => 'Degree',
                'type' => 'dropdown',
                'value' => $degreeArray,
                'default' => 'always'
            ),
            'e.type' => array(
                'title' => 'Education Type',
                'type' => 'dropdown',
                'value' => $levelArray,
                'default' => 'always'
            ),
            'd.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'd.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'd.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'add';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_Degree(
                    $this->getRequest()->getParam('id'), $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Degree";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $degreeModel->createRow();
                    $row->created_date = date('Y-m-d');

                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;

                    $row->name = $form->getValue('name');
                    $row->status = $form->getValue('status');
                    $row->education_type_id = $form->getValue('education_type_id');
                    $recordExist = $degreeModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('administration/degree/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $degreeModelData = $degreeModel->fetchRow('id=' . $id);
                    $form->populate($degreeModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $degreeModel->selectAllDegrees($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $degreeModel->selectAllDegrees('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;


        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from degree where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @degree Degree Edit
     */
    public function editDegreeAction() {
// action body
        $degreeModel = new Application_Model_Degree();
        $id = (int) $this->getRequest()->getParam('id');
        $degreeModelRecord = $degreeModel->fetchRow('id = ' . $id);
        if ($degreeModelRecord['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Degree Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Degree Updated Successfully');
        }
        $this->view->messages = $messages;


        $degreeModel = new Application_Model_Degree();
        $levelModel = new Application_Model_EducationType();
        $levelArray = $levelModel->getTheEduType('type');

        $degreeArray = $degreeModel->getTheDegrees('name');

        $fields = array(
            'd.name' => array(
                'title' => 'Degree',
                'type' => 'dropdown',
                'value' => $degreeArray,
                'default' => 'always'
            ),
            'e.type' => array(
                'title' => 'Education Type',
                'type' => 'dropdown',
                'value' => $levelArray,
                'default' => 'always'
            ),
            'd.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'd.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'd.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';

        $this->view->actionType = 'update';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_Degree(
                    $this->getRequest()->getParam('id'), $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Degree";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $id = (int) $this->getRequest()->getParam('id');
                    $row = $degreeModel->fetchRow('id=' . $id);
                    //print_r($row->employee_id);die;
                    $storage = new Zend_Auth_Storage_Session();
                    $row->modified_by = $storage->read()->id;

                    $row->name = $form->getValue('name');
                    $row->status = $form->getValue('status');
                    $row->education_type_id = $form->getValue('education_type_id');
                    $recordExist = $degreeModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('administration/degree/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $degreeModelData = $degreeModel->fetchRow('id=' . $id);
                    $form->populate($degreeModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $degreeModel->selectAllDegrees($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $degreeModel->selectAllDegrees('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;


        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from degree where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @finddegree Ajax Call for Degree List
     */
    public function findDegreeAction() {
        $this->_helper->layout->disableLayout();
        $id = (int) $this->_request->getParam('id');

        $degree = new Application_Model_Degree();
        $allDegrees = $degree->fetchAll("education_type_id='$id' and status = 'Active'", "name asc");

        $output = '';
        if ($allDegrees) {
            foreach ($allDegrees as $degreeRow) {
                $output .= ( $output == '') ? $degreeRow['id'] . '|' . $degreeRow['name'] : '#' . $degreeRow['id'] . '|' . $degreeRow['name'];
            }
        }

        echo $output;
        die('');
    }

    /**
     * ROLE
     * @bankbranch Bank Branch Management
     */
    public function bankBranchAction() {

        $bankModel = new Application_Model_Bank();
        $bankArray = $bankModel->getTheBanks('name');

        $fields = array(
            'bb.name' => array(
                'title' => 'Branch Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'b.name' => array(
                'title' => 'Bank',
                'type' => 'dropdown',
                'value' => $bankArray,
                'default' => 'yes'
            ),
            'bb.code' => array(
                'title' => 'Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
//            'bb.country_id' => array(
//                'title' => 'Country',
//                'type' => 'textbox',
//                'value' => '',
//                'default' => 'no'
//            ),
            'st.name' => array(
                'title' => 'State',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => $country,
                'default' => 'yes'
            ),
            'ct.name' => array(
                'title' => 'City',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'bb.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'bb.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'bb.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';

        $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;
        $branchMdl = new Application_Model_Branch();
        if (isset($this->arrSettings["sort"]))
            $records = $branchMdl->selectAllBranches($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $branchMdl->selectAllBranches('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        $cityModel = new Application_Model_City();
        foreach ($paginator as $page) {
            if ($page->city_id) {
                $cityRow = $cityModel->fetchRow("id='" . $page->city_id . "'");
                $page->city_id = $cityRow->name;
            }
        }

        $bankModel = new Application_Model_Bank();
        $stateModel = new Application_Model_State();
        $countryModel = new Application_Model_Country();

        $bnkArray = $bankModel->getTheBanks('id');
        $stArray = $stateModel->getTheStates('id');
        $cntryArray = $countryModel->allCountry('country');

        foreach ($paginator as $page) {
            $page->country_id = $cntryArray[$page->country_id];
            $page->state_id = $stArray[$page->state_id];
            $page->bank_id = $bnkArray[$page->bank_id];

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from bank_branch where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @bankbranch Bank Branch Add
     */
    public function addBankBranchAction() {
// action body

        $bankModel = new Application_Model_Bank();
        $bankArray = $bankModel->getTheBanks('name');

        $fields = array(
            'bb.name' => array(
                'title' => 'Branch Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'b.name' => array(
                'title' => 'Bank',
                'type' => 'dropdown',
                'value' => $bankArray,
                'default' => 'yes'
            ),
            'bb.code' => array(
                'title' => 'Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
//            'bb.country_id' => array(
//                'title' => 'Country',
//                'type' => 'textbox',
//                'value' => '',
//                'default' => 'no'
//            ),
            'st.name' => array(
                'title' => 'State',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => $country,
                'default' => 'yes'
            ),
            'ct.name' => array(
                'title' => 'City',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'bb.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'bb.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'bb.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';

        $this->view->actionType = 'add';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $this->view->inc_tooltip = true;
            $branchModel = new Application_Model_Branch();
            if ($this->view->actionType == 'add') {
                $form = new Application_Form_Branch(
                        '', '', '', $this->view->actionType);
            } else {
                $branchRow = $branchModel->fetchRow('id=' . $this->getRequest()->getParam('id'));
                $branchData = $branchRow->toArray();
                $form = new Application_Form_Branch(
                        $branchData['country_id'], $branchData['state_id'], $branchData['city_id'], $this->getRequest()->getParam('id'), $this->view->actionType);
            }
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Bank Branch";
            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $branchModel->createRow();
                    $row->created_date = date('Y-m-d');
                    ;
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;

                    $row->name = $form->getValue('name');
                    $row->bank_id = $form->getValue('bank_id');
                    $row->code = $form->getValue('code');
                    $row->country_id = $form->getValue('country_id');
                    $row->state_id = $form->getValue('state_id');
                    $row->city_id = $form->getValue('city_id');
                    $row->status = $form->getValue('status');

//                    $recordExist = $degreeModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
//                    if ($recordExist) {
//                        $this->view->errorMessages = array(
//                            array('ErrorMessage' => 'The record already exists')
//                        );
//                    }
                    // else {
                    $row->save();
                    $this->_redirect('administration/bank-branch/' . $this->view->actionType . '/s');
                    //}
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $branchModelData = $branchModel->fetchRow('id=' . $id);
                    $form->populate($branchModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;
        $branchMdl = new Application_Model_Branch();
        if (isset($this->arrSettings["sort"]))
            $records = $branchMdl->selectAllBranches($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $branchMdl->selectAllBranches('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        $cityModel = new Application_Model_City();
        foreach ($paginator as $page) {
            if ($page->city_id) {
                $cityRow = $cityModel->fetchRow("id='" . $page->city_id . "'");
                $page->city_id = $cityRow->name;
            }
        }

        $bankModel = new Application_Model_Bank();
        $stateModel = new Application_Model_State();
        $countryModel = new Application_Model_Country();

        $bnkArray = $bankModel->getTheBanks('id');
        $stArray = $stateModel->getTheStates('id');
        $cntryArray = $countryModel->allCountry('country');

        foreach ($paginator as $page) {
            $page->country_id = $cntryArray[$page->country_id];
            $page->state_id = $stArray[$page->state_id];
            $page->bank_id = $bnkArray[$page->bank_id];

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from bank_branch where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @bankbranch Bank Branch Edit
     */
    public function editBankBranchAction() {
// action body

        $bankModel = new Application_Model_Bank();
        $branchModel = new Application_Model_Branch();
        $id = (int) $this->getRequest()->getParam('id');
        $row = $branchModel->fetchRow('id=' . $id);
        if ($row['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        $bankArray = $bankModel->getTheBanks('name');

        $fields = array(
            'bb.name' => array(
                'title' => 'Branch Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'b.name' => array(
                'title' => 'Bank',
                'type' => 'dropdown',
                'value' => $bankArray,
                'default' => 'yes'
            ),
            'bb.code' => array(
                'title' => 'Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
//            'bb.country_id' => array(
//                'title' => 'Country',
//                'type' => 'textbox',
//                'value' => '',
//                'default' => 'no'
//            ),
            'st.name' => array(
                'title' => 'State',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => $country,
                'default' => 'yes'
            ),
            'ct.name' => array(
                'title' => 'City',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'bb.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'bb.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'bb.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';

        $this->view->actionType = 'update';

        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $this->view->inc_tooltip = true;
            $branchModel = new Application_Model_Branch();
            if ($this->view->actionType == 'add') {
                $form = new Application_Form_Branch(
                        '', '', '', $this->view->actionType);
            } else {
                $branchRow = $branchModel->fetchRow('id=' . $this->getRequest()->getParam('id'));
                $branchData = $branchRow->toArray();
                $form = new Application_Form_Branch(
                        $branchData['country_id'], $branchData['state_id'], $branchData['city_id'], $branchData['status'], $this->getRequest()->getParam('id'), $this->view->actionType);
            }

            $hdnCountryId = new Zend_Form_Element_Hidden('hdn_country_id');
            $hdnCountryId->setValue($branchData['country_id']);
            $form->addElement($hdnCountryId);

            $hdnStateId = new Zend_Form_Element_Hidden('hdn_state_id');
            $hdnStateId->setValue($branchData['state_id']);
            $form->addElement($hdnStateId);

            $hdnCityId = new Zend_Form_Element_Hidden('hdn_city_id');
            $hdnCityId->setValue($branchData['city_id']);
            $form->addElement($hdnCityId);

            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Bank Branch";
            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $id = (int) $this->getRequest()->getParam('id');
                    $row = $branchModel->fetchRow('id=' . $id);
                    //print_r($row->employee_id);die;
                    $storage = new Zend_Auth_Storage_Session();
                    $row->modified_by = $storage->read()->id;

                    $row->name = $form->getValue('name');
                    $row->bank_id = $form->getValue('bank_id');
                    $row->code = $form->getValue('code');
                    $row->country_id = $form->getValue('country_id');
                    $row->state_id = $form->getValue('state_id');
                    $row->city_id = $form->getValue('city_id');
                    $row->status = $form->getValue('status');
//                    $recordExist = $degreeModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
//                    if ($recordExist) {
//                        $this->view->errorMessages = array(
//                            array('ErrorMessage' => 'The record already exists')
//                        );
//                    }
                    // else {
                    $row->save();
                    $this->_redirect('administration/bank-branch/' . $this->view->actionType . '/s');
                    //}
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $branchModelData = $branchModel->fetchRow('id=' . $id);
                    $form->populate($branchModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;
        $branchMdl = new Application_Model_Branch();
        if (isset($this->arrSettings["sort"]))
            $records = $branchMdl->selectAllBranches($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $branchMdl->selectAllBranches('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;

        $cityModel = new Application_Model_City();
        foreach ($paginator as $page) {
            if ($page->city_id) {
                $cityRow = $cityModel->fetchRow("id='" . $page->city_id . "'");
                $page->city_id = $cityRow->name;
            }
        }

        $bankModel = new Application_Model_Bank();
        $stateModel = new Application_Model_State();
        $countryModel = new Application_Model_Country();

        $bnkArray = $bankModel->getTheBanks('id');
        $stArray = $stateModel->getTheStates('id');
        $cntryArray = $countryModel->allCountry('country');

        foreach ($paginator as $page) {
            $page->country_id = $cntryArray[$page->country_id];
            $page->state_id = $stArray[$page->state_id];
            $page->bank_id = $bnkArray[$page->bank_id];

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from bank_branch where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @leavereason Leave Reason Management
     */
    public function leaveReasonAction() {

        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;
        $fields = array(
            'lr.reason_name' => array(
                'title' => 'Reason',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'lr.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'lr.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'lr.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            )
        );
        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';

        $this->view->actionType = 'listing';


        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlLeaveReason = new Application_Model_LeaveReason();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlLeaveReason->selectAllLeaveReasons($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlLeaveReason->selectAllLeaveReasons('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;


        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from leave_reason where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @leavereason Leave Reason Add
     */
    public function addLeaveReasonAction() {
// action body

        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;
        $fields = array(
            'lr.reason_name' => array(
                'title' => 'Reason',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'lr.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'lr.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'lr.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            )
        );
        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'add';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $leaveReasonModel = new Application_Model_LeaveReason();
            $form = new Application_Form_LeaveReason($this->getRequest()->getParam('id'));
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Leave Reason";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $leaveReasonModel->createRow();
                    $row->created_date = date('Y-m-d');
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;
                    $row->reason_name = ucwords(strtolower($form->getValue('reason_name')));
                    $row->option_group = ucwords(strtolower($form->getValue('option_group')));
                    $row->status = $form->getValue('status');
                    $row->save();
                    $this->_redirect('administration/leave-reason/' . $this->view->actionType . '/s');
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $leaveReasonModelData = $leaveReasonModel->fetchRow('id=' . $id);
                    $form->populate($leaveReasonModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlLeaveReason = new Application_Model_LeaveReason();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlLeaveReason->selectAllLeaveReasons($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlLeaveReason->selectAllLeaveReasons('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;


        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from leave_reason where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @leavereason Leave Reason Edit
     */
    public function editLeaveReasonAction() {
// action body
        $leaveReasonModel = new Application_Model_LeaveReason();
        $id = (int) $this->getRequest()->getParam('id');
        $leaveReasonModelRecord = $leaveReasonModel->fetchRow('id = ' . $id);
        if ($leaveReasonModelRecord['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");


        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;
        $fields = array(
            'lr.reason_name' => array(
                'title' => 'Reason',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'Created By',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'lr.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'lr.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'lr.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            )
        );
        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'update';

        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $leaveReasonModel = new Application_Model_LeaveReason();
            $form = new Application_Form_LeaveReason($this->getRequest()->getParam('id'));
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Leave Reason";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $id = (int) $this->getRequest()->getParam('id');
                    $row = $leaveReasonModel->fetchRow('id=' . $id);
                    $storage = new Zend_Auth_Storage_Session();
                    $row->modified_by = $storage->read()->id;

                    $row->reason_name = ucwords(strtolower($form->getValue('reason_name')));
                    $row->option_group = ucwords(strtolower($form->getValue('option_group')));
                    $row->status = $form->getValue('status');
                    $row->save();
                    $this->_redirect('administration/leave-reason/' . $this->view->actionType . '/s');
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $leaveReasonModelData = $leaveReasonModel->fetchRow('id=' . $id);
                    $form->populate($leaveReasonModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlLeaveReason = new Application_Model_LeaveReason();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlLeaveReason->selectAllLeaveReasons($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlLeaveReason->selectAllLeaveReasons('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;


        foreach ($paginator as $page) {

// Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

// Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from leave_reason where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @dateformat Set Date Format
     */
    public function dateFormatAction() {
        $messages = array();


        if ($this->_request->update == 's') {
            $messages[] = array('success', 'Date Format Updated Successfully');
        }

        $this->view->messages = $messages;

        $storage = new Zend_Auth_Storage_Session();
        $modified_by = $storage->read()->id;

        $dateFormatForm = new Application_Form_DateFormat();
        $userModel = new Application_Model_Dateformat();
        $dateFormatForm->setMethod('post');
        $dateFormatForm->setName('dateformat');
        $dateFormatForm->setAction('user/date-format');

        $dateFormatForm->setAttrib('class', 'form-horizontal clearfix');
        $this->view->form = $dateFormatForm;

        $this->view->controller = $this->getRequest()->getControllerName();

        if ($this->_request->isPost()) {
            $format_id = $this->_request->getPost('format');

            if ($format_id == 1) {
                //$status = array('status' => '1');
                for ($i = 1; $i <= 4; $i++) {
                    if ($i == $format_id) {
                        $status = array('status' => '1');
                    } else {
                        $status = array('status' => '0');
                    }
                    $userModel->UpdatedateFormat($i, $status);
                }
            } elseif ($format_id == 2) {

                for ($i = 1; $i <= 4; $i++) {
                    if ($i == $format_id) {

                        $status = array('status' => '1');
                    } else {
                        $status = array('status' => '0');
                    }

                    $userModel->UpdatedateFormat($i, $status);
                }
            } elseif ($format_id == 3) {

                for ($i = 1; $i <= 4; $i++) {
                    if ($i == $format_id) {

                        $status = array('status' => '1');
                    } else {
                        $status = array('status' => '0');
                    }

                    $userModel->UpdatedateFormat($i, $status);
                }
            } elseif ($format_id == 4) {

                for ($i = 1; $i <= 4; $i++) {
                    if ($i == $format_id) {

                        $status = array('status' => '1');
                    } else {
                        $status = array('status' => '0');
                    }

                    $userModel->UpdatedateFormat($i, $status);
                }
            }

            $this->_redirect('administration/date-format/update/s');
        }
    }

    /**
     * Check the bank name or bank branch is exist or not in table.
     */
    public function checkBankNameExistAction() {
        $this->_helper->layout()->disableLayout();
        $bankId = $this->_request->getParam('bankId');
        $branchName = $this->_request->getParam('branchName');
        $bankName = $this->_request->getParam('bankName');
        if ($bankId && $branchName) {
            $bankBranchModel = new Application_Model_Branch();
            $where = 'bank_id = ' . $bankId . ' AND name like' . "'$branchName'";
            $results = $bankBranchModel->fetchRow($where);
        } else {
            $bankModel = new Application_Model_Bank();
            $where = 'name like ' . "'$bankName'";
            $results = $bankModel->fetchRow($where);
        }
        if (count($results)) {
            echo 1;
        } else {
            echo 0;
        }
        die;
    }

    /**
     * Check the education type and degree name is exist or not in table.
     */
    public function checkDegreeNameExistAction() {
        $this->_helper->layout()->disableLayout();
        $degreeModel = new Application_Model_Degree();
        $eduTypeId = $this->_request->getParam('eduTypeId');
        $degreeName = $this->_request->getParam('degreeName');
        $where = 'education_type_id = ' . $eduTypeId . ' AND name like' . "'$degreeName'";
        $results = $degreeModel->fetchRow($where);

        if (count($results)) {
            echo 1;
        } else {
            echo 0;
        }
        die;
    }

    public function getCountryNameByCountryId($sql_formatted_values, $nationalityList) {
        $str = $sql_formatted_values;
        $from = "%";
        $to = "%";
        $sub = substr($str, strpos($str, $from) + strlen($from), strlen($str));
        $countryName = substr($sub, 0, strpos($sub, $to));
        $key = array_search($countryName, $nationalityList);
        return $key;
    }

    public function getStateNameByStateId($sql_formatted_values, $nationalityList) {
        $str = $sql_formatted_values;
        $from = "%";
        $to = "%";
        $sub = substr($str, strpos($str, $from) + strlen($from), strlen($str));
        $stateName = substr($sub, 0, strpos($sub, $to));
        $stateModel = new Application_Model_State();
        $stateId = $stateModel->getStateId($stateName);
        return $stateId;
    }

    /**
     * ROLE
     * @bank Company Docs Management
     */
    public function companyDocsAction() {

        $companyDocsModel = new Application_Model_CompanyDocs();

        $companyDocsArray = $companyDocsModel->getTheDocs('name');

        $fields = array(
            'cd.name' => array(
                'title' => 'Document Name',
                'type' => 'dropdown',
                'value' => $companyDocsArray,
                'default' => 'always'
            ),
            'cd.file_name' => array(
                'title' => 'File Name',
                'type' => 'dropdown',
                'value' => $docsArray,
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'cd.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'cd.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'cd.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $companyDocsModel->selectAllDocsDetails($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $companyDocsModel->selectAllDocsDetails('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @bank Document Add
     */
    public function addCompanyDocAction() {
// action body
        $companyDocsModel = new Application_Model_CompanyDocs();

        $companyDocsArray = $companyDocsModel->getTheDocs('name');

        $fields = array(
            'cd.name' => array(
                'title' => 'Document Name',
                'type' => 'dropdown',
                'value' => $companyDocsArray,
                'default' => 'always'
            ),
            'cd.file_name' => array(
                'title' => 'File Name',
                'type' => 'dropdown',
                'value' => $docsArray,
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'cd.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'cd.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'cd.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'add';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $form = new Application_Form_CompanyDocs(
                    $this->getRequest()->getParam('id'), $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Company Document";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $form->file_name->receive();
                    $fileInfo = pathinfo($form->file_name->getFileName());
                    $corruptFileUploaded = false;
                    $row = $companyDocsModel->createRow();
                    if ($fileInfo) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                        $fileMimeType = finfo_file($finfo, $fileInfo['dirname'] . "/" . $fileInfo['basename']);
                        if ($fileMimeType == 'application/pdf' || $fileMimeType == 'application/doc' || $fileMimeType == 'application/docx' || $fileMimeType == 'application/zip') {
                            $fileName = strtolower(str_replace(" ", "_", $fileInfo['filename']));
                            $random = rand();
                            $newFile = $fileInfo["dirname"] . "/" . $fileName . "_" . $random . $id . "." . $fileInfo['extension'];
                            $file_name = $random . $id . "." . $fileInfo['extension'];
                            $filterRenameFile = new Zend_Filter_File_Rename(array(
                                'target' => $newFile,
                                'overwrite' => true,
                            ));
                            $filterRenameFile->filter($form->file_name->getFileName());
                            $row->file_name = $fileName . "_" . $random . $id . "." . $fileInfo['extension'];
                        } else {
                            $corruptFileUploaded = true;
                        }
                    }
                    $row->created_date = date('Y-m-d');
                    $row->modified_date = date('Y-m-d');
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;

                    $row->name = $form->getValue('name');
                    $row->status = $form->getValue('status');
                    $recordExist = $companyDocsModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist || $corruptFileUploaded) {
                        if ($recordExist) {
                            $this->view->errorMessages = array(
                                array('ErrorMessage' => 'The record already exists')
                            );
                        } elseif ($corruptFileUploaded) {
                            $this->view->errorMessages = array(
                                array('ErrorMessage' => 'This file is corrput. Please upload a valid document file.')
                            );
                        }
                    } else {
                        $row->save();
                        $this->_redirect('administration/company-docs/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $companyDocsModelData = $companyDocsModelData->fetchRow('id=' . $id);
                    $form->populate($companyDocsModelData->toArray());
                }
            }
        } else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        if (isset($this->arrSettings["sort"]))
            $records = $companyDocsModel->selectAllDocsDetails($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $companyDocsModel->selectAllDocsDetails('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @bank Bank Edit
     */
    public function editCompanyDocAction() {
// action body
        $companyDocsModel = new Application_Model_CompanyDocs();
        $id = (int) $this->getRequest()->getParam('id');
        $row = $companyDocsModel->fetchRow('id=' . $id);
        if ($row['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");

        $docsArray = $companyDocsModel->getTheDocs('name');

        $fields = array(
            'cd.name' => array(
                'title' => 'Document Name',
                'type' => 'dropdown',
                'value' => $docsArray,
                'default' => 'always'
            ),
            'cd.file_name' => array(
                'title' => 'File Name',
                'type' => 'dropdown',
                'value' => $docsArray,
                'default' => 'always'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'cd.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'cd.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'cd.status' => array(
                'title' => 'Status',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        $sql_formatted_values = '';


        $this->view->actionType = 'update';

        if ($id != 0) {
            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                $form = new Application_Form_CompanyDocs(
                        $this->getRequest()->getParam('id'), $this->view->actionType
                );
                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Document";

                if ($this->_request->isPost() && !isset($this->_request->searchFilter) && !isset($this->_request->removeallFilter) && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {

                        $id = (int) $this->getRequest()->getParam('id');
                        $row = $companyDocsModel->fetchRow('id=' . $id);

                        $form->file_name->receive();
                        $fileInfo = pathinfo($form->file_name->getFileName());
                        $corruptFileUploaded = false;
                        $storage = new Zend_Auth_Storage_Session();
                        $row->modified_by = $storage->read()->id;
                        $row->modified_date = date('Y-m-d');
                        if ($fileInfo) {
                            $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                            $fileMimeType = finfo_file($finfo, $fileInfo['dirname'] . "/" . $fileInfo['basename']);
                            if ($fileMimeType == 'application/pdf' || $fileMimeType == 'application/doc' || $fileMimeType == 'application/docx' || $fileMimeType == 'application/zip') {
                                $fileName = strtolower(str_replace(" ", "_", $fileInfo['filename']));
                                $random = rand();
                                $newFile = $fileInfo["dirname"] . "/" . $fileName . "_" . $random . $id . "." . $fileInfo['extension'];

                                $file_name = $random . $id . "." . $fileInfo['extension'];
                                $filterRenameFile = new Zend_Filter_File_Rename(array(
                                    'target' => $newFile,
                                    'overwrite' => true,
                                ));
                                $filterRenameFile->filter($form->file_name->getFileName());
                                $row->file_name = $fileName . "_" . $random . $id . "." . $fileInfo['extension'];
                            } else {
                                $corruptFileUploaded = true;
                            }
                        }

                        //print_r($row->employee_id);die;

                        $row->name = $form->getValue('name');
                        $row->status = $form->getValue('status');
                        $recordExist = $companyDocsModel->checkOnEditIfRecordExists(@$id, ucwords(strtolower($form->getValue('name'))));
                        if ($recordExist || $corruptFileUploaded) {
                            if ($recordExist) {
                                $this->view->errorMessages = array(
                                    array('ErrorMessage' => 'The record already exists')
                                );
                            } elseif ($corruptFileUploaded) {
                                $this->view->errorMessages = array(
                                    array('ErrorMessage' => 'This file is corrput. Please upload a valid document file.')
                                );
                            }
                        } else {
                            $row->save();
                            $this->_redirect('administration/company-docs/' . $this->view->actionType . '/s');
                        }
                    } else {
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    $id = (int) $this->_request->getParam('id', 0);
                    if ($id > 0) {
                        $companyDocsModelData = $companyDocsModel->fetchRow('id=' . $id);
                        $form->populate($companyDocsModelData->toArray());
                    }
                }
            } else
                $this->view->actionType = 'general';

            $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
            $this->view->arrSettings = $this->arrSettings;
            $search_paramter = $this->arrSettings["filters"];
            $posted_fields_string = $search_paramter['posted_values'];
            $sql_formatted_values = $search_paramter['sql_formatted_values'];
            $this->view->posted_fields_string = $posted_fields_string;
            $this->view->fields_array = $fields;

            if (isset($this->arrSettings["sort"]))
                $records = $companyDocsModel->selectAllDocsDetails($this->arrSettings["sort"], $sql_formatted_values);
            else
                $records = $companyDocsModel->selectAllDocsDetails('', $sql_formatted_values);
            $paginator = new Zend_Paginator($records);
            $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
            $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
            $this->view->paginator = $paginator;
        }else {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
    }

    public function downloadCompanyDocAction() {
        $companyDocsModel = new Application_Model_CompanyDocs();
        $id = (int) $this->getRequest()->getParam('id');
        $row = $companyDocsModel->fetchRow('id=' . $id);
        if ($row['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        $filename = $row['file_name']; //name of the file
        $filepath = "/public/uploads/Company_Material/" . $filename; //location of the file. I have put $file since your file is create on the same folder where this script is

        $this->_redirect($this->getRequest()->getBaseUrl() . "/uploads/Company_Material/" . $filename);
        exit;
    }

    public function updateResourcesAction($role_id = 0) {
        $module_dir = Zend_Controller_Front::getInstance()->getControllerDirectory();
        $mdlAclUserRole = new Application_Model_AclUserRole();
        $mdlResources = new Application_Model_Resources();
        $rolesTitleArray = array();

        foreach ($module_dir as $dir => $dirpath) {
            $diritem = new DirectoryIterator($dirpath);
            foreach ($diritem as $item) {
                if ($item->isFile()) {
                    if (strstr($item->getFilename(), 'Controller.php') != FALSE) {
                        include_once $dirpath . '/' . $item->getFilename();
                        $controller = strtolower(str_replace('Controller.php', '', $item->getFilename()));
                        $source = file_get_contents($dirpath . '/' . $item->getFilename());
                        $comment = array(
                            T_DOC_COMMENT, // All comments since PHP5      
                        );
                    }
                }
            }
            foreach (get_declared_classes() as $class) {
                if (is_subclass_of($class, 'Zend_Controller_Action')) {
                    $controllerTitle = preg_replace('/([A-Z])/', ' ${1}', substr($class, 0, strpos($class, "Controller")));
                    $controllerName = strtolower(substr($class, 0, strpos($class, "Controller")));
                    $controllerList['name'][$controllerName] = $controllerName;
                    $controllerList['title'][$controllerTitle] = $controllerTitle;
                    foreach (get_class_methods($class) as $method) {
                        if (strstr($method, 'Action') != false) {
                            $actionTitle = ucfirst(preg_replace('/([A-Z])/', ' ${1}', substr($method, 0, strpos($method, "Action"))));
                            $actionName = strtolower(substr($method, 0, strpos($method, "Action")));
                            if ($controllerName == 'index') {
                                if ($actionName == 'index' or $actionName == 'logout' or $actionName == 'recoverpassword' or $actionName == 'forgotpassword' or $actionName == 'permissiondenied')
                                    $mdlAclUserRole->addRolePermission($role_id, $controllerName, $actionName, 'allow');
                                else
                                    $mdlAclUserRole->addRolePermission($role_id, $controllerName, $actionName);
                            } else {
                                $mdlAclUserRole->addRolePermission($role_id, $controllerName, $actionName);
                            }
                            if (@$rolesTitleArray[$controllerName][$actionName])
                                $controllerList['actions'][$controllerName][$actionName] = $rolesTitleArray[$controllerName][$actionName];
                            else
                                $controllerList['actions'][$controllerName][$actionName] = $actionTitle;
                        }
                    }
                }
            }
        }
        sort($controllerList['name']);
        sort($controllerList['title']);
        foreach ($controllerList['name'] as $controller) {
            $actions = $controllerList['actions'][$controller];
            foreach ($actions as $action) {
                try {
                    $actionName = str_replace(" ", "-", strtolower($action));
                    $row = $mdlResources->createRow();
                    $row->controller = $controller;
                    $row->action = $actionName;
                    $row->created_by = 0;
                    $row->save();
                } catch (Exception $ex) {
                    
                }
            }
        }
        $this->_redirect('administration/management/update/s');
    }

}
