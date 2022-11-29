<?php

namespace O360Main\SaasBridge;

class PluginConfig
{
    protected string $version = "1.0.0";
    protected array $info = [];
    protected array $configs = [];
    protected array $removeConfigs = [];

    /**
     * @throws \Exception
     */
    public static function getFromFile()
    {
        $file = app_path('config.json');

        if (!file_exists($file)) {
            throw new \Exception('Config file not found');
        }

        $json = file_get_contents($file);

        return json_decode($json, true);
    }

    public function register($name, $description = null): PluginConfig
    {
        $this->info['name'] = $name;
        $this->info['description'] = $description;

        return $this;
    }


    public function setVersion($version): PluginConfig
    {
        $this->version = $version;
        return $this;
    }


    public function setOther($key, $value): PluginConfig
    {
        $this->info['other'][$key] = $value;

        return $this;
    }


    public function setConfig($type, $key, $label, $rules, $default = null): PluginConfig
    {
        $this->configs[$key] = [
            'key' => $key,
            'label' => $label,
            'rules' => $rules,
            'default' => $default,
            'type' => strtolower($type),
        ];
        return $this;
    }

    public function removeConfig($key): PluginConfig
    {
        $this->removeConfigs[] = $key;
        return $this;
    }

    public function getInfo(): array
    {
        $merge = [];
        $merge['add'] = $this->configs ?? [];
        $merge['remove'] = $this->removeConfigs ?? [];
        $this->info['options'] = $merge ?? [];
        $this->info['version'] = $this->version;
        return $this->info;
    }


}
