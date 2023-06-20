<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Error;
use Exception;

class GroupController extends Controller
{
    public function index()
    {
        return Group::latest()
            ->paginate(10)->through(fn ($app) => [
                'id' => $app->id,
                'group_name' => $app->group_name,
                'desc' => $app->group_desc,
                'created_at' => $app->created_at->format('Y-m-d h:i A'),
                'updated_at' => $app->updated_at->format('Y-m-d h:i A'),
            ]);
    }

    public function list()
    {
        $apps = Group::all();

        $data = $apps->map(function ($app) {
            $app->created_at = $app->created_at->format('Y-m-d h:i A');
            $app->updated_at = $app->updated_at->format('Y-m-d h:i A');
            return $app;
        });
        return $data;
    }

    public function santri()
    {
        $apps = Group::with('user')->paginate(5);

        return $apps;
    }

    public function form()
    {
        try {
            $id = request('santri');
            $id = json_decode($id, true);

            if ($id) {
                $users = User::whereIn('id', $id)->with('group')->get();
                return $users;
            }
        } catch (Exception $e) {
            logger($e);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }


    public function show($id)
    {
        $grup = Group::where('id', '=', $id)->get();
        return $grup;
    }

    public function link()
    {
        $array = [];
        $users = request('user');
        foreach ($users as $user) {
            $data = User::find($user);
            $data->group_id = request('group');
            $data->save();
            array_push($array, $data);
        }
        return $array;
    }

    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);

        $data = Group::create([
            'group_name' => request('name'),
            'group_desc' => request('desc')
        ]);
        return $data;
    }

    public function update()
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);

        $grup = Group::where('id', '=', request('id'))->first();

        $grup->update([
            'group_name' => request('name'),
            'group_desc' => request('desc')
        ]);

        return $grup;
    }


    public function bulkDelete()
    {
        Group::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Groups deleted successfully!']);
    }

    public function destroy($id)
    {
        $RES = Group::where('id', '=', $id)->first()->delete();

        return $RES;
    }

    public function user_search(Group $dispen)
    {
        $searchQuery = request('query');


        $apps = Group::with(['user' => function ($query) use ($searchQuery) {
            $query->where('name', 'like', "%{$searchQuery}%")->get();
        }])->paginate(5);

        return $apps;
    }

    public function search()
    {
        $searchQuery = request('query');

        $group = Group::where('group_name', 'like', "%{$searchQuery}%")->latest()->paginate(10);
        return response()->json($group);
    }
}
