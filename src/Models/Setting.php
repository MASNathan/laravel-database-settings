<?php

namespace MASNathan\LaravelDatabaseSettings\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public function __construct(array $attributes = [])
    {
        $this->table = config('settings.database.table');

        parent::__construct($attributes);
    }
}
