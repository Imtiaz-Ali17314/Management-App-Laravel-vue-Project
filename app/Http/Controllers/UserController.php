<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(5);
        return response()->json($users , 201);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'city'  => 'required|string|max:255',
        'image' => 'nullable|image',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    $imagePath = null;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('image', 'public');
    }

    $user = User::create([
        'name'       => $request->name,
        'email'      => $request->email,
        'city'       => $request->city,
        'image_path' => $imagePath,
    ]);

    return response()->json($user, 201);
}


    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
            // Validation
            $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'city'  => 'required|string|max:255',
                'image' => 'nullable|image|max:5120'
            ]);
        
            // Old image ka path store karo
            $oldImagePath = $user->image_path;
        
            // Image handle
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('image', 'public');
            
                if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            
                $user->image_path = $imagePath;
            }
        
            // Update fields
            $user->name  = $request->name;
            $user->email = $request->email;
            $user->city  = $request->city;
        
            // Save changes
            $user->save();
        
            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Agar image path set hai to storage se delete karein
        if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
            Storage::disk('public')->delete($user->image_path);
        }

        // User ko delete karein
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ], 200);
    }
}



