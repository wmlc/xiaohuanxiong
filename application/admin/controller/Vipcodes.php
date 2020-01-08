<?php


namespace app\admin\controller;

use app\model\VipCode;
use think\facade\Env;

class Vipcodes extends BaseAdmin
{
    public function index()
    {
        $data = VipCode::order('id', 'desc');
        $codes = $data->paginate(5, false,
            [
                //'query' => request()->param(),
                'type' => 'util\AdminPage',
                'var_page' => 'page',
            ]);

        $this->assign([
            'codes' => $codes,
            'count' => $data->count(),
            'api_key' => config('site.api_key'),
        ]);
        return view();
    }

    public function detail(){
        $this->assign([
            'salt' => config('kami.salt'),
            'vipcode_day' => config('kami.vipcode.day'),
            'vipcode_num' => config('kami.vipcode.num')
        ]);
        return view();
    }

    public function gencodes(){
        $num = input('vipcode_num'); //产生多少个
        $day = input('vipcode_day');

        $data = [
            'num' => $num,
            'day' => $day,
        ];
        $validate = new \app\admin\validate\Vipcode();
        if (!$validate->check($data)) {
           echo '设置错误';
        }

        $salt = config('site.' . config('kami.salt'));//根据配置，获取盐的方式
        for ($i = 1; $i <= $num; $i++) {
            $code = substr(md5($salt . time()), 8, 16);
            VipCode::create([
                'code' => $code,
                'add_day' => $day
            ]);
            echo '<p style="padding-left:15px;font-weight: 400;color:#999;">' . '生成' . $code . '，天数为' . $day . '</p>';
            sleep(1);
        }
    }

    public function search()
    {
        $where = array();
        $days = input('days');
        if (!empty($days)) {
            $where[] = ['add_day', '=', $days];
        }
        $used = input('used');
        if (!empty($used)) {
            $where[] = ['used', '=', $used];
        }
        $data = VipCode::where($where)->order('id', 'desc');

        $codes = $data->paginate(5, false,
            [
                'query' => request()->param(),
                'type' => 'util\AdminPage',
                'var_page' => 'page',
            ]);

        $this->assign([
            'codes' => $codes,
            'count' => $data->count(),
            'api_key' => config('site.api_key'),
        ]);
        return view('index');
    }

    public function export()
    {
        $data = VipCode::where('used', '=', 1)->select()->toArray();
        if (empty($data)) {
            return json(['err=>1', 'msg' => '没有可导出的']);
        }
        $arr = array();
        foreach ($data as $code) {
            VipCode::update([
                'id' => $code['id'],
                'used' => 2,
                'update_time' => time()
            ]);
            $arr[] = $code['code'];
        }
        $dir = Env::get('root_path') . '/public/downloads';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = $dir . '/vipcode.txt';
        if (file_exists($file)) {
            delete_dir_file($file);
        }
        file_put_contents($file, implode("\r\n", $arr));
        $site_url = config('site.url');

        return json(['err=>0', 'msg' => '成功导出，<a href="' . $site_url.'/downloads/chargecode.txt">点击下载</a>']);
    }
    
    public function delete(){
        $id = input('id');
        $code = VipCode::get($id);
        if (empty($code)){
            return json(['err' => '1','msg' => '找不到该项']);
        }
        $code->delete();
        return json(['err' => '0','msg' => '删除成功']);
    }

    public function deleteAll($ids){
        VipCode::destroy($ids);
    }
}