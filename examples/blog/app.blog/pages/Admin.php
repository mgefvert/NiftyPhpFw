<?php

/**
 * Front page for the administration functions. If we have an authenticated
 * session, forwards automatically to /admin/main. If we don't, show a login
 * form.
 */
class Admin extends NF_Page
{
    protected $iv;

    protected function init()
    {
        parent::init();

        $this->iv = new NF_IV();
    }

    protected function error($errmsg)
    {
        NF::response()->content = NF_Template::runDefault(null, array(
            'form'   => $this->iv->inject(),
            'errmsg' => $errmsg
        ));
    }

    /**
     * Check the submitted login info and create a session if it seems ok.
     */
    protected function handleLogin()
    {
        // Get the stuff from the URL
        $username = trim(NF::request()->name);
        $password = trim(NF::request()->password);

        // Check so we actually have data
        if (!$username || !$password)
            return $this->error(_t('Username or password was not given.'));

        // Load any matching users from the users table and get the first one.
        // There really shouldn't be more than one user with any given username,
        // but we don't check for that yet.
        $user = array_shift(NF::persist()->loadByFields('Data_User', array('username' => $username)));
        if (!$user)
            return $this->error(_t('Invalid username or password.'));

        // Compare the password against the stored password. NF_Password::compare
        // automatically applies the correct salt and checks password equality -
        // this is something that's easy to do wrong, so NF_Password does it for you.
        if (NF_Password::compare($password, $user->password) == false)
            return $this->error(_t('Invalid username or password.'));

        // We have a good login/password submission. Create an authenticated session.
        NF::session()->regenerate();             // Always regenerate the session identifier.
        NF::session()->uid = $user->id;          // $Session->uid acts as the NF_AuthPage catchword,
                                                 //   to differentiate betweeen authenticated
                                                 //   sessions and normal sessions.
        NF::session()->fullname = $user->fullname;    // Set the name too for easier access.
        NF::response()->slowRedirect('/blog/admin/main');  // Redirect
    }

    public function executeView()
    {
        if (NF::request()->isPost())
        {
            $this->handleLogin();
            return;
        }

        // Check if we already have an authenticated session (user is logged in).
        if (NF::session()->uid == null)
            // Nope. Show the login form.
            NF::response ()->content = NF_Template::runDefault(null, array(
                'form' => $this->iv->inject()
            ));
        else
            // Yep. Redirect to main. By the way - if the user ever calls
            // /admin/main either this way, or directly on the URL, without
            // being authenticated - it will throw a 304 Access Denied error.
            NF::response()->redirect('/blog/admin/main');
    }

    public function executeLogout()
    {
        NF::session()->end();
        NF::response()->slowRedirect('/');
    }
}
