<?php


namespace App\Model;


use think\Model;

class Activity extends Model
{
    protected $table = 'activity';

    // 定义全局的查询范围
    protected $globalScope = ['status'];

    public function scopeStatus($query)
    {
        $query->where('status',1);
    }

    public function goods()
    {
        return $this->belongsToMany(Goods::class,'activity_goods','goods_id','activity_id');
    }
}