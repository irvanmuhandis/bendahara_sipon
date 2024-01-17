<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Santri;
use Error;
use Exception;

class GroupController extends Controller
{
    var $table = "acc_groups";
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
        return Group::all();
    }

    public function santri()
    {
        $apps = Group::whereHas('santri')->with(['santri' => function ($query) {
            $query->selectRaw('santris.nis, santris.fullname');
        }])->paginate(5);

        return $apps;
    }

    public function form()
    {
        try {
            $nis = request('santri');
            $nis = json_decode($nis, true);

            if ($nis) {
                $santris = Santri::select('nis', 'fullname')->whereIn('nis', $nis)->with('group')->get();
                return $santris;
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
        $santris = request('santri');
        foreach ($santris as $santri) {
            $data = Santri::where('nis', '=', $santri)->first();

            $data->group()->attach(request('group'));
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
        $datas = request('datas');

        $log = [];
        foreach ($datas as $data) {
            // dd($data);
            $grup = Group::find($data['pivot']['group_id'])->santri()->detach($data['pivot']['nis']);
            array_push($log, $grup);
        }
        // Group::whereIn('id', request('datas'))->delete();

        return response()->json([
            'message' => 'Groups deleted successfully!',
            'data' => $log
        ]);
    }

    public function destroy()
    {
        foreach (request('ids') as $id) {
            Group::where('id', $id)->first()->santri()->detach();
        }
        $RES = Group::whereIn('id', request('ids'))->delete();

        return response()->json([
            'message' => 'Groups deleted successfully!',
            'data' => $RES
        ]);
    }

    public function santri_search()
    {
        $searchQuery = request('query');
        $searchGroup = request('group');

        $apps = Group::when($searchGroup, function ($query) use ($searchGroup) {
            $query->where('id', $searchGroup);
        })->whereHas('santri')->with(['santri' => function ($query) use ($searchQuery) {
            $query->where('fullname', 'like', "%{$searchQuery}%")->get();
        }])->paginate(5);

        return $apps;
    }

    public function search()
    {
        $searchQuery = request('query');

        $group = Group::where('group_name', 'like', "%{$searchQuery}%")->latest()->paginate(10);
        return response()->json($group);
    }

    public function santri_search_bill()
    {
        $searchQuery = request('query');
        $searchGroup = request('group');

        $apps = Santri::select('nis', 'fullname')->where('status', 1)->where('option', 1)->whereHas('group', function ($query) use ($searchGroup) {
            $query->when($searchGroup, function ($query) use ($searchGroup) {
                $query->whereRaw("$this->table.id=$searchGroup");
            });
        })->with(['group' => function ($query) use ($searchGroup) {
            $query->when($searchGroup, function ($query) use ($searchGroup) {
                $query->whereRaw("$this->table.id=$searchGroup");
            });
        }])->orderBy('nis', 'asc')->get();

        return $apps;
    }
}
