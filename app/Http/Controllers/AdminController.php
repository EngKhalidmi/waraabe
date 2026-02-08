<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\models\User;

class AdminController extends Controller
{
    //
    public function Profile(){
        // In this method we find which the the user is Logged in 
        // when we get the logged in user we store the User Data from the User Model to the $adminData Variable

        
        $id = Auth::id(); // Retrieve the authenticated user's ID

        if ($id) {
            $adminData = User::find($id);
    
            if ($adminData) {
                // User and ID exist, proceed with your logic here
                // ...
            } else {
                // If the user ID doesn't correspond to a valid user
                // Redirect to the login page or perform any other action
                return redirect()->route('login');
            }
        } else {
            // User is not authenticated, redirect to login
            return redirect()->route('login');
        }
    }

   
    
}
