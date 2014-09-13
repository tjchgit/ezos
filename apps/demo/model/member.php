<?php
class demo_mdl_member extends model{
    protected $tableName = 'web_user';
    public function getAllUser(){
        return $this->select();
    }
    public function userMakeCall($data){
        list($val1, $val2) = array_values($data);
        return $val1 == $val2;
    }
    public function insertOneUser(){
        $this->setProperty("_validate", $this->validate());
        if(!$this->create()){
            die($this->getError());
        }

        P("已经到了这里，可以继续执行");
    }

    public function validate(){
        $json = '[
                    ["username","require","用户名必须填写",1],
                    ["password","require","密码必须填写",1],
                    ["rpassword","require","重复密码必须填写",1],
                    ["password","rpassword","两次密码不一致",1,"confirm"],
                    ["password,dpassword","userMakeFunc","两次密码一致",1,"function",3],
                    ["password,dpassword","userMakeCall","错误提示信息",1,"callback",3]
                ]';
        $_validate = json_decode($json, true);
        return $_validate;
    }
}