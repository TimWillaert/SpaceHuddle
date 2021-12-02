<?php

namespace App\Action\View;

use App\Action\Base\ActionTrait;
use App\Domain\View\Service\ViewSingleReader;
use App\Responder\Responder;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Action for reading the detail data for the view with the specified type and idea.
 *
 * @OA\Get(
 *   path="/view/{type}/{typeId}/",
 *   summary="Detail data for the view with the specified type and id.",
 *   tags={"View"},
 *   @OA\Parameter(in="path", name="type", description="type of the view to return", required=true),
 *   @OA\Parameter(in="path", name="typeId", description="ID of the view to return", required=true),
 *   @OA\Response(response="200", description="Success",
 *     @OA\JsonContent(ref="#/components/schemas/IdeaData"),
 *   ),
 *   @OA\Response(response="404", description="Not Found"),
 *   security={{"api_key": {}}, {"bearerAuth": {}}}
 * )
 */
class ViewReadSingleAction
{
    use ActionTrait;
    protected ViewSingleReader $service;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ViewSingleReader $service The service
     */
    public function __construct(Responder $responder, ViewSingleReader $service)
    {
        $this->setUp($responder);
        $this->service = $service;
        $this->successStatusCode = StatusCodeInterface::STATUS_OK;
    }
}
