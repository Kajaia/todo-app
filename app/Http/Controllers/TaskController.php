<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tasks = Task::when($request->search, function($query) use ($request) {
                $query->where('title', 'LIKE', "%$request->search%");
            })
            ->when($request->status, function($query) use ($request) {
                if($request->status === 'active') {
                    $query->where('status', false);
                } elseif($request->status === 'completed') {
                    $query->where('status', true);
                }
            })
            ->orderBy($request->sortBy ?? 'id', $request->sortDirection ?? 'desc')
            ->paginate($request->paginate ?? 10);

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $task = Task::create([
            'title' => $request->title,
            'status' => $request->status
        ]);

        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::findorfail($id);

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = Task::findorfail($id);

        $task->update([
            'title' => $request->title
        ]);

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findorfail($id);

        $task->delete();

        return new TaskResource($task);
    }

    public function doneUndo($id)
    {
        $task = Task::findorfail($id);

        if($task->status) {
            $task->update(['status' => 0]);
        } else {
            $task->update(['status' => 1]);
        }

        return new TaskResource($task);
    }

    public function clearCompleted() {
        $tasks = Task::where('status', true)
            ->delete();

        return [
            'status' => 'cleared'
        ];
    }
}
