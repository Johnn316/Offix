<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Lang;

class LangController extends Controller
{
    public function switch(string $locale): void
    {
        Lang::setLocale($locale);
        // Redirect back to where user came from
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
}
