<?php

/**
 * This class extends the functionality of NF_AuthPage, which it turns requires
 * that a user has an authenticated session before the page can load. We use
 * this to make sure that we've logged in as a valid user. This allows us with
 * a very secure way of protecting our administration code - the page will
 * refuse to load if it doesn't find a valid login.
 *
 * AdminPage just adds to that by preloading the active user, so that's always
 * available.
 */
class AdminPage extends NF_AuthPage
{
    protected $user;

    protected function init()
    {
        parent::init();

        $this->user = NF::persist()->load('Data_User', NF::session()->uid);
    }
}
