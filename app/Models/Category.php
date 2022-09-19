<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    private $_connection;
    protected $table = 'category';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->_connection = DB::connection('mysql');
    }

    public function _getCategories()
    {
        $query = $this->_connection->table($this->table)->get();
        return $query;
    }

    public function _getCategory($id)
    {
        $query = $this->_connection->table($this->table)
            ->where('id', '=', $id)
            ->first();
        return $query;
    }
}
