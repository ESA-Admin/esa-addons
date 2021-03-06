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

use app\common\library\Auth;

/**
 * 插件基类
 * Class AddnsHook
 * @author 猪在天上飞 <root@bug-maker.com>
 * @package think\addons
 */
abstract class AddonsHook
{
    protected $admin = false;
    protected $auth = null;
    public function __construct()
    {
        $this->admin = session("admin_info");
        $this->auth = Auth::instance();
        $this->auth->init($this->auth->getClientToken());
    }
}
    
