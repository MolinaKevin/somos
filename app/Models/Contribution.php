<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'amount', 'somos_id', 'nro_id' 
    ];

    public function somos()
	{
		return $this->belongsTo(Somos::class);
	}

    public function nro()
	{
		return $this->belongsTo(Nro::class);
	}

}
