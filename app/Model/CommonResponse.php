<?php
/**
 * This model will be used to generate response for api, as well as return object for function need to return more than single object
 */
namespace App\Model;

use App\Helpers\Http\Response\MyResponse;

class CommonResponse
{
    /** @var bool */
    public $success;

    /** @var string */
    public $message;

    /** @var mixed */
    public $data;

    /** @var integer */
    public $code;

    /** @var array */
    public $errors;

    /**
     * CommonResponse constructor.
     * @param int $code
     * @param mixed $dataOrError if code is success then it's data. Otherwise, it's error
     * @param string $message
     */
    public function __construct(int $code, $dataOrError = null, $message = null)
    {
        $this->code = $code;
        $this->success = ($code >= 200 && $code < 300);
        if ($message) {
            $this->message = $message;
        } else if (!$this->success) {
            $this->message = $this->getErrorMessage($this->code);
        } else {
            $this->message = "Success";
        }
        if ($this->success) {
            $this->data = $dataOrError;
        } else {
            $this->errors = $dataOrError;
        }
    }

    public static function from(\stdClass $object)
    {
        if (isset($object->code)) {
            $response = new CommonResponse($object->code);

            if (isset($object->success)) {
                $response->success = $object->success;
            } else {
                $response->success = false;
            }

            if (isset($object->message)) {
                $response->message = $object->message;
            }

            if (isset($object->data)) {
                $response->data = $object->data;
            }

            if (isset($object->errors)) {
                $response->errors = $object->errors;
            }

            return $response;
        }
        return new CommonResponse(500);
    }

    public static function fromModel($modelData)
    {
        if ($modelData == null) {
            return new CommonResponse(404);
        }
        return new CommonResponse(200, $modelData);
    }

    public function toMyResponse()
    {
        return (new MyResponse)->response(
            $this->success ? $this->data : $this->errors,
            $this->success ? 1 : 0,
            $this->message,
            $this->code);
    }

    public function isSuccess()
    {
        return ($this->code >= 200 && $this->code < 300);
    }

    private function getErrorMessage($statusCode)
    {
        switch ($statusCode) {
            case 403:
                return "Unauthorized action";
            case 404:
                return "Object not found";
            case 402:
                return "Unprocessable entity";
            case 500:
                return "Internal server error";
            default:
                return "Error";
        }
    }

}