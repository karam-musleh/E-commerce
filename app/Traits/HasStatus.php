<?php

namespace App\Traits;

trait HasStatus
{
    /**
     * Scope to query models that are active.
     */
    public function scopeActive($query)
    {
        return $query->where('status', static::STATUS_ACTIVE);
    }

    /**
     * Scope to query models by a specific status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Set the status of the model to a new value.
     */
    public function setStatus(string $status): bool
    {
        if (!in_array($status, $this->getAllowedStatuses())) {
            return false; // status غير مسموح
        }

        $this->status = $status;
        return $this->save();
    }

    /**
     * Mark the model as active.
     */
    public function markAsActive(): bool
    {
        return $this->setStatus(static::STATUS_ACTIVE);
    }

    /**
     * Mark the model as inactive.
     */
    public function markAsInactive(): bool
    {
        return $this->setStatus(static::STATUS_INACTIVE);
    }

    /**
     * Check if the current model's status is a specific value.
     */
    public function isStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Check if the current model is active.
     */
    public function isActive(): bool
    {
        return $this->isStatus(static::STATUS_ACTIVE);
    }

    /**
     * Get all allowed statuses from the model constants.
     */
    public function getAllowedStatuses(): array
    {
        $class = new \ReflectionClass(static::class);
        $constants = $class->getConstants();

        return array_filter($constants, function ($key) {
            return str_starts_with($key, 'STATUS_');
        }, ARRAY_FILTER_USE_KEY);
    }
}
