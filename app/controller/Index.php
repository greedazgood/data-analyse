<?php
namespace app\controller;

use support\Db;
use support\Request;

class Index
{
    public function index(Request $request)
    {
        $name = Db::connection('data')
            ->table('INFORMATION_SCHEMA.Columns')
            ->select('column_name','column_comment')
            ->where('table_name','data2020')
            ->get();
        return view('index',['data'=>$name]);
    }

    public function login(Request $request)
    {
        $session = $request->session();
        $username = $request->post('username');
        $password = $request->post('password');
        if ($session->get('userinfo')){
            return redirect('/');
        }
        if ($username){
            $query = [
                'username' => $username,
                'password' => md5($password)
            ];
            $user = Db::table('users')->where($query)->first();
            if ($user){
                $session = $request->session();
                $session->set('userinfo',$user->id);
                return redirect('/');
            }
            return view('login');
        }
        return view('login');
    }

    public function json(Request $request)
    {
        return json(['code' => 0, 'msg' => 'ok']);
    }

    public function file(Request $request)
    {
        $file = $request->file('upload');
        if ($file && $file->isValid()) {
            $file->move(public_path().'/files/myfile.'.$file->getUploadExtension());
            return json(['code' => 0, 'msg' => 'upload success']);
        }
        return json(['code' => 1, 'msg' => 'file not found']);
    }

}
