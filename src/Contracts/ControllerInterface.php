<?php

namespace O360Main\SaasBridge\Contracts;

use Illuminate\Http\Request;

interface ControllerInterface
{
    public function config(Request $request = null);

    public function import(Request $request = null);

    public function export(Request $request = null);

    public function trigger(Request $request = null);
}
