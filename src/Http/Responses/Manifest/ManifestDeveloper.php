<?php

namespace O360Main\SaasBridge\Http\Responses\Manifest;

use Illuminate\Contracts\Support\Arrayable;

class ManifestDeveloper implements Arrayable
{

    /**
     * @throws \Exception
     */
    public function __construct(
        public readonly string $name,
        public readonly string $author_email,
        public readonly ?string $author_url,
        public readonly ?string $notification_email,
        public readonly ?array $notes,
    )
    {
        //check notes contain only key=>string => value=>string
        foreach ($this->notes as $key => $note) {
            if (!is_string($key) || (!is_string($note) || !is_numeric($note))) {
                throw new \Exception('notes -> Invalid notes, notes must be {key => string}=>{value => string|numeric}');
            }
        }
    }

    public
    function toArray(): array
    {
        return [
            'name' => $this->name,
            'author_url' => $this->author_url,
            'author_email' => $this->author_email,
            'notification_email' => $this->notification_email,
            'notes' => $this->notes,
        ];
    }
}
