<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Enums\PayStatus;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);
        return $users;
    }

    public function group(Request $request)
    {
        try {
            $group_id = request('user_id');

            if ($group_id) {
                // If group_id is specified, filter accounts by group_id
                $users = User::whereIn('id', $group_id)->with('group')->get();
                return $users;
            }
        } catch (Exception $e) {
            logger($e);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }


    public function bill($id)
    {
        $db = DB::table('bills')

            ->join('users', 'users.id', '=', 'bills.user_id')
            ->join('accounts', 'accounts.id', '=', 'bills.account_id')
            ->join('status_colors', 'status_colors.status_id', '=', 'bills.payment_status')
            ->select('bills.*', 'accounts.account_name', 'users.name', 'status_colors.color')
            ->where('bills.user_id', '=', $id)
            ->where('bills.payment_status', '<', 3)
            ->orderBy('bills.due_date', 'desc')
            ->latest()->paginate(9);

        return response()->json($db);
    }

    public function debt($id)
    {
        $db = DB::table('debts')
            ->join('users', 'users.id', '=', 'debts.user_id')
            ->join('accounts', 'accounts.id', '=', 'debts.account_id')
            ->join('status_colors', 'status_colors.status_id', '=', 'debts.payment_status')
            ->select('debts.*', 'accounts.account_name', 'users.name', 'status_colors.color')
            ->where('debts.user_id', '=', $id)
            ->where('debts.payment_status', '<', 3)
            ->orderBy('debts.created_at', 'desc')
            ->latest()->paginate(7);

        return response()->json($db);
    }

    public function list()
    {
        $users = DB::table('users')
            ->orderBy('users.name', 'asc')
            ->get();

        return $users;
    }

    public function store()
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:8',
        ]);

        return User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => bcrypt(request('password')),
        ]);
    }

    public function update(User $user)
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:8',
        ]);

        $user->update([
            'name' => request('name'),
            'email' => request('email'),
            'password' => request('password') ? bcrypt(request('password')) : $user->password,
        ]);

        return $user;
    }

    public function destory(User $user)
    {
        $user->delete();

        return response()->noContent();
    }

    public function changeRole(User $user)
    {
        $user->update([
            'role' => request('role'),
        ]);

        return response()->json(['success' => true]);
    }

    public function search()
    {
        $searchQuery = request('query');

        $users = User::where('name', 'like', "%{$searchQuery}%")->paginate();

        return response()->json($users);
    }

    public function bulkDelete()
    {
        User::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Users deleted successfully!']);
    }
}
