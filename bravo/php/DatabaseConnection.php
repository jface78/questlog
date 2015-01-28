<?php
require('../../credentials.php');

class DatabaseConnection {
  public $connection;
  public $isConnected = false;
  
  private function connect() {
    try {
      $this -> connection = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASS);
      $this -> connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this -> isConnected = true;
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
    }
  }
  
  public function matchColumns($table, $columns, $values) {
    $this -> checkConnection();
    $sqlString = '';
    for ($i=0; $i < count($columns); $i++) {
      $sqlString .= $columns[$i] . '=';
      if ($values[$i] == 'now()') {
        $sqlString .= 'now()';
      } else {
        $sqlString .= ':value' . $i;
      }
      if ($i < count($columns)-1) {
        $sqlString .= ' AND ';
      }
    }
    $result = $this -> connection -> prepare('SELECT count(*) FROM ' . $table . ' WHERE ' . $sqlString . ';');
    for ($i=0; $i < count($values); $i++) {
      $result -> bindParam(':value' . $i, $values[$i]);
    }
    $result->execute();
    return $result -> fetchColumn();
  }
  
  public function fetchColumn($table, $column, $where, $value) {
    $this -> checkConnection();
    $string = 'SELECT ' . $column . ' FROM ' . $table;
    if (isset($where) && isset($value)) {
      $string .= ' WHERE ' .$where . '=\'' . $value . '\';';
    }
    $statement = $this -> connection -> prepare($string);
    $statement -> execute();
    return $statement -> fetchColumn();
  }
  
  private function checkConnection() {
    if (!$this -> isConnected) {
      $this -> connect();
    }
  }
  
  public function updateColumns($table, $columns, $values, $where, $whereValue) {
    $this -> checkConnection();
    $sqlString = 'UPDATE ' . $table . ' SET ';
    for ($i=0; $i < count($columns); $i++) {
      $sqlString .= $columns[$i] . '=:value' . $i;
      if ($i < count($columns)-1) {
        $sqlString .= ',';
      }
    }
    if (isset($where) && isset($whereValue)) {
      $sqlString .= ' WHERE ' . $where . '=\'' . $whereValue . '\'';
    }
    $statement = $this -> connection -> prepare($sqlString);
    for ($i=0; $i < count($values); $i++) {
      if ($values[$i] == 'now()') {
        $date = date('Y-m-d H:i:s');
        $statement -> bindParam(':value' . $i, $date);
      } else {
        $statement -> bindParam(':value' . $i, $values[$i]);
      }
    }
    $statement -> execute();
  }
  
  public function fetchData($table, $columns, $wheres, $whereValues) {
    $this -> checkConnection();
    $sqlString = 'SELECT ';
    for ($i=0; $i < count($columns); $i++) {
      $sqlString .= $columns[$i];
      if ($i < count($columns)-1) {
        $sqlString .= ',';
      }
    }
    $sqlString .= ' FROM ' . $table;
    if (isset($wheres) && isset($whereValues)) {
      $sqlString .= ' WHERE ';
      for ($i=0; $i< count($wheres); $i++) {
        $sqlString .= $wheres[$i] . '=:value' . $i;
      }
    }
    $statement = $this -> connection -> prepare($sqlString);
    if (isset($whereValues)) {
      for ($i=0; $i < count($whereValues); $i++) {
        $statement -> bindParam(':value' . $i, $whereValues[$i]);
      }
    }
    $statement -> execute();
    return $statement -> fetchAll();
  }
  
  public function newRow($table, $columns, $values) {
    $this -> checkConnection();
    $columnsSQL = '';
    $valuesSQL = '';
    for ($i=0; $i < count($columns); $i++) {
      $columnsSQL .= $columns[$i];
      if ($values[$i] == 'now()') {
        $valuesSQL .= 'now()';
      } else {
        $valuesSQL .= '\'' . $values[$i] . '\'';
      }
      if ($i < count($columns)-1) {
        $columnsSQL .= ',';
        $valuesSQL .= ',';
      }
    }
    $string = 'INSERT INTO ' . $table . '(' . $columnsSQL . ') VALUES(' . $valuesSQL . ')';
    $statement = $this -> connection -> prepare($string);
    $statement -> execute();
  }
}
?>