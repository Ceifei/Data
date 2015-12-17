<?php

include('class_Data.php');

$data=new Data('db.sqlite');

/* //Create Table
$sql="CREATE TABLE tb_users(ID INTEGER PRIMARY KEY, Name TEXT,Update_Time TIMESTAMP NOT NULL DEFAULT(DATETIME('now','localtime')));";
//Carry Out This SQL
$data->doSQL($sql); */

/* //Insert Data by SQL
$str=rand();
$sql="INSERT INTO tb_users(Name) VALUES('Ceifei_sql_$str');";
$data->doSQL($sql); */

/* //Insert Data by Array
$str=rand();
$arr=array('Name'=>"Ceifei_array_$str");
//$id=$data->insert('tb_users',array($arr));
$id=$data->insert('tb_users',array($arr,$arr));
print_r($id); */

/* //Get Data
$sql="SELECT * FROM tb_users";
//$rs=$data->get($sql);
$rs=$data->get($sql,PDO::FETCH_ASSOC);
print_r($rs); */

/* //Get Data of Single Column
$sql="SELECT Name FROM tb_users";
$rs=$data->get($sql,PDO::FETCH_ASSOC);
print_r($rs); */

/* //Get Datum
$sql="SELECT Name FROM tb_users WHERE ID=1";
//$rs=$data->get($sql);
$rs=$data->getRecord($sql);
print_r($rs); */

/* //Update Data
$arr=array('Name'=>"Ceifei_update");
//$data->update('tb_users',$arr,'WHERE ID=4');
$data->update('tb_users',$arr,'WHERE ID=400'); */

//Delete Data
//$data->del('tb_users',9);
//$data->del('tb_users',900);

/* //Delete Data by WHERE
$data->delete('tb_users','WHERE ID>20'); */
?>
