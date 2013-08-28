<?php

/**
 * Page that handles the user administration.
 */
class Admin_Users extends AdminPage
{
    /**
     * Show a user table.
     */
    public function executeView()
    {
        $users = NF::persist()->loadByWhereClause('Data_User', 'order by [fullname]');

        NF::response()->content = NF_Template::runDefault(null, array(
            'users' => $users
        ));
    }

    /**
     * Show an edit-user form. And handle the POST to save it.
     */
    public function executeEdit($id = null)
    {
        // We instantiate a FormHelper here to protect the POST call. We don't
        // really need it for the whole page. NF_FormHelper automatically detects
        // if it's a GET or POST call and takes the appropriate action.
        $iv = new NF_IV();

        // Edit old user or create new user?
        $user = $id ? NF::persist()->load('Data_User', $id) : new Data_User();

        if (NF::request()->isGet())
        {
            NF::response()->content = NF_Template::runDefault(null, array(
                'user' => $user,
                'form' => $iv->inject()
            ));
        }
        else if (NF::request()->isPost())
        {
            $user->fullname = trim(NF::request()->fullname);
            $user->username = trim(NF::request()->username);
            $user->email    = trim(NF::request()->email);

            // If we set a new password, use NF_Password::crypt() to encrypt it.
            // NF_Password will automatically select the best encryption available
            // on the system and salt it accordingly.
            if (trim(NF::request()->password))
                $user->password = NF_Password::crypt(trim(NF::request()->password));

            // We use $user->id to determine if it's a new user or old edited user.
            // If $user->id exists, it's old, and we use a save() call (MySQL UPDATE).
            // If $user->id is null, it's a new user, so we insert() (MySQL INSERT INTO).
            if ($user->id)
                NF::persist()->save($user);
            else
                $id = NF::persist()->insert($user);

            // Redirect to the "view" method. $this->ref() constructs a URL to the current
            // page. If the parameter is blank (null or ''), it refers to the current method,
            // but we don't want that, since we're done editing this user. So call
            // "view" instead (back to the user table).
            NF::response()->slowRedirect($this->ref('view'));
        }
    }

    // AJAX call for deleting users. We could protect this better.
    public function executeDelete($id)
    {
        $id = (int)$id;

        // We could use $Persistence->delete() or $Persistence->deleteObject() too,
        // but... meh.
        NF::db()->execute("delete from users where u_id=$id");
    }
}
