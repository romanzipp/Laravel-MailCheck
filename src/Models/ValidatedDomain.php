<?php

namespace romanzipp\MailCheck\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $hits
 * @property \Carbon\Carbon $last_queried
 */
class ValidatedDomain extends Model
{
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_queried' => 'datetime',
    ];

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('mailcheck.checks_table'));
    }
}
