<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as ViewResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Super-admin user management.
 *
 * Only users with role `supreme` or `superadmin` (the "supreme admin"
 * tier, see {@see User::isSupreme()}) can reach these endpoints. From
 * here they can:
 *
 *   - browse every user in the system (all roles)
 *   - filter by role and free-text search on name/email
 *   - delete any user except themselves and other supreme admins
 *
 * Self-service account deletion was removed from /settings/profile. The
 * supreme admin is the only path to remove an account now.
 */
class SuperAdminUserController extends Controller
{
    /** Roles that this controller scopes to on the listing page. */
    private const ALL_ROLES = [
        'supreme',
        'superadmin',
        'admin',
        'promoter',
        'promoter_manager',
        'sub_promoter',
    ];

    /**
     * List every user in the system with role/search filters.
     */
    public function index(Request $request): ViewResponse
    {
        $this->authorizeSupreme();

        $search  = trim((string) $request->query('search', ''));
        $roleFilter = (string) $request->query('role', '');

        $query = User::query()->with('parent')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== '' && in_array($roleFilter, self::ALL_ROLES, true)) {
            $query->where('role', $roleFilter);
        }

        $users = $query->paginate(25)->withQueryString();

        // Per-user aggregates so the table can show how "active" each row is
        // without an N+1 query explosion.
        $userIds = $users->pluck('id')->all();

        $orderCounts = DB::table('ticket_orders')
            ->select('requested_by', DB::raw('count(*) as c'))
            ->whereIn('requested_by', $userIds)
            ->groupBy('requested_by')
            ->pluck('c', 'requested_by');

        $subCounts = DB::table('users')
            ->select('parent_id', DB::raw('count(*) as c'))
            ->whereIn('parent_id', $userIds)
            ->groupBy('parent_id')
            ->pluck('c', 'parent_id');

        $users->getCollection()->transform(function (User $u) use ($orderCounts, $subCounts) {
            $u->orders_count      = (int) ($orderCounts[$u->id] ?? 0);
            $u->sub_promoters_count = (int) ($subCounts[$u->id] ?? 0);
            return $u;
        });

        return view('pages.supremeadmin.users', [
            'users'        => $users,
            'allRoles'     => self::ALL_ROLES,
            'search'       => $search,
            'roleFilter'   => $roleFilter,
        ]);
    }

    /**
     * Permanently delete a user.
     *
     * Refuses to delete:
     *   - the currently authenticated supreme admin (can't lock yourself out)
     *   - any other supreme admin (one supreme admin can't nuke the others)
     *
     * Wrapped in a transaction so related rows (ticket_orders.commissions,
     * payments, etc.) stay consistent.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeSupreme();

        if ($user->id === Auth::id()) {
            return redirect()
                ->route('superadmin.users.index')
                ->with('error', __('alert.user_cannot_delete_self'));
        }

        if ($user->isSupreme()) {
            return redirect()
                ->route('superadmin.users.index')
                ->with('error', __('alert.user_cannot_delete_supreme'));
        }

        // Sub-promoters should be unlinked from their manager (parent_id set
        // to NULL) rather than cascade-deleted, so the manager still has
        // historical visibility into who used to be on their team.
        if ($user->role === 'sub_promoter') {
            User::where('parent_id', $user->id)->update(['parent_id' => null]);
        }

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', __('alert.user_deleted_success', ['name' => $user->name]));
    }

    /**
     * Defensive guard: every entry point on this controller requires the
     * supreme-admin role. The route group already applies the same
     * middleware, but checking again here protects against accidental
     * route changes.
     */
    private function authorizeSupreme(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->isSupreme(), 403, 'Supreme admin only.');
    }
}