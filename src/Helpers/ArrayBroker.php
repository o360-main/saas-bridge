<?php

namespace O360Main\SaasBridge\Helpers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ArrayBroker
{
    protected Collection $array;
    protected bool $verbose = true;

    public function __construct(array $array, $verbose = false)
    {
        $this->array = collect($array);
        $this->verbose = $verbose;
    }


    public static function from(array $array): self
    {
        return new self($array);
    }


    public static function use(array $array): self
    {
        return new self($array);
    }

    public static function make(array $array): self
    {
        return new self($array);
    }

    //__toString
    public function __toString(): string
    {
        return $this->array->toJson();
    }

    /**
     * Direct
     * @throws Exception
     */
    public function original(string $key, $default = null)
    {
        return $this->get($key, $default)->toOriginal();
    }

    /**
     * @throws Exception
     */
    public function take(string $key, $default = null)
    {
        return $this->get($key, $default)->toOriginal();
    }

    /**
     * @throws Exception
     */
    public function string(string $key, $default = null): ?string
    {
        return $this->get($key, $default)->toString($default);
    }


    /**
     * @throws Exception
     */
    public function date(string $key, $default = null): ?Carbon
    {
        return $this->get($key, $default)->toDateTime($default);
    }

    /**
     * @throws Exception
     */
    public function int(string $key, $default = null): ?int
    {
        return $this->get($key, $default)->toInt($default);
    }

    /**
     * @throws Exception
     */
    public function integer(string $key, $default = null): ?int
    {
        return $this->get($key, $default)->toInt($default);
    }

    /**
     * @throws Exception
     */
    public function float(string $key, $default = null): ?float
    {
        return $this->get($key, $default)->toFloat($default);
    }


    /**
     * @throws Exception
     */
    public function decimal(string $key, $default = null): ?float
    {
        return $this->get($key, $default)->toDecimal($default);
    }


    /**
     * @throws Exception
     */
    public function double(string $key, $default = null): ?float
    {
        return $this->get($key, $default)->toDecimal($default);
    }


    /**
     * @throws Exception
     */
    public function bool(string $key, $default = null): ?bool
    {
        return $this->get($key, $default)->toBool($default);
    }

    /**
     * @throws Exception
     */
    public function array(string $key, $default = null): ?array
    {
        return $this->get($key, $default)->toArray($default);
    }

    /**
     * @throws Exception
     */
    public function collection(string $key, $default = []): ?Collection
    {
        return $this->get($key, $default)->toCollection($default);
    }

    /**
     * @return Collection<ArrayBroker>
     * @throws Exception
     */
    public function brokerCollection($key, $default = []): ?Collection
    {
        return $this->collection($key, $default)->map(function ($arr) {
            return ArrayBroker::use($arr);
        });
    }

    /**
     * @throws Exception
     */
    public function broker(string $key, $default = []): ?ArrayBroker
    {
        return ArrayBroker::use($this->get($key, $default)->toArray($default));
    }

    /**
     * @return ArrayBroker|TypeCast
     * @throws Exception
     */
    public function __invoke($key, $value = null, $merge = false)
    {
        if (is_array($key)) {
            $this->array = $this->array->merge($key);
            return $this;
        }

        if (is_null($value)) {
            return $this->get($key);
        }

        if ($merge) {
//            $this->array = $this->array->push($key, $value);
            $values = $this->get($key)->toOriginal();

            if (is_array($values)) {
                $values = collect($values);
                $values->add($value);
                $this->array = $this->array->add($key, $values->toArray());
            }
        }
        //---

        $this->array = $this->array->put($key, $value);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function get($key, $default = null): TypeCast
    {
        if ($this->verbose && !Arr::exists($this->array->toArray(), $key)) {
            throw new Exception("Key [{$key}] not found in array");
        }
        return new TypeCast(Arr::get($this->array->toArray(), $key, $default));
    }

    public function set($key, $value): self
    {
        $this->array->put($key, $value);
        return $this;
    }

    public function toCollection(): Collection
    {
        return $this->array;
    }

    public function toArray(): array
    {
        return $this->array->toArray();
    }


}
