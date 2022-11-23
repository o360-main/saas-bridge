<?php

namespace O360Main\SaasBridge;

class SaasBridgeService
{
    // Build your next great package.

    public function agent(): SaasAgent
    {
        return app('saas-agent');
    }

}
