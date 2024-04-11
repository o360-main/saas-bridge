<?php

namespace O360Main\SaasBridge;

enum ModuleAction: string
{
    case import_all = 'import_all';
    case import_only = 'import_only';
    case import_by_date = 'import_by_date';
}
