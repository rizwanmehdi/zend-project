
<?php

class DashboardController extends Zend_Controller_Action {

    public function indexAction() {
        
        $handBookName = $this->_request->download;
        if(!empty($handBookName)){
            
            $this->_helper->ViewRenderer->setNoRender();  //to b ask

            $filePath = realpath(APPLICATION_PATH . '/../') . '/public/uploads/Employee_Documents/'.$handBookName.'';
            if (file_exists($filePath)) {
             
                header('Content-Description: File Transfer');
                header('Content-Type:  application/pdf'); //application/octet-stream
                header('Content-Disposition: attachment; filename='.$handBookName.'');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                ob_clean();
                flush();
                readfile($filePath);
            }

            exit;
        
        }
        
//        $this->view->message = 'Your alla bala message comes here...';
        $messages = array();

        $this->view->messages = $messages;

        $access_list = new Application_Controller_Plugin_Acl();

        $storage = new Zend_Auth_Storage_Session();

        $roleModel = new Application_Model_AclRole();

        $data = $storage->read();

        $roleList = $roleModel->selectRoles();

        if ($roleList[$data->role_id]['role_admin'] == 'Active') {

            $this->view->role = 'admin';
        }

        if ($data->name == 'admin@nexthrm.com') {

            $this->view->user_name = 'admin@nexthrm.com';
        }


        if ($data->name == 'admin@nexthrm.com') {

            $user_type = 'superadmin';
        } else {

            $user_type = 'notsuperadmin';
        }


        $this->view->employeeId = $storage->read()->employee_id;   //to b ask

        $eId = $storage->read()->employee_id;


///// Bulleting News Section Start : to be displayed for all Roles /////////////////////////////////////////////////


        $newsModel = new Application_Model_News();
        $newsSelect = $newsModel->select()
                ->where('status="active"')
                ->order("created_date DESC")
                ->limit(10, 0);
        $nRows = $newsModel->fetchAll($newsSelect);
        $counta = count($nRows);
        if ($counta > 0)
            $this->view->conditionNB = true;

//        $this->view->newsRows = $nRows;
/////// Bulleting News Section End  ///////////////////////////////////////////////////////////////////////////////

///// Company Documents Section Start : to be displayed for all Roles /////////////////////////////////////////////////


        $companyDocsModel = new Application_Model_CompanyDocs();
        $companyDocsSelect = $companyDocsModel->select()
                             ->where('status="active"')
                             ->order("created_date DESC");
        $companyDocsRows = $companyDocsModel->fetchAll($companyDocsSelect);
        $companyDocsRowsCount = count($companyDocsRows);
        if ($companyDocsRowsCount > 0){
            $this->view->conditionCD = true;
            $this->view->companyDocsRows = $companyDocsRows;
            
        }
//        $this->view->newsRows = $nRows;
///// Company Documents Section End  ///////////////////////////////////////////////////////////////////////////////

///// Employee CNIC Expiry Start : to be displayed for HR ////////////////////////////////////////////////
        
        if ($access_list->checkPermission('dashboard', 'FindCnicExpiry')){//print_r($data);die;
            $acl2Plugin = new Application_Controller_Plugin_Acl2();
            $empList = $acl2Plugin->getEmployeeList();
            if ($empList != '') {
                $dependantCondition = "(emp.current_supervisor_id= '" . $eId . "' or emp.current_teamlead_id = '" . $eId . "')";
            } else {
                $dependantCondition = "1";
            }
            $this->view->emp_cnic_exp_flag = TRUE;
            $employeeModel = new Application_Model_Employees();
            $employeeList = $employeeModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('emp'=>'employee'), array('emp.number','emp.cnic_expiry','emp.name'))
                    ->where($dependantCondition . ' and DATEDIFF(emp.cnic_expiry,NOW())<=30 AND emp.current_job_status NOT IN ("Resigned","Terminated")')// AND DATEDIFF(emp.cnic_expiry,NOW()) >= -50')
                    ->order("emp.cnic_expiry DESC");
            $e_list = $employeeModel->fetchAll($employeeList);
            //echo "<pre>";print_r($e_list);die;
            $this->view->emp_cnic_exp = $e_list;
        }

/////// Employee CNIC Expiry Section End  ///////////////////////////////////////////////////////////////////////////////
///// Employee CNIC Expiry Start : to be displayed for HR ////////////////////////////////////////////////
        
        if ($access_list->checkPermission('dashboard', 'myCnic')){//echo $data->role_id;die;
            $my_cnic_flag = true;
            $this->view->my_cnic_flag = $my_cnic_flag;
            $role_id = $data->role_id;
            $emp_id = $data->employee_id;
            $emp_model = new Application_Model_Employees();
            $emp_data = $emp_model->fetchRow("id = ".$emp_id);
            $cnic_exp = strtotime($emp_data['cnic_expiry']);//echo $cnic_exp;die;
            $today = strtotime(date("Y-m-d"));//echo $cnic_exp."  ".  $today          ;die;
            $diff = ($cnic_exp - $today);//echo "++++".$diff;die;
            if($cnic_exp < $today){
                $diff = $diff/(3600*24);
                $this->view->diff = $diff;
            }else{//echo "here";die;
                //$diff = date("d",$diff);
                $diff = $diff/(3600*24);
                //echo "++++".$diff;die;
                $this->view->diff = $diff;
            }$this->view->role_id = $role_id;
        }

/////// Employee CNIC Expiry Section End  ///////////////////////////////////////////////////////////////////////////////        
        
///// Employee  Probation Complete Dates Start : to be displayed for HR ////////////////////////////////////////////////
        
        if ($access_list->checkPermission('dashboard', 'EmployeeconfirmationDate')){
            $acl2Plugin = new Application_Controller_Plugin_Acl2();
            $empList = $acl2Plugin->getEmployeeList();
            if ($empList != '') {
                $dependantCondition = "(emp.current_supervisor_id= '" . $eId . "' or emp.current_teamlead_id = '" . $eId . "')";
            } else {
                $dependantCondition = "1";
            }
            $this->view->emp_conf_date_flag = TRUE;
            $employeeModel = new Application_Model_Employees();
            $employeeList = $employeeModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('emp'=>'employee'), array('emp.number','emp.confirmation_date','emp.name'))
                    ->where($dependantCondition . ' and DATEDIFF(emp.confirmation_date,NOW())<=30 AND DATEDIFF(emp.confirmation_date,NOW()) >= 0 AND emp.current_job_status NOT IN ("Resigned","Terminated")')
                    ->order("emp.confirmation_date ASC");
            $e_list = $employeeModel->fetchAll($employeeList);
            //echo "<pre>";print_r($e_list);die;
            $this->view->emp_conf_date = $e_list;
        }

/////// Employee  Probation Complete Date Section End  ///////////////////////////////////////////////////////////////////////////////     
        
        ///// Employee  Year Complete Dates Start : to be displayed for HR ////////////////////////////////////////////////
        
        if ($access_list->checkPermission('dashboard', 'EmployeeYearCompleationDate')){//echo "here";die;
            $acl2Plugin = new Application_Controller_Plugin_Acl2();
            $empList = $acl2Plugin->getEmployeeList();
            if ($empList != '') {
                $dependantCondition = "(employee.current_supervisor_id= '" . $eId . "' or employee.current_teamlead_id = '" . $eId . "')";
            } else {
                $dependantCondition = "1";
            }
            $salaryModel = new Application_Model_Salary();
            $this->view->emp_year_completion_flag = TRUE;
            $employeeModel = new Application_Model_Employees();
            /*$yearCompletionList = $employeeModel->select()
                                  ->setIntegrityCheck(FALSE)
                                  ->from(array("employee"=>'employee'),array('employee.number','employee.name','employee.current_job_status','employee.joining_date', 
                                  'CONCAT( YEAR( NOW() ),"-", MONTH(employee.joining_date),"-",DAY(employee.joining_date)) as year_completion_date'))
                                  ->where('MONTH(employee.joining_date) = MONTH(NOW())')
                                  ->order('employee.joining_date desc'); */
              // echo $yearCompletionList;exit;
            $salaryList = $salaryModel->select()
                    ->setIntegrityCheck(FALSE)
                    ->from('salary',array('salary.employee_id','salary.next_increment_date'))
                    ->joinInner('employee','salary.employee_id = employee.id and employee.current_job_status Not in ("Resigned","Terminate") and '.$dependantCondition,array('employee.number','employee.name','employee.current_job_status'))                    
                    ->where('DATEDIFF(salary.next_increment_date,NOW())<=30 AND DATEDIFF(salary.next_increment_date,NOW()) >= 0')
                    ->order("salary.next_increment_date ASC");
            //echo $salaryList;exit;
            $s_list = $salaryModel->fetchAll($salaryList);//echo "<pre>"; print_r($s_list);die;
            $this->view->emp_year_complete = $s_list;
        }

/////// Employee  Year Complete Date Section End  /////////////////////////////////////////////////////////////////////////////// 
        
///// Employee Overview Section Start : to be displayed for all Roles ////////////////////////////////////////////////
        $employeeAttendanceModel = new Application_Model_Leave();
        $acl2Plugin = new Application_Controller_Plugin_Acl2();
        $empList = $acl2Plugin->getEmployeeList();
        if ($empList != '') {
            $dependantCondition = "((r.supervisor_id= '" . $eId . "' or r.subordinate_id = '" . $eId . "') and r.recent_record= '1')";
        } else {
            $dependantCondition = "1";
        }

        $salaryModel = new Application_Model_Salary();
        $salarySelect = $salaryModel->select()
                ->setIntegrityCheck(false)
                ->from(array('s' => 'salary'), array('s.*'))
                ->joinLeft(array('e' => 'employee'), 's.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                ->joinLeft(array('r' => 'report_to'), 'r.employee_id = e.id', array('r.employee_id as r.employee_id'))
                ->where($dependantCondition . ' and e.current_job_status NOT IN ("Resigned","Terminated") and YEAR(next_increment_date) = YEAR(CURDATE()) and MONTH(next_increment_date) = MONTH(CURDATE())')
                ->group('e.id')
                ->limit(5, 0);
        $overViewRows = $salaryModel->fetchAll($salarySelect);//echo "<pre>"; print_r($overViewRows);die;
        $count = count($overViewRows);
        if ($count > 0) {
            $this->view->conditionEO = true;
            $this->view->condition = true;
        }

//        $this->view->overviewRows = $overViewRows;
/////// Employee Overview Section End  ///////////////////////////////////////////////////////////////////////////////
        //***************** Employee Attendance Section ******************************* Needs improvment - take alot of time

        $employeeAttendanceModel = new Application_Model_Leave();
        $acl2Plugin = new Application_Controller_Plugin_Acl2();
        $empList = $acl2Plugin->getEmployeeList();
        if ($empList != '') {
            $dependantCondition = "(e.current_supervisor_id= '" . $eId . "' or e.current_teamlead_id = '" . $eId . "')";
        } else {
            $dependantCondition = "1";
        }

        $employeeAttendanceSelect = $employeeAttendanceModel->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => 'attendance'), array('a.time_in as time_in', 'a.time_out as time_out','a.break_in as break_in','a.break_out as break_out', 'a.reason_id as a.reason_id', 'a.hrs as hrs_spent','a.break_time as break_time'))
                ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                ->where($dependantCondition . " and DATE(a.time_in) = subdate(date(now()), 1) and e.current_job_status NOT IN ('Resigned','Terminated')")
                ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id = lr.id', array('lr.reason_name as lr.reason_name', 'lr.id as lr.id'))
                ->group("e.id")
                ->limit(15)
                ->order("e.name ASC");
        //  echo $employeeAttendanceSelect; die;
        $employeeAttendanceSelect_not_informed = $employeeAttendanceModel->select()
                ->setIntegrityCheck(false)
                ->from(array('e' => 'employee'), array('e.name as e.name', 'e.number as e.number', 'e.current_job_status as e.current_job_status'))
                ->where($dependantCondition . " and e.current_job_status NOT IN ('Resigned','Terminated')")
                ->where("e.id NOT IN (SELECT employee_id FROM attendance WHERE time_in = subdate(current_date, 1))")
                ->group("e.id")
                ->limit(15);
        $employeeAttendanceSelect_leave = $employeeAttendanceModel->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => 'attendance'), array('TIME(a.time_in) as time_in', 'TIME(a.time_out) as time_out', 'a.time_in','a.break_time'))
                ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                ->where($dependantCondition . " and a.time_in = subdate(current_date, 1) and a.reason_id  != '0' and e.current_job_status NOT IN ('Resigned','Terminated')")
                ->group("e.id")
                ->limit(15)
                ->order("a.time_in DESC");
        $this->view->EA_user = 'Supervisor';
        //echo "salman";
//die($employeeAttendanceSelect);
        if ($access_list->checkPermission('dashboard', 'EmployeeAttendance')) {
            $employeeAttendanceRows = $employeeAttendanceModel->fetchAll($employeeAttendanceSelect);
            $count = count($employeeAttendanceRows); 
            $this->view->showEmployeeAttendance = true;
            if ($count > 0) {
                //$employeeAttendanceRows_leave = $employeeAttendanceModel->fetchAll($employeeAttendanceSelect_leave);
                //$employeeAttendanceRows_not_informed = $employeeAttendanceModel->fetchAll($employeeAttendanceSelect_not_informed);
                $this->view->conditionEA = true;

                //$this->view->showEmployeeAttendance = true;
                $this->view->employeeAttendanceRows = $employeeAttendanceRows;
                @$this->view->employeeAttendanceSelect_leave = $employeeAttendanceRows_leave;
                @$this->view->employeeAttendanceSelect_not_informed = $employeeAttendanceRows_not_informed;

                //*********** For Graph Data************
                $totalPresent = 0;
                foreach ($employeeAttendanceRows as $val) {
                    $totalPresent = $totalPresent + 1;
                }
                $this->view->totalPresent = $totalPresent;
                $totalNotmarked = 0;
                foreach ($employeeAttendanceRows_not_informed as $val) {
                    $totalNotmarked = $totalNotmarked + 1;
                }
                $this->view->totalNotmarked = $totalNotmarked;
                $totalLeave = 0;
                foreach ($employeeAttendanceRows_leave as $val) {
                    $totalLeave = $totalLeave + 1;
                }
                $this->view->totalLeave = $totalLeave;
            }
        }
        //***************** Employe Attendence End ********************************
///// Your Attendance  Section Start : to be displayed for all Roles ///////////////////////////////////////////////

        $userAttendanceModel = new Application_Model_Leave();
        $userAttendanceModelSelect = $userAttendanceModel->select()
                        ->setIntegrityCheck(false)
                        ->from(array('a' => 'attendance'), array('a.time_in', 'a.time_out', 'a.hrs','a.office_hrs','a.break_time','a.reason_id','a.remarks',
                                                                    'a.break_in','a.break_out'))  //made chnges by shahzeb a.office_hrs
                        ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.number as e.number'))
                        ->joinLeft(array('lvr' => 'leave_reason'), 'a.reason_id = lvr.id', array('lvr.reason_name as reason_name'))
                        ->where("e.id = '" . $eId . "'")
                        ->order("a.time_in desc")->limit(10);
       //echo $userAttendanceModelSelect;die();
        $alluserAttendanceRows = $userAttendanceModel->fetchAll($userAttendanceModelSelect);

        $userAttendanceRows = array();
        foreach($alluserAttendanceRows as $r){
            $userAttendanceRows[] = $r;
                       
        }
       
        
        $count = count($userAttendanceRows);
        if ($count > 0)
            $this->view->conditionYA = true;

//// Your Attendance Section Ends
///// Employee Leave  Section Start : to be displayed for all Roles ///////////////////////////////////////////////////

        $leaveModel = new Application_Model_Leave();

        if ($user_type == 'superadmin') {
//            $leaveSelect = $leaveModel->select()
//                    ->setIntegrityCheck(false)
//                    ->from(array('a' => 'attendance'), array('a.date'))
//                    ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
//                    ->joinLeft(array('r' => 'report_to'), 'e.id = r.employee_id', array('r.employee_id as r.employee_id'))
//                    ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
//                    ->where("r.recent_record= '1' and MONTH(a.date) = MONTH ( NOW() ) and a.reason_id != '0'")
//                    ->group("a.employee_id")
//                    ->order("a.time_in DESC")
//                    ->limit(15);

            $leaveSelect = $leaveModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'leave'), array('l.id as leave_id','l.token_attachment as token_attachment','l.from_date as l.from_date', 'l.to_date as l.to_date','l.status as status', 'l.employee_id as l.employee_id'))
                    ->joinLeft(array('e' => 'employee'), 'l.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    ->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where("(MONTH(l.from_date) = MONTH ( NOW() ) OR MONTH(l.to_date) = MONTH ( NOW() )) and l.reason_id != '0' and e.current_job_status not in ('Resigned','Terminated')")
                    //->where("(MONTH(l.from_date) = MONTH ( NOW() ) OR MONTH(l.to_date) = MONTH ( NOW() )) and l.reason_id != '0' ")
                    ->group("l.employee_id");
//                    ->limit(15);
        } else {
//            $leaveSelect = $leaveModel->select()
//                    ->setIntegrityCheck(false)
//                    ->from(array('a' => 'attendance'), array('a.date'))
//                    ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
//                    ->joinLeft(array('r' => 'report_to'), 'e.id = r.employee_id', array('r.employee_id as r.employee_id'))
//                    ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
//                    ->where("r.supervisor_id= '" . $eId . "' and r.recent_record= '1' and MONTH(a.date) = MONTH ( NOW() ) and a.reason_id != '0'")
//                    ->group("a.employee_id")
//                    ->order("a.time_in DESC")
//                    ->limit(15);
            $currentYear = date("Y");
            $leaveSelect = $leaveModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'leave'), array('l.id as leave_id','l.token_attachment as token_attachment','l.from_date as l.from_date', 'l.to_date as l.to_date', 'l.duration as l.duration', 'l.leave_type as leave_type','l.status as status', 'l.employee_id as l.employee_id'))
                    ->joinLeft(array('e' => 'employee'), 'l.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    ->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where("(e.current_supervisor_id= '" . $eId . "' or e.current_teamlead_id= '" . $eId . "'  ) and l.status='Approved' and EXTRACT(YEAR FROM l.from_date)='".$currentYear."' and (MONTH(l.from_date) = MONTH ( NOW() ) OR MONTH(l.to_date) = MONTH ( NOW() )) and l.reason_id != '0'  ")//
                    //->where("(e.current_supervisor_id= '" . $eId . "' or e.current_teamlead_id= '" . $eId . "'  ) and l.status='Approved' and EXTRACT(YEAR FROM l.from_date)='".$currentYear."' and (MONTH(l.from_date) = MONTH ( NOW() ) OR MONTH(l.to_date) = MONTH ( NOW() )) and l.reason_id != '0' ")//
                    ->group("l.employee_id");
//                    ->limit(15);
        }
        //echo $leaveSelect; dingo
        $leaveRows = $leaveModel->fetchAll($leaveSelect)->toArray();
        $leaveRows = $this->getTokensForLeave($leaveRows);
        $count = count($leaveRows);
        if ($count > 0)
            $this->view->conditionEL = true;
        
        
//        $this->view->leaveRows = $leaveRows;
//// Employee Leave Section Ends

        
        /*  Employee Leave Planner start*/
        
       // $leaveModel = new Application_Model_Leave();
        if($access_list->checkPermission('leave', 'finalApprovalLeave')){
                $query = '  l.status = "Approved by DM" ';
            }else{
                $query = ' (e.current_supervisor_id= "' . $eId . '" or e.current_teamlead_id= "' . $eId . '"  ) and l.status = "Pending"';
            }
        if ($user_type == 'superadmin') {
//            $leaveSelect = $leaveModel->select()
//                    ->setIntegrityCheck(false)
//                    ->from(array('a' => 'attendance'), array('a.date'))
//                    ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
//                    ->joinLeft(array('r' => 'report_to'), 'e.id = r.employee_id', array('r.employee_id as r.employee_id'))
//                    ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
//                    ->where("r.recent_record= '1' and MONTH(a.date) = MONTH ( NOW() ) and a.reason_id != '0'")
//                    ->group("a.employee_id")
//                    ->order("a.time_in DESC")
//                    ->limit(15);

            $leaveplannerSelect = $leaveModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'leave'), array('l.id as leave_id','l.token_attachment as token_attachment','l.from_date as l.from_date', 'l.to_date as l.to_date','l.status as status', 'l.employee_id as l.employee_id'))
                    ->joinLeft(array('e' => 'employee'), 'l.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    ->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where("(MONTH(l.from_date) = MONTH ( NOW() ) OR MONTH(l.to_date) = MONTH ( NOW() )) and l.reason_id != '0' ")
                    ->group("l.employee_id");
//                    ->limit(15);
        } else {
//            $leaveSelect = $leaveModel->select()
//                    ->setIntegrityCheck(false)
//                    ->from(array('a' => 'attendance'), array('a.date'))
//                    ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
//                    ->joinLeft(array('r' => 'report_to'), 'e.id = r.employee_id', array('r.employee_id as r.employee_id'))
//                    ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
//                    ->where("r.supervisor_id= '" . $eId . "' and r.recent_record= '1' and MONTH(a.date) = MONTH ( NOW() ) and a.reason_id != '0'")
//                    ->group("a.employee_id")
//                    ->order("a.time_in DESC")
//                    ->limit(15);

            $leaveplannerSelect = $leaveModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'leave'), array('l.id as leave_id','l.token_attachment as token_attachment','l.from_date as l.from_date', 'l.to_date as l.to_date', 'l.duration as l.duration', 'l.leave_type as leave_type','l.status as status', 'l.employee_id as l.employee_id'))
                    ->joinLeft(array('e' => 'employee'), 'l.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    ->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where(" $query   and l.reason_id != '0' ");//and (MONTH(l.from_date) = MONTH ( NOW() ) OR MONTH(l.to_date) = MONTH ( NOW() )) and l.status <> 'Approved'
                    //->group("l.employee_id")
//                    ->limit(15);
            
            // Leaves Pending by DM
            $leaveplannerPendingSelect = $leaveModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'leave'), array('l.id as leave_id','l.token_attachment as token_attachment','l.from_date as l.from_date', 'l.to_date as l.to_date', 'l.duration as l.duration', 'l.leave_type as leave_type','l.status as status', 'l.employee_id as l.employee_id'))
                    ->joinLeft(array('e' => 'employee'), 'l.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    ->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where(" l.status = 'Pending' and l.approved_by_dm IS NULL and l.reason_id != '0' and e.current_job_status not in ('Resigned','Terminated')") //and (MONTH(l.from_date) = MONTH ( NOW() ) OR MONTH(l.to_date) = MONTH ( NOW() )) and l.status <> 'Approved'
                    ->order('l.id desc');
                    //->group("l.employee_id")
//                    ->limit(15);
            
        }
        //echo $leaveSelect; die;1212
        $leaveplannerPendingRows = $leaveModel->fetchAll($leaveplannerPendingSelect)->toArray();//echo '<pre>';var_dump($leaveplannerRows);die;
        $leaveplannerPendingRows = $this->getTokensForLeave($leaveplannerPendingRows);
//        echo "<pre>";
//        print_r($leaveplannerPendingRows);exit;
        
        
        
        $leaveplannerRows = $leaveModel->fetchAll($leaveplannerSelect)->toArray();//echo '<pre>';var_dump($leaveplannerRows);die;
        $leaveplannerRows = $this->getTokensForLeave($leaveplannerRows);
//        echo "<pre>";
//        print_r($leaveplannerPendingRows);exit;
        $count = count($leaveplannerRows);
        if ($count > 0)
            $this->view->conditionELP = true;

        
        
        
        /* EMployee LEave planner End */
        
        
//
//
///// Your Leave Section Start : to be displayed for all Roles ///////////////////////////////////////////////////

        $leaveModel = new Application_Model_Leave();


        $empLeaveSelect = $leaveModel->select()
                ->setIntegrityCheck(false)
                ->from(array('l' => 'leave'), array('l.id as leave_id','l.token_attachment as token_attachment','l.from_date as l.from_date', 'l.to_date as l.to_date', 'l.status as status', 'l.leave_type as leave_type', 'l.duration as duration'))
                ->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                ->where("l.reason_id != '0' and l.employee_id= '" . $eId . "' and l.status != 'Rejected' and YEAR(l.from_date) = YEAR(CURDATE())") // and l.status='Approved'
                ->order("l.from_date DESC")
                ->limit(11);
        
        $empleaveRows = $leaveModel->fetchAll($empLeaveSelect);
        $count = count($empleaveRows);
        if ($count > 0)
            $this->view->conditionYL = true;
        
        
         $empLeaveSelectplanners = $leaveModel->select()
                ->setIntegrityCheck(false)
                ->from(array('l' => 'leave'), array('l.id as leave_id','l.token_attachment as token_attachment','l.from_date as l.from_date', 'l.to_date as l.to_date', 'l.status as status', 'l.leave_type as leave_type', 'l.duration as duration' ))
                ->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                ->where("l.reason_id != '0' and l.employee_id= '" . $eId . "' and l.status != 'Rejected'  and YEAR(l.from_date) = YEAR(CURDATE())") //and l.status != 'Approved'
                ->order("l.from_date DESC")
                ->limit(11);
        
        $empleaveRowsPlanner = $leaveModel->fetchAll($empLeaveSelectplanners);
        $empleaveRowsPlanner = $this->getTokensForLeave($empleaveRowsPlanner);
        $counts = count($empleaveRowsPlanner);
        if ($counts > 0)
            $this->view->conditionYLP = true;
        

//        $this->view->empLeaveRows = $empleaveRows;
/////// Your Leave Section End  //////////////////////////////////////////////////////////////////////////////////
        
/*  Employee Leave Token Planner start*/
        
        $tokenModel = new Application_Model_Token();
        
        //EMPLOYEE LEAVE TOKEN
        if ($user_type == 'superadmin') {
//            $leaveSelect = $leaveModel->select()
//                    ->setIntegrityCheck(false)
//                    ->from(array('a' => 'attendance'), array('a.date'))
//                    ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
//                    ->joinLeft(array('r' => 'report_to'), 'e.id = r.employee_id', array('r.employee_id as r.employee_id'))
//                    ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
//                    ->where("r.recent_record= '1' and MONTH(a.date) = MONTH ( NOW() ) and a.reason_id != '0'")
//                    ->group("a.employee_id")
//                    ->order("a.time_in DESC")
//                    ->limit(15);

            $mleavetokenSelect = $tokenModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('t' => 'tokens'), array('t.actual_hours_in_token as t.actual_hours_in_token','t.employee_id as t.employee_id', 't.hours_in_token as t.hours_in_token', 't.token_date as t.token_date','t.status as t.status'))
                    ->joinLeft(array('e' => 'employee'), 't.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    //->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where("(MONTH(t.token_date) = MONTH ( NOW() ) ) ")
                    ->group("t.employee_id");
//                    ->limit(15);
        } else {
//            $leaveSelect = $leaveModel->select()
//                    ->setIntegrityCheck(false)
//                    ->from(array('a' => 'attendance'), array('a.date'))
//                    ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
//                    ->joinLeft(array('r' => 'report_to'), 'e.id = r.employee_id', array('r.employee_id as r.employee_id'))
//                    ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
//                    ->where("r.supervisor_id= '" . $eId . "' and r.recent_record= '1' and MONTH(a.date) = MONTH ( NOW() ) and a.reason_id != '0'")
//                    ->group("a.employee_id")
//                    ->order("a.time_in DESC")
//                    ->limit(15);

            $mleavetokenSelect = $tokenModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('t' => 'tokens'), array('t.actual_hours_in_token as t.actual_hours_in_token','t.employee_id as t.employee_id', 't.hours_in_token as t.hours_in_token', 't.token_date as t.token_date','t.status as t.status'))
                    ->joinLeft(array('e' => 'employee'), 't.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    //->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where(" (e.current_supervisor_id= '" . $eId . "' or e.current_teamlead_id= '" . $eId . "'  ) and t.status='Approved'  and (MONTH(t.token_date) = MONTH ( NOW() ) )")
                    ->group("t.employee_id");
//                    ->limit(15);
        }
        //echo $leaveSelect; die;
        $mleavetokenRows = $tokenModel->fetchAll($mleavetokenSelect);//echo '<pre>';var_dump($mleavetokenRows);die;
        $count = count($mleavetokenRows);
        if ($count > 0)
            $this->view->conditionELT = true;
        //END EMPLOYEE LEAVE TOKEN

        if ($user_type == 'superadmin') {
//            $leaveSelect = $leaveModel->select()
//                    ->setIntegrityCheck(false)
//                    ->from(array('a' => 'attendance'), array('a.date'))
//                    ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
//                    ->joinLeft(array('r' => 'report_to'), 'e.id = r.employee_id', array('r.employee_id as r.employee_id'))
//                    ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
//                    ->where("r.recent_record= '1' and MONTH(a.date) = MONTH ( NOW() ) and a.reason_id != '0'")
//                    ->group("a.employee_id")
//                    ->order("a.time_in DESC")
//                    ->limit(15);

            $leaveTokenSelect = $tokenModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('t' => 'tokens'), array('t.actual_hours_in_token as t.actual_hours_in_token','t.employee_id as t.employee_id', 't.hours_in_token as t.hours_in_token', 't.token_date as t.token_date','t.status as t.status', 't.availability_status as t.availability_status'))
                    ->joinLeft(array('e' => 'employee'), 't.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    //->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where("(MONTH(t.token_date) = MONTH ( NOW() ))")
                    ->group("t.employee_id");
//                    ->limit(15);
        } else {
//            $leaveSelect = $leaveModel->select()
//                    ->setIntegrityCheck(false)
//                    ->from(array('a' => 'attendance'), array('a.date'))
//                    ->joinLeft(array('e' => 'employee'), 'a.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
//                    ->joinLeft(array('r' => 'report_to'), 'e.id = r.employee_id', array('r.employee_id as r.employee_id'))
//                    ->joinLeft(array('lr' => 'leave_reason'), 'a.reason_id  = lr.id', array('lr.reason_name as reason_name'))
//                    ->where("r.supervisor_id= '" . $eId . "' and r.recent_record= '1' and MONTH(a.date) = MONTH ( NOW() ) and a.reason_id != '0'")
//                    ->group("a.employee_id")
//                    ->order("a.time_in DESC")
//                    ->limit(15);
            if($access_list->checkPermission('token', 'final-approval-token')){
                $query = '  t.status = "Approved by DM" ';
            }else{
                $query = ' (e.current_supervisor_id= "' . $eId . '" or e.current_teamlead_id= "' . $eId . '"  ) and t.status = "Pending"';
            }
            $leaveTokenSelect = $tokenModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('t' => 'tokens'), array('t.actual_hours_in_token as t.actual_hours_in_token', 't.employee_id as t.employee_id', 't.hours_in_token as t.hours_in_token', 't.token_date as t.token_date','t.status as t.status', 't.availability_status as t.availability_status'))
                    ->joinLeft(array('e' => 'employee'), 't.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    //->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                    ->where("  $query "); //(MONTH(t.token_date) = MONTH( NOW() ) )
                    //->group("t.employee_id")
//                    ->limit(15);
        }
        
        $leaveTokenRows = $tokenModel->fetchAll($leaveTokenSelect);
        $count = count($leaveTokenRows); //echo '<pre> count = '.$count; print_r($leaveTokenRows);die;
        if ($count > 0)
            $this->view->conditionELTP = true;
//$this->view->conditionELP = true;

        
        
        
        /* EMployee LEave Token planner End */
        ///// Your Leave Token Section Start : to be displayed for all Roles ///////////////////////////////////////////////////

        


        $empTokenSelect = $tokenModel->select()
                ->setIntegrityCheck(false)
                ->from(array('t' => 'tokens'), array('t.employee_id as t.employee_id', 't.token_date as t.token_date', 't.status as t.status', 't.hours_in_token as t.hours_in_token'))
                //->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                ->where("t.employee_id= '" . $eId . "' and t.status <> 'Rejected' and YEAR(t.token_date) = YEAR(CURDATE())") // and t.status='Approved'
                ->where("t.token_date between (CURRENT_DATE() - INTERVAL 6 MONTH) AND CURRENT_DATE()")                 
                ->order("t.token_date DESC")
                ->limit(11);
        
        $empTokenRows = $tokenModel->fetchAll($empTokenSelect);
        $count = count($empTokenRows);
        if ($count > 0)
            $this->view->conditionYLT = true;
            //$this->view->conditionYL = true;
        
        
         $empLeaveTokenSelectPlanners = $tokenModel->select()
                ->setIntegrityCheck(false)
                ->from(array('t' => 'tokens'), array('t.employee_id as t.employee_id', 't.token_date as t.token_date', 't.status as t.status', 't.hours_in_token as t.hours_in_token', 't.actual_hours_in_token as t.actual_hours_in_token', 't.availability_status as t.availability_status','DATE_ADD(t.token_date, INTERVAL 6 MONTH) as t.expiry_date', 'datediff(DATE_ADD(t.token_date, INTERVAL 6 MONTH),now()) as t.days_remaining'))
                //->joinLeft(array('lr' => 'leave_reason'), 'l.reason_id  = lr.id', array('lr.reason_name as reason_name'))
                ->where("t.employee_id= '" . $eId . "'") //and t.status != 'Approved'
                ->where("t.token_date between (CURRENT_DATE() - INTERVAL 6 MONTH) AND CURRENT_DATE()")                 
                ->where("t.availability_status!='Utilized'")                 
                ->order("t.token_date DESC")
                ->limit(20);
        
        $empleaveTokenRowsPlanner = $tokenModel->fetchAll($empLeaveTokenSelectPlanners);
        $counts = count($empleaveTokenRowsPlanner);
        if ($counts > 0)
            $this->view->conditionYLTP = true;
//$this->view->conditionYLP = true;
        

//        $this->view->empLeaveRows = $empleaveRows;
///////  Token Section End  //////////////////////////////////////////////////////////////////////////////////
///// Employee Leave Quota Section Start : to be displayed for all Roles /////////////////////////////////////////////

        $qModel = new Application_Model_LeaveQuota();
        $employeeAttendanceModel = new Application_Model_Leave();
        $acl2Plugin = new Application_Controller_Plugin_Acl2();
        $empList = $acl2Plugin->getEmployeeList();
        if ($empList != '') {
            $dependantCondition = "((r.supervisor_id= '" . $eId . "' or r.subordinate_id = '" . $eId . "') and r.recent_record= '1')";
        } else {
            $dependantCondition = "1";
        }

        if ($user_type == 'superadmin') {

            $qSelect = $qModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('q' => 'leave_quota'), array('q.*'))
                    ->joinLeft(array('e' => 'employee'), 'q.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    ->group('e.id')
                    ->limit(5);
        } else {
            $qSelect = $qModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('q' => 'leave_quota'), array('q.*'))
                    ->joinLeft(array('r' => 'report_to'), 'r.employee_id = q.employee_id', array('r.supervisor_id as r.supervisor_id', 'r.subordinate_id as r.subordinate_id'))
                    ->joinLeft(array('e' => 'employee'), 'q.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    ->where($dependantCondition . " and e.current_job_status NOT IN ('Resigned','Terminated') and q.recent_record='1'")
                    ->order('e.name ASC')
                    ->group('e.id')
                    ->limit(5);
        }
        
        $qrows = $qModel->fetchAll($qSelect);
        $count = count($qrows);
        if (count($qrows) > 0) {
            $this->view->conditionELQ = true;
            $conditionELQ = true;
        }
///// Public Hoildays Section Start : to be displayed for all Roles ///////////////////////////////////////////////
        $holidayModel = new Application_Model_Holidays();
        $hRows = $holidayModel->fetchAll("month(date)='" . (date('m')) . "' and year(date)='" . (date('Y')) . "' and day(date) >='" . (date('d')) . "'");
        // $hRows = $holidayModel->fetchAll();
        $count = count($hRows);
        if ($count > 0) {
            $this->view->conditionPH = true;
            $this->view->conditionf = true;
        }


//        $this->view->hRows = $hRows;
///// Public Hoildays Section End  ////////////////////////////////////////////////////////////////////////////////
///// Recent Employees Section Start : to be displayed for Admin Only ///////////////////////////////////////////////

        $employeeModel = new Application_Model_Employees();

        $employeeAttendanceModel = new Application_Model_Leave();
        $acl2Plugin = new Application_Controller_Plugin_Acl2();
        $empList = $acl2Plugin->getEmployeeList();
        if ($empList != '') {
            $dependantCondition = "((f.supervisor_id= '" . $eId . "' or f.subordinate_id = '" . $eId . "') and f.recent_record= '1')";
        } else {
            $dependantCondition = "1";
        }

        if ($user_type == 'superadmin') {

            $select = $employeeModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('e' => 'employee'), array('e.name as e.name', 'e.*'))
                    ->joinLeft(array('d' => 'designation'), 'e.designation_id = d.id', array('d.name as d.name'))
                    ->order(array('e.id DESC', 'e.joining_date ASC'))
                    ->distinct(true)
                    ->limit(6, 0);
        } else {
            $select = $employeeModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('e' => 'employee'), array('e.name as e.name', 'e.*'))
                    ->joinLeft(array('f' => 'report_to'), 'f.employee_id = e.id', array('f.employee_id as f.employee_id'))
                    ->joinLeft(array('d' => 'designation'), 'e.designation_id = d.id', array('d.name as d.name'))
                    ->where($dependantCondition . " and e.joining_date is not null")
                    ->order(array('e.id DESC', 'e.joining_date ASC'))
                    ->distinct(true)
                    ->limit(6, 0);
        }

        $employeeRows = $employeeModel->fetchAll($select);
        $count = count($employeeRows);
        if (count($employeeRows) > 0) {
            $nationalityModel = new Application_Model_Country();
            $nationalityList = $nationalityModel->allCountry('nationality');
            foreach ($employeeRows as $eRow) {
                if ($eRow['nationality_id'] != '')
                    $eRow['nationality_id'] = $nationalityList[$eRow->nationality_id];
                else
                    $eRow['nationality_id'] = 'No Nationality added';
            }
            $conditionSRE = true;
            $this->view->conditionSRE = true;
        }

//        $this->view->employeeRows = $employeeRows;
///// Recent Employees Section End  ////////////////////////////////////////////////////////////////////////////////
///// Employee Review : to be displayed for all Roles ///////////////////////////////////////////////////
        $acl2Plugin = new Application_Controller_Plugin_Acl2();
        $empList = $acl2Plugin->getEmployeeList();

        if ($empList != '') {
            $employeeList = 'and rad.employee_id IN (' . $empList . ') and rad.employee_id != ' . $eId;
        } else {
            $employeeList = '';
        }

        $employeesReviewModel = new Application_Model_ReviewDataForm();
        $empReview = $employeesReviewModel->select()
                ->setIntegrityCheck(false)
                ->from(array('rad' => 'review_data_form'), array('rad.id as rad.id', 'rad.form_id as rad.form_id', 'rad.employee_id as rad.employee_id', 'rad.reviewer_sign_date as rad.reviewer_sign_date'))
                ->joinLeft(array('e' => 'employee'), 'rad.employee_id = e.id', array('e.name as e.name', 'e.number as e.number', 'e.id as e.id'))
                ->joinLeft(array('dpt' => 'department'), 'dpt.id = e.current_department_id', array('dpt.name as dpt.name'))
                ->joinLeft(array('dsig' => 'designation'), 'dsig.id = e.current_designation_id', array('dsig.name as dsig.name'))
                ->joinLeft(array('ro' => 'review_data_overall'), 'ro.answer_details_id = rad.id', array('SUM(ro.answer) as ro.answer', 'COUNT(ro.answer) as ro.count'))
                ->joinLeft(array('rf' => 'review_template_form'), 'rf.id = rad.form_id', array('rf.name as rf.name'))
                ->where('1 ' . $employeeList . ' and rad.reviewer_sign_date IS NOT NULL')
                ->having('COUNT(ro.answer) > 0')
                ->order('rad.modified_date DESC')
                ->group('rad.employee_id')
                ->limit(5);
        $empReview = $employeesReviewModel->fetchAll($empReview);
        $count = count($empReview);
        if (count($empReview) > 0) {
            $this->view->conditionERR = true;
            $conditionERR = true;
        }


        $mdlEvents = new Application_Model_Events();
        $eventRow = $mdlEvents->fetchAll("(event_type='Dashboard' or event_type='Both') and status='Active'", "id DESC");
        $eventRowCount = count($eventRow);
        if ($eventRowCount > 0)
            $this->view->globalEventCount = true;
        if ($user_type == 'superadmin') {

            $this->view->globalEvent = true;
            $this->view->globalEventRows = $eventRow;
        } elseif ($access_list->checkPermission('dashboard', 'GlobalEvents')) {

            $this->view->globalEvent = true;
            $this->view->globalEventRows = $eventRow;
        }


//        $this->view->empLeaveRows = $empleaveRows;
/////// Employee Review Section End  //////////////////////////////////////////////////////////////////////////////////
//// CHECKING PERMISSIONS AND PUSHING BLOCKS ON DASHBOARD ACCORDING TO ROLE PERMISSIONS//////////////////////////
// Bulletins/News : Permissions /////////////////////////////////////////////////////////////////////////////////

        if ($user_type == 'superadmin') {

            $this->view->showBullitenNews = true;
            $this->view->newsRows = $nRows;
        } elseif ($access_list->checkPermission('dashboard', 'BulletinNews')) {

            $this->view->showBullitenNews = true;
            $this->view->newsRows = $nRows;
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Employee Overview : Permissions //////////////////////////////////////////////////////////////////////////////

        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'EmployeeOverview') )) {
            $this->view->showEmployeeOverview = true;
            $this->view->overviewRows = $overViewRows;
        }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Your Attendance : Permissions ////////////////////////////////////////////////////////////////////////////////

        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'YourAttendance') )) {
            $this->view->showYourAttendance = true;
            $this->view->userAttendanceRows = $userAttendanceRows;
            $this->view->conditionb = true;
        }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Employee Stats Graph : Permissions ////
        $empStatusDpt = new Application_Model_EmploymentStatusDepartment();
         $empStatSelect = $empStatusDpt->select()
          ->setIntegrityCheck(false)
          ->from(array('e' => 'employee'), array('count(*) as e.count'))
          ->joinLeft(array('d' => 'division'), 'e.current_division_id = d.id', array('d.name as d.name'))
          ->group(array("e.current_division_id"))
          ->where("e.status = 'Active' 
                    and e.current_job_status NOT IN (
                                            'Resigned', 'Terminated'
                                           )")
          ->order(array("e.current_division_id")); 
        $db = Zend_Db_Table::getDefaultAdapter();
        $EmpDivisionStat = $db->fetchAll($empStatSelect);
        //$EmpDivisionStat = $empStatusDpt->fetchAll($empStatSelect);
        $EmpDivisionStatArray = array();
        foreach ($EmpDivisionStat as $empDivArr) {
            if($empDivArr['d.name']=="")
                    $empDivArr['d.name'] = "Information Not Updated";
            $EmpDivisionStatArray[$empDivArr['d.name']] = $empDivArr['e.count'];
        }

        if ($user_type == 'superadmin') {

            $this->view->showEmployeeStats = true;
            $this->view->employeeStatsRows = $EmpDivisionStatArray; //array("CORPORATE SERVICES"=>65,"NXB ROBOTICS"=>50,"PUBLISHING"=>45,"SOFTWARE DEVELOPMENT"=>115,"VERTICALS"=>115,"VTEAMS"=>190);
        } elseif (($access_list->checkPermission('dashboard', 'EmployeeStatsGraph'))) {

            $this->view->showEmployeeStats = true;
            $this->view->employeeStatsRows = $EmpDivisionStatArray; //array("CORPORATE SERVICES"=>65,"NXB ROBOTICS"=>50,"PUBLISHING"=>45,"SOFTWARE DEVELOPMENT"=>115,"VERTICALS"=>115,"VTEAMS"=>190);
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Your Last Review : Permissions ///////////////////////////////////////////////////////////////////////////////

        if (($user_type != 'superadmin') && $access_list->checkPermission('dashboard', 'YourLastReview')) {
            $detailsModel = new Application_Model_ReviewDataForm();
            $formModel = new Application_Model_ReviewTemplateForm();
            $sectionModel = new Application_Model_ReviewTemplateSection();
            $overallModel = new Application_Model_ReviewDataOverall();
            // Getting the Latest reviewed form
            $detailsRow = $detailsModel->fetchRow('employee_id = "' . $eId . '"', 'modified_date DESC');
            @$detailsId = $detailsRow->id;
            @$formId = $detailsRow->form_id;
            //@$this->view->formDuration = $detailsRow->form_duration
            //@$this->view->formDuration = $detailsRow->form_duration_from." To ".$detailsRow->form_duration_to." - ".$detailsRow->form_duration_year;
            
            $months2['1']='January';
                $months2['2']='Feburary';
                $months2['3']='March';
                $months2['4']='April';
                $months2['5']='May';
                $months2['6']='June';
                $months2['7']='July';
                $months2['8']='August';
                $months2['9']='September';
                $months2['10']='October';
                $months2['11']='November';
                $months2['12']='December';

                //@$this->view->formDuration = $months2[$detailsRow->form_duration_from]." To ".$detailsRow->$months2[form_duration_to]." - ".$detailsRow->form_duration_year;
                @$this->view->formDuration = $months2[$detailsRow->form_duration_from]." To ".$months2[$detailsRow->form_duration_to]." - ".$detailsRow->form_duration_year;
                
            if (isset($formId) && ($formId != '') && ($detailsRow->informemployee=='yes')) {
                $formRow = $formModel->fetchRow('id = ' . $formId . ' and status = "Active"');
                if ((isset($formRow)) && ($formRow != '')) {
                    $formName = $formRow->name;
                    $this->view->formName = $formName;
                    $sectionRow = $sectionModel->fetchAll('form_id = ' . $formId);
                    $this->view->sectionName = $sectionRow;
                    $overallRow = $overallModel->getOverallSections(' and roa.answer_details_id = ' . $detailsId . ' and rs.form_id = ' . $formId);
                    $this->view->overall = $overallRow;
                    $this->view->countOverall = count($overallRow);
                    $this->view->conditionYLRS = true;
                }
            }
            $this->view->showYourLastReview = true;
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Employees Reviews : Permissions //////////////////////////////////////////////////////////////////////////////

        if ($user_type == 'superadmin') {
            $this->view->showEmployeesReview = true;
            $this->view->employeesReviewRows = $empReview;
        } elseif ($access_list->checkPermission('dashboard', 'EmployeeReviews')) {

            $this->view->showEmployeesReview = true;
            $this->view->employeesReviewRows = $empReview;
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Employee Leave : Permissions /.//////////////////////////////////////////////////////////////////////////////


        if ($user_type == 'superadmin') {
            $this->view->showEmployeeLeave = true;
            //$this->view->showEmployeeLeaveToken = true;
            $this->view->showEmployeeLeavePlanner = true;
            $this->view->showEmployeeLeaveTokenPlanner = true;
            $this->view->leaveRows = $leaveRows;
            //$this->view->mleaveTokenRows = $mleavetokenRows;
            $this->view->leaveplannerRows = $leaveplannerRows;
            $this->view->leavetokenplannerRows = $leaveTokenRows;
            
        } elseif ($access_list->checkPermission('dashboard', 'EmployeeLeave')) {
            $this->view->showEmployeeLeave = true;
            $this->view->leaveRows = $leaveRows;
            
            
        }
        
        if ($user_type == 'superadmin') {
            
            //$this->view->showEmployeeLeaveToken = true;
            $this->view->showEmployeeLeaveTokenPlanner = true;
            //$this->view->mleaveTokenRows = $mleavetokenRows;
            $this->view->leavetokenplannerRows = $leaveTokenRows;
            
        } elseif ($access_list->checkPermission('dashboard', 'EmployeeLeaveToken')) {
            //for employee token
            //$this->view->showEmployeeLeaveToken = true;
            //$this->view->mleaveTokenRows = $mleavetokenRows;
            
            $this->view->showEmployeeLeaveTokenPlanner = true;
            $this->view->leavetokenplannerRows = $leaveTokenRows;
            
        }
        if($access_list->checkPermission('dashboard', 'EmployeeLeavePlanner')){
            $this->view->showEmployeeLeavePlanner = true;
            
            $this->view->leaveplannerRows = $leaveplannerRows;
            $this->view->leaveRows = $leaveRows;
          
        }
        
        if($access_list->checkPermission('dashboard', 'EmployeePlannedLeave')){
            $this->view->showEmployeePlannedLeavesPending = true;
            $this->view->leaveplannePendingrRows = $leaveplannerPendingRows;
            
        }


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Your Leave : Permissions /.//////////////////////////////////////////////////////////////....////////////////
/*
        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'YourLeave') )) {
            $this->view->showYourLeave = true;
            
            $this->view->empLeaveRows = $empleaveRows;
            
        }
        
        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'YourLeavePlanner') )) {
            $this->view->showYourLeavePlanner = true;
            
            $this->view->empLeaveRowsPlanner = $empleaveRowsPlanner;
            
            //print_r($empleaveRowsPlanner);die;
        }
*/
        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'YourLeave') )) {
            $this->view->showYourLeavePlanner = true;
            
            $this->view->empLeaveRowsPlanner = $empleaveRowsPlanner;
            
        }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Your Leave Token : Permissions hawk /.//////////////////////////////////////////////////////////////....////////////////
/*
        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'YourLeaveToken') )) {
            
            $this->view->showYourLeaveToken = true;
            
            $this->view->empLeaveTokenRows = $empTokenRows;
        }
        
        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'YourLeaveTokenPlanner') )) {
            
            $this->view->showYourLeaveTokenPlanner = true;
            
            $this->view->empLeaveTokenRowsPlanner = $empleaveTokenRowsPlanner;
            //print_r($empleaveRowsPlanner);die;
        }
*/
        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'YourLeaveToken') )) {
            
            $this->view->showYourLeaveTokenPlanner = true;
            
            $this->view->empLeaveTokenRowsPlanner = $empleaveTokenRowsPlanner;
        }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Public Holidays : Permissions /.//////////////////////////////////////////////////////////////////////////////

        if ($user_type == 'superadmin') {
            $this->view->showPublicHolidays = true;
            $this->view->hRows = $hRows;
        } elseif ($access_list->checkPermission('dashboard', 'PublicHolidays')) {
            $this->view->showPublicHolidays = true;
            $this->view->hRows = $hRows;
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Your Leave Quota : Permissions //////////////////////////////////////////////////////////////////////////////

        if (($user_type != 'superadmin') && ( $access_list->checkPermission('dashboard', 'YourLeaveQuota') )) {

            $this->view->showYourLeaveQuota = true;
            $qSelect = $qModel->select()
                    ->setIntegrityCheck(false)
                    ->from(array('q' => 'leave_quota'), array('q.*'))
                    ->joinLeft(array('e' => 'employee'), 'q.employee_id = e.id', array('e.name as e.name', 'e.number as e.number'))
                    ->where("e.id= '" . $eId . "' and q.recent_record = '1'");
            $eqrows = $qModel->fetchRow($qSelect);
            $this->view->conditione = true;
            $this->view->eqrows = $eqrows;
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Employees Leave Quota: Permissions ///////////////////////////////////////////////////////////////////////////

        if ($user_type == 'superadmin') {

            $this->view->showEmployeesLeaveQuota = true;
            $this->view->qrows = $qrows;
        } elseif ($access_list->checkPermission('dashboard', 'EmployeeLeaveQuota')) {

            $this->view->showEmployeesLeaveQuota = true;
            $this->view->qrows = $qrows;
        }


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Recent Employee: Permissions /////////////////////////////////////////////////////////////////////////////////

        if ($user_type == 'superadmin') {

            $this->view->showRecentEmployees = true;
            $this->view->employeeRows = $employeeRows;
        } elseif ($access_list->checkPermission('dashboard', 'RecentEmployees')) {
            $this->view->showRecentEmployees = true;
            $this->view->employeeRows = $employeeRows;
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $db = Zend_Db_Table::getDefaultAdapter();
        $pic = $db->fetchAll("select file_name from documents where status='Active' and employee_id=" . $eId . " and type='Picture' and file_type='Image'");
        if (isset($pic[0]["file_name"])) {
            $filePath = "/uploads/Picture/thumb_100_100_" . $pic[0]["file_name"];
        } else {
            $filePath = "/images/emp-picture.jpg";
        }
        $this->view->empImage = $filePath;
        $userId = $eId = $storage->read()->id;
        $desigModel = new Application_Model_EmploymentStatusDesignation();
        $desigRow = $desigModel->fetchRow('employee_id = ' . $eId . ' and recent_record = "1"');
        if ($desigRow) {
            $desigNameModel = new Application_Model_Designation();
            $designameRow = $desigNameModel->fetchRow('id = ' . $desigRow->designation_id);
            $this->view->empDesignation = $designameRow->name;
        } elseif ($userId == 0) {
            $this->view->empDesignation = 'Administrator';
        } else {
            $this->view->empDesignation = 'Not Available';
        }

        $empModel = new Application_Model_Employees();
        $empRow = $empModel->fetchRow('id = ' . $eId);
        if ($empRow) {
            $this->view->empNum = $empRow->number;
            $this->view->empName = $empRow->name;
        } else {
            $this->view->empNum = '00000';
            if ($userId == 0) {
                $this->view->empName = 'Administrator';
            } else {
                $this->view->empName = 'Not Available';
            }
        }


        if ($access_list->checkPermission('dashboard', 'EmployeeReviewSummary')) {
            $this->view->showReviewSummary = true;
            $employeesReviewModel = new Application_Model_ReviewDataForm();

            $acl2Plugin = new Application_Controller_Plugin_Acl2();
            $empList = $acl2Plugin->getEmployeeList();


           /* $settingsModel = new Application_Model_Settings();
            $fromDateRow = $settingsModel->fetchRow('param = "review_from_date"');
            $toDateRow = $settingsModel->fetchRow('param = "review_to_date"');*/

			if($this->_request->fromdate){
				$fromDateRow['value']=$this->_request->fromdate;//date("Y-m-d",(time() -  (86400 * 120)) );
				$toDateRow['value']=$this->_request->todate;//date("Y-m-d",time() );
                                 $this->view->hiddenfield=true;
                                
			}
			else{
				$fromDateRow['value']=date("Y-m-d",(time() -  (86400 * 120)) );
				$toDateRow['value']=date("Y-m-d",time() );
			}
			$this->view->fromdate=$fromDateRow['value'];
			$this->view->todate=$toDateRow['value'];
			


//            //Not Reviewed*****************
//            $revArray = $employeesReviewModel->fetchAll('created_date > "' . $fromDateRow['value'] . '" AND created_date < "' . $toDateRow['value'] . '" and manager_id = ' . $storage->read()->employee_id);
//            $empId = '';
//            foreach ($revArray as $val) {
//                $empId .= $val['employee_id'] . ',';
//            }
//            $empId = substr($empId, 0, -1);
//            $reportToModel = new Application_Model_ReportToMapper();
//            if ($empList != '') {
//                $empListQuerry = 'and r.employee_id NOT IN ("' . $empList . '")';
//            } else {
//                $empListQuerry = '';
//            }
//            $dependantList = $reportToModel->getDependantEmployeesForReviewReport($storage->read()->employee_id, $empListQuerry);
//            $count = 0;
//            foreach ($dependantList as $val) {
//                $notReviewedList .= $val['r.employee_id'] . ',';
//                $count++;
//            }
//            $this->view->notReviewed = $count;
            //Notified to Employee
            $reportToModel = new Application_Model_ReportToMapper();
            if($data->role_id==2 || $data->role_id==1)
                $dependants = $reportToModel->getAllEmployees($eId);
            else
                $dependants = $reportToModel->getDependantEmployees($eId);
            $totalDependants = count($dependants);
            if ($totalDependants > 0) {
                $empToSearch = 'employee_id IN (';
                foreach ($dependants as $val) {
                    $empToSearch .= $val['employee_id'] . ',';
                }
                $empToSearch = substr($empToSearch, 0, -1);
                $empToSearch .= ')';

                $where = 'created_date > "' . $fromDateRow['value'] . '" AND created_date < "' . $toDateRow['value'] . '" and ' . $empToSearch . ' and employee_sign_date IS NULL';
                $select = $employeesReviewModel->select()
                        ->distinct(true)
                        ->where($where);
                $revArray = $employeesReviewModel->fetchAll($select);
                $count = count($revArray);
                $empToExclude = 'and employee_id NOT IN (';
                if ($count > 0) {
                    foreach ($revArray as $val) {
                        $empToExclude .= $val['employee_id'] . ',';
                    }
                    $empToExclude = substr($empToExclude, 0, -1);
                    $empToExclude .= ')';
                } else {
                    $empToExclude = '';
                }
                $this->view->notifiedEmployee = $count;

                //Notified to Manager
                $where = 'created_date > "' . $fromDateRow['value'] . '" AND created_date < "' . $toDateRow['value'] . '"and ' . $empToSearch . ' and employee_sign_date IS NOT NULL and reviewer_sign_date IS NULL  ' . $empToExclude;
                $select = $employeesReviewModel->select()
                        ->distinct(true)
                        ->where($where);
                $revArray = $employeesReviewModel->fetchAll($select);
                $count = count($revArray);
                $empToExclude = 'and employee_id NOT IN (';
                if ($count > 0) {
                    foreach ($revArray as $val) {
                        $empToExclude .= $val['employee_id'] . ',';
                    }
                    $empToExclude = substr($empToExclude, 0, -1);
                    $empToExclude .= ')';
                } else {
                    $empToExclude = '';
                }
                $this->view->notifiedManager = $count;

                //Notified to HR
                $where = 'created_date > "' . $fromDateRow['value'] . '" AND created_date < "' . $toDateRow['value'] . '"and ' . $empToSearch . ' and employee_sign_date IS NOT NULL and reviewer_sign_date IS NOT NULL and hr_sign_date IS NULL ' . $empToExclude;
                $select = $employeesReviewModel->select()
                        ->distinct(true)
                        ->where($where);
                $revArray = $employeesReviewModel->fetchAll($select);
                $count = count($revArray);
                $empToExclude = 'and employee_id NOT IN (';
                if ($count > 0) {
                    foreach ($revArray as $val) {
                        $empToExclude .= $val['employee_id'] . ',';
                    }
                    $empToExclude = substr($empToExclude, 0, -1);
                    $empToExclude .= ')';
                } else {
                    $empToExclude = '';
                }
                $this->view->notifiedHR = $count;

                //Notified to VTO
                $where = 'created_date > "' . $fromDateRow['value'] . '" AND created_date < "' . $toDateRow['value'] . '" and ' . $empToSearch . ' and employee_sign_date IS NOT NULL and reviewer_sign_date IS NOT NULL and hr_sign_date IS NOT NULL and vto_sign_date IS NULL  ' . $empToExclude;
                $select = $employeesReviewModel->select()
                        ->distinct(true)
                        ->where($where);
                $revArray = $employeesReviewModel->fetchAll($select);
                $count = count($revArray);
                $empToExclude = 'and employee_id NOT IN (';
                if ($count > 0) {
                    foreach ($revArray as $val) {
                        $empToExclude .= $val['employee_id'] . ',';
                    }
                    $empToExclude = substr($empToExclude, 0, -1);
                    $empToExclude .= ')';
                } else {
                    $empToExclude = '';
                }
                //echo $count;
                //die();
                $this->view->notifiedVTO = $count;

                //Notified to GM
                $where = 'created_date > "' . $fromDateRow['value'] . '" AND created_date < "' . $toDateRow['value'] . '" and ' . $empToSearch . ' and employee_sign_date IS NOT NULL and reviewer_sign_date IS NOT NULL and hr_sign_date IS NOT NULL and vto_sign_date IS NOT NULL and gm_sign_date IS NULL  ' . $empToExclude;
                $select = $employeesReviewModel->select()
                        ->distinct(true)
                        ->where($where);
                $revArray = $employeesReviewModel->fetchAll($select);
                $count = count($revArray);
                $empToExclude = 'and employee_id NOT IN (';
                if ($count > 0) {
                    foreach ($revArray as $val) {
                        $empToExclude .= $val['employee_id'] . ',';
                    }
                    $empToExclude = substr($empToExclude, 0, -1);
                    $empToExclude .= ')';
                } else {
                    $empToExclude = '';
                }
                $this->view->notifiedGM = $count;

                //Complete
                $where = 'created_date > "' . $fromDateRow['value'] . '" AND created_date < "' . $toDateRow['value'] . '" and ' . $empToSearch . ' and employee_sign_date IS NOT NULL and reviewer_sign_date IS NOT NULL and hr_sign_date IS NOT NULL and gm_sign_date IS NOT NULL and vto_sign_date IS NOT NULL  ' . $empToExclude;
                $select = $employeesReviewModel->select()
                        ->distinct(true)
                        ->where($where);
                $revArray = $employeesReviewModel->fetchAll($select);
                $count = count($revArray);
                $empToExclude = 'and employee_id NOT IN (';
                if ($count > 0) {
                    foreach ($revArray as $val) {
                        $empToExclude .= $val['employee_id'] . ',';
                    }
                    $empToExclude = substr($empToExclude, 0, -1);
                    $empToExclude .= ')';
                } else {
                    $empToExclude = '';
                }
                $this->view->revComplete = $count;

                $this->view->notReviewed = ($totalDependants - ($this->view->revComplete + $this->view->notifiedGM + $this->view->notifiedVTO + $this->view->notifiedHR + $this->view->notifiedManager + $this->view->notifiedEmployee));
                $this->view->totalRev = $this->view->revComplete + $this->view->notifiedGM + $this->view->notifiedVTO + $this->view->notifiedHR + $this->view->notifiedManager + $this->view->notifiedEmployee + $this->view->notReviewed;
            } else {
                $this->view->notReviewed = $totalDependants;
                $this->view->totalRev = $this->view->revComplete = $this->view->notifiedGM = $this->view->notifiedVTO = $this->view->notifiedHR = $this->view->notifiedManager = $this->view->notifiedEmployee = 0;
            }
        }

        if (($user_type == 'superadmin') || ($access_list->checkPermission('dashboard', 'today-report'))) {
            $this->view->showTodayReport = true;



            $locationModel = new Application_Model_Location();
            $locationList = $locationModel->getTheLocations('id');
            unset($locationList['']);
            $employeeModel = new Application_Model_Employees();

            if (count($locationList) > 0) {
                $this->view->showConditionTUR = true;
                $this->view->locationList = $locationList;
            }
        }
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
    public function todayReportAjaxFunctionalityAction() {
        $report = $this->_request->report ;
        $reportdate = "";
        $count = 1;
            $dayLastDate = array();
            while (true) {
                  $lastday = date('l', strtotime('-' . $count . ' day',strtotime(date("Y-m-j"))));
                if ($lastday != "Saturday" && $lastday != "Sunday") {
                        $dayLastDate[] = date('Y-m-j', strtotime('-' . $count . ' day',strtotime(date("Y-m-j"))));
                }
                if (count($dayLastDate) == 2)
                    break;
                $count++;
            }
            
        if($report=="lastday")
            $reportdate = $dayLastDate[0];
        elseif($report=="daylastday")
            $reportdate = $dayLastDate[1];
        else
            $reportdate = date('Y-m-d');
        
        $this->_helper->layout->disableLayout();
        $htmlcontent = '<table class="table table-striped" >
            <thead>
                <tr>
                    <th>Sr. #</th>
                    <th>Name</th>
                    <th>Reason</th>
                    <th>Remarks</th>
                </tr></thead>';
                    
                    $locationModel = new Application_Model_Location();
                    $locationList = $locationModel->getTheLocations('id');
                    unset($locationList['']);
                    if (count($locationList) > 0) {
                    $attendanceModel = new Application_Model_Attendance();
                    $reasonModel = new Application_Model_LeaveReason();
                    $reasonOptionGroup = $reasonModel->getOptionGroup();
                    $acl2Plugin = new Application_Controller_Plugin_Acl2();
                    $empList = $acl2Plugin->getEmployeeList();
                    $count = 1;
                    foreach ($locationList as $key => $value) {
                        $attendanceRows = $attendanceModel->getTodaysUpdate($reportdate, $key, $empList);
                        if ($attendanceRows->count() > 0) {
                            //$htmlcontent = '';
                            $htmlcontent .= '<thead><tr>
                                        <th colspan="4"  style="text-align:center;"><b>' . $value . '</b></th>
                                            </tr></thead>';
                            foreach ($attendanceRows as $attendance) {
                                $htmlcontent .= '<tr>
                                                    <td style="text-align:center;">' . $count . '</td>
                                                    <td>' . $attendance['e.name'] . '</td>';
                                if ($attendance['a.reason_id'] != '' && $attendance['a.reason_id'] != 0) {
                                    if ($reasonOptionGroup[$attendance['a.reason_id']] == 'Other' && date('H:i', strtotime($attendance['time_in'])) != '00:00') {
                                        $htmlcontent .= '<td>' . date('H:i', strtotime($attendance['time_in'])) . '</td>';
                                    } else {
                                        $htmlcontent .= '<td>' . $attendance['r.reason_name'] . '</td>';
                                    }
                                } else {
                                    $htmlcontent .= '<td>' . date('H:i', strtotime($attendance['time_in'])) . '</td>';
                                }
                                $htmlcontent .= '<td>' . $attendance['remarks'] . '</td>';
                                $htmlcontent .= '</tr>';
                                $count = $count + 1;
                            }
                            //echo $htmlcontent;
                        }
                    }
                    if ($count <= 1) {
                        $htmlcontent .= '<tr>
                                    <td colspan="12">No Data Found</td>
                                  </tr>';
                    }
                    } else {
                    $htmlcontent .= '<tr>
                        <td colspan="12">No Data Found</td>
                    </tr>';
                    }
                    $htmlcontent .= '</table>';
                    echo $htmlcontent;
                    die();
                
    }
    
// end of indexAction function
    public function todayReportAction() {
        
    }
    
    
    public function BulletinNewsAction() {
        
    }

    public function EmployeeOverviewAction() {
        
    }

    public function EmployeeAttendanceAction() {
        
    }

    public function EmployeeLeaveAction() {
        
    }
    public function EmployeeLeaveTokenAction() {
        
    }
    public function EmployeeLeavePlannerAction() {
        
    }
    /*
     public function EmployeeLeaveTokenPlannerAction() {
        
    }*/

    public function RecentEmployeesAction() {
        
    }

    public function YourAttendanceAction() {
        
    }
    /*
    public function YourLeaveAction() {
        
    }
     * 
     
    
    public function YourLeaveTokenAction() {
        
    }
     * */
     
    public function YourLeaveTokenAction() {
        
    }
    public function YourLeaveAction() {
        
    }
    public function YourLastReviewAction() {
        
    }

    public function EmployeeReviewsAction() {
        
    }

    public function PublicHolidaysAction() {
        
    }

    public function YourLeaveQuotaAction() {
        
    }

    public function EmployeeLeaveQuotaAction() {
        
    }

    public function EmployeeReviewSummaryAction() {
        
    }

    public function EmployeeStatsGraphAction() {
        
    }

    public function GlobalEventsAction() {
        
    }
    public function FindCnicExpiryAction(){
        
    }
    public function EmployeeConfirmationDateAction(){
        
    }
    public function EmployeeYearCompleationDateAction(){
        
    }
    public function myCnicAction(){
        
    }
    public function EmployeePlannedLeaveAction() {
        
    }

    
    
    private function getTokensForLeave($leaveRows) {
        $tokenModel = new Application_Model_Token();
        for($i = 0; $i < count($leaveRows); $i++){
            if($leaveRows[$i]['token_attachment'] != ''){
                $selectToken = $tokenModel->select()
                        ->setIntegrityCheck(false)
                        ->from(array('t'=>'tokens'),array('t.token_date as t.token_date', 't.actual_hours_in_token as t.actual_hours_in_token'))
                        ->where('t.id IN ('.$leaveRows[$i]['token_attachment'].')');
                $tokens = $tokenModel->fetchAll($selectToken);
                $leaveRows[$i]['token_attachment'] = $tokens->toArray();//array('tokens' => $tokens );
            }else{
                $leaveRows[$i]['token_attachment'] = 'No Tokens';//array('tokens' => 'No Tokens' );
            }
        }
        return $leaveRows;
    }
    
}