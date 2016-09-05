<?php

/**
 * example of incorrect @global declaration #1
 * @author blahblah
 * @version -6
 *
 *
 *
 *
 */
class AclUserRoleController extends Zend_Controller_Action {

    /**
     * ROLE
     * @index Role Permissions Edit
     *
     *
     *
     *
     */
    public function indexAction() {
        $mdlAclUserRole = new Application_Model_AclUserRole();

        $role_id = $this->getRequest()->getParam('role_id');

        $rolesModel = new Application_Model_AclRole();
        $roleList = $rolesModel->selectRoles();
        $this->view->roleName = $roleList[$role_id]['name'];

        $controllersList = $this->getList($role_id);

        if ($this->getRequest()->isPost()) {
            //Zend_Debug::dump($_POST);
            foreach ($_POST as $item => $values) {
                $$item = $values;
                if (is_array($$item))
                    unset($$item);
            }

            $mdlAclUserRole->setDefaultPermissions($role_id);

            foreach ($_POST as $controller => $values) {
                if (is_array($values)) {
                    foreach ($values as $action => $value) {
                        //echo "<p>$item => $action => $value</p>";
                        $mdlAclUserRole->updateRolePermissions($role_id, $controller, $action);
                    }
                }
            }
            $this->_redirect('acl-user-role/roles/update/s');
        }

        $rolePermissions = $mdlAclUserRole->getRolePermissions($role_id);
        $this->view->rolePermissions = $rolePermissions;
        $this->view->controllersList = $controllersList;
    }

    protected function getList($role_id = 0) {
        $module_dir = Zend_Controller_Front::getInstance()->getControllerDirectory();
        $mdlAclUserRole = new Application_Model_AclUserRole();
        $rolesTitleArray = array();

        foreach ($module_dir as $dir => $dirpath) {
            $diritem = new DirectoryIterator($dirpath);
            foreach ($diritem as $item) {
                if ($item->isFile()) {
                    if (strstr($item->getFilename(), 'Controller.php') != FALSE) {
                        include_once $dirpath . '/' . $item->getFilename();
                        $controller = strtolower(str_replace('Controller.php', '', $item->getFilename()));
                        $source = file_get_contents($dirpath . '/' . $item->getFilename());
//                        $tokens = token_get_all($source);
                        $comment = array(
                            T_DOC_COMMENT, // All comments since PHP5      
                        );
//                        foreach ($tokens as $token) {
//                            if (in_array($token[0], $comment)) {
//                                $docBlock = $token[1];
//                                if (strstr($docBlock, 'ROLE')) {
//                                    $docBlock = trim(preg_replace('/\r?\n *\* */', ' ', $docBlock));
//                                    $matches = array();
//                                    preg_match_all('/@([a-z]+)\s+(.*?)(?=$|@[a-z])/', $docBlock, $matches);
//                                    $info = array_combine($matches[1], $matches[2]);
////                                  Zend_debug::dump($matches[2][0]);
//                                    $rolesTitleArray[$controller][$matches[1][0]] = $docBlock = trim(str_replace('/', ' ', $matches[2][0]));
//                                }
//                            }
//                        }
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
        return $controllerList;
    }

    /**
     * ROLE
     * @roles Roles List
     *
     *
     *
     *
     */
    public function rolesAction() {
        $messages = array();
        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;
        $this->view->defaultFields = array(
            'ar.name' => array(
                'title' => 'Role Title',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'description' => array(
                'title' => 'Role Description',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'ar.created_date' => array(
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
            'ar.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );

        if ($this->getRequest()->getParam('update') == 's') {
            $messages[] = array('success', 'Permissions Updated Successfully!');
        }

        $this->view->messages = $messages;

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
            $rolesModel = new Application_Model_AclRole();
            $form = new Application_Form_AclRoles(
                            $this->getRequest()->getParam('id'),
                            $this->getRequest()->getParam('emplId'),
                            $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Roles";
            $form->getElement('permissions')->setValue("None");
            $employeeRow = $rolesModel->fetchRow("id='" . $this->getRequest()->getParam('id') . "'");
            if (isset($employeeRow)) {

                if ($employeeRow->dependent_access == '1')
                    $form->getElement('permissions')->setValue("dependent");
                else if ($employeeRow->manager_access == '1')
                    $form->getElement('permissions')->setValue("manager");
                else {
                    $form->getElement('permissions')->setValue("None");
                }
                //   $form->getElement('role')=='employee';

                if ($employeeRow->role_hr == 'Active')
                    $form->getElement('role')->setValue("hr");
                else if ($employeeRow->role_employee == 'Active')
                    $form->getElement('role')->setValue("employee");
                else if ($employeeRow->role_admin == 'Active')
                    $form->getElement('role')->setValue("admin");
                else if ($employeeRow->role_manager == 'Active') {
                    $form->getElement('role')->setValue("manager");
                }
            }




            //  $form->getElement('role')->setValue('admin');

            if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                    && !isset($this->_request->removeallFilter)
                    && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {
                    if ($this->view->actionType == 'add') {
                        $row = $rolesModel->createRow();
                        $row->created_date = date('Y-m-d');
                        $storage = new Zend_Auth_Storage_Session();
                        $row->created_by = $storage->read()->id;
                    } else {
                        $id = (int) $this->getRequest()->getParam('id');
                        $row = $rolesModel->fetchRow('id=' . $id);
                        $storage = new Zend_Auth_Storage_Session();
                        $row->modified_by = $storage->read()->id;
                    }
                    $row->name = ucwords(strtolower($form->getValue('name')));



                    $fields = $form->getElement('permissions');
                    if ($fields->getValue('permissions') == 'dependent') {
                        $row->dependent_access = '1';
                        $row->manager_access = '0';
                    } else if ($fields->getValue('permissions') == 'manager') {
                        $row->manager_access = '1';
                        $row->dependent_access = '0';
                    } else {
                        $row->manager_access = '0';
                        $row->dependent_access = '0';
                    }

                    $fields = $form->getElement('role');
                    if ($fields->getValue('role') == 'hr') {
                        $row->role_hr = 'Active';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'admin') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Active';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'employee') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Active';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'manager') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Active';
                        $row->manager_access = '1';
                    }


                    //       $row->role_hr = $fields->getName();
                    //        $row->role_admin = ucwords(strtolower($fields->getMultiOption('admin')));
                    //       $row->role_employee = ucwords(strtolower($fields->getMultiOption('employee')));
                    //  $row->role_manager = ucwords(strtolower($form->getValue('role_manager')));
                    $row->description = $form->getValue('description');
                    //  $row->dependent_access = $form->getValue('dependent_access');
                    $row->save();

                    $this->_redirect('acl-user-role/roles/' . $this->view->actionType . '/s');
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $rolesModelData = $rolesModel->fetchRow('id=' . $id);
                    $form->populate($rolesModelData->toArray());
                }
            }
        }
        else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];

        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->inc_validator = true;
        $this->view->inc_calander = true;
        $this->view->inc_tooltip = true;
        $this->view->title = "Roles";

        $this->view->arrSettings = $this->arrSettings;

        $mdlRoles = new Application_Model_AclRole();

        if (isset($this->arrSettings["sort"])) {
            $adapter = $mdlRoles->getRoles($this->arrSettings["sort"], $sql_formatted_values);
        } else {
            $adapter = $mdlRoles->getRoles('', $sql_formatted_values);
        }

        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from acl_role where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }


        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @roles Roles Add
     *
     *
     *
     *
     */
    public function addRoleAction() {
        // action body
        $messages = array();
        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;
        $this->view->defaultFields = array(
            'ar.name' => array(
                'title' => 'Role Title',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'description' => array(
                'title' => 'Role Description',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'operational_head' => array(
                'title' => 'Operational Head',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'ar.created_date' => array(
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
            'ar.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );

        if ($this->getRequest()->getParam('update') == 's') {
            $messages[] = array('success', 'Permissions Updated Successfully!');
        }

        $this->view->messages = $messages;

        $sql_formatted_values = '';

        $this->view->actionType = 'add';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $rolesModel = new Application_Model_AclRole();
            $form = new Application_Form_AclRoles(
                            $this->getRequest()->getParam('id'),
                            $this->getRequest()->getParam('emplId'),
                            $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Roles";
            $form->getElement('permissions')->setValue("None");
            $employeeRow = $rolesModel->fetchRow("id='" . $this->getRequest()->getParam('id') . "'");
            if (isset($employeeRow)) {

                if ($employeeRow->dependent_access == '1')
                    $form->getElement('permissions')->setValue("dependent");
                else if ($employeeRow->manager_access == '1')
                    $form->getElement('permissions')->setValue("manager");
                else {
                    $form->getElement('permissions')->setValue("None");
                }
                //   $form->getElement('role')=='employee';

                if ($employeeRow->role_hr == 'Active')
                    $form->getElement('role')->setValue("hr");
                else if ($employeeRow->role_employee == 'Active')
                    $form->getElement('role')->setValue("employee");
                else if ($employeeRow->role_admin == 'Active')
                    $form->getElement('role')->setValue("admin");
                else if ($employeeRow->role_manager == 'Active') {
                    $form->getElement('role')->setValue("manager");
                }
            }




            //  $form->getElement('role')->setValue('admin');

            if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                    && !isset($this->_request->removeallFilter)
                    && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $rolesModel->createRow();
                    $row->created_date = date('Y-m-d');
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;
                    $row->operational_head = $form->getValue('operational_head');

                    $row->name = ucwords(strtolower($form->getValue('name')));



                    $fields = $form->getElement('permissions');
                    if ($fields->getValue('permissions') == 'dependent') {
                        $row->dependent_access = '1';
                        $row->manager_access = '0';
                    } else if ($fields->getValue('permissions') == 'manager') {
                        $row->manager_access = '1';
                        $row->dependent_access = '0';
                    } else {
                        $row->manager_access = '0';
                        $row->dependent_access = '0';
                    }

                    $fields = $form->getElement('role');
                    if ($fields->getValue('role') == 'hr') {
                        $row->role_hr = 'Active';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'admin') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Active';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'employee') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Active';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'manager') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Active';
                        $row->manager_access = '1';
                    }


                    //       $row->role_hr = $fields->getName();
                    //        $row->role_admin = ucwords(strtolower($fields->getMultiOption('admin')));
                    //       $row->role_employee = ucwords(strtolower($fields->getMultiOption('employee')));
                    //  $row->role_manager = ucwords(strtolower($form->getValue('role_manager')));
                    $row->description = $form->getValue('description');
                    //  $row->dependent_access = $form->getValue('dependent_access');
                    $row->save();

                    $this->_redirect('acl-user-role/roles/' . $this->view->actionType . '/s');
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $rolesModelData = $rolesModel->fetchRow('id=' . $id);
                    $form->populate($rolesModelData->toArray());
                }
            }
        }
        else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];

        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->inc_validator = true;
        $this->view->inc_calander = true;
        $this->view->inc_tooltip = true;
        $this->view->title = "Roles";

        $this->view->arrSettings = $this->arrSettings;

        $mdlRoles = new Application_Model_AclRole();

        if (isset($this->arrSettings["sort"])) {
            $adapter = $mdlRoles->getRoles($this->arrSettings["sort"], $sql_formatted_values);
        } else {
            $adapter = $mdlRoles->getRoles('', $sql_formatted_values);
        }

        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from acl_role where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }

        $this->view->paginator = $paginator;
    }

    /**
     * ROLE
     * @roles Roles Edit
     *
     *
     *
     *
     */
    public function editRoleAction() {
        // action body
        
        $rolesModel = new Application_Model_AclRole();
        
        $id = (int) $this->getRequest()->getParam('id');
        $rolesModelRecord = $rolesModel->fetchRow('id = ' . $id);
        if ($rolesModelRecord['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        
        $messages = array();
        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;
        $this->view->defaultFields = array(
            'ar.name' => array(
                'title' => 'Role Title',
                'type' => 'textbox',
                'value' => '',
                'default' => 'always'
            ),
            'description' => array(
                'title' => 'Role Description',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'operational_head' => array(
                'title' => 'Operational Head',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'ar.created_date' => array(
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
            'ar.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            )
        );

        if ($this->getRequest()->getParam('update') == 's') {
            $messages[] = array('success', 'Permissions Updated Successfully!');
        }

        $this->view->messages = $messages;

        $sql_formatted_values = '';


        $this->view->actionType = 'update';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $rolesModel = new Application_Model_AclRole();
            $form = new Application_Form_AclRoles(
                            $this->getRequest()->getParam('id'),
                            $this->getRequest()->getParam('emplId'),
                            $this->view->actionType
            );
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " Roles";
            $form->getElement('permissions')->setValue("None");
            $employeeRow = $rolesModel->fetchRow("id='" . $this->getRequest()->getParam('id') . "'");
            if (isset($employeeRow)) {

                if ($employeeRow->dependent_access == '1')
                    $form->getElement('permissions')->setValue("dependent");
                else if ($employeeRow->manager_access == '1')
                    $form->getElement('permissions')->setValue("manager");
                else {
                    $form->getElement('permissions')->setValue("None");
                }
                //   $form->getElement('role')=='employee';

                if ($employeeRow->role_hr == 'Active')
                    $form->getElement('role')->setValue("hr");
                else if ($employeeRow->role_employee == 'Active'){
                    $form->getElement('role')->setValue("employee");
                    $this->view->employeeRole = true;
                }else if ($employeeRow->role_admin == 'Active')
                    $form->getElement('role')->setValue("admin");
                else if ($employeeRow->role_manager == 'Active') {
                    $form->getElement('role')->setValue("manager");
                    $this->view->managerRole = true;
                }
            }




            //  $form->getElement('role')->setValue('admin');

            if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                    && !isset($this->_request->removeallFilter)
                    && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $id = (int) $this->getRequest()->getParam('id');
                    $row = $rolesModel->fetchRow('id=' . $id);
                    $storage = new Zend_Auth_Storage_Session();
                    $row->modified_by = $storage->read()->id;

                    $row->operational_head = $form->getValue('operational_head');

                    $row->name = ucwords(strtolower($form->getValue('name')));



                    $fields = $form->getElement('permissions');
                    if ($fields->getValue('permissions') == 'dependent') {
                        $row->dependent_access = '1';
                        $row->manager_access = '0';
                    } else if ($fields->getValue('permissions') == 'manager') {
                        $row->manager_access = '1';
                        $row->dependent_access = '0';
                    } else {
                        $row->manager_access = '0';
                        $row->dependent_access = '0';
                    }

                    $fields = $form->getElement('role');
                    if ($fields->getValue('role') == 'hr') {
                        $row->role_hr = 'Active';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'admin') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Active';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'employee') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Active';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Inactive';
                    } else if ($fields->getValue('role') == 'manager') {
                        $row->role_hr = 'Inactive';
                        $row->role_employee = 'Inactive';
                        $row->role_admin = 'Inactive';
                        $row->role_manager = 'Active';
                        $row->manager_access = '1';
                    }


                    //       $row->role_hr = $fields->getName();
                    //        $row->role_admin = ucwords(strtolower($fields->getMultiOption('admin')));
                    //       $row->role_employee = ucwords(strtolower($fields->getMultiOption('employee')));
                    //  $row->role_manager = ucwords(strtolower($form->getValue('role_manager')));
                    $row->description = $form->getValue('description');
                    //  $row->dependent_access = $form->getValue('dependent_access');
                    $row->save();

                    $this->_redirect('acl-user-role/roles/' . $this->view->actionType . '/s');
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $rolesModelData = $rolesModel->fetchRow('id=' . $id);
                    $form->populate($rolesModelData->toArray());
                }
            }
        }
        else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($this->view->defaultFields);
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];

        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->inc_validator = true;
        $this->view->inc_calander = true;
        $this->view->inc_tooltip = true;
        $this->view->title = "Roles";

        $this->view->arrSettings = $this->arrSettings;

        $mdlRoles = new Application_Model_AclRole();

        if (isset($this->arrSettings["sort"])) {
            $adapter = $mdlRoles->getRoles($this->arrSettings["sort"], $sql_formatted_values);
        } else {
            $adapter = $mdlRoles->getRoles('', $sql_formatted_values);
        }

        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);

        foreach ($paginator as $page) {

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from acl_role where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }


        $this->view->paginator = $paginator;
    }


}

