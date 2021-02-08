<?php
namespace app\controller;

use support\Db;
use support\Request;

class Action
{
    public function sd(Request $request)
    {
        $time1 = microtime(true);
        $columns = $request->post('column');
        $category_1 = $request->post('category_1');
        $category_2 = $request->post('category_2');

        $cat_unique_1 = Db::connection('data')->table('data2020')
            ->select($category_1)->distinct()->get()->toArray();
        $cat_unique_2 = Db::connection('data')->table('data2020')
            ->select($category_2)->distinct()->get()->toArray();
        $columns = is_array($columns) ? $columns:[$columns];
        $data = [];
        foreach ($cat_unique_1 as $cat_1){
            foreach ($cat_unique_2 as $cat_2){
                foreach ($columns as $column){
                    $query = Db::connection('data')->table('data2020')
                        ->whereNotNull($column)//74.58 注释后74 总结是没啥影响
                        ->where($category_1,$cat_1->$category_1)
                        ->where($category_2,$cat_2->$category_2);
                    $avg = $query->avg($column);
                    $count = $query->count() - 1;
                    $lists = $query->get();
                    $sum = 0;
                    foreach ($lists as $l){
                        if (is_numeric($l->$column)){
                            $diff = abs($l->$column - $avg);
                        }else{
                            $diff = 0;
                        }
                        $double = $diff*$diff;
                        $sum += $double;
                    }
                    $sd = $count==0? 0:$sum/$count;
                    $result = [
                        'column'=>$column,
                        'category_1' => $category_1,
                        'category_1_value' => $cat_1->$category_1,
                        'category_2' => $category_2,
                        'category_2_value' => $cat_2->$category_2,
                        'avg' => $avg,
                        'sd' => sqrt($sd)
                    ];
                    $data[] = $result;
                    Db::connection('data')->table('data2020sd')->insert($result);
                }
            }
        }
        $time2 = microtime(true);
        echo ($time2-$time1);
        return view('result',['data'=>$data,'category_1'=>$category_1,'category_2'=>$category_2]);
    }

    public function update()
    {
        $time1 = microtime(true);
//        $lists = Db::connection('data')->table('data2020sd')->whereNotNull('avg')->get();
//        foreach ($lists as $list){
//            if ($list->sd && $list->sd !=0){
//                $time4 = microtime(true);
//                $during = 3*$list->sd;
//                $min = $list->avg - $during;
//                $max = $list->avg + $during;
//                Db::connection('data')->table('data2020')
//                    ->where($list->category_1,$list->category_1_value)
//                    ->where($list->category_2,$list->category_2_value)
//                    ->whereRaw("{$list->column} <= {$max}")
//                    ->whereRaw("{$list->column} >= {$min}")
//                    ->update([$list->column.'sd'=>1]);
//                $time3 = microtime(true);
//                echo $time3-$time4.PHP_EOL;
//            }
//        }
        //Db::connection('data')->table('data2020')->update(['bmi'=>Db::raw('tz/((sg/100)*(sg/100))')]);
        $bmi = Db::connection('data')->table('data2020bmi')->where('age','>',10)->get();
        //$b = Db::table('bmi')->where('age',18)->first();
        foreach ($bmi as $b){
            echo $b->age.PHP_EOL;
            $time3  = microtime(true);
            echo ($time3-$time1).PHP_EOL;
            $age = $b->age;
            $age_up = $b->age+0.5;
            Db::connection('data')->table('data2020')
                ->whereRaw("`age` >= {$age}")
                ->whereRaw("`age` < {$age_up}")
                ->chunkById(1000,function ($users)use($b){
                    foreach ($users as $user){
                        $bmi = $user->bmi;
                        $bmi_under = $this->getBmiUnder($b,$user->xb,$bmi);
                        Db::connection('data')->table('data2020')->where('id',$user->id)->update(['bmi_under'=>$bmi_under]);
                    }
                });
        }
        $time2 = microtime(true);
        echo ($time2-$time1);
    }

    public function getBmiUnder($bmi,$gender,$real_bmi)
    {
        if ($gender ==1){
            $over_weight = $bmi->boy_ow;
            $fat = $bmi->boy_fat;
        }else{
            $over_weight = $bmi->girl_ow;
            $fat = $bmi->girl_fat;
        }

        if ($real_bmi>= $fat) {
            return 2;//肥胖
        }

        if ($real_bmi >=$over_weight && $real_bmi<$fat) {
            return 1;//超重
        }

        return 0;//正常
    }
}
