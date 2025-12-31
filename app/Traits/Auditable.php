<?php

namespace App\Traits;

trait Auditable
{
    /**
     * Boot the trait.
     */
    protected static function bootAuditable(): void
    {
        static::observe(\App\Observers\AuditObserver::class);
    }
}
