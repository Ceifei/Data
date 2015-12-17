<?php
class Data{
	
	//ver=11158
	
	//Fetch Mode
	public static $fetch_mode=PDO::FETCH_NUM;
	
	//Connection to Database
	private $_conn;
	
	//Database System: SQLite | MySQL | SQL_Server
	public $db_system;
	
	//Database Name
	public $db_name;
	
	//Constructor
	//Data __construct(string $db [,string $system [,string $server [,string $username [,string $password]]]])
	public function __construct($db,$system='SQLite',$server=NULL,$username=NULL,$password=NULL){
	
		//Database Server
		$this->db_system=$system;
		//Database Name
		$this->db_name=$db;
		
		//DSN
		if($system=='MySQL'){
			$dsn="mysql:host=$server;dbname=$db;";
		}
		if($system=='SQLite'){
			$dsn="sqlite:$db";
		}
				
        try{
			//PDO object
            $conn=new PDO($dsn,$username,$password);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (PDOException $e){
            echo "Failed to connect database: $dsn ".$e->getMessage();
        }
        $this->_conn=$conn;
	}
	
	//Get Recordset from Database
	public function get($sql,$fetch_mode=NULL){
	
		//Fetch Mode
		if(is_null($fetch_mode))
			$fetch_mode=self::$fetch_mode;
		
		//Clean
		$sql=trim($sql);
		if(!empty($sql)){
		
			//Resources, PDOStatement object
			$res=$this->_conn->query($sql) or die(print_r($this->_conn->errorInfo()).$sql);
			//Columns
			$n=$res->columnCount();
			
			//Empty recordset
			if($n==0) return NULL;
			//Multiple Columns
			if($n>1)
				$rs=$res->fetchAll($fetch_mode);
			elseif($n==1){
				//Single Column
				$rs=array();
				while($v=$res->fetch(PDO::FETCH_NUM)){
					$rs[]=$v[0];
				}
			}
            return $rs;
		}
		else{
			echo "Empty SQL statement: $sql!";
			return false;
		}
	}
	
	//Get Recordset from Database.
	//array getRS(string $sql)
    public function getRS($sql){
        if(is_string($sql)&&!empty($sql)){
            $res=$this->_conn->query($sql)
             or die(print_r($this->_conn->errorInfo()).$sql);
			$n=$res->columnCount();
			//Empty recordset
			if($n==0) return NULL;
			if($n>0){
				$rs=$res->fetchAll(self::$fetch_mode);
			}			
            return $rs;
        }else
           return false;
    }
	
	//Get Single Row from Database
	public function getRecord($sql){
		if(is_string($sql)&&!empty($sql)){
			//PDOStatement
			$res=$this->_conn->query($sql)
             or die(print_r($this->_conn->errorInfo()).$sql);
			$n=$res->columnCount();
			$rs=$res->fetch(self::$fetch_mode);
			if($n==1)
				$rs=$rs[0];
            return $rs;	
		}else
           return false;
	}
	
	//Carry out an SQL statement.
    //int doSQL(string $sql)
    public function doSQL($sql){
        if(is_string($sql)&&!empty($sql)){        
            $n=$this->_conn->exec($sql)
				or die(print_r($this->_conn->errorInfo()).$sql);
            return $n;
        }else
           return false;
    }
	
	protected function fn_quote($column, $string){
		return (strpos($column, '#') === 0 && preg_match('/^[A-Z0-9\_]*\([^)]*\)$/', $string)) ?

			$string :

			$this->_conn->quote($string);
	}
	
	protected function column_quote($string){
		return '`' . str_replace('.', '`.`', preg_replace('/(^#|\(JSON\))/', '', $string)) . '`';
	}
	
	//int insert(string $table, array $arr)
	public function insert($table,$arr_data){
		if(!empty($table) && is_array($arr_data)){
			
			$lastId = array();
			
			foreach($arr_data as $data){
				
				$values = array();
				$columns = array();

				foreach($data as $key => $value){
					array_push($columns, $this->column_quote($key));

					switch (gettype($value))
					{
						case 'NULL':
							$values[] = 'NULL';
							break;

						case 'array':
							preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

							$values[] = isset($column_match[0]) ?
								$this->_conn->quote(json_encode($value)) :
								$this->_conn->quote(serialize($value));
							break;

						case 'boolean':
							$values[] = ($value ? '1' : '0');
							break;

						case 'integer':
						case 'double':
						case 'string':
							$values[] = $this->fn_quote($key, $value);
							break;
					}
				}
				
				//SQL
				$sql="INSERT INTO `$table`(".implode(',',$columns).') VALUES (' . implode($values, ', ') . ')';
				$this->doSQL($sql);
				$lastId = $this->_conn->lastInsertId();
			}
			
			//Return ID
			return (int)$lastId;
		}
	}
	
	//Update Table
	public function update($table,$arr_data,$where){
		if(!empty($table) && is_array($arr_data)){
			
			$fields = array();

			foreach ($arr_data as $key => $value){
				preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);

				if (isset($match[3]))
				{
					if (is_numeric($value))
					{
						$fields[] = $this->column_quote($match[1]) . ' = ' . $this->column_quote($match[1]) . ' ' . $match[3] . ' ' . $value;
					}
				}
				else
				{
					$column = $this->column_quote($key);

					switch (gettype($value))
					{
						case 'NULL':
							$fields[] = $column . ' = NULL';
							break;

						case 'array':
							preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

							$fields[] = $column . ' = ' . $this->quote(
									isset($column_match[0]) ? json_encode($value) : serialize($value)
								);
							break;

						case 'boolean':
							$fields[] = $column . ' = ' . ($value ? '1' : '0');
							break;

						case 'integer':
						case 'double':
						case 'string':
							$fields[] = $column . ' = ' . $this->fn_quote($key, $value);
							break;
					}
				}
			}
			$sql="UPDATE `$table` SET ". implode(', ', $fields) .' '.$where;
			$this->doSQL($sql);
		}
	}
	
	//Delete One Record
	public function del($table,$id){
		if(!empty($table)&&!empty($id)){
			$sql="DELETE FROM `$table` WHERE `ID`=$id";
			$this->doSQL($sql);
		}
	}
	
	//Delete Some Records
	public function delete($table,$where){
		if(!empty($table)&&!empty($where)){
			$sql="DELETE FROM `$table` $where";
			$this->doSQL($sql);
		}
	}
}
?>
