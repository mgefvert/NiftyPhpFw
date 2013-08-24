<?php

class TestPage extends NF_Page
{
    protected function init()
    {
        global $testValue;
        
        parent::init();
        $testValue = 'i';
    }
    
    protected function finalize()
    {
        global $testValue;
        
        parent::finalize();
        $testValue .= 'f';
    }
    
    public function executeView()
    {
        global $testValue;
        
        $testValue .= 'V';
    }
    
    public function executeEdit($id)
    {
        global $testValue;
        
        $testValue .= "E{$id}";
    }
    
    public function executePostData($id, $var1, $var2 = 'x')
    {
        global $testValue;
        
        $testValue .= "P{$id}{$var1}{$var2}";
    }
    
    public function executeDefault()
    {
        global $testValue;
        
        $testValue .= 'd';
    }
}
