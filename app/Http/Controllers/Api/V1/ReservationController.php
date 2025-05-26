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
use Illuminate\Routing\Controllers\Middleware;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:read-reservations', only:['index', 'show', 'eventReservations']),
            new Middleware('permission:create-reservations', only:['store']),
            new Middleware('permission:update-reservations', only:['update']),
            new Middleware('permission:delete-reservations', only:['destroy']),
        ];
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
    public function store(StoreReservationRequest $request): ReservationResource|JsonResponse
    {
        // التحقق من عدم وجود حجز سابق للمستخدم في نفس الفعالية
        $event = Event::findOrFail($request->event_id);
        if ($this->reservationService->hasUserReserved($event)) {
            return response()->json([
                'message' => 'لديك حجز مسبق في هذه الفعالية'
            ], 422);
        }

        $reservation = $this->reservationService->create($request);
        
        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(201);
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

        $reservation->load(['event' => function($query) {
            $query->with(['eventType', 'location']);
        }, 'user']);
        
        return new ReservationResource($reservation);
    }

    /**
     * Update the specified reservation.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): ReservationResource|JsonResponse
    {
        // التحقق من إمكانية تعديل الحجز (الفعالية لم تبدأ بعد)
        if (!$reservation->canBeCancelled()) {
            return response()->json([
                'message' => 'لا يمكن تعديل الحجز لأن الفعالية قد بدأت بالفعل'
            ], 422);
        }

        try {
            $reservation = $this->reservationService->update($request, $reservation);
            
            // التحقق مما إذا تم تغيير أي شيء
            if ($reservation->isDirty()) {
                return response()->json([
                    'message' => 'لم يتم إجراء أي تغييرات'
                ]);
            }
            
            return new ReservationResource($reservation);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث الحجز: ' . $e->getMessage()
            ], 500);
        }
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

        // التحقق من إمكانية إلغاء الحجز
        if (!$reservation->canBeCancelled()) {
            return response()->json([
                'message' => 'لا يمكن إلغاء الحجز لأن الفعالية قد بدأت بالفعل'
            ], 422);
        }

        try {
            $this->reservationService->cancel($reservation);
            return response()->json(['message' => 'تم إلغاء الحجز بنجاح']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إلغاء الحجز: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Get popular events based on reservation count.
     */
    public function popularEvents(): JsonResponse
    {
        $events = $this->reservationService->getPopularEvents();
        return response()->json(['data' => $events]);
    }
}


