<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => $this->amount,
            'counterparty_user' => new UserResource($this->counterparty_user),
            'created_at' => $this->created_at->toDateTimeString()
        ];
    }
}
