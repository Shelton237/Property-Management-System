<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\FrontDeskActions;
use App\Services\FrontDeskBoard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class FrontDeskController extends Controller
{
    public function index(Request $request): Response
    {
        $hotel = $request->user()?->hotel;

        if ($hotel === null) {
            return Inertia::render('front-desk/Index', ['board' => null, 'planner' => null]);
        }

        $board = new FrontDeskBoard($hotel);

        return Inertia::render('front-desk/Index', [
            'board' => $board->today(),
            'planner' => $board->planner(),
        ]);
    }

    public function checkIn(Request $request, Room $room): RedirectResponse
    {
        return $this->perform(
            $request,
            $room,
            fn (FrontDeskActions $actions) => $actions->checkIn($room),
            'front_desk.flash.checked_in',
        );
    }

    public function checkOut(Request $request, Room $room): RedirectResponse
    {
        return $this->perform(
            $request,
            $room,
            fn (FrontDeskActions $actions) => $actions->checkOut($room),
            'front_desk.flash.checked_out',
        );
    }

    public function housekeeping(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'state' => ['required', 'string', 'in:'.implode(',', Room::HOUSEKEEPING_STATUSES)],
        ]);

        return $this->perform(
            $request,
            $room,
            fn (FrontDeskActions $actions) => $actions->setHousekeeping($room, $validated['state']),
            'front_desk.flash.housekeeping',
        );
    }

    public function service(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate(['out_of_order' => ['required', 'boolean']]);

        return $this->perform(
            $request,
            $room,
            fn (FrontDeskActions $actions) => $actions->setOutOfOrder($room, (bool) $validated['out_of_order']),
            $validated['out_of_order'] ? 'front_desk.flash.out_of_order' : 'front_desk.flash.back_in_service',
        );
    }

    /**
     * Runs an action against the room, refusing anything outside the hotel of
     * the signed in user, and turning a refused transition into a form error
     * rather than a crash.
     */
    private function perform(Request $request, Room $room, callable $action, string $message): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->hotel_id === $room->hotel_id, 404);

        try {
            $action(new FrontDeskActions($user));
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'room' => trans($exception->getMessage(), ['room' => $room->number]),
            ]);
        }

        return back()->with('status', trans($message, ['room' => $room->number]));
    }
}
