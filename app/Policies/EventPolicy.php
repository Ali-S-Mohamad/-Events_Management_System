<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return true; // جميع المستخدمين المصادقين يمكنهم عرض قائمة الأحداث
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        return true; // جميع المستخدمين المصادقين يمكنهم عرض تفاصيل الأحداث
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        // السماح لأي مستخدم مصادق بإنشاء فعالية
        return true;
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        // المسؤول يمكنه تعديل أي حدث
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // المنظم يمكنه تعديل الأحداث التي أنشأها فقط
        if ($user->hasRole('organizer') && $user->id === $event->user_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        // المسؤول يمكنه حذف أي حدث
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // المنظم يمكنه حذف الأحداث التي أنشأها فقط
        if ($user->hasRole('organizer') && $user->id === $event->user_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can set a cover image for the event.
     */
    public function setCoverImage(User $user, Event $event): bool
    {
        // المسؤول يمكنه تعيين صورة غلاف لأي حدث
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // المنظم يمكنه تعيين صورة غلاف للأحداث التي أنشأها فقط
        if ($user->hasRole('organizer') && $user->id === $event->user_id) {
            return true;
        }
        
        return false;
    }
}
