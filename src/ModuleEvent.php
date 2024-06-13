<?php

namespace O360Main\SaasBridge;

enum ModuleEvent: string
{
    case created = 'created';
    case updated = 'updated';
    case deleted = 'deleted';
    //    case RESTORED = 'restored';

}
