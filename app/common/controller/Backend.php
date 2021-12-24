<?php

namespace app\common\controller;

use think\App;
use app\BaseController;
use think\facade\Config;
use think\captcha\facade\Captcha;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\Lang;
use think\facade\Event;
use think\facade\Session;
use think\facade\View;
use think\exception\ValidateException;


/**
 * 后台控制器基类
 */
class Backend extends BaseController
{

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 布局模板
     * @var string
     */
    protected $layout = 'default';

    /**
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;

    /**
     * 模型对象
     * @var \think\Model
     */
    protected $model = null;

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id';

    /**
     * 是否是关联查询
     */
    protected $relationSearch = false;

    /**
     * 是否开启数据限制
     * 支持auth/personal
     * 表示按权限判断/仅限个人
     * 默认为禁用,若启用请务必保证表中存在admin_id字段
     */
    protected $dataLimit = false;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 数据限制开启时自动填充限制字段值
     */
    protected $dataLimitFieldAutoFill = true;

    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = false;

    /**
     * 是否开启模型场景验证
     */
    protected $modelSceneValidate = false;

    /**
     * Multi方法可批量修改的字段
     */
    protected $multiFields = 'status';

    /**
     * Selectpage可显示的字段
     */
    protected $selectpageFields = '*';

    /**
     * 前台提交过来,需要排除的字段数据
     */
    protected $excludeFields = "";

    /**
     * 导入文件首行类型
     * 支持comment/name
     * 表示注释或字段名
     */
    protected $importHeadType = 'comment';

    /**
     * 引入后台控制器的traits
     */
    //use \app\admin\library\traits\Backend;
    use \app\common\traits\Jump;
    use \app\common\traits\Curd;

    /**
     * @param App $app
     */
    public function initialize()
    {
        parent::initialize();
        $controller = parse_name($this->request->controller(),1);
        $controller = strtolower($controller);
        
        //$modulename = $this->request->module();
        /*$controllername = Loader::parseName($this->request->controller());
        $actionname = strtolower($this->request->action());

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;*/

        // 定义是否Addtabs请求
        !defined('IS_ADDTABS') && define('IS_ADDTABS', input("addtabs") ? true : false);

        // 定义是否Dialog请求
        !defined('IS_DIALOG') && define('IS_DIALOG', input("dialog") ? true : false);

        // 定义是否AJAX请求
        !defined('IS_AJAX') && define('IS_AJAX', $this->request->isAjax());

        // 检测IP是否允许
        // check_ip_allowed();
        /*
        $this->auth = Auth::instance();

        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //检测是否登录
            if (!$this->auth->isLogin()) {
                Hook::listen('admin_nologin', $this);
                $url = Session::get('referer');
                $url = $url ? $url : $this->request->url();
                if (in_array($this->request->pathinfo(), ['/', 'index/index'])) {
                    $this->redirect('index/login', [], 302, ['referer' => $url]);
                    exit;
                }
                $this->error(__('Please login first'), url('index/login', ['url' => $url]));
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法是否有对应权限
                if (!$this->auth->check($path)) {
                    Hook::listen('admin_nopermission', $this);
                    $this->error(__('You have no permission'), '');
                }
            }
        }

        // 非选项卡时重定向
        if (!$this->request->isPost() && !IS_AJAX && !IS_ADDTABS && !IS_DIALOG && input("ref") == 'addtabs') {
            $url = preg_replace_callback("/([\?|&]+)ref=addtabs(&?)/i", function ($matches) {
                return $matches[2] == '&' ? $matches[1] : '';
            }, $this->request->url());
            if (Config::get('url_domain_deploy')) {
                if (stripos($url, $this->request->server('SCRIPT_NAME')) === 0) {
                    $url = substr($url, strlen($this->request->server('SCRIPT_NAME')));
                }
                $url = url($url, '', false);
            }
            $this->redirect('index/index', [], 302, ['referer' => $url]);
            exit;
        }

        // 设置面包屑导航数据
        $breadcrumb = [];
        if (!IS_DIALOG && !config('fastadmin.multiplenav') && config('fastadmin.breadcrumb')) {
            $breadcrumb = $this->auth->getBreadCrumb($path);
            array_pop($breadcrumb);
        }
        $this->view->breadcrumb = $breadcrumb;

        // 如果有使用模板布局
        if ($this->layout) {
            $this->view->engine->layout('layout/' . $this->layout);
        }

        // 语言检测
        $lang = strip_tags($this->request->langset());

        $site = Config::get("site");

        $upload = \app\common\model\Config::upload();*/

        $upload = [];
        $site = ['name'=>"doorAdmin",'version'=>"1.0"];
        $lang = "zh-cn";

        // 上传信息配置后
        Event::trigger('upload_config_init', $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'indexurl', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            // 'modulename'     => $modulename,
            //'controllername' => $controllername,
            //'actionname'     => $actionname,
            //'jsname'         => 'backend/' . str_replace('.', '/', $controllername),
            //'moduleurl'      => rtrim(url("/{$modulename}", '', false), '/'),
            'language'       => $lang,
            'referer'        => Session::get("referer")
        ];
        
        $config = array_merge($config, Config::get("view_replace_str"));

        Config::set(array_merge(Config::get('upload'), $upload), 'upload');

        // 配置信息后
        Event::trigger('config_init', $config);
        //加载当前控制器语言包
        //$this->loadlang($controllername);
        //渲染站点配置
        View::assign('site', $site);
        //渲染配置信息
        View::assign('config', $config);
        //渲染权限对象
        View::assign('auth', $this->auth);
        //渲染管理员对象
        View::assign('admin', Session::get('admin'));
    }


    /**
     * @return \think\Response
     * 验证码
     */
    public function verify()
    {
        return Captcha::create();
    }

    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        try {
            parent::validate($data, $validate, $message, $batch);
            $this->checkToken();
        } catch (ValidateException $e) {
            $this->error($e->getMessage());
        }
        return true;
    }

    /**
     * 检测token 并刷新
     *
     */
    protected function checkToken()
    {
        $check = $this->request->checkToken('__token__', $this->request->param());
        if (false === $check) {
            $this->error(lang('Token verify error'));
        }
    }

    /**
     * 刷新Token
     */
    protected function token()
    {
        $check = $this->request->checkToken('__token__', $this->request->param());
        if (false === $check) {
            $this->error(lang('Token verify error'));
        }

        //刷新Token
        $this->request->buildToken();
    }
}
