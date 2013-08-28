<?php

NF_Persistence::mapTable('Data_User', 'users', 'id');
NF_Persistence::mapFields('Data_User', array(
    'id'       => 'u_id',
    'username' => 'u_username',
    'fullname' => 'u_fullname',
    'email'    => 'u_email',
    'password' => 'u_password'
));

/**
 * Class that contains the "users" table.
 */
class Data_User
{
    /** @persist-type int    */ public $id;
    /** @persist-type string */ public $username;
    /** @persist-type string */ public $fullname;
    /** @persist-type string */ public $email;
    /** @persist-type string */ public $password;   // Always encrypted
}
