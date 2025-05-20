<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\Attachmentable;
use App\Http\Traits\Response;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    use Response, Attachmentable;

    protected array $taskRelations = [
        'author:id,name,email',
        'users:id,name,email',
        'categories:id,name',
        'attachments:id,url,state,attachmentable_id,attachmentable_type',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 15);

        $query = Task::select('id', 'title', 'status', 'priority', 'completed_at')
            ->with([
            'users:id,name,email',
            'categories:id,name',
        ])
            ->latest();

        if ($request->has('isKanban') && $request->isKanban == 'true') {
            return $this->successResponse(
                $this->responseMessage('Task', 'index'),
                [
                    'results' => [
                        'task' => $query->get()->groupBy('status'),
                    ],
                ]
            );
        }

        $paginated = $query->paginate($perPage);

        return $this->successResponse(
            $this->responseMessage('Task', 'index'),
            [
                'results' => ['task' => $paginated->items()],
                'meta' => [
                    'total' => $paginated->total(),
                    'per_page' => $paginated->perPage(),
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'next_page_url' => $paginated->nextPageUrl(),
                    'prev_page_url' => $paginated->previousPageUrl(),
                    'from' => $paginated->firstItem(),
                    'to' => $paginated->lastItem(),
                ]
            ]
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();


            $validated = $request->validate([
                'title' => 'required|string|max:255|unique:tasks,title',
                'description' => 'required|string|max:1000',
                'priority' => 'required|in:Low,Medium,High',
                'status' => 'required|in:pending,in-progress,hold,review,cancel,completed',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'categories' => 'required|array',
                'categories.*' => 'exists:categories,id',
                'users' => 'required|array',
                'users.*' => 'exists:users,id',
            ]);

            $task = Task::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => $validated['status'],
                'completed_at' => $validated['status'] === 'completed' ? now() : null,
                'user_id' => auth('sanctum')->id(),
            ]);

            if ($files = $this->checkAttachment($request)) {
                $this->handleAttachment($request, $task, 'Task', 'task', $files);
            }

            $task->categories()->attach($validated['categories']);
            $task->users()->attach($validated['users']);

            DB::commit();

            return $this->successResponse(
                $this->responseMessage('Task', 'store'),
                $task,
                201
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse(
                $this->responseMessage('Task', 'validation'),
                $e->validator->errors()->messages()
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Task creation failed. ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return $this->successResponse(
            $this->responseMessage('Task', 'show'),
            $task->load($this->taskRelations)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255|unique:tasks,title,' . $task->id,
                'description' => 'sometimes|required|string|max:1000',
                'priority' => 'nullable|in:Low,Medium,High',
                'status' => 'nullable|in:pending,in-progress,hold,review,cancel,completed',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
                'users' => 'nullable|array',
                'users.*' => 'exists:users,id',
                'deleteAttachmentIds' => 'nullable|array',
                'deleteAttachmentIds.*' => 'exists:attachments,id',
            ]);

            if ($validated['status'] == 'completed') {
                $validated['completed_at'] = now();
            } else {
                $validated['completed_at'] = null;
            }

            $task->update($validated);

            if (!empty($validated['deleteAttachmentIds'])) {
                $this->deleteOldAttachment($validated['deleteAttachmentIds']);
            }

            if ($files = $this->checkAttachment($request)) {
                $this->handleAttachment($request, $task, 'Task', 'task', $files);
            }

            if (isset($validated['categories'])) {
                $task->categories()->sync($validated['categories']);
            }

            if (isset($validated['users'])) {
                $task->users()->sync($validated['users']);
            }

            DB::commit();

            return $this->successResponse(
                $this->responseMessage('Task', 'update'),
                $task
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse(
                $this->responseMessage('Task', 'validation'),
                $e->validator->errors()->messages()
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Task update failed. ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        try {
            DB::beginTransaction();

            $this->deleteAttachment($task);

            $task->categories()->detach();
            $task->users()->detach();
            $task->delete();

            DB::commit();

            return $this->successResponse(
                $this->responseMessage('Task', 'destroy'),
                null
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Task deletion failed. ' . $e->getMessage(), [], 500);
        }
    }
}
