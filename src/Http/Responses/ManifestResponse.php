<?php

namespace O360Main\SaasBridge\Http\Responses;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use O360Main\SaasBridge\Http\Responses\Manifest\Image;
use O360Main\SaasBridge\Http\Responses\Manifest\ManifestConfig;
use O360Main\SaasBridge\Http\Responses\Manifest\ManifestCredentialForm;
use O360Main\SaasBridge\Http\Responses\Manifest\ManifestDeveloper;
use O360Main\SaasBridge\Http\Responses\Manifest\ManifestLogo;
use O360Main\SaasBridge\Http\Responses\Manifest\PluginType;

const DEFAULT_MANIFEST_VERSION = 1;

class ManifestResponse implements Arrayable, Responsable
{
    public const VERSION1 = 'v1';

    public const VERSION2 = 'v2';

    /**
     * @throws \Exception
     */
    public function __construct(
        public readonly string $name,
        public readonly string $display_name,
        public readonly string $base_url,
        public readonly string $manifest_url,
        public readonly string $manifest_version,
        public readonly ManifestLogo $logo,
        public readonly PluginType $type,
        public readonly string $description,
        public readonly string $version,
        public readonly array $tags,
        public readonly ?ManifestDeveloper $developer,
        public readonly ?ManifestConfig $config,
        public readonly array $options,
    ) {

        //check version
        $versions = [
            self::VERSION1,
            self::VERSION2,
        ];

        if (! in_array($this->manifest_version, $versions)) {
            throw new Exception('manifest_version -> Invalid manifest_version, manifest_version must be v1 or v2');
        }

        //name allow only slug in lower
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $this->name)) {
            throw new Exception('name -> Invalid name, name must be slug in lower case');
        }

        //check $this->version must follow samvar -> https://semver.org/

        if (! preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $this->version)) {
            throw new Exception('version -> Invalid version, version must follow semvar');
        }

        if (! filter_var($this->base_url, FILTER_VALIDATE_URL)) {
            throw new Exception('base_url -> Invalid BaseUrl URL');
        }

        if (! filter_var($this->manifest_url, FILTER_VALIDATE_URL)) {
            throw new Exception('manifest_url -> Invalid Manifest URL');
        }

        //check tags contain only string
        foreach ($this->tags as $tag) {
            if (! is_string($tag)) {
                throw new Exception('tags -> Invalid tags, tags must be array of strings only');
            }
        }

        foreach ($this->options as $option) {

            if (! is_a($option, ManifestCredentialForm::class)) {
                throw new Exception('options -> Invalid ManifestCredential -> '.get_class($option).' is not a ManifestCredential');
            }
        }

    }

    public function toArray(): array
    {

        $version = config('saas-bridge.main_version');

        if ($version == 'v1') {
            //old

            return [
                'name' => $this->name,
                'manifest_version' => $this->manifest_version,
                'display_name' => $this->display_name,
                'base_url' => $this->base_url,
                'manifest_url' => $this->manifest_url,
                'logo' => $this->logo->toArray(),
                'type' => $this->type->value,
                'description' => $this->description,
                'version' => $this->version,
                'tags' => $this->tags,
                'developer' => $this->developer?->toArray(),
                'config' => $this->config?->toArray(),
                'options' => [
                    'add' => collect($this->options)->mapWithKeys(fn ($i) => [$i->key => $i->toArray()])->toArray(),
                    'remove' => [],
                ],
            ];
        }

        return [
            'manifest_version' => $this->manifest_version,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'base_url' => $this->base_url,
            'manifest_url' => $this->manifest_url,
            'logo' => $this->logo->toArray(),
            'type' => $this->type->value,
            'description' => $this->description,
            'version' => $this->version,
            'tags' => $this->tags,
            'developer' => $this->developer?->toArray(),
            'config' => $this->config?->toArray(),
            'options' => collect($this->options)->map(fn ($item) => $item->toArray())->toArray(),
        ];
    }

    /**
     * @return \O360Main\SaasBridge\Http\Responses\ManifestResponse|void
     *
     * @deprecated Use ManifestResponse as a replacement
     */
    public static function fromJsonFile($filePath)
    {
        if (! file_exists($filePath)) {
            abort(404, 'File not found');
        }

        try {
            $json = json_decode(file_get_contents($filePath), true, 512, JSON_THROW_ON_ERROR);

            //        public readonly string             $name,
            //        public readonly string             $display_name,
            //        public readonly string             $base_url,
            //        public readonly string             $manifest_url,
            //        public readonly ManifestLogo       $logo,
            //        public readonly PluginType         $type,
            //        public readonly string             $description,
            //        public readonly string             $version,
            //        public readonly array              $tags,
            //        public readonly ?ManifestDeveloper $developer,
            //        public readonly ?ManifestConfig    $config,
            //        public readonly array              $options,

            return new self(
                manifest_version: $json['manifest_version'] ?? DEFAULT_MANIFEST_VERSION,
                name: $json['name'],
                display_name: $json['display_name'],
                base_url: $json['base_url'],
                manifest_url: $json['manifest_url'],
                logo: new ManifestLogo(
                    small: new Image(
                        url: $json['logo']['small']['url'],
                        type: $json['logo']['small']['type'],
                    ),
                    medium: new Image(
                        url: $json['logo']['medium']['url'],
                        type: $json['logo']['medium']['type'],
                    ),
                    large: new Image(
                        url: $json['logo']['large']['url'],
                        type: $json['logo']['large']['type'],
                    ),
                ),
                type: PluginType::from($json['type']),
                description: $json['description'],
                version: $json['version'],
                tags: $json['tags'],
                developer: ManifestDeveloper::fromArray($json['developer']),
                config: ManifestConfig::fromArray($json['config']),
                options: value(function () use ($json) {

                    $items = $json['options']['add'] ?? $json['options'] ?? [];

                    $options = [];
                    foreach ($items as $option) {
                        $options[] = ManifestCredentialForm::fromArray($option);
                    }

                    return $options;
                }),
            );

        } catch (Exception $e) {
            abort(500, 'Invalid JSON');
        }
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json($this->toArray());
    }
}
