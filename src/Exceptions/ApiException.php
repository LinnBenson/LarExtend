<?php

namespace LarExtend\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * API 业务异常
 * 使用统一 JSON 格式返回业务错误。
 */
class ApiException extends HttpException {
    /**
     * 创建 API 异常
     * @param string $message 错误消息
     * @param int $statusCode HTTP 状态码
     * @param array|null $data 附加数据
     */
    public function __construct( string $message, int $statusCode = 400, private readonly ?array $data = null ) {
        parent::__construct( $statusCode, $message );
    }

    /**
     * 渲染异常响应
     * @param Request $request 当前请求
     * @return JsonResponse JSON 错误响应
     */
    public function render( Request $request ): JsonResponse {
        return config( 'lar.exceptions.api' )(
            2,
            $this->getMessage(),
            $this->data,
            $this->getStatusCode(),
            $this->getHeaders()
        );
    }
}