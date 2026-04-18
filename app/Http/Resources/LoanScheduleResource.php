<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Status;
use Carbon\Carbon;

class LoanScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isPaid = $this->payment_exists && $this->status_id === Status::PAID;
        $dueDate = Carbon::parse($this->due_date);

        return [
            'id' => $this->id,
            'total_payment' => (float) $this->total_payment,
            'due_date' => $this->due_date,
            'status_name' => $isPaid ? 'paid' : ($this->status?->name ?? '-'),

            // display purposes
            'due_date_display'    => $dueDate->format('M d, Y'),
        ];
    }
}
