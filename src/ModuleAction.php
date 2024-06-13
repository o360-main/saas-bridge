<?php

namespace O360Main\SaasBridge;

enum ModuleAction: string
{
    case import_all = 'import_all';
    case import = 'import';
    case import_periodic_updates = 'import_periodic_updates';

    case export_all = 'export_all';
    case export = 'export';
    case export_periodic_updates = 'export_periodic_updates';
}
