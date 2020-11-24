# ollie_frame

# 一个手动构建的现代化PHP框架(当然只是为了学习)

通过一步步搭建框架的每个部分,了解框架内部基本的实现原理,尝试编写相关代码去实现功能,或者引用别人写好的第三方包,
查看其源代码了解其实现原理，下面开始一步步介绍框架的各个组成。

## 单一入口
跟正常的现代化框架一样,所有请求通过单一入口进入,文件位于./index.php文件中

## 服务容器

参考[DennisRitche/php-base-container](https://gitee.com/obamajs/php-base-container)
深入理解了相关服务容器的核心,个人理解核心是通过添加$bind,绑定类的实现或类的映射,若绑定类的映射,
服务容器会通过反射类完成对类的实例化过程。

容器类代码位于./core/Container.php文件

核心方法为get(),bind(),下面为相关代码,其中bind()方法为绑定对象,向容器中添加类方法的映射或实例闭包,
方便后续get()方法获取类的实例。get()方法用于获取已绑定在$bind变量中的类,并将其返回实例化,
其中$is_singleton可指定为单例对象,若为true,会将该类添加到$instances变量中,后续再调用该类时,直接从$instances
变量中获取,类还是之前那个类。

```
    //获取指定类的实例
    public function get($name,$real_args = [])
    {
        //检查实例是否存在,已存在则直接返回
        if (isset($this->instances[$name])){
            return $this->instances[$name];
        }
        //检查是否绑定该类和当前类是否存在
        if (!isset($this->binds[$name]) && !isset($this->instances[$name])){
            if (!class_exists($name,true)){
                throw new \InvalidArgumentException('class not exists');
            }
        }
        if (isset($this->binds[$name])){
            if (is_callable($this->binds[$name]['concrete'])){
                $instance = $this->call($this->binds[$name]['concrete'],$real_args);
            }else{
                $instance = $this->build($name,$real_args);
            }
        }else{
            $instance = $this->build($name,$real_args);
        }

        //是否为单例,将其对象添加到绑定数组中
        if ($this->binds[$name]['is_singleton'] = true){
            $this->instances[$name] = $instance;
        }
        return $instance;

    }

    //将对象名和创建对象的闭包添加到绑定对象数组
    public function bind($name,$concrete,$is_singleton = false)
    {
        if ($concrete instanceof \Closure) {
            $this->binds[$name] = ['concrete' => $concrete, "is_singleton" => $is_singleton];
        } else {
            if (!is_string($concrete) || !class_exists($concrete, true)) {
                throw new \InvalidArgumentException("value must be callback or class name");
            }
        }

        $this->binds[$name] = ['concrete' => $concrete, "is_singleton" => $is_singleton];
    }

```

## 服务提供者

这里引入服务提供者的概念,每个服务添加一个服务提供者,服务提供者基础一个统一继承接口./core/providers/ServiceProviderInterface.php

实现接口方法,注册服务register();启用服务boot()

其中主要实现register方法,去实现bind()方法,向容器中添加绑定具体实现类

```
class ConfigServiceProvider implements ServiceProviderInterface
{
    //注册服务
    public function register()
    {
        app()->bind('config',function (){
            return new Config();
        },true);
    }

    //加载服务
    public function boot()
    {
        app('config');
    }

}
```

## 定义全局函数

在composer.json中添加自动加载文件,如下添加配置

```

"autoload": {
        "files": [
            "./helper.php"
        ]
   },
```

然后执行composer auto-dumpload

如下为我添加的一个可全局使用的函数.函数作用返回容器实例或容器服务实例
```
if (!function_exists('app')){
    //获取app容器服务
    function app($name = null){
        if (!$name){
            return \core\Container::getContainer();
        }
        return \core\Container::getContainer()->get($name);
    }
}
```

## 添加信息调试工具

通过命令` composer require symfony/var-dumper`引入第三方包

这样就可以在项目中,跟laravel框架一样使用dd(),dump()等函数打印调试

## 添加配置

在系统中封装一个config()全局函数,实现类似laravel的config目录下添加配置,并添加.env文件实现对私密信息的隐藏封装

创建./config目录,在目录下创建app.php,文件内容同laravel的config目录下文件保持一致,使用如下直接return

```
<?php

return [
    'name' => 'ollie',
    'db' => [
        'name' => 'test'
    ],
    //服务提供者
    'providers' => [
        \core\providers\RoutingServiceProvider::class,
        \core\providers\ViewServiceProvider::class,
        \core\providers\ResponseServiceProvider::class,
        \core\providers\RequestServiceProvider::class,
        \core\providers\LogServiceProvider::class,
        \core\providers\DBServiceProvider::class
    ]

];

```

在./core目录下创建Config.php文件,创建config类,类主要功能是对配置文件的获取,通过get()函数获取config目录下,各个文件配置的获取
,如获取app.php文件下的name,应传入参数get('app.name'),获取db.name,则传入get('app.db.name')


定义全局函数,便于通过config('app.name')这样的方式获取配置
```
if (!function_exists('config')){
    //获取配置文件信息
    function config($name = null){
        if (!$name){
            return null;
        }
        return app('config')->get($name);
    }
}
```

## 引入.env配置

[vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)

通过引用`composer require vlucas/phpdotenv`第三方包

在./core/config.php文件中引入,如下并创建.env文件

```
public function __construct()
{
    //加载env文件
    $dotenv = Dotenv::createImmutable(FRAME_BASE_PATH);
    $dotenv->load();
}
```

在查看其第三方源码过程中,研究发现其主要实现是通过./vendor/vlucas/phpdotenv/src/Parser/Parser.php中的
parse()函数,其中$content为.env文件内容,将读取的.env文件内容通过正则表达式解析返回
```
public function parse(string $content)
{
    return Regex::split("/(\r\n|\n|\r)/", $content)->mapError(static function () {
        return 'Could not split into separate lines.';
    })->flatMap(static function (array $lines) {
        return self::process(Lines::process($lines));
    })->mapError(static function (string $error) {
        throw new InvalidFileException(\sprintf('Failed to parse dotenv file. %s', $error));
    })->success()->get();
}
```

然后通过./vendor/vlucas/src/Loader/Loader.php中的load()函数将$_ENV设置成.env中添加的变量值
```
public function load(RepositoryInterface $repository, array $entries)
{
    return \array_reduce($entries, static function (array $vars, Entry $entry) use ($repository) {
        $name = $entry->getName();
        $value = $entry->getValue()->map(static function (Value $value) use ($repository) {
            return Resolver::resolve($repository, $value);
        });
        if ($value->isDefined()) {
            $inner = $value->get();
            if ($repository->set($name, $inner)) {
                return \array_merge($vars, [$name => $inner]);
            }
        } else {
            if ($repository->clear($name)) {
                return \array_merge($vars, [$name => null]);
            }
        }

        return $vars;
    }, []);
}
```
最终结果呢,是调用了./vendor/vlucas/phpdotenv/src/Repository/Adapter/EnvConstAdapter.php文件中的write()方法。

```
public function write(string $name, string $value)
{
    $_ENV[$name] = $value;

    return true;
}
```

定义全局函数,由于env()函数命名与第三方包命名有冲突,这里封装env1()函数,便于全局使用.env文件配置
```
if (!function_exists('env1')){
    //获取env配置文件信息
    function env1($name = null,$default = null){
        if (!$name){
            return null;
        }
        if (isset($_ENV[$name])){
            return $_ENV[$name];
        }
        return $default;
    }
}
```

## 请求

通过引用`composer require laminas/laminas-diactoros`第三方包,将request请求封装成一个对象操作

添加./core/providers/RequestServiceProvider请求服务提供者

```

use Laminas\Diactoros\ServerRequestFactory;
...
...
//注册服务
public function register()
{
    app()->bind('request',function (){
        return ServerRequestFactory::fromGlobals(
            $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
        );
    },true);
}
```

该扩展包内部代码的大致操作就是将$_SERVER,$_GET,$_POST,$_COOKIE,$_FILES等相关超全局变量赋值给类中的相应变量

以下为打印返回的request变量信息,可方便后续在开发过程中,通过该第三方包对请求的封装,对request进行统一处理
```
Laminas\Diactoros\ServerRequest {#38
  -attributes: []
  -cookieParams: []
  -parsedBody: array:2 [
    "a" => "1213"
    "b" => "2132"
  ]
  -queryParams: array:1 [
    "id" => "121"
  ]
  -serverParams: array:41 [
    "USER" => "tengtengcai"
    "HOME" => "/Users/tengtengcai"
    "HTTP_CONTENT_LENGTH" => "22685"
    "HTTP_CONTENT_TYPE" => "multipart/form-data; boundary=--------------------------962405142584130369455706"
    "HTTP_CONNECTION" => "keep-alive"
    "HTTP_ACCEPT_ENCODING" => "gzip, deflate, br"
    "HTTP_HOST" => "ollie.test"
    "HTTP_POSTMAN_TOKEN" => "98c248ae-5d5f-45dd-879c-5ac2a788fb6a"
    "HTTP_CACHE_CONTROL" => "no-cache"
    "HTTP_ACCEPT" => "*/*"
    "HTTP_USER_AGENT" => "PostmanRuntime/7.26.5"
    "REDIRECT_STATUS" => "200"
    "SERVER_NAME" => "ollie.test"
    "SERVER_PORT" => "80"
    "SERVER_ADDR" => "127.0.0.1"
    "REMOTE_PORT" => "55592"
    "REMOTE_ADDR" => "127.0.0.1"
    "SERVER_SOFTWARE" => "nginx/1.17.1"
    "GATEWAY_INTERFACE" => "CGI/1.1"
    "SERVER_PROTOCOL" => "HTTP/1.1"
    "DOCUMENT_ROOT" => "/Users/tengtengcai/sites/ollie"
    "DOCUMENT_URI" => "/Users/tengtengcai/.composer/vendor/laravel/valet/server.php"
    "REQUEST_URI" => "/?id=121"
    "SCRIPT_NAME" => "/index.php"
    "SCRIPT_FILENAME" => "/Users/tengtengcai/sites/ollie/index.php"
    "CONTENT_LENGTH" => "22685"
    "CONTENT_TYPE" => "multipart/form-data; boundary=--------------------------962405142584130369455706"
    "REQUEST_METHOD" => "POST"
    "QUERY_STRING" => "id=121"
    "FCGI_ROLE" => "RESPONDER"
    "PHP_SELF" => "/"
    "REQUEST_TIME_FLOAT" => 1606125093.5989
    "REQUEST_TIME" => 1606125093
  ]
  -uploadedFiles: array:1 [
    "c" => Laminas\Diactoros\UploadedFile {#42
      -clientFilename: "u=1035415831,1465727770&fm=26&gp=0.jpg"
      -clientMediaType: "image/jpeg"
      -error: 0
      -file: "/private/var/tmp/phpqeRBwc"
      -moved: false
      -size: 22244
      -stream: null
    }
  ]
  -method: "POST"
  -requestTarget: null
  -uri: Laminas\Diactoros\Uri {#35
    #allowedSchemes: array:2 [
      "http" => 80
      "https" => 443
    ]
    -scheme: "http"
    -userInfo: ""
    -host: "ollie.test"
    -port: null
    -path: "/"
    -query: "id=121"
    -fragment: ""
    -uriString: null
  }
  #headers: array:9 [
    "content-length" => array:1 [
      0 => "22685"
    ]
    "content-type" => array:1 [
      0 => "multipart/form-data; boundary=--------------------------962405142584130369455706"
    ]
    "connection" => array:1 [
      0 => "keep-alive"
    ]
    "accept-encoding" => array:1 [
      0 => "gzip, deflate, br"
    ]
    "host" => array:1 [
      0 => "ollie.test"
    ]
    "postman-token" => array:1 [
      0 => "98c248ae-5d5f-45dd-879c-5ac2a788fb6a"
    ]
    "cache-control" => array:1 [
      0 => "no-cache"
    ]
    "accept" => array:1 [
      0 => "*/*"
    ]
    "user-agent" => array:1 [
      0 => "PostmanRuntime/7.26.5"
    ]
  ]
  #headerNames: array:9 [
    "content-length" => "content-length"
    "content-type" => "content-type"
    "connection" => "connection"
    "accept-encoding" => "accept-encoding"
    "host" => "host"
    "postman-token" => "postman-token"
    "cache-control" => "cache-control"
    "accept" => "accept"
    "user-agent" => "user-agent"
  ]
  -protocol: "1.1"
  -stream: Laminas\Diactoros\PhpInputStream {#33
    -cache: ""
    -reachedEof: false
    #resource: stream resource @10
      timed_out: false
      blocked: true
      eof: false
      wrapper_type: "PHP"
      stream_type: "Input"
      mode: "rb"
      unread_bytes: 0
      seekable: true
      uri: "php://input"
      options: []
    }
    #stream: "php://input"
  }
}
```

## 路由系统

实际上路由系统,请求,响应等功能都是使用这里推荐的[route.thephpleague](https://route.thephpleague.com/4.x/usage/)

通过引用`composer require league/route`第三方包路由系统,由于该第三方包对响应进行了限制,只允许返回ResponseInterface接口
所以需要再引入其开发的响应包`composer require laminas/laminas-httphandlerrunner`,相关案例可查看[route.thephpleague](https://route.thephpleague.com/4.x/usage/)

根据官方给的案例,对源码进行分析
```
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

//实例化请求类
$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$router = new League\Route\Router;

//添加一个路由
$router->map('GET', '/', function (ServerRequestInterface $request) : ResponseInterface {
    $response = new Laminas\Diactoros\Response;
    $response->getBody()->write('<h1>Hello, World!</h1>');
    return $response;
});

//路由分发
$response = $router->dispatch($request);

//返回响应
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
```

对于\Legue\Route\Router对象中的map方法,其内部大致流程就是将定义的路由信息存放在一个$routes变量中

```
/**
 * {@inheritdoc}
 */
public function map(string $method, string $path, $handler): Route
{
    $path  = sprintf('/%s', ltrim($path, '/'));
    $route = new Route($method, $path, $handler);

    $this->routes[] = $route;

    return $route;
}
```

再来看看dispatch方法

```

/**
 * {@inheritdoc}
 */
//路由分发,这里限定了传入参数,所以这里传入的参数必须为$request对象
public function dispatch(ServerRequestInterface $request): ResponseInterface
{
    //这里设置了参数。。。具体要干嘛还不确定
    if ($this->getStrategy() === null) {
        $this->setStrategy(new ApplicationStrategy);
    }
    //准备路由,对定义的路由进行解析
    $this->prepRoutes($request);

    /** @var Dispatcher $dispatcher */
    //实例化分发路由对象,设置基本信息,并传入路由信息
    $dispatcher = (new Dispatcher($this->getData()))->setStrategy($this->getStrategy());
    //支持路由中间件的使用,后续再研究吧
    foreach ($this->getMiddlewareStack() as $middleware) {
        if (is_string($middleware)) {
            $dispatcher->lazyMiddleware($middleware);
            continue;
        }

        $dispatcher->middleware($middleware);
    }
    //最重要的一步,执行路由请求分发
    return $dispatcher->dispatchRequest($request);
}
```

再来看看dispatchRequest方法,方法的作用为调度当前路由

```
/**
 * Dispatch the current route
 *
 * @param ServerRequestInterface $request
 *
 * @return ResponseInterface
 */
public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
{
    //获取当前请求方法
    $httpMethod = $request->getMethod();
    //获取当前请求url
    $uri        = $request->getUri()->getPath();
    //匹配当前路由
    $match      = $this->dispatch($httpMethod, $uri);
    //匹配分为三部分,未匹配成功,请求方法不合法,匹配成功,这里主要看匹配成功的情况
    switch ($match[0]) {
        //未匹配成功
        case FastRoute::NOT_FOUND:
            $this->setNotFoundDecoratorMiddleware();
            break;
        //请求方法不合法
        case FastRoute::METHOD_NOT_ALLOWED:
            $allowed = (array) $match[1];
            $this->setMethodNotAllowedDecoratorMiddleware($allowed);
            break;
        //匹配成功
        case FastRoute::FOUND:
            //确保路由定义的handle变量符合规范可以执行
            $route = $this->ensureHandlerIsRoute($match[1], $httpMethod, $uri)->setVars($match[2]);
            //重新路由是否设置了中间件
            $this->setFoundMiddleware($route);
            //添加路由变量作为请求属性
            $request = $this->requestWithRouteAttributes($request, $route);
            break;
    }
    //处理执行handle
    return $this->handle($request);
}

```

这里添加一个RoutingServiceProvider路由服务提供者,创建./routes/web.php文件,将路由的定义写在这里

```
//启用路由服务,这样就可以在web.php中添加路由定义了
public function boot()
{
    $router = app('router');
    foreach ($this->mapRoutes as $route){
        call_user_func($this->$route(),$router);
    }
}


public function mapWebRoutes()
{
    return function ($router){
        require_once 'routes/web.php';
    };
}
```

## 响应

处理响应使用了`composer require laminas/laminas-httphandlerrunner`这个配套的第三方包,上述的路由系统返回结果依赖于
ResponseInterface这个接口,返回结果必须为继承ResponseInterface的对象,所以在添加路由闭包或者路由到控制器都必须返回该响应对象

添加ResponseServiceProvider响应服务提供者,将response对象绑定到容器当中。

这里对返回响应做了一层封装,添加了一个全局函数,其中$data为响应内容,$status为响应状态码,这样就可以简化代码,方便完成响应

```
if (!function_exists('response')){
    //返回响应
    function response($data,$status = 200){
        app('response')->getBody()->write($data);
        return app('response')->withStatus($status);
    }
}
```

## 视图

这里使用了laravel的模版引擎,参考了[V视图实现(Laravel Blade引擎)](https://www.kancloud.cn/learnku_/framework/1835178)

通过引入`composer require duncan3dc/blade`

添加视图配置./config/view.php

```

<?php

return [

    // 模板缓存路径
    'cache_path' => FRAME_BASE_PATH . '/resource/views/cache',

    // 模板的根目录
    'view_path' => FRAME_BASE_PATH . '/resource/views/'
];
```

添加视图核心类./core/View.php

```

use duncan3dc\Laravel\BladeInstance;

class View
{
    protected $template;

    public function __construct()
    {
        // 设置视图路径和缓存路径
        $this->template = new BladeInstance(config('view.view_path'), config('view.cache_path'));
    }

    // 传递路径和参数
    public function render($path, $params = [])
    {
        return $this->template->render($path, $params);
    }

}
```

然后再添加ViewServiceProvider视图服务提供者,将view对象绑定到容器当中

由于之前使用的路由系统,我们视图返回的内容必须ResponseInterface对象,所以这里对view返回结果进行封装,这样
视图的使用就跟laravel基本保存一致啦。

```
if (!function_exists('view')){
    //渲染视图
    function view($path,$params = []){
        $view = app('view')->render($path,$params);
        app('response')->getBody()->write($view);
        return app('response');
    }
}
```

## 日志

这里写了一个比较简单的日志实现类

添加日志配置./config/log.php

```
return [
    //默认渠道
    'default' => 'single',

    'channel' => [

        'single' => [
            //日志驱动为文件
            'driver' => 'file',
            'path' => FRAME_BASE_PATH.'/storage/logs/ollie.log'
        ],
        'daily' => [
            'driver' => 'file',
            'path' => FRAME_BASE_PATH.'/storage/logs/'.date('Y-m-d').'.log'
        ]
    ]

];
```

这里需要为日志创建相关的目录./storage/logs目录

添加日志核心类./core/Log.php,代码如下,实现比较简单,就是向文件写入信息。并添加了固定渠道和根据日期写入渠道

```
use core\logDriver\file;

class Log
{
    //日志渠道
    protected $channel;
    //日志驱动
    protected $driver;
    //路径
    protected $path;
    //当前日志实体类
    protected $instance;

    public function __construct()
    {
        $this->channel = config('log.default');
        $this->driver = config('log.channel.'.$this->channel.'.driver');
        $this->path = config('log.channel.'.$this->channel.'.path');
        $this->getDriverInstance();
    }

    //重新定义日志渠道
    public function channel($name = null)
    {
        if (!$name){
            $this->channel = config('log.default');
            $this->driver = config('log.channel.'.$this->channel.'.driver');
            $this->path = config('log.channel.'.$this->channel.'.path');
        }else{
            $this->channel = $name;
            $this->driver = config('log.channel.'.$this->channel.'.driver');
            $this->path = config('log.channel.'.$this->channel.'.path');
        }
        $this->getDriverInstance();
        return $this;
    }

    //获取日志驱动实体类
    public function getDriverInstance()
    {
        if ($this->driver == 'file'){
            $this->instance = new file();
        }
    }

    public function info($message)
    {
        if ($this->driver == 'file'){
            $this->instance->info($message,$this->path);
        }
    }
}
```