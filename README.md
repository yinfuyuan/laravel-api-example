## 概述

Laravel是一个非常流行的PHP框架，我们可以使用它快速构建一个WEB应用程序。而现在WEB应用中多会采用前后端分离技术，所以我们经常会遇到使用Laravel搭建API项目的需求。
Laravel在提供API这方面，有很多地方都只提供了一个规范，并没有告诉我们如何去实现它。这样带来的好处是Laravel放开了限制，使大家可以按照自己的习惯去使用它。
但这样做也给刚接触Laravel不久的同学带来了一些困扰：到底怎样使用这个框架才更优雅些呢，有没有例子可以参考下呢。

这个项目就是为了解决这个问题的，这里会带着大家打造一个Laravel API项目，当然这算不上最优雅的实现方式，只是为大家提供一个参考。

需要注意的是，这并不是一个Laravel的入门文档，如果你还不了解Laravel，请移步 [Laravel官网](https://laravel.com/docs)

## 安装

这里使用composer安装Laravel，下面命令会安装最新版本的Laravel。此项目创建时的Laravel版本为v8.21.0

    composer create-project --prefer-dist laravel/laravel laravel-api-example

安装好之后需要自行配置项目使其可以对外访问，当在浏览器中输入项目地址进入到Laravel的欢迎页时，就可以继续阅读了。

## 路由

在欢迎页我们可以看到，Laravel返回的信息是一个web页面，也就是html代码。这个默认的路由是在**routes/web.php**中定义的，我们需要把它给移除掉。[commit](https://github.com/yinfuyuan/laravel-api-example/commit/fa2dbd6b844dc3cf1b4805818c754c308f2135b3)

我们所有的路由都要定义**routes/api.php**中，这个是专门用来定义API路由的文件，当然如果你的路由特别多你也可以在routes中定义其他路由文件，然后在 [RouteServiceProvider](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Providers/RouteServiceProvider.php) 中按照同样的方式去加载它。
在**RouteServiceProvider**中我们可以看到，我们在**routes/api.php**中定义的路由会默认加上api前缀，这对WEB和API混写在同一个项目中很有必要。
但此项目是一个纯API项目，我们通常会对纯API项目使用单独域名，如：

    https://api.apihubs.cn/holiday/get

所以我们要移除这个api前缀或换成其他前缀如接口版本号V1 [commit](https://github.com/yinfuyuan/laravel-api-example/commit/df75964574f9a4e3e36044fbdccbb8fafa1bb38e)

在**routes/api.php**中默认的路由是一个需要身份验证的 **/user**

`我们使用浏览器或postman访问的时候，会得到一个错误页面,其中的主要信息为：Route [login] not defined.`

`我们使用ajax或者在postman的header中添加X-Requested-With:XMLHttpRequest头信息后又会得到一个JSON的错误信息：{"message": "Unauthenticated."}`

这实际上都是未登录的原因，在未登录时访问需要鉴权的接口Laravel会抛出一个**AuthenticationException**，而在响应类**Response**中会根据请求的header头自动做出响应。
也就是如果是以页面形式调用的，就会跳转到登录页面，因为项目中还没定义登录页面的路由就出现我们上面看到的那个错误。如果是以接口形式访问的就会401状态码并返回JSON信息。

但我们这是一个API项目，提供出去的都是接口地址，在接口地址中一会返回JSON一会又返回一个页面这是不是显得很尴尬。

这里我们需要新增一个 [Middleware](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Middleware/JsonApplication.php) 来解决这个问题。然后在 [Kernel](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Kernel.php) 中注册这个Middleware使其全局生效。 [commit](https://github.com/yinfuyuan/laravel-api-example/commit/3423b75e6cd88608415b55e30da9fa98106ed796)

这样我们已经配置好一个JSON应用了，在Laravel抛出任何异常时，无论我们以什么方式访问都会始终得到JSON响应信息。

需要注意的是我们依然不能在路由的闭包或控制器中使用~~return view($view)~~，因为这会强制返回一个html页面响应。
我们应该在路由的闭包或控制器中始终return一个对象或数组，这两种格式会使Laravel自动为我们返回正确的JSON信息。

## 错误码

通过上面的配置，我们的应用可以始终返回JSON信息了，但是返回的JSON格式并不统一。比如

- 鉴权失败回返回http状态码401的 {"message": "Unauthenticated."}
- 请求的method不对会返回http状态码405的 {"message":"The POST method is not supported for this route. Supported methods: GET, HEAD.","exception":"Symfony\\Component\\HttpKernel\\Exception\\MethodNotAllowedHttpException",...}
- 请求的URL地址不存在又会返回404的 {"message":"","exception":"Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",...}

而在实际使用中我们通常需要根据不同的业务场景定义不同的错误代码和错误信息, 然后返会http状态码200的 {"code":"","msg":"","data":""}

在这里我们需要引入一个第三方的库 [phpenum](https://github.com/yinfuyuan/php-enum) [commit]()

    composer require phpenum/phpenum

这是一个枚举库，在这里用来定义和管理错误码。我们在app目录下新建一个**Enums**目录，然后添加 [ErrorEnum](https://github.com/yinfuyuan/laravel-api-example/commit/a79b90a1760780d0dedc6513c69b12b775c148de) 为不同的错误和异常定义不同的错误码
