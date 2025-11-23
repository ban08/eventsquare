<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
	use HasFactory;

	protected $table = 'invitation';
	protected $primaryKey = 'id_invitation';
	public $timestamps = false; // Using custom sent_at/responded_at columns

	protected $fillable = [
		'id_event',
		'id_invitee',
		'status',
		'sent_at',
		'responded_at',
	];

	protected function casts(): array
	{
		return [
			'sent_at' => 'datetime',
			'responded_at' => 'datetime',
		];
	}

	public function event(): BelongsTo
	{
		return $this->belongsTo(Event::class, 'id_event');
	}

	public function invitee(): BelongsTo
	{
		return $this->belongsTo(User::class, 'id_invitee');
	}

	public function scopePending($query)
	{
		return $query->where('status', 'pending');
	}
}
