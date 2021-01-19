<?php

namespace App\Http\Resources;

use App\Enums\ErrorEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JsonResponse extends ResourceCollection
{
    /**
     * @var int $code
     */
    private int $code;

    /**
     * @var string $msg
     */
    private string $msg;

    /**
     * @var mixed $data
     */
    private $data;

    /**
     * ResponseCollection constructor.
     *
     * @param object|null $resource
     * @param string $collects
     * @param ErrorEnum|null $errorEnum
     */
    public function __construct($resource = null, string $collects = '', ErrorEnum $errorEnum = null)
    {
        $this->code = ErrorEnum::OK()->getCode();
        $this->msg = ErrorEnum::OK()->getMsg();
        if(!is_null($errorEnum)) {
            $this->code = $errorEnum->getCode();
            $this->msg = $errorEnum->getMsg();
        }

        if(!empty($collects) && is_subclass_of($collects, JsonResource::class)) {
            $this->collects = $collects;
        }

        if(empty($resource)) {
            $this->data = new \stdClass();
        } elseif ($resource instanceof Paginator) {
            $this->data = [
                'list' => empty($this->collects) ? $resource->all() : $this->collects::collection($resource),
                'page' => $resource->currentPage(),
                'size' => $resource->perPage(),
            ];
            if($resource instanceof LengthAwarePaginator) {
                $this->data['total'] = $resource->total();
            }
        } elseif ($resource instanceof Collection) {
            $this->data = empty($this->collects) ? $resource->all() : $this->collects::collection($resource);
        } elseif ($resource instanceof Model) {
            $this->data = empty($this->collects) ? $resource : new $this->collects($resource);
        } else {
            $this->data = $resource;
        }
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'msg' => $this->msg,
            'data' => $this->data,
        ];
    }
}
