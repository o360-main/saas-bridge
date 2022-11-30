<?php

namespace O360Main\SaasBridge\Helpers;

use Exception;

class NameHelper
{
    protected ArrayBroker $data;

    public ?string $fullName = null;
    public ?string $firstName = null;
    public ?string $middleName = null;
    public ?string $lastName = null;

    /**
     * @throws Exception
     */
    public function __construct(ArrayBroker $data)
    {
        $this->data = $data;
        $this->getFullName();
        $this->getFirstLastName();
    }


    /**
     * @throws Exception
     */
    public static function use(ArrayBroker $data): NameHelper
    {
        return new static($data);
    }

    /**
     * @throws Exception
     */
    private function getFullName()
    {
        $fullName = $this->data->string('full_name');

        if (!$fullName) {
            $this->fullName = collect([
                $this->data->string('first_name'),
                $this->data->string('middle_name'),
                $this->data->string('last_name'),
            ])->filter()->implode(' ');
            return;
        }

        $this->fullName = $fullName;
    }


    /**
     * @throws Exception
     */
    private function getFirstLastName()
    {
        $this->firstName = $this->data->string('first_name');
        $this->lastName = $this->data->string('last_name');

        if (!$this->firstName && !$this->lastName) {
            $parts = explode(' ', $this->fullName);

            $this->firstName = $parts[0];
            if (count($parts) === 1) {
                $this->lastName = null;
            } elseif (count($parts) === 2) {
                $this->lastName = $parts[1];
            } else {
                $this->middleName = $parts[1];
                $this->lastName = $parts[2];
            }
        }
    }

}
