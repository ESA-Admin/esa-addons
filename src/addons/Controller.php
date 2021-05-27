<?php
// +----------------------------------------------------------------------
// | thinkphp5.1.* Addons [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019 https://ffz.takeup.me All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 猪在天上飞 <root@bug-maker.com>
// +----------------------------------------------------------------------
namespace ESA\addons;

// use think\facade\Env;
// use think\facade\Request;
// use think\facade\Config;
// use think\Loader;
// use think\Container;
use app\common\controller\ESA;

/**
 * 插件基类控制器
 * Class Controller
 * @package think\addons
 */
class Controller extends ESA
{
    public function __construct()
    {
        parent::__construct();
        // exit(dump( $this->request));
        $controllers = explode("/",$this->controller);
        if(count($controllers) >= 2 && in_array(strtolower($controllers[0]),["index","admin","api"])){
            $this->ESA_TYPE = $controllers[0];
        }
        // $this->ADDON_TYPE 检查权限，分为api和ADMIN
        $this->ESA_TYPE = isset($this->ESA_TYPE) ? $this->ESA_TYPE : "INDEX";
        // $this->ESA_ADDON = model("common/Addons")->where("identification",$this->getName())->find();
        $this->ESA_ADDON_ROUTE = true; //经过插件的走插件的路由
        $this->ESA_CONFIG['addon'] = $this->ESA_ADDON;

        // 获取当前插件目录
        // $this->addons_path = Env::get('addons_path') . $this->getName() . DIRECTORY_SEPARATOR;
        // $this->addons_path = ADDONS_PATH . $this->getName() . DIRECTORY_SEPARATOR;
        
        // 重新定义模板的根目录
        // if ($this->view) {
        //     $this->view->config('view_path', $this->ESA_ADDON["path"] . 'view' . DIRECTORY_SEPARATOR);
        // }
    }
    // // 当前插件操作
    // protected $addon = null;
    // protected $controller = null;
    // protected $action = null;
    // // 模板配置信息
    // protected $config = [
    //     'type' => 'Think',
    //     'view_path' => '',
    //     'view_suffix' => 'html',
    //     'strip_space' => true,
    //     'view_depr' => DIRECTORY_SEPARATOR,
    //     'tpl_begin' => '{',
    //     'tpl_end' => '}',
    //     'taglib_begin' => '{',
    //     'taglib_end' => '}',
    // ];
    // /**
    //  * 架构函数
    //  * @param Request $request Request对象
    //  * @access public
    //  */
    // public function __construct()
    // {
    //     $this->request = Container::get('request');
    //     // 初始化配置信息
    //     $this->config = Config::get('template.') ?: $this->config;
    //     // 处理路由参数
    //     $route = $this->request->route();
    //     $addon = $this->request->module();
    //     $controller = $this->request->controller();
    //     $action = $this->request->action();
    //     // 是否自动转换控制器和操作名
    //     $convert = \think\facade\Config::get('url_convert');
    //     // 格式化路由的插件位置
    //     $this->action = $convert ? strtolower($action) : $action;
    //     $this->controller = $convert ? strtolower($controller) : $controller;
    //     $this->addon = $convert ? strtolower($addon) : $addon;
    //     $view_path = $this->config['view_path'] ?: 'view';
    //     // 重置配置
    //     // Config::set('template.view_path', Env::get('addons_path') . $this->addon . DIRECTORY_SEPARATOR . $view_path . DIRECTORY_SEPARATOR);
    //     parent::__construct();
    // }
    // /**
    //  * 加载模板输出
    //  * @access protected
    //  * @param string $template 模板文件名
    //  * @param array $vars 模板输出变量
    //  * @param array $replace 模板替换
    //  * @param array $config 模板参数
    //  * @return mixed
    //  */
    // protected function fetch($template = '', $vars = [], $config = [])
    // {
    //     $controller = Loader::parseName($this->controller);
    //     // 获取相关配置信息
    //     $config = [
    //         "template_id"   => "admin",
    //         "platform_id"   => 1,
    //         "modulename"   => $this->action,
    //         "controllername"=> $this->controller,
    //         "actionname"    => $this->addon,
    //         'jsname'         => 'js/backend/' . str_replace('.', '/', $this->controller) . "/" . str_replace(".", "/", $this->action),
    //         // 文件选择接口
    //         'load_file_url' => url("admin/file_manage/get_file_list"),
    //         "upload_iframe_url"	=> url("admin/tag/file_manage"),
    //         "globe_iframe_url"	=> url("admin/tag/globe"),
    //         "icon_iframe_url"	=> url("admin/tag/icon"),
    //         "upload_file_api"	=> url("admin/file_manage/upload_file"),
    //     ];
    //     $this->assign("esa_config",$config);
    //     if ('think' == strtolower($this->config['type']) && $controller && 0 !== strpos($template, '/')) {
    //         $depr = $this->config['view_depr'];
    //         $template = str_replace(['/', ':'], $depr, $template);
    //         if ('' == $template) {
    //             // 如果模板文件名为空 按照默认规则定位
    //             $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $this->action;
    //         } elseif (false === strpos($template, $depr)) {
    //             $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
    //         }
    //     }
    //     return $this->view->fetch($template, $vars, $config);
    // }
}
