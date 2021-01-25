## 概述

Laravel 是一个非常流行的 PHP 框架，我们可以使用它快速构建一个 WEB 应用程序。而现在 WEB 应用中多会采用前后端分离技术，所以我们经常会遇到使用 Laravel 搭建 API 项目的需求。
Laravel 在提供 API 这方面，很多地方都只是提供了一个规范，并没有告诉我们如何去实现它。这样带来的好处是 Laravel 放开了限制，使大家可以按照自己的习惯去使用它。
但这样做也给刚接触 Laravel 不久的同学带来了一些困扰：到底怎样使用这个框架才更优雅些呢，有没有例子可以参考下呢。

本项目就是为了给大家提供一个参考而建立的，这是一个使用 Laravel 框架实现的 API 项目，项目中提供了一些常见功能的示例。
本项目从零开始，在重要修改部分末尾都会添加 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commits/main) 链接, 可自行查看变更记录。

需要注意的是：

1. 这并不是一个 Laravel 的新手教程，文中很多地方需要你了解 Laravel 的基础知识。 [Laravel官网文档](https://laravel.com/docs)
2. 如果你已经有自己的实现方式，可以略过此教程，这个项目的实现方式可能也没比你的实现方式更优雅。
3. Laravel 各个版本之前实现方式还是有一些区别的，要注意区分，但整体思路是一样的。

## 安装

这里使用 composer 安装 Laravel ，下面命令会安装最新版本的 Laravel 。此项目创建时的 Laravel 版本为 v8.21.0

    composer create-project --prefer-dist laravel/laravel laravel-api-example

安装好之后需要自行配置项目使其可以对外访问，当在浏览器中输入项目地址进入到 Laravel 的欢迎页时，就可以继续向下阅读了。

## 路由

在欢迎页我们可以看到，Laravel 返回的信息是一个 web 页面，也就是 html 代码。这个默认的路由是在 **routes/web.php** 中定义的，我们需要把它给移除掉。[[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/fa2dbd6b844dc3cf1b4805818c754c308f2135b3)

我们所有的路由都要定义到 **routes/api.php** 中，这个是专门用来定义 API 路由的文件，当然如果你的路由特别多你也可以在 routes 中定义其他路由文件，然后在 [RouteServiceProvider](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Providers/RouteServiceProvider.php) 中按照同样的方式去加载它们。
在 **RouteServiceProvider** 中我们可以看到，我们在 **routes/api.php** 中定义的路由会默认加上 api 前缀，这对 WEB 和 API 混写在同一个项目中很有必要，但单独的 API 项目一般也会单独域名。如：

    https://api.apihubs.cn/holiday/get

所以我们要移除这个 api 前缀或换成其他前缀如接口版本号 V1 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/df75964574f9a4e3e36044fbdccbb8fafa1bb38e)

在 **routes/api.php** 中默认的路由是一个需要身份验证的 **/user**

我们使用浏览器或 postman 访问的时候，会得到一个错误页面,其中的主要信息为：Route [login] not defined.

我们使用 ajax 或者在 postman 的 header 中添加 X-Requested-With:XMLHttpRequest 头信息后又会得到一个 JSON 的错误信息：{"message": "Unauthenticated."}

这实际上都是未登录的原因，在未登录访问需要鉴权的接口时 Laravel 会抛出一个 **AuthenticationException** ，而在响应类 **Response** 中会根据请求的 header 头自动做出响应。
也就是如果是以页面形式调用的，就会跳转到登录页面，因为项目中还没定义登录页面的路由就出现我们上面看到的那个错误。如果是以接口形式访问的就会 401 状态码并返回 JSON 信息。

但我们这是一个 API 项目，提供出去的都是接口地址，在接口地址中一会返回 JSON 一会又返回一个页面这是不是显得很尴尬。

这里我们需要新增一个 [Middleware](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Middleware/JsonApplication.php) 来解决这个问题。然后在 [Kernel](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Kernel.php) 中注册这个 Middleware 使其全局生效。 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/3423b75e6cd88608415b55e30da9fa98106ed796)

    php artisan make:middleware JsonApplication

这样我们已经配置好一个 JSON 应用了，在 Laravel 抛出任何异常时，无论我们以什么方式访问都会始终得到 JSON 响应信息。

需要注意的是我们依然不能在路由的闭包或控制器中使用 ~~return view($view)~~ ，因为这会强制返回一个 html 页面响应。
我们应该在路由的闭包或控制器中始终 return 一个对象或数组，这两种格式会使 Laravel 自动为我们返回正确的 JSON 信息。

## 错误码

通过上面的配置，我们的应用可以始终返回 JSON 信息了。比如在出现异常的时候：

- 鉴权失败会返回 http 状态码 401 的 {"message": "Unauthenticated."}
- 请求的 method 不正确会返回 http 状态码 405 的 {"message":"The POST method is not supported for this route. Supported methods: GET, HEAD.","exception":"Symfony\\Component\\HttpKernel\\Exception\\MethodNotAllowedHttpException",...}
- 请求的 URL 地址不存在会返回 404 的 {"message":"","exception":"Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",...}
- ...

但这样的返回信息也有问题：

1. 返回信息的格式并不统一，JSON 中的 key 时有时无，接口调用方在很多时候找不到要以什么作为依据进行判断
2. 许多异常信息为服务端敏感信息，会直接报漏给用户，存在安全隐患
3. 异常信息都是通过 HTTP 状态码抛出的，会导致许多错误的 HTTP 状态码相同，比如 500

而通常的做法是需要根据不同的业务场景定义不同的错误代码和错误信息, 然后始终返会 http 状态码 200 的 {"code":"","msg":"","data":""}

在这里我们需要引入一个第三方的库（这个库会在项目中许多地方使用，也是本教程的核心，当然这个库的功能是仿照 Java 枚举而来的） [phpenum](https://github.com/yinfuyuan/php-enum) 。 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/34b22459e50a0e93203cd7b647535314ead58914)

    composer require phpenum/phpenum

这是一个枚举库，在这里用来定义和管理错误码和错误信息，错误码的位数应该是固定的，至少一个模块下的错误码位数是固定的，这里使用 5 位错误码，你可以根据实际使用场景来定义。
我们在 app 目录下新建一个 **Enums** 目录，然后添加 [ErrorEnum](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Enums/ErrorEnum.php) 为不同的错误和异常定义不同的错误码。 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/a79b90a1760780d0dedc6513c69b12b775c148de)

定义好错误码后，我们还需要借助 Laravel 的 [渲染异常](https://laravel.com/docs/8.x/errors#rendering-exceptions) 来渲染自定义异常类 [ApiException](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Exceptions/ApiException.php) 。 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/f99e7628f74a156db6c06a1475d343148a567efb)

    php artisan make:exception ApiException

在上面这个 commit 中，我们对常见的异常都做了处理，使他们返回固定的错误码和错误信息，尤其对数据验证失败在 data 中返回了详细的错误信息，你也可以在 [Handler](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Exceptions/Handler.php) 中添加一些其他需要处理的异常。

而未特殊定义状态码的异常会统一返回错误码 99999 的 **未知错误**，生产环境中是不应该出现这个错误码的，这个异常一般出现在调试阶段，我们需要解决掉它。

由于接口不再返回任何错误信息了，我们排查问题的方式也只能通过日志来排查 [默认的日志在你本地的这个目录下](https://github.com/yinfuyuan/laravel-api-example/tree/main/storage/logs) Laravel的日志也是非常强大，你可以随意更改存储的位置和介质，这里就不展开介绍了。

到这里我们就配置好统一错误码了，接下来无论在项目中出现什么错误，抛出什么异常，接口返回的信息始终保持为http状态码200的 {"code":"","msg":"","data":""}

但这些状态码都是系统产生异常时返回的，我们要自己返回自定义状态码要怎么做呢? 非常简单，你只需要在任何你想返回自定义状态码的地方抛出自定义异常就可以了（但除了 controller 层，其他层可能会以非 web 的方式掉用，比如 console ，它不应该捕获到 ApiException ，所以尽量保证在 controller 抛出 ApiException）

    throw new ApiException(ErrorEnum::UNKNOWN_ERROR()); // {"code":99999,"msg":"服务器繁忙，请稍后再试","data":""}
    throw new ApiException(ErrorEnum::UNKNOWN_ERROR(), 'This is an data'); // {"code":99999,"msg":"服务器繁忙，请稍后再试","data":"This is an data"}

那要是返回成功信息要怎么办呢，这个实现方式有很多，可以用官方文档示例中的 [响应宏](https://laravel.com/docs/8.x/responses#response-macros) , 也可以使用帮助类，还可以使用...
这里我们选 Laravel 的 Resource , 新建一个 Resource 类 [JsonResponse](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Resources/JsonResponse.php), 在里面处理了常见的 Laravel 对象和添加分页处理。 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/11667c5ef1c718604048b2ea86ef6796daa35c8a)

这样当我们想返回成功信息时只需要 return 这个实例就可以了。

    return new JsonResponse($mixed);

## 配置信息

Laravel 的配置信息都保存在 [config](https://github.com/yinfuyuan/laravel-api-example/tree/main/config) 中，你也可以自定义自己的配置信息，获取时只需要使用 config('filename.array_key') （支持多层级）就能轻松的获取到配置信息。
Laravel 的配置信息多是搭配了各个服务的门面模式来定义的，就比如 cache ，你只需要在 config 中修改 default driver ，就可以轻松的在 file 或 redis 或 database 等等等诸多存储介质之间切换，你还可以自定义存储介质。关于门面模式你可以自行查看 [文档](https://laravel.com/docs/8.x/facades) 和 [源码](https://github.com/laravel/framework) ，这里不展开介绍了。

这里还有个问题，就是比如数据库配置，一般我们都会区分开发、测试、生产环境，但是 config 中的配置只能在仓库中保存一份，这里我们就要用到另外一个特殊配置文件 **.env**

所有配置文件中以类似于 env('DB_HOST', '127.0.0.1') 这种方式定义的都会读取 .env 文件，第一个参数作为配置名称，如果未在 .env 文件中定义，则使用第二个参数默认值返回

**一般我们会把所有区分环境的配置都定义在这个文件中**

注意这个文件是不能提交到仓库的，所以你拉去代码后很有可能看不到这个文件，只需要将 **.env.example** copy 一份为 **.env** 即可 （首次安装 Laravel 会自动执行 copy） 然后配置好正确的数据库连接信息

## 数据库迁移（生产环境使用需谨慎）

[文档](https://laravel.com/docs/8.x/migrations)

大多数框架都有数据库迁移功能，它对保持数据库结构的一致性起到非常大的作用，但这个功能如果没有合理使用则风险非常大，我之前就有同事使用了这个功能不小心把所有表都给重置了，还好数据是有备份的，及时进行了恢复。
Laravel 的数据迁移文件在 [database/migrations](https://github.com/yinfuyuan/laravel-api-example/tree/main/database/migrations) 中，由于 Laravel 框架是国外开发者开发的，他们对用户的信息是以 email 为主，我们要在 [user](https://github.com/yinfuyuan/laravel-api-example/blob/main/database/migrations/2014_10_12_000000_create_users_table.php) 表中增加手机号码字段。[[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/22ea469dc3c002c6cf775ce9676a74dd06d4773c)

添加完成后要在 [model](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Models/User.php) 添加该字段 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/ccdfcdbed1dc59f215265cfb58d86208f1c185ea)

这里我们是直接对表迁移文件进行修改，是因为我们还没有进行数据库迁移，当你执行过数据库迁移后，Laravel 会在数据库中记录你已经迁移过的文件，这时如果再想修改应使用 [更新表](https://laravel.com/docs/8.x/migrations#updating-tables) 的操作

接下来就可以执行数据库迁移操作了 **（开发环境）**

    php artisan migrate

## 验证规则

Laravel 提供了非常多的 [验证规则](https://laravel.com/docs/8.x/validation#available-validation-rules) ，这些验证规则可以满足大数据的验证场景，部分特殊验证需要我们自定义验证规则，比如手机号码
我们通过以下命令来创建一个 [手机号码的验证规则](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Rules/PhoneNumber.php) [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/bf5973f5efbe6d4c27cb826bdaab1d854e177e8d)

    php artisan make:rule PhoneNumber

添加好规则后我们可以在 [ServiceProvider](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Providers/AppServiceProvider.php) 中为规则配置别名 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/80568ae63780cfc3db21824e847ff76ec2961a6f)

## 参数验证

验证规则一般可以直接写在 controller 中，也可以单独定义 [Requests](https://github.com/yinfuyuan/laravel-api-example/tree/main/app/Http/Requests) 进行管理，这里我们使用第二种方式统一在 Requests 中定义管理验证逻辑。

我们先来创建一个获取验证码的 [Request](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Requests/GetSmsCodeRequest.php) [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/17842364e0f12eae24b58010d1b95582154190b6)

    php artisan make:request GetSmsCodeRequest

Request 中一般我们要在 messages 方法中重新定义错误信息，添加好 Request 后，我们就可以直接在 controller 中使用，结合之前我们的配置，当验证不通过时会返回以下信息

    {
        "code": 10004,
        "msg": "数据验证失败",
        "data": {
            "phone_number": [
                "请输入您的手机号码"
            ]
        }
    }

## 获取验证码

到这里我们的基础配置就完成了，让我们来实际的添加一个接口吧，首先在 **routes/api.php** 中添加一条获取验证码的路由（限制访问频次 30 分钟 100 次） [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/d380c587ea3cabbcd9b6489443da5079656df41c)

    Route::middleware('throttle:100,30')->post('getSmsCode', [AuthController::class, 'getSmsCode']);

然后在添加 [Controller](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Controllers/AuthController.php) [Contract](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Contracts/AuthService.php) [Service](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Services/AuthService.php) 以及在 [ServiceProvider](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Providers/AppServiceProvider.php) 中绑定 Contract 和 Service 的关系 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/97597ae759aefe812b1dc09364a77bf8b580aa00)

    php artisan make:controller AuthController

这种实现方式是参考了 Laravel 的 [自动注入](https://laravel.com/docs/8.x/container#automatic-injection) 和 Laravel 的 [将接口绑定到实现](https://laravel.com/docs/8.x/container#binding-interfaces-to-implementations) 而实现的

**这里为了方便测试，验证码直接在接口中返回，实际使用需要修改为短信发送**

## 注册

按照上面的教程，我们先来添加一个注册的路由 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/455ea84adc91557613fe51e01337e6c5ed456fd0)

    Route::middleware('throttle:100,30')->post('register', [AuthController::class, 'register']);

然后添加一个验证码的 [rule](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Rules/SmsCode.php) 并为其添加别名 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/0111a118e3c12d0e195db054d24847c69a6af5d3)

使用验证码规则创建注册的 [request](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Requests/RegisterRequest.php) [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/6fda807a6a2443e081b6ee37d69c8199c1386734)

最后在添加 [Controller](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Http/Controllers/AuthController.php) [Contract](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Contracts/AuthService.php) [Service](https://github.com/yinfuyuan/laravel-api-example/blob/main/app/Services/AuthService.php) 中的方法 [[commit]](https://github.com/yinfuyuan/laravel-api-example/commit/ded346a74703143db9582664a95d271d27320273)


