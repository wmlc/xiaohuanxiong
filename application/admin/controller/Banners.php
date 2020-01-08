<?php
/**
 * Created by PhpStorm.
 * User: zhangxiang
 * Date: 2018/10/16
 * Time: 下午9:14
 */

namespace app\admin\controller;

use app\model\Banner as BannerModel;
use think\Request;
use think\facade\App;

class Banners extends BaseAdmin
{
    public function index()
    {
        $data = BannerModel::with('book');
        $banners = $data->order('id','desc')->paginate(5, false,
            [
                'query' => request()->param(),
                'type' => 'util\AdminPage',
                'var_page' => 'page',
            ]);
        $this->assign([
            'banners' => $banners,
            'count' => $data->count()
        ]);
        return view();
    }

    public function create()
    {
        return view();
    }

    public function save(Request $request)
    {
        $data = $request->param();
        $validate = new \app\admin\validate\Banner();
        if ($validate->check($data)) {
            if (count($request->file()) > 0) {
                $pic = $request->file('pic_name');
                $dir = App::getRootPath() . '/public/static/upload/banner/';
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $info = $pic->validate(['size' => 2048000, 'ext' => 'jpg,jpeg,png'])->rule('md5')->move($dir);
                if ($info) {
                    $data['pic_name'] = $info->getSaveName();
                }
                BannerModel::create($data);
                $this->success('添加成功');
            } else {
                $this->error('未上传图片');
            }
        } else {
            $this->error($validate->getError());
        }
    }

    public function edit()
    {
        $id = input('id');
        $banner = BannerModel::get($id);
        $this->assign([
            'banner' => $banner,
        ]);
        return view();
    }

    public function update(Request $request)
    {
        $data = $request->param();
        $validate = new \app\admin\validate\Banner();
        if ($validate->check($data)) {
            if (count($request->file()) > 0) {
                $pic = $request->file('pic_name');
                $dir = App::getRootPath() . '/public/static/upload/banner/';
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $info = $pic->validate(['size' => 2048000, 'ext' => 'jpg,jpeg,png'])->rule('md5')->move($dir);
                if ($info) {
                    $data['pic_name'] = $info->getSaveName();
                }
            }

            $result = BannerModel::update($data);
            if ($result) {

                $this->success('编辑成功');
            } else {
                $this->error('编辑失败');
            }
        } else {
            $this->error($validate->getError());
        }
    }

    public function delete()
    {
        $id = input('id');
        $result = BannerModel::destroy($id);
        if ($result) {
            return ['err' => 0, 'msg' => '删除成功'];
        } else {
            return ['err' => 1, 'msg' => '删除失败'];
        }
    }

    public function deleteAll($ids){
        BannerModel::destroy($ids);
    }
}