<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Scopes\AktuellScope;
use App\Models\Traits\Active;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Kaufvertraege extends Model implements ActiveContract
{
    use Searchable {
        Searchable::scopeSearch as scopeSearchFromTrait;
    }
    use DefaultOrder;
    use Active;

    public $timestamps = false;
    protected $table = 'WEG_MITEIGENTUEMER';
    protected $primaryKey = 'ID';
    protected $searchableFields = ['ID', 'VON', 'BIS'];
    protected $defaultOrder = ['VON' => 'desc', 'BIS' => 'desc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'purchase_contract';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function eigentuemer()
    {
        return $this->belongsToMany(Person::class, 'WEG_EIGENTUEMER_PERSON', 'WEG_EIG_ID', 'PERSON_ID')->wherePivot('AKTUELL', '1');
    }

    public function einheit()
    {
        return $this->belongsTo('App\Models\Einheiten', 'EINHEIT_ID', 'EINHEIT_ID');
    }

    public function getStartDateFieldName()
    {
        return 'VON';
    }

    public function getEndDateFieldName()
    {
        return 'BIS';
    }

    public function scopeSearch($query, $tokens)
    {
        $query->with(['einheit', 'eigentuemer'])->orWhere(function ($query) use ($tokens) {
            $query->searchFromTrait($tokens);
        })->orWhereHas('einheit', function ($query) use ($tokens) {
            $query->search($tokens);
        })->orWhereHas('eigentuemer', function ($query) use ($tokens) {
            $query->search($tokens);
        });
        return $query;
    }
}
