<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Exception;
use Ramsey\Uuid\Type\Integer;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $group_id = $request->query('group_id');

            if ($group_id) {
                // If group_id is specified, filter accounts by group_id
                $users = User::where('group_id', $group_id)->get();
            } else {
                // Otherwise, return all accounts
                $users = User::latest()->get();
            }

            return $users;
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
            ->select('bills.*', 'accounts.account_name', 'users.name')
            ->where('bills.user_id', '=', $id)
            ->where('bills.payment_status', '<', 3)
            ->orderBy('users.id', 'asc')
            ->get();
        return $db;
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
