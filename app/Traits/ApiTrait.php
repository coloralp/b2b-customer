<?php

namespace App\Traits;

use App\Enums\PermissionEnum;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Http\Response;

trait ApiTrait
{

    public function changRoute(string $collection, $process = "Listelemek"): JsonResponse
    {
        $response = [
            'error' => 1,
            'message' => "$process için $collection/all post metodunu deneyin!",
        ];

        return response()->json($response, 404);
    }

    public function exceptionResponse(\Exception $exception): JsonResponse
    {
        $response = [
            'error' => 1,
            'messages' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'code' => $exception->getCode(),
        ];

        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function exceptionThrowable(Throwable $exception): JsonResponse
    {
        $response = [
            'error' => 1,
            'messages' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'code' => $exception->getCode(),
        ];

        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function apiSuccessResponse($data = null, $statuCode = Response::HTTP_OK, $message = null): JsonResponse
    {
        $basicData = ['error' => false];

        if ($data) {
            $basicData['data'] = $data;
        }

        if ($message) {
            $basicData['messages'] = $message;
        }

        return response()->json($basicData, $statuCode);
    }

    public function returnWithMessage($message, $error = 1, $statu = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        return response()->json([
            'error' => $error,
            'messages' => $message,
        ], $statu);
    }

    public function returnWithMessageData($message, mixed $data = null, $error = 1, $statu = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        $errorResponse = [
            'error' => $error,
            'messages' => $message,
        ];

        if (!is_null($data)) {
            $errorResponse = array_merge($errorResponse, ['data' => $data]);
        }
        return response()->json($errorResponse, $statu);
    }

    public function validatorFails($errors, $error = 1, $statu = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        return response()->json([
            'error' => $error,
            'messages' => $errors,
        ], $statu);
    }

    public function apiRequestError(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => true,
            'errors' => $validator->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    public function generateUniqueRandomNumber($length): string
    {
        $number = '';
        do {
            $number = str_pad(random_int(0, (int)pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        } while (User::where('code', $number)->exists());

        return $number;
    }

    public function numberFormat($item): float
    {
        $string = (number_format((float)$item, 2, '.', ','));

        return (float)$string;
    }

    public function checkPermission(User $user, PermissionEnum $permissionEnum)
    {
        if (!$user->hasPermissionTo($permissionEnum)) {
            $permissionName = $permissionEnum->value;

            return response()->json([
                'message' => "$permissionName yetkisine sahip değilsiniz",
                'error' => 1
            ], Response::HTTP_FORBIDDEN);
        }
    }

    public function abortMessage($message = null): JsonResponse
    {
        $message = $message ?? 'burası için gerekli yetkiye sahip değilsiniz';
        return response()->json([
            'message' => $message,
            'error' => 1
        ], Response::HTTP_FORBIDDEN);

    }
}
