<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login()
    {
        return view("backend.login");
    }
    function dologin(Request $request){
        $username =$request->email;
        $password = $request->password;
        $data_login=[
            'email'=>$username,
            'password'=>$password
        
        ];
        print_r($data_login);
        if(Auth::attempt($data_login)){
            return redirect()->route('admin.dashboard');
            // echo"thanh cong";
        }
        else{
            return redirect()->route('admin.login')->with('error','Thông tin không chính xác!');
        }
        // return redirect()->route('admin.login')->with('error','Thông tin không chính xác!');
    }
    function logout(){
        Auth::logout();
        return redirect('admin.login');
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
