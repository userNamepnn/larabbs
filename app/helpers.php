<?php
/**
 * Created by PhpStorm.
 * User: Panninan
 * Date: 2019/1/12 0012
 * Time: 11:17
 */

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}
