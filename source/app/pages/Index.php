<?php

class Index extends NF_Page
{
    public function executeView()
    {
        NF::response()->content = NF_Template::runDefault();
    }
}
