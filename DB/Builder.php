<?php
/**
 * Description of Builder
 *
 * @author atrichkov
 */
/* Засега хелпъра ще е пряко обвързан с PDO */
/* //        $a = $db->table('users')->update(array('gsm' => '9999', 'description' => 'asd'))->where('id', '2')->execute(); */
namespace EF\DB;
class Builder{
    // Metod build v simpleDB koito da priema paramet1r select/update/delete/insert
    protected $_query = null;
    protected $_table = null;
    protected $_columns = null;
    protected $_params = array();
    protected $_where = null;
    protected $_join = null;
    
    public function table($table) {
        $this->_table = $table;
        return $this;
    }
    
    public function getColumns() {
        return $this->_columns;
    }
    
    public function select($columns){
        if ($columns){
            $this->_columns = func_get_args();
            $this->_query = 'SELECT ' . implode(', ', $this->_columns) . ' FROM ' . $this->_table;
            return $this;
        } else {
            throw new \Exception('Invalid column name');
        }
    }
    public function where($conditions){
        if ($conditions){
            $this->_where = ' WHERE ';
            $arr = func_get_args();
            $this->_where .= $arr[0] . '=:' . $arr[0];
            $this->_params = array_merge($this->_params, array($arr[0] => $arr[1]));
            return $this;
        } else {
            throw new \Exception('Wrong parameters');
        }
        return $this;
    }
    
    public function update($columns){
        if ($columns){
            $this->_columns = $columns;
            foreach ($this->_columns as $k => $v){
                $keys .= $k . '=:' . $k . ', ';
            }
            $keys = substr($keys, 0, strrpos($keys, ','));
            $this->_params = array_merge($this->_params, $this->_columns);
            $this->_query = 'UPDATE ' . $this->_table . ' SET '. $keys;
            return $this;
        } else {
            throw new \Exception('Invalid column name');
        }
    }
    
    public function insert($columns){
        if ($columns){
            $this->_columns = $columns;
            foreach ($this->_columns as $k => $v){
                $keys .= ':' . $k. ',';
            }
            $keys = substr($keys, 0, strrpos($keys, ','));
            $this->_params = $columns;
            $this->_query = 'INSERT INTO ' . $this->_table . '  (' . implode(', ', array_keys($columns)) . ') VALUES ('. $keys . ')';
            return $this;
        } else {
            throw new \Exception('Invalid column name');
        }
    }
    public function delete($table){
        if ($table)
            $this->_table = $table;
        $this->_query = 'DELETE FROM ' . $this->_table; 
        return $this;
    }
    
    public function buildQuery(){
        return $this->_query . $this->_join . $this->_where;
    }
    
}

?>
