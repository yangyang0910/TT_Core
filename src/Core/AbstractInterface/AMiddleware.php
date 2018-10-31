<?php
/**
 * Created by PhpStorm.
 * User: yangcai
 * Date: 2018/8/21
 * Time: 17:49
 */

namespace Core\AbstractInterface;

use Core\Http\Request as HttpRequest;
use Core\Http\Response as HttpResponse;

/**
 * 中间件
 *
 * @method $this setHttpRequest(HttpRequest $request)
 * @method $this setHttpResponse(HttpResponse $response)
 * @method HttpRequest getHttpRequest()
 * @method HttpResponse getHttpResponse()
 *
 * Class AMiddleware
 * @package Core\AbstractInterface
 */
abstract class AMiddleware
{
    use TSingleton;
    use TBaseAbstract;

    /**
     * @var HttpRequest
     */
    protected $httpRequest;
    /**
     * @var HttpResponse
     */
    protected $httpResponse;
}