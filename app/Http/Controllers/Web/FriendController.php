<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use App\Services\FriendshipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FriendController extends Controller
{
    public function __construct(private readonly FriendshipService $friendshipService) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $relationships = Friendship::query()
            ->involving($user->id)
            ->with(['user:id,name,email', 'friend:id,name,email'])
            ->latest()
            ->get();

        $relationshipCard = function (Friendship $friendship) use ($user): array {
            return [
                'friendship' => $friendship,
                'user' => $friendship->user_id === $user->id
                    ? $friendship->friend
                    : $friendship->user,
            ];
        };

        $incomingRequests = $relationships
            ->where('status', Friendship::STATUS_PENDING)
            ->where('requested_by', '!=', $user->id)
            ->map($relationshipCard);

        $outgoingRequests = $relationships
            ->where('status', Friendship::STATUS_PENDING)
            ->where('requested_by', $user->id)
            ->map($relationshipCard);

        $friends = $relationships
            ->where('status', Friendship::STATUS_ACCEPTED)
            ->map($relationshipCard);

        $excludedUserIds = $relationships
            ->whereIn('status', [Friendship::STATUS_PENDING, Friendship::STATUS_ACCEPTED])
            ->flatMap(fn (Friendship $friendship) => [$friendship->user_id, $friendship->friend_id])
            ->push($user->id)
            ->unique();

        $search = trim((string) $request->query('search'));
        $suggestedUsers = User::query()
            ->select('id', 'name', 'email')
            ->whereNotIn('id', $excludedUserIds)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        return view('friends.index', compact(
            'friends',
            'incomingRequests',
            'outgoingRequests',
            'suggestedUsers',
            'search'
        ));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $this->friendshipService->send($request->user(), $user);

        return back()->with('success', "تم إرسال طلب الصداقة إلى {$user->name}.");
    }

    public function accept(Request $request, Friendship $friendship): RedirectResponse
    {
        $this->friendshipService->accept($request->user(), $friendship);

        return back()->with('success', 'تم قبول طلب الصداقة.');
    }

    public function destroy(Request $request, Friendship $friendship): RedirectResponse
    {
        $this->friendshipService->decline($request->user(), $friendship);

        return back()->with('success', 'تم رفض طلب الصداقة.');
    }
}
