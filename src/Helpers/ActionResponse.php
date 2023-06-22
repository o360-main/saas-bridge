<?php

namespace O360Main\SaasBridge\Helpers;

use Illuminate\Contracts\Support\Responsable;

class ActionResponse implements Responsable
{
    private bool $completed;
    private int|null $progressPercentage;
    private int $interval;
    private bool $isError = false;
    private string|null $errorMsg = null;
    private array $data;

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }


    public static function make(): self
    {
        return new self();
    }

    public function setProgressPercentage(int $progressPercentage): self
    {
        $this->progressPercentage = $progressPercentage;
        return $this;
    }

    public function setInterval(int $interval): self
    {
        $this->interval = $interval;
        return $this;
    }

    public function setError(bool $isError, string $errorMessage): self
    {
        $this->isError = $isError;
        $this->errorMsg = $errorMessage;
        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function toArray()
    {

        if ($this->progressPercentage > 100) {
            $this->progressPercentage = 100;
        }

        return [
            'progress' => $this->progressPercentage,
            'completed' => $this->completed,
            'data' => $this->data,
            'interval' => $this->interval,
            'error' => $this->isError,
            'errorMsg' => $this->errorMsg,
        ];
    }

    public function toResponse($request)
    {
        $this->toArray();
    }
}
