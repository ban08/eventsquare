<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    // Tell Laravel which database table this model uses.
    protected $table = 'tag';
    // Tell Laravel which column is the primary key of this table.
    protected $primaryKey = 'id_tag';

    // "tag" table does NOT have "created_at" or "updated_at" columns.
    public $timestamps = false;

    // Only tag name.
    protected $fillable = [
        'name',
    ];

    // Events that have this tag.
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_tag', 'id_tag', 'id_event');
    }
}
