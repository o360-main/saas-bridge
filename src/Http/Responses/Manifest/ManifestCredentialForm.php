<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

use Illuminate\Contracts\Support\Arrayable;

class ManifestCredentialForm implements Arrayable
{
    /**
     * @throws \Exception
     */
    public function __construct(
        public readonly string                 $key,
        public readonly string                 $label,
        public readonly string                 $description,
        public readonly string                 $rules,
        public readonly ?string                $default,
        public readonly ManifestCredentialType $type,
        public readonly int                    $index = 0,
        public readonly array                  $options = [],
    )
    {

        if ($this->type == ManifestCredentialType::SELECT) {
            if (count($this->options) == 0) {
                throw new \Exception('Invalid options, options must be provided for select type');
            }

            foreach ($this->options as $option) {

                if (!is_a($option, ManifestCredentialOption::class)) {
                    throw new \Exception('Invalid ManifestCredentialOption -> ' . get_class($option) . ' is not a ManifestCredentialOption');
                }
            }
        }

        if ($this->type != ManifestCredentialType::SELECT && count($this->options) > 0) {
            throw new \Exception('Invalid options, options must be empty for non select type');
        }
    }


    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'rules' => $this->rules,
            'default' => $this->default,
            'type' => $this->type,
            'index' => $this->index,
            'options' => collect($this->options)->map(fn($item) => $item->toArray())->toArray(),
        ];
    }


    public static function fromArray(array $data): static
    {
        return new static(
            key: $data['key'],
            label: $data['label'],
            description: $data['description'],
            rules: $data['rules'],
            default: $data['default'],
            type: $data['type'],
            index: $data['index'],
            options: collect($data['options'])->map(fn($item) => ManifestCredentialOption::fromArray($item))->toArray(),
        );
    }
}
