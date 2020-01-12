<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TasksModel extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'task_text', 'task_status', 'updated_by',
    ];

    public function getUpdatedByAttribute($value)
    {
        $value = explode(',', $value);

        return array_shift($value);
    }

    public function setUpdatedByAttribute($value)
    {
        if (isset($this->original['updated_by'])) {
            $value = $value . ',' . $this->original['updated_by'];
        }

        $this->attributes['updated_by'] = $value;
    }

}
