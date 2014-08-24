<?php
class demo_wid_demo extends widget{
    public function run($data){
        P($data);
        $vars = array(
            'title' => 'title',
            'content' => 'content',
            'other' => 'other',
            'usr'   => array(
                'usr1'=> 1,
                'usr2'=> 2,
                'usr3'=> 3,
            ),
        );
        return $this->renderFile('', $vars);
    }
}