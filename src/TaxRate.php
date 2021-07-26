<?php

namespace Laravel\Spark;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tax_rates';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
