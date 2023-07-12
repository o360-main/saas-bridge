<?php

namespace O360Main\SaasBridge\Contracts;

use Illuminate\Http\Request;

interface ControllerInterface
{
    //Routes : /api/{module}/config
    public function config(Request $request = null);

    //Routes : /api/{module}/data
    public function data(Request $request = null);

    //Routes : /api/{module}/import
    public function import(Request $request = null);

    //Routes : /api/{module}/export
    public function export(Request $request = null);

    public function trigger(Request $request = null);
}
