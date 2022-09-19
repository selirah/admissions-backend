<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Courses extends Model
{
    private $_connection;
    protected $table = 'courses';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _save(array $payload)
    {
        return $this->_connection->table($this->table)->insert($payload);
    }

    public function _getCourses($schoolId)
    {
        return $this->_connection->table($this->table)->where('school_id', '=', $schoolId)
            ->orderByDesc('created_at')->get();
    }

    public function _getCourse($courseCode)
    {
        return $this->_connection->table($this->table)->where('course_code', '=', $courseCode)->first();
    }
}
