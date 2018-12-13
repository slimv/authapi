<?php

namespace App\Helpers\Http\Response;

/**
 * @SWG\Response(
 *      response="BaseResponse",
 *      description="the basic response",
 *      @SWG\Schema(
 *          @SWG\Property(
 *             property="success",
 *             type="integer",
 *             description="1 if success, 0 if failure",
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *      )
 * )
 */
class MyResponse
{

    /**
     * Function to format the response of all api in the system.
     * Note: All api will be using this format so carefully when edit this function
     *
     * @param  [object]  $data    [Data to be converted to json and attach into the response]
     * @param  integer $isSuccess [1 if success, 0 if failure. True false is not allowed]
     * @param  [string]  $message [Message for this api]
     * @param  integer $code [HTTP response code]
     * @return [json]           [Reponse in json string]
     */
    public function response($data, $isSuccess = 1, $message = null, $code = 200)
    {
        //check the status code
        if ($data || $data == []) {
            $response = [
                "success" => $isSuccess ? 1 : 0,
                "message" => $message,
                "data" => $data
            ];
            return response()->json($response, $code);
        } else if($message) {
            return response($message, $code)->header('Content-Type', 'text/plain');
        } elseif ($code == 400) {
            return $this->badRequest($message);
        } elseif ($code == 401) {
            return $this->unauthorize($message);
        } elseif ($code == 403) {
            return $this->unauthorizeAction($message);
        } elseif ($code == 404) {
            return $this->notFound($message);
        } else {
            return $this->error($message);
        }
    }

    /**
     * Fire the 400 bad request
     * @param  [string] $message [If not set, the default message will be used]
     */
    public function badRequest($message = null)
    {
        $responseMessage = ($message) ? $message : __('Bad Request.');
        return response($responseMessage, 400)->header('Content-Type', 'text/plain');
    }

    /**
     * Fire the 401 unauthorize respose
     * @param  [string] $message [If not set, the default message will be used]
     */
    public function unauthorize($message = null)
    {
        $responseMessage = ($message) ? $message : __('Unauthorize personel cannot access this data.');
        return response($responseMessage, 401)->header('Content-Type', 'text/plain');
    }

    /**
     * Fire the 403 unauthorize action respose
     * @param  [string] $message [If not set, the default message will be used]
     */
    public function unauthorizeAction($message = null)
    {
        $responseMessage = ($message) ? $message : __('Unauthorized action.');
        return response($responseMessage, 403)->header('Content-Type', 'text/plain');
    }

    /**
     * Fire the 404 not found respose
     * @param  [string] $message [If not set, the default message will be used]
     */
    public function notFound($message = null)
    {
        $responseMessage = ($message) ? $message : __('Could not found the request object.');
        return response($responseMessage, 404)->header('Content-Type', 'text/plain');
    }

    /**
     * Fire the 500 unexpected respose
     * @param  [string] $message [If not set, the default message will be used]
     */
    public function error($message = null)
    {
        $responseMessage = ($message) ? $message : __('Unexpected error.');
        return response($responseMessage, 500)->header('Content-Type', 'text/plain');
    }

    /**
     * Return the format when froala upload success
     * @param  [string] $path [upload file path]
     * @return [any]       [response]
     */
    public function froalaUploadResponse($path) {
        $response = [
            "link" => $path
        ];

        return response()->json($response);
    }

}