<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'is_verified' => (bool) $this->is_verified,
            'avatar' => $this->avatar ? asset('uploads/avatars/' . $this->avatar) : null,
            'email_verified' => !is_null($this->email_verified_at),
            'phone_verified' => !is_null($this->phone_verified_at),
        ];
    }
}
