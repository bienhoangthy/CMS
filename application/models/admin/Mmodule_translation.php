<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class mmodule_translation extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    protected $table = "cms_module_translation";

    public function checkLanguage($module_id)
    {
        $listLanguage = $this->getQuery("language_code", "module_id = ".$module_id, "language_code desc", "");
        return $listLanguage;
    }
}
