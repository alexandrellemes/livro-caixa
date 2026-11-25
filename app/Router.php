<?php

namespace App;

final class Router
{
    public static function dispatch(): void
    {
        Routes::dispatch();
    }
}
