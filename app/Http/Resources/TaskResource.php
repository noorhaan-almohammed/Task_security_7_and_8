<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'description'    => $this->description,
            'type'           => $this->type,
            'status'         => $this->status,
            'priority'       => $this->priority,
            'due_date'       => $this->due_date,
            'assigned_to'    => new UserResource($this->whenLoaded('assignee')), // Assuming there is a relation for assigned user
            'created_by'     => new UserResource($this->whenLoaded('createdBy')) ,
            'dependencies'   => TaskResource::collection($this->whenLoaded('dependencies')), // Assuming there is a dependencies relation
            'created_at'     => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at'     => $this->updated_at->format('d-m-Y H:i:s'),
            'task_dependencies' => $this->whenPivotLoaded('task_dependencies', function () {
                return [
                    'depends_on' => $this->pivot->depends_on_id,
                ];
            }),
            'comments'       => CommentResource::collection($this->whenLoaded('comments')), // Assuming comments relation
            'attachments'    => AttachmentResource::collection($this->whenLoaded('attachments')) // Assuming attachments relation
        ];
    }
}
