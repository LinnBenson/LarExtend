<?php

namespace LarExtend\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * 请求验证异常处理
 * 为 JSON 和 API 请求返回统一错误响应。
 */
class ValidationExceptionHandler {
    /**
     * 渲染验证失败响应
     * @param ValidationException $exception 验证异常
     * @param Request $request 当前请求
     * @return JsonResponse|null 返回 null 时继续默认处理
     */
    public function __invoke( ValidationException $exception, Request $request ): ?JsonResponse {
        if ( !$request->expectsJson() && !$request->is( 'api/*' ) ) { return null; }

        return config( 'lar.exceptions.validation' )(
            2,
            $exception->validator->errors()->all(),
            null,
            $exception->status
        );
    }
}