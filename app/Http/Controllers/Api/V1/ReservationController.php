<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Event;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
        $this->middleware('permission:read-reservations')->only(['index', 'show', 'eventReservations']);
        $this->middleware('permission:create-reservations')->only(['store']);
        $this->middleware('permission:update-reservations')->only(['update']);
        $this->middleware('permission:delete-reservations')->only(['destroy']);
    }

    /**
     * Display a listing of the user's reservations.
     */
    public function index(): AnonymousResourceCollection
    {
        $reservations = $this->reservationService->getUserReservations();
        return ReservationResource::collection($reservations);
    }

    /**
     * Store a newly created reservation in storage.
     */
    public function store(StoreReservationRequest $request): ReservationResource
    {
        $reservation = $this->reservationService->create($request);
        return new ReservationResource($reservation);
    }

    /**
     * Display the specified reservation.
     */
    public function show(Reservation $reservation): ReservationResource|JsonResponse
    {
        // التحقق من أن المستخدم هو صاحب الحجز أو مسؤول
        if ($reservation->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'غير مصرح لك بعرض هذا الحجز'], 403);
        }

        $reservation->load(['event', 'user']);
        return new ReservationResource($reservation);
    }

    /**
     * Update the specified reservation.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): ReservationResource
    {
        $reservation = $this->reservationService->update($request, $reservation);
        return new ReservationResource($reservation);
    }

    /**
     * Remove the specified reservation.
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        // التحقق من أن المستخدم هو صاحب الحجز أو مسؤول
        if ($reservation->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الحجز'], 403);
        }

        $this->reservationService->cancel($reservation);
        return response()->json(['message' => 'تم إلغاء الحجز بنجاح']);
    }

    /**
     * Get all reservations for a specific event.
     */
    public function eventReservations(Event $event): AnonymousResourceCollection|JsonResponse
    {
        // التحقق من أن المستخدم هو منظم الفعالية أو مسؤول
        if ($event->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'غير مصرح لك بعرض حجوزات هذه الفعالية'], 403);
        }

        $reservations = $this->reservationService->getEventReservations($event);
        return ReservationResource::collection($reservations);
    }

    /**
     * Check if user has already reserved for an event.
     */
    public function checkReservation(Event $event): JsonResponse
    {
        $hasReserved = $this->reservationService->hasUserReserved($event);
        return response()->json(['has_reserved' => $hasReserved]);
    }
}

