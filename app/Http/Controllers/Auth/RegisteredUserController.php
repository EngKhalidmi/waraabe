<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Departments;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $query = User::where('username', '!=', 'usame');
    
            // Applying filters
            if ($request->input('name')) {
                $query->where('name', 'LIKE', "%{$request->input('name')}%");
            }

            if ($request->input('username')) {
                $query->where('username', 'LIKE', "%{$request->input('username')}%");
            }

            if ($request->input('depID')) {
                $query->where('depID', 'LIKE', "%{$request->input('depID')}%");
            }

            if ($request->input('phone')) {
                $query->where('phone', 'LIKE', "%{$request->input('phone')}%");
            }

            if ($request->input('role')) {
                $query->where('role', 'LIKE', "%{$request->input('role')}%");
            }
    

            if ($request->input('startDate') && $request->input('endDate')) {
                $query->whereBetween('created_at', [$request->input('startDate'), $request->input('endDate')]);
            }  

            $totalData = $query->count();  // Count before pagination
            $totalFiltered = $totalData;
    
            $columns = [
                0 => 'id',
                1 => 'name',
                2 => 'username',
                3 => 'role',
                5 => 'phone',
                6 => 'image',
                7 => 'status',
                8 => 'created_at',
                9 => 'depID',
            ];
    
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
    
            $query->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir);
    
        $users = $query->get();

        $data = [];
        foreach ($users as $user) {
            $nestedData['id'] = $user->id;
            $nestedData['name'] = $user->name;
            $nestedData['username'] = $user->username;
            $nestedData['role'] = $user->role;
            $nestedData['phone'] = $user->phone ? $user->phone : 'N/A';
            $nestedData['image'] = $user->image ? '<img src="'.asset('images/users/'. $user->image).'" alt="User Image" width="40" height="40">' : 'N/A';
            $nestedData['status'] = ($user->status == 1)? 'Active' : 'Inactive';
            $nestedData['created_at'] = $user->created_at->format('Y-m-d');
            $nestedData['depID'] = $user->depID ? $user->department->name : 'N/A';
            $nestedData['action'] = $user->id;

            $data[] = $nestedData;
        }
    
        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ];

        return response()->json($json_data);
        }
        $departments = Departments::get();
        return view('auth.all_users', compact('departments'));
    }

    public function create(){
        $departments = Departments::get();
        return view('auth.register', compact('departments'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // Approve User
    public function approve($id) {
        $user = User::findOrFail($id);
        $user->status = 1;
        $user->save();

        return redirect()->route('users')->with('status', 'User Approved Successfully');
    }

    // Pending User
    public function pend($id) {
        $user = User::findOrFail($id);
        $user->status = 0;
        $user->save();

        return redirect()->route('users')->with('status', 'User Disabled Successfully');
    }


    public function store(Request $request): RedirectResponse
    {
    if (Auth::user()->role === 'acc') {
        return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to register a new user.']);
    }
    
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'phone' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'depID' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3048'],
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $usersData = [
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => false,
            'email' => $request->email,
            'depID' => $request->depID,
            'password' => Hash::make($request->password),
        ];
    
        // Handle file upload if an image is present
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $userName = preg_replace('/[^A-Za-z0-9]/', '', $request->username); // Remove special characters from the username
                $filename = $userName . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/users'), $filename);
                $usersData['image'] = $filename;
            } else {
                // Handle invalid file
                return redirect()->back()->withErrors(['image' => 'Invalid image file.']);
            }
        } else {
            return redirect()->back()->withErrors(['image' => 'Image file not found.']);
        }
    
        $user = User::create($usersData);
        return redirect()->route('users')->with('status', 'User Registered Successfully');
    }

        // Fetch classes Data 
    // Edit Menu
    public function edit($id){
        $record = User::find($id);
        $departments = Departments::get();
        return view('auth.update', compact('record', 'departments'));
    }

    // Update classes Data
    public function update(Request $request, int $id){
            // Validating Form
            $request->validate([
            'name' => 'required|max:255|string',
            'username' => 'required|max:255|string',
            'role' => 'required|max:255|string',
            'depID' => 'required',
            'status' => 'required',
            'phone' => 'required',
            'email' => 'required',
        ]);
    
        // Retrieve the user by ID
        $user = User::findOrFail($id);

        // Initialize an array for classes data
        $usersData = [
            'name' => $request->name,
            'username' => $request->username,
            'role' => $request->role,
            'status' => $request->status,
            'depID' => $request->depID,
            'phone' => $request->phone,
            'email' => $request->email,
        ];
            // Handle file upload if an image is present
        // If there's a new image file save or un link the existing
        if ($request->hasFile('image')) {
            if ($user->image) {
                $imagePath = public_path('/images/users/'. $user->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $image = $request->file('image');
            $imageName = $request->username. time(). '.'. $image->getClientOriginalExtension();
            $destinationPath = public_path('/images/users');
            $image->move($destinationPath, $imageName);
            $user->image = $imageName;
        } 
        $user->save();
    

        // Create a new users record
        User::findOrFail($id)->update($usersData);
    
        // After sending data, display the success message
        return redirect('/users')->with('status', 'User Updated successfully');
    }
    
    // Delete User data
    public function destroy($id){
        //Delete User 
        $user = User::find($id);
        // unlink it's image
        if ($user->image) {
            $imagePath = public_path('/images/users/'. $user->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $user->delete();

        //Redirect to Index Page
        return redirect()->route('users')->with('status', 'User Deleted Successfully');
    }
}
