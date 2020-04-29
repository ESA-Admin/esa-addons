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
namespace ESA;

// use think\facade\Env;
// use think\Controller;
use think\addons\Controller;

/**
 * 插件基类
 * Class Addns
 * @author coming <root@bug-maker.com>
 * @package think\addons
 */
abstract class Addons extends Controller
{
    // 当前错误信息
    protected $error;

    /**
     * $info = [
     *  'name'          => 'Test',
     *  'title'         => '测试插件',
     *  'description'   => '用于thinkphp5.1的插件扩展演示',
     *  'status'        => 1,
     *  'author'        => '猪在天上飞',
     *  'version'       => '0.1'
     * ]
     */
    public $info = []; 

    // 初始化
    // protected function initialize()
    // {
    //     $controllers = explode("/",$this->controller);
    //     if(count($controllers) >= 2 && in_array(strtolower($controllers[0]),["index","admin","api"])){
    //         $this->ADDON_TYPE = $controllers[0];
    //     }
    //     // $this->ADDON_TYPE 检查权限，分为api和ADMIN
    //     $this->ADDON_TYPE = isset($this->ADDON_TYPE) ? $this->ADDON_TYPE : "INDEX";
    //     $this->authcheck($this->ADDON_TYPE);

    //     // 获取当前插件目录
    //     // $this->addons_path = Env::get('addons_path') . $this->getName() . DIRECTORY_SEPARATOR;
    //     $this->addons_path = ADDONS_PATH . $this->getName() . DIRECTORY_SEPARATOR;

    //     // 重新定义模板的根目录
    //     if ($this->view) {
    //         $this->view->config('view_path', $this->addons_path . 'view' . DIRECTORY_SEPARATOR);
    //     }
    // }

    /**
     * 获取插件信息
     * @return array
     */
    final public function getInfo()
    {
        $info_path = ADDONS_PATH . $this->getName() . DIRECTORY_SEPARATOR . 'info.ini';
        if (is_file($info_path)) {
            $info = parse_ini_file($info_path);
            if (is_array($info)) {
                $this->info = array_merge($this->info, $info);
            }
        }
        return $this->info;
    }

    /**
     * 获取插件的配置数组
     * @param string $name 可选模块名
     * @return array|mixed|null
     */
    final public function getConfig($parse = false)
    {
        $name = $this->getName();
        return get_addons_config($name, $parse);
    }

    /**
     * 获取当前模块名
     * @return string
     */
    final public function getName()
    {
        $data = array_reverse(explode('\\', get_class($this)));
        return $data[count($data)-2];
    }

    /**
     * 检查配置信息是否完整
     * @return bool
     */
    final public function checkInfo()
    {
        $info_check_keys = ['name', 'title', 'description', 'status', 'author', 'version'];
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, $this->getInfo())) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取当前错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    //必须实现安装
    // abstract public function install();

    //必须卸载插件方法
    // abstract public function uninstall();
}
    
