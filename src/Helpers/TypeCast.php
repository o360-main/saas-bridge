<?php

namespace O360Main\SaasBridge\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class TypeCast
{

    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var mixed|null
     */
    private $defaultValue = null;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toOriginal()
    {
        return $this->value;
    }

    public function __toString()
    {
        if (is_null($this->value)) {
            return "";
        }
        //if array
        if (is_array($this->value) || is_object($this->value)) {
            return $this->toCollection()->join(', ');
        }
        return $this->toString();
    }


    public function default($default = null): self
    {
        $this->defaultValue = $default;
        return $this;
    }

    protected function getDefaultValue($default = null)
    {
        if (is_null($default)) {
            return $this->defaultValue;
        }
        return $default;
    }


    public function toString($default = null): ?string
    {
        $default = $this->getDefaultValue($default);

        if (is_null($this->value)) {
            return $default;
        }

        if (is_array($this->value) || is_object($this->value)) {
            return $this->toCollection()->join(', ');
        }

        //convert to string
        if (is_string($this->value)) {
            return $this->value;
        }

        return (string)$this->value;
//        return $this->value ? trim(strval($this->value)) : $default;
    }

    public function toInt($default = null): ?int
    {
        $default = $this->getDefaultValue($default);

        if (is_null($this->value)) {
            return $default;
        }

        if (is_numeric($this->value)) {
            return $this->value;
        }

        return intval($this->value);
    }

    public function toFloat($default = null): ?float
    {
        $default = $this->getDefaultValue($default);

        if (is_null($this->value)) {
            return $default;
        }

        if ($this->value == 0) {
            return 0;
        }

        if (is_numeric($this->value)) {
            return $this->value;
        }

        return floatval($this->value);
    }

    public function toDecimal($default = null): ?float
    {
        $default = $this->getDefaultValue($default);

        if (is_null($this->value)) {
            return $default;
        }

        if ($this->value == 0) {
            return 0;
        }

        return doubleval($this->value);
    }

    public function toBool($default = null): ?bool
    {
        $default = $this->getDefaultValue($default);

        if (is_null($this->value)) {
            return $default;
        }

        if (is_bool($this->value)) {
            return $this->value;
        }
        return boolval($this->value);
    }

    public function toCollection($default = []): Collection
    {
        $default = $this->getDefaultValue($default);

        if ($this->value instanceof Collection) {
            return $this->value;
        }
        return collect($this->toArray($default) ?? $default);
    }

    public function toArray($default = null): ?array
    {
        if (is_null($this->value)) {
            return $default;
        }

        if (is_array($this->value)) {
            return $this->value;
        }

        if ($this->value instanceof Collection) {
            return $this->value->toArray();
        }

        return (array)$this->value;
    }

    public function toObject($default = null): ?object
    {
        $default = $this->getDefaultValue($default);

        if (is_null($this->value)) {
            return $default;
        }

        if (is_object($this->value)) {
            return $this->value;
        }

        return (object)$this->value;
    }

    public function toJson($default = null): ?string
    {
        $default = $this->getDefaultValue($default);

        return $this->value ? json_encode($this->value) : $default;
    }

    public function toJsonArray($default = null): ?array
    {
        $default = $this->getDefaultValue($default);
        return $this->value ? json_decode($this->value, true) : $default;
    }

    public function toDateTime($default = null): ?Carbon
    {
        $default = $this->getDefaultValue($default);

        if (is_null($this->value)) {
            return $default;
        }

        if ($this->value instanceof Carbon) {
            return $this->value;
        }

        if (is_string($this->value)) {
            return Carbon::parse($this->value);
        }

        return Carbon::parse($this->value);
    }


}
