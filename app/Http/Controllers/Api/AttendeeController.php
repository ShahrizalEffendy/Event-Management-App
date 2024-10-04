<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AttendeeController extends Controller implements HasMiddleware
{

    use CanLoadRelationships;

    private array $relations = ['user','attendees','attendees.user'];

    public static function middleware()
    {
        return collect([
            new Middleware('auth:sanctum', except: ['index', 'show']),
            new Middleware('throttle:60,1', only: ['store','destroy'])
        ]);
    }

    public function index(Event $event)
    {
        $this->authorize('viewAny', Attendee::class);
        $attendees = $this->loadRelationships($event->attendees()->latest());

        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('create', Attendee::class);
        $attendee = $this->loadRelationships(
            $event->attendees()->create([
                'user_id' => $request->user()->id
            ])
        );
            
        return new AttendeeResource($attendee);
    }

    public function show(Event $event, Attendee $attendee)
    {
        $this->authorize('view', Attendee::class);
        return new AttendeeResource( 
            $this->loadRelationships($attendee
        ));
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(Event $event, Attendee $attendee)
    {
        $this->authorize('delete', [$event, $attendee]);
        $attendee->delete();

        return response(status: 204);
    }
}
