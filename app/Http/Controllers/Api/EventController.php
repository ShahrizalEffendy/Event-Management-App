<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EventController extends Controller implements HasMiddleware
{
    use CanLoadRelationships;
    private array $relations = ['user','attendees','attendees.user'];

    public static function middleware()
    {
        return collect([
            new Middleware('auth:sanctum', except: ['index', 'show']),
            new Middleware('throttle:60,1', only: ['store','update','destroy'])
        ]);
    }

    public function index()
    {
        $this->authorize('viewAny', Event::class);

        return EventResource::collection(Event::with('user')->get());   

        // $query = $this->loadRelationships(Event::query());
        
        // return EventResource::collection(
        //     $query->latest()->paginate()
        // );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
        ...$request->validate([
            'name' => 'required|string|max:255',
            'description'=>'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => $request->user()->id
        ]);

        return new EventResource($this->loadRelationships($event));
    }

    public function show(Event $event)
    {
        $this->authorize('view', Event::class);
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public 
    function update(Request $request, Event $event)
    {
        // if (Gate::denies('update-event',$event)){
        //     abort(403,'You are not authorized to update this event.');
        // // }
        //  Gate::authorize('update-event', $event);
        $this->authorize('update', $event);

        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description'=>'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
                ])
            );

            return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', Event::class);
        $event->delete();

        return response(status: 204);
    }
}

