<?php

/**
 * Front page for the administration functions. If we have an authenticated
 * session, forwards automatically to /admin/main. If we don't, show a login
 * form.
 */
class Admin extends NF_Page
{
    protected $formHelper;

    protected function init()
    {
        parent::init();

        $this->formHelper = new NF_FormHelper();
    }

    protected function error($errmsg)
    {
        global $Response;

        $Response->content = NF_Template::runDefault(null, array(
            'form'   => $this->formHelper->inject(),
            'errmsg' => $errmsg
        ));
    }

    /**
     * Check the submitted login info and create a session if it seems ok.
     *
     * @global NF_Request $Request
     * @global NF_Persistence $Persistence
     * @global NF_Response $Response
     * @global NF_Session $Session
     * @return void
     */
    protected function handleLogin()
    {
        global $Request, $Persistence, $Response, $Session;

        // Get the stuff from the URL
        $username = trim($Request->name);
        $password = trim($Request->password);

        // Check so we actually have data
        if (!$username || !$password)
            return $this->error(_t('Username or password was not given.'));

        // Load any matching users from the users table and get the first one.
        // There really shouldn't be more than one user with any given username,
        // but we don't check for that yet.
        $user = array_shift($Persistence->loadByFields('Data_User', array('username' => $username)));
        if (!$user)
            return $this->error(_t('Invalid username or password.'));

        // Compare the password against the stored password. NF_Password::compare
        // automatically applies the correct salt and checks password equality -
        // this is something that's easy to do wrong, so NF_Password does it for you.
        if (NF_Password::compare($password, $user->password) == false)
            return $this->error(_t('Invalid username or password.'));

        // We have a good login/password submission. Create an authenticated session.
        $Session->regenerate();                  // Always regenerate the session identifier.
        $Session->uid = $user->id;               // $Session->uid acts as the NF_AuthPage catchword,
                                                 //   to differentiate betweeen authenticated
                                                 //   sessions and normal sessions.
        $Session->fullname = $user->fullname;    // Set the name too for easier access.
        $Response->slowRedirect('/admin/main');  // Redirect
    }

    public function executeView()
    {
        global $Session, $Request, $Response;

        if ($Request->isPost())
        {
            $this->handleLogin();
            return;
        }

        // Check if we already have an authenticated session (user is logged in).
        if ($Session->uid == null)
            // Nope. Show the login form.
            $Response->content = NF_Template::runDefault(null, array(
                'form' => $this->formHelper->inject()
            ));
        else
            // Yep. Redirect to main. By the way - if the user ever calls
            // /admin/main either this way, or directly on the URL, without
            // being authenticated - it will throw a 304 Access Denied error.
            $Response->redirect('/admin/main');
    }

    public function executeLogout()
    {
        global $Session, $Response;

        $Session->end();
        $Response->slowRedirect('/');
    }
}
