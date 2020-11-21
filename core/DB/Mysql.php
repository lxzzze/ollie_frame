<?php


namespace core\DB;


class Mysql
{
    //pdo实例
    protected $pdo;
    //执行的sql语句
    protected $sql;
    //查询表
    protected $table;
    //select语句
    protected $select;
    //where语句
    protected $where = [];
    //
    protected $operator = ['=','<>','!=','>','>=','<','<='];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function select(...$query)
    {
        //支持传入数组的形式如['id','name','title']
        if (count($query) == 1 && is_array($query)){
            foreach ($query[0] as $item){
                $this->select[] = $item;
            }
        }else{
            foreach ($query as $item){
                $this->select[] = $item;
            }
        }
        return $this;
    }

    public function table($table)
    {
        if (is_string($table)){
            $this->table = $table;
            return $this;
        }
        //报错
    }

    public function where($column,$operator = null,$value = null)
    {
        //支持传入数组形式
        if (is_array($column)){
            foreach ($column as $item){
                if (count($item) == 3){
                    if (in_array($item[1],$this->operator)){
                        $this->where[] = [$item[0],$item[1],$item[2]];
                    }
                }else if (count($item) == 2){
                    $this->where[] = [$item[0],'=',$item[1]];
                }
            }
        }else{
            if (in_array($operator,$this->operator)){
                $this->where[] = [$column,$operator,$value];
            }else{
                $this->where[] = [$column,'=',$value];
            }
        }
    }

//    public function select(...$query)
//    {
//        $this->sql = 'select ';
//        foreach ($query as $item){
//            $this->sql = $this->sql.'`'.$item.'`, ';
//        }
//        $this->sql = substr($this->sql,0,-2);
//        $this->sql = $this->sql.' ';
//        return $this;
//    }
//
//    public function from($table){
//        $this->sql = $this->sql.'from `'.$table.'` ';
//        return $this;
//    }
//
//    public function where($column,$operator,$value)
//    {
//        $this->sql = $this->sql.'where `'.$column.'` '.$operator.' '.$value.' ';
//        return $this;
//    }
//
//    public function get()
//    {
//        $statement = $this->pdo;
//        $prepare = $statement->prepare($this->sql);
//        try {
//            $prepare->execute();
//            return $prepare->fetchAll(\PDO::FETCH_ASSOC);
//        }catch (\PDOException $exception){
//            dd($exception->getMessage());
//        }
//    }

}