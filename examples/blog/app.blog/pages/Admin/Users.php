<?php

/**
 * Page that handles the user administration.
 */
class Admin_Users extends AdminPage
{
    /**
     * Show a user table.
     *
     * @global NF_Response $Response
     * @global NF_Persistence $Persistence
     */
    public function executeView()
    {
        global $Response, $Persistence;

        $users = $Persistence->loadByWhereClause('Data_User', 'order by [fullname]');

        $Response->content = NF_Template::runDefault(null, array(
            'users' => $users
        ));
    }

    /**
     * Show an edit-user form. And handle the POST to save it.
     *
     * @global NF_Persistence $Persistence
     * @global NF_Response $Response
     * @global NF_Request $Request
     * @global NF_Session $Session
     * @param int $id
     */
    public function executeEdit($id = null)
    {
        global $Persistence, $Response, $Request, $Session;

        // We instantiate a FormHelper here to protect the POST call. We don't
        // really need it for the whole page. NF_FormHelper automatically detects
        // if it's a GET or POST call and takes the appropriate action.
        $formHelper = new NF_FormHelper();

        // Edit old user or create new user?
        $user = $id ? $Persistence->load('Data_User', $id) : new Data_User();

        if ($Request->isGet())
        {
            $Response->content = NF_Template::runDefault(null, array(
                'user' => $user,
                'form' => $formHelper->inject()
            ));
        }
        else if ($Request->isPost())
        {
            $user->fullname = trim($Request->fullname);
            $user->username = trim($Request->username);
            $user->email    = trim($Request->email);

            // If we set a new password, use NF_Password::crypt() to encrypt it.
            // NF_Password will automatically select the best encryption available
            // on the system and salt it accordingly.
            if (trim($Request->password))
                $user->password = NF_Password::crypt(trim($Request->password));

            // We use $user->id to determine if it's a new user or old edited user.
            // If $user->id exists, it's old, and we use a save() call (MySQL UPDATE).
            // If $user->id is null, it's a new user, so we insert() (MySQL INSERT INTO).
            if ($user->id)
                $Persistence->save($user);
            else
                $id = $Persistence->insert($user);

            // Redirect to the "view" method. $this->ref() constructs a URL to the current
            // page. If the parameter is blank (null or ''), it refers to the current method,
            // but we don't want that, since we're done editing this user. So call
            // "view" instead (back to the user table).
            $Response->slowRedirect($this->ref('view'));
        }
    }

    // AJAX call for deleting users. We could protect this better.
    public function executeDelete($id)
    {
        // Force $id to become an integer. NF_Filter is handy because it
        // can take several variables at once.
        NF_Filter::fint($id);

        // We could use $Persistence->delete() or $Persistence->deleteObject() too,
        // but... meh.
        NF_DB::connect()->execute("delete from users where u_id=$id");
    }
}
