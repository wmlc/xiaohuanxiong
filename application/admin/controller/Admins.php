<?php
/**
 * Created by PhpStorm.
 * User: hiliq
 * Date: 2019/1/8
 * Time: 17:03
 */

namespace app\admin\controller;


use app\model\Admin;
use app\service\AdminService;
use think\App;
use think\Request;

class Admins extends BaseAdmin
{

    public function index(){
        $adminService = new AdminService();
        $data = $adminService->GetAll();
        $this->assign([
            'admins' => $data['admins'],
            'count' => $data['count']
        ]);
        return view();
    }

    public function create(){
        return view();
    }

    public function save(Request $request){
        $data = $request->param();
        $admin = Admin::where('username','=',trim($data['username']))->find();
        if ($admin){
            $this->error('存在同名账号');
        }else{
            $admin = new Admin();
            $admin->username = $data['username'];
            $admin->password = md5(strtolower(trim($data['password'])).config('site.salt'));
            $admin->save();
            $this->success('新增管理员成功');
        }
    }

    public function edit(){
        $admin = Admin::get(input('id'));
        $this->assign([
            'admin' => $admin,
        ]);
        return view();
    }

    public function update(Request $request){
        $data = $request->param();
        $admin = new Admin();
        $admin->id = $data['id'];
        $admin->username = $data['username'];
        if (!empty($data['password'])){
            $admin->password = md5(strtolower(trim($data['password'])).config('site.salt'));
        }
        $admin->isUpdate(true)->save();
        $this->success('编辑成功');
    }

    public function delete(){
        $id = input('$id');
        $count = count(Admin::all());
        if ($count <= 1){
            return ['err' => '1','msg' => '至少保留一个管理员账号'];
        }
        Admin::destroy($id);
        return ['err' => '0','msg' => '删除成功'];
    }
}