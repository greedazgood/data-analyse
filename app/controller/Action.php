<?php
namespace app\controller;

use support\Db;
use support\Request;

class Action
{
    public function sd(Request $request)
    {
        $time1 = microtime(true);
        $column = $request->post('column');
        $category_1 = $request->post('category_1');
        $category_2 = $request->post('category_2');

        $cat_unique_1 = Db::connection('data')->table('data2020')
            ->select($category_1)->distinct()->get()->toArray();
        $cat_unique_2 = Db::connection('data')->table('data2020')
            ->select($category_2)->distinct()->get()->toArray();
        $data = [];
        foreach ($cat_unique_1 as $cat_1){
            foreach ($cat_unique_2 as $cat_2){
                $query = Db::connection('data')->table('data2020')
                    ->whereNotNull($column)
                    ->where($category_1,$cat_1->$category_1)
                    ->where($category_2,$cat_2->$category_2);
                $avg = $query->avg($column);
                $count = $query->count();
                $lists = $query->get();
                $sum = 0;
                foreach ($lists as $l){
                    $diff = abs($l->$column-$avg);
                    $double = $diff*$diff;
                    $sum += $double;
                }
                $sd = $sum/$count;
                $data[] = [
                    'category_1' => $category_1,
                    'category_1_value' => $cat_unique_1,
                    'category_2' => $category_2,
                    'category_2_value' => $cat_unique_2,
                    'sd' => sqrt($sd)
                ];
                return view('result',['data'=>$data]);
            }
        }

        $time2 = microtime(true);
        echo ($time2-$time1);

    }
}
