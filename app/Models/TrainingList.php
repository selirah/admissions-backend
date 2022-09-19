<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrainingList extends Model
{
    private $_connection;
    protected $table = 'training_list';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _getAllTrainingList()
    {
        return $this->_connection->table($this->table)->get();
    }

    public function _getTrainingList()
    {
        return $this->_connection->table($this->table)
            ->join('school', $this->table . '.school_id', '=', 'school.id')
            ->join('training', $this->table . '.training_id', '=', 'training.id')
            ->join('training_attendees', $this->table . '.id', '=', 'training_attendees.training_list_id')
            ->get();
    }

    public function _getTrainingListByYear($year)
    {
        return $this->_connection->table($this->table)
            ->join('school', $this->table . '.school_id', '=', 'school.id')
            ->join('training', $this->table . '.training_id', '=', 'training.id')
            ->join('training_attendees', $this->table . '.id', '=', 'training_attendees.training_list_id')
            ->where($this->table . '.year', '=', $year)
            ->get();
    }

    public function _getTrainingListByTrainingIdAndYear($traningId, $year)
    {
        return $this->_connection->table($this->table)
            ->join('school', $this->table . '.school_id', '=', 'school.id')
            ->join('training', $this->table . '.training_id', '=', 'training.id')
            ->join('training_attendees', $this->table . '.id', '=', 'training_attendees.training_list_id')
            ->where($this->table . '.training_id', '=', $traningId)
            ->where($this->table . '.year', '=', $year)
            ->get();
    }

    public function _getTraining($id)
    {
        return $this->_connection->table($this->table)
            ->join('school', $this->table . '.school_id', '=', 'school.id')
            ->join('training', $this->table . '.training_id', '=', 'training.id')
            ->join('training_attendees', $this->table . '.id', '=', 'training_attendees.training_list_id')
            ->where($this->table . '.id', '=', $id)
            ->first();
    }

    public function _count($trainingId, $year)
    {
        return $this->_connection->table($this->table)
            ->where('training_id', '=', $trainingId)
            ->where('year', '=', $year)
            ->count();
    }

    public function _getTrainingBasedOnSchool($schoolId, $year)
    {
        return $this->_connection->table($this->table)
            ->where('school_id', '=', $schoolId)
            ->where('year', '=', $year)
            ->first();
    }

    public function _save(array $payload)
    {
        return $this->_connection->table($this->table)->insertGetId($payload);
    }

    public function _update($id, array $payload)
    {
        $this->_connection->table($this->table)->where('id', '=', $id)->update($payload);
    }

    public function _delete($id)
    {
        $this->_connection->table($this->table)->delete($id);
    }

    public function _saveBatch(array $payload)
    {
        $this->_connection->table($this->table)->insertOrIgnore($payload);
    }
}
