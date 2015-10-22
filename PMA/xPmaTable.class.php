<?php

class PmaTable{
    protected $id = null;
    protected $table = null;
    
    function __construct() {        
    }
    
    function bind($data){
        foreach ($data as $key=>$value)
        {
            $this->$key = $value;
        }
    }
    
    function load($id=null)
    {
        $this->id=$id;
        $dbo = PmaDb::getInstance();
        $sql = $this->buildQuery('load');
        
        $dbo->doQuery($sql);
        echo $dbo->getNumRows();
        $junk = $dbo->getResults();
        
        $recs = $junk;
        
      while($r = mysqli_fetch_array($junk)){
          echo $r[0] . "<br/>";
      }

        $row = $dbo->loadObjectList();
        
        foreach($row as $key=>$value)
        {
            if($key === "id")
            {
                continue;
            }
            $this->$key = $value;
        }   
         
        return $sql;
    }
    
    public function store()
    {
        $dbo = PmaDb::getInstance();
        $sql = $this->buildQuery('store');
        $dbo->doQuery($sql);
    }
    
    protected function buildQuery($task)
    {
        $sql = "";
        if($task == 'store')
        {
            if($this->id == "")
            {
                $keys = "";
                $values ="";
                $classVars = get_class_vars(get_class($this));
                $sql .= "insert into {$this->table}";
                foreach($classVars as $key=>$value)
                {
                    if($key =="id" || $key == "table")
                    {
                        continue;
                    }                    
                    $keys .= "{$key},";
                    $values .= "'{$this->$key}',";
                }
                $sql .="(" .substr($keys,0,-1) .") Values(" .substr($values, 0, -1).")"; //removes last comma
            }else{
               $classVars = get_class_vars(get_class($this));
               $sql .="Update $this->table set ";
              foreach($classVars as $key => $value)
              {
                  if($key =="id" || $key == "table")
                    {
                        continue;
                    }
                    if($key=="request_desc")
                    {
                        $crap =  mysql_real_escape_string($this->$key);
                    }
                    else{
                        $crap =  $this->$key;
                    }
                    
                    $sql .= "{$key} = '$crap', ";
              }
              $sql = substr($sql,0,-2)." where id = {$this->id}";
              
            }
        }
        elseif($task == 'load')
        {
            $sql = "Select * from {$this->table} ";
            if($this->id){
                $sql .= "where id='{$this->id}'";
            }                
        }
        echo $sql;
        return $sql;
    }
    
}

?>

