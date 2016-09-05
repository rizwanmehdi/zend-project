<?php

//define('compdetailindex', $_SERVER['DOCUMENT_ROOT'].'/datat');

class CompanyInformationController extends Zend_Controller_Action {

    protected $arrSettings = null;
    protected $controller = null;

    /**
     * ROLE
     * @workspace Employee Workspace Management
     * 
     * 
     * 
     *
     */
    
    public function workspaceAction() {
        $empid = $this->_request->emplId;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);

        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Workspace Info Added Successfully');
        } else if ($this->_request->update == 's') {            
            $messages[] = array('success', 'Employee Workspace Info Updated Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Workspace Info Deleted Successfully');
        }
        
        $storage = new Zend_Auth_Storage_Session();
        $session_data = $storage->read();
        if($session_data->emp_data){
            $empOldData = unserialize($session_data->emp_data);//echo "<pre>";print_r($empOldData);die;
            $oldWorkSpaceId = $empOldData->current_workspace_id;
            $this->view->oldWorkspaceId = $oldWorkSpaceId;
        }
        
        $this->view->messages = $messages;

        $modelLoc = new Application_Model_Location();
            $locRows = $modelLoc->getTheLocations('name');

            $wsModel = new Application_Model_Workspace();
            $wsRows = $wsModel->getWorkspace('name'); 
            
            $wsTableModel = new Application_Model_WorkspaceTable();
            $wsTableRows  = $wsTableModel->getAllWorkspaceTable('name');

            $emplModel = new Application_Model_Employees();
            $managerList = $emplModel->getManagers();

            $fields = array(
            'l.name' => array(
                'title' => 'Location',
                'type' => 'dropdown',
                'value' => $locRows,
                'default' => 'always'
            ),
            'ws.name' => array(
                'title' => 'Workspace',
                'type' => 'dropdown',
                'value' => $wsRows,
                'default' => 'always'
            ),
            'wstable.name' => array(
                'title' => 'Table Name',
                'type' => 'dropdown',
                'value' => $wsTableRows,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esws.created_date' => array(
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
            'esws.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esws.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');
            $this->view->actionType = 'listing';

            $eswsModel = new Application_Model_EmploymentStatusWorkspace();
            $DataForForm = array();
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusWorkspace();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllWorkspaces($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllWorkspaces('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }


        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from workspace where id=" . $page['id']);

//				if( $checkAdmin[0]['modified_by']=='0' ){
//					$page['emp2.name']='Admin';
//				}
            }
        }
    }
    
    
    public function workspaceAdminAddAction() { 
        $this->_helper->layout()->disableLayout();    
        
        $empid = $this->_request->emplId;
        
        $modelLoc = new Application_Model_Location();
            $locRows = $modelLoc->getTheLocations('name');

            $wsModel = new Application_Model_Workspace();
            $wsRows = $wsModel->getWorkspace('name');
            
            $wsTableModel = new Application_Model_WorkspaceTable();
            $wsTableRows  = $wsTableModel->getAllWorkspaceTable('name');

            $emplModel = new Application_Model_Employees();
            $managerList = $emplModel->getManagers();
        if ($this->_request->emplId != '') {
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esws.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');
            $this->view->actionType = 'add';


            $eswsModel = new Application_Model_EmploymentStatusWorkspace();
            $DataForForm = array();
            
            if ($this->view->actionType == 'add' ) {
                
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                if ($this->view->actionType == 'add') {
                    $eswsSelect = $eswsModel->select()
                                    ->where("employee_id=" . $this->_request->emplId)
                                    ->order("modified_date DESC")->limit(1);
                    $eswsRow = $eswsModel->fetchRow($eswsSelect);
                    if ($eswsRow) {
                        $DataForForm = $eswsRow->toArray();
                        $form = new Application_Form_EmploymentStatusWorkspace($DataForForm["location_id"], $DataForForm["workspace_id"],0, $this->_request->emplId);
                        $form->setAttrib('class', 'form-horizontal clearfix');
                        $form->populate($DataForForm);
                    } else {
                        $form = new Application_Form_EmploymentStatusWorkspace(0, 0,0, $this->_request->emplId);
                    }
                }
                
                    $form->setAction("/company-information/workspace-admin-add/emplId/".$this->_request->emplId);
                    $form->removeElement("Cancel");
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Employee Workspace";

                if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {
                    
                    $formData = $this->_request->getPost();//print_r($formData);die;
                    if ($form->isValid($formData)) {
                        
                        $officeLayoutPlugin = new Application_Controller_Plugin_OfficeLayout();
                        // Establishing Session for Emloyees old work space
                        $employeeOldData = $emplModel->fetchRow('id = '.$empid);
                        $employeeOldData = serialize($employeeOldData);
                        $storage = new Zend_Auth_Storage_Session();
                        $session_data = $storage->read();
                        $session_data->emp_data = $employeeOldData;
                        $storage->write($session_data);//echo "<pre>";print_r($storage->read());die;
                        
                        if ($this->view->actionType == 'add') {
                            $row = $eswsModel->createRow();
                            $row->created_date = date('Y-m-d H:i:s');
                            $storage = new Zend_Auth_Storage_Session();
                            $row->created_by = $storage->read()->id;
                            $row->employee_id = $form->getValue('employee_id');
                            $storage = new Zend_Auth_Storage_Session();
                        }
                        $eswsSelect = $eswsModel->select()
                                    ->where("employee_id=" . $this->_request->emplId)
                                    ->order("modified_date DESC")->limit(1);
                        $eswsRow = $eswsModel->fetchRow($eswsSelect);
                        if($eswsRow){
                            $currentEmpSitting = $eswsRow->toArray();
                            $oldWorkspaceId = $currentEmpSitting["workspace_id"];
                        }else{
                            $oldWorkspaceId = 0;
                        }
                        $newWorkspaceId = $form->getValue('workspace_id');     
                        
                        $row->employee_id = $form->getValue('employee_id');
                        $row->workspace_table_id = $form->getValue('workspace_table_id');
                        $row->workspace_id = $form->getValue('workspace_id');
                        $row->location_id = $form->getValue('location_id');
                        $row->comments = $form->getValue('comments');
                        $row->recent_record = 1;
                        $row->approved_by = $form->getValue('approved_by');
                        $row->effective_date = date('Y-m-d', strtotime($form->getValue('effective_date')));
                        $column = array(
                            "recent_record" => "0"
                        );
                        $eswsModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and recent_record='1'");
                        $row->save();
                        $employeeColumns = array(
                            "current_workspace_id" => $form->getValue('workspace_id'),
                            "current_location_id" => $form->getValue('location_id')
                        );
                        $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");
                        $workSapceImagesToCreate = array();
                        if($oldWorkspaceId==0){
                            $workSapceImagesToCreate['0']['id'] = $newWorkspaceId;                        
                        }else{
                            $workSapceImagesToCreate['0']['id'] = $oldWorkspaceId;
                            $workSapceImagesToCreate['1']['id'] = $newWorkspaceId;                        
                        }
                        $officeLayoutPlugin->generateNewHallImages($workSapceImagesToCreate);
                        $this->_redirect("/workspace/index/emplId/".$this->_request->emplId."/w". $this->view->actionType . '/s/workspace_id/'.$row->workspace_id);
                    } else {
                        $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    $form->populate($DataForForm);
                }
            }
        }
    }
    
    /**
     * ROLE
     * @workspace Employee Workspace Add
     * 
     * 
     * 
     *
     */
    public function workspaceAddAction() { 
        // action body
        //echo "((salman))";
        //die();
        
        $empid = $this->_request->emplId;
        
        
            $front = Zend_Controller_Front::getInstance();
            $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
            $Plugin->verifyAccess($empid);
        


        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Workspace Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Workspace Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $modelLoc = new Application_Model_Location();
            $locRows = $modelLoc->getTheLocations('name');

            $wsModel = new Application_Model_Workspace();
            $wsRows = $wsModel->getWorkspace('name');
            
            $wsTableModel = new Application_Model_WorkspaceTable();
            $wsTableRows  = $wsTableModel->getAllWorkspaceTable('name');

            $emplModel = new Application_Model_Employees();
            $managerList = $emplModel->getManagers();

            $fields = array(
            'l.name' => array(
                'title' => 'Location',
                'type' => 'dropdown',
                'value' => $locRows,
                'default' => 'always'
            ),
            'ws.name' => array(
                'title' => 'Workspace',
                'type' => 'dropdown',
                'value' => $wsRows,
                'default' => 'always'
            ),
            'wstable.name' => array(
                'title' => 'Table Name',
                'type' => 'dropdown',
                'value' => $wsTableRows,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esws.created_date' => array(
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
            'esws.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esws.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');
            $this->view->actionType = 'add';


            $eswsModel = new Application_Model_EmploymentStatusWorkspace();
            $DataForForm = array();

            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                if ($this->view->actionType == 'add') {
                    $eswsSelect = $eswsModel->select()
                                    ->where("employee_id=" . $this->_request->emplId)
                                    ->order("modified_date DESC")->limit(1);
                    $eswsRow = $eswsModel->fetchRow($eswsSelect);
                    if ($eswsRow) {
                        $DataForForm = $eswsRow->toArray();
                        $form = new Application_Form_EmploymentStatusWorkspace($DataForForm["location_id"], $DataForForm["workspace_id"],0, $this->_request->emplId);
                        $form->populate($DataForForm);
                    } else {
                        $form = new Application_Form_EmploymentStatusWorkspace(0, 0,0, $this->_request->emplId);
                    }
                }
                
                    $form->removeElement("employee_name");
                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Employee Workspace";

                if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {
                    
                    $officeLayoutPlugin = new Application_Controller_Plugin_OfficeLayout();
                    

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {
                        
                            $eswsSelect = $eswsModel->select()
                                            ->where("employee_id=" . $this->_request->emplId)
                                            ->order("modified_date DESC")->limit(1);
                            
                            $eswsRow = $eswsModel->fetchRow($eswsSelect);
                            
                            if($eswsRow) {
                                $currentEmpSitting = $eswsRow->toArray();
                                $oldWorkspaceId = $currentEmpSitting["workspace_id"];                            
                            }else{
                                $oldWorkspaceId = 0;
                            }
//                            $currentEmpSitting = $eswsRow->toArray();
//                            $oldWorkspaceId = $currentEmpSitting["workspace_id"];
                            $newWorkspaceId = $form->getValue('workspace_id');     

                        
                        // Establishing Session for Emloyees old work space
                        $employeeOldData = $emplModel->fetchRow('id = '.$empid);
                        $employeeOldData = serialize($employeeOldData);
                        $storage = new Zend_Auth_Storage_Session();
                        $session_data = $storage->read();
                        $session_data->emp_data = $employeeOldData;
                        $storage->write($session_data);//echo "<pre>";print_r($storage->read());die;
                            
                        if ($this->view->actionType == 'add') {
                            $row = $eswsModel->createRow();
                            $row->created_date = date('Y-m-d H:i:s');
                            $storage = new Zend_Auth_Storage_Session();
                            $row->created_by = $storage->read()->id;
                            $row->employee_id = $form->getValue('employee_id');
                            $storage = new Zend_Auth_Storage_Session();
                        }
                        $row->employee_id = $form->getValue('employee_id');
                        $row->workspace_table_id = $form->getValue('workspace_table_id');
                        $row->workspace_id = $form->getValue('workspace_id');
                        $row->location_id = $form->getValue('location_id');
                        $row->comments = $form->getValue('comments');
                        $row->recent_record = 1;
                        $row->approved_by = $form->getValue('approved_by');
                        $row->effective_date = date('Y-m-d', strtotime($form->getValue('effective_date')));
                        $column = array(
                            "recent_record" => "0"
                        );
                        $eswsModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and recent_record='1'");
                        //$empStatusWorkspaceModel = new Application_Model_EmploymentStatusWorkspace();
						//$lastSitting = $empStatusWorkspaceModel->getLastSitting($empid);//update last sitting
						$row->save();

                        $employeeColumns = array(
                            "current_workspace_id" => $form->getValue('workspace_id'),
                            "current_location_id" => $form->getValue('location_id')
                        );
                        $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");
                        
                        $workSapceImagesToCreate = array();
                        $workSapceImagesToCreate['0']['id'] = $oldWorkspaceId;
                        $workSapceImagesToCreate['1']['id'] = $newWorkspaceId;                        
                        $officeLayoutPlugin->generateNewHallImages($workSapceImagesToCreate);
                        
                        
                            $this->_redirect('company-information/workspace/emplId/'
                                . $this->_request->emplId . '/'
                                . $this->view->actionType . '/s'
                        );
                        
                    } else {
                        $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    $form->setAttrib('class', 'form-horizontal clearfix');
                    $form->populate($DataForForm);
                }
            }
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusWorkspace();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllWorkspaces($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllWorkspaces('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }

        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from workspace where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }



        $this->view->paginator = $paginator;
    }

    public function workspaceEditAction() {
        // action body
        // action body
        //echo "((slaman))";
        //die("here");
        $empid = $this->_request->emplId;
        $id = $this->_request->id;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);

        if ($id != 0) {
            $this->view->showLeftSubMenu = true;
            
            
            
            $messages = array();

            if ($this->_request->update == 's') {
                $messages[] = array('success', 'Employee Workspace Info Updated Successfully');
            } else if ($this->_request->delete == 's') {
                $messages[] = array('success', 'Employee Workspace Info Deleted Successfully');
            }
            $this->view->messages = $messages;

            $modelLoc = new Application_Model_Location();
            $locRows = $modelLoc->getTheLocations('name');

            $wsModel = new Application_Model_Workspace();
            $wsRows = $wsModel->getWorkspace('name');
            
            $wsTableModel = new Application_Model_WorkspaceTable();
            $wsTableRows  = $wsTableModel->getAllWorkspaceTable('name');

            $emplModel = new Application_Model_Employees();
            $managerList = $emplModel->getManagers();          
           
                    
            
            $fields = array(
                'l.name' => array(
                    'title' => 'Location',
                    'type' => 'dropdown',
                    'value' => $locRows,
                    'default' => 'always'
                ),
                'ws.name' => array(
                    'title' => 'Workspace',
                    'type' => 'dropdown',
                    'value' => $wsRows,
                    'default' => 'always'
                ),
                'wstable.name' => array(
                    'title' => 'Table Name',
                    'type' => 'dropdown',
                    'value' => $wsTableRows,
                    'default' => 'always'
                ),
                'effective_date' => array(
                    'title' => 'Effective Date',
                    'type' => 'textbox',
                    'value' => '',
                    'default' => 'no'
                ),
                'comments' => array(
                    'title' => 'Comments',
                    'type' => 'dropdown',
                    'value' => '',
                    'default' => 'yes'
                ),
                'em.name' => array(
                    'title' => 'Approved By',
                    'type' => 'dropdown',
                    'value' => $managerList,
                    'default' => 'yes'
                ),
                'emp1.name' => array(
                    'title' => 'Created by',
                    'type' => 'textbox',
                    'value' => '',
                    'default' => 'no'
                ),
                'esws.created_date' => array(
                    'title' => 'created date',
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
                'esws.modified_date' => array(
                    'title' => 'Modified Date',
                    'type' => 'textbox',
                    'value' => '',
                    'default' => 'no'
                )
            );


            $sql_formatted_values = '';


            if ($this->_request->emplId != '') {
                unset($fields['e.name']);
                $this->view->showEmployeeLeftMenu = true;
                $sql_formatted_values = "and esws.employee_id='{$this->_request->emplId}'";
                $this->view->id = $this->getRequest()->getParam('emplId');
                $this->view->actionType = 'update';


                $eswsModel = new Application_Model_EmploymentStatusWorkspace();
                $eswsModelRecord = $eswsModel->fetchRow('id= ' . $id);
                if ($eswsModelRecord['location_id'] == "")
                    $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
                $DataForForm = array();

                if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                    $this->view->inc_validator = true;
                    $this->view->inc_calander = true;
                    if ($this->view->actionType == 'update') {
                        $eswsSelect = $eswsModel->select()
                                        ->where("employee_id=" . $this->_request->emplId . " and id=" . $id)
                                        ->order("modified_date DESC")->limit(1);
                        $eswsRow = $eswsModel->fetchRow($eswsSelect);
                        if ($eswsRow) {
                            $DataForForm = $eswsRow->toArray();
                            $form = new Application_Form_EmploymentStatusWorkspace($DataForForm["location_id"],$DataForForm["workspace_id"],0, $this->_request->emplId);
                            $form->populate($DataForForm);
                        } else {
                            $form = new Application_Form_EmploymentStatusWorkspace(0, 0, 0, $this->_request->emplId);
                        }
                    }


                    $this->view->form = $form;
                    $this->view->title = ucfirst($this->view->actionType) . " Employee Workspace";

                    if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                            && !isset($this->_request->removeallFilter)
                            && !isset($this->_request->search_table_value)) {

                        
                        $officeLayoutPlugin = new Application_Controller_Plugin_OfficeLayout();
                        
                        $formData = $this->_request->getPost();
                        if ($form->isValid($formData)) {
                            
                            $eswsSelect = $eswsModel->select()
                                            ->where("employee_id=" . $this->_request->emplId . " and id=" . $id)
                                            ->order("modified_date DESC")->limit(1);
                            $eswsRow = $eswsModel->fetchRow($eswsSelect);
                            $currentEmpSitting = $eswsRow->toArray();
                            $oldWorkspaceId = $currentEmpSitting["workspace_id"];
                            $newWorkspaceId = $form->getValue('workspace_id');     

                            
                            
                            $storage = new Zend_Auth_Storage_Session();
                            //$modified_by = $storage->read()->id;
                            // Establishing Session for Emloyees old work space
                            $employeeOldData = $emplModel->fetchRow('id = '.$empid);
                            $employeeOldData = serialize($employeeOldData);
                            $storage = new Zend_Auth_Storage_Session();
                            $session_data = $storage->read();
                            $session_data->emp_data = $employeeOldData;
                            $storage->write($session_data);//echo "<pre>";print_r($storage->read());die;

                            $column = array(
                                'employee_id' => $form->getValue('employee_id'),
                                'workspace_id' => $form->getValue('workspace_id'),
                                'workspace_table_id' => $form->getValue('workspace_table_id'),
                                'location_id' => $form->getValue('location_id'),
                                'comments' => $form->getValue('comments'),
                                'effective_date' => date('Y-m-d', strtotime($form->getValue('effective_date'))),
                                'approved_by' => $form->getValue('approved_by'),
                                'modified_by' => $storage->read()->id
                            );
                            $eswsModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and id = " . $id);

                            $eswsGet = $eswsModel->fetchRow("employee_id='" . $form->getValue('employee_id') . "' and recent_record = '1' ");
                            $employeeColumns = array(
                                "current_workspace_id" => $eswsGet->workspace_id,
                                "current_location_id" => $eswsGet->location_id
                            );
                            $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");

                        $workSapceImagesToCreate = array();
                        $workSapceImagesToCreate['0']['id'] = $oldWorkspaceId;
                        $workSapceImagesToCreate['1']['id'] = $newWorkspaceId;                        
                        $officeLayoutPlugin->generateNewHallImages($workSapceImagesToCreate);
                            
                            
                            $this->_redirect('company-information/workspace/emplId/'
                                    . $this->_request->emplId . '/'
                                    . $this->view->actionType . '/s'
                            );
                        } else {
                            $form->setAttrib('class', 'form-horizontal clearfix');
                            $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                            $form->populate($formData);
                            $this->view->errorMessages = $form->getMessages();
                        }
                    } else {
                        $form->setAttrib('class', 'form-horizontal clearfix');
                        $this->view->dataForForm = $DataForForm;
                        $form->populate($DataForForm);
                    }
                }
            }else
                $this->view->actionType = 'general';


            $this->view->defaultFields = $fields;
            $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
            $this->view->arrSettings = $this->arrSettings;
            $search_paramter = $this->arrSettings["filters"];
            $posted_fields_string = $search_paramter['posted_values'];
            $sql_formatted_values .= $search_paramter['sql_formatted_values'];
            $this->view->posted_fields_string = $posted_fields_string;
            $this->view->fields_array = $fields;

            $mdlCInfo = new Application_Model_EmploymentStatusWorkspace();
            if (isset($this->arrSettings["sort"]))
                $records = $mdlCInfo->selectAllWorkspaces($this->arrSettings["sort"], $sql_formatted_values);
            else
                $records = $mdlCInfo->selectAllWorkspaces('', $sql_formatted_values);
            $paginator = new Zend_Paginator($records);
            $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
            $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

            foreach ($paginator as $page) {
                if ($page['effective_date'])
                    $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
            }

            foreach ($paginator as $page) {

                // Name of Created by Employee
                if ($page['emp1.name'] == '') {
                    $page['emp1.name'] = 'Administrator';
                }

                // Name of Modified by Employee
                if ($page['emp2.name'] == '') {

                    $db = Zend_Db_Table::getDefaultAdapter();
                    $checkAdmin = $db->fetchAll("select modified_by from workspace where id=" . $page['id']);

                    if ($checkAdmin[0]['modified_by'] == '0') {

                        $page['emp2.name'] = 'Admin';
                    }
                }
            }
        } else {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @designation Employee Designation Management
     * 
     * 
     * 
     *
     */
    public function designationAction() {

        $empid = $this->_request->emplId;


        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);


        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Designation Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Designation Info Deleted Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Employee Designation Info Updated Successfully');
        }

        $this->view->messages = $messages;

        $modelLoc = new Application_Model_Designation();
        $locRows = $modelLoc->getTheDesignations('name');

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $fields = array(
            'des.name' => array(
                'title' => 'Designation',
                'type' => 'dropdown',
                'value' => $locRows,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esdes.created_date' => array(
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
            'esdes.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esdes.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');
            $this->view->actionType = 'listing';

            $esdesModel = new Application_Model_EmploymentStatusDesignation();
            $DataForForm = '';
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusDesignation();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllDesignations($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllDesignations('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }


        $this->view->paginator = $paginator;

        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from designation where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @designation Employee Designation Add
     * 
     * 
     * 
     *
     */
    public function designationAddAction() {
        // action body

        $empid = $this->_request->emplId;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);



        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Designation Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Designation Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $modelLoc = new Application_Model_Designation();
        $locRows = $modelLoc->getTheDesignations('name');

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $fields = array(
            'des.name' => array(
                'title' => 'Designation',
                'type' => 'dropdown',
                'value' => $locRows,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esdes.created_date' => array(
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
            'esdes.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esdes.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'add';
            $esdesModel = new Application_Model_EmploymentStatusDesignation();
            $DataForForm = '';

            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                if ($this->view->actionType == 'add') {
                    $esdesSelect = $esdesModel->select()
                                    ->where("employee_id=" . $this->_request->emplId)
                                    ->order("modified_date DESC")->limit(1);
                    $esdesRow = $esdesModel->fetchRow($esdesSelect);
                    if ($esdesRow) {
                        $DataForForm = $esdesRow->toArray();
                        $form = new Application_Form_EmploymentStatusDesignation(0, $this->_request->emplId);
                        $form->populate($DataForForm);
                    } else {
                        $form = new Application_Form_EmploymentStatusDesignation(0, $this->_request->emplId);
                    }
                }

                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Employee Designation";

                if ($this->_request->isPost()
                        && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {
                        if ($this->view->actionType == 'add') {
                            $row = $esdesModel->createRow();
                            $row->created_date = date('Y-m-d H:i:s');
                            $storage = new Zend_Auth_Storage_Session();
                            $row->created_by = $storage->read()->id;
                            $row->employee_id = $form->getValue('employee_id');
                        }
                        $row->employee_id = $form->getValue('employee_id');
                        $row->designation_id = $form->getValue('designation_id');
                        $row->comments = $form->getValue('comments');
                        $row->recent_record = 1;
                        $row->approved_by = $form->getValue('approved_by');
                        $row->effective_date = date('Y-m-d', strtotime($form->getValue('effective_date')));
                        $column = array(
                            "recent_record" => "0"
                        );
                        $esdesModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and recent_record='1'");
                        $row->save();

                        $employeeColumns = array(
                            "current_designation_id" => $form->getValue('designation_id')
                        );
                        $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");

                        $this->_redirect('company-information/designation/emplId/'
                                . $this->_request->emplId . '/'
                                . $this->view->actionType . '/s'
                        );
                    } else {
                        $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    if (is_array($DataForForm))
                        $form->populate($DataForForm);
                }
            }
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusDesignation();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllDesignations($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllDesignations('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }


        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from designation where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }


        $this->view->paginator = $paginator;
    }

    public function designationEditAction() {
        // action body

        $empid = $this->_request->emplId;
        $id = $this->_request->id;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);



        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Designation Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Designation Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $modelLoc = new Application_Model_Designation();
        $locRows = $modelLoc->getTheDesignations('name');

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $fields = array(
            'des.name' => array(
                'title' => 'Designation',
                'type' => 'dropdown',
                'value' => $locRows,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esdes.created_date' => array(
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
            'esdes.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esdes.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'update';
            $esdesModel = new Application_Model_EmploymentStatusDesignation();
            $DataForForm = '';

            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                if ($this->view->actionType == 'update') {
                    $esdesSelect = $esdesModel->select()
                                    ->where("employee_id=" . $this->_request->emplId . " and id = " . $id)
                                    ->order("modified_date DESC")->limit(1);
                    $esdesRow = $esdesModel->fetchRow($esdesSelect);
                    if ($esdesRow) {
                        $DataForForm = $esdesRow->toArray();
                        $form = new Application_Form_EmploymentStatusDesignation(0, $this->_request->emplId);
                        $form->populate($DataForForm);
                    } else {
                        $form = new Application_Form_EmploymentStatusDesignation(0, $this->_request->emplId);
                    }
                }

                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Employee Designation";

                if ($this->_request->isPost()
                        && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {
                        $storage = new Zend_Auth_Storage_Session();
                        $column = array(
                            "employee_id" => $form->getValue('employee_id'),
                            "designation_id" => $form->getValue('designation_id'),
                            "comments" => $form->getValue('comments'),
                            "approved_by" => $form->getValue('approved_by'),
                            "effective_date" => date('Y-m-d', strtotime($form->getValue('effective_date'))),
                            'modified_by' => $storage->read()->id
                        );
                        $esdesModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and id = " . $id);

                        $esdesGet = $esdesModel->fetchRow("employee_id='" . $form->getValue('employee_id') . "' and recent_record = '1' ");
                        $employeeColumns = array(
                            "current_designation_id" => $esdesGet->designation_id
                        );
                        $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");

                        $this->_redirect('company-information/designation/emplId/'
                                . $this->_request->emplId . '/'
                                . $this->view->actionType . '/s'
                        );
                    } else {
                        $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    if (is_array($DataForForm))
                        $form->populate($DataForForm);
                }
            }
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusDesignation();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllDesignations($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllDesignations('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }


        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from designation where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }


        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @department Employee Department Management
     * 
     * 
     * 
     *
     */
    public function departmentAction() {

        $empid = $this->_request->emplId;


        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);

        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Department Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Department Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $modelDiv = new Application_Model_Division();
        $divRows = $modelDiv->selectTheDivisions('id');

        $deptModel = new Application_Model_Department();
        $deptRows = $deptModel->getTheDepartments('id');

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $settingsModel = new Application_Model_Settings();
        $settingsRowOne = $settingsModel->fetchRow("param='departmen_optional_field_1_label'");
        $settingsRowTwo = $settingsModel->fetchRow("param='departmen_optional_field_2_label'");

        $fields = array(
            'dept.name' => array(
                'title' => 'Department',
                'type' => 'dropdown',
                'value' => $deptRows,
                'default' => 'always'
            ),
            'div.name' => array(
                'title' => 'Division',
                'type' => 'dropdown',
                'value' => $divRows,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
            'optional_field_1' => array(
                'title' => $settingsRowOne->value,
                'type' => 'text',
                'value' => '',
                'default' => 'no'
            ),
            'optional_field_2' => array(
                'title' => $settingsRowTwo->value,
                'type' => 'text',
                'value' => '',
                'default' => 'no'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esdept.created_date' => array(
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
            'esdept.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esdept.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'listing';

            $esdeptModel = new Application_Model_EmploymentStatusDepartment();
            $DataForForm = '';
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusDepartment();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllDepartments($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllDepartments('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }


        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from department where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
//					
                }
            }
        }



        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @department Employee Department Add
     * 
     * 
     *
     */
    public function departmentAddAction() {
        // action body

        $empid = $this->_request->emplId;


        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);

        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Department Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Department Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $modelDiv = new Application_Model_Division();
        $divRows = $modelDiv->selectTheDivisions('id');

        $deptModel = new Application_Model_Department();
        $deptRows = $deptModel->getTheDepartments('id');

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $settingsModel = new Application_Model_Settings();
        $settingsRowOne = $settingsModel->fetchRow("param='departmen_optional_field_1_label'");
        $settingsRowTwo = $settingsModel->fetchRow("param='departmen_optional_field_2_label'");

        $fields = array(
            'dept.name' => array(
                'title' => 'Department',
                'type' => 'dropdown',
                'value' => $deptRows,
                'default' => 'always'
            ),
            'div.name' => array(
                'title' => 'Division',
                'type' => 'dropdown',
                'value' => $divRows,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
            'optional_field_1' => array(
                'title' => $settingsRowOne->value,
                'type' => 'text',
                'value' => '',
                'default' => 'no'
            ),
            'optional_field_2' => array(
                'title' => $settingsRowTwo->value,
                'type' => 'text',
                'value' => '',
                'default' => 'no'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esdept.created_date' => array(
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
            'esdept.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esdept.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'add';


            $esdeptModel = new Application_Model_EmploymentStatusDepartment();
            $DataForForm = '';

            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                if ($this->view->actionType == 'add') {
                    $esdeptSelect = $esdeptModel->select()
                                    ->where("employee_id=" . $this->_request->emplId)
                                    ->order("modified_date DESC")->limit(1);
                    $esdeptRow = $esdeptModel->fetchRow($esdeptSelect);
                    if ($esdeptRow) {
                        $DataForForm = $esdeptRow->toArray();
                        $form = new Application_Form_EmploymentStatusDepartment($DataForForm["division_id"], 0, $this->_request->emplId);
                       
                        $form->populate($DataForForm);
                    } else {
                        $form = new Application_Form_EmploymentStatusDepartment(0, 0, $this->_request->emplId);
                    }
                }

                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                
                $this->view->title = ucfirst($this->view->actionType) . " Employee Department";

                if ($this->_request->isPost()
                        && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {
                        if ($this->view->actionType == 'add') {
                            $row = $esdeptModel->createRow();
                            $row->created_date = date('Y-m-d H:i:s');
                            $storage = new Zend_Auth_Storage_Session();
                            $row->created_by = $storage->read()->id;
                            $row->employee_id = $form->getValue('employee_id');
                            $storage = new Zend_Auth_Storage_Session();
                        }
                        $row->employee_id = $form->getValue('employee_id');
                        $row->division_id = $form->getValue('division_id');
                        $row->department_id = $form->getValue('department_id');
                        $row->comments = $form->getValue('comments');
                        $row->optional_field_1 = $form->getValue('optional_field_1');
                        $row->optional_field_2 = $form->getValue('optional_field_2');
                        $row->recent_record = 1;
                        $row->approved_by = $form->getValue('approved_by');
                        $row->effective_date = date('Y-m-d', strtotime($form->getValue('effective_date')));
                        $column = array(
                            "recent_record" => "0"
                        );
                        $esdeptModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and recent_record='1'");
                        $row->save();

                        $employeeColumns = array(
                            "current_division_id" => $form->getValue('division_id'),
                            "current_department_id" => $form->getValue('department_id')
                        );
                        $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");

                        $this->_redirect('company-information/department/emplId/'
                                . $this->_request->emplId . '/'
                                . $this->view->actionType . '/s'
                        );
                    } else {
                        $form->setAttrib('class', 'form-horizontal clearfix');
                        $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    if (is_array($DataForForm))
                        $form->populate($DataForForm);
                }
            }
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusDepartment();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllDepartments($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllDepartments('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }


        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from department where id=" . $page['id']);

                /* 				if( $checkAdmin[0]['modified_by']=='0' ){

                  $page['emp2.name']='Admin';

                  }
                 */
            }
        }



        $this->view->paginator = $paginator;
    }

    public function departmentEditAction() {
        // action body
        // action body

        $empid = $this->_request->emplId;


        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);

        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Department Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Department Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $modelDiv = new Application_Model_Division();
        $divRows = $modelDiv->selectTheDivisions('id');

        $deptModel = new Application_Model_Department();
        $deptRows = $deptModel->getTheDepartments('id');

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $settingsModel = new Application_Model_Settings();
        $settingsRowOne = $settingsModel->fetchRow("param='departmen_optional_field_1_label'");
        $settingsRowTwo = $settingsModel->fetchRow("param='departmen_optional_field_2_label'");

        $fields = array(
            'dept.name' => array(
                'title' => 'Department',
                'type' => 'dropdown',
                'value' => $deptRows,
                'default' => 'always'
            ),
            'div.name' => array(
                'title' => 'Division',
                'type' => 'dropdown',
                'value' => $divRows,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'no'
            ),
            'optional_field_1' => array(
                'title' => $settingsRowOne->value,
                'type' => 'text',
                'value' => '',
                'default' => 'no'
            ),
            'optional_field_2' => array(
                'title' => $settingsRowTwo->value,
                'type' => 'text',
                'value' => '',
                'default' => 'no'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esdept.created_date' => array(
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
            'esdept.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            $id = $this->_request->id;
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esdept.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'update';


            $esdeptModel = new Application_Model_EmploymentStatusDepartment();
            $DataForForm = '';

            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                if ($this->view->actionType == 'update') {
                    $esdeptSelect = $esdeptModel->select()
                                    ->where("employee_id=" . $this->_request->emplId . " and id = " . $id)
                                    ->order("modified_date DESC")->limit(1);
                    $esdeptRow = $esdeptModel->fetchRow($esdeptSelect);
                    if ($esdeptRow) {
                        $DataForForm = $esdeptRow->toArray();
                        $form = new Application_Form_EmploymentStatusDepartment($DataForForm["division_id"], 0, $this->_request->emplId);
                        $form->populate($DataForForm);
                    } else {
                        $form = new Application_Form_EmploymentStatusDepartment(0, 0, $this->_request->emplId);
                    }
                }

                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Employee Department";

                if ($this->_request->isPost()
                        && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {
                        $storage = new Zend_Auth_Storage_Session();
                        $column = array(
                            "employee_id" => $form->getValue('employee_id'),
                            "division_id" => $form->getValue('division_id'),
                            "department_id" => $form->getValue('department_id'),
                            "comments" => $form->getValue('comments'),
                            "optional_field_1" => $form->getValue('optional_field_1'),
                            "optional_field_2" => $form->getValue('optional_field_2'),
                            "approved_by" => $form->getValue('approved_by'),
                            "effective_date" => date('Y-m-d', strtotime($form->getValue('effective_date'))),
                            'modified_by' => $storage->read()->id
                        );
                        $esdeptModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and id = " . $id);

                        $esdeptGet = $esdeptModel->fetchRow("employee_id='" . $form->getValue('employee_id') . "' and recent_record = '1' ");
                        $employeeColumns = array(
                            "current_division_id" => $esdeptGet->division_id,
                            "current_department_id" => $esdeptGet->department_id
                        );
                        $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");

                        $this->_redirect('company-information/department/emplId/'
                                . $this->_request->emplId . '/'
                                . $this->view->actionType . '/s'
                        );
                    } else {
                        $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    if (is_array($DataForForm))
                        $form->populate($DataForForm);
                }
            }
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusDepartment();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllDepartments($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllDepartments('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }


        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from department where id=" . $page['id']);

                /* 				if( $checkAdmin[0]['modified_by']=='0' ){

                  $page['emp2.name']='Admin';

                  }
                 */
            }
        }



        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @jobstatus Employee Job Status Management
     * 
     * 
     * 
     *
     */
    public function jobStatusAction() {

        $empid = $this->_request->emplId;


        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);

        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Job Status Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Job Status Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $statusArray = array(
            'Internship' => 'Internship',
            'Permanent' => 'Permanent',
            'Probation' => 'Probation',
            'Resigned' => 'Resigned',
            'Terminated' => 'Terminated'
        );

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $fields = array(
            'esjs.job_status' => array(
                'title' => 'Job Status',
                'type' => 'dropdown',
                'value' => $statusArray,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esjs.created_date' => array(
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
            'esjs.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esjs.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'listing';

            $esjsModel = new Application_Model_EmploymentStatusJobStatus();
            $DataForForm = '';
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusJobStatus();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllJobStatus($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllJobStatus('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }


        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from employment_status_job_status where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }

        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @jobstatus Employee Job Status Add
     * 
     * 
     *
     */
    public function jobStatusAddAction() {

        $empid = $this->_request->emplId;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);



        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Job Status Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Job Status Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $statusArray = array(
            'Internship' => 'Internship',
            'Permanent' => 'Permanent',
            'Probation' => 'Probation',
            'Resigned' => 'Resigned',
            'Terminated' => 'Terminated'
        );

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $fields = array(
            'esjs.job_status' => array(
                'title' => 'Job Status',
                'type' => 'dropdown',
                'value' => $statusArray,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esjs.created_date' => array(
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
            'esjs.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esjs.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'add';


            $esjsModel = new Application_Model_EmploymentStatusJobStatus();
            $DataForForm = '';

            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                if ($this->view->actionType == 'add') {
                    $esjsSelect = $esjsModel->select()
                                    ->where("employee_id=" . $this->_request->emplId)
                                    ->order("modified_date DESC")->limit(1);
                    $esjsRow = $esjsModel->fetchRow($esjsSelect);
                    if ($esjsRow) {
                        $DataForForm = $esjsRow->toArray();
                        $form = new Application_Form_EmploymentStatusJobStatus(0, $this->_request->emplId);
                        $form->populate($DataForForm);
                    } else {
                        $form = new Application_Form_EmploymentStatusJobStatus(0, $this->_request->emplId);
                    }
                }

                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Employee Job Status";

                if ($this->_request->isPost()
                        && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {
                        if ($this->view->actionType == 'add') {
                            $row = $esjsModel->createRow();
                            $row->created_date = date('Y-m-d H:i:s');
                            $storage = new Zend_Auth_Storage_Session();
                            $row->created_by = $storage->read()->id;
                            $row->employee_id = $form->getValue('employee_id');
                            $storage = new Zend_Auth_Storage_Session();
                        }
                        $row->employee_id = $form->getValue('employee_id');
                        $row->job_status = $form->getValue('job_status');
                        $row->comments = $form->getValue('comments');
                        $row->recent_record = 1;
                        $row->approved_by = $form->getValue('approved_by');
                        $row->effective_date = date('Y-m-d', strtotime($form->getValue('effective_date')));
                        $column = array(
                            "recent_record" => "0"
                        );
                        $previousStatusRow = $esjsModel->fetchRow("employee_id='" . $form->getValue('employee_id') . "' and recent_record='1'");
                        //Updating quota if required
                        /*if (($form->getValue('job_status') != $previousStatusRow->job_status)
                                || ($form->getValue('effective_date') != $previousStatusRow->effective_date)) {
                            $quotaModel = new Application_Model_LeaveQuota();
                            $employeeQuotaRow = $quotaModel->fetchRow("employee_id='" . $form->getValue('employee_id') . "'");
                            if ($employeeQuotaRow) {
                                if ($form->getValue('job_status') == 'Permanent') {
                                    $leave = $quotaModel->calculateRemainingYearLeave($form->getValue('effective_date'));
                                    $employeeQuotaRow->sick_leave = $leave['Sick'];
                                    //$employeeQuotaRow->sick_leave_availed = null;
                                    $employeeQuotaRow->annual_leave = $leave['annual'];
                                    //$employeeQuotaRow->annual_leave_availed = null;
                                } elseif ($form->getValue('job_status') == 'Internship') {
                                    $employeeQuotaRow->sick_leave = 0;
                                    //$employeeQuotaRow->sick_leave_availed = null;
                                    $employeeQuotaRow->annual_leave = 0;
                                    //$employeeQuotaRow->annual_leave_availed = null;
                                } elseif ($form->getValue('job_status') == 'Probation') {
                                    $employeeQuotaRow->sick_leave = 2;
                                    //$employeeQuotaRow->sick_leave_availed = null;
                                    $employeeQuotaRow->annual_leave = 0;
                                    //$employeeQuotaRow->annual_leave_availed = null;
                                }
                            } else {
                                $employeeQuotaRow = $quotaModel->createRow();
                                $employeeQuotaRow->sick_leave = 0;
                                //$employeeQuotaRow->sick_leave_availed = null;
                                $employeeQuotaRow->annual_leave = 0;
                                //$employeeQuotaRow->annual_leave_availed = null;
                            }
                            $employeeQuotaRow->employee_id = $form->getValue('employee_id');
                            $employeeQuotaRow->save();
                        }*/
                        //Updating latest record to 0 flag
                        $esjsModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and recent_record='1'");

						if($form->getValue('job_status')=='Resigned' || $form->getValue('job_status')=='Terminated' ){
							$userModel = new Application_Model_User();
							$column = array(
								"status" => "Inactive"
							);
							$userModel->update($column, "employee_id='" . $form->getValue('employee_id')."'");
						}
                        $row->save();
                        
                        if($form->getValue('job_status')=='Resigned' || $form->getValue('job_status')=='Terminated'){
                            $employeeColumns = array(
                                "current_job_status" => $form->getValue('job_status'),
                                "status" => 'Inactive'
                            );
                        }else{
                            $employeeColumns = array(
                                "current_job_status" => $form->getValue('job_status')
                            );
                        }
                        
                        $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");

                        $this->_redirect('company-information/job-status/emplId/'
                                . $this->_request->emplId . '/'
                                . $this->view->actionType . '/s'
                        );
                    } else {
                        $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    if (is_array($DataForForm))
                        $form->populate($DataForForm);
                }
            }
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusJobStatus();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllJobStatus($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllJobStatus('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }

        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from employment_status_job_status where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }

        $this->view->paginator = $paginator;
    }

    public function jobStatusEditAction() {

        $empid = $this->_request->emplId;
        $id = $this->_request->id;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);



        $this->view->showLeftSubMenu = true;

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Employee Job Status Info Added Successfully');
        } else if ($this->_request->delete == 's') {
            $messages[] = array('success', 'Employee Job Status Info Deleted Successfully');
        }
        $this->view->messages = $messages;

        $statusArray = array(
            'Internship' => 'Internship',
            'Permanent' => 'Permanent',
            'Probation' => 'Probation',
            'Resigned' => 'Resigned',
            'Terminated' => 'Terminated'
        );

        $emplModel = new Application_Model_Employees();
        $managerList = $emplModel->getManagers();

        $fields = array(
            'esjs.job_status' => array(
                'title' => 'Job Status',
                'type' => 'dropdown',
                'value' => $statusArray,
                'default' => 'always'
            ),
            'effective_date' => array(
                'title' => 'Effective Date',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'comments' => array(
                'title' => 'Comments',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
			'employee_exit' => array(
                'title' => 'Employee Exit',
                'type' => 'dropdown',
                'value' => '',
                'default' => 'yes'
            ),
            'em.name' => array(
                'title' => 'Approved By',
                'type' => 'dropdown',
                'value' => $managerList,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'esjs.created_date' => array(
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
            'esjs.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );


        $sql_formatted_values = '';


        if ($this->_request->emplId != '') {
            unset($fields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = "and esjs.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'update';


            $esjsModel = new Application_Model_EmploymentStatusJobStatus();
            $DataForForm = '';

            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                if ($this->view->actionType == 'update') {
                    $esjsSelect = $esjsModel->select()
                                    ->where("employee_id=" . $this->_request->emplId . " and id = " . $id)
                                    ->order("modified_date DESC")->limit(1);
                    $esjsRow = $esjsModel->fetchRow($esjsSelect);
                    if ($esjsRow) {
                        $DataForForm = $esjsRow->toArray();
                        $form = new Application_Form_EmploymentStatusJobStatus(0, $this->_request->emplId);
                        $form->populate($DataForForm);
                    } else {
                        $form = new Application_Form_EmploymentStatusJobStatus(0, $this->_request->emplId);
                    }
                }

                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Employee Job Status";

                if ($this->_request->isPost()
                        && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    if ($form->isValid($formData)) {
                        $column = array(
                            "employee_id" => $form->getValue('employee_id'),
                            "job_status" => $form->getValue('job_status'),
                            "comments" => $form->getValue('comments'),
                            "approved_by" => $form->getValue('approved_by'),
							"employee_exit" => $form->getValue('employee_exit'),
                            "effective_date" => date('Y-m-d', strtotime($form->getValue('effective_date')))
                        );
                        $previousStatusRow = $esjsModel->fetchRow("employee_id='" . $form->getValue('employee_id') . "'  and id = " . $id);
                        //Updating quota if required
                        /*if (($form->getValue('job_status') != $previousStatusRow->job_status)
                                || ($form->getValue('effective_date') != $previousStatusRow->effective_date)) {
                            $quotaModel = new Application_Model_LeaveQuota();
                            $employeeQuotaRow = $quotaModel->fetchRow("employee_id='" . $form->getValue('employee_id') . "'");
                            if ($employeeQuotaRow) {
                                if ($form->getValue('job_status') == 'Permanent') {
                                    $leave = $quotaModel->calculateRemainingYearLeave($form->getValue('effective_date'));
                                    $employeeQuotaRow->sick_leave = $leave['Sick'];
                                    //$employeeQuotaRow->sick_leave_availed = null;
                                    $employeeQuotaRow->annual_leave = $leave['annual'];
                                    //$employeeQuotaRow->annual_leave_availed = null;
                                } elseif ($form->getValue('job_status') == 'Internship') {
                                    $employeeQuotaRow->sick_leave = 0;
                                    //$employeeQuotaRow->sick_leave_availed = null;
                                    $employeeQuotaRow->annual_leave = 0;
                                    //$employeeQuotaRow->annual_leave_availed = null;
                                } elseif ($form->getValue('job_status') == 'Probation') {
                                    $employeeQuotaRow->sick_leave = 2;
                                    //$employeeQuotaRow->sick_leave_availed = null;
                                    $employeeQuotaRow->annual_leave = 0;
                                    //$employeeQuotaRow->annual_leave_availed = null;
                                }
                            } else {
                                $employeeQuotaRow = $quotaModel->createRow();
                                $employeeQuotaRow->sick_leave = 0;
                                //$employeeQuotaRow->sick_leave_availed = null;
                                $employeeQuotaRow->annual_leave = 0;
                                //$employeeQuotaRow->annual_leave_availed = null;
                            }
                            $employeeQuotaRow->employee_id = $form->getValue('employee_id');
                            $employeeQuotaRow->save();
                        }*/
                        //Updating latest record to 0 flag
                        $esjsModel->update($column, "employee_id='" . $form->getValue('employee_id') . "' and id = " . $id);

                        $esjsGet = $esjsModel->fetchRow("employee_id='" . $form->getValue('employee_id') . "' and recent_record = '1' ");
                        if( $esjsGet->job_status=='Terminated' || $esjsGet->job_status=='Resigned' ){
                            $employeeColumns = array(
                                "current_job_status" => $esjsGet->job_status,
                                "status" => 'Inactive'
                            );
                        }else{
                            $employeeColumns = array(
                                "current_job_status" => $esjsGet->job_status
                            );
                        }
                        
                        $emplModel->update($employeeColumns, "id='" . $form->getValue('employee_id') . "'");

                        $this->_redirect('company-information/job-status/emplId/'
                                . $this->_request->emplId . '/'
                                . $this->view->actionType . '/s'
                        );
                    } else {
                        $formData['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($formData['effective_date']));
                        $form->populate($formData);
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    if (is_array($DataForForm))
                        $form->populate($DataForForm);
                }
            }
        }else
            $this->view->actionType = 'general';


        $this->view->defaultFields = $fields;
        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCInfo = new Application_Model_EmploymentStatusJobStatus();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCInfo->selectAllJobStatus($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCInfo->selectAllJobStatus('', $sql_formatted_values);
        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {
            if ($page['effective_date'])
                $page['effective_date'] = date(Zend_Registry::getInstance()->get('DATE'), strtotime($page['effective_date']));
        }

        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from employment_status_job_status where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }

        $this->view->paginator = $paginator;
    }

    public function selfWorkSpaceAction() {
        if (isset($this->_request->emplId))
            $empid = $this->_request->emplId;
        else if (isset($this->_request->id))
            $empid = $this->_request->id;

        $storage = new Zend_Auth_Storage_Session();
        $sessionEmpId = $storage->read()->employee_id;

        if ($sessionEmpId != $empid) {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
        $this->workspaceAction();
    }

    public function selfDesignationAction() {
        if (isset($this->_request->emplId))
            $empid = $this->_request->emplId;
        else if (isset($this->_request->id))
            $empid = $this->_request->id;

        $storage = new Zend_Auth_Storage_Session();
        $sessionEmpId = $storage->read()->employee_id;

        if ($sessionEmpId != $empid) {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
        $this->designationAction();
    }

    public function selfDepartmentAction() {
        if (isset($this->_request->emplId))
            $empid = $this->_request->emplId;
        else if (isset($this->_request->id))
            $empid = $this->_request->id;

        $storage = new Zend_Auth_Storage_Session();
        $sessionEmpId = $storage->read()->employee_id;

        if ($sessionEmpId != $empid) {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
        $this->departmentAction();
    }

    public function selfJobStatusAction() {
        if (isset($this->_request->emplId))
            $empid = $this->_request->emplId;
        else if (isset($this->_request->id))
            $empid = $this->_request->id;

        $storage = new Zend_Auth_Storage_Session();
        $sessionEmpId = $storage->read()->employee_id;

        if ($sessionEmpId != $empid) {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
        $this->jobStatusAction();
    }
    
    public function departmentNotesDeleteAction(){
        $this->_helper->layout->disableLayout();
        $esdnModel = new Application_Model_EmploymentStatusDepartmentNotes();
        $this->view->actionType = 'del';
        $id = $this->_request->getParam('noteId');
        $empId = $this->_request->getParam('emplId');
        $this->view->dptId = $this->_request->id;
        if($esdnModel->delete("id='".$id."'"))
            $this->_redirect('company-information/department-notes/emplId/'
                                . $empId . '/id/'.$this->view->dptId.'/'
                                . $this->view->actionType . '/d'
                        );
    }
    
    public function ajaxDepartmentEditNotesAction(){
        $this->_helper->layout->disableLayout();
        $esdnModel = new Application_Model_EmploymentStatusDepartmentNotes();
        $created_date = date('Y-m-d H:i:s');
        $storage = new Zend_Auth_Storage_Session();
        $created_by = $storage->read()->id;
        $notes = $this->_request->getParam('note');
        $id = $this->_request->getParam('noteId');
        if($esdnModel->update(array('notes'=>$notes,"created_by"=>$created_by,"created_date"=>$created_date),"id='".$id."'"))
            echo 'true';
        else
            echo false;
//	die();
    }
        
}