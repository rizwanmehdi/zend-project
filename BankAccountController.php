<?php

class BankAccountController extends Zend_Controller_Action {

    protected $arrSettings = null;
    protected $controller = null;

    /**
     * ROLE
     * @index Bank Account Management
     *
     *
     */
    public function indexAction() {


        $empid = $this->_request->emplId;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);

        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Bank Account Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Bank Account Updated Successfully');
        }

        $this->view->messages = $messages;

        $employeeList = new Application_Model_Employees();
        $employeeData = $employeeList->getTheEmployees('name');

        $empListData = array();
        foreach ($employeeData as $empValue) {
            $empListData[$empValue['key']] = $empValue['value'];
        }

        $user = new Application_Model_User();
        $userList = $user->getUsers('name');
//        $userListData = array();
//        foreach($userList as $userData) {
//            $userListData[$userData['key']] = $userData['value'];
//        }

        $this->view->title = "Bank Accounts";

        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->defaultFields = array(
            'e.name' => array(
                'title' => 'Employee Name',
                'type' => 'dropdown',
                'value' => $empListData,
                'default' => 'always'
            ),
            'account_title' => array(
                'title' => 'Account Title',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'account_number' => array(
                'title' => 'Account Number',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            )
//            ,
//            'bn.name' => array(
//                'title' => 'Branch Name',
//                'type' => 'textbox',
//                'value' => '',
//                'default' => 'no'
//            )
            ,
            'b.name' => array(
                'title' => 'Bank Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'ba.status' => array('title' => 'Status',
                'type' => 'dropdown',
                'value' => array(
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ),
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created By ',
                'type' => 'dropdown',
                'value' => $userList,
                'default' => 'no'
            ),
            'ba.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified By ',
                'type' => 'dropdown',
                'value' => $userList,
                'default' => 'no'
            ),
            'ba.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );
        $sql_formatted_values = '';
        if ($this->_request->emplId != '') {
            unset($this->view->defaultFields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = " and ba.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');
            $this->view->actionType = 'listing';
        } else {
            $this->view->actionType = 'general';
        }
        $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];

        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->arrSettings = $this->arrSettings;
        $bank_account = new Application_Model_BankAccountMapper();
        $records = $bank_account->fetchAll($this->arrSettings["sort"], $sql_formatted_values);
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
                $checkAdmin = $db->fetchAll("select modified_by from bank_account where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @index Bank Account Add
     *
     *
     */
    public function bankAddAction() {

        $empid = $this->_request->emplId;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);


        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Bank Account Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Bank Account Updated Successfully');
        }

        $this->view->messages = $messages;

        $employeeList = new Application_Model_Employees();
        $employeeData = $employeeList->getTheEmployees('name');

        $empListData = array();
        foreach ($employeeData as $empValue) {
            $empListData[$empValue['key']] = $empValue['value'];
        }

        $user = new Application_Model_User();
        $userList = $user->getUsers('name');
//        $userListData = array();
//        foreach($userList as $userData) {
//            $userListData[$userData['key']] = $userData['value'];
//        }

        $this->view->title = "Bank Accounts";

        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->defaultFields = array(
            'e.name' => array(
                'title' => 'Employee Name',
                'type' => 'dropdown',
                'value' => $empListData,
                'default' => 'always'
            ),
            'account_title' => array(
                'title' => 'Account Title',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'account_number' => array(
                'title' => 'Account Number',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            )
//            ,
//            'bn.name' => array(
//                'title' => 'Branch Name',
//                'type' => 'textbox',
//                'value' => '',
//                'default' => 'no'
//            )
            ,
            'b.name' => array(
                'title' => 'Bank Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'ba.status' => array('title' => 'Status',
                'type' => 'dropdown',
                'value' => array(
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ),
                'default' => 'no'
            ),
            'emp1.name' => array(
                'title' => 'Created By ',
                'type' => 'dropdown',
                'value' => $userList,
                'default' => 'no'
            ),
            'ba.created_date' => array(
                'title' => 'Created Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'emp2.name' => array(
                'title' => 'Modified By ',
                'type' => 'dropdown',
                'value' => $userList,
                'default' => 'no'
            ),
            'ba.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );
        $sql_formatted_values = '';
        if ($this->_request->emplId != '') {
            unset($this->view->defaultFields['e.name']);
            $this->view->showEmployeeLeftMenu = true;
            $sql_formatted_values = " and ba.employee_id='{$this->_request->emplId}'";
            $this->view->id = $this->getRequest()->getParam('emplId');

            $this->view->actionType = 'add';

            if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {

                $this->view->inc_validator = true;
                $this->view->inc_calander = true;
                $this->view->inc_tooltip = true;
                $mapper = new Application_Model_BankAccountMapper();

                $form = new Application_Form_BankAccount(
                                '',
                                $this->getRequest()->getParam('id'),
                                $this->getRequest()->getParam('emplId'),
                                $this->view->actionType);
                $empModel = new Application_Model_Employees();
                $empRow = $empModel->fetchRow("id='" . $this->_request->emplId . "'");
                $form->getElement('account_title')->setValue($empRow->name);
                
                $form->setAttrib('class', 'form-horizontal clearfix');
                $this->view->form = $form;
                $this->view->title = ucfirst($this->view->actionType) . " Bank Account Record";
                if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                        && !isset($this->_request->removeallFilter)
                        && !isset($this->_request->search_table_value)) {

                    $formData = $this->_request->getPost();
                    $this->view->bank_name = $formData["bank_name"];
                    $this->view->bank_branch_name = $formData["bank_branch_name"];
                    if ($form->isValid($formData)) {
                        $data = new Application_Model_BankAccount($form->getValues());
                        if (!$mapper->CheckBankDetailAlreadyExist($data)) {
                            $status = $mapper->save($data);
                            $this->_redirect('bank-account/index/emplId/' . $this->_request->emplId . '/' . $this->view->actionType . '/s');
                        }
                        else
                            $this->view->errorMessages = array(array('Bank detail' => 'Duplicate Bank Account Number')); /* <br \><br \>*&nbsp;&nbsp;Account Number<br \>*&nbsp;&nbsp;Bank Branch Name<br \>*&nbsp;&nbsp;Bank Name')); */
                    }
                    else {

                        $form->populate($formData);
                        //$this->view->bank_name = $formData["bank_name"];
                        $this->view->errorMessages = $form->getMessages();
                    }
                } else {
                    $id = (int) $this->_request->getParam('id', 0);
                    if ($id > 0) {
                        $form->populate($bankAccData);
                        $this->view->form = $form;
                    }
                }
            }
        } else {
            $this->view->actionType = 'general';
        }
        $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values .= $search_paramter['sql_formatted_values'];

        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->arrSettings = $this->arrSettings;
        $bank_account = new Application_Model_BankAccountMapper();
        $records = $bank_account->fetchAll($this->arrSettings["sort"], $sql_formatted_values);
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
                $checkAdmin = $db->fetchAll("select modified_by from bank_account where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @index Bank Account Edit
     *
     *
     */
    public function bankEditAction() {
        // action body

        $empid = $this->_request->emplId;

        $front = Zend_Controller_Front::getInstance();
        $Plugin = $front->getPlugin('Application_Controller_Plugin_Acl2');
        $Plugin->verifyAccess($empid);


        $messages = array();

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'Bank Account Added Successfully');
        } else if ($this->_request->update == 's') {
            $messages[] = array('success', 'Bank Account Updated Successfully');
        }
        $id = $this->getRequest()->getParam('id');
        if ($id != 0) {
            $this->view->messages = $messages;

            $employeeList = new Application_Model_Employees();
            $employeeData = $employeeList->getTheEmployees('name');

            $empListData = array();
            foreach ($employeeData as $empValue) {
                $empListData[$empValue['key']] = $empValue['value'];
            }

            $user = new Application_Model_User();
            $userList = $user->getUsers('name');
//        $userListData = array();
//        foreach($userList as $userData) {
//            $userListData[$userData['key']] = $userData['value'];
//        }

            $this->view->title = "Bank Accounts";

            $this->view->controller = $this->getRequest()->getControllerName();
            $this->view->defaultFields = array(
                'e.name' => array(
                    'title' => 'Employee Name',
                    'type' => 'dropdown',
                    'value' => $empListData,
                    'default' => 'always'
                ),
                'account_title' => array(
                    'title' => 'Account Title',
                    'type' => 'textbox',
                    'value' => '',
                    'default' => 'always'
                ),
                'account_number' => array(
                    'title' => 'Account Number',
                    'type' => 'textbox',
                    'value' => '',
                    'default' => 'yes'
                )
//            ,
//            'bn.name' => array(
//                'title' => 'Branch Name',
//                'type' => 'textbox',
//                'value' => '',
//                'default' => 'no'
//            )
                ,
                'b.name' => array(
                    'title' => 'Bank Name',
                    'type' => 'textbox',
                    'value' => '',
                    'default' => 'yes'
                ),
                'ba.status' => array('title' => 'Status',
                    'type' => 'dropdown',
                    'value' => array(
                        'Active' => 'Active',
                        'Inactive' => 'Inactive'
                    ),
                    'default' => 'no'
                ),
                'emp1.name' => array(
                    'title' => 'Created By ',
                    'type' => 'dropdown',
                    'value' => $userList,
                    'default' => 'no'
                ),
                'ba.created_date' => array(
                    'title' => 'Created date',
                    'type' => 'textbox',
                    'value' => '',
                    'default' => 'no'
                ),
                'emp2.name' => array(
                    'title' => 'Modified By ',
                    'type' => 'dropdown',
                    'value' => $userList,
                    'default' => 'no'
                ),
                'ba.modified_date' => array(
                    'title' => 'Modified Date',
                    'type' => 'textbox',
                    'value' => '',
                    'default' => 'no'
                )
            );
            $sql_formatted_values = '';
            if ($this->_request->emplId != '') {
                unset($this->view->defaultFields['e.name']);
                $this->view->showEmployeeLeftMenu = true;
                $sql_formatted_values = " and ba.employee_id='{$this->_request->emplId}'";
                $this->view->id = $this->getRequest()->getParam('emplId');

                $this->view->actionType = 'update';

                if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
                    $this->view->inc_validator = true;
                    $this->view->inc_calander = true;
                    $this->view->inc_tooltip = true;
                    $mapper = new Application_Model_BankAccountMapper();

                    $bankAccModel = new Application_Model_DbTable_BankAccount();
                    $bankAccRow = $bankAccModel->fetchRow('id=' . $this->getRequest()->getParam('id'));
                    if ($bankAccRow['account_number'] == "")
                        $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
                    $bankAccData = $bankAccRow->toArray();
                    $form = new Application_Form_BankAccount(
                                    $bankAccData['bank_name'],
                                    $this->getRequest()->getParam('id'),
                                    $this->getRequest()->getParam('emplId'),
                                    $this->view->actionType);
                    
                    $form->setAttrib('class', 'form-horizontal clearfix');
                    $this->view->form = $form;
                    $this->view->title = ucfirst($this->view->actionType) . " Bank Account Record";
                    if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                            && !isset($this->_request->removeallFilter)
                            && !isset($this->_request->search_table_value)) {

                        $formData = $this->_request->getPost();
                        $this->view->bank_name = $formData["bank_name"];
                        $this->view->bank_branch_name = $formData["bank_branch_name"];
                        if ($form->isValid($formData)) {
                            $data = new Application_Model_BankAccount($form->getValues());
                            if (!$mapper->CheckBankDetailAlreadyExist($data)) {
                                $status = $mapper->save($data);
                                $this->_redirect('bank-account/index/emplId/' . $this->_request->emplId . '/' . $this->view->actionType . '/s');
                            }
                            else
                                $this->view->errorMessages = array(array('Bank detail' => 'Duplicate Bank Account Number')); /* <br \><br \>*&nbsp;&nbsp;Account Number<br \>*&nbsp;&nbsp;Bank Branch Name<br \>*&nbsp;&nbsp;Bank Name')); */
                        }
                        else {
                            $form->populate($formData);
                            $this->view->errorMessages = $form->getMessages();
                        }
                    } else {
                        $id = (int) $this->_request->getParam('id', 0);
                        if ($id > 0) {
                            $bankAccData["status"] = strtolower($bankAccData["status"]);
                            $form->populate($bankAccData);
                            $this->view->form = $form;
                        }
                    }
                }
            } else {
                $this->view->actionType = 'general';
            }
            $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
            $search_paramter = $this->arrSettings["filters"];
            $posted_fields_string = $search_paramter['posted_values'];
            $sql_formatted_values .= $search_paramter['sql_formatted_values'];

            $this->view->posted_fields_string = $posted_fields_string;
            $this->view->arrSettings = $this->arrSettings;
            $bank_account = new Application_Model_BankAccountMapper();
            $records = $bank_account->fetchAll($this->arrSettings["sort"], $sql_formatted_values);
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
                    $checkAdmin = $db->fetchAll("select modified_by from bank_account where id=" . $page['id']);

                    if ($checkAdmin[0]['modified_by'] == '0') {

                        $page['emp2.name'] = 'Admin';
                    }
                }
            }
        } else {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
    }

    /**
     * ROLE
     * @findbranch Ajax Call for Bank Branch List
     *
     *
     */
    public function findBranchAction() {
        $this->_helper->layout->disableLayout();
        $id = (int) $this->_request->getParam('id');

        $branchModel = new Application_Model_Branch();
        $allBranches = $branchModel->fetchAll("bank_id='$id' AND status='Active'", "name ASC")->toArray();
        //Zend_Debug::dump($allDepartments);die;

        $output = '';
        if ($allBranches) {
            foreach ($allBranches as $branchRow) {
                $output .= ( $output == '') ? $branchRow['id'] . '|' . $branchRow['name'] : '#' . $branchRow['id'] . '|' . $branchRow['name'];
            }
        }


        echo $output;
        die('');
    }

    /**
     * ROLE
     * @findaccount Ajax Call for Bank Accounts List
     *
     *
     */
    public function findAccountAction() {
        $this->_helper->layout->disableLayout();
        $id = $this->_request->getParam('id');

        $bankAccount = new Application_Model_BankAccountMapper();
        $bankAccountSelect = $bankAccount->getDbTable()->select()
                ->where("employee_id='" . $id . "'")
                ->where("status='Active'");
        $allAccounts = $bankAccount->getDbTable()->fetchAll($bankAccountSelect);
//        foreach($allAccounts as $acc){
//            $desiCount = $desiCount+1;
//        }

        if (count($allAccounts) > 1) {
            echo 'yes';
        } else {
            echo 'no';
        }
        die('');
    }

    public function selfIndexAction() {
        if (isset($this->_request->emplId))
            $empid = $this->_request->emplId;
        else if (isset($this->_request->id))
            $empid = $this->_request->id;

        $storage = new Zend_Auth_Storage_Session();
        $sessionEmpId = $storage->read()->employee_id;

        if ($sessionEmpId != $empid) {
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        }
        $this->indexAction();
    }

}

