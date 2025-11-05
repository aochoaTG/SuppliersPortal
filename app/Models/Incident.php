<?php

// app/Models/Incident.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = [
        'user_id','reporter_name','reporter_email','module','severity','title',
        'steps','expected','actual','reproducibility','impact','happened_at',
        'current_url','user_agent','status','image_path',
    ];

    protected $casts = ['happened_at' => 'datetime'];

    public function attachments() { return $this->hasMany(IncidentAttachment::class); }
}
