<?php

namespace Controllers;

use Mikrofraim\Controller\BaseController;
use Mikrofraim\Facades\Cache;
use Mikrofraim\Facades\Form;
use Mikrofraim\Facades\Log;
use Mikrofraim\Facades\Session;
use Mikrofraim\Facades\Syslog;
use Mikrofraim\Facades\View;

class HomeController extends BaseController
{
    public function index()
    {
        /* Render using the app container */
        return $this->app->view->render('home.html');

        /* Render using facade */
        return View::render('home.html');
    }
}
