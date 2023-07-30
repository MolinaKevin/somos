<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;

	protected $table = 'entities';

    protected $fillable = [
		'name', 'address', 'points'
	];
	/**
	 * Relationships
	 */

    public function users()
    {
        return $this->belongsToMany(User::class);
    }	
	
	public function entityable()
	{
		return $this->morphTo();
	}	
}
