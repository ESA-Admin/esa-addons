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

use think\facade\App;
use think\facade\Env;
use think\facade\Hook;
use think\facade\Config;
use think\Loader;
use think\facade\Cache;
use think\facade\Route;

// 插件目录
$appPath = App::getAppPath();
$addons_path = dirname($appPath) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR;
Env::set('addons_path', $addons_path);
if (!defined("ADDONS_PATH")) {
    define("ADDONS_PATH",$addons_path);
}

$esa_url = request()->url();
if((preg_match("/PLATFORM_ID\/(\d+)/",$esa_url,$platform) || preg_match("/PLATFORM_ID=(\d+)/",$esa_url,$platform)) && !defined("PLATFORM_ID")){
    // exit(dump($platform[1]));
    define("PLATFORM_ID",$platform[1]);
}
if((preg_match("/ADDON\/(\w+)/",$esa_url,$addon) || preg_match("/ADDON=(\w+)/",$esa_url,$addon)) && !defined("ADDON_ID")){
    define("ADDON_ID",$addon[1]);
}
// 插件访问路由配置
Route::group('addons', function () {
    if (!isset($_SERVER['REQUEST_URI'])) {
        return 'error addons';
    }
    // 请求位置
    if (!empty(request()->server()['PATH_INFO'])) {
        $path_info = request()->server()['PATH_INFO'];
        $path = substr(ltrim($path_info),1,strlen($path_info));
    } else {
        $path = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    }
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if ($ext) {
        $path = substr($path, 0, strlen($path) - (strlen($ext) + 1));
    }
    $pathinfo = explode('/', $path);
    // 路由地址
    if ($pathinfo[0] == 'addons' && isset($pathinfo[2])) {
        // 获取路由地址
        if(is_numeric($pathinfo[1])){
            $platform_id = $pathinfo[1];
            !defined("PLATFORM_ID") ? define("PLATFORM_ID",$pathinfo[1]) : "";
            $route = explode('.', $pathinfo[2]);
            $module = array_shift($route);
            $action = $pathinfo[3];
        }else{
            $platform_id = "";
            !defined("PLATFORM_ID") ? define("PLATFORM_ID",0) : "";
            $route = explode('.', $pathinfo[1]);
            $module = array_shift($route);
            $action = $pathinfo[2];
        }
        define("ADDON_ID",$module);
        
        $className = ucfirst(array_pop($route));
        array_push($route, $className);
        $controller = join('\\', $route);
        $type = array_shift($route);
        // 生成view_path
        $view_path = Env::get('addons_path') . $module . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
        Config::set('template.view_path', $view_path);
        // 中间件
        $middleware = [];
        $config = Config::get('addons.middleware');
        if (is_array($config) && isset($config[$type])) {
            $middleware = (array)$config[$type];
        }
        // 请求转入
        // Route::rule(':rule', "\\addons\\{$module}\\controller\\{$controller}@{$pathinfo[2]}")->middleware($middleware);
        
        $controller2 = strtolower(str_replace("\\",".",$controller));
        $rule = $platform_id."/{$module}.{$controller2}/:rule";
        // 转换_为大写字母
        $controller = lcfirst(str_replace(" ","",ucwords(str_replace("_"," ",$controller))));
        Route::rule($rule, "\\addons\\{$module}\\controller\\{$controller}@{$action}")->middleware($middleware);
    }
})->middleware(function ($request, \Closure $next) {
    // 路由地址
    $pathinfo = explode('/', $request->path());
    if(is_numeric($pathinfo[1])){
        $routes = explode('.', $pathinfo[2]);
        $action = $pathinfo[3];
    }else{
        $routes = explode('.', $pathinfo[1]);
        $action = $pathinfo[2];
    }
    $request->setModule(array_shift($routes));
    $request->setController(join('/', $routes));
    $request->setAction($action);
    $request->__set("pfid",PLATFORM_ID);

    return $next($request);
});

// 如果插件目录不存在则创建
if (!is_dir($addons_path)) {
    @mkdir($addons_path, 0777, true);
}

// 注册类的根命名空间
Loader::addNamespace('addons', $addons_path);

// 闭包自动识别插件目录配置
Hook::add('app_init', function () {
    
    // Hook::import([
    //     "esa_attachment_init"=>["addons\demo\Hook","addons\\esa_backstage\\Hook"],
    //     "esa_attachment_done"=>["addons\demo\Hook"]
    // ], true);
    // 获取开关
    $autoload = (bool)Config::get('addons.autoload', false);
    // 非正是返回
    if (!$autoload) {
        $stract_hooks = config('addons.hooks');
        if (!empty($stract_hooks)) {
            cache('addons',$stract_hooks);
        }
        return;
    }
    
    // 当debug时不缓存配置
    $config = App::isDebug() ? [] : cache('addons');
    if (empty($config)) {
        // 读取插件目录及钩子列表
        $base = get_class_methods("\\ESA\\Addons");
        // 读取插件目录中的php文件
        foreach (glob(Env::get('addons_path') . '*/*.php') as $addons_file) {
            // 格式化路径信息
            $info = pathinfo($addons_file);
            // 获取插件目录名
            $name = pathinfo($info['dirname'], PATHINFO_FILENAME);
            // 找到插件入口文件
            if (strtolower($info['filename']) == 'main') {
                // 读取出所有公共方法
                $methods = (array)get_class_methods("\\addons\\" . $name . "\\" . $info['filename']);
                // 跟插件基类方法做比对，得到差异结果
                $hooks = array_diff($methods, $base);
                // 循环将钩子方法写入配置中
                foreach ($hooks as $hook) {
                    if (!isset($config['hooks'][$hook])) {
                        $config['hooks'][$hook] = [];
                    }
                    // 兼容手动配置项
                    if (is_string($config['hooks'][$hook])) {
                        $config['hooks'][$hook] = explode(',', $config['hooks'][$hook]);
                    }
                    if (!in_array($name, $config['hooks'][$hook])) {
                        $config['hooks'][$hook][] = $name;
                    }
                }
            }
        }
        cache('addons', $config);
    }

    config('addons', $config);
    // 获取系统配置
    $data = App::isDebug() ? [] : Cache::get('hooks', []);
    $config = config('addons');
    $addons = isset($config['hooks']) ? $config['hooks'] : [];
    if (empty($data)) {
        // 初始化钩子
        foreach ($addons as $key => $values) {
            if (is_string($values)) {
                $values = explode(',', $values);
            } else {
                $values = (array)$values;
            }
            $addons[$key] = array_filter(array_map('get_addons_class', $values));
            array_push($addons[$key],$key);
            Hook::add($key, $addons[$key]);
            // 注册意义对应的钩子
            foreach ($values as $key2 => $value2) {
                Hook::add($key."@".$value2,[$addons[$key][$key2],$key]);
            }
        }
        cache('hooks', $addons);
    } else {
        Hook::import($data, false);
    }
});

/**
 * 处理插件钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = [])
{
    $result = Hook::listen($hook, $params);
    if (is_array($result)) {
        foreach ($result as &$item) {
            if ($item instanceof \think\response\View) {
                $item = $item->getContent();
            }
        }
        $result = join(PHP_EOL, $result);
    } else {
        if ($result instanceof \think\response\View) {
            $result = $result->getContent();
        }
    }
    return $result;
}

/**
 * 获取插件类的类名
 * @param $name 插件名
 * @param string $type 返回命名空间类型
 * @param string $class 当前类名
 * @return string
 */
if (!function_exists('get_addons_class')) {
    function get_addons_class($name, $type = 'hook', $class = null)
    {
        $name = Loader::parseName($name);
        // 处理多级控制器情况
        if (!is_null($class) && strpos($class, '.')) {
            $class = explode('.', $class);
            foreach ($class as $key => $cls) {
                $class[$key] = Loader::parseName($cls, 1);
            }
            $class = implode('\\', $class);
        } else {
            $class = Loader::parseName(is_null($class) ? $name : $class, 1);
        }
        switch ($type) {
            case 'controller':
                $namespace = "\\addons\\" . $name . "\\controller\\" . $class;
                break;
            default:
                $namespace = "\\addons\\" . $name . "\\Main";
        }

        return class_exists($namespace) ? $namespace : '';
    }
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 * @return array
 */
if (!function_exists('get_addons_config')) {
    function get_addons_config($name, $parse = false)
    {
        static $_config = array();

        // 获取当前插件目录
        $addons_path = Env::get('addons_path') . $name . DIRECTORY_SEPARATOR;
        // 读取当前插件配置信息
        if (is_file($addons_path . 'config.php')) {
            $config_file = $addons_path . 'config.php';
        }

        if (isset($_config[$name])) {
            return $_config[$name];
        }

        $config = [];
        if (isset($config_file) && is_file($config_file)) {
            $temp_arr = include $config_file;
            if (is_array($temp_arr)) {
                if ($parse) {
                    foreach ($temp_arr as $key => $value) {
                        if ($value['type'] == 'group') {
                            foreach ($value['options'] as $gkey => $gvalue) {
                                foreach ($gvalue['options'] as $ikey => $ivalue) {
                                    $config[$ikey] = $ivalue['value'];
                                }
                            }
                        } else {
                            $config[$key] = $temp_arr[$key]['value'];
                        }
                    }
                } else {
                    $config = $temp_arr;
                }
            }
            unset($temp_arr);
        }
        $_config[$name] = $config;

        return $config;
    }
}

/**
 * 获取插件信息
 * @param $name
 * @return array
 */
if (!function_exists('get_addons_info')) {
    function get_addons_info($name)
    {
        $class = "\\addons\\{$name}\\Main";
        if (!class_exists($class)) {
            return [];
        }
        $addon = new $class();
        return $addon->getInfo();
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param $url
 * @param array $param
 * @param bool|string $suffix 生成的URL后缀
 * @param bool|string $domain 域名
 * @return bool|string
 */
if (!function_exists('addons_url')) {
    function addons_url($url = '', $param = [], $suffix = true, $domain = false, $platform_id = null)
    {
        $platform_id = $platform_id === null && defined('PLATFORM_ID') ? PLATFORM_ID : $platform_id;
        if (empty($url)) {
            // 生成 url 模板变量
            $addons = request()->module();
            $controller = request()->controller();
            $controller = str_replace('/', '.', $controller);
            $action = request()->action();
        } else {
            $url = Loader::parseName($url, 1);
            $url = parse_url($url);
            $case = config('url_convert');
            $addons = $case ? Loader::parseName($url['scheme']) : $url['scheme'];
            $controller = $case ? Loader::parseName($url['host']) : $url['host'];
            // $action = trim($case ? strtolower($url['path']) : $url['path'], '/');
            $action = trim($url['path'],"/");
            $dstr = preg_replace_callback('/([A-Z]+)/',function($matchs)
            {
                return '_'.strtolower($matchs[0]);
            },$action);
            $action = trim(preg_replace('/_{2,}/','_',$dstr),'_');

            /* 解析URL带的参数 */
            if (isset($url['query'])) {
                parse_str($url['query'], $query);
                $param = array_merge($query, $param);
            }
        }
        // $platform_id = defined("PLATFORM_ID") && !empty(PLATFORM_ID) ? PLATFORM_ID."/" : "";
        $platform_id = !empty($platform_id) ? $platform_id."/" : "";
        return url("/addons/{$platform_id}{$addons}.{$controller}/{$action}", $param, $suffix, $domain);
    }
}
