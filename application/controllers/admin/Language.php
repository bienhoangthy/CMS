<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Language extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->lang->load('language',$this->_data['language']);
    }

    public function index()
    {
        $this->mpermission->checkPermissionModule($this->uri->segment(2),$this->_data['user_active']['active_user_module']);
        $this->mpermission->checkPermission("language","index",$this->_data['user_active']['active_user_group']);
        $this->_data['formData'] = array(
            'lang_name' => '',
            'lang_code' => '',
            'lang_flag' => '',
            'lang_staus' => 1
        );
        if (isset($_POST['lang_name'])) {
            $this->mpermission->checkPermission("language","add",$this->_data['user_active']['active_user_group']);
            $this->_data['formData'] = array(
                'lang_name' => $this->input->post('lang_name'), 
                'lang_code' => $this->input->post('lang_code'), 
                'lang_flag' => '', 
                'lang_staus' => $this->input->post('lang_staus')
            );
            $error = false;
            $checkLangname = $this->mlanguage->getData('id', array('lang_name' => $this->_data['formData']['lang_name']));
            $checkLangcode = $this->mlanguage->getData('id', array('lang_code' => $this->_data['formData']['lang_code']));
            do {
                if ($this->_data['formData']['lang_name'] == null) {
                    $text = lang('pleaseinput').lang('language');$error = true;break;
                }
                if ($this->_data['formData']['lang_code'] == null) {
                    $text = lang('pleaseinput').lang('code');$error = true;break;
                }
                if (!empty($checkLangname)) {
                    $text = 'language'.lang('exists');$error = true;break;
                }
                if (!empty($checkLangcode)) {
                    $text = 'code'.lang('exists');$error = true;break;
                }
            } while (0);
            if ($error == true) {
                $notify = array(
                    'title' => lang('unsuccessful'), 
                    'text' => $text,
                    'type' => 'error'
                );
                $this->session->set_userdata('notify', $notify);
            } else {
                $flag_name = 'flag_'.$this->_data['formData']['lang_code'].'.png';
                $flag = $this->mlanguage->do_upload($flag_name);
                $this->_data['formData']['lang_flag'] = $flag ? $flag['file_name'] : '';
                $insert = $this->mlanguage->add($this->_data['formData']);
                if (is_numeric($insert) > 0) {
                    $notify = array(
                        'title' => lang('success'), 
                        'text' => $this->_data['formData']['lang_name'].lang('added'),
                        'type' => 'success'
                    );
                    $this->session->set_userdata('notify', $notify);
                    redirect(my_library::admin_site()."language");
                } else {
                    $notify = array(
                        'title' => lang('unsuccessful'),
                        'text' => lang('checkinfo'),
                        'type' => 'error'
                    );
                    $this->session->set_userdata('notify', $notify);
                    redirect(my_library::admin_site()."language");
                }
            }
        }
        $this->_data['action'] = 1;//Add
        $this->_data['title'] = lang('list');
        $this->_data['list'] = $this->mlanguage->getQuery("", "", "id asc","");
        $this->_data['record'] = $this->mlanguage->countQuery("");
        $this->_data['extraCss'] = ['iCheck/skins/flat/green.css'];
        $this->_data['extraJs'] = ['validator.js','module/language.js','icheck.min.js'];
        $this->_data['token_name'] = $this->security->get_csrf_token_name();
        $this->_data['token_value'] = $this->security->get_csrf_hash();
        $this->my_layout->view("admin/language/index", $this->_data);
    }

    public function edit($id)
    {
        $this->mpermission->checkPermissionModule($this->uri->segment(2),$this->_data['user_active']['active_user_module']);
        $this->mpermission->checkPermission("language","edit",$this->_data['user_active']['active_user_group']);
        if (is_numeric($id) && $id > 0) {
            $myLanguage = $this->mlanguage->getData("",array('id' => $id));
            if ($myLanguage && $myLanguage['id'] > 0) {
                $flag = $myLanguage['lang_flag'] != '' ? base_url().'media/language/'.$myLanguage['lang_flag'] : '';
                $this->_data['formData'] = array(
                    'lang_name' => $myLanguage['lang_name'],
                    'lang_code' => $myLanguage['lang_code'],
                    'lang_flag' => $flag,
                    'lang_staus' => $myLanguage['lang_staus']
                );
                if (isset($_POST['lang_name'])) {
                    $this->_data['formData'] = array(
                        'lang_name' => $this->input->post('lang_name'),
                        'lang_staus' => $this->input->post('lang_staus')
                    );
                    $error = false;
                    $checkLangname = $this->mlanguage->getData('id', array('lang_name' => $this->_data['formData']['lang_name']));
                    do {
                        if ($this->_data['formData']['lang_name'] == null) {
                            $text = lang('pleaseinput').lang('language');$error = true;break;
                        }
                        if (!empty($checkLangname) && $this->_data['formData']['lang_name'] != $myLanguage['lang_name']) {
                            $text = 'language'.lang('exists');$error = true;break;
                        }
                    } while (0);
                    if ($error == true) {
                        $notify = array(
                            'title' => lang('unsuccessful'), 
                            'text' => $text,
                            'type' => 'error'
                        );
                        $this->session->set_userdata('notify', $notify);
                    } else {
                        if (isset($_FILES['lang_flag']) && $_FILES['lang_flag']['name'] != "") {
                            $this->mlanguage->do_upload($myLanguage['lang_flag'],true);
                        }
                        if ($this->mlanguage->edit($id,$this->_data['formData'])) {
                            $notify = array(
                                'title' => lang('success'), 
                                'text' => $this->_data['formData']['lang_name'].lang('edited').' | '.lang('please').' Shift + F5!',
                                'type' => 'success'
                            );
                            $this->session->set_userdata('notify', $notify);
                            redirect(my_library::admin_site()."language");
                        } else {
                            $notify = array(
                                'title' => lang('unsuccessful'),
                                'text' => lang('checkinfo'),
                                'type' => 'error'
                            );
                            $this->session->set_userdata('notify', $notify);
                            redirect(my_library::admin_site()."language");
                        }
                    }
                }
                $this->_data['title'] = lang('editlang').' #'.$id;
                $this->_data['id'] = $id;
                $this->_data['list'] = $this->mlanguage->getQuery("", "", "id asc","");
                $this->_data['record'] = $this->mlanguage->countQuery("");
                $this->_data['extraCss'] = ['iCheck/skins/flat/green.css'];
                $this->_data['extraJs'] = ['validator.js','module/language.js','icheck.min.js'];
                $this->_data['token_name'] = $this->security->get_csrf_token_name();
                $this->_data['token_value'] = $this->security->get_csrf_hash();
                $this->my_layout->view("admin/language/index", $this->_data);
            } else {
                $notify = array(
                    'title' => lang('unsuccessful'), 
                    'text' => lang('language').lang('notexists'),
                    'type' => 'warning'
                );
                $this->session->set_userdata('notify', $notify);
                redirect(my_library::admin_site()."language");
            }
        } else {
            $notify = array(
                'title' => lang('notfound'), 
                'text' => lang('wrongid'),
                'type' => 'warning'
            );
            $this->session->set_userdata('notify', $notify);
            redirect(my_library::admin_site()."language");
        }   
    }

    // public function delete($id)
    // {
    //     $this->mpermission->checkPermissionModule($this->uri->segment(2),$this->_data['user_active']['active_user_module']);
    //     $this->mpermission->checkPermission("language","delete",$this->_data['user_active']['active_user_group']);
    //     if (is_numeric($id) && $id != 1) {
    //         $myUser = $this->muser->getData("",array('id' => $id));
    //         if ($myUser && $myUser['id'] > 0) {
    //             $this->muser->delete($id);
    //             $this->muser->delavatar($myUser['user_folder']);
    //             $title = lang('success');
    //             $text = $myUser['user_username'].lang('deleted');
    //             $type = 'success';
    //         } else {
    //             $title = lang('unsuccessful');
    //             $text = 'User'.lang('notexists');
    //             $type = 'error';
    //         } 
    //     } else {
    //         $title = lang('unsuccessful');
    //         $text = lang('wrongid');
    //         $type = 'error';
    //     }
    //     $notify = array(
    //         'title' => $title, 
    //         'text' => $text,
    //         'type' => $type
    //     );
    //     $this->session->set_userdata('notify', $notify);
    //     redirect(my_library::admin_site()."user");
    // }  

    public function setlanguage($language='vietnamese')
    {
        $redirect = $this->input->get('redirect');
        if ($language == 'vietnamese' || $language == 'english') {
        	$this->session->unset_userdata('language');
	        $this->session->set_userdata('language', $language);
            $this->cache->delete('module');
        }
        $redirect = $redirect != null ? base64_decode($redirect) : my_library::admin_site();
	    redirect($redirect);
    }
}

