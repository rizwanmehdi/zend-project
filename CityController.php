<?php

class CityController extends Zend_Controller_Action {

    protected $arrSettings, $controller;

    /**
     * ROLE
     * @index City Management
     */
    public function indexAction() {
        $messages = array();
        $model = new Application_Model_Country();
        $country = $model->allCountry('country');
        $country[''] = '';

        $model = new Application_Model_State();
        $states = $model->fetchAll();
        $stateSelect = array();
        foreach ($states as $state) {
            $stateSelect[$state->name] = $state->name;
        }
        $user_id = 5;
        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;

        if ($this->_request->add == 's') {
            $messages[] = array('success', 'City Added Successfully');
        }
        if ($this->_request->update == 's') {
            $messages[] = array('success', 'City Updated Successfully');
        }
        $this->view->messages = $messages;
        $fields = array(
            'ct.name' => array(
                'title' => 'City Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'ct.code' => array(
                'title' => 'Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'st.name' => array(
                'title' => 'State',
                'type' => 'dropdown',
                'value' => $stateSelect,
                'default' => 'yes'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => $country,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'ct.created_date' => array(
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
            'ct.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'ct.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => array(
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ),
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        if (!isset($_COOKIE[$this->controller]["fields"])) {
            $fieldnames = array_keys($this->view->defaultFields);

            foreach ($fieldnames as $item => $field)
                setcookie($this->controller . "[fields][$item]", $field, time() + 7200, '/');
        }


        $sql_formatted_values = '';

        $this->view->actionType = 'listing';



        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;
        
        $postedStrArr = json_decode($posted_fields_string, true);
        
        $countryFilter = array();
        foreach ($postedStrArr as $subarray) {
            if(isset($subarray['country_id'])){
                $countryFilter['operatorvalue'] = $subarray['country_id']['operatorvalue'];
                $countryFilter['posted_value'] = $subarray['country_id']['posted_value'];
            }
        }
        
        $mdlCity = new Application_Model_City();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCity->selectAllCities($this->arrSettings["sort"], $sql_formatted_values,$countryFilter);  
        else
            $records = $mdlCity->selectAllCities('', $sql_formatted_values,$countryFilter);

        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;
        $model = new Application_Model_Country();
        $countryList = $model->allCountry('country');
        foreach ($paginator as $page) {

            $page->country_id = $countryList[$page->country_id];

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from city where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @index City Add
     */
    public function addCityAction() {
        // action body
        $model = new Application_Model_Country();
        $country = $model->allCountry('country');
        $country[''] = '';

        $model = new Application_Model_State();
        $states = $model->fetchAll();
        $stateSelect = array();
        foreach ($states as $state) {
            $stateSelect[$state->name] = $state->name;
        }
        $user_id = 5;
        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;
        $fields = array(
            'ct.name' => array(
                'title' => 'City Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'ct.code' => array(
                'title' => 'Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'st.name' => array(
                'title' => 'State',
                'type' => 'dropdown',
                'value' => $stateSelect,
                'default' => 'yes'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => $country,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'ct.created_date' => array(
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
            'ct.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'ct.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => array(
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ),
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        if (!isset($_COOKIE[$this->controller]["fields"])) {
            $fieldnames = array_keys($this->view->defaultFields);

            foreach ($fieldnames as $item => $field)
                setcookie($this->controller . "[fields][$item]", $field, time() + 7200, '/');
        }


        $sql_formatted_values = '';


        $this->view->actionType = 'add';

        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $cityModel = new Application_Model_City();
            $form = new Application_Form_City();
            
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " City";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                    && !isset($this->_request->removeallFilter)
                    && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $row = $cityModel->createRow();
                    $row->created_date = date('Y-m-d');
                    $storage = new Zend_Auth_Storage_Session();
                    $row->created_by = $storage->read()->id;

                    $row->name = ucwords(strtolower($form->getValue('name')));
                    $row->code = $form->getValue('code');
                    $row->state_id = $form->getValue('state_id');
                    $row->country_id = $form->getValue('country_id');
                    $row->status = $form->getValue('status');
                    $recordExist = $cityModel->checkOnEditIfRecordExists($id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('city/index/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $cityModelData = $cityModel->fetchRow('id=' . $id);
                    $getData = $cityModelData->toArray();
                    $formEdit = new Application_Form_City(
                                    $getData['country_id'],
                                    $getData['state_id']
                    );
                    $formEdit->populate($getData);
                    $this->view->form = $formEdit;
                }
            }
        }
        else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCity = new Application_Model_City();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCity->selectAllCities($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCity->selectAllCities('', $sql_formatted_values);

        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;
        $model = new Application_Model_Country();
        $countryList = $model->allCountry('country');
        foreach ($paginator as $page) {

            $page->country_id = $countryList[$page->country_id];

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from city where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @index City Edit
     */
    public function editCityAction() {
        // action body
        $cityModel = new Application_Model_City();
        $id = (int) $this->getRequest()->getParam('id');
        $cityModelRecord = $cityModel->fetchRow('id = ' . $id);
        if ($cityModelRecord['id'] == "")
            $this->_redirect($this->getRequest()->getBaseUrl() . "/index/permission-denied");
        $model = new Application_Model_Country();
        $country = $model->allCountry('country');
        $country[''] = '';

        $model = new Application_Model_State();
        $states = $model->fetchAll();
        $stateSelect = array();
        foreach ($states as $state) {
            $stateSelect[$state->name] = $state->name;
        }
        $user_id = 5;
        $this->controller = $this->getRequest()->getControllerName();
        $this->view->controller = $this->controller;
        $fields = array(
            'ct.name' => array(
                'title' => 'City Name',
                'type' => 'textbox',
                'value' => '',
                'default' => 'yes'
            ),
            'ct.code' => array(
                'title' => 'Code',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'st.name' => array(
                'title' => 'State',
                'type' => 'dropdown',
                'value' => $stateSelect,
                'default' => 'yes'
            ),
            'country_id' => array(
                'title' => 'Country',
                'type' => 'textbox',
                'value' => $country,
                'default' => 'yes'
            ),
            'emp1.name' => array(
                'title' => 'Created by',
                'type' => 'textbox',
                'value' => '',
                'default' => 'no'
            ),
            'ct.created_date' => array(
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
            'ct.modified_date' => array(
                'title' => 'Modified Date',
                'type' => 'date',
                'value' => '',
                'default' => 'no'
            ),
            'ct.status' => array(
                'title' => 'Status',
                'type' => 'dropdown',
                'value' => array(
                    'Active' => 'Active',
                    'Inactive' => 'Inactive'
                ),
                'default' => 'no'
            ),
        );

        $this->view->defaultFields = $fields;

        if (!isset($_COOKIE[$this->controller]["fields"])) {
            $fieldnames = array_keys($this->view->defaultFields);

            foreach ($fieldnames as $item => $field)
                setcookie($this->controller . "[fields][$item]", $field, time() + 7200, '/');
        }


        $sql_formatted_values = '';


        $this->view->actionType = 'update';


        if ($this->view->actionType == 'add' or $this->view->actionType == 'update') {
            $this->view->inc_validator = true;
            $this->view->inc_calander = true;
            $cityModel = new Application_Model_City();
            $form = new Application_Form_City();
            
            $form->setAttrib('class', 'form-horizontal clearfix');
            $this->view->form = $form;
            $this->view->title = ucfirst($this->view->actionType) . " City";

            if ($this->_request->isPost() && !isset($this->_request->searchFilter)
                    && !isset($this->_request->removeallFilter)
                    && !isset($this->_request->search_table_value)) {

                $formData = $this->_request->getPost();
                if ($form->isValid($formData)) {

                    $id = (int) $this->getRequest()->getParam('id');
                    $row = $cityModel->fetchRow('id=' . $id);
                    $storage = new Zend_Auth_Storage_Session();
                    $row->modified_by = $storage->read()->id;
                    $row->name = ucwords(strtolower($form->getValue('name')));
                    $row->code = $form->getValue('code');
                    $row->state_id = $form->getValue('state_id');
                    $row->country_id = $form->getValue('country_id');
                    $row->status = $form->getValue('status');

                    $recordExist = $cityModel->checkOnEditIfRecordExists($id, ucwords(strtolower($form->getValue('name'))));
                    if ($recordExist) {
                        $this->view->errorMessages = array(
                            array('ErrorMessage' => 'The record already exists')
                        );
                    } else {
                        $row->save();
                        $this->_redirect('city/index/' . $this->view->actionType . '/s');
                    }
                } else {
                    $form->populate($formData);
                    $this->view->errorMessages = $form->getMessages();
                }
            } else {
                $id = (int) $this->_request->getParam('id', 0);
                if ($id > 0) {
                    $cityModelData = $cityModel->fetchRow('id=' . $id);
                    $getData = $cityModelData->toArray();
                    $formEdit = new Application_Form_City(
                                    $getData['country_id'],
                                    $getData['state_id']
                    );
                    $formEdit->populate($getData);
                    $formEdit->setAttrib('class', 'form-horizontal clearfix');
                    $this->view->form = $formEdit;
                }
            }
        }
        else
            $this->view->actionType = 'general';

        $this->arrSettings = $this->getHelper('Settings')->getParams($fields);
        $this->view->arrSettings = $this->arrSettings;
        $search_paramter = $this->arrSettings["filters"];
        $posted_fields_string = $search_paramter['posted_values'];
        $sql_formatted_values = $search_paramter['sql_formatted_values'];
        $this->view->posted_fields_string = $posted_fields_string;
        $this->view->fields_array = $fields;

        $mdlCity = new Application_Model_City();
        if (isset($this->arrSettings["sort"]))
            $records = $mdlCity->selectAllCities($this->arrSettings["sort"], $sql_formatted_values);
        else
            $records = $mdlCity->selectAllCities('', $sql_formatted_values);

        $paginator = new Zend_Paginator($records);
        $paginator->setItemCountPerPage($this->arrSettings["perpage"]);
        $paginator->setCurrentPageNumber($this->arrSettings["currentpage"]);
        $this->view->paginator = $paginator;
        $model = new Application_Model_Country();
        $countryList = $model->allCountry('country');
        foreach ($paginator as $page) {

            $page->country_id = $countryList[$page->country_id];

            // Name of Created by Employee
            if ($page['emp1.name'] == '') {
                $page['emp1.name'] = 'Administrator';
            }

            // Name of Modified by Employee
            if ($page['emp2.name'] == '') {

                $db = Zend_Db_Table::getDefaultAdapter();
                $checkAdmin = $db->fetchAll("select modified_by from city where id=" . $page['id']);

                if ($checkAdmin[0]['modified_by'] == '0') {

                    $page['emp2.name'] = 'Admin';
                }
            }
        }
    }

    /**
     * ROLE
     * @findcity Ajax Call for City List
     */
    public function findCityAction() {
        $this->_helper->layout->disableLayout();
        $id = (int) $this->_request->getParam('id');

        $city = new Application_Model_City();
        $citySelect = $city->select()->order("name asc")->where("state_id='$id'");
        $allCity = $city->fetchAll($citySelect);

        $output = '';
        if ($allCity) {
            foreach ($allCity as $cityRow) {
                $output .= ( $output == '') ? $cityRow['id'] . '|' . $cityRow['name'] : '#' . $cityRow['id'] . '|' . $cityRow['name'];
            }
        }

        echo $output;
        die('');
    }
    
    /**
     * Check the city name is exist or not in table.
     */
    public function checkCityNameExistAction() 
    {
       $this->_helper->layout()->disableLayout();
       $cityModel = new Application_Model_City();
       $cityName = $this->_request->getParam('cityName');
       $where = 'name like '. "'$cityName'";
       $results = $cityModel->fetchRow( $where);  
       if (count($results)) {
            echo 1;
       } else {
            echo 0;
       }
       die;
    }
}
